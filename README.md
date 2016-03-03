dc-wp-plugin
============

A Wordpress Plugin for Deseret Connect

Installation
============
1. Upload the contents of wp-deseret_connect.zip to your plugins directory.
2. Activate the plugin
3. Enter your API key in the "deseret_connect" menu on your admin panel.

Options
============
* Mark new articles as pending?
* Include author name in article body?
* Include canonical tags in the head of the page?
* Post Type - When an article is imported, what type would you like it to be?

Changelog
============
~Current Version:1.0.7~

### 1.0.7
* Added featured image support
* Added State id support for bulk imports (coordinate with Deseret Connect)

### 1.0.6
* Turned of Kses filtering when posts are inserted so we can insert data attributes

### 1.0.5
* Added support for extra author data
* Fixed image attachment issues

### 1.0.4
* Added new config option to toggle the output of canonical tags in the head of the page
* Plugin checks if current_user_can 'manage_options' before adding the DC Admin item in the nav
* Started adding things to this Changelog. The rest is history.
