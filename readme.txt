=== Analytics Insights for Google Analytics 4 (AIWP) ===
Contributors: deconf
Donate link: https://deconf.com/donate/
Tags: analytics,google analytics,google analytics 4,google analytics dashboard, GA4, google analytics plugin,google analytics tracking code,google analytics widget,wordpress analytics, gainwp
Requires at least: 3.5
Tested up to: 6.3
Stable tag: 5.9.3
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connects Google Analytics 4 with your WordPress site. Displays stats and gives insights to help you understand your users and site content on a whole new level!

== Description ==

Analytics Insights is a WordPress plugin that enables site tracking using the latest Google Analytics 4 tracking code. It allows you to view key Google Analytics stats in your WordPress Dashboard.

= Google Analytics Reports, Stats and Insights =

Analytics Insights displays the reports you need on your dashboard and on the site's frontend. Audience, acquisition, behavior, engagement and real-time stats are all presented as charts within a dedicated dashboard widget.  

In addition to a set of general Google Analytics stats, insights like in-depth Page reports and in-depth Post reports are available.

= Google Analytics Tracking =

The Google Analytics tracking code is fully customizable through options and hooks, allowing advanced data collection like custom dimensions and events.

The plugin installs the latest Google Analytics 4 tracking code. No matter the tracking method you choose, they are all available and customizable: Google Analytics 4 (GA4), Global Site Tag (gtag.js) tracking code, and Accelerated Mobile Pages (AMP) tracking. Accelerated Mobile Pages (AMP) tracking for Google Analytics 4 is also available. 

Advanced features like events tracking and custom dimensions tracking can be enabled with a switch of a button, without the need of any programming skills.  

Old Google Analytics 3 reports are still available. Whenever you need to have a look at your old reports, you can switch back to Google Analytics 3 reports using the option available on <em>Analytics Insights > General Settings</em> screen. 
 
= Analytics Insights on Multisite =

This plugin is fully compatible with multisite network installs. Allows using multiple Google Analytics accounts or using a single Google Analytics account for the entire network.

= Further reading =

