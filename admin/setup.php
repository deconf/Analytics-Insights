<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Backend_Setup' ) ) {

	final class GADWP_Backend_Setup {

		private $gadwp;

		public function __construct() {
			$this->gadwp = GADWP();

			// Styles & Scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
			// Site Menu
			add_action( 'admin_menu', array( $this, 'site_menu' ) );
			// Network Menu
			add_action( 'network_admin_menu', array( $this, 'network_menu' ) );
			// Settings link
			add_filter( "plugin_action_links_" . plugin_basename( GADWP_DIR . 'gadwp.php' ), array( $this, 'settings_link' ) );
			// Updated admin notice
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}

		/**
		 * Add Site Menu
		 */
		public function site_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_options' ) ) {
				include ( GADWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Google Analytics", 'google-analytics-dashboard-for-wp' ), __( "Google Analytics", 'google-analytics-dashboard-for-wp' ), 'manage_options', 'gadash_settings', array( 'GADWP_Settings', 'general_settings' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : GADWP_URL . 'admin/images/gadash-icon.png' );
				add_submenu_page( 'gadash_settings', __( "General Settings", 'google-analytics-dashboard-for-wp' ), __( "General Settings", 'google-analytics-dashboard-for-wp' ), 'manage_options', 'gadash_settings', array( 'GADWP_Settings', 'general_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Backend Settings", 'google-analytics-dashboard-for-wp' ), __( "Backend Settings", 'google-analytics-dashboard-for-wp' ), 'manage_options', 'gadash_backend_settings', array( 'GADWP_Settings', 'backend_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Frontend Settings", 'google-analytics-dashboard-for-wp' ), __( "Frontend Settings", 'google-analytics-dashboard-for-wp' ), 'manage_options', 'gadash_frontend_settings', array( 'GADWP_Settings', 'frontend_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Tracking Code", 'google-analytics-dashboard-for-wp' ), __( "Tracking Code", 'google-analytics-dashboard-for-wp' ), 'manage_options', 'gadash_tracking_settings', array( 'GADWP_Settings', 'tracking_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Errors & Debug", 'google-analytics-dashboard-for-wp' ), __( "Errors & Debug", 'google-analytics-dashboard-for-wp' ), 'manage_options', 'gadash_errors_debugging', array( 'GADWP_Settings', 'errors_debugging' ) );
			}
		}

		/**
		 * Add Network Menu
		 */
		public function network_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_netwrok' ) ) {
				include ( GADWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Google Analytics", 'google-analytics-dashboard-for-wp' ), "Google Analytics", 'manage_netwrok', 'gadash_settings', array( 'GADWP_Settings', 'general_settings_network' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : GADWP_URL . 'admin/images/gadash-icon.png' );
				add_submenu_page( 'gadash_settings', __( "General Settings", 'google-analytics-dashboard-for-wp' ), __( "General Settings", 'google-analytics-dashboard-for-wp' ), 'manage_netwrok', 'gadash_settings', array( 'GADWP_Settings', 'general_settings_network' ) );
				add_submenu_page( 'gadash_settings', __( "Errors & Debug", 'google-analytics-dashboard-for-wp' ), __( "Errors & Debug", 'google-analytics-dashboard-for-wp' ), 'manage_network', 'gadash_errors_debugging', array( 'GADWP_Settings', 'errors_debugging' ) );
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
			 * GADWP main stylesheet
			 */
			wp_enqueue_style( 'gadwp', GADWP_URL . 'admin/css/gadwp.css', null, GADWP_CURRENT_VERSION );

			/*
			 * GADWP UI
			 */

			if ( GADWP_Tools::get_cache( 'gapi_errors' ) ) {
				$ed_bubble = '!';
			} else {
				$ed_bubble = '';
			}

			wp_enqueue_script( 'gadwp_backend_ui', plugins_url( 'js/ui.js', __FILE__ ), array( 'jquery' ), GADWP_CURRENT_VERSION, true );

			/* @formatter:off */
			wp_localize_script( 'gadwp_backend_ui', 'gadwp_ui_data', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'gadwp_dismiss_notices' ),
				'ed_bubble' => $ed_bubble,
			)
			);
			/* @formatter:on */

			/*
			 * Dashboard Widgets Styles & Scripts
			 */
			$widgets_hooks = array( 'index.php' );

			if ( in_array( $new_hook, $widgets_hooks ) ) {
				if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) && $this->gadwp->config->options['dashboard_widget'] ) {

					wp_enqueue_style( 'gadwp-nprogress', GADWP_URL . 'tools/nprogress/nprogress.css', null, GADWP_CURRENT_VERSION );

					wp_enqueue_script( 'gadwp-admin-widgets', plugins_url( 'js/widgets.js', __FILE__ ), array( 'jquery' ), GADWP_CURRENT_VERSION );

					if ( ! wp_script_is( 'googlejsapi' ) ) {
						wp_register_script( 'googlejsapi', 'https://www.google.com/jsapi' );
						wp_enqueue_script( 'googlejsapi' );
					}

					wp_enqueue_script( 'gadwp-nprogress', GADWP_URL . 'tools/nprogress/nprogress.js', array( 'jquery' ), GADWP_CURRENT_VERSION );
				}
			}

			/*
			 * Posts/Pages List Styles & Scripts
			 */
			$contentstats_hooks = array( 'edit.php' );
			if ( in_array( $hook, $contentstats_hooks ) ) {
				if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) && $this->gadwp->config->options['backend_item_reports'] ) {

					wp_enqueue_style( 'gadwp-nprogress', GADWP_URL . 'tools/nprogress/nprogress.css', null, GADWP_CURRENT_VERSION );

					wp_enqueue_style( 'gadwp_backend_item_reports', GADWP_URL . 'admin/css/item-reports.css', null, GADWP_CURRENT_VERSION );

					$country_codes = GADWP_Tools::get_countrycodes();

					if ( $this->gadwp->config->options['ga_target_geomap'] && isset( $country_codes[$this->gadwp->config->options['ga_target_geomap']] ) ) {
						$region = $this->gadwp->config->options['ga_target_geomap'];
					} else {
						$region = false;
					}

					wp_enqueue_style( "wp-jquery-ui-dialog" );

					if ( ! wp_script_is( 'googlejsapi' ) ) {
						wp_register_script( 'googlejsapi', 'https://www.google.com/jsapi' );
					}

					wp_enqueue_script( 'gadwp-nprogress', GADWP_URL . 'tools/nprogress/nprogress.js', array( 'jquery' ), GADWP_CURRENT_VERSION );

					wp_enqueue_script( 'gadwp_backend_item_reports', GADWP_URL . 'tools/js/item-reports.js', array( 'gadwp-nprogress', 'googlejsapi', 'jquery', 'jquery-ui-dialog' ), GADWP_CURRENT_VERSION );

					/* @formatter:off */
					wp_localize_script( 'gadwp_backend_item_reports', 'gadwp_item_data', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'gadwp_backend_item_reports' ),
						'dateList' => array(
							'today' => __( "Today", 'google-analytics-dashboard-for-wp' ),
							'yesterday' => __( "Yesterday", 'google-analytics-dashboard-for-wp' ),
							'7daysAgo' => sprintf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 7 ),
							'14daysAgo' => sprintf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 14 ),
							'30daysAgo' => sprintf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 30 ),
							'90daysAgo' => sprintf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 90 ),
							'365daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 1, 'google-analytics-dashboard-for-wp' ), __('One', 'google-analytics-dashboard-for-wp') ),
							'1095daysAgo' =>  sprintf( _n( "%s Year", "%s Years", 3, 'google-analytics-dashboard-for-wp' ), __('Three', 'google-analytics-dashboard-for-wp') ),
						),
						'reportList' => array(
							'uniquePageviews' => __( "Unique Views", 'google-analytics-dashboard-for-wp' ),
							'users' => __( "Users", 'google-analytics-dashboard-for-wp' ),
							'organicSearches' => __( "Organic", 'google-analytics-dashboard-for-wp' ),
							'pageviews' => __( "Page Views", 'google-analytics-dashboard-for-wp' ),
							'visitBounceRate' => __( "Bounce Rate", 'google-analytics-dashboard-for-wp' ),
							'locations' => __( "Location", 'google-analytics-dashboard-for-wp' ),
							'referrers' => __( "Referrers", 'google-analytics-dashboard-for-wp' ),
							'searches' => __( "Searches", 'google-analytics-dashboard-for-wp' ),
							'trafficdetails' => __( "Traffic Details", 'google-analytics-dashboard-for-wp' )
						),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'google-analytics-dashboard-for-wp' ), //0
							__( "Traffic Mediums", 'google-analytics-dashboard-for-wp' ),
							__( "Visitor Type", 'google-analytics-dashboard-for-wp' ),
							__( "Social Networks", 'google-analytics-dashboard-for-wp' ),
							__( "Search Engines", 'google-analytics-dashboard-for-wp' ),
							__( "Unique Views", 'google-analytics-dashboard-for-wp' ),
							__( "Users", 'google-analytics-dashboard-for-wp' ),
							__( "Page Views", 'google-analytics-dashboard-for-wp' ),
							__( "Bounce Rate", 'google-analytics-dashboard-for-wp' ),
							__( "Organic Search", 'google-analytics-dashboard-for-wp' ),
							__( "Pages/Session", 'google-analytics-dashboard-for-wp' ),
							__( "Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp' ),
							__( "Not enough data collected", 'google-analytics-dashboard-for-wp' ),
							__( "This report is unavailable", 'google-analytics-dashboard-for-wp' ),
							__( "report generated by", 'google-analytics-dashboard-for-wp' ), //14
							__( "This plugin needs an authorization:", 'google-analytics-dashboard-for-wp' ) . ' <a href="' . menu_page_url( 'gadash_settings', false ) . '">' . __( "authorize the plugin", 'google-analytics-dashboard-for-wp' ) . '</a>.',
						),
						'colorVariations' => GADWP_Tools::variations( $this->gadwp->config->options['ga_dash_style'] ),
						'region' => $region,
						'language' => get_bloginfo( 'language' ),
						'scope' => 'admin-item',
						)
					);
					/* @formatter:on */
				}
			}

			/*
			 * Settings Styles & Scripts
			 */
			$settings_hooks = array( '_page_gadash_settings', '_page_gadash_backend_settings', '_page_gadash_frontend_settings', '_page_gadash_tracking_settings', '_page_gadash_errors_debugging' );

			if ( in_array( $new_hook, $settings_hooks ) ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker-script-handle', plugins_url( 'js/wp-color-picker-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
				wp_enqueue_script( 'gadwp-settings', plugins_url( 'js/settings.js', __FILE__ ), array( 'jquery' ), GADWP_CURRENT_VERSION );
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
			$settings_link = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=gadash_settings' ) ) . '">' . __( "Settings", 'google-analytics-dashboard-for-wp' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 *  Add an admin notice after a manual or atuomatic update
		 */
		function admin_notice() {
			if ( get_option( 'gadwp_got_updated' ) ) :
				?>
<div id="gadwp-notice" class="notice is-dismissible">
    <p><?php echo sprintf( __('Google Analytics Dashboard for WP has been updated to version %s.', 'google-analytics-dashboard-for-wp' ), GADWP_CURRENT_VERSION).' '.sprintf( __('For details, check out %1$s and %2$s.', 'google-analytics-dashboard-for-wp' ), sprintf(' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_notice&utm_medium=link&utm_content=release_notice&utm_campaign=gadwp">%s</a> ', __('the documentation page', 'google-analytics-dashboard-for-wp') ), sprintf(' <a href="%1$s">%2$s</a>', esc_url( get_admin_url( null, 'admin.php?page=gadash_settings' ) ), __('the plugin&#39;s settings page', 'google-analytics-dashboard-for-wp') ) ); ?></p>
</div>

			<?php
			endif;
		}
	}
}
