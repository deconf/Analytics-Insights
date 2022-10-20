<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2017 Alin Marcu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();
if ( ! class_exists( 'AIWP_Tracking' ) ) {

	class AIWP_Tracking {

		private $aiwp;

		public $analytics;

		public $analytics_amp;

		public $tagmanager;

		public function __construct() {
			$this->aiwp = AIWP();
			$this->init();
		}

		public function tracking_code() { // Removed since 5.0
			AIWP_Tools::doing_it_wrong( __METHOD__, __( "This method is deprecated, read the documentation!", 'analytics-insights' ), '5.0' );
		}

		public static function aiwp_user_optout( $atts, $content = "" ) {
			if ( ! isset( $atts['html_tag'] ) ) {
				$atts['html_tag'] = 'a';
			}
			if ( 'a' == $atts['html_tag'] ) {
				return '<a href="#" class="aiwp_useroptout" onclick="gaOptout()">' . esc_html( $content ) . '</a>';
			} else if ( 'button' == $atts['html_tag'] ) {
				return '<button class="aiwp_useroptout" onclick="gaOptout()">' . esc_html( $content ) . '</button>';
			}
		}

		public function init() {
			// excluded roles
			if ( AIWP_Tools::check_roles( $this->aiwp->config->options['track_exclude'], true ) || ( $this->aiwp->config->options['superadmin_tracking'] && current_user_can( 'manage_network' ) ) ) {
				return;
			}

			if ( ( 'ga4tracking' == $this->aiwp->config->options['tracking_type'] || 'globalsitetag' == $this->aiwp->config->options['tracking_type'] || 'dualtracking' == $this->aiwp->config->options['tracking_type'] ) && ( $this->aiwp->config->options['tableid_jail'] || $this->aiwp->config->options['webstream_jail'] ) ) {

				require_once 'tracking-analytics.php';

				if ( 'globalsitetag' == $this->aiwp->config->options['tracking_type'] && $this->aiwp->config->options['tableid_jail'] ) {
					// Global Site Tag (gtag.js)
					if ( $this->aiwp->config->options['amp_tracking_analytics'] ) {
						$this->analytics_amp = new AIWP_Tracking_GlobalSiteTag_AMP();
					}
				}

				if ( 'ga4tracking' == $this->aiwp->config->options['tracking_type'] && $this->aiwp->config->options['webstream_jail'] ) {
					// Global Site Tag (gtag.js)
						if ( $this->aiwp->config->options['amp_tracking_analytics'] ) {
							$this->analytics_amp = new AIWP_Tracking_GA4_AMP();
						}
				}

				if ( 'dualtracking' == $this->aiwp->config->options['tracking_type'] && $this->aiwp->config->options['tableid_jail'] && $this->aiwp->config->options['webstream_jail'] ) {
					// Global Site Tag (gtag.js)
					if ( $this->aiwp->config->options['amp_tracking_analytics'] ) {
						$this->analytics_amp = new AIWP_Tracking_GlobalSiteTag_AMP();
						$this->analytics_amp = new AIWP_Tracking_GA4_AMP();
					}
				}

				$this->analytics = new AIWP_Tracking_GlobalSiteTag();

			}

			if ( 'universal' == $this->aiwp->config->options['tracking_type'] && $this->aiwp->config->options['tableid_jail'] ) {
				// Universal Analytics (analytics.js)
				require_once 'tracking-analytics.php';
					if ( $this->aiwp->config->options['amp_tracking_analytics'] ) {
						$this->analytics_amp = new AIWP_Tracking_Analytics_AMP();
					}

					$this->analytics = new AIWP_Tracking_Analytics();

			}

			if ( 'tagmanager' == $this->aiwp->config->options['tracking_type'] && $this->aiwp->config->options['web_containerid'] ) {
				// Tag Manager
				require_once 'tracking-tagmanager.php';
					if ( $this->aiwp->config->options['amp_tracking_tagmanager'] && $this->aiwp->config->options['amp_containerid'] ) {
						$this->tagmanager_amp = new AIWP_Tracking_TagManager_AMP();
					}

					$this->tagmanager = new AIWP_Tracking_TagManager();

			}

			add_shortcode( 'aiwp_useroptout', array( $this, 'aiwp_user_optout' ) );

		}
	}
}
