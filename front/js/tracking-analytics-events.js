/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
var gadwpRedirectLink;
var gadwpRedirectCalled = false;

function gadwpRedirect () {
	if ( gadwpRedirectCalled ) {
		return;
	}
	gadwpRedirectCalled = true;
	document.location.href = gadwpRedirectLink;
}

( function ( $ ) {
	$( window ).load( function () {

		if ( gadwpUAEventsData.options[ 'event_tracking' ] ) {
			// Track Downloads
			$( 'a' ).filter( function () {
				var reg = new RegExp( '.*\\.(' + gadwpUAEventsData.options[ 'event_downloads' ] + ')(\\?.*)?$' );
				return this.href.match( reg );
			} ).click( function ( e ) {
				if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
					ga( 'send', 'event', 'download', 'click', this.href, {
						'nonInteraction' : 1
					} );
				} else {
					ga( 'send', 'event', 'download', 'click', this.href );
				}
			} );

			// Track Mailto
			$( 'a[href^="mailto"]' ).click( function ( e ) {
				if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
					ga( 'send', 'event', 'email', 'send', this.href, {
						'nonInteraction' : 1
					} );
				} else {
					ga( 'send', 'event', 'email', 'send', this.href );
				}
			} );

			// Track telephone calls
			$( 'a[href^="tel"]' ).click( function ( e ) {
				if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
					ga( 'send', 'event', 'telephone', 'call', this.href, {
						'nonInteraction' : 1
					} );
				} else {
					ga( 'send', 'event', 'telephone', 'call', this.href );
				}

			} );

			if ( gadwpUAEventsData.options[ 'root_domain' ] ) {

				// Track Outbound Links
				$( 'a[href^="http"]' ).filter( function () {
					var reg = new RegExp( '.*\\.(' + gadwpUAEventsData.options[ 'event_downloads' ] + ')(\\?.*)?$' );
					if ( !this.href.match( reg ) ) {
						if ( this.href.indexOf( gadwpUAEventsData.options[ 'root_domain' ] ) == -1 )
							return this.href;
					}
				} ).click( function ( e ) {
					gadwpRedirectCalled = false;
					gadwpRedirectLink = this.href;
					if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
						ga( 'send', 'event', 'outbound', 'click', this.href, {
							'nonInteraction' : 1,
							'hitCallback' : gadwpRedirect
						} );
					} else {
						ga( 'send', 'event', 'outbound', 'click', this.href, {
							'hitCallback' : gadwpRedirect
						} );
					}
					if ( this.target != '_blank' ) {
						setTimeout( gadwpRedirect, gadwpUAEventsData.options[ 'event_timeout' ] );
						return false;
					} else {
						gadwpRedirectCalled = true;
					}
				} );
			}
		}

		if ( gadwpUAEventsData.options[ 'event_affiliates' ] && gadwpUAEventsData.options[ 'aff_tracking' ] ) {

			// Track Affiliates
			$( 'a' ).filter( function () {
				if ( gadwpUAEventsData.options[ 'event_affiliates' ] != '' ) {
					var reg = new RegExp( '(' + gadwpUAEventsData.options[ 'event_affiliates' ].replace( /\//g, '\/' ) + ')' );
					return this.href.match( reg );
				}
			} ).click( function ( event ) {
				gadwpRedirectCalled = false;
				gadwpRedirectLink = this.href;
				if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
					ga( 'send', 'event', 'affiliates', 'click', this.href, {
						'nonInteraction' : 1,
						'hitCallback' : gadwpRedirect
					} );
				} else {
					ga( 'send', 'event', 'affiliates', 'click', this.href, {
						'hitCallback' : gadwpRedirect
					} );
				}
				if ( this.target != '_blank' ) {
					setTimeout( gadwpRedirect, gadwpUAEventsData.options[ 'event_timeout' ] );
					return false;
				} else {
					gadwpRedirectCalled = true;
				}
			} );
		}

		if ( gadwpUAEventsData.options[ 'root_domain' ] && gadwpUAEventsData.options[ 'hash_tracking' ] ) {

			// Track Hashmarks
			$( 'a' ).filter( function () {
				if ( this.href.indexOf( gadwpUAEventsData.options[ 'root_domain' ] ) != -1 || this.href.indexOf( '://' ) == -1 )
					return this.hash;
			} ).click( function ( e ) {
				if ( gadwpUAEventsData.options[ 'event_bouncerate' ] ) {
					ga( 'send', 'event', 'hashmark', 'click', this.href, {
						'nonInteraction' : 1
					} );
				} else {
					ga( 'send', 'event', 'hashmark', 'click', this.href );
				}
			} );
		}

		if ( gadwpUAEventsData.options[ 'event_formsubmit' ] ) {

			// Track Form Submit
			$( 'input[type="submit"]' ).click( function ( e ) {
				gadwpSubmitObject = this;
				var label = gadwpSubmitObject.value;
				if ( gadwpUAEventsData.options[ 'event_formsubmit' ] ) {
					ga( 'send', 'event', 'form', 'submit', label, {
						'nonInteraction' : 1
					} );
				} else {
					ga( 'send', 'event', 'form', 'submit', label );
				}
			} );
		}

		if ( gadwpUAEventsData.options[ 'ga_pagescrolldepth_tracking' ] ) {
			// Track Page Scroll Depth
			$.scrollDepth( {
				percentage : true,
				userTiming : false,
				pixelDepth : false,
				gtmOverride : true,
				nonInteraction : gadwpUAEventsData.options[ 'event_bouncerate' ],
			} );
		}

	} );
} )( jQuery );