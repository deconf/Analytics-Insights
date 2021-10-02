=== Analytics Insights - Google Analytics, AMP Analytics, Stats ===
Contributors: deconf
Donate link: https://deconf.com/donate/
Tags: google analytics dashboard, google analytics widget, WordPress analytics, google analytics plugin, global site tag
Requires at least: 3.5
Tested up to: 5.8
Stable tag: 5.4.5
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connects Google Analytics with your WordPress site. Displays stats and gives insights to help you understand your users and site content on a whole new level!

== Description ==

Analytics Insights is a WordPress plugin that enables site tracking using the latest Google Analytics tracking code. It allows you to view key Google Analytics stats in your WordPress Dashboard.

= Google Analytics Reports, Stats and Insights =

Analytics Insights displays the reports you need on your dashboard and on the site's frontend. Audience, acquisition, behavior, and real-time stats are all presented as charts within a dedicated dashboard widget.  

In addition to a set of general Google Analytics stats, insights like in-depth Page reports and in-depth Post reports are available.

= Google Analytics Tracking =

The Google Analytics tracking code is fully customizable through options and hooks, allowing advanced data collection like custom dimensions and events.

The plugin installs the latest Google Analytics tracking code. No matter the tracking method you choose, they are all available and customizable: Universal Google Analytics (analytics.js) tracking code, Global Site Tag (gtag.js) tracking code, and Accelerated Mobile Pages (AMP) tracking.

Advanced features like events tracking and custom dimensions tracking can be enabled with a switch of a button, without the need of any programming skills.  

= Analytics Insights on Multisite =

This plugin is fully compatible with multisite network installs. Allows using multiple Google Analytics accounts or using a single Google Analytics account for the entire network.

== Installation ==

1. Upload the full analytics-insights directory into your wp-content/plugins directory.
2. In WordPress select Plugins from your sidebar menu and activate the Analytics Insights plugin.
3. Open the plugin configuration page, which is located under Analytics Insights menu.
4. Authorize the plugin to connect to Google Analytics using the Authorize Plugin button.
5. Go back to the plugin configuration page, which is located under Analytics Insights menu to update/set your settings.
6. Go to Analytics Insights -> Tracking Code to configure/enable/disable tracking.

== Frequently Asked Questions == 

= Do I have to insert the Google Analytics tracking code manually? =

No, once the plugin is authorized and a default domain is selected the Google Analytics tracking code is automatically inserted in all webpages.

= How can I suggest a new feature, contribute or report a bug? =

