<?php
/*
Plugin Name: WordPress Varnish ESI Widget
Plugin URI: https://github.com/cd34/cd34-varnish-esi
Description: Utilize Varnish and cache the sidebar using ESI
Author: Chris Davies
Version: 0.3
Author URI: https://cd34.com/
License: MIT
License URI: https://opensource.org/licenses/MIT
Text Domain: varnish-esi-widget
Requires at least: 5.0
Requires PHP: 7.4
*/

class ESI_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'esi_widget',
            __( 'ESI Widget', 'varnish-esi-widget' ),
            array( 'description' => __( 'Renders the ESI Widget Sidebar via Varnish ESI include.', 'varnish-esi-widget' ) )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        ?>
        <esi:include src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'esihandler.php' ); ?>"/>
        <?php
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        ?>
        <p><?php esc_html_e( 'Place the Widgets in "ESI Widget Sidebar" and configure as needed.', 'varnish-esi-widget' ); ?></p>
        <?php
    }
}

function esi_widget_register() {
    register_widget( 'ESI_Widget' );

    register_sidebar( array(
        'name'          => __( 'ESI Widget Sidebar', 'varnish-esi-widget' ),
        'id'            => 'esi-widget-sidebar',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ) );
}

function esi_purge( $post_id ) {
    $permalink = get_permalink( $post_id );
    if ( ! $permalink ) {
        return;
    }
    $url = wp_parse_url( $permalink );
    _esi_purge( $url['host'], $url['path'] );
    _esi_purge( wp_parse_url( site_url(), PHP_URL_HOST ), wp_parse_url( plugin_dir_url( __FILE__ ) . 'esihandler.php', PHP_URL_PATH ) );
    _esi_purge( wp_parse_url( site_url(), PHP_URL_HOST ), '/' );
}

function esi_purge_comment( $comment_id ) {
    $comment = get_comment( $comment_id );
    if ( $comment ) {
        esi_purge( $comment->comment_post_ID );
    }
}

function _esi_purge( $hostname, $uri ) {
    $varnish_ips = explode( ',', get_option( 'varnish-esi-servers', '' ) );
    foreach ( $varnish_ips as $ip ) {
        $ip = trim( $ip );
        if ( empty( $ip ) ) {
            continue;
        }
        wp_remote_request( "http://{$ip}{$uri}", array(
            'method'  => 'BAN',
            'headers' => array( 'Host' => $hostname ),
            'timeout' => 5,
        ) );
    }
}

function esi_widget_menu() {
    add_options_page(
        __( 'ESI Widget Options', 'varnish-esi-widget' ),
        __( 'Varnish ESI Widget', 'varnish-esi-widget' ),
        'manage_options',
        'esi-widget-options',
        'esi_widget_options'
    );
}

function esi_widget_options() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'varnish-esi-widget' ) );
    }
    ?>
    <div class="wrap">
        <h2><?php esc_html_e( 'Varnish ESI Widget Setup', 'varnish-esi-widget' ); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'varnish-esi' ); ?>
            <?php do_settings_sections( 'varnish-esi' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="varnish-esi-servers"><?php esc_html_e( 'Varnish Server IPs (comma separated)', 'varnish-esi-widget' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="varnish-esi-servers" name="varnish-esi-servers" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'varnish-esi-servers', '' ) ); ?>" />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function esi_widget_settings_init() {
    register_setting( 'varnish-esi', 'varnish-esi-servers', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
}

add_action( 'widgets_init', 'esi_widget_register' );
add_action( 'edit_post', 'esi_purge' );
add_action( 'deleted_post', 'esi_purge' );
add_action( 'comment_post', 'esi_purge_comment' );
add_action( 'admin_menu', 'esi_widget_menu' );
add_action( 'admin_init', 'esi_widget_settings_init' );
