=== The Events Calendar Extension: Events Happening Now ===
Contributors: ModernTribe
Donate link: http://m.tri.be/29
Tags: events, calendar
Requires at least: 4.5
Tested up to: 5.3.2
Requires PHP: 5.6
Stable tag: 1.0.0
License: GPL version 3 or any later version
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Provides a widget, shortcode, and optional meta for displaying events that are happening now.

== Description ==

This extension adds the `[tribe-happening-now]` shortcode. The parameters that can be passed are as follows:

* `all_day`: If true, only includes all day events. If false, excludes all day events. If null (left off the shortcode), includes all.
* `category`: Categories to display
* `featured`: If true, only includes featured events. If false, excludes featured events. If null (left off the shortcode), includes all.
* `hide_url`: If true, suppresses the Website URL CTA
* `quantity`: Max number of events to show (defaults to unlimited “-1”)
* `title`: Title above event list
* `url_title`: Title of event’s Website URL CTA
* `start_margin`: Start time margin for live events. Uses PHP createFromDateString syntax. Examples: 30 minutes, 1 hour + 15 minutes, 2 hours

== Installation ==

Install and activate like any other plugin!

* You can upload the plugin zip file via the *Plugins ‣ Add New* screen
* You can unzip the plugin and then upload to your plugin directory (typically _wp-content/plugins_) via FTP
* Once it has been installed or uploaded, simply visit the main plugin list and activate it

== Frequently Asked Questions ==

= Where can I find more extensions? =

Please visit our [extension library](https://theeventscalendar.com/extensions/) to learn about our complete range of extensions for The Events Calendar and its associated plugins.

= What if I experience problems? =

We're always interested in your feedback and our [Help Desk](https://support.theeventscalendar.com/) are the best place to flag any issues. Do note, however, that the degree of support we provide for extensions like this one tends to be very limited.

== Changelog ==

= [1.2.0] 2020-09-?? =

* Add start_margin shortcode parameter to set a start time margin. Example: [tribe-happening-now start_margin="30 minutes"]

= [1.1.0] 2020-03-24 =

* Removing a blue border from styles

= [1.0.0] 2020-03-18 =

* Initial release
