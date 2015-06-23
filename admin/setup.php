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
			// Error bubble
			add_action( 'admin_print_scripts', array( $this, 'draw_error_bubble' ), 10000 );
			// Updated admin notice
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}

		/**
		 * Error bubble for Errors & Debug
		 */
		public function draw_error_bubble() {
			$bubble_msg = '!';
			if ( get_transient( 'ga_dash_gapi_errors' ) ) {
				?>
<script type="text/javascript">
  jQuery(document).ready(function() {
      jQuery('#toplevel_page_gadash_settings li > a[href*="page=gadash_errors_debugging"]').append('&nbsp;<span class="awaiting-mod count-1"><span class="pending-count" style="padding:0 7px;"><?php echo $bubble_msg ?></span></span>');
  });
</script>
<?php
			}
		}

		/**
		 * Add Site Menu
		 */
		public function site_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_options' ) ) {
				include ( GADWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Google Analytics", 'ga-dash' ), __( "Google Analytics", 'ga-dash' ), 'manage_options', 'gadash_settings', array( 'GADWP_Settings', 'general_settings' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : GADWP_URL . 'admin/images/gadash-icon.png' );
				add_submenu_page( 'gadash_settings', __( "General Settings", 'ga-dash' ), __( "General Settings", 'ga-dash' ), 'manage_options', 'gadash_settings', array( 'GADWP_Settings', 'general_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Backend Settings", 'ga-dash' ), __( "Backend Settings", 'ga-dash' ), 'manage_options', 'gadash_backend_settings', array( 'GADWP_Settings', 'backend_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Frontend Settings", 'ga-dash' ), __( "Frontend Settings", 'ga-dash' ), 'manage_options', 'gadash_frontend_settings', array( 'GADWP_Settings', 'frontend_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Tracking Code", 'ga-dash' ), __( "Tracking Code", 'ga-dash' ), 'manage_options', 'gadash_tracking_settings', array( 'GADWP_Settings', 'tracking_settings' ) );
				add_submenu_page( 'gadash_settings', __( "Errors & Debug", 'ga-dash' ), __( "Errors & Debug", 'ga-dash' ), 'manage_options', 'gadash_errors_debugging', array( 'GADWP_Settings', 'errors_debugging' ) );
			}
		}

		/**
		 * Add Network Menu
		 */
		public function network_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_netwrok' ) ) {
				include ( GADWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Google Analytics", 'ga-dash' ), "Google Analytics", 'manage_netwrok', 'gadash_settings', array( 'GADWP_Settings', 'general_settings_network' ), version_compare( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : GADWP_URL . 'admin/images/gadash-icon.png' );
				add_submenu_page( 'gadash_settings', __( "General Settings", 'ga-dash' ), __( "General Settings", 'ga-dash' ), 'manage_netwrok', 'gadash_settings', array( 'GADWP_Settings', 'general_settings_network' ) );
				add_submenu_page( 'gadash_settings', __( "Errors & Debug", 'ga-dash' ), __( "Errors & Debug", 'ga-dash' ), 'manage_network', 'gadash_errors_debugging', array( 'GADWP_Settings', 'errors_debugging' ) );
			}
		}

		/**
		 * Styles & Scripts conditional loading (based on current URI)
		 *
		 * @param
		 *            $hook
		 */
		public function load_styles_scripts( $hook ) {

			$new_hook = explode('_page_', $hook);

			if (isset($new_hook[1])){
				$new_hook = '_page_'.$new_hook[1];
			}else{
				$new_hook = $hook;
			}

			/*
			 * GADWP main stylesheet
			 */
			wp_enqueue_style( 'gadwp', GADWP_URL . 'admin/css/gadwp.css', null, GADWP_CURRENT_VERSION );

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

					wp_enqueue_script( 'gadwp_backend_item_reports', plugins_url( 'js/item-reports.js', __FILE__ ), array( 'gadwp-nprogress', 'googlejsapi', 'jquery', 'jquery-ui-dialog' ), GADWP_CURRENT_VERSION );

					/* @formatter:off */
					wp_localize_script( 'gadwp_backend_item_reports', 'gadwp_item_data', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'gadwp_backend_item_reports' ),
						'dateList' => array(
							'today' => __( "Today", 'ga-dash' ),
							'yesterday' => __( "Yesterday", 'ga-dash' ),
							'7daysAgo' => __( "Last 7 Days", 'ga-dash' ),
							'30daysAgo' => __( "Last 30 Days", 'ga-dash' ),
							'90daysAgo' => __( "Last 90 Days", 'ga-dash' ) ),
						'reportList' => array(
							'uniquePageviews' => __( "Unique Views", 'ga-dash' ),
							'users' => __( "Users", 'ga-dash' ),
							'organicSearches' => __( "Organic", 'ga-dash' ),
							'pageviews' => __( "Page Views", 'ga-dash' ),
							'visitBounceRate' => __( "Bounce Rate", 'ga-dash' ),
							'locations' => __( "Location", 'ga-dash' ),
							'referrers' => __( "Referrers", 'ga-dash' ),
							'searches' => __( "Searches", 'ga-dash' ),
							'trafficdetails' => __( "Traffic Details", 'ga-dash' ) ),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'ga-dash' ),
							__( "Traffic Mediums", 'ga-dash' ),
							__( "Visitor Type", 'ga-dash' ),
							__( "Social Networks", 'ga-dash' ),
							__( "Search Engines", 'ga-dash' ),
							__( "Unique Views", 'ga-dash' ),
							__( "Users", 'ga-dash' ),
							__( "Page Views", 'ga-dash' ),
							__( "Bounce Rate", 'ga-dash' ),
							__( "Organic Search", 'ga-dash' ),
							__( "Pages/Session", 'ga-dash' ),
							__( "Invalid response, more details in JavaScript Console (F12).", 'ga-dash' ),
							__( "Not enough data collected", 'ga-dash' ),
							__( "This report is unavailable", 'ga-dash' ),
							__( "report generated by", 'ga-dash' ) ),
						'colorVariations' => GADWP_Tools::variations( $this->gadwp->config->options['ga_dash_style'] ),
						'region' => $region )
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
			$settings_link = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=gadash_settings' ) ) . '">' . __( "Settings", 'ga-dash' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 *  Add an admin notice after a manual or atuomatic update
		 */
		function admin_notice() {
			if ( get_option( 'gadwp_got_updated' ) ) {
				?>
				<div class="updated">
				    <p><?php echo __( 'Google Analytics Dashboard for WP has been updated to version', 'ga-dash' ).' '.GADWP_CURRENT_VERSION.'. '.__('For details, check out the').sprintf(' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_notice&utm_medium=link&utm_content=release_notice&utm_campaign=gadwp">%s</a> ', __('documentation page', 'ga-dash') ). __('and the', 'ga-dash').sprintf(' <a href="%1$s">%2$s</a>', esc_url( get_admin_url( null, 'admin.php?page=gadash_settings' ) ), __('plugin&#39;s settings page', 'ga-dash') ).'.'; ?></p>
				</div>
				<?php
			}
		}
	}
}
