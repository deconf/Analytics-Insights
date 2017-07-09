=== Google Analytics Dashboard for WP (GADWP) ===
Contributors: deconf
Donate link: https://deconf.com/donate/
Tags: analytics,google analytics,google analytics dashboard,google analytics plugin,google analytics widget
Requires at least: 3.5
Tested up to: 4.8
Stable tag: 5.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connects Google Analytics with your WordPress site. Displays stats to help you understand your users and site content on a whole new level!

== Description ==
This Google Analytics for WordPress plugin enables you to track your site using the latest Google Analytics tracking code and allows you to view key Google Analytics stats in your WordPress install.

In addition to a set of general Google Analytics stats, in-depth Page reports and in-depth Post reports allow further segmentation of your analytics data, providing performance details for each post or page from your website.

The Google Analytics tracking code is fully customizable through options and hooks, allowing advanced data collection like custom dimensions and events.    

= Google Analytics Real-Time Stats =

Google Analytics reports, in real-time, in your dashboard screen:

- Real-time number of visitors 
- Real-time acquisition channels
- Real-time traffic sources details 

= Google Analytics Reports =

The Google Analytics reports you need, on your dashboard, in your All Posts and All Pages screens, and on site's frontend:  

- Sessions, organic searches, page views, bounce rate analytics stats
- Locations, pages, referrers, keywords, 404 errors analytics stats
- Traffic channels, social networks, traffic mediums, search engines analytics stats
- Device categories, browsers, operating systems, screen resolutions, mobile brands analytics stats

In addition, you can control who can view specific Google Analytics reports by setting permissions based on user roles.

= Google Analytics Tracking =

Installs the latest Google Analytics tracking code and allows full code customization:

- Universal Google Analytics tracking code
- IP address anonymization
- Enhanced link attribution
- Remarketing, demographics and interests tracking
- Page Speed sampling rate control
- User sampling rate control
- Cross domain tracking
- Exclude user roles from tracking
- Accelerated Mobile Pages (AMP) support for Google Analytics
- Ecommerce support for Google Analytics

Google Analytics Dashboard for WP enables you to easily track events like:
 
- Downloads
- Emails 
- Outbound links
- Affiliate links
- Fragment identifiers
- Telephone
- Page Scrolling Depth
- Custom event categories, actions and labels using annotated HTML elements

With Google Analytics Dashboard for WP you can use custom dimensions to track:

- Authors
- Publication year
- Publication month
- Categories
- Tags
- User engagement

Actions and filters are available for further Google Analytics tracking code customization.

= Google Tag Manager Tracking =

As an alternative to Google Analytics tracking code, you can use Google Tag Manager for tracking:

- Google Tag Manager code
- Data Layer variables: authors, publication year, publication month, categories, tags, user type
- Additional Data Layer variables for page scrolling depth
- Exclude user roles from tracking
- Accelerated Mobile Pages (AMP) support for Google Tag Manager

= Accelerated Mobile Pages (AMP) features =

- Google Tag Manager basic tracking
- Google Analytics basic tracking 
- Automatically removes <em>amp/</em> from Google Analytics tracking page URL
- Scrolling depth tracking
- Custom dimensions tracking
- User sampling rate control
- Form submit tracking
- File downloads tracking
- Affiliate links tracking
- Hashmarks, outbound links, telephones and e-mails tracking
- Custom event categories, actions and labels using annotated HTML elements

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

This is a major update, please read the [release notes](https://deconf.com/google-analytics-dashboard-for-wp-5-0-release-notes/) first.

== Changelog ==

= 5.1.1 =
* New Features:
	* adds non-interaction hit support for AMP
* Enhancements:
	* automatically annotates all AMP HTML elements with the appropriate data when DOM and libxml PHP extension are available
	* renames the access code POST variable
	* exclude scroll page depth events from bounce rate calculation
* Bug Fixes:
	* fixes the switch between metrics on Location report
	* fixes PHP notices on empty reports	
	
= 5.1 =
* Bug Fixes:
	* if tracking is disabled it keeps it that way while upgrading from versions lower than 5.0
	* fixes the undeclared variable gadwpSubmitObject error on submitted forms
	* added deprecation warning for tracking_code method
	* fix network admin menu capabilities, props by [Maxime Culea](https://github.com/MaximeCulea)
* Enhancements:
	* switch to a customized version of GAPI to avoid conflicts with other plugins
	* automatically remove <em>amp/</em> from Google Analytics tracking page URL
	* events tracking is now using <em>data-vars-ga-*</em> attributes instead of <em>data-ga-*</em>
	* increase default report expiration cookie from 7 days to 365 days
* New Features:
	* sampleRate support for web analytics and AMP
	* custom dimensions tracking support for AMP
	* scrolling depth tracking support for AMP
	* form submit tracking support for AMP
	* downloads, affiliate links, hashmarks, outbound links, telephones, e-mails tracking support for AMP
	
= 5.0.1 =
* Enhancements:
	* use site_url() to find the main domain name
	* display the page load time in seconds with two decimals
	* add custom categories, custom actions, and custom labels support for events tracking using <em>data-ga-*</em> attributes; props by [Timo Schneider](https://github.com/tschneid)
	* display the update notice only on automatic updates
* Bug Fixes:
	* prevent the default action of the click event to be triggered if the anchor click event was already prevented by other scripts 
	
= 5.0.0.1 =
* Bug Fixes:
	* filedsObject is missing for the create command
	* display the last accessed report instead of the default one
	
= 5.0 =
* Release notes: [GADWP 5.0](https://deconf.com/google-analytics-dashboard-for-wp-5-0-release-notes/)
* Enhancements:
	* complete redesign of the tracking component
	* AdSense Linking feature was removed since the new linking procedure does not require a special code anymore
	* dropping support for Classic Analytics (ga.js) since all properties were transferred to Universal Analytics
	* events are now tracked using a JS file instead of in-line JavaScript
	* multiple improvements for events tracking accuracy
	* ability to switch between sessions, users and pageviews metrics on reports like Location, Traffic, Searches
	* the GAPI PHP Client was updated to v1.1.8
* New Features:
	* ability to use Google Tag Manager instead of Google Analytics tracking
	* added Accelerated Mobile Pages (AMP) support for Google Analytics and Google Tag Manager tracking
	* users can now switch the position of the tracking codes from head to body through options
	* option to load Ecommerce or Enhanced Ecommerce plug-ins for analytics.js 
	* option to select the placement of the tracking code (head or footer)
	* events tracking for form submit actions
	* events tracking for telephone calls
	* events tracking for page scrolling depth
	* full support for experiments with Optimize

The full changelog is [available here](https://deconf.com/changelog-google-analytics-dashboard-for-wp/).
