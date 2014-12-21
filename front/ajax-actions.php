<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists('GADASH_Frontend_Ajax')) {

    final class GADASH_Frontend_Ajax
    {

        function __construct()
        {
            // Frontend Visits action
            add_action('wp_ajax_gadash_get_frontendvisits_data', array(
                $this,
                'ajax_afterpost_visits'
            ));
            // Frontend Seraches action
            add_action('wp_ajax_gadash_get_frontendsearches_data', array(
                $this,
                'ajax_afterpost_searches'
            ));
            // Frontend Widget actions
            add_action('wp_ajax_gadash_get_frontendwidget_data', array(
                $this,
                'ajax_frontend_widget'
            ));
            
            add_action('wp_ajax_nopriv_gadash_get_frontendwidget_data', array(
                $this,
                'ajax_frontend_widget'
            ));
        }
        
        // Frontend Visits Request
        /**
         * Ajax handler for getting analytics data for frontend Views vs UniqueViews
         *
         * @return string|int
         */
        function ajax_afterpost_visits()
        {
            global $GADASH_Config;
            
            $page_url = $_REQUEST['gadash_pageurl'];
            $post_id = $_REQUEST['gadash_postid'];
            
            if (! isset($_REQUEST['gadash_security_aaf']) or ! wp_verify_nonce($_REQUEST['gadash_security_aaf'], 'gadash_get_frontendvisits_data')) {
                print(json_encode(- 30));
                die();
            }
            
            if ($GADASH_Config->options['ga_dash_token'] and function_exists('curl_version') and $GADASH_Config->options['ga_dash_tableid_jail']) {
                include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
                global $GADASH_GAPI;
                include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
                $tools = new GADASH_Tools();
            } else {
                print(json_encode(- 24));
                die();
            }
            
            $projectId = $GADASH_Config->options['ga_dash_tableid_jail'];
            $profile_info = $tools->get_selected_profile($GADASH_Config->options['ga_dash_profile_list'], $projectId);
            if (isset($profile_info[4])) {
                $GADASH_GAPI->timeshift = $profile_info[4];
            } else {
                $GADASH_GAPI->timeshift = (int) current_time('timestamp') - time();
            }
            
            if (! $GADASH_GAPI->client->getAccessToken()) {
                print(json_encode(- 25));
                die();
            }
            
            $data_visits = $GADASH_GAPI->frontend_afterpost_visits($projectId, $page_url, $post_id);
            
            print($data_visits);
            
            die();
        }

        /**
         * Ajax handler for getting analytics data for frontend searches
         *
         * @return string|int
         */
        function ajax_afterpost_searches()
        {
            global $GADASH_Config;
            
            $page_url = $_REQUEST['gadash_pageurl'];
            $post_id = $_REQUEST['gadash_postid'];
            
            if (! isset($_REQUEST['gadash_security_aas']) or ! wp_verify_nonce($_REQUEST['gadash_security_aas'], 'gadash_get_frontendsearches_data')) {
                print(json_encode(- 30));
                die();
            }
            
            if ($GADASH_Config->options['ga_dash_token'] and function_exists('curl_version') and $GADASH_Config->options['ga_dash_tableid_jail']) {
                include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
                global $GADASH_GAPI;
                include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
                $tools = new GADASH_Tools();
            } else {
                print(json_encode(- 24));
                die();
            }
            
            $projectId = $GADASH_Config->options['ga_dash_tableid_jail'];
            $profile_info = $tools->get_selected_profile($GADASH_Config->options['ga_dash_profile_list'], $projectId);
            if (isset($profile_info[4])) {
                $GADASH_GAPI->timeshift = $profile_info[4];
            } else {
                $GADASH_GAPI->timeshift = (int) current_time('timestamp') - time();
            }
            
            if (! $GADASH_GAPI->client->getAccessToken()) {
                print(json_encode(- 25));
                die();
            }
            
            $data_keywords = $GADASH_GAPI->frontend_afterpost_searches($projectId, $page_url, $post_id);
            
            print($data_keywords);
            
            die();
        }
        // Frontend Visits Request
        /**
         * Ajax handler for getting analytics data for frontend Widget
         *
         * @return string|int
         */
        function ajax_frontend_widget()
        {
            global $GADASH_Config;
            
            $anonim = $_REQUEST['gadash_anonim'];
            $period = $_REQUEST['gadash_period'];
            $display = $_REQUEST['gadash_display'];
            
            if (! isset($_REQUEST['gadash_security_afw']) or ! wp_verify_nonce($_REQUEST['gadash_security_afw'], 'gadash_get_frontendwidget_data')) {
                print(json_encode(- 30));
                die();
            }
            
            if ($GADASH_Config->options['ga_dash_token'] and function_exists('curl_version') and $GADASH_Config->options['ga_dash_tableid_jail']) {
                include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
                global $GADASH_GAPI;
                include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
                $tools = new GADASH_Tools();
            } else {
                print(json_encode(- 24));
                die();
            }
            
            $projectId = $GADASH_Config->options['ga_dash_tableid_jail'];
            $profile_info = $tools->get_selected_profile($GADASH_Config->options['ga_dash_profile_list'], $projectId);
            if (isset($profile_info[4])) {
                $GADASH_GAPI->timeshift = $profile_info[4];
            } else {
                $GADASH_GAPI->timeshift = (int) current_time('timestamp') - time();
            }
            
            if (! $GADASH_GAPI->client->getAccessToken()) {
                print(json_encode(- 25));
                die();
            }
            
            $data_widget = $GADASH_GAPI->frontend_widget_stats($projectId, $period, $anonim);
            
            print(json_encode($data_widget));
            
            die();
        }
    }
}

$GADASH_Frontend_Ajax = new GADASH_Frontend_Ajax();