* [Clicky Analytics](https://wordpress.org/plugins/clicky-analytics/) - An easy to use and cookie-free analytics service for website tracking.
* [Search Engine Insights](https://wordpress.org/plugins/search-engine-insights/) - The perfect tool for viewing Google Search Console stats in your WordPress dashboard.

== Installation ==

1. Upload the full analytics-insights directory into your wp-content/plugins directory.
2. In WordPress select Plugins from your sidebar menu and activate the Analytics Insights plugin.
3. Open the plugin configuration page, which is located under the Analytics Insights menu.
4. Authorize the plugin to connect to Google Analytics using the Authorize Plugin button.
5. Go back to the plugin configuration page, which is located under Analytics Insights menu to update/set your settings.
6. Go to Analytics Insights -> Tracking Code to configure/enable/disable tracking.

== Frequently Asked Questions == 

= Do I have to insert the Google Analytics tracking code manually? =

No, once the plugin is authorized and a default domain is selected the Google Analytics tracking code is automatically inserted in all webpages.

= Why the numbers on Google Analytics 4 don't match those from Universal Analytics =

To put it simply, different technologies and approaches lead to different results.

= Is Google Analytics 4 tracking supported? =

Yes, you can use Google Analytics 4 properties and/or Universal Analytics properties for tracking, both are supported. 

= Can I only use a Google Analytics 4 property =

Yes, you don't need to create or have an Universal Analytics property. A Google Analytics 4 property is enough for the plugin to be fully functional. 

= How can I suggest a new feature, contribute or report a bug? =

You can submit pull requests, feature requests, and bug reports on [our GitHub repository](https://github.com/deconf/analytics-insights).

= Documentation, Tutorials, and FAQ =

For documentation, tutorials, FAQ and videos check out: [Analytics Insights documentation](https://deconf.com/analytics-insights-for-wordpress/).

== Screenshots ==

1. Google Analytics 4 reports
2. Real-Time Google Analytics 4 
3. Analytics reports per Posts/Pages
4. Google Analytics 4 Geo Map
5. Google Analytics 4 Top Pages, Top Referrers, and Top Searches
6. Google Analytics 4 Traffic Overview
7. Google Analytics 4 Statistics per page on Frontend
8. Google Analytics 4 Cities on the region map
9. Analytics Insights Widget

== Upgrade Notice ==

== Changelog ==

= 5.9.3 (2023.09.12) =
* Bug Fixes:
	* frontend widget displays wrong the same date on analytics chart

= 5.9.2 (2023.09.06) =
* Bug Fixes:
	* text fix on Engagement report
	* add details about the Google Analytics 4 WebStream used for tracking on Tracking Code screen
	
= 5.9.1 (2023.08.08) =
* Bug Fixes:
	* fix for Disable option on Google Analytics tracking code
* Enhancements:
	* replaced Engagement Rate with Organic Search on bottom stats report	

= 5.9 (2023.08.07) =
* Enhancements:
	* switch the default tracking and reporting type to Google Analytics 4
	* remove Universal Analytics (analytics.js) tracking options and code
	* remove Google Analytics 3 tracking options and code
	* multiple fixes for Google Analytics 4 events tracking
		
= 5.8.11 (2023.07.17) =
* Bug Fixes:
	* fixes the authorization endpoint which was causing issues with refresh tokens
	
= 5.8.10 (2023.07.04) =
* Enhancements:
	* Improvements on error reporting system

= 5.8.9 (2023.06.23) =
* Bug Fixes:
	* Multiple JS fixes on Google Analytics item reports 
* Enhancements:
	* Improvements on error reporting system	

= 5.8.8 (2023.05.31) =
* Bug Fixes:
	* Prefix PSR classes and namespace to avoid conflicts
* Enhancements:
	* Google Analytics library update
	
= 5.8.7 (2023.04.21) =
* Enhancements:
	* Google Analytics tracking improvements on Multisite
	
= 5.8.6 (2023.04.21) =
* Enhancements:
	* revoke the Google Analytics refresh token before uninstall
	* error handling improvements

= 5.8.4 (2023.01.02) =
* Enhancements:
	* option to force IPv4 or IPv6 using AIWP_FORCE_IP_RESOLVE
	* error handling improvements

= 5.8.3 (2022.12.07) =
* Enhancements:
	* introducing two additional hooks (aiwp_gtag_output_before and aiwp_gtag_output_after) for Global Site Tag tracking code; props [@jerclarke](https://profiles.wordpress.org/jerclarke/)
	* add full Google Analytics 4 support to AMP Standard, AMP Transitional, and AMP Reader pages  

= 5.8.2 (2022.12.06) =
* Bug Fixes:
	* Fixes a critical bug introduced with some API changes, by replacing pagePathPlusQueryString with pagePath
	
= 5.8.1 (2022.10.20) =
* Bug Fixes:
	* Fixes a bug introduced in 5.8 preventing Google Analytics 4 webstreams retrieval at authorization

= 5.8 (2022.10.18) =
* [Release notes](https://deconf.com/google-analytics-4-support-for-wordpress-amp-pages/)
* Bug Fixes:
	* Fixes multiple validation errors for AMP pages
	* Google Analytics 4 (GA4) realtime reporting fixes
* Enhancements:
	* introducing Google Analytics 4 AMP tracking support (Experimental); you can now use Google Analytics 4 tracking on AMP pages
	
= 5.7.8 (2022.10.06) =
* Bug Fixes:
	* improvements on error reporting; for both Google Analytics EndPoint and Deconf Endpoint 
	
= 5.7.7 (2022.09.27) =
* Bug Fixes:
	* permanent fix for precision loses warning in PHP 8.1
	
= 5.7.6 (2022.09.20) =
* Bug Fixes:
	* "Search ..." placeholder missing for admin reports
	* solves precision loses warning for PHP 8.1 

= 5.7.5 (2022.07.31) =
* Bug Fixes:
	* some namespace fixes
	* code optimization on Google Analytics 4 properties retrieval

= 5.7.4 (2022.07.30) =
* Bug Fixes:
	* fixes on Universal Analytics and Google Analytics 4 properties list, when the number of properties exceeds 200
	
= 5.7.3 (2022.06.18) =
* Bug Fixes:
	* fixes on Google Analytics Client library

= 5.7.2 (2022.06.17) =
* Bug Fixes:
	* switch View functionality wasn't working properly for Google Analytics 4 properties
	* fixes PHP 5.6 compatibility issues

= 5.7.1 (2022.06.01) =
* Bug Fixes:
	* prefix namespaces to avoid autoloading collisions

= 5.6.6 (2022.05.29) =
* Enhancements:
	* simplify the Google Analytics API token revoke method
	* increase the maximum number of Google Analytics 4 accounts to 200
	* make all Google Analytics API calls using quotaUser.  
* Bug Fixes:	
	* fixes Google Analytics 4 total engagement time on bottom stats board 
	
= 5.6.5 (2022.05.03) =
* Enhancements:
	* replace text with dashicons on Posts List to save column space
* Bug Fixes:
	* small CSS fixes
	* Google Analytics 4 tracking code missing when Universal Analytics is missing or is disabled

= 5.6.4 (2022.04.26) =
* Enhancements:
	* API EndPoint v2 upgrade for more stable and reliable requests 
* Bug Fixes:
	* corrected a bug that was causing fatal errors when using custom Google Cloud Console API projects
	* prevent PHP fatal errors by properly storing Google Analytics API errors 
	
= 5.6.3 (2022.04.23) =
* Enhancements:
	* token handling improvements between DeConf EndPoint and Google API Client, to avoid random token resets
	
= 5.6.2 (2022.04.22) =
* Bug Fixes:
	* escape single quotes on Google Analytics custom dimensions to prevent JavaScript Errors
* Enhancements:
	* more accurate errors and error description
	* reset CSS style applied during error display on Google Analytics reports widget
	* handling improvements on Google Analytics APIs errors 
	  	
= 5.6 (2022.04.14) =
* [Release notes](https://deconf.com/analytics-insights-for-google-analytics-4/)
* Enhancements:
	* switching all reports to Google Analytics Reporting v4 for an easier migration to Google Analytics Data API
	* Google Analytics 4 reports are now available
	* Global Site Tag is now the default tracking method on new installations
	* Tracking only with a Google Analytics 4 property is now possible
	* Google Analytics Data API update
	* Google Analytics Reporting API update
	* Google Analytics 4 real-time reports are now available
	* Google Analytics 4 frontend reports UX improvements
	* Google Analytics 3 frontend reports UX improvements
* Bug Fixes:
	* fix frontend per page Google Analytics reports
	* redesign the Real-Time Report for Google Analytics 4  
	* multiple CSS fixes
	* Frontend Reports URI fix
	
= 5.5.6 (2022.03.31) =
* Bug Fixes:
	* Google Analytics 4 events tracking fix
* Security:
	* Google Analytics library update
	
= 5.5.5 (2022.03.05) =
* Bug Fixes:
	* update and multiple fixes for Google Analytics Admin service 
	
= 5.5.4 (2022.03.04) =
* Bug Fixes:
	* 404 error during Google Analytics 4 web datastreams list request
	
= 5.5.3 (2022.02.14) =
* Enhancements:
	* improvements on detecting default GA4 webstream after install
* Bug Fixes:
	* 404 error reports are empty 
	
= 5.5.2 (2022.02.08) =
* Bug Fixes:
	* Lock Selection button missing when there are no GA4 properties defined

= 5.5.1 (2022.02.08) =
* Bug Fixes:
	* properly encode the access token
	
= 5.5.0 (2022.02.07) =
* Enhancements:
 	* Google Analytics library update to v2
	* Google Analytics API Endpoint update to v1
	* automatically get Google Analytics 4 (GA4) webstreams list
	* add Google Analytics 4 (GA4) tracking feature
	* dual tracking is now available; use both Google Analytics 4 (GA4) tracking and Universal analytics (UA) tracking at the same time

* Requirements:
	* minimum requirements changed to PHP 5.6.0 or higher	 

* Bug Fixes:
	* multiple bugfixes for network mode setup
	* fix events tracking in Universal Analytics
		

= 5.4.7 (2021.11.28) =
* Bug Fixes:
	* multiple bugfixes for network mode setup
	* admin page css fixes
	* fixing multiple notices and errors for PHP 8 
		
= 5.4.6 (2021.10.14) =
* Bug Fixes:
	* Maps API key missing quotes
	* use only minified scripts and follow SCRIPT_DEBUG flag while debugging
	* fix invalid links
	* geochart overlapping the metrics icons on mobile devices
* Enhancements:
	* UX updates for the analytics real-time report

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
* [Release notes](https://deconf.com/analytics-insights-for-wordpress/)
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
	* use <em>gadwp_useroptout</em> shortcode to easily generate opt-out buttons and links, [more details](https://deconf.com/google-analytics-gdpr-and-user-data-privacy-compliance)
	* adding <em>gadwp_gtag_commands</em> and <em>gadwp_gtag_script_path</em> hooks to allow further gtag (Global Site Tag) code customization
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
	* GADWP Endpoint improvements
	* Error reporting improvements
	* introducing the gadwp_maps_api_key filter
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
