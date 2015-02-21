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
      // Add custom column
      add_filter('manage_posts_columns', array(
        $this,
        'add_stats_column'
      ));
      // Populate custom column
      add_action('manage_posts_custom_column', array(
        $this,
        'display_post_stats'
      ), 10, 2);
    }

    function display_post_stats($column, $post_id)
    {
      global $GADASH_Config, $wp_version;
      
      if (version_compare($wp_version, '3.8.0', '>=')) {
        echo '<a id="gadwp-' . $post_id . '" href="#' . $post_id . '" class="gadwp-icon dashicons-before dashicons-chart-area"></a>';
      } else {
        echo '<a id="gadwp-' . $post_id . '" href="#' . $post_id . '"><img class="gadwp-icon-oldwp" src="' . $GADASH_Config->plugin_url . '/admin/images/gadash-icon.png"</a>';
      }
    }

    function add_stats_column($columns)
    {
      return array_merge($columns, array(
        'gadwp_stats' => __('Analytics', 'ga-dash')
      ));
    }
  }
}

if (is_admin()) {
  $GADASH_Back_Stats = new GADASH_Back_Stats();
}
