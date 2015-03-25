<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists('GADASH_Back_Stats')) {

  final class GADASH_Back_Stats
  {

    function __construct()
    {
      global $GADASH_Config;
      $tools = new GADASH_Tools();
      if (! $tools->check_roles($GADASH_Config->options['ga_dash_access_back']) or 0 == $GADASH_Config->options['item_reports']) {
        return;
      }
      // Add custom column in Posts List
      add_filter('manage_posts_columns', array(
        $this,
        'add_stats_column'
      ));
      // Populate custom column in Posts List
      add_action('manage_posts_custom_column', array(
        $this,
        'display_item_stats'
      ), 10, 2);
      // Add custom column in Pages List
      add_filter('manage_pages_columns', array(
        $this,
        'add_stats_column'
      ));
      // Populate custom column in Pages List
      add_action('manage_pages_custom_column', array(
        $this,
        'display_item_stats'
      ), 10, 2);
    }

    function display_item_stats($column, $id)
    {
      global $GADASH_Config, $wp_version;
      
      if ($column != 'gadwp_stats'){
        return;
      }
      
      if (version_compare($wp_version, '3.8.0', '>=')) {
        echo '<a id="gadwp-' . $id . '" title="' . get_the_title($id) . '" href="#' . $id . '" class="gadwp-icon dashicons-before dashicons-chart-area"></a>';
      } else {
        echo '<a id="gadwp-' . $id . '" title="' . get_the_title($id) . '" href="#' . $id . '"><img class="gadwp-icon-oldwp" src="' . $GADASH_Config->plugin_url . '/admin/images/gadash-icon.png"</a>';
      }
    }

    function add_stats_column($columns)
    {
      return array_merge($columns, array(
        'gadwp_stats' => __('Analytics', 'ga-dash'),
      ));
    }
  }
}

if (is_admin()) {
  $GADASH_Back_Stats = new GADASH_Back_Stats();
}
