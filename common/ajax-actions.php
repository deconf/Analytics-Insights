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
if ( ! class_exists( 'AIWP_Common_Ajax' ) ) {

	final class AIWP_Common_Ajax {

		private $aiwp;

		public function __construct() {
			$this->aiwp = AIWP();
			if ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_back'] ) || AIWP_Tools::check_roles( $this->aiwp->config->options['access_front'] ) ) {
				add_action( 'wp_ajax_aiwp_set_error', array( $this, 'ajax_set_error' ) );
			}
		}

		/**
		 * Ajax handler for storing JavaScript Errors
		 *
		 * @return json|int
		 */
		public function ajax_set_error() {
			if ( ! isset( $_POST['aiwp_security_set_error'] ) || ! ( wp_verify_nonce( $_POST['aiwp_security_set_error'], 'aiwp_backend_item_reports' ) || wp_verify_nonce( $_POST['aiwp_security_set_error'], 'aiwp_frontend_item_reports' ) ) ) {
				wp_die( - 40 );
			}
			$timeout = 24 * 60 * 60;
			AIWP_Tools::set_error( $_POST['response'], $timeout );
			wp_die();
		}
	}
}
