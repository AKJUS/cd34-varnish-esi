=== Plugin Name ===
Plugin Name: WordPress + Varnish ESI Caching
Donate: https://cd34.com/
Contributors: cd34
Tags: cache, caching, varnish, esi
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.3

This plugin uses WordPress + Varnish and caches the sidebar with ESI.

== Description ==

This plugin uses [Varnish](https://www.varnish-cache.org/) and [ESI](https://www.varnish-cache.org/docs/trunk/users-guide/esi.html) to cache the sidebar separately from the page and aggressively uses bans so that you can set long expire times without worrying about comments and post edits not showing up immediately.

Should you use this?

Probably not.

If you're on a virtual server, it is likely that you won't be able to install
Varnish or run your site behind it. If you are able to run Varnish, the use
case for this plugin is a little different than [WordPress Varnish](https://wordpress.org/plugins/flavflavor-varnish/).

If you are trying to cache your site where your visitors are hitting one
or two pages and you don't get many comments, a standard Varnish purge plugin will result in faster pageloads.

If your site is dynamic and you receive a lot of comments, or your visitors
are likely to surf multiple pages rather than a single landing page, using
ESI will probably provide enough benefit to make the plugin worthwhile for
you.

== Installation ==

1. Activate the plugin through the 'Plugins' menu in WordPress
2. Appearance, Widgets, drag the ESI Widget to Sidebar 1
3. Drag the Widgets that you want to display on your sidebar to the
newly created ESI Widget Sidebar.
4. Settings, Varnish ESI Widget, set the Server IP(s) that the plugin
needs to communicate with to issue bans.
5. Make sure you have configured Varnish 4.0+ with the included default.vcl
or with one that you have created that allows the plugin to issue BANs.
Verify your ACL is set to allow banning.

== Frequently Asked Questions ==

= Why is this approach better than other caching systems? =

The worst queries in WordPress are usually contained within the sidebar. If
your content is engaging and someone browses from one page to the next,
most caching plugins need to generate the page and sidebar on a fresh visit.
Since this plugin caches the sidebar separately using ESI, the only
queries executed on the second and successive pages is the page content —
not the sidebar.

= What plugins break with this? =

Any plugin that requires the page to load and not be served from cache
will break. Since your page might be served from Varnish, it will never
hit the backend server. Statistics plugins are generally affected, but
Google Analytics and Matomo work properly. Banner ad solutions also
need to serve the content through an iframe or JavaScript rather than
depending on a pageload.

WP Greet Box and WP Post Views are known to break with caching.

== Changelog ==

= 0.3 =
* Modernized plugin: uses WP_Widget class instead of deprecated functions
* Updated VCL from Varnish 3 to Varnish 4+ syntax
* Added comment_post hook for automatic cache purging on new comments
* Fixed string concatenation bug in ESI handler purge
* Added output escaping and input sanitization
* Replaced fsockopen with wp_remote_request
* Updated ESI handler to use dirname instead of getcwd

= 0.2 =
* Updated VCL to actually parse for ESI
* Fixed ESI handler path

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.3 =
Major update: requires Varnish 4.0+ and WordPress 5.0+. Uses modern WordPress Widget API.
