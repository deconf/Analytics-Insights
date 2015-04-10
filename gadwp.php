<?php
/**
 * Plugin Name: Google Analytics Dashboard for WP
 * Plugin URI: https://deconf.com
 * Description: Displays Google Analytics Reports and Real-Time Statistics in your Dashboard. Automatically inserts the tracking code in every page of your website.
 * Author: Alin Marcu
 * Version: 4.6
 * Author URI: https://deconf.com
 */

// Exit if accessed directly
if (! defined('ABSPATH'))
    exit();

if (! class_exists('GADWP_Manager')) {

    final class GADWP_Manager
    {

        private static $instance = null;

        public $config = null;

        public $frontend_actions = null;

        public $backend_actions = null;

        public $tracking = null;

        public $frontend_item_reports = null;

        public $backend_setup = null;

        public $backend_widgets = null;

        public $backend_item_reports = null;

        public $gapi_controller = null;

        /**
         * Construct forbidden
         */
        private function __construct()
        {
            if (null !== self::$instance) {
                _doing_it_wrong(__FUNCTION__, __("This is not allowed, read the documentation!", 'ga-dash'), '4.6');
            }
        }

        /**
         * Clone warning
         */
        private function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __("This is not allowed, read the documentation!", 'ga-dash'), '4.6');
        }

        /**
         * Wakeup warning
         */
        private function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __("This is not allowed, read the documentation!", 'ga-dash'), '4.6');
        }

        /**
         * Creates a single instance for GADWP and makes sure only one instance is present in memory.
         *
         * @return GADWP_Manager
         */
        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
                self::$instance->setup();
                self::$instance->config = new GADWP_Config();
            }
            return self::$instance;
        }

        /**
         * Defines constants and loads required classes
         */
        private function setup()
        {
            
            // Plugin Version
            if (! defined('GADWP_CURRENT_VERSION')) {
                define('GADWP_CURRENT_VERSION', '4.6');
            }
            
            // Plugin Path
            if (! defined('GADWP_DIR')) {
                define('GADWP_DIR', plugin_dir_path(__FILE__));
            }
            
            // Plugin URL
            if (! defined('GADWP_URL')) {
                define('GADWP_URL', plugin_dir_url(__FILE__));
            }
            
            // Plugin main File
            if (! defined('GADWP_FILE')) {
                define('GADWP_FILE', __FILE__);
            }
            
            /*
             * Load Tools class
             */
            include_once (GADWP_DIR . 'tools/tools.php');
            
            /*
             * Load Config class
             */
            include_once (GADWP_DIR . 'config.php');
            
            /*
             * Load GAPI Controller class
             */
            include_once (GADWP_DIR . 'tools/gapi.php');
            
            /*
             * Load Frontend Ajax class
             */
            include_once (GADWP_DIR . 'front/ajax-actions.php');
            
            /*
             * Load Backend Ajax class
            */
            include_once (GADWP_DIR . 'admin/ajax-actions.php');

            /*
             * Backend ajax actions instance
             */
            self::$instance->backend_actions = new GADWP_Backend_Ajax();
            
            /*
             * Frontend ajax actions instance
            */
            self::$instance->frontend_actions = new GADWP_Frontend_Ajax();

            /*
             * Plugin i18n
             */
            add_action('init', array(
                self::$instance,
                'load_i18n'
            ));            
            
            if (!defined('DOING_AJAX') || (defined('DOING_AJAX') && !DOING_AJAX)) { //Let's keep things lite!
                
                /*
                 * Include Install
                 */
                include_once (GADWP_DIR . 'install/install.php');
                register_activation_hook(GADWP_FILE, array(
                    'GADWP_Install',
                    'install'
                ));
                
                /*
                 * Include Uninstall
                 */
                include_once (GADWP_DIR . 'install/uninstall.php');
                register_uninstall_hook(GADWP_FILE, array(
                    'GADWP_Uninstall',
                    'uninstall'
                ));
                
                /*
                 * Load tracking class
                 */
                include_once (GADWP_DIR . 'front/tracking.php');
                
                /*
                 * Load Frontend Item Reports class
                 */
                include_once (GADWP_DIR . 'front/item-reports.php');
                
                /*
                 * Load Backend Setup class
                 */
                include_once (GADWP_DIR . 'admin/setup.php');
                
                /*
                 * Load Backend Widget class
                 */
                include_once (GADWP_DIR . 'admin/widgets.php');
                
                /*
                 * Load Backend Item Reports class
                 */
                include_once (GADWP_DIR . 'admin/item-reports.php');
                
                /*
                 * Load Frontend Widgets
                 */
                include_once (GADWP_DIR . 'front/widgets.php');
                
                /*
                 * Add Frontend Widgets
                 */
                add_action('widgets_init', array(
                    self::$instance,
                    'add_frontend_widget'
                ));
                
                /*
                 * Plugin Init
                 */
                add_action('init', array(
                    self::$instance,
                    'on_init'
                ));
            } 
        }
        
        /**
         * Load i18n
         */
        public function load_i18n(){
            load_plugin_textdomain('ga-dash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
        }            

        /**
         * Register Frontend Widgets
         */
        public function add_frontend_widget()
        {
            register_widget('GADWP_Frontend_Widget');
        }

        /**
         * Conditional instances creation
         */
        public function on_init()
        {
            if (is_admin()) {
                if (GADWP_Tools::check_roles(self::$instance->config->options['ga_dash_access_back'])) {
                    /*
                     * Backend Setup instance
                     */
                    self::$instance->backend_setup = new GADWP_Backend_Setup();
                    
                    /*
                     * Backend Widget instance
                     */
                    if (self::$instance->config->options['dashboard_widget']) {
                        self::$instance->backend_widgets = new GADWP_Backend_Widgets();
                    }
                    /*
                     * Backend Item Reports instance
                     */
                    if (self::$instance->config->options['item_reports']) {
                        self::$instance->backend_item_reports = new GADWP_Backend_Item_Reports();
                    }
                }
            } else {
                /*
                 * Frontend Item Reports instance
                 */
                if (GADWP_Tools::check_roles(self::$instance->config->options['ga_dash_access_front']) && (self::$instance->config->options['ga_dash_frontend_stats'] || self::$instance->config->options['ga_dash_frontend_keywords'])) {
                    self::$instance->frontend_item_reports = new GADWP_Frontend_Item_Reports();
                }
                
                /*
                 * Tracking instance
                 */
                if (! GADWP_Tools::check_roles(self::$instance->config->options['ga_track_exclude'], true) && self::$instance->config->options['ga_dash_tracking']) {
                    self::$instance->tracking = new GADWP_Tracking();
                }
            }
        }
    }
}

/**
 * Returns a unique instance of GADWP
 */
function GADWP()
{
    return GADWP_Manager::instance();
}

/*
 * Start GADWP
 */
GADWP();
