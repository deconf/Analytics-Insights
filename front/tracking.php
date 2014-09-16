<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists ( 'GADASH_Tracking' )) {
	class GADASH_Tracking {
		function __construct() {
			add_action ( 'wp_head', array (
					$this,
					'ga_dash_tracking' 
			) );
			
			add_action ( 'init', array (
			$this,
			'ga_dash_tracking_events'
					) );			
		}
		
		function ga_dash_tracking_events() {
			global $GADASH_Config;
			/*
			 * Include Tools
			*/
			include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
			$tools = new GADASH_Tools ();
				
			if ($tools->check_roles ( $GADASH_Config->options ['ga_track_exclude'], true )) {
				return;
			}
				
			if ($GADASH_Config->options ['ga_dash_tracking'] > 0) {
		
				if (! $GADASH_Config->options ['ga_dash_tableid_jail']) {
					return;
				}

				$bouncerate = (isset ( $GADASH_Config->options ['ga_event_bouncerate'] ) && $GADASH_Config->options ['ga_event_bouncerate']) ? '{"nonInteraction": "1"}' : '{"nonInteraction": "0"}';
						
				if ($GADASH_Config->options ['ga_dash_tracking'] == "classic") {

					if ($GADASH_Config->options ['ga_event_tracking']) {
						wp_register_script ( 'gadash_events', plugins_url ( 'tracking/events-classic.js', __FILE__ ), array('jquery'), NULL, $GADASH_Config->options['ga_event_pos']);
						wp_localize_script ( 'gadash_events', 'gadash_eventsdata', array (
						'extensions' => esc_js ( $GADASH_Config->options ['ga_event_downloads'] ),
						'siteurl' => esc_html(get_option('siteurl'))
						) );
						wp_enqueue_script ( 'gadash_events' );						
					}
						
				} else {
					if ($GADASH_Config->options ['ga_event_tracking']) {
						wp_register_script ( 'gadash_events', plugins_url ( 'tracking/events-universal.js', __FILE__ ), array('jquery'), NULL, $GADASH_Config->options['ga_event_pos']);
						wp_localize_script ( 'gadash_events', 'gadash_eventsdata', array (
						'extensions' => esc_js ( $GADASH_Config->options ['ga_event_downloads'] ),
						'bouncerate' => $bouncerate,
						'siteurl' => esc_html(get_option('siteurl'))
						) );
						wp_enqueue_script ( 'gadash_events' );
					}
				}
			}
		}		
		
		
		function ga_dash_tracking($head) {
			global $GADASH_Config;
			/*
			 * Include Tools
			 */
			include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
			$tools = new GADASH_Tools ();
			
			if ($tools->check_roles ( $GADASH_Config->options ['ga_track_exclude'], true )) {
				return;
			}
			
			if ($GADASH_Config->options ['ga_dash_tracking'] > 0) {
				
				if (! $GADASH_Config->options ['ga_dash_tableid_jail']) {
					return;
				}
				
				if ($GADASH_Config->options ['ga_dash_tracking'] == "classic") {
					echo "\n<!-- BEGIN GADWP v" . GADWP_CURRENT_VERSION . " Classic Tracking - https://deconf.com/google-analytics-dashboard-wordpress/ -->\n";
					require_once 'tracking/code-classic.php';
					echo "\n<!-- END GADWP Classic Tracking -->\n\n";
				} else {
					echo "\n<!-- BEGIN GADWP v" . GADWP_CURRENT_VERSION . " Universal Tracking - https://deconf.com/google-analytics-dashboard-wordpress/ -->\n";
					require_once 'tracking/code-universal.php';
					echo "\n<!-- END GADWP Universal Tracking -->\n\n";
				}
			}
		}
	}
}

if (! is_admin ()) {
	$GLOBALS ['GADASH_Tracking'] = new GADASH_Tracking ();
}
