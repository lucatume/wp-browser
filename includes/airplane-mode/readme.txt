=== Airplane Mode ===
Contributors: norcross, johnbillion, afragen, szepeviktor, chriscct7, markjaquith
Website Link: https://github.com/norcross/airplane-mode
Donate link: https://andrewnorcross.com/donate
Tags: external calls, HTTP
Requires at least: 4.4
Tested up to: 5.3
Stable tag: 0.2.5
License: MIT
License URI: http://norcross.mit-license.org/

Control loading of external files when developing locally

== Description ==

Control loading of external files when developing locally. WP loads certain external files (fonts, gravatar, etc) and makes external HTTP calls. This isn't usually an issue, unless you're working in an evironment without a web connection. This plugin removes / unhooks those actions to reduce load time and avoid errors due to missing files.

Features

* removes external JS and CSS files from loading
* replaces all instances of Gravatar with a local image to remove external call
* removes all HTTP requests
* disables all WP update checks for core, themes, and plugins
* includes toggle in admin bar for quick enable / disable

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `airplane-mode` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Toggle users as needed

If you need offline activation, see [this script](https://gist.github.com/solepixel/e1d03f4dcd1b9e86552b3cc6937325bf) written by [Brian DiChiara](https://github.com/solepixel)

== Frequently Asked Questions ==

= Why do I need this? =

Because you are a jet set developer who needs to work without internet.


== Screenshots ==


== Changelog ==

= 0.2.5 - 2019/11/15
* Remove usage of method that's been deprecated in WordPress trunk. props @johnbillion

= 0.2.4 - 2017/12/13
* Added localhost bypass for loading CSS and JS files when hot reloading. props @shadyvb
* Added additional hook removals for new automated updates and language packs.
* Removed outdated `create_function` call for PHP 7.2 compatibility. props @geminorum
* General cleanup

= 0.2.3 - 2017/03/05
* Adding option to force transient purge
* Adding `clean` action to WP-CLI functions

= 0.2.2 - 2016/12/07
* Adding WP-CLI support. props @markjaquith

= 0.2.1 - 2016/10/19
* Fix settings page language drowdown when used offline. props @onnimonni

= 0.2.0 - 2016/08/26
* modify CSS loading for front-end, removed loading for login page. props @barryceelen
* fixed WP.org theme API call request to avoid `WP_Error` return. props @onnimonni

= 0.1.9 - 2016/07/25
* Prevent BuddyPress from falling back to Gravatar. props @johnbillion

= 0.1.8 - 2016/07/12
* allow `JETPACK_DEV_DEBUG` constant to take priority over filter. props @kopepasah
* added additional CSS for upcoming 4.6. change to upload tab.

= 0.1.7 - 2016/05/18
* allow local HTTP calls with optional filter. props @johnbillion
* add back index.php link to main dashboard menu item
* bumped minimum WP version requirement to 4.4

= 0.1.6 - 2016/04/25
* minor tweak to include CSS for new icon font

= 0.1.5 - 2016/04/24
* adding custom icon font for display and removing label. props @barryceelen

= 0.1.4 - 2016/02/26
* better setup for blocked external assets. props @johnbillion

= 0.1.3 - 2016/02/22
* modified CSS rules to fix media bulk actions bar from disappearing
* moved `airplane_mode_status_change` action to run before redirect, and now includes the status being run.

= 0.1.2 - 2016/01/09
* added back HTTP count when inactive
* removed HTTP count completely when Query Monitor is active

= 0.1.1 - 2016/01/06
* fixed incorrect nonce check that was breaking toggle
* changed CSS and JS checks to include all themes and plugins as well as core

= 0.1.0 - 2015/12/30
* added `airplane_mode_purge_transients` filter to bypass transient purge

= 0.0.9 - 2015/12/07
* changed from colored circle to actual airplane icon for usability
* fixed dashboard link icon for multisite
* changed to exclude all external stylesheets, not just Open Sans
* added language files for translateable goodness
* general cleanup for WP coding standards

= 0.0.8 - 2015/05/18
* added `class_exists` as now included in DesktopServer and collisions could result
* fixed `if ( ! defined ...` for `AIRMDE_BASE` constant

= 0.0.7 - 2015/04/21 =
* fixed some CSS from hiding plugins page bar
* moved changelog to it's own file
* added `composer.json`
* added contributors to readme
* clarified license (MIT)

= 0.0.6 - 2015/04/02 =
* version bump for GitHub updater

= 0.0.5 - 2015/04/02 =
* fixed bug in update logic that was preventing checks when disabled (but activated). props @johnbillion

= 0.0.4 - 2015/04/02 =
* added multiple checks for all the various theme and plugin update calls. props @chriscct7
* added HTTP counter on a per-page basis. props @szepeviktor

= 0.0.3 - 2015/01/23 =
* added `airplane_mode_status_change` hook for functions to fire on status change
* purge update related transients on disable
* added WordPress formatted readme file

= 0.0.2 - 2015/01/21 =
* added GitHub Updater support
* fixed update capabilities when status is disabled

= 0.0.1 - 2014/06/01 =
* lots of stuff. I wasn't keeping a changelog. I apologize.


== Upgrade Notice ==

= 0.0.1 =
Initial release
