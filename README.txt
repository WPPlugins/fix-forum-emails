=== Plugin Name ===
Contributors: fliz, kona
Tags: bbpress, email, HTML entities, notifications, email notifications, entities  
Requires at least: 3.9
Tested up to: 4.8
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fixes unwanted HTML entities in bbPress notification email subject lines and message bodies.

== Description ==

Email notifications sent by bbPress will sometimes include unwanted HTML entities, particularly in the email subject but sometimes in the email text as well.

For example, a forum topic as follows:

			My thoughts & objections

might come through with an email subject as follows:

			My thoughts &amp; objections

There is a fix for this marked "under consideration" in the bbPress bug tracking system:
[https://bbpress.trac.wordpress.org/ticket/2865](https://bbpress.trac.wordpress.org/ticket/2865)

In the meantime, this plugin implements a version of the suggested fix.  (Props to @thebrandonallen for the patch code.)


**IMPORTANT NOTES:**

This plugin only works with **bbPress 2.5.8** or later.

If you're using other plugins that affect how bbPress sends emails, then this plugin may not solve your HTML entities issues.

== Installation ==

1. Install this plugin via the WordPress plugin control panel, or by manually downloading it and uploading the extracted folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's all! There are no configurable options for this plugin.

== Frequently Asked Questions ==

= I'm still seeing HTML entities in my emails. =

Check that you're not using other add-on plugins that affect how bbPress sends
emails.  If you aren't, please report this to us as a bug (including details
of which HTML entities are appearing and where), and we'll see what
we can do.

== Screenshots ==

== Changelog ==

= 1.0 =
* Initial version

== Upgrade Notice ==

= 1.0 =
Initial version.
