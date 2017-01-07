<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Tracking' ) ) {

	class GADWP_Tracking {

		private $gadwp;

		public function __construct() {
			$this->gadwp = GADWP();

			add_action( 'wp_head', array( $this, 'tracking_code' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		}

		public function load_scripts() {
			if ( $this->gadwp->config->options['ga_event_tracking'] ) {
				if ( wp_script_is( 'jquery' ) ) {
					wp_dequeue_script( 'jquery' );
				}
				wp_enqueue_script( 'jquery' );
			}
		}

		public function tracking_code() {
			if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_track_exclude'], true ) || ( $this->gadwp->config->options['ga_dash_excludesa'] && current_user_can( 'manage_network' ) ) ) {
				return;
			}
			$traking_mode = $this->gadwp->config->options['ga_dash_tracking'];
			$traking_type = $this->gadwp->config->options['ga_dash_tracking_type'];
			if ( $traking_mode > 0 ) {
				if ( ! $this->gadwp->config->options['ga_dash_tableid_jail'] ) {
					return;
				}
				if ( $traking_type == "classic" ) {
					echo "\n<!-- BEGIN GADWP v" . GADWP_CURRENT_VERSION . " Classic Tracking - https://deconf.com/google-analytics-dashboard-wordpress/ -->\n";
					if ( $this->gadwp->config->options['ga_event_tracking'] ) {
						require_once 'tracking/events-classic.php';
					}
					require_once 'tracking/code-classic.php';
					echo "\n<!-- END GADWP Classic Tracking -->\n\n";
				} else {

					if ( $this->gadwp->config->options['ga_event_tracking'] || $this->gadwp->config->options['ga_aff_tracking'] || $this->gadwp->config->options['ga_hash_tracking'] ) {

						$domaindata = GADWP_Tools::get_root_domain( esc_html( get_option( 'siteurl' ) ) );
						$root_domain = $domaindata ['domain'];

						wp_enqueue_script( 'gadwp-tracking-ua-events', GADWP_URL . 'front/tracking/js/ua-events.js', array( 'jquery' ), GADWP_CURRENT_VERSION );

						/* @formatter:off */
						wp_localize_script( 'gadwp-tracking-ua-events', 'gadwpUAEventsData', array(
							'options' => array(
								'event_tracking' => $this->gadwp->config->options['ga_event_tracking'],
								'event_downloads' => esc_js($this->gadwp->config->options['ga_event_downloads']),
								'event_bouncerate' => $this->gadwp->config->options['ga_event_bouncerate'],
								'aff_tracking' => $this->gadwp->config->options['ga_aff_tracking'],
								'event_affiliates' =>  esc_js($this->gadwp->config->options['ga_event_affiliates']),
								'hash_tracking' =>  $this->gadwp->config->options ['ga_hash_tracking'],
								'root_domain' => $root_domain,
								'event_timeout' => apply_filters( 'gadwp_uaevent_timeout', 100 ),
							),
						)
						);
						/* @formatter:on */

					}

					require_once 'tracking/universal-analytics.php';
					new GADWP_Universal_Analytics( $this->gadwp->config->options );
				}
			}
		}
	}
}
