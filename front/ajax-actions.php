<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (! defined('ABSPATH'))
    exit();

if (! class_exists('GADWP_Frontend_Ajax')) {

    final class GADWP_Frontend_Ajax
    {

        private $gadwp;

        public function __construct()
        {
            $this->gadwp = GADWP();
            
            // Frontend Reports/Page action
            add_action('wp_ajax_gadash_get_frontend_pagereports', array(
                $this,
                'ajax_afterpost_reports'
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

        /**
         * Ajax handler for getting analytics data for frontend Views vs UniqueViews
         *
         * @return string|int
         */
        public function ajax_afterpost_reports()
        {
            if (! isset($_REQUEST['gadash_security_pagereports']) or ! wp_verify_nonce($_REQUEST['gadash_security_pagereports'], 'gadash_get_frontend_pagereports')) {
                wp_die(- 30);
            }
            $page_url = esc_url($_REQUEST['gadash_pageurl']);
            $post_id = (int) $_REQUEST['gadash_postid'];
            $query = $_REQUEST['query'];
            if (ob_get_length()) {
                ob_clean();
            }
            
            if (! GADWP_Tools::check_roles($this->gadwp->config->options['ga_dash_access_front']) or ! ($this->gadwp->config->options['ga_dash_frontend_stats'] or $this->gadwp->config->options['ga_dash_frontend_keywords'])) {
                wp_die(- 31);
            }
            if ($this->gadwp->config->options['ga_dash_token'] and $this->gadwp->config->options['ga_dash_tableid_jail']) {
                if (null === $this->gadwp->gapi_controller) {
                    $this->gadwp->gapi_controller = new GADWP_GAPI_Controller();
                }
            } else {
                wp_die(- 24);
            }
            if ($this->gadwp->config->options['ga_dash_tableid_jail']) {
                $projectId = $this->gadwp->config->options['ga_dash_tableid_jail'];
            } else {
                wp_die(- 25);
            }
            $profile_info = GADWP_Tools::get_selected_profile($this->gadwp->config->options['ga_dash_profile_list'], $projectId);
            if (isset($profile_info[4])) {
                $this->gadwp->gapi_controller->timeshift = $profile_info[4];
            } else {
                $this->gadwp->gapi_controller->timeshift = (int) current_time('timestamp') - time();
            }
            switch ($query) {
                case 'pageviews':
                    wp_send_json($this->gadwp->gapi_controller->frontend_afterpost_pageviews($projectId, $page_url, $post_id));
                    break;
                default:
                    wp_send_json($this->gadwp->gapi_controller->frontend_afterpost_searches($projectId, $page_url, $post_id));
                    break;
            }
        }
        // Frontend Widget Reports
        /**
         * Ajax handler for getting analytics data for frontend Widget
         *
         * @return string|int
         */
        public function ajax_frontend_widget()
        {
            if (! isset($_REQUEST['gadash_number']) or ! isset($_REQUEST['gadash_optionname']) or ! is_active_widget(false, false, 'gadash_frontend_widget')) {
                wp_die(- 30);
            }
            $widget_index = $_REQUEST['gadash_number'];
            $option_name = $_REQUEST['gadash_optionname'];
            $options = get_option($option_name);
            if (isset($options[$widget_index])) {
                $instance = $options[$widget_index];
            } else {
                wp_die(- 32);
            }
            switch ($instance['period']) { // make sure we have a valid request
                case '7daysAgo':
                    $period = '7daysAgo';
                    break;
                case '14daysAgo':
                    $period = '14daysAgo';
                    break;
                default:
                    $period = '30daysAgo';
                    break;
            }
            if (ob_get_length()) {
                ob_clean();
            }
            if ($this->gadwp->config->options['ga_dash_token'] and $this->gadwp->config->options['ga_dash_tableid_jail']) {
                if (null === $this->gadwp->gapi_controller) {
                    $this->gadwp->gapi_controller = new GADWP_GAPI_Controller();
                }
            } else {
                wp_die(- 24);
            }
            $projectId = $this->gadwp->config->options['ga_dash_tableid_jail'];
            $profile_info = GADWP_Tools::get_selected_profile($this->gadwp->config->options['ga_dash_profile_list'], $projectId);
            if (isset($profile_info[4])) {
                $this->gadwp->gapi_controller->timeshift = $profile_info[4];
            } else {
                $this->gadwp->gapi_controller->timeshift = (int) current_time('timestamp') - time();
            }
            wp_send_json($this->gadwp->gapi_controller->frontend_widget_stats($projectId, $period, (int) $instance['anonim']));
        }
    }
}
