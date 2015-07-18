<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Backend_Ajax' ) ) {

	final class GADWP_Backend_Ajax {

		private $gadwp;

		public function __construct() {
			$this->gadwp = GADWP();

			if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) && ( 1 == $this->gadwp->config->options['dashboard_widget'] ) ) {
				// Admin Widget action
				add_action( 'wp_ajax_gadash_get_widgetreports', array( $this, 'ajax_widget_reports' ) );
			}

			if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) && ( 1 == $this->gadwp->config->options['backend_item_reports'] ) ) {
				// Items action
				add_action( 'wp_ajax_gadwp_backend_item_reports', array( $this, 'ajax_item_reports' ) );
			}
		}

		/**
		 * Ajax handler for Item Reports
		 *
		 * @return json|int
		 */
		public function ajax_item_reports() {
			if ( ! isset( $_REQUEST['gadwp_security_backend_item_reports'] ) || ! wp_verify_nonce( $_REQUEST['gadwp_security_backend_item_reports'], 'gadwp_backend_item_reports' ) ) {
				wp_die( - 30 );
			}

			$from = $_REQUEST['from'];
			$to = $_REQUEST['to'];
			$query = $_REQUEST['query'];
			$filter_id = $_REQUEST['filter'];

			if ( ob_get_length() ) {
				ob_clean();
			}

			if ( ! GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) || 0 == $this->gadwp->config->options['backend_item_reports'] ) {
				wp_die( - 31 );
			}
			if ( $this->gadwp->config->options['ga_dash_token'] && $this->gadwp->config->options['ga_dash_tableid_jail'] && $from && $to ) {
				if ( null === $this->gadwp->gapi_controller ) {
					$this->gadwp->gapi_controller = new GADWP_GAPI_Controller();
				}
			} else {
				wp_die( - 24 );
			}
			$projectId = $this->gadwp->config->options['ga_dash_tableid_jail'];
			$profile_info = GADWP_Tools::get_selected_profile( $this->gadwp->config->options['ga_dash_profile_list'], $projectId );
			if ( isset( $profile_info[4] ) ) {
				$this->gadwp->gapi_controller->timeshift = $profile_info[4];
			} else {
				$this->gadwp->gapi_controller->timeshift = (int) current_time( 'timestamp' ) - time();
			}

			$uri_parts = explode( '/', get_permalink( $filter_id ), 4 );

			if ( isset( $uri_parts[3] ) ) {
				$uri = '/' . $uri_parts[3];
			} else {
				wp_die( - 25 );
			}

			// allow URL correction before sending an API request
			$filter = apply_filters( 'gadwp_backenditem_uri', $uri );

			$lastchar = substr( $filter, - 1 );

			if ( isset( $profile_info[6] ) && $profile_info[6] && $lastchar == '/' ) {
				$filter = $filter . $profile_info[6];
			}

			// Encode URL
			$filter = rawurlencode( rawurldecode( $filter ) );

			$queries = explode( ',', $query );

			$results = array();

			foreach ( $queries as $value ) {
				$results[] = $this->gadwp->gapi_controller->get( $projectId, $value, $from, $to, $filter );
			}

			wp_send_json( $results );
		}

		/**
		 * Ajax handler for Admin Widget
		 *
		 * @return json|int
		 */
		public function ajax_widget_reports() {
			if ( ! isset( $_REQUEST['gadash_security_widget_reports'] ) || ! wp_verify_nonce( $_REQUEST['gadash_security_widget_reports'], 'gadash_get_widgetreports' ) ) {
				wp_die( - 30 );
			}

			$projectId = $_REQUEST['projectId'];
			$from = $_REQUEST['from'];
			$to = $_REQUEST['to'];
			$query = $_REQUEST['query'];

			if ( ob_get_length() ) {
				ob_clean();
			}

			if ( ! GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) || 0 == $this->gadwp->config->options['dashboard_widget'] ) {
				wp_die( - 31 );
			}

			if ( $this->gadwp->config->options['ga_dash_token'] && $projectId && $from && $to ) {
				if ( null === $this->gadwp->gapi_controller ) {
					$this->gadwp->gapi_controller = new GADWP_GAPI_Controller();
				}
			} else {
				wp_die( - 24 );
			}

			$profile_info = GADWP_Tools::get_selected_profile( $this->gadwp->config->options['ga_dash_profile_list'], $projectId );

			if ( isset( $profile_info[4] ) ) {
				$this->gadwp->gapi_controller->timeshift = $profile_info[4];
			} else {
				$this->gadwp->gapi_controller->timeshift = (int) current_time( 'timestamp' ) - time();
			}

			$queries = explode( ',', $query );

			$results = array();

			foreach ( $queries as $value ) {
				$results[] = $this->gadwp->gapi_controller->get( $projectId, $value, $from, $to );
			}

			wp_send_json( $results );
		}
	}
}
