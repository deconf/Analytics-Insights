<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

use Deconf\AIWP\Google\Service\Exception as GoogleServiceException;

if ( ! class_exists( 'AIWP_GAPI_Controller' ) ) {

	final class AIWP_GAPI_Controller {

		public $client;

		public $service;

		public $service_ga3_reporting;

		public $service_ga4_admin;

		public $service_ga4_data;

		public $timeshift;

		public $quotauser;

		private $aiwp;

		private $access = array( '220758964178-rhheb4146405g3fs6e4qjkk0rnf5q9q5.apps.googleusercontent.com', 'secret' );

		public function __construct() {
			$this->aiwp = AIWP();
			include_once ( AIWP_DIR . 'tools/vendor/autoload.php' );
			$this->client = new Deconf\AIWP\Google\Client();

			// add cURL server settings to Guzzle

			$httpoptions = array();

			$origin = AIWP_Tools::strip_protocol( get_site_url() );

			$httpoptions [ 'headers' ] = array( 'referer' => AIWP_CURRENT_VERSION, 'User-Agent' => $origin );

			if ( defined( 'WP_PROXY_HOST' ) && defined( 'WP_PROXY_PORT' ) ) {
				$httpoptions [ 'proxy' ] = WP_PROXY_HOST . ":". WP_PROXY_PORT;
				if ( defined( 'WP_PROXY_USERNAME' ) && defined( 'WP_PROXY_PASSWORD' ) ) {
					$httpoptions [ 'auth' ] = array( WP_PROXY_USERNAME, WP_PROXY_PASSWORD );
				}
			}

			if ( defined( 'AIWP_FORCE_IP_RESOLVE' ) ){

				if ( 'v4' == AIWP_FORCE_IP_RESOLVE ) {
					$httpoptions [ 'force_ip_resolve' ] = 'v4';
				}

				if ( 'v6' == AIWP_FORCE_IP_RESOLVE ) {
					$httpoptions [ 'force_ip_resolve' ] = 'v6';
				}

			}

			$httpClient = new Deconf\AIWP\GuzzleHttp\Client( $httpoptions );

			$this->client->setHttpClient( $httpClient );

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
			$this->quotauser = 'u' . get_current_user_id() . 's' . get_current_blog_id();
			if ( $this->aiwp->config->options['user_api'] ) {
				$this->client->setClientId( $this->aiwp->config->options['client_id'] );
				$this->client->setClientSecret( $this->aiwp->config->options['client_secret'] );
				$this->client->setRedirectUri( AIWP_URL . 'tools/oauth2callback.php' );
				define( 'AIWP_OAUTH2_REVOKE_URI', 'https://oauth2.googleapis.com/revoke' );
				define( 'AIWP_OAUTH2_TOKEN_URI', 'https://oauth2.googleapis.com/token' );
			} else {
				$this->client->setClientId( $this->access[0] );
				$this->client->setClientSecret( $this->access[1] );
				$this->client->setRedirectUri( AIWP_ENDPOINT_URL . 'oauth2callback.php' );
				define( 'AIWP_OAUTH2_REVOKE_URI', AIWP_ENDPOINT_URL . 'aiwp-revoke.php' );
				define( 'AIWP_OAUTH2_TOKEN_URI', AIWP_ENDPOINT_URL . 'aiwp-token.php' );
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
						if ( $this->client->isAccessTokenExpired() ) {
							$creds = $this->client->fetchAccessTokenWithRefreshToken( $this->client->getRefreshToken() );
							if ( $creds && isset( $creds['access_token'] ) ) {
								$this->aiwp->config->options['token'] = $this->client->getAccessToken();
							} else {
								$timeout = $this->get_timeouts();
								AIWP_Tools::set_error( $creds, $timeout );
								if ( isset( $creds['error'] ) && 'invalid_grant' == $creds['error'] ){
									$this->reset_token();
								}
							}
						}
					} catch ( GoogleServiceException $e ) {
						$timeout = $this->get_timeouts();
						AIWP_Tools::set_error( $e, $timeout );
						$this->reset_token();
					} catch ( Exception $e ) {
						$timeout = $this->get_timeouts();
						AIWP_Tools::set_error( $e, $timeout );
					}

					if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
						$this->aiwp->config->set_plugin_options( true );
					} else {
						$this->aiwp->config->set_plugin_options();
					}
				}
			}

			$this->service = new Deconf\AIWP\Google\Service\Analytics( $this->client );

			$this->service_ga4_admin = new Deconf\AIWP\Google\Service\GoogleAnalyticsAdmin( $this->client );

			$this->service_ga3_reporting = new Deconf\AIWP\Google\Service\AnalyticsReporting( $this->client );

			$this->service_ga4_data = new Deconf\AIWP\Google\Service\AnalyticsData( $this->client );

		}


		public function authenticate( $access_code ) {

			try {
					$this->client->fetchAccessTokenWithAuthCode( $access_code );
					return $this->client->getAccessToken();
				} catch ( GoogleServiceException $e ) {
					$timeout = $this->get_timeouts();
					AIWP_Tools::set_error( $e, $timeout );
				} catch ( Exception $e ) {
					$timeout = $this->get_timeouts();
					AIWP_Tools::set_error( $e, $timeout );
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

			if ( $all ){
				$this->aiwp->config->options['site_jail'] = "";
				$this->aiwp->config->options['sites_list'] = array();
				$this->aiwp->config->options['ga_profiles_list'] = array();
				$this->aiwp->config->options['ga4_webstreams_list'] = array();
				$this->aiwp->config->options['webstream_jail'] = '';
				$this->aiwp->config->options['tableid_jail'] = '';
				$this->aiwp->config->options['reporting_type'] = 0;
			}

			$this->aiwp->config->options['token'] = "";
			$this->aiwp->config->options['sites_list_locked'] = 0;

			if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
				$this->aiwp->config->set_plugin_options( true );
			} else {
				$this->aiwp->config->set_plugin_options();
			}

			if ( $token ) {
				try {
					$this->client->revokeToken( $token );
				} catch ( GoogleServiceException $e ) {
					return;
				} catch ( Exception $e ) {
					return;
				}
			}

		}


		/**
		 * Handles errors returned by GAPI Library
		 *
		 * @return boolean
		 */
		public function aiwp_api_errors_handler() {

			$errors = AIWP_Tools::get_cache( 'aiwp_api_errors' );

			//Proceed as normal if we don't know the error
			if ( false === $errors || ! isset( $errors[0] ) ) {
				return false;
			}

			//Reset the token since these are unrecoverable errors and need user intervention
			if ( isset( $errors[1][0]['reason'] ) && ( 'authError' == $errors[1][0]['reason'] || 'invalidParameter' == $errors[1][0]['reason'] || 'badRequest' == $errors[1][0]['reason'] || 'invalidCredentials' == $errors[1][0]['reason'] || 'insufficientPermissions' == $errors[1][0]['reason'] || 'required' == $errors[1][0]['reason'] ) ) {
				$this->reset_token();
				return $errors[0];
			}

			if ( 400 == $errors[0] || 401 == $errors[0] ) {
				return $errors[0];
			}

			//Backoff processing until the error timeouts, usually at midnight
			if ( isset( $errors[1][0]['reason'] ) && ( 'dailyLimitExceeded' == $errors[1][0]['reason'] || 'userRateLimitExceeded' == $errors[1][0]['reason'] || 'rateLimitExceeded' == $errors[1][0]['reason'] || 'quotaExceeded' == $errors[1][0]['reason'] ) ) {
				return $errors[0];
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
					return $errors[0];
				}
			}

			if ( 500 == $errors[0] || 503 == $errors[0] || $errors[0] < - 50 ) {
				return $errors[0];
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
				return 0;
			}
		}

		/**
		 * Retrieves all Universal Analytics Views with details
		 *
		 * @return array
		 */
		 public function refresh_profiles_ua() {

			try {

				$ga_profiles_list = array();
				$startindex = 1;
				$maxresults = 1000;
				$totalresults = 65535; // use something big

				while ( $startindex <= $totalresults ) {
					$profiles = $this->service->management_profiles->listManagementProfiles( '~all', '~all', array( 'start-index' => $startindex, 'max-results' => $maxresults ) );
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

				return $ga_profiles_list;

			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
			}

		}

		/**
		 * Retrieves all Google Analytics 4 Properties with details
		 *
		 * @return array
		 */
		public function refresh_profiles_ga4() {
			try {

				$ga4_webstreams_list = array();
				$pagesize = 200;
				$options = array( 'pageSize' => $pagesize );
				$pagetoken = 'start'; //populate with something evaluating as true

				while ( $pagetoken ){

					$listaccounts = $this->service_ga4_admin->accountSummaries->listAccountSummaries( $options );

					$accounts = $listaccounts->getAccountSummaries();
					$pagetoken = $listaccounts->getNextPageToken();

					if ( $pagetoken ){
						$options = array( 'pageSize' => $pagesize, 'pageToken' => $pagetoken );
					}

					if ( ! empty( $accounts ) ) {
						foreach ( $accounts as $account ) {
							$properties = $account->getPropertySummaries();
							if ( ! empty( $properties ) ) {
								foreach ( $properties as $property ) {
									$datastreams = $this->service_ga4_admin->properties_dataStreams->listPropertiesDataStreams( $property->getProperty() )->getDataStreams();
									if ( ! empty( $datastreams ) ) {
										foreach ( $datastreams as $datastream ) {
											$webstream = $datastream->getWebStreamData();
											if ( 'WEB_DATA_STREAM' == $datastream->type ) {
												$ga4_webstreams_list[] = array( $datastream->getDisplayName(), $datastream->getName(), $webstream->getDefaultUri(), $webstream->getMeasurementId() );
											}
										}
									}
								}
							}
						}
					}

				}

				return $ga4_webstreams_list;

			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
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
		 * Get and cache Google Analytics 3 Core Reports
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
		 * @return int|Deconf\AIWP\Google\Service\AnalyticsReporting\DateRangeValues
		 */
		private function handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial ) {
			try {
				if ( 'today' == $from ) {
					$interval = 'hourly';
				} else {
					$interval = 'daily';
				}
				$transient = AIWP_Tools::get_cache( $serial );
				if ( false === $transient ) {
					if ( $this->aiwp_api_errors_handler() ) {
						return $this->aiwp_api_errors_handler();
					}

					$quotauser = $this->get_serial( $this->quotauser . $projectId );

					// Create the DateRange object.
					$dateRange = new Deconf\AIWP\Google\Service\AnalyticsReporting\DateRange();
					$dateRange->setStartDate( $from );
					$dateRange->setEndDate( $to );

					// Create the Metrics object.
					if ( is_array( $metrics ) ){
						foreach ( $metrics as $value ){
							$metricobj = new Deconf\AIWP\Google\Service\AnalyticsReporting\Metric();
							$metricobj->setExpression( $value );
							$metric[] = $metricobj;
						}
					} else {
						$metricobj = new Deconf\AIWP\Google\Service\AnalyticsReporting\Metric();
						$metricobj->setExpression( $metrics );
						$metric[] = $metricobj;
					}

					// Create the ReportRequest object.
					$request = new Deconf\AIWP\Google\Service\AnalyticsReporting\ReportRequest();
					$request->setViewId( $projectId );
					$request->setDateRanges( $dateRange );
					$request->setMetrics( $metric );
					$request->setIncludeEmptyRows( true );

					// Create the Dimensions object.
					if ( $dimensions ){

						if ( is_array( $dimensions ) ){
							foreach ( $dimensions as $value ){
								$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsReporting\Dimension();
								$dimensionobj->setName( $value );
								$dimension[] = $dimensionobj;
							}
						} else {
							$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsReporting\Dimension();
							$dimensionobj->setName( $dimensions );
							$dimension[] = $dimensionobj;
						}

						$request->setDimensions( $dimension );
					}

					// Create the Filters
					if ( $filters ) {

						foreach ( $filters as $value ){
							$dimensionFilterobj = new Deconf\AIWP\Google\Service\AnalyticsReporting\DimensionFilter();
							$dimensionFilterobj->setDimensionName( $value[0] );
							$dimensionFilterobj->setOperator( $value[1] );
							$dimensionFilterobj->setExpressions( array( $value[2] ) );
							$dimensionFilterobj->setNot( $value[3] );
							$dimensionFilter[] = $dimensionFilterobj;
						}

						// Create the DimensionFilterClauses
						$dimensionFilterClause = new Deconf\AIWP\Google\Service\AnalyticsReporting\DimensionFilterClause();
						$dimensionFilterClause->setOperator( 'AND' );
						$dimensionFilterClause->setFilters( $dimensionFilter );

						$request->setDimensionFilterClauses( array( $dimensionFilterClause ) );

					}

					// Create the Ordering.
					if ( $sortby ){
						$ordering = new Deconf\AIWP\Google\Service\AnalyticsReporting\OrderBy();
						$ordering->setOrderType( 'VALUE' );
						$ordering->setSortOrder( 'DESCENDING' );
						$ordering->setFieldName( $metrics );
						$request->setOrderBys( $ordering );
					}

					$body = new Deconf\AIWP\Google\Service\AnalyticsReporting\GetReportsRequest();
					$body->setReportRequests( array( $request) );

					$response = $this->service_ga3_reporting->reports->batchGet( $body, array( 'quotaUser' => $quotauser ) );

					$reports = $response->getReports();

					$dataraw = $reports[0]->getData();

					$data['values'] = array();

					foreach ( $dataraw->getRows() as $row ) {

						$values = array();

						if ( isset( $row->getDimensions()[0] ) ){
							foreach ( $row->getDimensions() as $value){
								$values[] = $value;
							}
						}

						if ( isset( $row->getMetrics()[0] ) ){
							foreach ( $row->getMetrics()[0]->getValues() as $value){
								$values[] = $value;
							}
						}

						$data['values'][] = $values;

					}

					if ( isset( $dataraw->getTotals()[0]->getValues()[0] ) ){
						$data['totals'] = $dataraw->getTotals()[0]->getValues()[0];
					}

					AIWP_Tools::set_cache( $serial, $data, $this->get_timeouts( $interval ) );

				} else {
					$data = $transient;
				}
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			}
			$this->aiwp->config->options['api_backoff'] = 0;
			$this->aiwp->config->set_plugin_options();

			return $data;

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
				$dimensions = array(
					'ga:yearMonth',
					'ga:month'
				);
				$dayorhour = __( "Date", 'analytics-insights' );
			} else {
				$dimensions = array(
					'ga:date',
 				'ga:dayOfWeekName'
				);
				$dayorhour = __( "Date", 'analytics-insights' );
			}

			$filters = false;

			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}

			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $metrics . $filter );

			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, false, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			if ( empty( $data['values'] ) ) {
				// unable to render it as an Area Chart, returns a numeric value to be handled by reportsx.js
				return 621;
			}

			$aiwp_data = array( array( $dayorhour, $title ) );
			if ( 'today' == $from || 'yesterday' == $from ) {
				foreach ( $data['values'] as $row ) {
					$aiwp_data[] = array( (int) $row[0] . ':00', round( $row[1], 2 ) );
				}
			} else if ( '365daysAgo' == $from || '1095daysAgo' == $from ) {
				foreach ( $data['values'] as $row) {
					/*
					 * translators:
					 * Example: 'F, Y' will become 'November, 2015'
					 * For details see: https://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
					$aiwp_data[] = array( date_i18n( __( 'F, Y', 'analytics-insights' ), strtotime( $row[0] . '01' ) ), round( $row[2], 2 ) );
				}
			} else {
				foreach ( $data['values'] as $row ) {
					/*
					 * translators:
					 * Example: 'l, F j, Y' will become 'Thusday, November 17, 2015'
					 * For details see: https://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
					$aiwp_data[] = array( date_i18n( __( 'l, F j, Y', 'analytics-insights' ), strtotime( $row[0] ) ), round( $row[2], 2 ) );
				}
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for Bottom Stats (bottom stats on main report)
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

			$filters = false;

			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
				$metrics = array (
					'ga:uniquePageviews',
					'ga:users',
					'ga:pageviews',
					'ga:BounceRate',
					'ga:organicSearches',
					'ga:pageviewsPerSession',
					'ga:avgTimeOnPage',
					'ga:avgPageLoadTime',
					'ga:exitRate'
				);
			} else {
				$metrics = array (
					'ga:sessions',
					'ga:users',
					'ga:pageviews',
					'ga:BounceRate',
					'ga:organicSearches',
					'ga:pageviewsPerSession',
					'ga:avgTimeOnPage',
					'ga:avgPageLoadTime',
					'ga:avgSessionDuration'
				);
			}

			$sortby = false;

			$serial = 'qr3_' . $this->get_serial( $projectId . $from . $filter );
			$data = $this->handle_corereports( $projectId, $from, $to, $metrics, false, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array();

			$aiwp_data = $data['values'][0];

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
		 * Google Analytics 3 data for Table Charts (content pages)
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

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}

			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Pages", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );

			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for 404 Errors
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

			$dimensions = array (
				'ga:pagePath',
				'ga:fullReferrer'
			);

			$sortby = '-' . $metrics;

			$filters[] = array( 'ga:pageTitle', 'PARTIAL', $filter, false );

			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "404 Errors", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data['values'] as $row ) {
				$path = esc_html( $row[0] );
				$source = esc_html( $row[1] );
				$aiwp_data[] = array( "<strong>" . __( "URI:", 'analytics-insights' ) . "</strong> " . $path . "<br><strong>" . __( "Source:", 'analytics-insights' ) . "</strong> " . $source, (int) $row[2] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for Table Charts (referrers)
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

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
				$filters[] = array( 'ga:medium', 'EXACT', 'referral', false );
			} else {
				$filters[] = array( 'ga:medium', 'EXACT', 'referral', false );
			}

			$serial = 'qr5_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Referrers", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );

			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for Table Charts (searches)
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

			$dimensions = 'ga:source';

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
				$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
			} else {
				$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
			}

			$serial = 'qr5_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Search Engines", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );

			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for Table Charts (location reports)
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

			$title = __( "Countries", 'analytics-insights' );

			$serial = 'qr7_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$dimensions = 'ga:country';

			$local_filter = '';

			if ( $this->aiwp->config->options['ga_target_geomap'] ) {
				$dimensions = array (
					'ga:city',
					'ga:region'
				);

				$country_codes = AIWP_Tools::get_countrycodes();
				if ( isset( $country_codes[$this->aiwp->config->options['ga_target_geomap']] ) ) {
					$local_filter = array( 'ga:country', 'EXACT', ( $country_codes[$this->aiwp->config->options['ga_target_geomap']] ), false );
					$title = __( "Cities from", 'analytics-insights' ) . ' ' . __( $country_codes[$this->aiwp->config->options['ga_target_geomap']] );
					$serial = 'qr7_' . $this->get_serial( $projectId . $from . $this->aiwp->config->options['ga_target_geomap'] . $filter . $metric );
				}
			}

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter , false);
				if ( $local_filter ) {
					$filters[] = array ( 'ga:pagePath', 'EXACT', $filter, false );
					$filters[1] = $local_filter;
				}
			} else {
				if ( $local_filter ) {
					$filters[] = $local_filter;
				}
			}

			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( $title, __( ucfirst( $metric ), 'analytics-insights' ) ) );

			foreach ( $data['values'] as $row ) {
				if ( isset( $row[2] ) ) {
					$aiwp_data[] = array( esc_html( $row[0] ) . ', ' . esc_html( $row[1] ), (int) $row[2] );
				} else {
					$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
				}
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for Org Charts (traffic channels, device categories)
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

			$sortby = '-' . $metrics;


			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}

			$serial = 'qr8_' . $this->get_serial( $projectId . $from . $query . $filter . $metric );

			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			if ( empty( $data['values'] ) ) {
				// unable to render as an Org Chart, returns a numeric value to be handled by reportsx.js
				return 621;
			}

			$block = ( 'channelGrouping' == $query ) ? __( "Channels", 'analytics-insights' ) : __( "Devices", 'analytics-insights' );
			$aiwp_data = array( array( '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totals'] . '</div>', "" ) );
			foreach ( $data['values'] as $row ) {
				$shrink = explode( " ", $row[0] );
				$aiwp_data[] = array( '<div style="color:black; font-size:1.1em">' . esc_html( $shrink[0] ) . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $row[1] . '</div>', '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totals'] . '</div>' );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for Pie Charts (traffic mediums, serach engines, social networks, browsers, screen rsolutions, etc.)
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
			$sortby = false;
			$filters = false;

			if ( 'source' == $query ) {
				$sortby = '-' . $metrics;
				if ( $filter ) {
					$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
					$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
					$filters[] = array( 'ga:keyword', 'EXACT', '(not set)', true );
				} else {
					$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
					$filters[] = array( 'ga:keyword', 'EXACT', '(not set)', true );
				}
			} else {
				$sortby = '-' . $metrics;
				if ( $filter ) {
					$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
					$filters[] = array( 'ga:' . $query, 'EXACT', '(not set)', true );
				} else {
					$filters[] = array( 'ga:' . $query, 'EXACT', '(not set)', true );
				}
			}

			$serial = 'qr10_' . $this->get_serial( $projectId . $from . $query . $filter . $metric );

			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Type", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );

			$included = 0;
			foreach ( $data['values'] as $row ) {
					$aiwp_data[] = array( str_replace( "(none)", "direct", esc_html( $row[0] ) ), (int) $row[1] );
					$included += $row[1];
			}

			$totals = $data['totals'];
			$others = $totals - $included;
			if ( $others > 0 ) {
				$aiwp_data[] = array( __( 'Other', 'analytics-insights' ), $others );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 3 data for Frontend Widget (chart data and totals)
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

			$to = 'yesterday';
			$metrics = 'ga:sessions';
			$dimensions = array (
				'ga:date',
				'ga:dayOfWeekName'
			);

			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $metrics );


			$data =  $this->handle_corereports( $projectId, $from, $to, $metrics, $dimensions, false, false, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Date", 'analytics-insights' ), __( "Sessions", 'analytics-insights' ) ) );

			if ( $anonim ) {
				$max_array = array();
				foreach ( $data['values'] as $row ) {
					$max_array[] = $row[2];
				}
				$max = max( $max_array ) ? max( $max_array ) : 1;
			}

			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( date_i18n( __( 'l, F j, Y', 'analytics-insights' ), strtotime( $row[0] ) ), ( $anonim ? round( $row[2] * 100 / $max, 2 ) : (int) $row[2] ) );
			}
			$totals = $data['totals'];

			return array( $aiwp_data, $anonim ? 0 : number_format_i18n( $totals ) );

		}

		/**
		 * Google Analytics 3 data for Realtime component (the real-time report)
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

					if ( $this->aiwp_api_errors_handler() ) {
						return $this->aiwp_api_errors_handler();
					}

					$data = $this->service->data_realtime->get( 'ga:' . $projectId, $metrics, array( 'dimensions' => $dimensions, 'quotaUser' => $this->quotauser . 'p' . $projectId ) );

					AIWP_Tools::set_cache( $serial, $data, 55 );

				} else {

					$data = $transient;

				}
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			}

			if ( $data->getRows() < 1 ) {
				return 621;
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
		 * Google Analtyics 4 Reports Get and cache
		 *
		 * @param
		 *            $projectId
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
		 * @param
		 *            $limit
		 * @return int|Deconf\AIWP\Google\Service\AnalyticsReporting\DateRangeValues
		 */
		public function handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial, $limit = 0 ) {
			try {
				if ( 'today' == $from ) {
					$interval = 'hourly';
				} else {
					$interval = 'daily';
				}
				$transient = AIWP_Tools::get_cache( $serial );
				if ( false === $transient ) {
					if ( $this->aiwp_api_errors_handler() ) {
						return $this->aiwp_api_errors_handler();
					}

					$projectIdArr = explode( '/dataStreams/', $projectId );
					$projectId = $projectIdArr[0];

					$quotauser = $this->get_serial( $this->quotauser . $projectId );

					// Create the DateRange object.
					$dateRange = new Deconf\AIWP\Google\Service\AnalyticsData\DateRange();
					$dateRange->setStartDate( $from );
					$dateRange->setEndDate( $to );

					// Create the Metrics object.
					if ( is_array( $metrics ) ){
						foreach ( $metrics as $value ){
							$value = AIWP_Tools::ga3_ga4_mapping( $value );
							$metricobj = new Deconf\AIWP\Google\Service\AnalyticsData\Metric();
							$metricobj->setName( $value );
							$metric[] = $metricobj;
						}
					} else {
						$metrics = AIWP_Tools::ga3_ga4_mapping( $metrics );
						$metricobj = new Deconf\AIWP\Google\Service\AnalyticsData\Metric();
						$metricobj->setName( $metrics );
						$metric[] = $metricobj;
					}

					// Create the ReportRequest object.
					$request = new Deconf\AIWP\Google\Service\AnalyticsData\RunReportRequest();
					$request->setProperty( $projectId );
					$request->setDateRanges( $dateRange );
					$request->setMetrics( $metric );
					$request->setMetricAggregations( 'TOTAL' );
					$request->setKeepEmptyRows( true );

					// Create the Dimensions object.
					if ( $dimensions ){

						if ( is_array( $dimensions ) ){
							foreach ( $dimensions as $value ){
								$value = AIWP_Tools::ga3_ga4_mapping( $value );
								$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsData\Dimension();
								$dimensionobj->setName( $value );
								$dimension[] = $dimensionobj;
							}
						} else {
							$dimensions = AIWP_Tools::ga3_ga4_mapping( $dimensions );
							$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsData\Dimension();
							$dimensionobj->setName( $dimensions );
							$dimension[] = $dimensionobj;
						}

						$request->setDimensions( $dimension );
					}

					// Create the Filters
					if ( $filters ) {

						$dimensionFilterExpression = array();

						foreach ( $filters as $value ){
							$dimensionFilter = new Deconf\AIWP\Google\Service\AnalyticsData\Filter();
							$stringFilter = new Deconf\AIWP\Google\Service\AnalyticsData\StringFilter();
							$value[0] = AIWP_Tools::ga3_ga4_mapping( $value[0] );
							$dimensionFilter->setFieldName( $value[0] );
							$stringFilter->setValue( $value[2] );
							$dimensionFilter->setStringFilter( $stringFilter );

							$dimensionFilterExpressionobj = new Deconf\AIWP\Google\Service\AnalyticsData\FilterExpression();
							$notexpr = new Deconf\AIWP\Google\Service\AnalyticsData\FilterExpression();

							if ( $value[3] ){
								$dimensionFilterExpressionobj->setFilter( $dimensionFilter );
								$notexpr->setNotExpression( $dimensionFilterExpressionobj );
								$dimensionFilterExpression[] = $notexpr;
							} else {
								$dimensionFilterExpressionobj->setFilter( $dimensionFilter );
								$dimensionFilterExpression[] = $dimensionFilterExpressionobj;
							}

						}

						$dimensionFilterExpressionList = new Deconf\AIWP\Google\Service\AnalyticsData\FilterExpressionList();
						$dimensionFilterExpressionList->setExpressions( $dimensionFilterExpression );

						$dimensionFilterExpressionobj = new Deconf\AIWP\Google\Service\AnalyticsData\FilterExpression();
						if ( count( $dimensionFilterExpression ) > 1 ){
							$dimensionFilterExpressionobj->setAndGroup( $dimensionFilterExpressionList );
						} else{
							$dimensionFilterExpressionobj = $dimensionFilterExpression[0];
						}

						$request->setDimensionFilter( $dimensionFilterExpressionobj );

					}

					// Create the Ordering.
					if ( $sortby ){
						$ordering = new Deconf\AIWP\Google\Service\AnalyticsData\OrderBy();
						$metrics = AIWP_Tools::ga3_ga4_mapping( $metrics );
						$metricOrderBy = new Deconf\AIWP\Google\Service\AnalyticsData\MetricOrderBy();
						$metricOrderBy->setMetricName( $metrics );
						$ordering->setMetric( $metricOrderBy );
						$ordering->setDesc( true );
						$request->setOrderBys( $ordering );
					} else {
						if ( isset( $dimension[0] ) ){
							$dimensionOrderBy = new Deconf\AIWP\Google\Service\AnalyticsData\DimensionOrderBy();
							$dimensionOrderBy->setDimensionName($dimension[0]->getName());
							$dimensionOrderBy->setOrderType( 'NUMERIC' );
							$ordering = new Deconf\AIWP\Google\Service\AnalyticsData\OrderBy();
							$ordering->setDimension( $dimensionOrderBy );
							$ordering->setDesc( false );
							$request->setOrderBys( $ordering );
						}
					}

					if ($limit) {
						$request->setLimit($limit);
					}
					
					$response = $this->service_ga4_data->properties->runReport ( $projectId, $request, array( 'quotaUser' => $quotauser ) );

					$dataraw = $response;

					$data['values'] = array();

					foreach ( $dataraw->getRows() as $row ) {

						$values = array();

						if ( isset( $row->getDimensionValues()[0] ) ){
							foreach ( $row->getDimensionValues() as $item ){
								$values[] = $item->getValue();
							}
						}

						if ( isset( $row->getMetricValues()[0] ) ){
							foreach ( $row->getMetricValues() as $item){
								$values[] = $item->getValue();
							}
						}

						$data['values'][] = $values;

					}

					$data['totals'] = 0;

					if ( method_exists( $dataraw, 'getTotals') && isset( $dataraw->getTotals()[0]->getMetricValues()[0] ) ){
						$data['totals'] = $dataraw->getTotals()[0]->getMetricValues()[0]->getValue();
					}

					AIWP_Tools::set_cache( $serial, $data, $this->get_timeouts( $interval ) );

				} else {
					$data = $transient;
				}
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			}
			$this->aiwp->config->options['api_backoff'] = 0;
			$this->aiwp->config->set_plugin_options();

			return $data;

		}

		/**
		 * Google Analytics 4 data for Area Charts (Admin Dashboard Widget report)
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
		private function get_areachart_data_ga4( $projectId, $from, $to, $query, $filter = '' ) {

			$factor = 1;

			switch ( $query ) {
				case 'users' :
					$title = __( "Users", 'analytics-insights' );
					break;
				case 'pageviews' :
					$title = __( "Page Views", 'analytics-insights' );
					break;
				case 'visitBounceRate' :
					$title = __( "Bounce Rate", 'analytics-insights' );
					$factor = 100;
					break;
				case 'organicSearches' :
					$title = __( "Engagement", 'analytics-insights' );
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
				$dimensions = array(
					'ga:year',
					'ga:month'
				);
				$dayorhour = __( "Date", 'analytics-insights' );
			} else {
				$dimensions = array(
					'ga:date',
					'ga:dayOfWeekName'
				);
				$dayorhour = __( "Date", 'analytics-insights' );
			}

			$filters = false;

			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}

			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $metrics . $filter );

			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, false, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			if ( empty( $data['values'] ) ) {
				// unable to render it as an Area Chart, returns a numeric value to be handled by reportsx.js
				return 621;
			}

			$aiwp_data = array( array( $dayorhour, $title ) );
			if ( 'today' == $from || 'yesterday' == $from ) {

				for ( $i=0; $i<24; $i++ ){
					$fill_data[$i] = 0;
				}
				foreach ( $data['values'] as $row ) {
					if ( array_key_exists((int) $row[0], $fill_data) ){
						$fill_data[(int) $row[0]] = round( $row[1], 2 ) * $factor;
					}
				}
				foreach ( $fill_data as $key => $value ) {
					$aiwp_data[] = array( $key . ':00', $value );
				}

			} else if ( '365daysAgo' == $from || '1095daysAgo' == $from ) {

				$yesterday = date("Y-m-d", strtotime("now"));
				$offset = str_replace('daysAgo', '', $from);
				$xdaysago =  date("Y-m-d", strtotime("-" . $offset . " day"));

				$period = new DatePeriod(
					new DateTime($xdaysago),
					new DateInterval('P1M'),
					new DateTime($yesterday)
					);

				foreach ($period as $key => $value) {
					$fill_data[$value->format('Ym')] = 0;
				}

				foreach ( $data['values'] as $row ) {
					$key = $row[0] . $row[1];
					if ( array_key_exists($key, $fill_data) ){
						$fill_data[$key] = round( $row[2], 2 ) * $factor;
					}
				}

				foreach ( $fill_data as $key => $value ) {
					/*
					 * translators:
					 * Example: 'F, Y' will become 'November, 2015'
					 * For details see: https://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
					$aiwp_data[] = array( date_i18n( __( 'F, Y', 'analytics-insights' ), strtotime( $key . '01' ) ), $value );
				}
			} else {

				$yesterday = date("Y-m-d", strtotime("now"));
				$offset = str_replace('daysAgo', '', $from);
				$xdaysago =  date("Y-m-d", strtotime("-" . $offset . " day"));

				$period = new DatePeriod(
					new DateTime($xdaysago),
					new DateInterval('P1D'),
					new DateTime($yesterday)
					);

				foreach ($period as $key => $value) {
					$fill_data[$value->format('Ymd')] = 0;
				}

				foreach ( $data['values'] as $row ) {
					if ( array_key_exists($row[0], $fill_data) ){
						$fill_data[$row[0]] = round( $row[2], 2 ) * $factor;
					}
				}

				foreach ( $fill_data as $key => $value ) {
					/*
					 * translators:
					 * Example: 'l, F j, Y' will become 'Thusday, November 17, 2015'
					 * For details see: https://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
					$aiwp_data[] = array( date_i18n( __( 'l, F j, Y', 'analytics-insights' ), strtotime( $key ) ), $value );
				}
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Bottom Stats (bottom stats on main report)
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
		private function get_bottomstats_ga4( $projectId, $from, $to, $filter = '' ) {

			$filters = false;

			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
				$metrics = array (
					'ga:sessions',
					'ga:users',
					'ga:pageviews',
					'ga:BounceRate',
					'averageSessionDuration',
					'ga:pageviewsPerSession',
					'engagedSessions',
					'userEngagementDuration',
				);
			} else {
				$metrics = array (
					'ga:sessions',
					'ga:users',
					'ga:pageviews',
					'ga:BounceRate',
					'averageSessionDuration',
					'ga:pageviewsPerSession',
					'engagedSessions',
					'userEngagementDuration',
				);
			}

			$sortby = false;

			$serial = 'qr30_' . $this->get_serial( $projectId . $from . $filter );
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, false, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array();

			$aiwp_data = $data['values'][0];

			// i18n support
			$aiwp_data[0] = isset( $aiwp_data[0] ) ? number_format_i18n( $aiwp_data[0] ) : 0;
			$aiwp_data[1] = isset( $aiwp_data[1] ) ? number_format_i18n( $aiwp_data[1] ) : 0;
			$aiwp_data[2] = isset( $aiwp_data[2] ) ? number_format_i18n( $aiwp_data[2] ) : 0;
			$aiwp_data[3] = isset( $aiwp_data[3] ) ? number_format_i18n( $aiwp_data[3] * 100, 2 ) . '%' : '0%';
			$aiwp_data[4] = isset( $aiwp_data[4] ) ? AIWP_Tools::secondstohms( $aiwp_data[4] ) : '00:00:00';
			$aiwp_data[5] = isset( $aiwp_data[5] ) ? number_format_i18n( $aiwp_data[5], 2 ) : 0;
			$aiwp_data[6] = isset( $aiwp_data[6] ) ? number_format_i18n( $aiwp_data[6] ) : 0;;
			$aiwp_data[7] = isset( $aiwp_data[7] ) ? AIWP_Tools::secondstohms( $aiwp_data[7] ) : '00:00:00';


			// Get Organic Searches
			$metrics = 'ga:sessions';

			$dimensions = 'ga:' . 'channelGrouping';

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}

			$serial = 'qr9_' . $this->get_serial( $projectId . $from . 'channelGrouping' . $filter . 'ga:sessions' );

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

				$organic = 0;

			foreach ( $data['values'] as $row ) {
				if ( 'Organic Search' == $row[0] ){
					$organic = number_format_i18n( $row[1] );
				}
			}

			array_splice( $aiwp_data, 6, 0, $organic );

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Table Charts (location reports)
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
		private function get_locations_ga4( $projectId, $from, $to, $metric, $filter = '' ) {

			$metrics = 'ga:' . $metric;

			$title = __( "Countries", 'analytics-insights' );

			$serial = 'qr7_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$dimensions = 'ga:country';

			$local_filter = '';

			if ( $this->aiwp->config->options['ga_target_geomap'] ) {
				$dimensions = array (
					'ga:city',
					'ga:region'
				);

				$country_codes = AIWP_Tools::get_countrycodes();
				if ( isset( $country_codes[$this->aiwp->config->options['ga_target_geomap']] ) ) {
					$local_filter = array( 'ga:country', 'EXACT', ( $country_codes[$this->aiwp->config->options['ga_target_geomap']] ), false );
					$title = __( "Cities from", 'analytics-insights' ) . ' ' . __( $country_codes[$this->aiwp->config->options['ga_target_geomap']] );
					$serial = 'qr7_' . $this->get_serial( $projectId . $from . $this->aiwp->config->options['ga_target_geomap'] . $filter . $metric );
				}
			}

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter , false);
				if ( $local_filter ) {
					$filters[] = array ( 'ga:pagePath', 'EXACT', $filter, false );
					$filters[1] = $local_filter;
				}
			} else {
				if ( $local_filter ) {
					$filters[] = $local_filter;
				}
			}

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( $title, __( ucfirst( $metric ), 'analytics-insights' ) ) );

			foreach ( $data['values'] as $row ) {
				if ( isset( $row[2] ) ) {
					$aiwp_data[] = array( esc_html( $row[0] ) . ', ' . esc_html( $row[1] ), (int) $row[2] );
				} else {
					$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
				}
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Table Charts (content pages)
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
		private function get_contentpages_ga4( $projectId, $from, $to , $metric, $filter = '' ) {

			$metrics = 'ga:' . $metric;

			$dimensions = 'ga:pageTitle';

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}

			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Pages", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );

			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Org Charts (traffic channels, device categories)
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
		private function get_orgchart_data_ga4( $projectId, $from, $to, $query, $metric, $filter = '' ) {

			$metrics = 'ga:' . $metric;

			$dimensions = 'ga:' . $query;

			$sortby = '-' . $metrics;


			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}

			$serial = 'qr8_' . $this->get_serial( $projectId . $from . $query . $filter . $metric );

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			if ( empty( $data['values'] ) ) {
				// unable to render as an Org Chart, returns a numeric value to be handled by reportsx.js
				return 621;
			}

			$block = ( 'channelGrouping' == $query ) ? __( "Channels", 'analytics-insights' ) : __( "Devices", 'analytics-insights' );
			$aiwp_data = array( array( '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totals'] . '</div>', "" ) );
			foreach ( $data['values'] as $row ) {
				$shrink = explode( " ", $row[0] );
				if ( isset( $shrink[1] ) ){
					$shrink[0] = esc_html( $shrink[0] ) . '<br>' . esc_html( $shrink[1] );
				}
				if ( 'Unassigned' !== $shrink[0] ){
					$aiwp_data[] = array( '<div style="color:black; font-size:1.1em">' . $shrink[0] . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $row[1] . '</div>', '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totals'] . '</div>' );
				}
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Table Charts (referrers)
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
		private function get_referrers_ga4( $projectId, $from, $to, $metric, $filter = '' ) {

			$metrics = 'ga:' . $metric;

			$dimensions = 'ga:source';

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
				$filters[] = array( 'ga:medium', 'EXACT', 'referral', false );
			} else {
				$filters[] = array( 'ga:medium', 'EXACT', 'referral', false );
			}

			$serial = 'qr5_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Referrers", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );

			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Table Charts (searches)
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
		private function get_searches_ga4( $projectId, $from, $to, $metric, $filter = '' ) {

			$metrics = 'ga:' . $metric;

			$dimensions = 'ga:source';

			$sortby = '-' . $metrics;

			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
				$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
			} else {
				$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
			}

			$serial = 'qr6_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Search Engines", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( esc_html( $row[0] ), (int) $row[1] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Pie Charts (traffic mediums, serach engines, languages, browsers, screen rsolutions, etc.)
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
		private function get_piechart_data_ga4( $projectId, $from, $to, $query, $metric, $filter = '' ) {

			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:' . $query;
			$sortby =  false;
			$filters = false;

			if ( 'source' == $query ) {
				$sortby = '-' . $metrics;
				if ( $filter ) {
					$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
					$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
				} else {
					$filters[] = array( 'ga:medium', 'EXACT', 'organic', false );
				}
			} else {
				$sortby = '-' . $metrics;
				if ( $filter ) {
					$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
					$filters[] = array( 'ga:' . $query, 'EXACT', '(not set)', true );
				} else {
					$filters[] = array( 'ga:' . $query, 'EXACT', '(not set)', true );
				}
			}

			$serial = 'qr10_' . $this->get_serial( $projectId . $from . $query . $filter . $metric );

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Type", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );

			$included = 0;
			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( str_replace( "(none)", "direct", esc_html( $row[0] ) ), (int) $row[1] );
				$included += $row[1];
			}

			$totals = $data['totals'];
			$others = $totals - $included;
			if ( $others > 0 ) {
				$aiwp_data[] = array( __( 'Other', 'analytics-insights' ), $others );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for 404 Errors
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @return array|int
		 */
		private function get_404errors_ga4( $projectId, $from, $to, $metric, $filter = "Page Not Found" ) {

			$metrics = 'ga:' . $metric;

			$dimensions = array (
				'ga:pagePath',
				'ga:fullReferrer'
			);

			$sortby = '-' . $metrics;

			$filters[] = array( 'ga:pageTitle', 'PARTIAL', $filter, false );

			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );

			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "404 Errors", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data['values'] as $row ) {
				$path = esc_html( $row[0] );
				$source = esc_html( $row[1] );
				$aiwp_data[] = array( "<strong>" . __( "URI:", 'analytics-insights' ) . "</strong> " . $path . "<br><strong>" . __( "Source:", 'analytics-insights' ) . "</strong> " . $source, (int) $row[2] );
			}

			return $aiwp_data;

		}

		/**
		 * Google Analytics 4 data for Frontend Widget (chart data and totals)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $period
		 * @param
		 *            $anonim
		 * @return array|int
		 */
		public function frontend_widget_stats_ga4( $projectId, $from, $anonim ) {

			$to = 'yesterday';
			$metrics = 'ga:sessions';
			$dimensions = array (
				'ga:date',
				'ga:dayOfWeekName'
			);

			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $metrics );


			$data =  $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, false, false, $serial );

			if ( is_numeric( $data ) ) {
				return $data;
			}

			$aiwp_data = array( array( __( "Date", 'analytics-insights' ), __( "Sessions", 'analytics-insights' ) ) );

			if ( $anonim ) {
				$max_array = array();
				foreach ( $data['values'] as $row ) {
					$max_array[] = $row[2];
				}
				$max = max( $max_array ) ? max( $max_array ) : 1;
			}

			foreach ( $data['values'] as $row ) {
				$aiwp_data[] = array( date_i18n( __( 'l, F j, Y', 'analytics-insights' ), strtotime( $row[0] ) ), ( $anonim ? round( $row[2] * 100 / $max, 2 ) : (int) $row[2] ) );
			}
			$totals = $data['totals'];

			return array( $aiwp_data, $anonim ? 0 : number_format_i18n( $totals ) );

		}

		/**
		 * Google Analytics 4 data for Realtime component (the real-time report)
		 *
		 * @param
		 *            $projectId
		 * @return array|int
		 */
		private function get_realtime_ga4( $projectId ) {
			$metrics = 'activeUsers';
			$dimensions = array('unifiedScreenName');
			$dimensions1 = array('deviceCategory');

			$projectIdArr = explode( '/dataStreams/',$projectId );
			$projectId = $projectIdArr[0];

			$quotauser = $this->get_serial( $this->quotauser . $projectId );

			try {
				$serial = 'qr_realtimecache_' . $this->get_serial( $projectId );
				$transient = AIWP_Tools::get_cache( $serial );
				if ( false === $transient ) {

					if ( $this->aiwp_api_errors_handler() ) {
						return $this->aiwp_api_errors_handler();
					}

					$request = new Deconf\AIWP\Google\Service\AnalyticsData\RunRealtimeReportRequest();
					$request1 = new Deconf\AIWP\Google\Service\AnalyticsData\RunRealtimeReportRequest();

					// Create the Metrics object.
					$metrics = AIWP_Tools::ga3_ga4_mapping( $metrics );
					$metricobj = new Deconf\AIWP\Google\Service\AnalyticsData\Metric();
					$metricobj->setName( $metrics );
					$metric[] = $metricobj;

					// Create the ReportRequest object.
					$request->setMetrics( $metric );
					$request->setMetricAggregations( 'TOTAL' );

					// Create the ReportRequest object.
					$request1->setMetrics( $metric );
					$request1->setMetricAggregations( 'TOTAL' );

					// Create the Dimensions object.
					if ( $dimensions ){

						if ( is_array( $dimensions ) ){
							foreach ( $dimensions as $value ){
								$value = AIWP_Tools::ga3_ga4_mapping( $value );
								$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsData\Dimension();
								$dimensionobj->setName( $value );
								$dimension[] = $dimensionobj;
							}
						} else {
							$dimensions = AIWP_Tools::ga3_ga4_mapping( $dimensions );
							$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsData\Dimension();
							$dimensionobj->setName( $dimensions );
							$dimension[] = $dimensionobj;
						}

						$request->setDimensions( $dimension );

					}

					// Create the Dimensions object.
					if ( $dimensions1 ){

						if ( is_array( $dimensions1 ) ){
							foreach ( $dimensions1 as $value ){
								$value = AIWP_Tools::ga3_ga4_mapping( $value );
								$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsData\Dimension();
								$dimensionobj->setName( $value );
								$dimension1[] = $dimensionobj;
							}
						} else {
							$dimensions1 = AIWP_Tools::ga3_ga4_mapping( $dimensions );
							$dimensionobj = new Deconf\AIWP\Google\Service\AnalyticsData\Dimension();
							$dimensionobj->setName( $dimensions );
							$dimension1[] = $dimensionobj;
						}

						$request->setDimensions( $dimension );
						$request1->setDimensions( $dimension1 );

					}

					$data = $this->service_ga4_data->properties->runRealtimeReport( $projectId, $request, array( 'quotaUser' => $quotauser ) );
					$category = $this->service_ga4_data->properties->runRealtimeReport( $projectId, $request1, array( 'quotaUser' => $quotauser ) );

					AIWP_Tools::set_cache( $serial, array( $data, $category), 55 );

				} else {

					$data = $transient[0];
					$category = $transient[1];

				}
			} catch ( GoogleServiceException $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			} catch ( Exception $e ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $e, $timeout );
				return $e->getCode();
			}

			if ( $data->getRows() < 1 ) {
				return 621;
			}

			$aiwp_data['rows'] = array();

			foreach ( $data->getRows() as $row ) {

				$values = array();

				if ( isset( $row->getDimensionValues()[0] ) ){
					foreach ( $row->getDimensionValues() as $item ){
						$values[] = esc_html( $item->getValue() );
					}
				}

				if ( isset( $row->getMetricValues()[0] ) ){
					foreach ( $row->getMetricValues() as $item){
						$values[] = esc_html( $item->getValue() );
					}
				}

				$aiwp_data['rows'][] = $values;

			}

			$aiwp_data['category'] = array();

			foreach ( $category->getRows() as $row ) {

				$values = array();

				if ( isset( $row->getDimensionValues()[0] ) ){
					foreach ( $row->getDimensionValues() as $item ){
						$values[] = esc_html( $item->getValue() );
					}
				}

				if ( isset( $row->getMetricValues()[0] ) ){
					foreach ( $row->getMetricValues() as $item){
						$values[] = esc_html( $item->getValue() );
					}
				}

				$aiwp_data['category'][] = $values;

			}


			$aiwp_data['totals'] = 0;

			if ( method_exists( $category, 'getTotals') && isset( $category->getTotals()[0]->getMetricValues()[0] ) ){
				$aiwp_data['totals'] = (int)$category->getTotals()[0]->getMetricValues()[0]->getValue();
			}

			return $aiwp_data;

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
		 * @return number|Deconf\AIWP\Google\Service\Analytics\GaData
		 */
		public function get( $projectId, $query, $from = false, $to = false, $filter = '', $metric = 'sessions' ) {

			if ( empty( $projectId ) || '' == $projectId || 'Disabled' == $projectId ) {
				wp_die( 626 );
			}

			if ( in_array( $query, array( 'sessions', 'users', 'organicSearches', 'visitBounceRate', 'pageviews', 'uniquePageviews' ) ) ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_areachart_data_ga4( $projectId, $from, $to, $query, $filter );
				} else {
					return $this->get_areachart_data( $projectId, $from, $to, $query, $filter );
				}
			}
			if ( 'bottomstats' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_bottomstats_ga4( $projectId, $from, $to, $filter );
				} else {
					return $this->get_bottomstats( $projectId, $from, $to, $filter );
				}
			}
			if ( 'locations' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_locations_ga4( $projectId, $from, $to, $metric, $filter );
				} else {
					return $this->get_locations( $projectId, $from, $to, $metric, $filter );
				}
			}
			if ( 'contentpages' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_contentpages_ga4( $projectId, $from, $to, $metric, $filter );
				} else {
					return $this->get_contentpages( $projectId, $from, $to, $metric, $filter );
				}
			}
			if ( 'referrers' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_referrers_ga4( $projectId, $from, $to, $metric, $filter );
				} else {
					return $this->get_referrers( $projectId, $from, $to, $metric, $filter );
				}
			}
			if ( 'searches' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_searches_ga4( $projectId, $from, $to, $metric, $filter );
				} else {
					return $this->get_searches( $projectId, $from, $to, $metric, $filter );
			 }
			}
			if ( '404errors' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					$filter = $this->aiwp->config->options['pagetitle_404'];
					return $this->get_404errors_ga4( $projectId, $from, $to, $metric, $filter );
				} else {
					$filter = $this->aiwp->config->options['pagetitle_404'];
					return $this->get_404errors( $projectId, $from, $to, $metric, $filter );}
			}
			if ( 'realtime' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_realtime_ga4( $projectId );
				} else {
					return $this->get_realtime( $projectId );
				}
			}
			if ( 'channelGrouping' == $query || 'deviceCategory' == $query ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_orgchart_data_ga4( $projectId, $from, $to, $query, $metric, $filter );
				} else {
					return $this->get_orgchart_data( $projectId, $from, $to, $query, $metric, $filter );
				}
			}
			if ( in_array( $query, array( 'medium', 'visitorType', 'socialNetwork', 'source', 'browser', 'operatingSystem', 'screenResolution', 'mobileDeviceBranding' ) ) ) {
				if ( $this->aiwp->config->options['reporting_type'] ) {
					return $this->get_piechart_data_ga4( $projectId, $from, $to, $query, $metric, $filter );
				} else {
					return $this->get_piechart_data( $projectId, $from, $to, $query, $metric, $filter );
				}
			}

			wp_die( 627 );

		}
	}
}
