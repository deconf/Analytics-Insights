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

if ( ! class_exists( 'GADWP_Tools' ) ) {

	class GADWP_Tools {

		public static function get_countrycodes() {
			include_once 'iso3166.php';
			return $country_codes;
		}

		public static function guess_default_domain( $profiles ) {
			$domain = get_option( 'siteurl' );
			$domain = str_ireplace( array( 'http://', 'https://' ), '', $domain );
			if ( ! empty( $profiles ) ) {
				foreach ( $profiles as $items ) {
					if ( strpos( $items[3], $domain ) ) {
						return $items[1];
					}
				}
				return $profiles[0][1];
			} else {
				return '';
			}
		}

		public static function get_selected_profile( $profiles, $profile ) {
			if ( ! empty( $profiles ) ) {
				foreach ( $profiles as $item ) {
					if ( $item[1] == $profile ) {
						return $item;
					}
				}
			}
		}

		public static function get_root_domain() {
			$url = site_url();
			$root = explode( '/', $url );
			preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', str_ireplace( 'www', '', isset( $root[2] ) ? $root[2] : $url ), $root );
			if ( isset( $root['domain'] ) ) {
				return $root['domain'];
			} else {
				return '';
			}
		}

		public static function strip_protocol( $domain ) {
			return str_replace( array( "https://", "http://", " " ), "", $domain );
		}

		public static function clear_transients() {
			global $wpdb;
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_gadash%%'" );
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_gadash%%'" );
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_ga_dash%%'" );
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_ga_dash%%'" );
		}

		public static function colourVariator( $colour, $per ) {
			$colour = substr( $colour, 1 );
			$rgb = '';
			$per = $per / 100 * 255;
			if ( $per < 0 ) {
				// Darker
				$per = abs( $per );
				for ( $x = 0; $x < 3; $x++ ) {
					$c = hexdec( substr( $colour, ( 2 * $x ), 2 ) ) - $per;
					$c = ( $c < 0 ) ? 0 : dechex( $c );
					$rgb .= ( strlen( $c ) < 2 ) ? '0' . $c : $c;
				}
			} else {
				// Lighter
				for ( $x = 0; $x < 3; $x++ ) {
					$c = hexdec( substr( $colour, ( 2 * $x ), 2 ) ) + $per;
					$c = ( $c > 255 ) ? 'ff' : dechex( $c );
					$rgb .= ( strlen( $c ) < 2 ) ? '0' . $c : $c;
				}
			}
			return '#' . $rgb;
		}

		public static function variations( $base ) {
			$variations[] = $base;
			$variations[] = self::colourVariator( $base, - 10 );
			$variations[] = self::colourVariator( $base, + 10 );
			$variations[] = self::colourVariator( $base, + 20 );
			$variations[] = self::colourVariator( $base, - 20 );
			$variations[] = self::colourVariator( $base, + 30 );
			$variations[] = self::colourVariator( $base, - 30 );
			return $variations;
		}

		public static function check_roles( $access_level, $tracking = false ) {
			if ( is_user_logged_in() && isset( $access_level ) ) {
				$current_user = wp_get_current_user();
				$roles = (array) $current_user->roles;
				if ( ( current_user_can( 'manage_options' ) ) && ! $tracking ) {
					return true;
				}
				if ( count( array_intersect( $roles, $access_level ) ) > 0 ) {
					return true;
				} else {
					return false;
				}
			}
		}

		public static function unset_cookie( $name ) {
			$name = 'gadwp_wg_' . $name;
			setcookie( $name, '', time() - 3600, '/' );
			$name = 'gadwp_ir_' . $name;
			setcookie( $name, '', time() - 3600, '/' );
		}

		public static function set_cache( $name, $value, $expiration = 0 ) {
			$option = array( 'value' => $value, 'expires' => time() + (int) $expiration );
			update_option( 'gadwp_cache_' . $name, $option, 'no' );
		}

		public static function delete_cache( $name ) {
			delete_option( 'gadwp_cache_' . $name );
		}

		public static function get_cache( $name ) {
			$option = get_option( 'gadwp_cache_' . $name );

			if ( false === $option || ! isset( $option['value'] ) || ! isset( $option['expires'] ) ) {
				return false;
			}

			if ( $option['expires'] < time() ) {
				delete_option( 'gadwp_cache_' . $name );
				return false;
			} else {
				return $option['value'];
			}
		}

		public static function set_site_cache( $name, $value, $expiration = 0 ) {
			$option = array( 'value' => $value, 'expires' => time() + (int) $expiration );
			update_site_option( 'gadwp_cache_' . $name, $option );
		}

		public static function delete_site_cache( $name ) {
			delete_site_option( 'gadwp_cache_' . $name );
		}

		public static function get_site_cache( $name ) {
			$option = get_site_option( 'gadwp_cache_' . $name );

			if ( false === $option || ! isset( $option['value'] ) || ! isset( $option['expires'] ) ) {
				return false;
			}

			if ( $option['expires'] < time() ) {
				delete_option( 'gadwp_cache_' . $name );
				return false;
			} else {
				return $option['value'];
			}
		}

		public static function clear_cache() {
			global $wpdb;
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'gadwp_cache_qr%%'" );
		}

		public static function get_sites( $args ) { // Use wp_get_sites() if WP version is lower than 4.6.0
			global $wp_version;
			if ( version_compare( $wp_version, '4.6.0', '<' ) ) {
				return wp_get_sites( $args );
			} else {
				foreach ( get_sites( $args ) as $blog ) {
					$blogs[] = (array) $blog; // Convert WP_Site object to array
				}
				return $blogs;
			}
		}

		/**
		 * Loads a view file
		 *
		 * $data parameter will be available in the template file as $data['value']
		 *
		 * @param string $template - Template file to load
		 * @param array $data - data to pass along to the template
		 * @return boolean - If template file was found
		 **/
		public static function load_view( $path, $data = array() ) {
			if ( file_exists( GADWP_DIR . $path ) ) {
				require_once ( GADWP_DIR . $path );
				return true;
			}
			return false;
		}

		public static function doing_it_wrong( $function, $message, $version ) {
			if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true ) ) {
				if ( is_null( $version ) ) {
					$version = '';
				} else {
					/* translators: %s: version number */
					$version = sprintf( __( 'This message was added in version %s.', 'google-analytics-dashboard-for-wp' ), $version );
				}

				/* translators: Developer debugging message. 1: PHP function name, 2: Explanatory message, 3: Version information message */
				trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', 'google-analytics-dashboard-for-wp' ), $function, $message, $version ) );
			}
		}

		public static function get_dom_from_content( $content ) {
			$libxml_previous_state = libxml_use_internal_errors( true );
			if ( class_exists( 'DOMDocument' ) ) {
				$dom = new DOMDocument();
				$result = $dom->loadHTML( '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>' );
				libxml_clear_errors();
				libxml_use_internal_errors( $libxml_previous_state );
				if ( ! $result ) {
					return false;
				}
				return $dom;
			} else {
				self::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . __( 'DOM is disabled or libxml PHP extension is missing. Contact your hosting provider. Automatic tracking of events for AMP pages is not possible.', 'google-analytics-dashboard-for-wp' ), 24*60*60 );
				return false;
			}
		}

		public static function get_content_from_dom( $dom ) {
			$out = '';
			$body = $dom->getElementsByTagName( 'body' )->item( 0 );
			foreach ( $body->childNodes as $node ) {
				$out .= $dom->saveXML( $node );
			}
			return $out;
		}
	}
}
