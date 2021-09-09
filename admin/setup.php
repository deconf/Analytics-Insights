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
if ( ! class_exists( 'AIWP_Backend_Setup' ) ) {

	final class AIWP_Backend_Setup {

		private $aiwp;

		public function __construct() {
			$this->aiwp = AIWP();
			// Styles & Scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
			// Site Menu
			add_action( 'admin_menu', array( $this, 'site_menu' ) );
			// Network Menu
			add_action( 'network_admin_menu', array( $this, 'network_menu' ) );
			// Settings link
			add_filter( "plugin_action_links_" . plugin_basename( AIWP_DIR . 'analytics-insights.php' ), array( $this, 'settings_link' ) );
		}

		/**
		 * Add Site Menu
		 */
		public function site_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_options' ) ) {
				include ( AIWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Analytics Insights", 'analytics-insights' ), __( "Analytics Insights", 'analytics-insights' ), 'manage_options', 'aiwp_settings', array( 'AIWP_Settings', 'general_settings' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : AIWP_URL . 'admin/images/aiwp-icon.png' );
				add_submenu_page( 'aiwp_settings', __( "General Settings", 'analytics-insights' ), __( "General Settings", 'analytics-insights' ), 'manage_options', 'aiwp_settings', array( 'AIWP_Settings', 'general_settings' ) );
				add_submenu_page( 'aiwp_settings', __( "Backend Settings", 'analytics-insights' ), __( "Backend Settings", 'analytics-insights' ), 'manage_options', 'aiwp_backend_settings', array( 'AIWP_Settings', 'backend_settings' ) );
				add_submenu_page( 'aiwp_settings', __( "Frontend Settings", 'analytics-insights' ), __( "Frontend Settings", 'analytics-insights' ), 'manage_options', 'aiwp_frontend_settings', array( 'AIWP_Settings', 'frontend_settings' ) );
				add_submenu_page( 'aiwp_settings', __( "Tracking Code", 'analytics-insights' ), __( "Tracking Code", 'analytics-insights' ), 'manage_options', 'aiwp_tracking_settings', array( 'AIWP_Settings', 'tracking_settings' ) );
				add_submenu_page( 'aiwp_settings', __( "Errors & Debug", 'analytics-insights' ), __( "Errors & Debug", 'analytics-insights' ), 'manage_options', 'aiwp_errors_debugging', array( 'AIWP_Settings', 'errors_debugging' ) );
			}
		}

		/**
		 * Add Network Menu
		 */
		public function network_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_network' ) ) {
				include ( AIWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Analytics Insights", 'analytics-insights' ), "Analytics Insights", 'manage_network', 'aiwp_settings', array( 'AIWP_Settings', 'general_settings_network' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : AIWP_URL . 'admin/images/aiwp-icon.png' );
				add_submenu_page( 'aiwp_settings', __( "General Settings", 'analytics-insights' ), __( "General Settings", 'analytics-insights' ), 'manage_network', 'aiwp_settings', array( 'AIWP_Settings', 'general_settings_network' ) );
				add_submenu_page( 'aiwp_settings', __( "Errors & Debug", 'analytics-insights' ), __( "Errors & Debug", 'analytics-insights' ), 'manage_network', 'aiwp_errors_debugging', array( 'AIWP_Settings', 'errors_debugging' ) );
			}
		}

		/**
		 * Styles & Scripts conditional loading (based on current URI)
		 *
		 * @param
		 *            $hook
		 */
		public function load_styles_scripts( $hook ) {
			$new_hook = explode( '_page_', $hook );
			if ( isset( $new_hook[1] ) ) {
				$new_hook = '_page_' . $new_hook[1];
			} else {
				$new_hook = $hook;
			}
			/*
			 * AIWP main stylesheet
			 */
			wp_enqueue_style( 'aiwp', AIWP_URL . 'admin/css/aiwp.css', null, AIWP_CURRENT_VERSION );
			/*
			 * AIWP UI
			 */
			if ( AIWP_Tools::get_cache( 'gapi_errors' ) ) {
				$ed_bubble = '!';
			} else {
				$ed_bubble = '';
			}
			wp_enqueue_script( 'aiwp-backend-ui', plugins_url( 'js/ui.js', __FILE__ ), array( 'jquery' ), AIWP_CURRENT_VERSION, true );
			/* @formatter:off */
			wp_localize_script( 'aiwp-backend-ui', 'aiwp_ui_data', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'aiwp_dismiss_notices' ),
				'ed_bubble' => $ed_bubble,
			)
			);
			/* @formatter:on */
			if ( $this->aiwp->config->options['switch_profile'] && count( $this->aiwp->config->options['ga_profiles_list'] ) > 1 ) {
				$views = array();
				foreach ( $this->aiwp->config->options['ga_profiles_list'] as $items ) {
					if ( $items[3] ) {
						$views[$items[1]] = sanitize_text_field( AIWP_Tools::strip_protocol( $items[3] ) );
					}
				}
			} else {
				$views = false;
			}
			/*
			 * Main Dashboard Widgets Styles & Scripts
			 */
			$widgets_hooks = array( 'index.php' );
			if ( in_array( $new_hook, $widgets_hooks ) ) {
				if ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_back'] ) && $this->aiwp->config->options['dashboard_widget'] ) {
					if ( $this->aiwp->config->options['ga_target_geomap'] ) {
						$country_codes = AIWP_Tools::get_countrycodes();
						if ( isset( $country_codes[$this->aiwp->config->options['ga_target_geomap']] ) ) {
							$region = sanitize_text_field( $this->aiwp->config->options['ga_target_geomap'] );
						} else {
							$region = false;
						}
					} else {
						$region = false;
					}
					wp_enqueue_style( 'aiwp-nprogress', AIWP_URL . 'common/nprogress/nprogress.css', null, AIWP_CURRENT_VERSION );
					wp_enqueue_style( 'aiwp-backend-item-reports', AIWP_URL . 'admin/css/admin-widgets.css', null, AIWP_CURRENT_VERSION );
					wp_register_style( 'jquery-ui-tooltip-html', AIWP_URL . 'common/realtime/jquery.ui.tooltip.html.css' );
					wp_enqueue_style( 'jquery-ui-tooltip-html' );
					wp_register_script( 'jquery-ui-tooltip-html', AIWP_URL . 'common/realtime/jquery.ui.tooltip.html.js' );
					wp_register_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), null );
					wp_enqueue_script( 'aiwp-nprogress', AIWP_URL . 'common/nprogress/nprogress.js', array( 'jquery' ), AIWP_CURRENT_VERSION );
					wp_enqueue_script( 'aiwp-backend-dashboard-reports', AIWP_URL . 'common/js/reports5.js', array( 'jquery', 'googlecharts', 'aiwp-nprogress', 'jquery-ui-tooltip', 'jquery-ui-core', 'jquery-ui-position', 'jquery-ui-tooltip-html' ), AIWP_CURRENT_VERSION, true );
					/* @formatter:off */

					$datelist = array(
						'realtime' => __( "Real-Time", 'analytics-insights' ),
						'today' => __( "Today", 'analytics-insights' ),
						'yesterday' => __( "Yesterday", 'analytics-insights' ),
						'7daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 7 ),
						'14daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 14 ),
						'30daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 30 ),
						'90daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 90 ),
						'365daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 1, 'analytics-insights' ), __('One', 'analytics-insights') ),
						'1095daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 3, 'analytics-insights' ), __('Three', 'analytics-insights') ),
					);


					if ( $this->aiwp->config->options['user_api'] && ! $this->aiwp->config->options['backend_realtime_report'] ) {
						array_shift( $datelist );
					}

					wp_localize_script( 'aiwp-backend-dashboard-reports', 'aiwpItemData', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'aiwp_backend_item_reports' ),
						'dateList' => $datelist,
						'reportList' => array(
							'sessions' => __( "Sessions", 'analytics-insights' ),
							'users' => __( "Users", 'analytics-insights' ),
							'organicSearches' => __( "Organic", 'analytics-insights' ),
							'pageviews' => __( "Page Views", 'analytics-insights' ),
							'visitBounceRate' => __( "Bounce Rate", 'analytics-insights' ),
							'locations' => __( "Location", 'analytics-insights' ),
							'contentpages' =>  __( "Pages", 'analytics-insights' ),
							'referrers' => __( "Referrers", 'analytics-insights' ),
							'searches' => __( "Searches", 'analytics-insights' ),
							'trafficdetails' => __( "Traffic", 'analytics-insights' ),
							'technologydetails' => __( "Technology", 'analytics-insights' ),
							'404errors' => __( "404 Errors", 'analytics-insights' ),
						),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'analytics-insights' ), //0
							__( "Traffic Mediums", 'analytics-insights' ),
							__( "Visitor Type", 'analytics-insights' ),
							__( "Search Engines", 'analytics-insights' ),
							__( "Social Networks", 'analytics-insights' ),
							__( "Sessions", 'analytics-insights' ),
							__( "Users", 'analytics-insights' ),
							__( "Page Views", 'analytics-insights' ),
							__( "Bounce Rate", 'analytics-insights' ),
							__( "Organic Search", 'analytics-insights' ),
							__( "Pages/Session", 'analytics-insights' ),
							__( "Invalid response", 'analytics-insights' ),
							__( "No Data", 'analytics-insights' ),
							__( "This report is unavailable", 'analytics-insights' ),
							__( "report generated by", 'analytics-insights' ), //14
							__( "This plugin needs an authorization:", 'analytics-insights' ) . ' <a href="' . menu_page_url( 'aiwp_settings', false ) . '">' . __( "authorize the plugin", 'analytics-insights' ) . '</a>.',
							__( "Browser", 'analytics-insights' ), //16
							__( "Operating System", 'analytics-insights' ),
							__( "Screen Resolution", 'analytics-insights' ),
							__( "Mobile Brand", 'analytics-insights' ),
							__( "REFERRALS", 'analytics-insights' ), //20
							__( "KEYWORDS", 'analytics-insights' ),
							__( "SOCIAL", 'analytics-insights' ),
							__( "CAMPAIGN", 'analytics-insights' ),
							__( "DIRECT", 'analytics-insights' ),
							__( "NEW", 'analytics-insights' ), //25
							__( "Time on Page", 'analytics-insights' ),
							__( "Page Load Time", 'analytics-insights' ),
							__( "Session Duration", 'analytics-insights' ),
							__( "", 'analytics-insights' ),
							__( "Search ...", 'analytics-insights' ), //30
						),
						'rtLimitPages' => $this->aiwp->config->options['ga_realtime_pages'],
						'colorVariations' => AIWP_Tools::variations( sanitize_text_field( $this->aiwp->config->options['theme_color'] ) ),
						'region' => $region,
						'mapsApiKey' => apply_filters( 'aiwp_maps_api_key', sanitize_text_field( $this->aiwp->config->options['maps_api_key'] ) ),
						'language' => get_bloginfo( 'language' ),
						'viewList' => $views,
						'scope' => 'admin-widgets',
					)

					);
					/* @formatter:on */
				}
			}
			/*
			 * Posts/Pages List Styles & Scripts
			 */
			$contentstats_hooks = array( 'edit.php' );
			if ( in_array( $hook, $contentstats_hooks ) ) {
				if ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_back'] ) && $this->aiwp->config->options['backend_item_reports'] ) {
					if ( $this->aiwp->config->options['ga_target_geomap'] ) {
						$country_codes = AIWP_Tools::get_countrycodes();
						if ( isset( $country_codes[$this->aiwp->config->options['ga_target_geomap']] ) ) {
							$region = $this->aiwp->config->options['ga_target_geomap'];
						} else {
							$region = false;
						}
					} else {
						$region = false;
					}
					wp_enqueue_style( 'aiwp-nprogress', AIWP_URL . 'common/nprogress/nprogress.css', null, AIWP_CURRENT_VERSION );
					wp_enqueue_style( 'aiwp-backend-item-reports', AIWP_URL . 'admin/css/item-reports.css', null, AIWP_CURRENT_VERSION );
					wp_enqueue_style( "wp-jquery-ui-dialog" );
					wp_register_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), null );
					wp_enqueue_script( 'aiwp-nprogress', AIWP_URL . 'common/nprogress/nprogress.js', array( 'jquery' ), AIWP_CURRENT_VERSION );
					wp_enqueue_script( 'aiwp-backend-item-reports', AIWP_URL . 'common/js/reports5.js', array( 'aiwp-nprogress', 'googlecharts', 'jquery', 'jquery-ui-dialog' ), AIWP_CURRENT_VERSION, true );
					/* @formatter:off */
					wp_localize_script( 'aiwp-backend-item-reports', 'aiwpItemData', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'aiwp_backend_item_reports' ),
						'dateList' => array(
							'today' => __( "Today", 'analytics-insights' ),
							'yesterday' => __( "Yesterday", 'analytics-insights' ),
							'7daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 7 ),
							'14daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 14 ),
							'30daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 30 ),
							'90daysAgo' => sprintf( __( "Last %d Days", 'analytics-insights' ), 90 ),
							'365daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 1, 'analytics-insights' ), __('One', 'analytics-insights') ),
							'1095daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 3, 'analytics-insights' ), __('Three', 'analytics-insights') ),
						),
						'reportList' => array(
							'uniquePageviews' => __( "Unique Views", 'analytics-insights' ),
							'users' => __( "Users", 'analytics-insights' ),
							'organicSearches' => __( "Organic", 'analytics-insights' ),
							'pageviews' => __( "Page Views", 'analytics-insights' ),
							'visitBounceRate' => __( "Bounce Rate", 'analytics-insights' ),
							'locations' => __( "Location", 'analytics-insights' ),
							'referrers' => __( "Referrers", 'analytics-insights' ),
							'searches' => __( "Searches", 'analytics-insights' ),
							'trafficdetails' => __( "Traffic", 'analytics-insights' ),
							'technologydetails' => __( "Technology", 'analytics-insights' ),
						),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'analytics-insights' ), //0
							__( "Traffic Mediums", 'analytics-insights' ),
							__( "Visitor Type", 'analytics-insights' ),
							__( "Social Networks", 'analytics-insights' ),
							__( "Search Engines", 'analytics-insights' ),
							__( "Unique Views", 'analytics-insights' ),
							__( "Users", 'analytics-insights' ),
							__( "Page Views", 'analytics-insights' ),
							__( "Bounce Rate", 'analytics-insights' ),
							__( "Organic Search", 'analytics-insights' ),
							__( "Pages/Session", 'analytics-insights' ),
							__( "Invalid response", 'analytics-insights' ),
							__( "No Data", 'analytics-insights' ),
							__( "This report is unavailable", 'analytics-insights' ),
							__( "report generated by", 'analytics-insights' ), //14
							__( "This plugin needs an authorization:", 'analytics-insights' ) . ' <a href="' . menu_page_url( 'aiwp_settings', false ) . '">' . __( "authorize the plugin", 'analytics-insights' ) . '</a>.',
							__( "Browser", 'analytics-insights' ), //16
							__( "Operating System", 'analytics-insights' ),
							__( "Screen Resolution", 'analytics-insights' ),
							__( "Mobile Brand", 'analytics-insights' ), //19
							__( "Future Use", 'analytics-insights' ),
							__( "Future Use", 'analytics-insights' ),
							__( "Future Use", 'analytics-insights' ),
							__( "Future Use", 'analytics-insights' ),
							__( "Future Use", 'analytics-insights' ),
							__( "Future Use", 'analytics-insights' ), //25
							__( "Time on Page", 'analytics-insights' ),
							__( "Page Load Time", 'analytics-insights' ),
							__( "Exit Rate", 'analytics-insights' ),
							__( "", 'analytics-insights' ),
							__( "Search ...", 'analytics-insights' ), //30
						),
						'colorVariations' => AIWP_Tools::variations( $this->aiwp->config->options['theme_color'] ),
						'region' => $region,
						'mapsApiKey' => apply_filters( 'aiwp_maps_api_key', sanitize_text_field( $this->aiwp->config->options['maps_api_key'] ) ),
						'language' => get_bloginfo( 'language' ),
						'viewList' => false,
						'scope' => 'admin-item',
						)
					);
					/* @formatter:on */
				}
			}
			/*
			 * Settings Styles & Scripts
			 */
			$settings_hooks = array( '_page_aiwp_settings', '_page_aiwp_backend_settings', '_page_aiwp_frontend_settings', '_page_aiwp_tracking_settings', '_page_aiwp_errors_debugging' );
			if ( in_array( $new_hook, $settings_hooks ) ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker-script-handle', plugins_url( 'js/wp-color-picker-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
				wp_enqueue_script( 'aiwp-settings', plugins_url( 'js/settings.js', __FILE__ ), array( 'jquery' ), AIWP_CURRENT_VERSION, true );
			}
		}

		/**
		 * Add "Settings" link in Plugins List
		 *
		 * @param
		 *            $links
		 * @return array
		 */
		public function settings_link( $links ) {
			$settings_link = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=aiwp_settings' ) ) . '">' . __( "Settings", 'analytics-insights' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}
	}
}
