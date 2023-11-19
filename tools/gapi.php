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

		private $client_id;

		private $client_secret;

		private $redirect_uri;

		private $state;

		private $token_uri;

		private $revoke_uri;

		public function __construct() {
			$this->aiwp = AIWP();
			$this->quotauser = 'u' . get_current_user_id() . 's' . get_current_blog_id();
			$security = wp_create_nonce( 'aiwp_security' );
			if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
				$state_uri = network_admin_url( 'admin.php?page=aiwp_settings' ) . '&aiwp_security=' . $security;
			} else {
				$state_uri = admin_url( 'admin.php?page=aiwp_settings' ) . '&aiwp_security=' . $security;
			}
			$this->state = $state_uri;
			if ( $this->aiwp->config->options['user_api'] ) {
				$this->client_id = $this->aiwp->config->options['client_id'];
				$this->client_secret = $this->aiwp->config->options['client_secret'];
				$this->redirect_uri = AIWP_URL . 'tools/oauth2callback.php';
				$this->token_uri = 'https://oauth2.googleapis.com/token';
				$this->revoke_uri = 'https://oauth2.googleapis.com/revoke';
			} else {
				$this->client_id = '220758964178-rhheb4146405g3fs6e4qjkk0rnf5q9q5.apps.googleusercontent.com';
				$this->client_secret = 'GOCSPX';
				$this->redirect_uri = AIWP_ENDPOINT_URL . 'oauth2callback.php';
				$this->token_uri = AIWP_ENDPOINT_URL . 'aiwp-token.php';
				$this->revoke_uri = AIWP_ENDPOINT_URL . 'aiwp-revoke.php';
			}
		}

		/**
		 * Creates the oauth2 link for Google API authorization
		 * @return string
		 */
		public function createAuthUrl() {
			$scope = 'https://www.googleapis.com/auth/analytics.readonly';
			// @formatter:off
			$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?';
			$query_arr = array(
				'client_id' => $this->client_id,
				'redirect_uri' => $this->redirect_uri,
				'response_type' => 'code',
				'scope' => $scope,
				'state' => $this->state,
				'access_type' => 'offline',
				'prompt' => 'consent',
			);
			// @formatter:on
			$auth_url = $auth_url . http_build_query( $query_arr );
			return $auth_url;
		}

		/**
		 * Handles the exchange of an access code with a token
		 * @param string $access_code
		 * @return string|mixed
		 */
		public function authenticate( $access_code ) {
			// @formatter:off
			$token_data = array(
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'code' => $access_code,
				'redirect_uri' => $this->redirect_uri,
				'grant_type' => 'authorization_code',
			);
			$request_args = array( 'body' => $token_data, 'headers' => array( 'Referer' => AIWP_CURRENT_VERSION ) );
			// @formatter:on
			$response = wp_remote_post( $this->token_uri, $request_args );
			if ( is_wp_error( $response ) ) {
				$timeout = $this->get_timeouts();
				AIWP_Tools::set_error( $response, $timeout );
				return false;
			}
			$body = wp_remote_retrieve_body( $response );
			$token = json_decode( $body, true );
			if ( isset( $token['error'] ) ) {
				$timeout = $this->get_timeouts();
				$error = new WP_Error();
				if ( isset( $token['error']['code'] ) && isset( $token['error']['code'] ) && isset( $token['error']['status'] ) ) {
					$error->add( $token['error']['code'], $token['error']['message'], array( $token['error']['status'], 'trying to exchange access code for token' ) );
				} else if ( isset( $token['error'] ) && isset( $token['error_description'] ) ) {
					$error->add( $token['error'], $token['error_description'], 'trying to exchange access code for token' );
				}
				AIWP_Tools::set_error( $error, $timeout );
				return false;
			}
			if ( isset( $token['access_token'] ) ) {
				return $token;
			} else {
				return false;
			}
		}

		/**
		 * Handles the token refresh process
		 * @return string|number|boolean
		 */
		public function refresh_token() {
			$token = (array) $this->aiwp->config->options['token'];
			$refresh_token = $token['refresh_token'];
			$challenge = ( isset( $token['challenge'] ) && $token['challenge'] ) ? $token['challenge'] : '';
			if ( ! $token || ! isset( $token['expires_in'] ) || ( $token['created'] + ( $token['expires_in'] - 30 ) ) < time() ) {
				// @formatter:off
				$post_data = array(
					'client_id' => $this->client_id,
					'client_secret' => $this->aiwp->config->options['user_api'] ? $this->client_secret : $this->client_secret . '-' . $challenge,
					'refresh_token' => $refresh_token,
					'grant_type' => 'refresh_token'
				);
				// @formatter:on
				$request_args = array( 'body' => $post_data, 'headers' => array( 'Referer' => AIWP_CURRENT_VERSION ) );
				if ( $this->aiwp->config->options['user_api'] ) {
					$token_uri = 'https://oauth2.googleapis.com/token';
				} else {
					$token_uri = $challenge ? 'https://oauth2.googleapis.com/token' : AIWP_ENDPOINT_URL . 'aiwp-token.php';
				}
				$response = wp_remote_post( $token_uri, $request_args );
				if ( is_wp_error( $response ) ) {
					$timeout = $this->get_timeouts();
					AIWP_Tools::set_error( $response, $timeout );
				} else {
					$body = wp_remote_retrieve_body( $response );
					if ( is_string( $body ) && ! empty( $body ) ) {
						$newtoken = json_decode( $body, true );
						if ( isset( $newtoken['error'] ) ) {
							$timeout = $this->get_timeouts();
							$error = new WP_Error();
							if ( isset( $newtoken['error']['code'] ) && isset( $newtoken['error']['code'] ) && isset( $newtoken['error']['status'] ) ) {
								$error->add( $newtoken['error']['code'], $newtoken['error']['message'], array( $newtoken['error']['status'], 'trying to refresh token' ) );
							} else if ( isset( $newtoken['error'] ) && isset( $newtoken['error_description'] ) ) {
								$error->add( $newtoken['error'], $newtoken['error_description'], 'trying to refresh token' );
							}
							AIWP_Tools::set_error( $error, $timeout );
							return false;
						}
						if ( ! empty( $newtoken ) && isset( $newtoken['access_token'] ) ) {
							if ( ! isset( $newtoken['created'] ) ) {
								$newtoken['created'] = time();
							}
							if ( ! isset( $newtoken['refresh_token'] ) ) {
								$newtoken['refresh_token'] = $refresh_token;
							}
							if ( ! isset( $newtoken['challenge'] ) && $newtoken['challenge'] ) {
								$newtoken['challenge'] = $challenge;
							}
							$this->aiwp->config->options['token'] = $newtoken;
						} else {
							$this->aiwp->config->options['token'] = false;
						}
					} else {
						$this->aiwp->config->options['token'] = false;
					}
					if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
						$this->aiwp->config->set_plugin_options( true );
					} else {
						$this->aiwp->config->set_plugin_options();
					}
				}
			}
			if ( $this->aiwp->config->options['token'] ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Handles the token reset process
		 *
		 * @param
		 *            $all
		 */
		public function reset_token( $all = false ) {
			$token = (array) $this->aiwp->config->options['token'];
			if ( $all ) {
				$this->aiwp->config->options['site_jail'] = '';
				$this->aiwp->config->options['sites_list'] = array();
				$this->aiwp->config->options['ga4_webstreams_list'] = array();
				$this->aiwp->config->options['webstream_jail'] = '';
			}
			$this->aiwp->config->options['token'] = false;
			$this->aiwp->config->options['sites_list_locked'] = 0;
			if ( is_multisite() && $this->aiwp->config->options['network_mode'] ) {
				$this->aiwp->config->set_plugin_options( true );
			} else {
				$this->aiwp->config->set_plugin_options();
			}
			if ( isset( $token['refresh_token'] ) ) {
				// @formatter:off
				$post_data = array(
					'client_id' => $this->client_id,
					'client_secret' => $this->client_secret,
					'refresh_token' => $token['refresh_token'],
				);
				// @formatter:on
				$request_args = array( 'body' => $post_data, 'headers' => array( 'Referer' => AIWP_CURRENT_VERSION ) );
				$response = wp_remote_post( $this->revoke_uri, $request_args );
			}
		}

		/**
		 * Handles errors returned by GAPI Library
		 *
		 * @return boolean
		 */
		public function api_errors_handler() {
			$errors = AIWP_Tools::get_cache( 'aiwp_api_errors' );
			// Proceed as normal if we don't know the error
			if ( false === $errors || ! isset( $errors[0] ) ) {
				return false;
			}
			// Reset the token since these are unrecoverable errors and need user intervention
			// We can also add 'INVALID_ARGUMENT'
			if ( isset( $errors[2][0] ) && ( 'INVALID_ARGUMENTS' == $errors[2][0] || 'UNAUTHENTICATED' == $errors[2][0] || 'PERMISSION_DENIED' == $errors[2][0] ) ) {
				$this->reset_token();
				return $errors[0];
			}
			// Reset the token since these are unrecoverable errors and need user intervention
			// We can also add 'invalid_grant'
			if ( isset( $errors[0] ) && ( 'invalid_grant' == $errors[0] || 'invalid_token' == $errors[0] ) ) {
				$this->reset_token();
				return $errors[0];
			}
			if ( 401 == $errors[0] || 403 == $errors[0] ) {
				return $errors[0];
			}
			// Back-off processing until the error timeouts, usually at midnight
			if ( isset( $errors[1][0]['reason'] ) && ( 'dailyLimitExceeded' == $errors[1][0]['reason'] || 'userRateLimitExceeded' == $errors[1][0]['reason'] || 'rateLimitExceeded' == $errors[1][0]['reason'] || 'quotaExceeded' == $errors[1][0]['reason'] ) ) {
				return $errors[0];
			}
			// Back-off system for subsequent requests - an Auth error generated after a Service request
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
			if ( 500 == $errors[0] || 503 == $errors[0] ) {
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
		 * Retrieves all Google Analytics 4 Properties with details
		 *
		 * @return array
		 */
		public function refresh_profiles_ga4() {
			if ( $this->aiwp->config->options['token'] ) {
				$this->refresh_token();
			}
			$token = (array) $this->aiwp->config->options['token'];
			$access_token = $token['access_token'];
			$pageSize = 100;
			// @formatter:off
			$headers = array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type' => 'application/json'
			);
			// @formatter:on
			$accountPageToken = null;
			do {
				$accountsEndpoint = "https://analyticsadmin.googleapis.com/v1beta/accounts?pageSize={$pageSize}";
				if ( $accountPageToken ) {
					$accountsEndpoint .= "&pageToken={$accountPageToken}";
				}
				$accountsResponse = wp_remote_get( $accountsEndpoint, array( 'headers' => $headers ) );
				if ( is_wp_error( $accountsResponse ) ) {
					$timeout = $this->get_timeouts();
					AIWP_Tools::set_error( $accountsResponse, $timeout );
					return $accountsResponse->get_error_code();
				} else {
					$accountsBody = wp_remote_retrieve_body( $accountsResponse );
					$accountsData = json_decode( $accountsBody, true );
					if ( isset( $accountsData['error'] ) ) {
						$timeout = $this->get_timeouts();
						$error = new WP_Error();
						if ( isset( $accountsData['error']['code'] ) && isset( $accountsData['error']['code'] ) && isset( $accountsData['error']['status'] ) ) {
							$error->add( $accountsData['error']['code'], $accountsData['error']['message'], array( $accountsData['error']['status'], 'trying to refresh token' ) );
						} else if ( isset( $accountsData['error'] ) && isset( $accountsData['error_description'] ) ) {
							$error->add( $accountsData['error'], $accountsData['error_description'], 'trying to refresh token' );
						}
						AIWP_Tools::set_error( $error, $timeout );
						return $error->get_error_code();
					}
					if ( isset( $accountsData['accounts'] ) ) {
						foreach ( $accountsData['accounts'] as $account ) {
							$propertyPageToken = null;
							do {
								$propertiesEndpoint = "https://analyticsadmin.googleapis.com/v1beta/properties?filter=parent:{$account['name']}&pageSize={$pageSize}";
								if ( $propertyPageToken ) {
									$propertiesEndpoint .= "&pageToken={$propertyPageToken}";
								}
								$propertiesResponse = wp_remote_get( $propertiesEndpoint, array( 'headers' => $headers ) );
								if ( is_wp_error( $propertiesResponse ) ) {
									$timeout = $this->get_timeouts();
									AIWP_Tools::set_error( $propertiesResponse, $timeout );
									return $propertiesResponse->get_error_code();
								} else {
									$propertiesBody = wp_remote_retrieve_body( $propertiesResponse );
									$propertiesData = json_decode( $propertiesBody, true );
									if ( isset( $propertiesData['error'] ) ) {
										$timeout = $this->get_timeouts();
										$error = new WP_Error();
										if ( isset( $propertiesData['error']['code'] ) && isset( $propertiesData['error']['code'] ) && isset( $propertiesData['error']['status'] ) ) {
											$error->add( $propertiesData['error']['code'], $propertiesData['error']['message'], array( $propertiesData['error']['status'], 'trying to refresh token' ) );
										} else if ( isset( $propertiesData['error'] ) && isset( $propertiesData['error_description'] ) ) {
											$error->add( $propertiesData['error'], $propertiesData['error_description'], 'trying to refresh token' );
										}
										AIWP_Tools::set_error( $error, $timeout );
										return $error->get_error_code();
									}
									if ( isset( $propertiesData['properties'] ) ) {
										foreach ( $propertiesData['properties'] as $property ) {
											$datastreamsPageToken = null;
											do {
												$datastreamsEndpoint = "https://analyticsadmin.googleapis.com/v1beta/{$property['name']}/dataStreams?pageSize={$pageSize}";
												if ( $datastreamsPageToken ) {
													$datastreamsEndpoint .= "&pageToken={$datastreamsPageToken}";
												}
												$datastreamsResponse = wp_remote_get( $datastreamsEndpoint, array( 'headers' => $headers ) );
												if ( is_wp_error( $datastreamsResponse ) ) {
													$timeout = $this->get_timeouts();
													AIWP_Tools::set_error( $datastreamsResponse, $timeout );
													return $datastreamsResponse->get_error_code();
												} else {
													$datastreamsBody = wp_remote_retrieve_body( $datastreamsResponse );
													$datastreamsData = json_decode( $datastreamsBody, true );
													if ( isset( $datastreamsData['error'] ) ) {
														$timeout = $this->get_timeouts();
														$error = new WP_Error();
														if ( isset( $datastreamsData['error']['code'] ) && isset( $datastreamsData['error']['code'] ) && isset( $datastreamsData['error']['status'] ) ) {
															$error->add( $datastreamsData['error']['code'], $datastreamsData['error']['message'], array( $datastreamsData['error']['status'], 'trying to refresh token' ) );
														} else if ( isset( $datastreamsData['error'] ) && isset( $datastreamsData['error_description'] ) ) {
															$error->add( $datastreamsData['error'], $datastreamsData['error_description'], 'trying to refresh token' );
														}
														AIWP_Tools::set_error( $error, $timeout );
														return $error->get_error_code();
													}
													if ( isset( $datastreamsData['dataStreams'] ) ) {
														foreach ( $datastreamsData['dataStreams'] as $datastream ) {
															if ( 'WEB_DATA_STREAM' == $datastream['type'] ) {
																$timetz = new DateTimeZone( $property['timeZone'] );
																$localtime = new DateTime( 'now', $timetz );
																$timeshift = strtotime( $localtime->format( 'Y-m-d H:i:s' ) ) - time();
																$ga4_webstreams_list[] = array( $datastream['displayName'], $datastream['name'], $datastream['webStreamData']['defaultUri'], $datastream['webStreamData']['measurementId'], $timeshift, $property['timeZone'] );
															}
														}
														$datastreamsPageToken = isset( $datastreamsData['nextPageToken'] ) ? $datastreamsData['nextPageToken'] : null;
													}
												}
											} while ( $datastreamsPageToken );
										}
									}
									$propertyPageToken = isset( $propertiesData['nextPageToken'] ) ? $propertiesData['nextPageToken'] : null;
								}
							} while ( $propertyPageToken );
						}
					}
					$accountPageToken = isset( $accountsData['nextPageToken'] ) ? $accountsData['nextPageToken'] : null;
				}
			} while ( $accountPageToken );
			return $ga4_webstreams_list;
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
		 * Google Analtyics 4 Reports Get and cache
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
		 * @return 			int
		 */
		private function handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial ) {
			if ( $this->aiwp->config->options['token'] ) {
				$this->refresh_token();
			}
			if ( 'today' == $from ) {
				$interval = 'hourly';
			} else {
				$interval = 'daily';
			}
			$transient = AIWP_Tools::get_cache( $serial );
			if ( false === $transient ) {
				if ( $this->api_errors_handler() ) {
					return $this->api_errors_handler();
				}
				$projectIdArr = explode( '/dataStreams/', $projectId );
				$projectId = $projectIdArr[0];
				$api_url = 'https://analyticsdata.googleapis.com/v1beta/' . $projectId . ':runReport';
				$quotauser = $this->get_serial( $this->quotauser . $projectId );
				$api_url = $api_url . '?quotaUser=' . $quotauser;
				$token = (array) $this->aiwp->config->options['token'];
				if ( isset( $token['access_token'] ) ) {
					$access_token = $token['access_token'];
				} else {
					return 624;
				}
				// @formatter:off
				$headers = array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type' => 'application/json',
				);
				// @formatter:on
				$request_body = array( 'dateRanges' => array( 'startDate' => $from, 'endDate' => $to ) );
				if ( is_array( $metrics ) ) {
					$request_body['metrics'] = array();
					foreach ( $metrics as $metric ) {
						$metric = AIWP_Tools::ga3_ga4_mapping( $metric );
						$request_body['metrics'][] = array( 'name' => $metric );
					}
				} else {
					$metric = AIWP_Tools::ga3_ga4_mapping( $metrics );
					$request_body['metrics'] = array();
					$request_body['metrics'][] = array( 'name' => $metric );
				}
				$request_body['metricAggregations'] = 'TOTAL';
				$request_body['keepEmptyRows'] = 'true';
				if ( $dimensions ) {
					if ( is_array( $dimensions ) ) {
						$request_body['dimensions'] = array();
						foreach ( $dimensions as $dimension ) {
							$dimension = AIWP_Tools::ga3_ga4_mapping( $dimension );
							$request_body['dimensions'][] = array( 'name' => $dimension );
						}
					} else {
						$dimension = AIWP_Tools::ga3_ga4_mapping( $dimensions );
						$request_body['dimensions'] = array();
						$request_body['dimensions'][] = array( 'name' => $dimension );
					}
				}
				if ( $filters ) {
					if ( count( $filters ) === 1 ) {
						$filter = $filters[0];
						$filter[0] = AIWP_Tools::ga3_ga4_mapping( $filter[0] );
						$fieldName = $filter[0];
						$filterMatch = $filter[1];
						$filterValue = $filter[2];
						$filterData = array( 'fieldName' => $fieldName, 'stringFilter' => array( 'matchType' => $filterMatch, 'value' => $filterValue ) );
						if ( $filter[3] ) {
							$request_body['dimensionFilter']['notExpression']['filter'] = $filterData;
						} else {
							$request_body['dimensionFilter']['filter'] = $filterData;
						}
					} else {
						$filterExpressions = array();
						foreach ( $filters as $filter ) {
							$filter[0] = AIWP_Tools::ga3_ga4_mapping( $filter[0] );
							$fieldName = $filter[0];
							$filterMatch = $filter[1];
							$filterValue = $filter[2];
							$filterData = array( 'fieldName' => $fieldName, 'stringFilter' => array( 'matchType' => $filterMatch, 'value' => $filterValue ) );
							if ( $filter[3] ) {
								$notExpression = array();
								$notExpression['notExpression'] = array( 'filter' => $filterData );
								$filterExpressions[] = $notExpression;
							} else {
								$filterExpressions[] = array( 'filter' => $filterData );
							}
							$request_body['dimensionFilter']['andGroup'] = array( 'expressions' => $filterExpressions );
						}
					}
				}
				if ( $sortby ) {
					$metric = AIWP_Tools::ga3_ga4_mapping( $metrics );
					$metricName = $metric;
					$desc = true;
					$orderCriteria = array( 'metricName' => $metricName );
					$request_body['orderBys']['metric'] = $orderCriteria;
					$request_body['orderBys']['desc'] = $desc;
				} else {
					if ( is_array( $dimensions ) ) {
						$dimension = AIWP_Tools::ga3_ga4_mapping( $dimensions[0] );
						$dimensionName = $dimension;
						$orderType = 'NUMERIC';
						$desc = false;
						$orderCriteria = array( 'dimensionName' => $dimensionName, 'orderType' => $orderType );
						$request_body['orderBys']['dimension'] = $orderCriteria;
						$request_body['orderBys']['desc'] = $desc;
					}
				}
				$request_body_json = json_encode( $request_body );
				$args = array( 'headers' => $headers, 'body' => $request_body_json );
				$response = wp_remote_post( $api_url, $args );
				if ( is_wp_error( $response ) ) {
					$timeout = $this->get_timeouts();
					AIWP_Tools::set_error( $response, $timeout );
					return $response->get_error_code();
				} else {
					$response_body = wp_remote_retrieve_body( $response );
					$response_data = json_decode( $response_body, true );
					if ( isset( $response_data['error'] ) ) {
						$timeout = $this->get_timeouts();
						$error = new WP_Error();
						if ( isset( $response_data['error']['code'] ) && isset( $response_data['error']['code'] ) && isset( $response_data['error']['status'] ) ) {
							$error->add( $response_data['error']['code'], $response_data['error']['message'], array( $response_data['error']['status'], 'trying to refresh token' ) );
						} else if ( isset( $response_data['error'] ) && isset( $response_data['error_description'] ) ) {
							$error->add( $response_data['error'], $response_data['error_description'], 'trying to refresh token' );
						}
						AIWP_Tools::set_error( $error, $timeout );
						return $error->get_error_code();
					}
					if ( isset( $response_data['rows'] ) ) {
						$data['values'] = array();
						foreach ( $response_data['rows'] as $row ) {
							$values = array();
							if ( isset( $row['dimensionValues'][0] ) ) {
								foreach ( $row['dimensionValues'] as $item ) {
									$values[] = $item['value'];
								}
							}
							if ( isset( $row['metricValues'][0] ) ) {
								foreach ( $row['metricValues'] as $item ) {
									$values[] = $item['value'];
								}
							}
							$data['values'][] = $values;
						}
						$data['totals'] = 0;
						if ( isset( $response_data['totals'][0]['metricValues'][0]['value'] ) ) {
							$data['totals'] = $response_data['totals'][0]['metricValues'][0]['value'];
						}
						AIWP_Tools::set_cache( $serial, $data, $this->get_timeouts( $interval ) );
					} else {
						return 621;
					}
				}
			} else {
				$data = $transient;
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
				$dimensions = array( 'ga:year', 'ga:month' );
				$dayorhour = __( "Date", 'analytics-insights' );
			} else {
				$dimensions = array( 'ga:date', 'ga:dayOfWeekName' );
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
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
			}
			$aiwp_data = array( array( $dayorhour, $title ) );
			if ( 'today' == $from || 'yesterday' == $from ) {
				for ( $i = 0; $i < 24; $i++ ) {
					$fill_data[$i] = 0;
				}
				foreach ( $data['values'] as $row ) {
					if ( array_key_exists( (int) $row[0], $fill_data ) ) {
						$fill_data[(int) $row[0]] = round( $row[1], 2 ) * $factor;
					}
				}
				foreach ( $fill_data as $key => $value ) {
					$aiwp_data[] = array( $key . ':00', $value );
				}
			} else if ( '365daysAgo' == $from || '1095daysAgo' == $from ) {
				$yesterday = date( "Y-m-d", strtotime( "now" ) );
				$offset = str_replace( 'daysAgo', '', $from );
				$xdaysago = date( "Y-m-d", strtotime( "-" . $offset . " day" ) );
				$period = new DatePeriod( new DateTime( $xdaysago ), new DateInterval( 'P1M' ), new DateTime( $yesterday ) );
				foreach ( $period as $key => $value ) {
					$fill_data[$value->format( 'Ym' )] = 0;
				}
				foreach ( $data['values'] as $row ) {
					$key = $row[0] . $row[1];
					if ( array_key_exists( $key, $fill_data ) ) {
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
				$yesterday = date( "Y-m-d", strtotime( "now" ) );
				$offset = str_replace( 'daysAgo', '', $from );
				$xdaysago = date( "Y-m-d", strtotime( "-" . $offset . " day" ) );
				$period = new DatePeriod( new DateTime( $xdaysago ), new DateInterval( 'P1D' ), new DateTime( $yesterday ) );
				foreach ( $period as $key => $value ) {
					$fill_data[$value->format( 'Ymd' )] = 0;
				}
				foreach ( $data['values'] as $row ) {
					if ( array_key_exists( $row[0], $fill_data ) ) {
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
				// @formatter:off
				$metrics = array(
					'ga:sessions',
					'ga:users',
					'ga:pageviews',
					'ga:BounceRate',
					'averageSessionDuration',
					'ga:pageviewsPerSession',
					'engagedSessions',
 				'userEngagementDuration',
				);
				// @formatter:on
			} else {
				// @formatter:off
				$metrics = array(
					'ga:sessions',
					'ga:users',
					'ga:pageviews',
					'ga:BounceRate',
					'averageSessionDuration',
					'ga:pageviewsPerSession',
					'engagedSessions',
					'userEngagementDuration',
				);
				// @formatter:on
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
			$aiwp_data[6] = isset( $aiwp_data[6] ) ? number_format_i18n( $aiwp_data[6] ) : 0;
			;
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
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			$organic = 0;
			foreach ( $data['values'] as $row ) {
				if ( 'Organic Search' == $row[0] ) {
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
				$dimensions = array( 'ga:city', 'ga:region' );
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
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
				if ( $local_filter ) {
					$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
					$filters[1] = $local_filter;
				}
			} else {
				if ( $local_filter ) {
					$filters[] = $local_filter;
				}
			}
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
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
		private function get_contentpages_ga4( $projectId, $from, $to, $metric, $filter = '' ) {
			$metrics = 'ga:' . $metric;
			$dimensions = 'ga:pageTitle';
			$sortby = '-' . $metrics;
			$filters = false;
			if ( $filter ) {
				$filters[] = array( 'ga:pagePath', 'EXACT', $filter, false );
			}
			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
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
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
			}
			$block = ( 'channelGrouping' == $query ) ? __( "Channels", 'analytics-insights' ) : __( "Devices", 'analytics-insights' );
			$aiwp_data = array( array( '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totals'] . '</div>', "" ) );
			foreach ( $data['values'] as $row ) {
				$shrink = explode( " ", $row[0] );
				if ( isset( $shrink[1] ) ) {
					$shrink[0] = esc_html( $shrink[0] ) . '<br>' . esc_html( $shrink[1] );
				}
				if ( 'Unassigned' !== $shrink[0] ) {
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
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
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
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
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
			$sortby = false;
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
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
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
			$dimensions = array( 'ga:pagePath', 'ga:fullReferrer', 'ga:pageTitle' );
			$sortby = '-' . $metrics;
			$filters[] = array( 'ga:pageTitle', 'CONTAINS', $filter, false );
			$serial = 'qr4_' . $this->get_serial( $projectId . $from . $filter . $metric );
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
			}
			$aiwp_data = array( array( __( "404 Errors", 'analytics-insights' ), __( ucfirst( $metric ), 'analytics-insights' ) ) );
			foreach ( $data['values'] as $row ) {
				$path = esc_html( $row[0] );
				$source = esc_html( $row[1] );
				$aiwp_data[] = array( "<strong>" . __( "URI:", 'analytics-insights' ) . "</strong> " . $path . "<br><strong>" . __( "Source:", 'analytics-insights' ) . "</strong> " . $source, (int) $row[3] );
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
			$dimensions = array( 'ga:date', 'ga:dayOfWeekName' );
			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $metrics );
			$data = $this->handle_corereports_ga4( $projectId, $from, $to, $metrics, $dimensions, false, false, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( isset( $data['values'] ) && empty( $data['values'] ) ) {
				return 621;
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
			if ( $this->aiwp->config->options['token'] ) {
				$this->refresh_token();
			}
			$metrics = 'activeUsers';
			$dimensions = array( 'unifiedScreenName' );
			$dimensions1 = array( 'deviceCategory' );
			$projectIdArr = explode( '/dataStreams/', $projectId );
			$projectId = $projectIdArr[0];
			$api_url = 'https://analyticsdata.googleapis.com/v1beta/' . $projectId . ':runRealtimeReport';
			$quotauser = $this->get_serial( $this->quotauser . $projectId );
			$api_url = $api_url . '?quotaUser=' . $quotauser;
			$serial = 'qr_realtimecache_' . $this->get_serial( $projectId );
			$transient = AIWP_Tools::get_cache( $serial );
			if ( false === $transient ) {
				if ( $this->api_errors_handler() ) {
					return $this->api_errors_handler();
				}
				if ( is_array( $metrics ) ) {
					$request_body['metrics'] = array();
					foreach ( $metrics as $metric ) {
						$metric = AIWP_Tools::ga3_ga4_mapping( $metric );
						$request_body['metrics'][] = array( 'name' => $metric );
					}
				} else {
					$metric = AIWP_Tools::ga3_ga4_mapping( $metrics );
					$request_body['metrics'] = array();
					$request_body['metrics'][] = array( 'name' => $metric );
				}
				$request_body['metricAggregations'] = 'TOTAL';
				if ( $dimensions ) {
					if ( is_array( $dimensions ) ) {
						$request_body['dimensions'] = array();
						foreach ( $dimensions as $dimension ) {
							$dimension = AIWP_Tools::ga3_ga4_mapping( $dimension );
							$request_body['dimensions'][] = array( 'name' => $dimension );
						}
					} else {
						$dimension = AIWP_Tools::ga3_ga4_mapping( $dimensions );
						$request_body['dimensions'] = array();
						$request_body['dimensions'][] = array( 'name' => $dimension );
					}
				}
				$token = (array) $this->aiwp->config->options['token'];
				if ( isset( $token['access_token'] ) ) {
					$access_token = $token['access_token'];
				} else {
					return 624;
				}
				// Get Page Data
				$headers = array( 'Authorization' => 'Bearer ' . $access_token, 'Content-Type' => 'application/json' );
				$request_body_json = json_encode( $request_body );
				$args = array( 'headers' => $headers, 'body' => $request_body_json );
				$response = wp_remote_post( $api_url, $args );
				if ( is_wp_error( $response ) ) {
					$timeout = $this->get_timeouts();
					AIWP_Tools::set_error( $response, $timeout );
					return $response->get_error_code();
				} else {
					$response_body = wp_remote_retrieve_body( $response );
					$response_data = json_decode( $response_body, true );
					if ( isset( $response_data['error'] ) ) {
						$timeout = $this->get_timeouts();
						$error = new WP_Error();
						if ( isset( $response_data['error']['code'] ) && isset( $response_data['error']['code'] ) && isset( $response_data['error']['status'] ) ) {
							$error->add( $response_data['error']['code'], $response_data['error']['message'], array( $response_data['error']['status'], 'trying to refresh token' ) );
						} else if ( isset( $response_data['error'] ) && isset( $response_data['error_description'] ) ) {
							$error->add( $response_data['error'], $response_data['error_description'], 'trying to refresh token' );
						}
						AIWP_Tools::set_error( $error, $timeout );
						return $error->get_error_code();
					}
					$data = $response_data;
				}
				// Get Device Category Data
				if ( $dimensions1 ) {
					if ( is_array( $dimensions1 ) ) {
						$request_body['dimensions'] = array();
						foreach ( $dimensions1 as $dimension ) {
							$dimension = AIWP_Tools::ga3_ga4_mapping( $dimension );
							$request_body['dimensions'][] = array( 'name' => $dimension );
						}
					} else {
						$dimension = AIWP_Tools::ga3_ga4_mapping( $dimensions1 );
						$request_body['dimensions'] = array();
						$request_body['dimensions'][] = array( 'name' => $dimension );
					}
				}
				$headers = array( 'Authorization' => 'Bearer ' . $access_token, 'Content-Type' => 'application/json' );
				$request_body_json = json_encode( $request_body );
				$args = array( 'headers' => $headers, 'body' => $request_body_json );
				$response = wp_remote_post( $api_url, $args );
				if ( is_wp_error( $response ) ) {
					$timeout = $this->get_timeouts();
					AIWP_Tools::set_error( $response, $timeout );
					return $response->get_error_code();
				} else {
					$response_body = wp_remote_retrieve_body( $response );
					$response_data = json_decode( $response_body, true );
					if ( isset( $response_data['error'] ) ) {
						$timeout = $this->get_timeouts();
						$error = new WP_Error();
						if ( isset( $response_data['error']['code'] ) && isset( $response_data['error']['code'] ) && isset( $response_data['error']['status'] ) ) {
							$error->add( $response_data['error']['code'], $response_data['error']['message'], array( $response_data['error']['status'], 'trying to refresh token' ) );
						} else if ( isset( $response_data['error'] ) && isset( $response_data['error_description'] ) ) {
							$error->add( $response_data['error'], $response_data['error_description'], 'trying to refresh token' );
						}
						AIWP_Tools::set_error( $error, $timeout );
						return $error->get_error_code();
					}
					$category = $response_data;
				}
				AIWP_Tools::set_cache( $serial, array( $data, $category ), 55 );
			} else {
				$data = $transient[0];
				$category = $transient[1];
			}
			if ( ! isset( $data['rows'] ) ) {
				return 621;
			}
			if ( isset( $data['rows'] ) ) {
				$aiwp_data['rows'] = array();
				foreach ( $data['rows'] as $row ) {
					$values = array();
					if ( isset( $row['dimensionValues'][0] ) ) {
						foreach ( $row['dimensionValues'] as $item ) {
							$values[] = $item['value'];
						}
					}
					if ( isset( $row['metricValues'][0] ) ) {
						foreach ( $row['metricValues'] as $item ) {
							$values[] = $item['value'];
						}
					}
					$aiwp_data['rows'][] = $values;
				}
			}
			if ( isset( $category['rows'] ) ) {
				$aiwp_data['category'] = array();
				foreach ( $category['rows'] as $row ) {
					$values = array();
					if ( isset( $row['dimensionValues'][0] ) ) {
						foreach ( $row['dimensionValues'] as $item ) {
							$values[] = $item['value'];
						}
					}
					if ( isset( $row['metricValues'][0] ) ) {
						foreach ( $row['metricValues'] as $item ) {
							$values[] = $item['value'];
						}
					}
					$aiwp_data['category'][] = $values;
				}
			}
			$aiwp_data['totals'] = 0;
			if ( isset( $category['totals'][0]['metricValues'][0]['value'] ) ) {
				$aiwp_data['totals'] = $data['totals'][0]['metricValues'][0]['value'];
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
		 * @return number
		 */
		public function get( $projectId, $query, $from = false, $to = false, $filter = '', $metric = 'sessions' ) {
			if ( empty( $projectId ) || '' == $projectId || 'Disabled' == $projectId ) {
				wp_die( 626 );
			}
			if ( in_array( $query, array( 'sessions', 'users', 'organicSearches', 'visitBounceRate', 'pageviews', 'uniquePageviews' ) ) ) {
				return $this->get_areachart_data_ga4( $projectId, $from, $to, $query, $filter );
			}
			if ( 'bottomstats' == $query ) {
				return $this->get_bottomstats_ga4( $projectId, $from, $to, $filter );
			}
			if ( 'locations' == $query ) {
				return $this->get_locations_ga4( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'contentpages' == $query ) {
				return $this->get_contentpages_ga4( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'referrers' == $query ) {
				return $this->get_referrers_ga4( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'searches' == $query ) {
				return $this->get_searches_ga4( $projectId, $from, $to, $metric, $filter );
			}
			if ( '404errors' == $query ) {
				$filter = $this->aiwp->config->options['pagetitle_404'];
				return $this->get_404errors_ga4( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'realtime' == $query ) {
				return $this->get_realtime_ga4( $projectId );
			}
			if ( 'channelGrouping' == $query || 'deviceCategory' == $query ) {
				return $this->get_orgchart_data_ga4( $projectId, $from, $to, $query, $metric, $filter );
			}
			if ( in_array( $query, array( 'medium', 'visitorType', 'socialNetwork', 'source', 'browser', 'operatingSystem', 'screenResolution', 'mobileDeviceBranding' ) ) ) {
				return $this->get_piechart_data_ga4( $projectId, $from, $to, $query, $metric, $filter );
			}
			wp_die( 627 );
		}
	}
}
