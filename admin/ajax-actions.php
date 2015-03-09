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
      // Admin Widget action
      add_action('wp_ajax_gadash_get_widgetreports', array(
        $this,
        'ajax_widget_reports'
      ));
      // Items action
      add_action('wp_ajax_gadwp_get_ItemReports', array(
        $this,
        'ajax_item_reports'
      ));
    }

    /**
     * Ajax handler for Items (posts/pages)
     *
     * @return json|int
     */
    function ajax_item_reports()
    {
      global $GADASH_Config;
      if (! isset($_REQUEST['gadwp_security_item_reports']) or ! wp_verify_nonce($_REQUEST['gadwp_security_item_reports'], 'gadwp_get_itemreports')) {
        wp_die(- 30);
      }
      $from = $_REQUEST['from'];
      $to = $_REQUEST['to'];
      $query = $_REQUEST['query'];
      $filter_id = $_REQUEST['filter'];
      if (ob_get_length()) {
        ob_clean();
      }
      $tools = new GADASH_Tools();
      if (! $tools->check_roles($GADASH_Config->options['ga_dash_access_back']) or 1 == $GADASH_Config->options['item_reports']) {
        wp_die(- 31);
      }
      if ($GADASH_Config->options['ga_dash_token'] and $GADASH_Config->options['ga_dash_tableid_jail'] and $from and $to) {
        include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
        global $GADASH_GAPI;
      } else {
        wp_die(- 24);
      }
      $projectId = $GADASH_Config->options['ga_dash_tableid_jail'];
      $profile_info = $tools->get_selected_profile($GADASH_Config->options['ga_dash_profile_list'], $projectId);
      if (isset($profile_info[4])) {
        $GADASH_GAPI->timeshift = $profile_info[4];
      } else {
        $GADASH_GAPI->timeshift = (int) current_time('timestamp') - time();
      }
      //strip the protocol & domain 
      $uri = str_replace($tools->strip_protocol($profile_info[3]),'',$tools->strip_protocol(get_permalink($filter_id)));
      //make sure the path starts with '/'
      if ($uri){
        $uri = '/'.ltrim($uri,'/');
      }  
      //allow URI correction before sending an API request
      $filter = apply_filters('gadwp_backenditem_uri', $uri);
      $GADASH_GAPI->get($projectId, $query, $from, $to, $filter);
    }

    /**
     * Ajax handler for Admin Widget
     *
     * @return json|int
     */
    function ajax_widget_reports()
    {
      global $GADASH_Config;
      if (! isset($_REQUEST['gadash_security_widget_reports']) or ! wp_verify_nonce($_REQUEST['gadash_security_widget_reports'], 'gadash_get_widgetreports')) {
        wp_die(- 30);
      }
      $projectId = $_REQUEST['projectId'];
      $from = $_REQUEST['from'];
      $to = $_REQUEST['to'];
      $query = $_REQUEST['query'];
      if (ob_get_length()) {
        ob_clean();
      }
      $tools = new GADASH_Tools();
      if (! $tools->check_roles($GADASH_Config->options['ga_dash_access_back']) or 1 == $GADASH_Config->options['dashboard_widget']) {
        wp_die(- 31);
      }
      if ($GADASH_Config->options['ga_dash_token'] and $projectId and $from and $to) {
        include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
        global $GADASH_GAPI;
      } else {
        wp_die(- 24);
      }
      $profile_info = $tools->get_selected_profile($GADASH_Config->options['ga_dash_profile_list'], $projectId);
      if (isset($profile_info[4])) {
        $GADASH_GAPI->timeshift = $profile_info[4];
      } else {
        $GADASH_GAPI->timeshift = (int) current_time('timestamp') - time();
      }
      $GADASH_GAPI->get($projectId, $query, $from, $to);
    }
  }
}
$GADASH_Backend_Ajax = new GADASH_Backend_Ajax();
