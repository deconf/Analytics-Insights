/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

"use strict";

var aiwpRedirectLink;
var aiwpRedirectCalled = false;
var aiwpDefaultPrevented = false;

function aiwpRedirect () {
	if ( aiwpRedirectCalled ) {
		return;
	}
	aiwpRedirectCalled = true;
	if ( aiwpDefaultPrevented == false ) {
		document.location.href = aiwpRedirectLink;
	} else {
		aiwpDefaultPrevented = false;
	}
}

function aiwp_send_event ( category, action, label, withCallBack ) {

	if ( aiwpUAEventsData.options[ 'ga_with_gtag' ] ) {
		if ( withCallBack ) {
			if ( aiwpUAEventsData.options[ 'event_bouncerate' ] ) {
				gtag( 'event', action, {
					'event_category': category, 
					'event_label': label,
					'non_interaction' : 1,
					'event_callback' : aiwpRedirect
				} );
			} else {
				gtag( 'event', action, {
					'event_category': category, 
					'event_label': label,
					'event_callback' : aiwpRedirect
				} );
			}
		} else {
			if ( aiwpUAEventsData.options[ 'event_bouncerate' ] ) {
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
			if ( aiwpUAEventsData.options[ 'event_bouncerate' ] ) {
				ga( 'send', 'event', category, action, label, {
					'nonInteraction' : 1,
					'hitCallback' : aiwpRedirect
				} );
			} else {
				ga( 'send', 'event', category, action, label, {
					'hitCallback' : aiwpRedirect
				} );
			}
		} else {
			if ( aiwpUAEventsData.options[ 'event_bouncerate' ] ) {
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

	if ( aiwpUAEventsData.options[ 'event_tracking' ] ) {
		// Track Downloads
		jQuery( 'a' ).filter( function () {
            if (typeof this.href === 'string') {
                var reg = new RegExp( '.*\\.(' + aiwpUAEventsData.options[ 'event_downloads' ] + ')(\\?.*)?$' );
                return this.href.match( reg );
            }
		} ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'download';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			aiwp_send_event ( category, action, label, false );
		} );

		// Track Mailto
		jQuery( 'a[href^="mailto"]' ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'email';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'send';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			aiwp_send_event ( category, action, label, false );
		} );

		// Track telephone calls
		jQuery( 'a[href^="tel"]' ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'telephone';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'call';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			aiwp_send_event ( category, action, label, false );
		} );

		if ( aiwpUAEventsData.options[ 'root_domain' ] ) {

			// Track Outbound Links
			jQuery( 'a[href^="http"]' ).filter( function () {
	            if (typeof this.href === 'string') {
	                var reg = new RegExp( '.*\\.(' + aiwpUAEventsData.options[ 'event_downloads' ] + ')(\\?.*)?$' );
	            }				
				if ( reg && !this.href.match( reg ) ) {
					if ( this.href.indexOf( aiwpUAEventsData.options[ 'root_domain' ] ) == -1 && this.href.indexOf( '://' ) > -1 )
						return this.href;
				}
			} ).click( function ( e ) {
				aiwpRedirectCalled = false;
				aiwpRedirectLink = this.href;
				var category = this.getAttribute( 'data-vars-ga-category' ) || 'outbound';
				var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
				var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
				if ( this.target != '_blank' && aiwpUAEventsData.options[ 'event_precision' ] ) {
					if ( e.isDefaultPrevented() ) {
						aiwpDefaultPrevented = true;
						aiwpRedirectCalled = false;						
					}
				} else {
					aiwpRedirectCalled = true;
					aiwpDefaultPrevented = false;
				}
				if ( this.target != '_blank' && aiwpUAEventsData.options[ 'event_precision' ] ) {
					aiwp_send_event( category, action, label, true );	
					setTimeout( aiwpRedirect, aiwpUAEventsData.options[ 'event_timeout' ] );
					return false;
				} else {
					aiwp_send_event( category, action, label, false );	
				}
			} );
		}
	}

	if ( aiwpUAEventsData.options[ 'event_affiliates' ] && aiwpUAEventsData.options[ 'aff_tracking' ] ) {

		// Track Affiliates
		jQuery( 'a' ).filter( function () {
			if ( aiwpUAEventsData.options[ 'event_affiliates' ] != '' ) {
				if (typeof this.href === 'string') {
					var reg = new RegExp( '(' + aiwpUAEventsData.options[ 'event_affiliates' ].replace( /\//g, '\/' ) + ')' );
					return this.href.match( reg );
				}	
			}
		} ).click( function ( e ) {
			aiwpRedirectCalled = false;
			aiwpRedirectLink = this.href;
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'affiliates';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			if ( this.target != '_blank' && aiwpUAEventsData.options[ 'event_precision' ] ) {
				if ( e.isDefaultPrevented() ) {
					aiwpDefaultPrevented = true;
					aiwpRedirectCalled = false;
				}
			} else {
				aiwpRedirectCalled = true;
				aiwpDefaultPrevented = false;
			}			
			if ( this.target != '_blank' && aiwpUAEventsData.options[ 'event_precision' ] ) {
				aiwp_send_event( category, action, label, true );
				setTimeout( aiwpRedirect, aiwpUAEventsData.options[ 'event_timeout' ] );
				return false;
			} else {
				aiwp_send_event( category, action, label, false );
			}
		} );
	}

	if ( aiwpUAEventsData.options[ 'root_domain' ] && aiwpUAEventsData.options[ 'hash_tracking' ] ) {

		// Track Hashmarks
		jQuery( 'a' ).filter( function () {
			if ( this.href.indexOf( aiwpUAEventsData.options[ 'root_domain' ] ) != -1 || this.href.indexOf( '://' ) == -1 )
				return this.hash;
		} ).click( function ( e ) {
			var category = this.getAttribute( 'data-vars-ga-category' ) || 'hashmark';
			var action = this.getAttribute( 'data-vars-ga-action' ) || 'click';
			var label = this.getAttribute( 'data-vars-ga-label' ) || this.href;
			aiwp_send_event ( category, action, label, false );
		} );
	}

	if ( aiwpUAEventsData.options[ 'event_formsubmit' ] ) {

		// Track Form Submit
		jQuery( 'input[type="submit"], button[type="submit"]' ).click( function ( e ) {
			var aiwpSubmitObject = this;
			var category = aiwpSubmitObject.getAttribute( 'data-vars-ga-category' ) || 'form';
			var action = aiwpSubmitObject.getAttribute( 'data-vars-ga-action' ) || 'submit';
			var label = aiwpSubmitObject.getAttribute( 'data-vars-ga-label' ) || aiwpSubmitObject.name || aiwpSubmitObject.value;
			aiwp_send_event ( category, action, label, false );
		} );
	}

	if ( aiwpUAEventsData.options[ 'ga_pagescrolldepth_tracking' ] ) {
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
