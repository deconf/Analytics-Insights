/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

"use strict";

jQuery( document ).ready( function () {

	var aiwp_ui = {
		action : 'aiwp_dismiss_notices',
		aiwp_security_dismiss_notices : aiwp_ui_data.security,
	}

	jQuery( "#aiwp-notice .notice-dismiss" ).click( function () {
		jQuery.post( aiwp_ui_data.ajaxurl, aiwp_ui );
	} );

	if ( aiwp_ui_data.ed_bubble != '' ) {
		jQuery( '#toplevel_page_aiwp_settings li > a[href*="page=aiwp_errors_debugging"]' ).append( '&nbsp;<span class="awaiting-mod count-1"><span class="pending-count" style="padding:0 7px;">' + aiwp_ui_data.ed_bubble + '</span></span>' );
	}

} );