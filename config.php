<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Config' ) ) {

	final class GADWP_Config {

		public $options;

		public function __construct() {
			// Get plugin options
			$this->get_plugin_options();
			// Automatic updates
			add_filter( 'auto_update_plugin', array( $this, 'automatic_update' ), 10, 2 );
			// Provide language packs for all available Network languages
			if ( is_multisite() ) {
				add_filter( 'plugins_update_check_locales', array( $this, 'translation_updates' ), 10, 1 );
			}
		}

		public function get_major_version( $version ) {
			$exploded_version = explode( '.', $version );
			if ( isset( $exploded_version[2] ) ) {
				return $exploded_version[0] . '.' . $exploded_version[1] . '.' . $exploded_version[2];
			} else {
				return $exploded_version[0] . '.' . $exploded_version[1] . '.0';
			}
		}

		public function automatic_update( $update, $item ) {
			$item = (array) $item;
			if ( is_multisite() && ! is_main_site() ) {
				return;
			}
			if ( ! isset( $item['new_version'] ) || ! isset( $item['plugin'] ) || ! $this->options['automatic_updates_minorversion'] ) {
				return $update;
			}
			if ( isset( $item['slug'] ) && 'google-analytics-dashboard-for-wp' == $item['slug'] ) {
				// Only when a minor update is available
				if ( $this->get_major_version( GADWP_CURRENT_VERSION ) == $this->get_major_version( $item['new_version'] ) ) {
					update_option( 'gadwp_got_updated', true );
					return ( $this->get_major_version( GADWP_CURRENT_VERSION ) == $this->get_major_version( $item['new_version'] ) );
				}
			}
			return $update;
		}

		public function translation_updates( $locales ) {
			$languages = get_available_languages();
			return array_values( $languages );
		}

		// Validates data before storing
		private function validate_data( $options ) {
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
								'ga_dash_excludesa',
								'ga_pagescrolldepth_tracking',
								'tm_pagescrolldepth_tracking',
								'ga_speed_samplerate',
								'ga_user_samplerate',
								'ga_event_precision',
			);
			foreach ( $numerics as $key ) {
				if ( isset( $options[$key] ) ) {
					$options[$key] = (int) $options[$key];
				}
			}

			$texts = array( 'ga_crossdomain_list',
							'ga_dash_clientid',
							'ga_dash_clientsecret',
							'ga_dash_style',
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
							'ga_dash_tracking_type',
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

			$token = json_decode( $options['ga_dash_token'] ); // v4.8.2
			if ( isset( $token->token_type ) ) {
				unset( $options['ga_dash_refresh_token'] );
			}

			return $options;
		}

		public function set_plugin_options( $network_settings = false ) {
			// Handle Network Mode
			$options = $this->options;
			$get_network_options = get_site_option( 'gadash_network_options' );
			$old_network_options = (array) json_decode( $get_network_options );
			if ( is_multisite() ) {
				if ( $network_settings ) { // Retrieve network options, clear blog options, store both to db
					$network_options['ga_dash_token'] = $this->options['ga_dash_token'];
					$options['ga_dash_token'] = '';
					if ( is_network_admin() ) {
						$network_options['ga_dash_profile_list'] = $this->options['ga_dash_profile_list'];
						$options['ga_dash_profile_list'] = array();
						$network_options['ga_dash_clientid'] = $this->options['ga_dash_clientid'];
						$options['ga_dash_clientid'] = '';
						$network_options['ga_dash_clientsecret'] = $this->options['ga_dash_clientsecret'];
						$options['ga_dash_clientsecret'] = '';
						$network_options['ga_dash_userapi'] = $this->options['ga_dash_userapi'];
						$options['ga_dash_userapi'] = 0;
						$network_options['ga_dash_network'] = $this->options['ga_dash_network'];
						$network_options['ga_dash_excludesa'] = $this->options['ga_dash_excludesa'];
						$network_options['automatic_updates_minorversion'] = $this->options['automatic_updates_minorversion'];
						unset( $options['ga_dash_network'] );
						if ( isset( $this->options['ga_dash_tableid_network'] ) ) {
							$network_options['ga_dash_tableid_network'] = $this->options['ga_dash_tableid_network'];
							unset( $options['ga_dash_tableid_network'] );
						}
					}
					update_site_option( 'gadash_network_options', json_encode( $this->validate_data( array_merge( $old_network_options, $network_options ) ) ) );
				}
			}
			update_option( 'gadash_options', json_encode( $this->validate_data( $options ) ) );
		}

		private function get_plugin_options() {
			/*
			 * Get plugin options
			 */
			global $blog_id;

			if ( ! get_option( 'gadash_options' ) ) {
				GADWP_Install::install();
			}
			$this->options = (array) json_decode( get_option( 'gadash_options' ) );
			// Maintain Compatibility
			$this->maintain_compatibility();
			// Handle Network Mode
			if ( is_multisite() ) {
				$get_network_options = get_site_option( 'gadash_network_options' );
				$network_options = (array) json_decode( $get_network_options );
				if ( isset( $network_options['ga_dash_network'] ) && ( $network_options['ga_dash_network'] ) ) {
					if ( ! is_network_admin() && ! empty( $network_options['ga_dash_profile_list'] ) && isset( $network_options['ga_dash_tableid_network']->$blog_id ) ) {
						$network_options['ga_dash_profile_list'] = array( 0 => GADWP_Tools::get_selected_profile( $network_options['ga_dash_profile_list'], $network_options['ga_dash_tableid_network']->$blog_id ) );
						$network_options['ga_dash_tableid_jail'] = $network_options['ga_dash_profile_list'][0][1];
					}
					$this->options = array_merge( $this->options, $network_options );
				} else {
					$this->options['ga_dash_network'] = 0;
				}
			}
		}

		private function maintain_compatibility() {
			$flag = false;

			if ( GADWP_CURRENT_VERSION != get_option( 'gadwp_version' ) ) {
				$flag = true;
				update_option( 'gadwp_version', GADWP_CURRENT_VERSION );
				$rebuild_token = json_decode( $this->options['ga_dash_token'] ); // v4.8.2
				if ( is_object( $rebuild_token ) && ! isset( $rebuild_token->token_type ) ) {
					if ( isset( $this->options['ga_dash_refresh_token'] ) ) {
						$rebuild_token->refresh_token = $this->options['ga_dash_refresh_token'];
					}
					$rebuild_token->token_type = "Bearer";
					$this->options['ga_dash_token'] = json_encode( $rebuild_token );
					unset( $this->options['ga_dash_refresh_token'] );
					$this->set_plugin_options( true );
				} else {
					unset( $this->options['ga_dash_refresh_token'] );
				}
				GADWP_Tools::clear_cache();
				GADWP_Tools::delete_cache( 'last_error' );
				if ( is_multisite() ) { // Cleanup errors and cookies on the entire network
					foreach ( GADWP_Tools::get_sites( array( 'number' => apply_filters( 'gadwp_sites_limit', 100 ) ) ) as $blog ) {
						switch_to_blog( $blog['blog_id'] );
						GADWP_Tools::delete_cache( 'gapi_errors' );
						restore_current_blog();
					}
				} else {
					GADWP_Tools::delete_cache( 'gapi_errors' );
				}
				// GADWP_Tools::unset_cookie( 'default_metric' );
				// GADWP_Tools::unset_cookie( 'default_dimension' );
				// GADWP_Tools::unset_cookie( 'default_view' );
			}

			/* @formatter:off */
			$zeros = array( 	'ga_enhanced_links',
								'ga_dash_network',
								'ga_enhanced_excludesa',
								'ga_dash_remarketing',
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
								'ga_dash_excludesa', //v5.0
								'ga_pagescrolldepth_tracking', //v5.0
								'tm_pagescrolldepth_tracking', //v5.0
								'ga_event_precision', //v5.1.1.1
								'ga_force_ssl', //v5.1.2
			);
			foreach ( $zeros as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = 0;
					$flag = true;
				}
			}

			if ( isset( $this->options['item_reports'] ) ) { // v4.8
				$this->options['backend_item_reports'] = $this->options['item_reports'];
			}
			if ( isset( $this->options['ga_dash_frontend_stats'] ) ) { // v4.8
				$this->options['frontend_item_reports'] = $this->options['ga_dash_frontend_stats'];
			}

			if ( isset($this->options['ga_dash_tracking']) && 0 == $this->options['ga_dash_tracking'] ) { // v5.0.1
				$this->options['ga_dash_tracking_type'] = 'disabled';
				$flag = true;
			}

			$unsets = array( 	'ga_dash_jailadmins', // v4.7
								'ga_tracking_code',
								'ga_dash_tableid', // v4.9
								'ga_dash_frontend_keywords', // v4.8
								'ga_dash_apikey', // v4.9.1.3
								'ga_dash_default_metric', // v4.8.1
								'ga_dash_default_dimension', // v4.8.1
								'ga_dash_adsense', // v5.0
								'ga_dash_frontend_stats', // v4.8
								'item_reports', // v4.8
								'ga_dash_tracking', // v5.0
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
			);
			foreach ( $empties as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = '';
					$flag = true;
				}
			}

			$ones = array( 	'ga_speed_samplerate',
							'automatic_updates_minorversion',
							'backend_item_reports', // v4.8
							'dashboard_widget', // v4.7
			);
			foreach ( $ones as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = 1;
					$flag = true;
				}
			}

			$arrays = array( 	'ga_dash_access_front',
								'ga_dash_access_back',
								'ga_dash_profile_list',
								'ga_track_exclude',
			);
			foreach ( $arrays as $key ) {
				if ( ! is_array( $this->options[$key] ) ) {
					$this->options[$key] = array();
					$flag = true;
				}
			}
			if ( empty( $this->options['ga_dash_access_front'] ) ) {
				$this->options['ga_dash_access_front'][] = 'administrator';
			}
			if ( empty( $this->options['ga_dash_access_back'] ) ) {
				$this->options['ga_dash_access_back'][] = 'administrator';
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

			if ( 'classic' == $this->options['ga_dash_tracking_type'] ) { // v5.0
				$this->options['ga_dash_tracking_type'] = 'universal';
				$flag = true;
			}

			if ( $flag ) {
				$this->set_plugin_options( false );
			}
		}
	}
}
