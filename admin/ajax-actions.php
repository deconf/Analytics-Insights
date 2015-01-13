<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists('GADASH_Backend_Ajax')) {

    final class GADASH_Backend_Ajax
    {

        function __construct()
        {
            // Backend Widget Realtime action
            add_action('wp_ajax_gadashadmin_get_realtime', array(
                $this,
                'ajax_adminwidget_realtime'
            ));
            // Admin Widget get Reports action
            add_action('wp_ajax_gadashadmin_get_widgetreports', array(
                $this,
                'ajax_adminwidget_reports'
            ));
        }

        /**
         * Ajax handler for getting reports for Admin Widget
         *
         * @return string|int
         */
        function ajax_adminwidget_reports()
        {
            global $GADASH_Config;
            
            $projectId = $_REQUEST['projectId'];
            $from = $_REQUEST['from'];
            $to = $_REQUEST['to'];
            $query = $_REQUEST['query'];
            
            ob_clean();
            
            if (! isset($_REQUEST['gadashadmin_security_widget_reports']) or ! wp_verify_nonce($_REQUEST['gadashadmin_security_widget_reports'], 'gadashadmin_get_widgetreports')) {
                print(json_encode(- 30));
                die();
            }
            
            /*
             * Include Tools
             */
            include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
            $tools = new GADASH_Tools();
            
            if (! $tools->check_roles($GADASH_Config->options['ga_dash_access_back'])) {
                print(json_encode(- 31));
                die();
            }
            
            if ($GADASH_Config->options['ga_dash_token'] and function_exists('curl_version') and $projectId and $from and $to) {
                include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
                global $GADASH_GAPI;
            } else {
                print(json_encode(- 24));
                die();
            }
            
            switch ($query) {
                case 'referrers':
                    print($GADASH_GAPI->get_referrers($projectId, $from, $to));
                    break;
                case 'contentpages':
                    print($GADASH_GAPI->get_contentpages($projectId, $from, $to));
                    break;
                case 'locations':
                    print($GADASH_GAPI->get_locations($projectId, $from, $to));
                    break;
                case 'bottomstats':
                    print(json_encode($GADASH_GAPI->get_bottomstats($projectId, $from, $to)));
                    break;
                case 'trafficchannels':
                    print($GADASH_GAPI->get_trafficchannels($projectId, $from, $to));
                    break;
                case 'medium':
                    print($GADASH_GAPI->get_trafficdetails($projectId, $from, $to, 'medium'));
                    break;
                case 'visitorType':
                    print($GADASH_GAPI->get_trafficdetails($projectId, $from, $to, 'visitorType'));
                    break;
                case 'socialNetwork':
                    print($GADASH_GAPI->get_trafficdetails($projectId, $from, $to, 'socialNetwork'));
                    break;
                case 'source':
                    print($GADASH_GAPI->get_trafficdetails($projectId, $from, $to, 'source'));
                    break;                    
                case 'searches':
                    print($GADASH_GAPI->get_searches($projectId, $from, $to));
                    break;
                default:
                    print($GADASH_GAPI->get_mainreport($projectId, $from, $to, $query));
                    break;
            }

            die();
        }
        
        // Real-Time Request
        /**
         * Ajax handler for getting realtime analytics data for Admin widget
         *
         * @return string|int
         */
        function ajax_adminwidget_realtime()
        {
            global $GADASH_Config;
            
            $projectId = $_REQUEST['projectId'];
            
            ob_clean();
            
            if (! isset($_REQUEST['gadashadmin_security_widgetrealtime']) or ! wp_verify_nonce($_REQUEST['gadashadmin_security_widgetrealtime'], 'gadashadmin_get_realtime')) {
                print(json_encode(- 30));
                die();
            }
            
            /*
             * Include Tools
             */
            include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
            $tools = new GADASH_Tools();
            
            if (! $tools->check_roles($GADASH_Config->options['ga_dash_access_back'])) {
                print(json_encode(- 31));
                die();
            }
            
            if ($GADASH_Config->options['ga_dash_token'] and function_exists('curl_version') and $projectId) {
                include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
                global $GADASH_GAPI;
            } else {
                print(json_encode(- 24));
                die();
            }
            
            print($GADASH_GAPI->gadash_realtime_data($projectId));
            
            die();
        }
    }
}

$GADASH_Backend_Ajax = new GADASH_Backend_Ajax();
