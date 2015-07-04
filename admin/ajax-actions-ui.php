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

if ( ! class_exists( 'GADWP_UI_Ajax' ) ) {

	final class GADWP_UI_Ajax {

		public function __construct() {
			if ( current_user_can( 'manage_options' ) ) {
				// Admin Widget action
				add_action( 'wp_ajax_gadwp_dismiss_notices', array( $this, 'ajax_dismiss_notices' ) );
			}
		}

		/**
		 * Ajax handler for dismissing Admin notices
		 *
		 * @return json|int
		 */
		public function ajax_dismiss_notices() {
			if ( ! isset( $_REQUEST['gadwp_security_dismiss_notices'] ) || ! wp_verify_nonce( $_REQUEST['gadwp_security_dismiss_notices'], 'gadwp_dismiss_notices' ) ) {
				wp_die( - 30 );
			}

			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( - 31 );
			}

			delete_option( 'gadwp_got_updated' );

			wp_send_json( 1 );
		}
	}
}
