<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists('GADASH_Back_Setup')) {

    final class GADASH_Back_Setup
    {

        public function __construct()
        {
            global $GADASH_Config;
            // Styles & Scripts
            add_action('admin_enqueue_scripts', array(
                $this,
                'load_styles_scripts'
            ));
            // Site Menu
            add_action('admin_menu', array(
                $this,
                'site_menu'
            ));
            // Network Menu
            add_action('network_admin_menu', array(
                $this,
                'network_menu'
            ));
            // Settings link
            add_filter("plugin_action_links_" . plugin_basename($GADASH_Config->plugin_path) . '/gadwp.php', array(
                $this,
                'settings_link'
            ));
            // Error bubble
            add_action('admin_print_scripts', array(
                $this,
                'draw_error_bubble'
            ), 10000);
        }

        /**
         * Error bubble for Errors & Debug
         */
        public function draw_error_bubble()
        {
            $bubble_msg = '!';
            if (get_transient('ga_dash_gapi_errors')) {
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
        public function site_menu()
        {
            global $GADASH_Config;
            global $wp_version;
            if (current_user_can('manage_options')) {
                include ($GADASH_Config->plugin_path . '/admin/settings.php');
                add_menu_page(__("Google Analytics", 'ga-dash'), 'Google Analytics', 'manage_options', 'gadash_settings', array(
                    'GADASH_Settings',
                    'general_settings'
                ), version_compare($wp_version, '3.8.0', '>=') ? 'dashicons-chart-area' : $GADASH_Config->plugin_url . '/admin/images/gadash-icon.png');
                add_submenu_page('gadash_settings', __("General Settings", 'ga-dash'), __("General Settings", 'ga-dash'), 'manage_options', 'gadash_settings', array(
                    'GADASH_Settings',
                    'general_settings'
                ));
                add_submenu_page('gadash_settings', __("Backend Settings", 'ga-dash'), __("Backend Settings", 'ga-dash'), 'manage_options', 'gadash_backend_settings', array(
                    'GADASH_Settings',
                    'backend_settings'
                ));
                add_submenu_page('gadash_settings', __("Frontend Settings", 'ga-dash'), __("Frontend Settings", 'ga-dash'), 'manage_options', 'gadash_frontend_settings', array(
                    'GADASH_Settings',
                    'frontend_settings'
                ));
                add_submenu_page('gadash_settings', __("Tracking Code", 'ga-dash'), __("Tracking Code", 'ga-dash'), 'manage_options', 'gadash_tracking_settings', array(
                    'GADASH_Settings',
                    'tracking_settings'
                ));
                add_submenu_page('gadash_settings', __("Errors & Debug", 'ga-dash'), __("Errors & Debug", 'ga-dash'), 'manage_options', 'gadash_errors_debugging', array(
                    'GADASH_Settings',
                    'errors_debugging'
                ));
            }
        }

        /**
         * Add Network Menu
         */
        public function network_menu()
        {
            global $GADASH_Config;
            global $wp_version;
            if (current_user_can('manage_netwrok')) {
                include ($GADASH_Config->plugin_path . '/admin/settings.php');
                add_menu_page(__("Google Analytics", 'ga-dash'), "Google Analytics", 'manage_netwrok', 'gadash_settings', array(
                    'GADASH_Settings',
                    'general_settings_network'
                ), version_compare($wp_version, '3.8.0', '>=') ? 'dashicons-chart-area' : $GADASH_Config->plugin_url . '/admin/images/gadash-icon.png');
                add_submenu_page('gadash_settings', __("General Settings", 'ga-dash'), __("General Settings", 'ga-dash'), 'manage_netwrok', 'gadash_settings', array(
                    'GADASH_Settings',
                    'general_settings_network'
                ));
                add_submenu_page('gadash_settings', __("Errors & Debug", 'ga-dash'), __("Errors & Debug", 'ga-dash'), 'manage_network', 'gadash_errors_debugging', array(
                    'GADASH_Settings',
                    'errors_debugging'
                ));
            }
        }

        /**
         * Styles & Scripts conditional loading (based on current URI)
         *
         * @param
         *            $hook
         */
        public function load_styles_scripts($hook)
        {
            global $GADASH_Config;
            $tools = new GADASH_Tools();
            /*
             * GADWP main stylesheet
             */
            wp_enqueue_style('gadwp', $GADASH_Config->plugin_url . '/admin/css/gadwp.css', null, GADWP_CURRENT_VERSION);
            /*
             * Dashboard Widgets Styles & Scripts
             */
            $widgets_hooks = array(
                'index.php'
            );
            if (in_array($hook, $widgets_hooks)) {
                wp_enqueue_style('gadwp-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.css', null, GADWP_CURRENT_VERSION);
                wp_enqueue_style('gadwp-admin-widgets', $GADASH_Config->plugin_url . '/admin/css/gadwp.css', null, GADWP_CURRENT_VERSION);
                wp_enqueue_script('gadwp-admin-widgets', plugins_url('js/widgets.js', __FILE__), array(
                    'jquery'
                ), GADWP_CURRENT_VERSION);
                if (! wp_script_is('googlejsapi')) {
                    wp_register_script('googlejsapi', 'https://www.google.com/jsapi');
                    wp_enqueue_script('googlejsapi');
                }
                wp_enqueue_script('gadwp-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.js', array(
                    'jquery'
                ), GADWP_CURRENT_VERSION);
            }
            /*
             * Posts/Pages List Styles & Scripts
             */
            $contentstats_hooks = array(
                'edit.php'
            );
            if (in_array($hook, $contentstats_hooks)) {
                if (! $tools->check_roles($GADASH_Config->options['ga_dash_access_back']) or 0 == $GADASH_Config->options['item_reports']) {
                    return;
                }
                wp_enqueue_style('gadwp-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.css', null, GADWP_CURRENT_VERSION);
                wp_enqueue_style('gadwp_itemreports', $GADASH_Config->plugin_url . '/admin/css/item-reports.css', null, GADWP_CURRENT_VERSION);
                $tools->getcountrycodes();
                if ($GADASH_Config->options['ga_target_geomap'] and isset($tools->country_codes[$GADASH_Config->options['ga_target_geomap']])) {
                    $region = $GADASH_Config->options['ga_target_geomap'];
                } else {
                    $region = false;
                }
                wp_enqueue_style("wp-jquery-ui-dialog");
                if (! wp_script_is('googlejsapi')) {
                    wp_register_script('googlejsapi', 'https://www.google.com/jsapi');
                }
                wp_enqueue_script('gadwp-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.js', array(
                    'jquery'
                ), GADWP_CURRENT_VERSION);
                wp_enqueue_script('gadwp_itemreports', plugins_url('js/item-reports.js', __FILE__), array(
                    'gadwp-nprogress',
                    'googlejsapi',
                    'jquery',
                    'jquery-ui-dialog'
                ), GADWP_CURRENT_VERSION);
                wp_localize_script('gadwp_itemreports', 'gadwp_item_data', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'security' => wp_create_nonce('gadwp_get_itemreports'),
                    'dateList' => array(
                        'today' => __("Today", 'ga-dash'),
                        'yesterday' => __("Yesterday", 'ga-dash'),
                        '7daysAgo' => __("Last 7 Days", 'ga-dash'),
                        '30daysAgo' => __("Last 30 Days", 'ga-dash'),
                        '90daysAgo' => __("Last 90 Days", 'ga-dash')
                    ),
                    'reportList' => array(
                        'uniquePageviews' => __("Unique Views", 'ga-dash'),
                        'users' => __("Users", 'ga-dash'),
                        'organicSearches' => __("Organic", 'ga-dash'),
                        'pageviews' => __("Page Views", 'ga-dash'),
                        'visitBounceRate' => __("Bounce Rate", 'ga-dash'),
                        'locations' => __("Location", 'ga-dash'),
                        'referrers' => __("Referrers", 'ga-dash'),
                        'searches' => __("Searches", 'ga-dash'),
                        'trafficdetails' => __("Traffic Details", 'ga-dash')
                    ),
                    'i18n' => array(
                        __("A JavaScript Error is blocking plugin resources!", 'ga-dash'),
                        __("Traffic Mediums", 'ga-dash'),
                        __("Visitor Type", 'ga-dash'),
                        __("Social Networks", 'ga-dash'),
                        __("Search Engines", 'ga-dash'),
                        __("Unique Views", 'ga-dash'),
                        __("Users", 'ga-dash'),
                        __("Page Views", 'ga-dash'),
                        __("Bounce Rate", 'ga-dash'),
                        __("Organic Search", 'ga-dash'),
                        __("Pages/Session", 'ga-dash'),
                        __("Invalid response, more details in JavaScript Console (F12).", 'ga-dash'),
                        __("Not enough data collected", 'ga-dash'),
                        __("This report is unavailable", 'ga-dash'),
                        __("report generated by", 'ga-dash')
                    ),
                    'colorVariations' => $tools->variations($GADASH_Config->options['ga_dash_style']),
                    'region' => $region
                ));
            }
            /*
             * Settings Styles & Scripts
             */
            $settings_hooks = array(
                'toplevel_page_gadash_settings',
                'google-analytics_page_gadash_backend_settings',
                'google-analytics_page_gadash_frontend_settings',
                'google-analytics_page_gadash_tracking_settings',
                'google-analytics_page_gadash_errors_debugging'
            );
            
            if (in_array($hook, $settings_hooks)) {
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
                wp_enqueue_script('wp-color-picker-script-handle', plugins_url('js/wp-color-picker-script.js', __FILE__), array(
                    'wp-color-picker'
                ), false, true);
                wp_enqueue_script('gadwp-settings', plugins_url('js/settings.js', __FILE__), array(
                    'jquery'
                ), GADWP_CURRENT_VERSION);
            }
        }

        /**
         * Add "Settings" link in Plugins List
         *
         * @param
         *            $links
         * @return array
         */
        public function settings_link($links)
        {
            $settings_link = '<a href="' . get_admin_url(null, 'admin.php?page=gadash_settings') . '">' . __("Settings", 'ga-dash') . '</a>';
            array_unshift($links, $settings_link);
            return $links;
        }
    }
}
if (is_admin()) {
    $GADASH_Back_Setup = new GADASH_Back_Setup();
}
