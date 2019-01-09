=== Plugin Name ===
Contributors: fabrizim
Tags: advancedcustomfields, acf, multisite
Requires at least: 5.0.1
Tested up to: 5.7.9
Requires PHP: 5.6.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds the ability to add options pages in the network admin menu.

== Description ==

This plugin adds the ability to add options pages in the network admin menu.

== Installation ==

Install the plugin the normal way. Make sure that Advanced Custom Fields Pro
is "Network Activated". Also "Network Activate" this plugin.

When using the `acf_add_options_page` or `acf_add_options_sub_page` functions,
you can now use an optional `network` option to display the page on the Network
Admin pages.

Examples:

```
<?php
acf_add_options_page([
    'network' => true,
    'post_id' => 'acf_network_options',
    'page_title' => 'Network Options',
    'menu_title' => 'Network Options'
]);

acf_add_options_page([
    'network' => true,
    'post_id' => 'acf_network_options2',
    'page_title' => 'Network Options 2',
    'menu_title' => 'Network Options 2',
    'parent_slug' => 'settings.php'
]);
```

== Frequently Asked Questions ==

= Where are the values stored? =

The values are stored in the `sitemeta` table, allowing for access
across all sites in the network.

== Notes ==

If this functionality was to be introduce into the ACF core, it would reduce a lot of
redundant code in this plugin, as it is necessary to re-implement several
of the functions surrounding getting and setting values.

Field groups for the multisite options pages should be saved and loaded
to a Network Activated plugin via [https://www.advancedcustomfields.com/resources/local-json/](ACF local JSON).
