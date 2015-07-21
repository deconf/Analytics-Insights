=== Google Analytics Dashboard for WP ===
Contributors: deconf
Donate link: https://deconf.com/donate/
Tags: google,analytics,google analytics,dashboard,analytics dashboard,google analytics dashboard,google analytics plugin,google analytics widget,tracking,universal google analytics,realtime,multisite,gadwp
Requires at least: 3.5
Tested up to: 4.2.2
Stable tag: 4.8.1.3
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
- Locations, pages, referrers, keywords analytics reports
- Traffic channels, social networks, traffic mediums, search engines analytics reports
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
- Categories
- User engagement

= Google Analytics Dashboard for WP on Multisite =

This plugin is fully compatible with multisite network installs, allowing three setup modes:

- Mode 1: network activated using multiple Google Analytics accounts
- Mode 2: network activated using a single Google Analytics account
- Mode 3: network deactivated using multiple Google Analytics accounts

> <strong>Google Analytics Dashboard for WP on GitHub</strong><br>
> You can submit feature requests or bugs on [Google Analytics Dashboard for WP](https://github.com/deconf/Google-Analytics-Dashboard-for-WP) repository.

= Further reading =

* Homepage of [Google Analytics Dashboard](https://deconf.com/google-analytics-dashboard-wordpress/) for WordPress
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

= How can I make suggest a new feature, contribute or report a bug? =

You can submit pull requests, feature requests, translations or bug reports on [our GitHub repository](https://github.com/deconf/Google-Analytics-Dashboard-for-WP).

= Documentation, Tutorials and FAQ =

For documentation, tutorials, FAQ and videos check out: [Google Analytics Dashboard Documentation](https://deconf.com/google-analytics-dashboard-wordpress/).

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

== License ==

Google Analytics Dashboard for WP it's released under the GPLv2, you can use it free of charge on your personal or commercial website.

== Changelog ==

= 4.8.1.3 =
- Enhancement: throw an error in the General Settings screen (even if it's not a blocker) to acknowledge the user
- Enhancement: item reports improvements, more suggestive error messages in item reports, hide unnecessary item reports divs on critical errors
- Bug Fix: truncate long translation strings in google analytics reports
- Bug Fix: rename query args to something more unique to avoid conflicts with other plugins
- Bug Fix: Italian translation small fix
- Bug Fix: add text domain and domain path in plugin's header; switch to default text domain
- Bug Fix: avoid empty item reports while the URI ends with a slash and a Default Page is set in View settings (requires re-authorization)

= 4.8.1.2 =
- Bug Fix: fixes automatic update switched on after each update
- Bug Fix: add missing domain to an i18n string
- Bug Fix: small CSS fix in item-reports.css
- Bug Fix: using PHP to get web pages URIs instead of JavaScript
- Enhancement: Italian translation updated
- Enhancement: set the cookies to expire in 7 days

= 4.8.1.1 =
- Bug Fix: headers already sent warning on main dashboard widget
- Bug Fix: plural form fix for a string in translation PO files 

= 4.8.1 =
- Bug Fix: add % suffix to bouncerate item reports
- Bug Fix: add query string support to frontend item reports
- Bug Fix: make the main menu translatable
- Bug Fix: PHP notice while no View is assigned to a new network site  
- Enhancement: French translation updated
- Enhancement: Romanian translation updated
- Enhancement: display an admin notice after manual and automatic updates
- Enhancement: small I18N tweaks and POT file update
- Enhancement: introducing last 14 days range in items reports
- Enhancement: introducing One Year and Three Years range for all google analytics reports
- Enhancement: set the last selected report and date range as default for subsequent requests 

= 4.8 =
- Enhancement: optimize the number of ajax requests
- Enhancement: new versioning standard for a better management of automatic updates (M.M.m.u) 
- Enhancement: JavaScript code cleanup and optimization
- Enhancement: memory usage optimization
- Enhancement: small assets fixes, UX improvements, props by [Adrian Pop](https://github.com/adipop)
- Enhancement: introducing google analytics reports for all frontend web pages (new feature)
- Enhancement: gadwp_frontenditem_uri filter to allow URI corrections for frontend item reports
- Bug Fix: avoid double encoding of UTF-8 URIs
- Bug Fix: 100% number formatting issue on bounce rate report

= 4.7.5 =
- Bug Fix: html encode single quotes for custom dimensions 

= 4.7.4 =
- Bug Fix: Settings action unavailable on Installed Plugins screen
- Enhancement: German translation updated
- Enhancement: Romanian translation updated
- Enhancement: Dutch translation updated

= 4.7.3 =
- Enhancement: Russian translation
- Enhancement: Romanian translation
- Enhancement: Hungarian translation updated
- Enhancement: UX improvements, props by [Adrian Pop](https://github.com/adipop)
- Enhancement: settings page cleanup

= 4.7.2 =
- Enhancement: Czech translation
- Bug Fix: apply tooltips only on GADWP widget
- Bug Fix: use a custom data attribute instead of title to attach the tooltip

= 4.7.1 =
- Enhancement: Italian translation updated
- Bug Fix: use url-encoding for API filters to avoid generating invalid parameters
- Bug Fix: cache reports for pages and posts with queries in URI
- Bug Fix: avoid double encoding while doing Google Analytics API requests

= 4.7 =
- Enhancement: Dutch translation updated
- Enhancement: using wp_get_current_user() to check users' roles
- Enhancement: fit longer titles in backend item reports widget
- Enhancement: disable the drop-down select list while a single Google Analytics View is available
- Bug Fix: views missing on huge google analytics accounts
- Bug Fix: unable to add new widgets on frontend

= 4.6 =
- Enhancement: Italian translation updated
- Enhancement: Japanese translation updated
- Enhancement: Portuguese (Brazil) translation updated
- Enhancement: introducing a manager class to keep track of all instances and their references
- Enhancement: push the google analytics tracking code at the end of head section
- Enhancement: better support for remove_action and wp_dequeue_script
- Enhancement: Ajax calls optimization
- Bug Fix: loading bar issues while not all frontend features are enabled
- Bug Fix: in-existent script enqueued in frontend component
- Bug Fix: i18n improvements, props by [Hinaloe](https://github.com/hinaloe)
- Bug Fix: PHP notice when using bbPress
- Bug Fix: in-existent script enqueued in frontend component
- Bug Fix: improved URI detection in Pages and Posts backend reports
- Bug Fix: color picker and settings page tabs not working when per posts/pages reports are disabled 

= 4.5.1 =
- Bug Fix: analytics icons added to all custom columns
- Bug Fix: unable to switch tabs in plugin options for some languages

= 4.5 =
- Requirements: WordPress 3.5 and above
- Enhancement: automatic updates for minor versions (security and maintenance releases)
- Enhancement: improvements while enqueuing styles & scripts
- Enhancement: google analytics reports per post in Post List (new feature)
- Enhancement: google analytics reports per page in Page List (new feature)
- Enhancement: gadwp_backenditem_uri allows URI corrections for backend item reports
- Enhancement: option to enable/disable the custom dashboard widget
- Enhancement: Japanese translation
- Enhancement: Dutch translation updated
- Enhancement: Portuguese (Brazil) translation
- Enhancement: UI improvements, props by [Paal Joachim Romdahl](https://github.com/paaljoachim)
- Bug Fix: Arabic translation not loading properly
- Bug Fix: initialize time-shift for all Google Analytics API calls
- Bug Fix: include Google Analytics API library only when a API call is made
- Bug Fix: keep the percentage numeric while anonymizing data
- Bug Fix: add PHP 5.3 as a requirement when forcing IPv4
- Bug Fix: typo fix, props by [Andrew Minion](https://github.com/macbookandrew)

= 4.4.7 =
- Bug Fix: fatal error in plugin settings screen, under certain circumstances
- Bug Fix: fix refresh interval for google analytics backend reports
 
= 4.4.6 =
- Bug Fix: maintain compatibility with WordPress 3.0+

= 4.4.5 =
- Enhancement: Google Analytics API requests optimization 
- Enhancement: server responses improvements
- Enhancement: filter data through query options
- Bug Fix: additional checks before displaying an error
- Bug Fix: wrong error displayed on IE
- Bug Fix: set correct Content-Type before sending responses

= 4.4.4 =
- Bug Fix: end tag missing on error message
- Bug Fix: additional checks before making a View list request
- Bug Fix: avoid deleting errors while clearing the cache
- Bug Fix: PHP notices fix for some requests
- Bug Fix: PHP notices fix when calling ob_clean on an empty buffer
- Bug Fix: frontend stats not responsive 
- Enhancement: handle some additional Google Analytics API errors
- Enhancement: set totals to zero when anonymize stats is enabled
- Enhancement: auto-cleanup removed; all transients have static identifiers now
- Enhancement: dump error details to JavaScript Console and throw an alert on invalid responses
- Enhancement: Italian translation

= 4.4.3 =
- Enhancement: further optimization on google analytics api queries
- Enhancement: less error prone while running JavaScript
- Enhancement: Google Analytics API errors handling improvement
- Enhancement: added GADWP_IP_VERSION constant to force a particular Internet Protocol version when needed  
- Enhancement: run the clean-up method only in settings screen
- Enhancement: added tabs to Tracking Code page
- Enhancement: added a new menu item for errors and debugging
- Enhancement: error alerts for Error & Debug sub-menu
- Enhancement: disable file cache functionality in GAPI library
- Enhancement: if cURL is not available fall-back to HTTP streams; cURL is no longer a requirement
- Enhancement: wp_get_sites limit can now be adjusted through gadwp_sites_limit filter

= 4.4.2 =
- Bug Fix: additional check for frontend widget

= 4.4.1 =
- Bug Fix: frontend widget nonce issue while using a cache plugin
- Bug Fix: clear the buffer immediately before returning AJAX response
- Bug Fix: add full-path while loading autoload.php

= 4.4 =
- Bug Fix: frontend reports and widget are not responsive
- Bug Fix: random notices for today and yesterday reports
- Enhancement: Italian translation
- Enhancement: admin widget responsive design and optimizations
- Enhancement: added acquisition channel reports
- Enhancement: added acquisition social networks reports
- Enhancement: added acquisition search engines reports
- Enhancement: new location report and countries/cities list table
- Enhancement: new pages report (removed top 24 limit)
- Enhancement: new searches report (removed top 24 limit)
- Enhancement: new referrers report (removed top 24 limit)
- Enhancement: frontend, per page reports (removed top 24 limit)
- Enhancement: added campaigns in real-time report/screen
- Enhancement: asynchronous reports loading and speed improvements
- Enhancement: code optimization for all frontend and backend features
- Enhancement: finished the error standardization process; easier debugging
- Enhancement: Google Analytics API library update

= 4.3.11 =
- Bug Fix: improvements on QPS management
- Bug Fix: fall-back to world map when a wrong country code is entered
- Bug Fix: removed double transient call on successful google analytics authorization
- Bug Fix: PHP warning when authorizing without a Google Analytics account
- Bug Fix: switch back to initial blog after completing an error clean up in multisite mode
- Enhancement: clear all errors on version change
- Enhancement: grid lines are now transparent
- Enhancement: responsive design improvements for admin widget
- Enhancement: add css and js version number

= 4.3.10 =
- Bug Fix: removed the PHP debugging log for frontend queries
- Enhancement: adding library conflict notice in General Settings
- Enhancement: better handling of Google Analytics API errors
- Enhancement: added an error when user enters the Google Analytics Tracking ID instead of an access code    
- Enhancement: improved error reporting for frontend stats and widgets

= 4.3.9 =
- Enhancement: marking classes as final
- Enhancement: re-design of the google analytics frontend widget
- Enhancement: responsive design for frontend widget
- Enhancement: responsive design for page reports
- Enhancement: error codes standardization
- Enhancement: frontend stats are now able to display the error number
- Bug Fix: load jsapi only when the frontend widget is active
- Bug Fix: javascript errors while resizing window
- Bug Fix: real-time component not loading properly in certain conditions
- Bug Fix: stop retrying when a daily limit has exceeded

= 4.3.8 =
- Enhancement: frontend component re-design
- Enhancement: optimizing frontend component to improve page loading speed
- Enhancement: optimizing frontend component to minimize GAPI requests  
- Enhancement: loading jsapi using wp-enqueue-script
- Enhancement: better escaping to avoid javascript errors

= 4.3.7 =
- Enhancement: option to exclude Super Administrator tracking for the entire network
- Bug Fix: warning during Network Activate
- Bug Fix: track affiliates while downloads, mailto and outbound links tracking is disabled
- Bug Fix: avoid reload loops for realtime component
- Enhancement: track fragment identifiers, hashmarks (#) in URI links
- Enhancement: improving i18n
- Enhancement: moving bounce-rate option to Advanced Tracking 

= 4.3.6 =
- Bug Fix: clear cache not working properly
- Bug Fix: error correction in Spanish localization file

= 4.3.5 =
- Bug Fix: authors custom dimension not working for pages
- Bug Fix: outbound detection
- Bug Fix: fixed unicode issue
- Bug Fix: properly display cities with same name from different regions
- Enhancement: removed image extensions from default download filter
- Enhancement: add day of week to dashboard dates
- Enhancement: Arabic translation
- Bug Fix: multiple fixes for real time reports

= 4.3.4 =
- Enhancement: ga_dash_addtrackingcode action hook
- Enhancement: French translation
- Enhancement: cross domain tracking support
- Enhancement: Google Analytics custom definitions, using custom dimensions to track authors, years, categories and engagement
- Enhancement: support for affiliate links tracking 
- Enhancement: never treat downloads as outbound links

= 4.3.3 =
- Enhancement: added Polish translation
- Bug Fix: missing icon and wrong link in GADWP settings
- Enhancement: moving Page Speed SR to top, to avoid some confusions
- Enhancement: added plugin version to debugging data

= v4.3.2 =
- Bug Fix: fixes for multisite with a single Google Analytics Account
- Bug Fix: notice while displaying searches report
- Bug Fix: downloads regex update
- Bug Fix: always exclude outbound links from bounce-rate calculation 
- Enhancement: Adsense account linking
- Enhancement: adjust page speed sample rate
- Enhancement: exclude event tracking from bounce-rate calculation for downloads and mailto
- Enhancement: reset downloads filters to default when empty
- deprecate: classic google analytics

= v4.3.1 =
- Bug Fix: link on top referrers list not working
- allowing today as default stats
- Bug Fix: google analytics profiles refresh issue
- Enhancement: remove table borders on frontend widget
- Bug Fix: multiple fixes for network mode
- updated GAPI libarry
- using autloader for PHP 5.3.0 and greater
- security improvements
- tracking code update

= v4.3 =
- responsive google analytics charts
- single authorization for multisite
- Bug Fix: SERVER_ADDR PHP notice
- Bug Fix: notices on admin dashboard
- additional data validation and sanitizing
- Bug Fix: realtime switching profile functionality
- multisite: blog's cleanup on uninstall
- deprecating custom tracking code

= v4.2.21 =
- added hungarian translation
- added italian translation
- Bug Fix: escaping characters in google analytics charts
- new filter on frontend widget
- cache timeout adjustments
- description update
- Bug Fix: fatal error on invalid_grant
- added timestamp on last error  
 
= v4.2.20 =
- Bug Fix: russian country map is not working
- Bug Fix: only administrator can see google analytics reports while using a cache plugin
- Bug Fix: division by zero on frontend widget
- added german translation
- added spanish translation

= v4.2.19 =
- added portuguese translation
- frontend widget CSS fix
- added remarketing, demographics and interests tracking support for Google Analytics tracking code
- universal google analytics is now the default tracking method
- CSS fix for dashboard widgets

= v4.2.18 =
- translations bugfix
- menu display tweaks
- removed debugging log file
- permissions fix for WPMU
- URI fix for frontend filters (top pages and top searches)
- exclude google analytics reports in preview mode
- updated download filters
- by default administrators are not excluded from tracking
- bugfix for refresh_profiles() method 

= v4.2.17 =
- fixed on/off toggle bug for frontend settings

= v4.2.16 =
- properly nonce verification

= v4.2.15 =
- force token reset procedure when failing to authenticate with Google Analytics
- deleting refresh token transient on uninstall
- trying to catch all possible exceptions thrown by Google Analytics API
- no token reset on network connection errors
- fixed screen options bug
- added capability to select each role for access levels and exclude tracking
- added links to top pages table
- added links to top referrers table
- added option to display Chart&Totals/Chart/Totals to frontend widget
- retrieving realtime analytics using wp ajax
- switching to default jquery-ui-tooltip wordpress library
- fixed settings link not displayed in plugins page

= v4.2.14 =
- bugfix for error reporting
- custom API credential are now saved before starting the authorization procedure
- hiding additional info in log data

= v4.2.13 =
- bugfix for I18n
- implemented a basic debugging log
- CURL required error messages
- option to hide all other google analytics properties/views from Select Domain list
- added periodical _transient_timeout cleanup
- fixed bug in property refresh method
- disable hide option when none or a single google analytics property is available
- better handling errors when a user authorizes without actually having a Google Analytics account
- fixed bug in token revoke method
- fixed bug in token refresh method
- additional validations on frontend features


= v4.2.12 =
- refreshing charts when the time interval changes
- saving last selection
- minimizing requests by using same query serial for frontend and backend queries
- fixed bug in dashboard's switch options for non-admins
- fixed Notice: Undefined index: ga_dash_frontend_stats for new installs
- no more queries if there is no token
 
= v4.2.11 =
- added support for google analytics enhanced link attribution
- bugfix on google analytics classic tracking code

= v4.2.10 =
- using predefined color for pie charts 

= v4.2.9b =
- refresh token handles additional uncaught exceptions
- partially resolved conflicts with other google analytics plugins

= v4.2.8b =
- checkboxes replaced with switch on/off buttons
- multiple bug fixes

= v4.2.7b =
- plugin code rewritten from scratch
- new enhanced, user friendly interface
- added custom tracking code
- added a new frontend widget
- cache improvements, loading speeds optimization, less GAPI queries
- responsive design

= v4.2.6 =
- google analytics api token refresh bugfix

= v4.2.5 =
- corrected wrong google analytics stats reporting

= v4.2.4 =
- css fixes
- clear cache fixes

= v4.2.3 =
- time zone fixes
- hourly reports for yesterday and today
- small css fix on frontend

= v4.2.2 =
- small fixes and update

= v4.2.1 =
- fixed Domain and Subdomains tracking code for Universal Google Analytics 

= v4.2 =
- added google analytics real-time support
- new date ranges: Today, Yesterday, Last 30 Days and Last 90 Days 

= v4.1.5 =
- fixed "lightblack" color issue, on geomap, on light theme
- added cursor:pointer property to class .gabutton

= v4.1.4 =
- added access level option to backend google analytics reports 
- added access level option to frontend google analytics reports
- new feature for Geo Map allowing local websites to display cities, instead of countries, on a regional map
- fixed colors for Geo Chart containing world visits by country

= v4.1.3 =
- solved WooCommerce conflict using .note class
- added Google Analytics tracking exclusion based on user level access

= v4.1.1 =
- added missing files
- other minor fixes

= v4.1 =
- added Google Analyticsevent tracking feature: track downloads, track emails, track outbound links
- remove trailing comma for IE8 compatibility

= v4.0.4 =
- a better way to retrieve domains and subdomains from Google Analytics profiles
- remove escaping slashes generating errors on table display

= v4.0.3 =
- improvements on google analytics tracking code
- redundant variable for default domain name
- fix for "cannot redeclare class URI_Template_Parser" error
- added Settings to plugins page
- modified Google Analytics Profiles timeouts

= v4.0.2 =
- minimize Google Analytics API requests
- new warnings available on Admin Option Page
- avoid any unnecessary profile list update
- avoid errors output for regular users while adding the google analytics tracking code

= v4.0.1 =
- fixed some 'Undefined index' notices
- cache fix to decrease number of Google Analytics API requests

= v4.0 =

* simplified authorization process for beginners
* advanced users can use their own Google Analytics API Project

= v3.5.3 =

* translation fix, textdomain ga-dash everywhere

= v3.5.2 =

* some small javascript fixes for google analytics tracking code

= v3.5.1 =

* renamed function get_main_domain() to ga_dash_get_main_domain

= v3.5 =

* small bug fix for multiple TLD domains tracking and domain with subdomains tracking
* added universal google analytics support (you can track visits using analytics.js or using ga.js)

= v3.4.1 =

* switch to domain names instead of google analytics profile names on select lists
* added is_front_page() check to avoid problems in Woocommerce

= v3.4 =

* i8n improvements
* RTL improvements
* usability and accessibility improvements
* added google analytics tracking features

= v3.3.3 =

* a better way to determine temp dir for google analytics api cache

= v3.3.3 =

* added error handles 
* added quick support buttons
* added Sticky Notes
* switched from Visits to Views vs UniqueViews on frontpage
* fixed select lists issues after implementing translation, fixed frontend default google analytics profile
* added frontpage per article statistics

= v3.2 =

* added multilingual support
* small bug fix when locking admins to a single google analytics profile

= v3.1 =

* added Traffic Overview in Pie Charts
* added lock google analytics profile feature for Admins
* code optimization

= v3.0 =

* added Geo Map, sortable tables
* minor fixes

= v2.5 =

* added cache feature
* simplifying google analytics api authorizing process

= v2.0 =

* added light theme
* added top pages google analytics report
* added top searches google analytics report
* added top referrers google analytics report
* added display settings

= v1.6 =

* admins can jail access level to a single google analytics profile

= v1.5 =

* added multi-website support
* table ids and profile names are now automatically retrived from google analytics

= v1.4 =

* added View access levels (be caution, ex: if level is set to "Authors" than all editors and authors will have view access)
* fixed menu display issue

= v1.3 =

* switch to Google API PHP Client 0.6.1
* resolved some Google Analytics Dashboard conflicts

= v1.2.1 =

* minor fixes on google analytics api
* added video tutorials

= v1.2 =

* minor fixes

= v1.0 =

* first release
