/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

"use strict";

var gadwpRedirectLink;
var gadwpRedirectCalled = false;
var gadwpDefaultPrevented = false;

function gadwpRedirect () {
	if ( gadwpRedirectCalled ) {
		return;
	}
	gadwpRedirectCalled = true;
	if ( gadwpDefaultPrevented == false ) {
		document.location.href = gadwpRedirectLink;
	} else {
		gadwpDefaultPrevented = false;
	}
}

function gadwp_send_event ( category, action, label, withCallBack ) {

	if ( gadwpUAEventsData.options[ 'ga_with_gtag' ] ) {
		if ( withCallBack ) {
			if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
				gtag( 'event', action, {
					'event_category': category, 
					'event_label': label,
					'non_interaction' : 1,
					'event_callback' : gadwpRedirect
				} );
			} else {
				gtag( 'event', action, {
					'event_category': category, 
					'event_label': label,
					'event_callback' : gadwpRedirect
				} );
			}
		} else {
			if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
				gtag( 'event', action, {
					'event_category': category, 
					'event_label': label,
					'non_interaction' : 1,
				} );
			} else {
				gtag( 'event', action, {
					'event_category': category, 
					'event_label': label
				} );
			}
		}
	} else {
		if ( withCallBack ) {
			if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
				ga( 'send', 'event', category, action, label, {
					'nonInteraction' : 1,
					'hitCallback' : gadwpRedirect
				} );
			} else {
				ga( 'send', 'event', category, action, label, {
					'hitCallback' : gadwpRedirect
				} );
			}
		} else {
			if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
				ga( 'send', 'event', category, action, label, {
					'nonInteraction' : 1
				} );
			} else {
				ga( 'send', 'event', category, action, label );
			}
		}
	}	
}

jQuery( window ).on( 'load', function () {

	if ( gadwpUAEventsData.options[ 'event_tracking' ] ) {
		// Track Downloads
		jQuery( 'a' ).filter( function () {
            if (typeof this.href === 'string') {
                var reg = new RegExp( '.*\\.(' + gadwpUAEventsData.options[ 'event_downloads' ] + ')(\\?.*)?$' );
                return this.href.match( reg );
            }
		} ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'download';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			gadwp_send_event ( category, action, label, false );
		} );

		// Track Mailto
		jQuery( 'a[href^="mailto"]' ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'email';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'send';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			gadwp_send_event ( category, action, label, false );
		} );

		// Track telephone calls
		jQuery( 'a[href^="tel"]' ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'telephone';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'call';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			gadwp_send_event ( category, action, label, false );
		} );

		if ( gadwpUAEventsData.options[ 'root_domain' ] ) {

			// Track Outbound Links
			jQuery( 'a[href^="http"]' ).filter( function () {
	            if (typeof this.href === 'string') {
	                var reg = new RegExp( '.*\\.(' + gadwpUAEventsData.options[ 'event_downloads' ] + ')(\\?.*)?$' );
	            }				
				if ( reg && !this.href.match( reg ) ) {
					if ( this.href.indexOf( gadwpUAEventsData.options[ 'root_domain' ] ) == -1 && this.href.indexOf( '://' ) > -1 )
						return this.href;
				}
			} ).click( function ( e ) {
				gadwpRedirectCalled = false;
				gadwpRedirectLink = this.href;
				var category = this.getAttribute( 'data-vars-ga-category' ) || 'outbound';
				var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
				var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
				if ( this.target != '_blank' && gadwpUAEventsData.options[ 'event_precision' ] ) {
					if ( e.isDefaultPrevented() ) {
						gadwpDefaultPrevented = true;
						gadwpRedirectCalled = false;						
					}
				} else {
					gadwpRedirectCalled = true;
					gadwpDefaultPrevented = false;
				}
				if ( this.target != '_blank' && gadwpUAEventsData.options[ 'event_precision' ] ) {
					gadwp_send_event( category, action, label, true );	
					setTimeout( gadwpRedirect, gadwpUAEventsData.options[ 'event_timeout' ] );
					return false;
				} else {
					gadwp_send_event( category, action, label, false );	
				}
			} );
		}
	}

	if ( gadwpUAEventsData.options[ 'event_affiliates' ] && gadwpUAEventsData.options[ 'aff_tracking' ] ) {

		// Track Affiliates
		jQuery( 'a' ).filter( function () {
			if ( gadwpUAEventsData.options[ 'event_affiliates' ] != '' ) {
				if (typeof this.href === 'string') {
					var reg = new RegExp( '(' + gadwpUAEventsData.options[ 'event_affiliates' ].replace( /\//g, '\/' ) + ')' );
					return this.href.match( reg );
				}	
			}
		} ).click( function ( e ) {
			gadwpRedirectCalled = false;
			gadwpRedirectLink = this.href;
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'affiliates';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			if ( this.target != '_blank' && gadwpUAEventsData.options[ 'event_precision' ] ) {
				if ( e.isDefaultPrevented() ) {
					gadwpDefaultPrevented = true;
					gadwpRedirectCalled = false;
				}
			} else {
				gadwpRedirectCalled = true;
				gadwpDefaultPrevented = false;
			}			
			if ( this.target != '_blank' && gadwpUAEventsData.options[ 'event_precision' ] ) {
				gadwp_send_event( category, action, label, true );
				setTimeout( gadwpRedirect, gadwpUAEventsData.options[ 'event_timeout' ] );
				return false;
			} else {
				gadwp_send_event( category, action, label, false );
			}
		} );
	}

	if ( gadwpUAEventsData.options[ 'root_domain' ] && gadwpUAEventsData.options[ 'hash_tracking' ] ) {

		// Track Hashmarks
		jQuery( 'a' ).filter( function () {
			if ( this.href.indexOf( gadwpUAEventsData.options[ 'root_domain' ] ) != -1 || this.href.indexOf( '://' ) == -1 )
				return this.hash;
		} ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'hashmark';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			gadwp_send_event ( category, action, label, false );
		} );
	}

	if ( gadwpUAEventsData.options[ 'event_formsubmit' ] ) {

		// Track Form Submit
		jQuery( 'input[type="submit"]' ).click( function ( e ) {
			var gadwpSubmitObject = this;
			var category = gadwpSubmitObject.getAttribute( 'data-vars-ga-category' ) || 'form';
			var action = gadwpSubmitObject.getAttribute( 'data-vars-ga-action' ) || 'submit';
			var label = gadwpSubmitObject.getAttribute( 'data-vars-ga-label' ) || gadwpSubmitObject.name || gadwpSubmitObject.value;
			gadwp_send_event ( category, action, label, false );
		} );
	}

	if ( gadwpUAEventsData.options[ 'ga_pagescrolldepth_tracking' ] ) {
		// Track Page Scroll Depth
		jQuery.scrollDepth( {
			percentage : true,
			userTiming : false,
			pixelDepth : false,
			gtmOverride : true,
			nonInteraction : true,
		} );
	}

} );
