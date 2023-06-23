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
if ( ! class_exists( 'AIWP_Config' ) ) {

	final class AIWP_Config {

		public $options;

		public $reporting_ready;

		public function __construct() {

			$this->option_keys_rename(); 	// Rename old option keys

			$this->get_plugin_options(); // Get plugin options

			$this->reporting_ready = $this->options['tableid_jail'] || $this->options['webstream_jail'];

		}

		// Validates data before storing
		public function validate_data( $options ) {
			/* @formatter:off */
			$numerics = array( 	'ga_realtime_pages',
								'ga_enhanced_links',
								'ga_crossdomain_tracking',
								'ga_author_dimindex',
								'ga_category_dimindex',
								'ga_tag_dimindex',
								'ga_user_dimindex',
								'ga_pubyear_dimindex',
								'ga_pubyearmonth_dimindex',
								'tm_author_var',
								'tm_category_var',
								'tm_tag_var',
								'tm_user_var',
								'tm_pubyear_var',
								'tm_pubyearmonth_var',
								'ga_aff_tracking',
								'amp_tracking_analytics',
								'amp_tracking_clientidapi',
								'amp_tracking_tagmanager',
								'optimize_tracking',
								'optimize_pagehiding',
								'trackingcode_infooter',
								'trackingevents_infooter',
								'ga_formsubmit_tracking',
								'superadmin_tracking',
								'ga_pagescrolldepth_tracking',
								'tm_pagescrolldepth_tracking',
								'ga_speed_samplerate',
								'ga_user_samplerate',
								'ga_event_precision',
								'backend_realtime_report',
								'ga_optout',
								'ga_dnt_optout',
								'tm_optout',
								'tm_dnt_optout',
								'reporting_type',
			);
			foreach ( $numerics as $key ) {
				if ( isset( $options[$key] ) ) {
					$options[$key] = (int) $options[$key];
				}
			}

			$texts = array( 'ga_crossdomain_list',
							'client_id',
							'client_secret',
							'theme_color',
							'ga_target_geomap',
							'ga_cookiedomain',
							'ga_cookiename',
							'pagetitle_404',
							'maps_api_key',
							'web_containerid',
							'amp_containerid',
							'optimize_containerid',
							'ga_event_downloads',
							'ga_event_affiliates',
							'ecommerce_mode',
							'tracking_type',
			);
			foreach ( $texts as $key ) {
				if ( isset( $options[$key] ) ) {
					$options[$key] = trim (sanitize_text_field( $options[$key] ));
				}
			}
			/* @formatter:on */
			if ( isset( $options['ga_event_downloads'] ) && empty( $options['ga_event_downloads'] ) ) {
				$options['ga_event_downloads'] = 'zip|mp3*|mpe*g|pdf|docx*|pptx*|xlsx*|rar*';
			}
			if ( isset( $options['pagetitle_404'] ) && empty( $options['pagetitle_404'] ) ) {
				$options['pagetitle_404'] = 'Page Not Found';
			}
			if ( isset( $options['ga_event_affiliates'] ) && empty( $options['ga_event_affiliates'] ) ) {
				$options['ga_event_affiliates'] = '/out/';
			}
			if ( isset( $options['ga_speed_samplerate'] ) && ( $options['ga_speed_samplerate'] < 1 || $options['ga_speed_samplerate'] > 100 ) ) {
				$options['ga_speed_samplerate'] = 1;
			}
			if ( isset( $options['ga_user_samplerate'] ) && ( $options['ga_user_samplerate'] < 1 || $options['ga_user_samplerate'] > 100 ) ) {
				$options['ga_user_samplerate'] = 100;
			}
			if ( isset( $options['ga_cookieexpires'] ) && $options['ga_cookieexpires'] ) { // v4.9
				$options['ga_cookieexpires'] = (int) $options['ga_cookieexpires'];
			}
			return $options;
		}

		public function set_plugin_options( $network_settings = false ) {

			// Update reporting ready state
			$this->reporting_ready = $this->options['tableid_jail'] || $this->options['webstream_jail'];

			// Handle Network Mode
			$options = $this->options;
			$get_network_options = get_site_option( 'aiwp_network_options' );
			$old_network_options = (array) json_decode( $get_network_options );
			if ( is_multisite() ) {
				if ( $network_settings ) { // Retrieve network options, clear blog options, store both to db
					$network_options['token'] = $this->options['token'];
					$options['token'] = '';
					if ( is_network_admin() ) {
						$network_options['ga_profiles_list'] = $this->options['ga_profiles_list'];
						$options['ga_profiles_list'] = array();
						$network_options['ga4_webstreams_list'] = $this->options['ga4_webstreams_list'];
						$options['ga4_webstreams_list'] = array();
						$network_options['client_id'] = $this->options['client_id'];
						$options['client_id'] = '';
						$network_options['client_secret'] = $this->options['client_secret'];
						$options['client_secret'] = '';
						$network_options['user_api'] = $this->options['user_api'];
						$options['user_api'] = 0;
						$network_options['network_mode'] = $this->options['network_mode'];
						$network_options['superadmin_tracking'] = $this->options['superadmin_tracking'];
						//unset( $options['network_mode'] );
						if ( isset( $this->options['network_tableid'] ) ) {
							$network_options['network_tableid'] = $this->options['network_tableid'];
						}
						if ( isset( $this->options['network_webstream'] ) ) {
							$network_options['network_webstream'] = $this->options['network_webstream'];
						}
					}
					$merged_options = array_merge( $old_network_options, $network_options );
					update_site_option( 'aiwp_network_options', json_encode( $this->validate_data( $merged_options ) ) );
				}
			}
			update_option( 'aiwp_options', json_encode( $this->validate_data( $options ) ) );
		}

		private function get_plugin_options() {
			/*
			 * Get plugin options
			 */
			global $blog_id;
			if ( ! get_option( 'aiwp_options' ) ) {
				AIWP_Install::install();
			}
			$this->options = (array) json_decode( get_option( 'aiwp_options' ) );
			// Maintain Compatibility
			$this->maintain_compatibility();
			// Handle Network Mode
			if ( is_multisite() ) {
				$get_network_options = get_site_option( 'aiwp_network_options' );
				$network_options = (array) json_decode( $get_network_options );
				if ( isset( $network_options['network_mode'] ) && ( $network_options['network_mode'] ) ) {
					if ( ! is_network_admin() && ! empty( $network_options['ga_profiles_list'] ) && isset( $network_options['network_tableid']->$blog_id ) ) {
						$network_options['ga_profiles_list'] = array( 0 => AIWP_Tools::get_selected_profile( $network_options['ga_profiles_list'], $network_options['network_tableid']->$blog_id ) );
						if ( isset( $network_options['ga_profiles_list'][0][1] ) ){
							$network_options['tableid_jail'] = $network_options['ga_profiles_list'][0][1];
						}
					}
					if ( ! is_network_admin() && ! empty( $network_options['ga4_webstreams_list'] ) && isset( $network_options['network_webstream']->$blog_id ) ) {
						$network_options['ga4_webstreams_list'] = array( 0 => AIWP_Tools::get_selected_profile( $network_options['ga4_webstreams_list'], $network_options['network_webstream']->$blog_id ) );
						if ( isset( $network_options['ga4_webstreams_list'][0][1] ) ){
							$network_options['webstream_jail'] = $network_options['ga4_webstreams_list'][0][1];
						}
					}
					$this->options = array_merge( $this->options, $network_options );
				} else {
					$this->options['network_mode'] = 0;
				}
			}
		}

		private function maintain_compatibility() {
			$flag = false;
			$prevver = get_option( 'aiwp_version' );
			if ( $prevver && AIWP_CURRENT_VERSION != $prevver ) {
				$flag = true;
				update_option( 'aiwp_version', AIWP_CURRENT_VERSION );
				AIWP_Tools::clear_cache();
				if ( is_multisite() ) { // Cleanup errors and cookies on the entire network
					foreach ( AIWP_Tools::get_sites( array( 'number' => apply_filters( 'aiwp_sites_limit', 100 ) ) ) as $blog ) {
						switch_to_blog( $blog['blog_id'] );
						AIWP_Tools::delete_cache( 'aiwp_api_errors' );
						restore_current_blog();
					}
				} else {
					AIWP_Tools::delete_cache( 'aiwp_api_errors' );
				}
			}

			AIWP_Tools::delete_cache( 'last_error' ); //removed since 5.8.4

			if ( isset( $this->options['item_reports'] ) ) { // v4.8
				$this->options['backend_item_reports'] = $this->options['item_reports'];
			}
			if ( isset( $this->options['ga_dash_frontend_stats'] ) ) { // v4.8
				$this->options['frontend_item_reports'] = $this->options['ga_dash_frontend_stats'];
			}
			/* @formatter:off */
			$zeros = array( 	'ga_enhanced_links',
								'network_mode',
								'ga_enhanced_excludesa',
								'ga_remarketing',
								'ga_event_bouncerate',
								'ga_author_dimindex',
								'ga_tag_dimindex',
								'ga_category_dimindex',
								'ga_user_dimindex',
								'ga_pubyear_dimindex',
								'ga_pubyearmonth_dimindex',
								'tm_author_var', // v5.0
								'tm_category_var', // v5.0
								'tm_tag_var', // v5.0
								'tm_user_var', // v5.0
								'tm_pubyear_var', // v5.0
								'tm_pubyearmonth_var', // v5.0
								'ga_crossdomain_tracking',
								'api_backoff',  // v4.8.1.3
								'ga_aff_tracking',
								'ga_hash_tracking',
								'switch_profile', // V4.7
								'amp_tracking_analytics', //v5.0
								'amp_tracking_clientidapi', //v5.1.2
								'optimize_tracking', //v5.0
								'optimize_pagehiding', //v5.0
								'amp_tracking_tagmanager', //v5.0
								'trackingcode_infooter', //v5.0
								'trackingevents_infooter', //v5.0
								'ga_formsubmit_tracking', //v5.0
								'superadmin_tracking', //v5.0
								'ga_pagescrolldepth_tracking', //v5.0
								'tm_pagescrolldepth_tracking', //v5.0
								'ga_event_precision', //v5.1.1.1
								'ga_force_ssl', //v5.1.2
								'backend_realtime_report', //v5.2
								'ga_optout', //v5.2.3
								'ga_dnt_optout', //v5.2.3
								'frontend_item_reports',
								'tm_optout', //v5.3.1.2
								'tm_dnt_optout', //v5.3.1.2
								'reporting_type', //v5.6
			);
			foreach ( $zeros as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = 0;
					$flag = true;
				}
			}

			if ( isset($this->options['ga_dash_tracking']) && 0 == $this->options['ga_dash_tracking'] ) { // v5.0.1
				$this->options['tracking_type'] = 'disabled';
				$flag = true;
			}

			if ( isset($this->options['ga_with_gtag']) && 1 == $this->options['ga_with_gtag'] ) { // v5.4.4
				$this->options['tracking_type'] = 'globalsitetag';
				$flag = true;
			}

			$unsets = array( 	'ga_dash_jailadmins', // v4.7
								'ga_tracking_code',
								'ga_dash_tableid', // v4.9
								'ga_dash_frontend_keywords', // v4.8
								'ga_dash_apikey', // v4.9.1.3
								'ga_dash_adsense', // v5.0
								'ga_dash_frontend_stats', // v4.8
								'item_reports', // v4.8
								'ga_dash_tracking', // v5.0
								'ga_dash_cachetime', // v5.2
								'ga_dash_default_ua', // v5.2
								'ga_dash_hidden', // v5.2
								'ga_with_gtag', // v5.4.4
								'ga_webstreams_list',
								'with_endpoint', // v5.6.1
			);
			foreach ( $unsets as $key ) {
				if ( isset( $this->options[$key] ) ) {
					unset( $this->options[$key] );
					$flag = true;
				}
			}

			$empties = array( 	'ga_crossdomain_list',
								'ga_cookiedomain',  // v4.9.4
								'ga_cookiename',  // v4.9.4
								'ga_cookieexpires',  // v4.9.4
								'maps_api_key',  // v4.9.4
								'web_containerid', // v5.0
								'amp_containerid', // v5.0
								'optimize_containerid', // v5.0
								'webstream_jail', // v5.5
								'ga_target_geomap', // v5.5
			);
			foreach ( $empties as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = '';
					$flag = true;
				}
			}

			$ones = array( 	'ga_speed_samplerate',
							'backend_item_reports', // v4.8
							'dashboard_widget', // v4.7
			);
			foreach ( $ones as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = 1;
					$flag = true;
				}
			}

			$arrays = array( 	'access_front',
								'access_back',
								'ga_profiles_list',
								'track_exclude',
								'ga4_webstreams_list',	// v5.5
			);
			foreach ( $arrays as $key ) {
				if ( ! is_array( $this->options[$key] ) ) {
					$this->options[$key] = array();
					$flag = true;
				}
			}
			if ( empty( $this->options['access_front'] ) ) {
				$this->options['access_front'][] = 'administrator';
			}
			if ( empty( $this->options['access_back'] ) ) {
				$this->options['access_back'][] = 'administrator';
			}
			/* @formatter:on */
			if ( ! isset( $this->options['ga_event_affiliates'] ) ) {
				$this->options['ga_event_affiliates'] = '/out/';
				$flag = true;
			}
			if ( ! isset( $this->options['ga_user_samplerate'] ) ) {
				$this->options['ga_user_samplerate'] = 100;
			}
			if ( ! isset( $this->options['ga_event_downloads'] ) ) {
				$this->options['ga_event_downloads'] = 'zip|mp3*|mpe*g|pdf|docx*|pptx*|xlsx*|rar*';
				$flag = true;
			}
			if ( ! isset( $this->options['pagetitle_404'] ) ) { // v4.9.4
				$this->options['pagetitle_404'] = 'Page Not Found';
				$flag = true;
			}
			if ( ! isset( $this->options['ecommerce_mode'] ) ) { // v5.0
				$this->options['ecommerce_mode'] = 'disabled';
				$flag = true;
			}
			if ( isset( $this->options['ga_dash_tracking'] ) && 'classic' == $this->options['ga_dash_tracking'] ) { // v5.0
				$this->options['tracking_type'] = 'universal';
				$flag = true;
			}
			if ( ! isset( $this->options['ga_realtime_pages'] ) ) { // v5.4
				$this->options['ga_realtime_pages'] = 10;
				$flag = true;
			}
			if ( ! isset( $this->options['theme_color'] ) ) { // v5.5
				$this->options['theme_color'] = '#1e73be';
				$flag = true;
			}
			if ( ! isset( $this->options['reporting_type'] ) ) { // v5.5.6
				$this->options['reporting_type'] = 0;
				$flag = true;
			}

			if ( $this->options['tableid_jail'] && !$this->options['webstream_jail'] ){
				$this->options['reporting_type'] = 0;
				$flag = true;
			}

			if ( !$this->options['tableid_jail'] && $this->options['webstream_jail'] ){
				$this->options['reporting_type'] = 1;
				$flag = true;
			}

			if ( $flag ) {
				$this->set_plugin_options( false );
			}
		}

		private function option_keys_rename() {
			/* @formatter:off */
			$batch = array( 	'ga_dash_token' => 'token',
								'ga_dash_clientid' => 'client_id',
								'ga_dash_clientsecret' => 'client_secret',
								'ga_dash_access_front' => 'access_front',
								'ga_dash_access_back' => 'access_back',
								'ga_dash_tableid_jail' => 'tableid_jail',
								'ga_dash_tracking_type' => 'tracking_type',
								'ga_dash_userapi' => 'user_api',
								'ga_dash_network' => 'network_mode',
								'ga_dash_tableid_network' => 'network_tableid',
								'ga_dash_anonim' => 'ga_anonymize_ip',
								'ga_dash_profile_list' => 'ga_profiles_list',
								'ga_dash_remarketing' => 'ga_remarketing',
								'ga_dash_excludesa' => 'superadmin_tracking',
								'ga_track_exclude' => 'track_exclude',
								'ga_dash_style' => 'theme_color',
			);
			/* @formatter:on */
			if ( is_multisite() ) {
				$options = get_site_option( 'gadash_network_options' );
				if ( $options ) {
					$options = (array) json_decode( $options );
					$options = AIWP_Tools::array_keys_rename( $options, $batch );
					update_site_option( 'aiwp_network_options', json_encode( $this->validate_data( $options ) ) );
					delete_site_option( 'gadash_network_options' );
				}
			}
			$options = get_option( 'gadash_options' );
			if ( $options ) {
				$options = (array) json_decode( $options );
				$options = AIWP_Tools::array_keys_rename( $options, $batch );
				update_option( 'aiwp_options', json_encode( $this->validate_data( $options ) ) );
				delete_option( 'gadash_options' );
			}
		}
	}
}