You can submit pull requests, feature requests, and bug reports on [our GitHub repository](https://github.com/deconf/analytics-insights).

= Documentation, Tutorials, and FAQ =

For documentation, tutorials, FAQ and videos check out: [Analytics Insights documentation](https://deconf.com/analytics-insights-for-wordpress/).

== Screenshots ==

1. Blue Color
2. Real-Time Analytics
3. Analytics reports per Posts/Pages
4. Geo Map
5. Top Pages, Top Referrers, and Top Searches
6. Traffic Overview
7. Statistics per page on Frontend
8. Cities on the region map
9. Analytics Insights Widget

== Upgrade Notice ==

== Changelog ==

[AIWP v5.4 release notes](https://deconf.com/analytics-insights-for-wordpress/)

= 5.4.5 (2021.09.28) =
* Bug Fixes:
	* Maps API key missing quotes
	* use only minified scripts and follow SCRIPT_DEBUG flag while debugging
	* fix invalid links
	* geochart overlapping the metrics icons on mobile devices

= 5.4.4 (2021.09.24) =
* Enhancements:
	* add AMP Analytics support for gtag.js (Global Site Tag)
	* add events support to Global Site Tag tracking on AMP pages; including scroll depth and HTML attributes triggers
	* add custom dimensions support to Global Site Tag tracking on AMP pages 

= 5.4.3 (2021.09.16) =
* Bug Fixes:
	* fixes javascript error when rendering anonymized charts on frontend analytics widget
* Enhancements:
	* readme.txt and assets update

= 5.4.2 (2021.09.10) =
* Bug Fixes:
	* fixes uaid issue for universal analytics

= 5.4.1 (2021.09.09) =
* Security Fixes:
	* sanitizing, escaping, and validating additional data
* Enhancements:
	* new translations for multiple languages
	* replacing <strong>Cheating huh?<strong> with a more helpful feedback	
* Bug Fixes:
	* fixes multisite/network mode random token resets 

= 5.4 (2021.08.30) =
* Enhancements:
	* capability to filter and search within displayed tables
	* automatically authorize users with Google Analytics, without copy/pasting the access codes
	* improvements on UX for reports and stats switching (no more charts reloading)
	* removed automatic updates
	* settings page optimization
	* refactoring code on the GADWP settings page
	* removed a duplicate option in Tracking Code 
* Bug Fixes:	
	* CSS fix for on/off buttons on GADWP settings page
	
= 5.3.2 =
* Bug Fixes:	
	* fixes for user opt-out feature 
* Enhancements: 
	* use <em>aiwp_useroptout</em> shortcode to easily generate opt-out buttons and links, [more details](https://deconf.com/google-analytics-gdpr-and-user-data-privacy-compliance)
	* adding <em>aiwp_gtag_commands</em> and <em>aiwp_gtag_script_path</em> hooks to allow further gtag (Global Site Tag) code customization
	* adds opt-out and DNT support for Google Tag Manager	
	
= 5.3.1.1 =
* Bug Fixes:	
	* avoid universal analytics tracking issues by not clearing the profiles list on automatic token resets

= 5.3.1 =
* Bug Fixes:	
	* fixing PHP notices on frontend stats when upgrading from a version lower than v4.8.0.1   

= 5.3 =
* Enhancements: 
	* adds full support for Global Site Tag (gtag.js)
	* remove Scroll Depth functionality, since this is now available as a trigger on Google Tag Manager
	* adds custom dimensions support for AMP pages with Google Tag Manager tracking
	* adds support for button submits
* Bug Fixes:	
	* form submit analytics events were not following the non-interaction settings   
	
= 5.2.3.1 =
* Bug Fixes:	
	* fixing a small reporting issue on dashboard stats
	
= 5.2.3 =
* Enhancements:
	* add Google Analytics user opt-out support
	* add option to exclude Google Analytics tracking for users sending the <em>Do Not Track</em> header
	* add System tab to Errors & Debug screen
	* check to avoid using a redeemed Google Analytics access code
* Bug Fixes:	
	* remove a debugging message for analytics reports
	* cURL options were overwritten during regular Analytics API calls	

= 5.2.2 =
* Enhancements:  
	* more informative alerts and suggestions on the authorization screen
	* disable autocomplete for the access code input field to avoid reuse of the same unique Google Analytics authorization code
	* AIWP Endpoint improvements
	* Error reporting improvements
	* introducing the aiwp_maps_api_key filter
* Bug Fixes:	
	* use the theme color palette for the frontend stats widget 	 

= 5.2.1 =
* Enhancements:  
	* avoid submitting empty error reports from Google Analytics API
* Bug Fixes:	
	* fixes a bug for custom PHP cURL options 
	
= 5.2 =
* Enhancements:  
	* improvements on Google Analytics API exponential backoff system
	* introduces a new authentication method with endpoints for Google Analytics
	* multiple updates of plugin's options
	* code cleanup
	* improvements on error reporting system
	* option to report errors to developer
	* move the upgrade notice from the Dashboard to Insights Analytics settings page
	* enable PHP cURL proxy support using WordPress settings, props by [Joe Hobson](https://github.com/joehobson)
	* hide unusable options based on Analytics Insights settings 
* Bug Fixes:	
	* some thrown errors were not displayed on Errors & Debug screen
	* analytics icon disappears from post list after quick edit, props by [karex](https://github.com/karex)
	* fix for inline SVG links, props by [Andrew Minion](https://github.com/macbookandrew)
	* fixes a bug on affiliate events tracking

The full changelog is [available here](https://deconf.com/changelog-analytics-insights/).
