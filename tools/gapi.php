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

use Google\Service\Exception as GoogleServiceException;

if ( ! class_exists( 'AIWP_GAPI_Controller' ) ) {

	final class AIWP_GAPI_Controller {

		public $client;

		public $service;

		public $service_ga4;

		public $timeshift;

		public $managequota;

		private $aiwp;

		private $access = array( '220758964178-rhheb4146405g3fs6e4qjkk0rnf5q9q5.apps.googleusercontent.com', 'secret' );

		public function __construct() {
			$this->aiwp = AIWP();
			include_once ( AIWP_DIR . 'tools/vendor/autoload.php' );
			$this->client = new Google\Client();

			// add Proxy server settings to Guzzle, if defined

			if ( defined( 'WP_PROXY_HOST' ) && defined( 'WP_PROXY_PORT' ) ) {
				$httpoptions = array();
				$httpoptions [ 'proxy' ] = "'" . WP_PROXY_HOST . ":". WP_PROXY_PORT ."'";
				if ( defined( 'WP_PROXY_USERNAME' ) && defined( 'WP_PROXY_PASSWORD' ) ) {
					$httpoptions [ 'auth' ] = array( WP_PROXY_USERNAME, WP_PROXY_PASSWORD );
				}
				$httpClient = new GuzzleHttp\Client( $httpoptions );
				$this->client->setHttpClient( $httpClient );
			}

			$this->client->setScopes( array( 'https://www.googleapis.com/auth/analytics.readonly' ) );
			$this->client->setAccessType( 'offline' );
			$this->client->setApprovalPrompt( 'force' );
			$this->client->setApplicationName( 'AIWP ' . AIWP_CURRENT_VERSION );
			$security = wp_create_nonce( 'aiwp_security' );
			if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
				$state_uri = network_admin_url( 'admin.php?page=aiwp_settings' ) . '&aiwp_security=' . $security;
			} else {
				$state_uri = admin_url( 'admin.php?page=aiwp_settings' ) . '&aiwp_security=' . $security;
			}
			$this->client->setState( $state_uri );
			$this->managequota = 'u' . get_current_user_id() . 's' . get_current_blog_id();
			if ( $this->aiwp->config->options['user_api'] ) {
				$this->client->setClientId( $this->aiwp->config->options['client_id'] );
				$this->client->setClientSecret( $this->aiwp->config->options['client_secret'] );
				$this->client->setRedirectUri( AIWP_URL . 'tools/oauth2callback.php' );
			} else {
				$this->client->setClientId( $this->access[0] );
				$this->client->setClientSecret( $this->access[1] );
				$this->client->setRedirectUri( AIWP_ENDPOINT_URL . 'oauth2callback.php' );
			}

			/**
			 * AIWP Endpoint support
			 */
			if ( $this->aiwp->config->options['token'] ) {
				$token = $this->aiwp->config->options['token'];
				if ( $token ) {
					try {
						$array_token = (array)$token;
						$this->client->setAccessToken( $array_token );
						if ( $this->isAccessTokenExpired() ) {
							$this->fetch_new_token( $token );
						}
					} catch ( GoogleServiceException $e ) {
						$timeout = $this->get_timeouts( 'midnight' );
						aiwp_Tools::set_error( $e, $timeout );
						$this->reset_token();
					} catch ( Exception $e ) {
						$timeout = $this->get_timeouts( 'midnight' );
						aiwp_Tools::set_error( $e, $timeout );
						$this->reset_token();
					}

					if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
						$this->aiwp->config->set_plugin_options( true );
					} else {
						$this->aiwp->config->set_plugin_options();
					}
				}
			}

			$this->service = new Google\Service\Analytics( $this->client );
			$this->service_ga4 = new Google\Service\GoogleAnalyticsAdmin( $this->client );

		}

		/**
		 * Returns if the access_token is expired.
		 * @return bool Returns True if the access_token is expired.
		 */
		public function isAccessTokenExpired() {
			$token = (array)$this->aiwp->config->options['token'];
			if ( ! $token ) {
				return true;
			}
			$created = 0;
			if ( isset( $token['created'] ) ) {
				$created = $token['created'];
			}
			// If the token is set to expire in the next 90 seconds.
			return ( $created + ( $token['expires_in'] - 90 ) ) < time();
		}

		public function fetch_new_token( $oldtoken ) {
			if ( $this->aiwp->config->options['with_endpoint'] && ! $this->aiwp->config->options['user_api'] ) {

				$endpoint = AIWP_ENDPOINT_URL . 'aiwp-token.php';

				$token = json_encode( $oldtoken );

				$response = wp_remote_post( $endpoint, array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => array(
						'token' => $token,
						'client_id' => $this->client->getClientId(),
						'version' => AIWP_CURRENT_VERSION
					),
					'cookies' => array()
				)
					);

				if ( is_wp_error( $response ) ) { //AIWP Endpoint Error
					$e = __("Endpoint Error:", 'analytics-insights') . $response->get_error_message();
					$timeout = $this->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} else {
					$token = json_decode( $response['body'] );
					$array_token = (array)$token;
					if ( isset( $array_token['access_token'] ) ){
						$this->client->setAccessToken( $array_token );
						$this->aiwp->config->options['token'] = $this->client->getAccessToken();
					} else{ //Google Endpoint Error
						$timeout = $this->get_timeouts( 'midnight' );
						AIWP_Tools::set_error( $token, $timeout );
					}
				}
			} else {
				try {
					$this->client->refreshToken( $this->client->getRefreshToken() );
					$this->aiwp->config->options['token'] = $this->client->getAccessToken();
				} catch ( GoogleServiceException $e ) {
					$timeout = $this->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} catch ( Exception $e ) {
					$timeout = $this->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				}
			}
		}

		public function authenticate( $access_code ) {
			if ( $this->aiwp->config->options['with_endpoint'] && ! $this->aiwp->config->options['user_api'] ) {

				$endpoint = AIWP_ENDPOINT_URL . 'aiwp-token.php';

				$response = wp_remote_post( $endpoint, array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => array(
						'access_code' => $access_code,
						'client_id' => $this->client->getClientId(),
						'version' => AIWP_CURRENT_VERSION
					),
					'cookies' => array()
				)
					);

				if ( is_wp_error( $response ) ) { //AIWP Endpoint Error
					$e = __("Endpoint Error:", 'analytics-insights') . $response->get_error_message();
					$timeout = $this->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} else {
					$token = json_decode( $response['body'] );
					$array_token = (array)$token;
					if ( isset( $array_token['access_token'] ) ){
						return $token;
					} else { //Google Endpoint Error
						$timeout = $this->get_timeouts( 'midnight' );
						AIWP_Tools::set_error( $token, $timeout );
					}
				}
			} else {
				try {
					$this->client->fetchAccessTokenWithAuthCode( $access_code );
					return $this->client->getAccessToken();
				} catch ( GoogleServiceException $e ) {
					$timeout = $this->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} catch ( Exception $e ) {
					$timeout = $this->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				}
			}
		}

		/**
		 * Handles the token reset process
		 *
		 * @param
		 *            $all
		 */
		public function reset_token( $all = false ) {

			$token = $this->client->getAccessToken();

			if ( $all && $token ) {

				if ( $this->aiwp->config->options['with_endpoint'] && ! $this->aiwp->config->options['user_api'] ) {

					$endpoint = AIWP_ENDPOINT_URL . 'aiwp-revoke.php';

					$response = wp_remote_post( $endpoint, array(
						'method' => 'POST',
						'timeout' => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'headers' => array(),
						'body' => array(
							'client_id' => $this->client->getClientId(),
							'token' => json_encode( $this->client->getAccessToken() ),
							'version' => AIWP_CURRENT_VERSION
						),
						'cookies' => array()
					)
						);
					if ( is_wp_error( $response ) ) { // AIWP Endpoint Error
						$e = __( "Endpoint Error:", 'analytics-insights' ) . $response->get_error_message();
						$timeout = $this->get_timeouts( 'midnight' );
						AIWP_Tools::set_error( $e, $timeout );
					}
				} else {
					$this->client->revokeToken();
				}
			}

			if ( $all ){
				$this->aiwp->config->options['site_jail'] = "";
				$this->aiwp->config->options['sites_list'] = array();
			}

			$this->aiwp->config->options['token'] = "";
			$this->aiwp->config->options['sites_list_locked'] = 0;

			if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
				$this->aiwp->config->set_plugin_options( true );
			} else {
				$this->aiwp->config->set_plugin_options();
			}
		}

		/**
		 * Handles errors returned by GAPI Library
		 *
		 * @return boolean
		 */
		public function gapi_errors_handler() {
			$errors = AIWP_Tools::get_cache( 'gapi_errors' );
			if ( false === $errors || ! isset( $errors[0] ) ) { // invalid error
				return false;
			}
			if ( isset( $errors[1][0]['reason'] ) && ( 'invalidParameter' == $errors[1][0]['reason'] || 'badRequest' == $errors[1][0]['reason'] || 'invalidCredentials' == $errors[1][0]['reason'] || 'insufficientPermissions' == $errors[1][0]['reason'] || 'required' == $errors[1][0]['reason'] ) ) {
				$this->reset_token();
				return true;
			}
			if ( 400 == $errors[0] || 401 == $errors[0] || 403 == $errors[0] ) {
				$this->reset_token();
				return true;
			}

			/** Back-off system for subsequent requests - an Auth error generated after a Service request
			 *  The native back-off system for Service requests is covered by the GAPI PHP Client
			 */
			if ( isset( $errors[1][0]['reason'] ) && ( 'authError' == $errors[1][0]['reason'] ) ) {
				if ( $this->aiwp->config->options['api_backoff'] <= 5 ) {
					usleep( $this->aiwp->config->options['api_backoff'] * 1000000 + rand( 100000, 1000000 ) );
					$this->aiwp->config->options['api_backoff'] = $this->aiwp->config->options['api_backoff'] + 1;
					$this->aiwp->config->set_plugin_options();
					return false;
				} else {
					return true;
				}
			}
			if ( 500 == $errors[0] || 503 == $errors[0] || $errors[0] < - 50 ) {
				return true;
			}
			return false;
		}

		/**
		 * Calculates proper timeouts for each GAPI query
		 *
		 * @param
		 *            $interval
		 * @return number
		 */
		public function get_timeouts( $interval = '' ) {
			$local_time = time() + $this->timeshift;
			if ( 'daily' == $interval ) {
				$nextday = explode( '-', date( 'n-j-Y', strtotime( ' +1 day', $local_time ) ) );
				$midnight = mktime( 0, 0, 0, $nextday[0], $nextday[1], $nextday[2] );
				return $midnight - $local_time;
			} else if ( 'midnight' == $interval ) {
				$midnight = strtotime( "tomorrow 00:00:00" ); // UTC midnight
				$midnight = $midnight + 8 * 3600; // UTC 8 AM
				return $midnight - time();
			} else if ( 'hourly' == $interval ) {
				$nexthour = explode( '-', date( 'H-n-j-Y', strtotime( ' +1 hour', $local_time ) ) );
				$newhour = mktime( $nexthour[0], 0, 0, $nexthour[1], $nexthour[2], $nexthour[3] );
				return $newhour - $local_time;
			} else {
				$newtime = strtotime( ' +5 minutes', $local_time );
				return $newtime - $local_time;
			}
		}

		/**
		 * Retrieves all Universal Analytics Views with details
		 *
		 * @return array
		 */
		 public function ua_refresh_profiles() {

			try {
				$ga_profiles_list = array();
				$startindex = 1;
				$totalresults = 65535; // use something big
				while ( $startindex < $totalresults ) {
					$profiles = $this->service->management_profiles->listManagementProfiles( '~all', '~all', array( 'start-index' => $startindex ) );
					$items = $profiles->getItems();
					$totalresults = $profiles->getTotalResults();
					if ( $totalresults > 0 ) {
						foreach ( $items as $profile ) {
							$timetz = new DateTimeZone( $profile->getTimezone() );
							$localtime = new DateTime( 'now', $timetz );
							$timeshift = strtotime( $localtime->format( 'Y-m-d H:i:s' ) ) - time();
							$ga_profiles_list[] = array( $profile->getName(), $profile->getId(), $profile->getwebPropertyId(), $profile->getwebsiteUrl(), $timeshift, $profile->getTimezone(), $profile->getDefaultPage() );
							$startindex++;
						}
					}
				}
				if ( empty( $ga_profiles_list ) ) {
					$timeout = $this->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( 'No properties were found in this account!', $timeout );
				} else {
					AIWP_Tools::delete_cache( 'last_error' );
				}
				return $ga_profiles_list;
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
			}

		}

		/**
		 * Retrieves all Google Analytics 4 Properties with details
		 *
		 * @return array
		 */
		public function ga4_refresh_profiles() {
			try {
				$ga4_webstreams_list = array();

				 $accounts = $this->service_ga4->accountSummaries->listAccountSummaries()->getAccountSummaries();

				 if ( !empty( $accounts ) ) {
				 	foreach ( $accounts as $account ) {
				 		$properties = $account->getPropertySummaries();
				 		if ( !empty( $properties ) ) {
				 			foreach ( $properties as $property ) {
				 				$datastreams = $this->service_ga4->properties_dataStreams->listPropertiesDataStreams( $property->getProperty() )->getDataStreams();

				 				if ( !empty( $datastreams ) ) {
				 					foreach ( $datastreams as $datastream ) {
				 						$webstream = $datastream->getWebStreamData();
					 						if ('WEB_DATA_STREAM' == $datastream->type){
						 						$ga4_webstreams_list[] = array( $datastream->getDisplayName(), $datastream->getName(), $webstream->getDefaultUri(), $webstream->getMeasurementId() );
					 						}
				 					}

				 				}
				 			}
				 		}
				 	}
				 }
				return $ga4_webstreams_list;
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
			}
		}


		/**
		 * Get and cache Core Reports
		 *
		 * @param
		 *            $projecId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $metrics
		 * @param
		 *            $options
		 * @param
		 *            $serial
		 * @return int|Deconf_Service_Analytics_GaData
		 */
		private function handle_corereports( $projectId, $from, $to, $metrics, $options, $serial ) {
			try {
				if ( 'today' == $from ) {
					$interval = 'hourly';
				} else {
					$interval = 'daily';
				}
				$transient = AIWP_Tools::get_cache( $serial );
				if ( false === $transient ) {
					if ( $this->gapi_errors_handler() ) {
						return - 23;
					}
					$options['samplingLevel'] = 'HIGHER_PRECISION';
					$data = $this->service->data_ga->get( 'ga:' . $projectId, $from, $to, $metrics, $options );
					if ( method_exists( $data, 'getContainsSampledData' ) && $data->getContainsSampledData() ) {
						$sampling['date'] = date( 'Y-m-d H:i:s' );
						$sampling['percent'] = number_format( ( $data->getSampleSize() / $data->getSampleSpace() ) * 100, 2 ) . '%';
						$sampling['sessions'] = $data->getSampleSize() . ' / ' . $data->getSampleSpace();
						AIWP_Tools::set_cache( 'sampleddata', $sampling, 30 * 24 * 3600 );
						AIWP_Tools::set_cache( $serial, $data, $this->get_timeouts( 'hourly' ) ); // refresh every hour if data is sampled
					} else {
						AIWP_Tools::set_cache( $serial, $data, $this->get_timeouts( $interval ) );
					}
				} else {
					$data = $transient;
				}
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			}
			$this->aiwp->config->options['api_backoff'] = 0;
			$this->aiwp->config->set_plugin_options();
			if ( $data->getRows() > 0 ) {
				return $data;
			} else {
				$data->rows = array();
				return $data;
			}
		}

		/**
		 * Generates serials for transients
		 *
		 * @param
		 *            $serial
		 * @return string
		 */
		public function get_serial( $serial ) {
			return sprintf( "%u", crc32( $serial ) );
		}

		/**
		 * Analytics data for Area Charts (Admin Dashboard Widget report)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $query
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_areachart_data( $projectId, $from, $to, $query, $filter = '' ) {
			switch ( $query ) {
				case 'users' :
					$title = __( "Users", 'analytics-insights' );
					break;
				case 'pageviews' :
					$title = __( "Page Views", 'analytics-insights' );
					break;
				case 'visitBounceRate' :
					$title = __( "Bounce Rate", 'analytics-insights' );
					break;
				case 'organicSearches' :
					$title = __( "Organic Searches", 'analytics-insights' );
					break;
				case 'uniquePageviews' :
					$title = __( "Unique Page Views", 'analytics-insights' );
					break;
				default :
					$title = __( "Sessions", 'analytics-insights' );
			}
			$metrics = 'ga:' . $query;
			if ( 'today' == $from || 'yesterday' == $from ) {
				$dimensions = 'ga:hour';
				$dayorhour = __( "Hour", 'analytics-insights' );
			} else if ( '365daysAgo' == $from || '1095daysAgo' == $from ) {
				$dimensions = 'ga:yearMonth, ga:month';
				$dayorhour = __( "Date", 'analytics-insights' );
			} else {
				$dimensions = 'ga:date,ga:dayOfWeekName';
				$dayorhour = __( "Date", 'analytics-insights' );
			}
			$options = array( 'dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId );
			if ( $filter ) {
				$options['filters'] = 'ga:pagePath==' . $filter;
			}
			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $metrics . $filter );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( empty( $data->rows ) ) {
				// unable to render it as an Area Chart, returns a numeric value to be handled by reportsx.js
				return - 21;
			}
			$aiwp_data = array( array( $dayorhour, $title ) );
			if ( 'today' == $from || 'yesterday' == $from ) {
				foreach ( $data->getRows() as $row ) {
					$aiwp_data[] = array( (int) $row[0] . ':00', round( $row[1], 2 ) );
				}
			} else if ( '365daysAgo' == $from || '1095daysAgo' == $from ) {
				foreach ( $data->getRows() as $row ) {
					/*
					 * translators:
					 * Example: 'F, Y' will become 'November, 2015'
					 * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
					$aiwp_data[] = array( date_i18n( __( 'F, Y', 'analytics-insights' ), strtotime( $row[0] . '01' ) ), round( $row[2], 2 ) );
				}
			} else {
				foreach ( $data->getRows() as $row ) {
					/*
					 * translators:
					 * Example: 'l, F j, Y' will become 'Thusday, November 17, 2015'
					 * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
					$aiwp_data[] = array( date_i18n( __( 'l, F j, Y', 'analytics-insights' ), strtotime( $row[0] ) ), round( $row[2], 2 ) );
				}
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Bottom Stats (bottom stats on main report)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_bottomstats( $projectId, $from, $to, $filter = '' ) {
			$options = array( 'dimensions' => null, 'quotaUser' => $this->managequota . 'p' . $projectId );
			if ( $filter ) {
				$options['filters'] = 'ga:pagePath==' . $filter;
				$metrics = 'ga:uniquePageviews,ga:users,ga:pageviews,ga:BounceRate,ga:organicSearches,ga:pageviewsPerSession,ga:avgTimeOnPage,ga:avgPageLoadTime,ga:exitRate';
			} else {
				$metrics = 'ga:sessions,ga:users,ga:pageviews,ga:BounceRate,ga:organicSearches,ga:pageviewsPerSession,ga:avgTimeOnPage,ga:avgPageLoadTime,ga:avgSessionDuration';
			}
			$serial = 'qr3_' . $this->get_serial( $projectId . $from . $filter );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array();
			foreach ( $data->getRows() as $row ) {
				$aiwp_data = array_map( 'floatval', $row );
			}
			// i18n support
			$aiwp_data[0] = isset( $aiwp_data[0] ) ? number_format_i18n( $aiwp_data[0] ) : 0;
			$aiwp_data[1] = isset( $aiwp_data[1] ) ? number_format_i18n( $aiwp_data[1] ) : 0;
			$aiwp_data[2] = isset( $aiwp_data[2] ) ? number_format_i18n( $aiwp_data[2] ) : 0;
			$aiwp_data[3] = isset( $aiwp_data[3] ) ? number_format_i18n( $aiwp_data[3], 2 ) . '%' : '0%';
			$aiwp_data[4] = isset( $aiwp_data[4] ) ? number_format_i18n( $aiwp_data[4] ) : 0;
			$aiwp_data[5] = isset( $aiwp_data[5] ) ? number_format_i18n( $aiwp_data[5], 2 ) : 0;
			$aiwp_data[6] = isset( $aiwp_data[6] ) ? gmdate( "H:i:s", $aiwp_data[6] ) : '00:00:00';
			$aiwp_data[7] = isset( $aiwp_data[7] ) ? number_format_i18n( $aiwp_data[7], 2 ) : 0;
			if ( $filter ) {
				$aiwp_data[8] = isset( $aiwp_data[8] ) ? number_format_i18n( $aiwp_data[8], 2 ) . '%' : '0%';
			} else {
				$aiwp_data[8] = isset( $aiwp_data[8] ) ? gmdate( "H:i:s", $aiwp_data[8] ) : '00:00:00';
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Table Charts (content pages)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_contentpages( $projectId, $from, $to , $metric, $filter = '' ) {
			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:pageTitle';
			$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
			if ( $filter ) {
				$options['filters'] = 'ga:pagePath==' . $filter;
			}
			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array( array( __( "Pages", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data->getRows() as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for 404 Errors
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @return array|int
		 */
		private function get_404errors( $projectId, $from, $to, $metric, $filter = "Page Not Found" ) {
			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:pagePath,ga:fullReferrer';
			$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
			$options['filters'] = 'ga:pageTitle=@' . $filter;
			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array( array( __( "404 Errors", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data->getRows() as $row ) {
				$path = esc_html( $row[0] );
				$source = esc_html( $row[1] );
				$aiwp_data[] = array( "<strong>" . __( "URI:", 'analytics-insights' ) . "</strong> " . $path . "<br><strong>" . __( "Source:", 'analytics-insights' ) . "</strong> " . $source, (int) $row[2] );
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Table Charts (referrers)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_referrers( $projectId, $from, $to, $metric, $filter = '' ) {
			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:source';
			$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
			if ( $filter ) {
				$options['filters'] = 'ga:medium==referral;ga:pagePath==' . $filter;
			} else {
				$options['filters'] = 'ga:medium==referral';
			}
			$serial = 'qr5_' . $this->get_serial( $projectId . $from . $filter . $metric );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array( array( __( "Referrers", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data->getRows() as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Table Charts (searches)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_searches( $projectId, $from, $to, $metric, $filter = '' ) {
			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:keyword';
			$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
			if ( $filter ) {
				$options['filters'] = 'ga:keyword!=(not set);ga:pagePath==' . $filter;
			} else {
				$options['filters'] = 'ga:keyword!=(not set)';
			}
			$serial = 'qr6_' . $this->get_serial( $projectId . $from . $filter . $metric );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array( array( __( "Searches", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data->getRows() as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Table Charts (location reports)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_locations( $projectId, $from, $to, $metric, $filter = '' ) {
			$metrics = 'ga:' . $metric;
			$options = "";
			$title = __( "Countries", 'analytics-insights' );
			$serial = 'qr7_' . $this->get_serial( $projectId . $from . $filter . $metric );
			$dimensions = 'ga:country';
			$local_filter = '';
			if ( $this->aiwp->config->options['ga_target_geomap'] ) {
				$dimensions = 'ga:city, ga:region';
				$country_codes = AIWP_Tools::get_countrycodes();
				if ( isset( $country_codes[$this->aiwp->config->options['ga_target_geomap']] ) ) {
					$local_filter = 'ga:country==' . ( $country_codes[$this->aiwp->config->options['ga_target_geomap']] );
					$title = __( "Cities from", 'analytics-insights' ) . ' ' . __( $country_codes[$this->aiwp->config->options['ga_target_geomap']] );
					$serial = 'qr7_' . $this->get_serial( $projectId . $from . $this->aiwp->config->options['ga_target_geomap'] . $filter . $metric );
				}
			}
			$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
			if ( $filter ) {
				$options['filters'] = 'ga:pagePath==' . $filter;
				if ( $local_filter ) {
					$options['filters'] .= ';' . $local_filter;
				}
			} else {
				if ( $local_filter ) {
					$options['filters'] = $local_filter;
				}
			}
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array( array( $title, __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data->getRows() as $row ) {
				if ( isset( $row[2] ) ) {
					$aiwp_data[] = array( esc_html( $row[0] ) . ', ' . esc_html( $row[1] ), (int) $row[2] );
				} else {
					$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
				}
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Org Charts (traffic channels, device categories)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $query
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_orgchart_data( $projectId, $from, $to, $query, $metric, $filter = '' ) {
			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:' . $query;
			$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
			if ( $filter ) {
				$options['filters'] = 'ga:pagePath==' . $filter;
			}
			$serial = 'qr8_' . $this->get_serial( $projectId . $from . $query . $filter . $metric );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( empty( $data->rows ) ) {
				// unable to render as an Org Chart, returns a numeric value to be handled by reportsx.js
				return - 21;
			}
			$block = ( 'channelGrouping' == $query ) ? __( "Channels", 'analytics-insights' ) : __( "Devices", 'analytics-insights' );
			$aiwp_data = array( array( '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totalsForAllResults'][$metrics] . '</div>', "" ) );
			foreach ( $data->getRows() as $row ) {
				$shrink = explode( " ", $row[0] );
				$aiwp_data[] = array( '<div style="color:black; font-size:1.1em">' . esc_html( $shrink[0] ) . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $row[1] . '</div>', '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totalsForAllResults'][$metrics] . '</div>' );
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Pie Charts (traffic mediums, serach engines, social networks, browsers, screen rsolutions, etc.)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $query
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_piechart_data( $projectId, $from, $to, $query, $metric, $filter = '' ) {
			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:' . $query;
			if ( 'source' == $query ) {
				$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
				if ( $filter ) {
					$options['filters'] = 'ga:medium==organic;ga:keyword!=(not set);ga:pagePath==' . $filter;
				} else {
					$options['filters'] = 'ga:medium==organic;ga:keyword!=(not set)';
				}
			} else {
				$options = array( 'dimensions' => $dimensions, 'sort' => '-' . $metrics, 'quotaUser' => $this->managequota . 'p' . $projectId );
				if ( $filter ) {
					$options['filters'] = 'ga:' . $query . '!=(not set);ga:pagePath==' . $filter;
				} else {
					$options['filters'] = 'ga:' . $query . '!=(not set)';
				}
			}
			$serial = 'qr10_' . $this->get_serial( $projectId . $from . $query . $filter . $metric );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array( array( __( "Type", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			$i = 0;
			$included = 0;
			foreach ( $data->getRows() as $row ) {
				if ( $i < 20 ) {
					$aiwp_data[] = array( str_replace( "(none)", "direct", esc_html( $row[0] ) ), (int) $row[1] );
					$included += $row[1];
					$i++;
				} else {
					break;
				}
			}
			$totals = $data->getTotalsForAllResults();
			$others = $totals[$metrics] - $included;
			if ( $others > 0 ) {
				$aiwp_data[] = array( __( 'Other', 'analytics-insights' ), $others );
			}
			return $aiwp_data;
		}

		/**
		 * Analytics data for Frontend Widget (chart data and totals)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $period
		 * @param
		 *            $anonim
		 * @return array|int
		 */
		public function frontend_widget_stats( $projectId, $from, $anonim ) {
			$content = '';
			$to = 'yesterday';
			$metrics = 'ga:sessions';
			$dimensions = 'ga:date,ga:dayOfWeekName';
			$options = array( 'dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId );
			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $metrics );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $options, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$aiwp_data = array( array( __( "Date", 'analytics-insights' ), __( "Sessions", 'analytics-insights' ) ) );
			if ( $anonim ) {
				$max_array = array();
				foreach ( $data->getRows() as $item ) {
					$max_array[] = $item[2];
				}
				$max = max( $max_array ) ? max( $max_array ) : 1;
			}
			foreach ( $data->getRows() as $row ) {
				$aiwp_data[] = array( date_i18n( __( 'l, F j, Y', 'analytics-insights' ), strtotime( $row[0] ) ), ( $anonim ? round( $row[2] * 100 / $max, 2 ) : (int) $row[2] ) );
			}
			$totals = $data->getTotalsForAllResults();
			return array( $aiwp_data, $anonim ? 0 : number_format_i18n( $totals['ga:sessions'] ) );
		}

		/**
		 * Analytics data for Realtime component (the real-time report)
		 *
		 * @param
		 *            $projectId
		 * @return array|int
		 */
		private function get_realtime( $projectId ) {
			$metrics = 'rt:activeUsers';
			$dimensions = 'rt:pagePath,rt:source,rt:keyword,rt:trafficType,rt:visitorType,rt:pageTitle';
			try {
				$serial = 'qr_realtimecache_' . $this->get_serial( $projectId );
				$transient = AIWP_Tools::get_cache( $serial );
				if ( false === $transient ) {
					if ( $this->gapi_errors_handler() ) {
						return - 23;
					}
					$data = $this->service->data_realtime->get( 'ga:' . $projectId, $metrics, array( 'dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId ) );
					AIWP_Tools::set_cache( $serial, $data, 55 );
				} else {
					$data = $transient;
				}
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts( 'midnight' );
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			}
			if ( $data->getRows() < 1 ) {
				return - 21;
			}
			$i = 0;
			$aiwp_data = $data;
			foreach ( $data->getRows() as $row ) {
				$strip = array_map( 'wp_kses_data', $row );
				$aiwp_data->rows[$i] = array_map( 'esc_html', $strip );
				$i++;
			}
			$this->aiwp->config->options['api_backoff'] = 0;
			$this->aiwp->config->set_plugin_options();
			return array( $aiwp_data );
		}

		/**
		 * Handles ajax requests and calls the needed methods
		 * @param
		 * 		$projectId
		 * @param
		 * 		$query
		 * @param
		 * 		$from
		 * @param
		 * 		$to
		 * @param
		 * 		$filter
		 * @return number|Deconf_Service_Analytics_GaData
		 */
		public function get( $projectId, $query, $from = false, $to = false, $filter = '', $metric = 'sessions' ) {
			if ( empty( $projectId ) || ! is_numeric( $projectId ) ) {
				wp_die( - 26 );
			}
			if ( in_array( $query, array( 'sessions', 'users', 'organicSearches', 'visitBounceRate', 'pageviews', 'uniquePageviews' ) ) ) {
				return $this->get_areachart_data( $projectId, $from, $to, $query, $filter );
			}
			if ( 'bottomstats' == $query ) {
				return $this->get_bottomstats( $projectId, $from, $to, $filter );
			}
			if ( 'locations' == $query ) {
				return $this->get_locations( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'referrers' == $query ) {
				return $this->get_referrers( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'contentpages' == $query ) {
				return $this->get_contentpages( $projectId, $from, $to, $metric, $filter );
			}
			if ( '404errors' == $query ) {
				$filter = $this->aiwp->config->options['pagetitle_404'];
				return $this->get_404errors( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'searches' == $query ) {
				return $this->get_searches( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'realtime' == $query ) {
				return $this->get_realtime( $projectId );
			}
			if ( 'channelGrouping' == $query || 'deviceCategory' == $query ) {
				return $this->get_orgchart_data( $projectId, $from, $to, $query, $metric, $filter );
			}
			if ( in_array( $query, array( 'medium', 'visitorType', 'socialNetwork', 'source', 'browser', 'operatingSystem', 'screenResolution', 'mobileDeviceBranding' ) ) ) {
				return $this->get_piechart_data( $projectId, $from, $to, $query, $metric, $filter );
			}
			wp_die( - 27 );
		}
	}
}
