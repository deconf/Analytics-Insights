<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
Class GADASH_Uninstall{
	static function uninstall(){
		global $wpdb;
		$sqlquery = $wpdb->query ( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_gadash%%'" );
		$sqlquery = $wpdb->query ( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_gadash%%'" );		
		delete_option('gadash_options');
		delete_option('gadash_lasterror');
		delete_transient ( 'ga_dash_refresh_token' );
	}
}
