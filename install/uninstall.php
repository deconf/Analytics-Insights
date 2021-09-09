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

class AIWP_Uninstall {

	public static function uninstall() {
		global $wpdb;
		if ( is_multisite() ) { // Cleanup Network install
			foreach ( AIWP_Tools::get_sites( array( 'number' => apply_filters( 'aiwp_sites_limit', 100 ) ) ) as $blog ) {
				switch_to_blog( $blog['blog_id'] );
				$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'aiwp_cache_%%'" );
				delete_option( 'aiwp_options' );
				restore_current_blog();
			}
			delete_site_option( 'aiwp_network_options' );
		} else { // Cleanup Single install
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'aiwp_cache_%%'" );
			delete_option( 'aiwp_options' );
		}
		AIWP_Tools::unset_cookie( 'default_metric' );
		AIWP_Tools::unset_cookie( 'default_dimension' );
		AIWP_Tools::unset_cookie( 'default_view' );
	}
}
