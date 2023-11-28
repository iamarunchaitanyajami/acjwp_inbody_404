=== ACJWP INBODY 404's CLI ===

Donate link: https://iamarunchaitanyajami.com

**Tags**: CLI

**Requires at least**: 3.0.1

**Tested up to**: 6.4

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

**== Description ==**

This plugin is useful to run custom cli that run a query on the posts table to find 404 in the body of each post type.

**== Installation ==**

This section describes how to install the plugin and get it working.

e.g.

1. Upload `wp-inbody-404.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Now login into you server and in root of the project run the following command
4. ``wp inbody404 find-404-url --post_types="post,page" --limit=1000 --post_status="publish,draft" --between="2021-01-01,2022-01-01" --allow-root``

