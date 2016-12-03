=== Google Analytics Dashboard for WP ===
Contributors: deconf
Donate link: https://deconf.com/donate/
Tags: google,analytics,google analytics,dashboard,analytics dashboard,google analytics dashboard,google analytics plugin,google analytics widget,tracking,universal google analytics,realtime,multisite,gadwp
Requires at least: 3.5
Tested up to: 4.7
Stable tag: 4.9.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays Google Analytics reports in your WordPress Dashboard. Inserts the latest Google Analytics tracking code in your pages.

== Description ==
This Google Analytics for WordPress plugin enables you to track your site using the latest Google Analytics tracking code and allows you to view key Google Analytics reports in your WordPress install.

In addition to a set of general Google Analytics reports, in-depth Page reports and in-depth Post reports allow further segmentation of your analytics data, providing performance details for each post or page from your website.

The Google Analytics tracking code is fully customizable through options and hooks, allowing advanced data collection using custom dimensions and events.    

= Google Analytics Real-Time =

Google Analytics reports, in real-time, in your dashboard screen:

- Real-time number of visitors 
- Real-time acquisition channels
- Real-time traffic sources details 

= Google Analytics Reports =

The Google Analytics reports you need, on your dashboard, in your All Posts and All Pages screens, and on site's frontend:  

- Sessions, organic searches, page views, bounce rate analytics reports
- Locations, pages, referrers, keywords, 404 errors analytics reports
- Traffic channels, social networks, traffic mediums, search engines analytics reports
- Device categories, browsers, operating systems, screen resolutions, mobile brands analytics reports 
- User access control over analytics reports

= Google Analytics Basic Tracking =

Installs the latest Google Analytics tracking code and allows full code customization:

- Switch between Universal Google Analytics and Classic Google Analytics code
- IP address anonymization
- Enhanced link attribution
- Remarketing, demographics and interests tracking
- Google AdSense linking
- Page Speed sampling rate control
- Cross domain tracking
- Exclude user roles from tracking

= Google Analytics Event Tracking =

Google Analytics Dashboard for WP enables you to easily track events like:
 
- Downloads
- Emails 
- Outbound links
- Affiliate links
- Fragment identifiers

= Google Analytics Custom Dimensions =

With Google Analytics Dashboard for WP you can use custom dimensions to track:

- Authors
- Publication year
- Publication month
- Categories
- Tags
- User engagement

= Google Analytics Dashboard for WP on Multisite =

This plugin is fully compatible with multisite network installs, allowing three setup modes:

- Mode 1: network activated using multiple Google Analytics accounts
- Mode 2: network activated using a single Google Analytics account
- Mode 3: network deactivated using multiple Google Analytics accounts

> <strong>Google Analytics Dashboard for WP on GitHub</strong><br>
> You can submit feature requests or bugs on [Google Analytics Dashboard for WP](https://github.com/deconf/Google-Analytics-Dashboard-for-WP) repository.

= Further reading =

* Homepage of [Google Analytics Dashboard for WP](https://deconf.com/google-analytics-dashboard-wordpress/)
* Other [WordPress Plugins](https://deconf.com/wordpress/) by same author
* [Google Analytics | Partners](https://www.google.com/analytics/partners/company/5127525902581760/gadp/5629499534213120/app/5707702298738688/listing/5639274879778816) Gallery

== Installation ==

1. Upload the full google-analytics-dashboard-for-wp directory into your wp-content/plugins directory.
2. In WordPress select Plugins from your sidebar menu and activate the Google Analytics Dashboard for WP plugin.
3. Open the plugin configuration page, which is located under Google Analytics menu.
4. Authorize the plugin to connect to Google Analytics using the Authorize Plugin button.
5. Go back to the plugin configuration page, which is located under Google Analytics menu to update/set your settings.
6. Go to Google Analytics -> Tracking Code to configure/enable/disable tracking.

== Frequently Asked Questions == 

= Do I have to insert the Google Analytics tracking code manually? =

No, once the plugin is authorized and a default domain is selected the Google Analytics tracking code is automatically inserted in all webpages.

= Some settings are missing in the video tutorial =

We are constantly improving Google Analytics Dashboard for WP, sometimes the video tutorial may be a little outdated.

= How can I suggest a new feature, contribute or report a bug? =

You can submit pull requests, feature requests and bug reports on [our GitHub repository](https://github.com/deconf/Google-Analytics-Dashboard-for-WP).

= Documentation, Tutorials and FAQ =

For documentation, tutorials, FAQ and videos check out: [Google Analytics Dashboard for WP documentation](https://deconf.com/google-analytics-dashboard-wordpress/).

== Screenshots ==

1. Google Analytics Dashboard for WP Blue Color
2. Google Analytics Dashboard for WP Real-Time
3. Google Analytics Dashboard for WP reports per Posts/Pages
4. Google Analytics Dashboard for WP Geo Map
5. Google Analytics Dashboard for WP Top Pages, Top Referrers and Top Searches
6. Google Analytics Dashboard for WP Traffic Overview
7. Google Analytics Dashboard for WP statistics per page on Frontend
8. Google Analytics Dashboard for WP cities on region map
9. Google Analytics Dashboard for WP Widget

== Localization ==

You can translate Google Analytics Dashboard for WP on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/google-analytics-dashboard-for-wp).

== License ==

Google Analytics Dashboard for WP it's released under the GPLv2, you can use it free of charge on your personal or commercial website.

== Upgrade Notice ==

== Changelog ==

= 4.9.4 =
* Enhancements: 
	* always load analytics.js over SSL
	* gadwp_backenditem_uri filter passes post ID as an additional variable
	* option to use a Google Maps API key for the Locations report
* New Features:
	* a new year-month dimension is now available, to allow further segmentation of the most successful publication years, by month; props by [Antoine Girard](https://github.com/thetoine)
	* a new 404 Errors report designed to analyze and easily identify the source of 404 errors
* Bug Fixes:
	* switch to get_sites() while maintaining compatibility with older WP installs
	* fix for multisite installs, Properties/Views Settings list was not properly displayed on PHP7
	* prevent autoloading of reports' cache entries; props by [Alex Bradaric](https://github.com/bradaric)

The full changelog is [available here](https://deconf.com/changelog-google-analytics-dashboard-for-wp/).
