<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();
if ( ! class_exists( 'AIWP_Backend_Ajax' ) ) {

	final class AIWP_Backend_Ajax {

		private $aiwp;

		public function __construct() {
			$this->aiwp = AIWP();
			if ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_back'] ) && ( ( 1 == $this->aiwp->config->options['backend_item_reports'] ) || ( 1 == $this->aiwp->config->options['dashboard_widget'] ) ) ) {
				// Items action
				add_action( 'wp_ajax_aiwp_backend_item_reports', array( $this, 'ajax_item_reports' ) );
			}
			if ( current_user_can( 'manage_options' ) ) {
				// Admin Widget action
				add_action( 'wp_ajax_aiwp_dismiss_notices', array( $this, 'ajax_dismiss_notices' ) );
			}
		}

		/**
		 * Ajax handler for Item Reports
		 *
		 * @return mixed|int
		 */
		public function ajax_item_reports() {
			if ( ! isset( $_POST['aiwp_security_backend_item_reports'] ) || ! wp_verify_nonce( $_POST['aiwp_security_backend_item_reports'], 'aiwp_backend_item_reports' ) ) {
				wp_die( - 30 );
			}
			if ( isset( $_POST['projectId'] ) && $this->aiwp->config->options['switch_profile'] && 'false' !== $_POST['projectId'] ) {
				$projectId = sanitize_text_field( $_POST['projectId'] );
			} else {
				$projectId = false;
			}
			$from = sanitize_text_field( $_POST['from'] );
			$to = sanitize_text_field( $_POST['to'] );
			$query = sanitize_text_field( $_POST['query'] );
			if ( isset( $_POST['filter'] ) ) {
				$filter_id = sanitize_text_field( $_POST['filter'] );
			} else {
				$filter_id = false;
			}
			if ( isset( $_POST['metric'] ) ) {
				$metric = sanitize_text_field( $_POST['metric'] );
			} else {
				$metric = 'sessions';
			}
			if ( $filter_id && $metric == 'sessions' ) { // Sessions metric is not available for item reports
				$metric = 'pageviews';
			}
			if ( ob_get_length() ) {
				ob_clean();
			}
			if ( ! ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_back'] ) && ( ( 1 == $this->aiwp->config->options['backend_item_reports'] ) || ( 1 == $this->aiwp->config->options['dashboard_widget'] ) ) ) ) {
				wp_die( - 31 );
			}

			if ( $this->aiwp->config->options['token'] && $this->aiwp->config->reporting_ready && $from && $to ) {
				if ( null === $this->aiwp->gapi_controller ) {
					$this->aiwp->gapi_controller = new AIWP_GAPI_Controller();
				}
			} else {
				wp_die( - 24 );
			}
			if ( false == $projectId ) {
				if ( $this->aiwp->config->options['reporting_type'] ){
					$projectId = $this->aiwp->config->options['webstream_jail'];
				} else {
					$projectId = $this->aiwp->config->options['tableid_jail'];
				}
			}
			$profile_info = AIWP_Tools::get_selected_profile( $this->aiwp->config->options['ga_profiles_list'], $projectId );
			if ( isset( $profile_info[4] ) ) {
				$this->aiwp->gapi_controller->timeshift = $profile_info[4];
			} else {
				$this->aiwp->gapi_controller->timeshift = (int) current_time( 'timestamp' ) - time();
			}
			if ( $filter_id ) {
				$uri_parts = explode( '/', get_permalink( $filter_id ), 4 );
				if ( isset( $uri_parts[3] ) ) {
					$uri = '/' . $uri_parts[3];
				} else {
					wp_die( - 25 );
				}
				// allow URL correction before sending an API request
				$filter = apply_filters( 'aiwp_backenditem_uri', $uri, $filter_id );
				$lastchar = substr( $filter, - 1 );
				if ( isset( $profile_info[6] ) && $profile_info[6] && '/' == $lastchar ) {
					$filter = $filter . $profile_info[6];
				}
				// Encode URL
				//$filter = rawurlencode( rawurldecode( $filter ) );
			} else {
				$filter = false;
			}
			$queries = explode( ',', $query );
			$results = array();
			foreach ( $queries as $value ) {
				$results[] = $this->aiwp->gapi_controller->get( $projectId, $value, $from, $to, $filter, $metric );
			}
			wp_send_json( $results );
		}

		/**
		 * Ajax handler for dismissing Admin notices
		 *
		 * @return json|int
		 */
		public function ajax_dismiss_notices() {
			if ( ! isset( $_POST['aiwp_security_dismiss_notices'] ) || ! wp_verify_nonce( $_POST['aiwp_security_dismiss_notices'], 'aiwp_dismiss_notices' ) ) {
				wp_die( - 30 );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 31 );
			}
			wp_die();
		}
	}
}
