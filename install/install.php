<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

class AIWP_Install {

	public static function install() {

			$options = array();
			$options['client_id'] = '';
			$options['client_secret'] = '';
			$options['access_front'][] = 'administrator';
			$options['access_back'][] = 'administrator';
			$options['webstream_jail'] = '';
			$options['theme_color'] = '#1e73be';
			$options['switch_profile'] = 0;
			$options['tracking_type'] = 'ga4tracking';
			$options['ga_anonymize_ip'] = 0;
			$options['user_api'] = 0;
			$options['ga_event_tracking'] = 0;
			$options['ga_event_downloads'] = 'zip|mp3*|mpe*g|pdf|docx*|pptx*|xlsx*|rar*';
			$options['track_exclude'] = array();
			$options['ga_target_geomap'] = 'None';
			$options['ga_realtime_pages'] = 10;
			$options['token'] = false;
			$options['ga4_webstreams_list'] = array();
			$options['ga_tracking_code'] = '';
			$options['ga_enhanced_links'] = 0;
			$options['network_mode'] = 0;
			$options['ga_speed_samplerate'] = 1;
			$options['ga_user_samplerate'] = 100;
			$options['ga_event_bouncerate'] = 0;
			$options['ga_crossdomain_tracking'] = 0;
			$options['ga_crossdomain_list'] = '';
			$options['ga_author_dimindex'] = 0;
			$options['ga_category_dimindex'] = 0;
			$options['ga_tag_dimindex'] = 0;
			$options['ga_user_dimindex'] = 0;
			$options['ga_pubyear_dimindex'] = 0;
			$options['ga_pubyearmonth_dimindex'] = 0;
			$options['ga_aff_tracking'] = 0;
			$options['ga_event_affiliates'] = '/out/';
			$options['backend_item_reports'] = 1;
			$options['frontend_item_reports'] = 0;
			$options['dashboard_widget'] = 1;
			$options['api_backoff'] = 0;
			$options['ga_cookiedomain'] = '';
			$options['ga_cookiename'] = '';
			$options['ga_cookieexpires'] = '';
			$options['pagetitle_404'] = 'Page Not Found';
			$options['maps_api_key'] = '';
			$options['tm_author_var'] = 0;
			$options['tm_category_var'] = 0;
			$options['tm_tag_var'] = 0;
			$options['tm_user_var'] = 0;
			$options['tm_pubyear_var'] = 0;
			$options['tm_pubyearmonth_var'] = 0;
			$options['web_containerid'] = '';
			$options['amp_containerid'] = '';
			$options['amp_tracking_tagmanager'] = 0;
			$options['amp_tracking_analytics'] = 0;
			$options['amp_tracking_clientidapi'] = 0;
			$options['trackingcode_infooter'] = 0;
			$options['trackingevents_infooter'] = 0;
			$options['ecommerce_mode'] = 'disabled';
			$options['ga_formsubmit_tracking'] = 0;
			$options['optimize_tracking'] = 0;
			$options['optimize_containerid'] = '';
			$options['optimize_pagehiding'] = '';
			$options['superadmin_tracking'] = 0;
			$options['ga_pagescrolldepth_tracking'] = 0;
			$options['tm_pagescrolldepth_tracking'] = 0;
			$options['ga_event_precision'] = 0;
			$options['ga_force_ssl'] = 0;
			$options['ga_optout'] = 0;
			$options['ga_dnt_optout'] = 0;
			$options['tm_optout'] = 0;
			$options['tm_dnt_optout'] = 0;

			add_option( 'aiwp_options', json_encode( $options ) );
	}
}
