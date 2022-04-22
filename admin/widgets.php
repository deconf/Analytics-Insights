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
if ( ! class_exists( 'AIWP_Backend_Widgets' ) ) {

	class AIWP_Backend_Widgets {

		private $aiwp;

		public function __construct() {
			$this->aiwp = AIWP();
			if ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_back'] ) && ( 1 == $this->aiwp->config->options['dashboard_widget'] ) ) {
				add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
			}
		}

		public function add_widget() {
			wp_add_dashboard_widget( 'aiwp-widget', __( "Analytics Insights", 'analytics-insights' ), array( $this, 'dashboard_widget' ), $control_callback = null );
		}

		public function dashboard_widget() {
			$projectId = 0;
			if ( empty( $this->aiwp->config->options['token'] ) ) {
				echo '<p>' . __( "This plugin needs an authorization:", 'analytics-insights' ) . '</p><form action="' . menu_page_url( 'aiwp_settings', false ) . '" method="POST">' . get_submit_button( __( "Authorize Plugin", 'analytics-insights' ), 'secondary' ) . '</form>';
				return;
			}
			if ( current_user_can( 'manage_options' ) ) {
				if ( $this->aiwp->config->reporting_ready ) {
					if ( $this->aiwp->config->options['reporting_type'] ){
						$projectId = $this->aiwp->config->options['webstream_jail'];
					} else {
						$projectId = $this->aiwp->config->options['tableid_jail'];
					}
				} else {
					echo '<p>' . __( "An admin should asign a default Google Analytics property.", 'analytics-insights' ) . '</p><form action="' . menu_page_url( 'aiwp_settings', false ) . '" method="POST">' . get_submit_button( __( "Select Domain", 'analytics-insights' ), 'secondary' ) . '</form>';
					return;
				}
			} else {
				if ( $this->aiwp->config->reporting_ready ) {
					if ( $this->aiwp->config->options['reporting_type'] ){
						$projectId = $this->aiwp->config->options['webstream_jail'];
					} else {
						$projectId = $this->aiwp->config->options['tableid_jail'];
					}
				} else {
					echo '<p>' . __( "An admin should asign a default Google Analytics property.", 'analytics-insights' ) . '</p><form action="' . menu_page_url( 'aiwp_settings', false ) . '" method="POST">' . get_submit_button( __( "Select Domain", 'analytics-insights' ), 'secondary' ) . '</form>';
					return;
				}
			}
			if ( ! ( $projectId ) ) {
				echo '<p>' . __( "Something went wrong while retrieving property data. You need to create and properly configure a Google Analytics account:", 'analytics-insights' ) . '</p> <form action="https://deconf.com/how-to-set-up-google-analytics-on-your-website/" method="POST">' . get_submit_button( __( "Find out more!", 'analytics-insights' ), 'secondary' ) . '</form>';
				return;
			}
			?>
<div id="aiwp-window-1"></div>
<?php
		}
	}
}
