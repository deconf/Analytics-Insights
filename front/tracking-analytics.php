<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2017 Alin Marcu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();
if ( ! class_exists( 'AIWP_Tracking_Analytics_Base' ) ) {

	class AIWP_Tracking_Analytics_Base {

		protected $aiwp;

		protected $gaid;

		protected $mid;

		public function __construct() {
			$this->aiwp = AIWP();
			if ( $this->aiwp->config->options['tableid_jail'] ){
				$profile = AIWP_Tools::get_selected_profile( $this->aiwp->config->options['ga_profiles_list'], $this->aiwp->config->options['tableid_jail'] );
				$this->gaid = sanitize_text_field( $profile[2] );
			} else {
				$this->gaid = '';
			}
			if ( $this->aiwp->config->options['webstream_jail'] ) {
				$webstream = AIWP_Tools::get_selected_profile( $this->aiwp->config->options['ga4_webstreams_list'], $this->aiwp->config->options['webstream_jail'] );
				$this->mid = sanitize_text_field( $webstream[3] );
			} else {
				$this->mid = '';
			}
			if( 'ga4tracking' == $this->aiwp->config->options ['tracking_type'] ){
				$this->gaid = $this->mid;
			}
		}

		protected function build_custom_dimensions() {
			$custom_dimensions = array();
			if ( $this->aiwp->config->options['ga_author_dimindex'] && ( is_single() || is_page() ) ) {
				global $post;
				$author_id = $post->post_author;
				$author_name = get_the_author_meta( 'display_name', $author_id );
				$index = (int) $this->aiwp->config->options['ga_author_dimindex'];
				$custom_dimensions[$index] = sanitize_text_field( $author_name );
			}
			if ( $this->aiwp->config->options['ga_pubyear_dimindex'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y', $post->ID );
				$index = (int) $this->aiwp->config->options['ga_pubyear_dimindex'];
				$custom_dimensions[$index] = (int) $date;
			}
			if ( $this->aiwp->config->options['ga_pubyearmonth_dimindex'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y-m', $post->ID );
				$index = (int) $this->aiwp->config->options['ga_pubyearmonth_dimindex'];
				$custom_dimensions[$index] = sanitize_text_field( $date );
			}
			if ( $this->aiwp->config->options['ga_category_dimindex'] && is_category() ) {
				$fields = array();
				$index = (int) $this->aiwp->config->options['ga_category_dimindex'];
				$custom_dimensions[$index] = sanitize_text_field( single_tag_title( '', false ) );
			}
			if ( $this->aiwp->config->options['ga_category_dimindex'] && is_single() ) {
				global $post;
				$categories = get_the_category( $post->ID );
				foreach ( $categories as $category ) {
					$index = (int) $this->aiwp->config->options['ga_category_dimindex'];
					$custom_dimensions[$index] = sanitize_text_field( $category->name );
					break;
				}
			}
			if ( $this->aiwp->config->options['ga_tag_dimindex'] && is_single() ) {
				global $post;
				$fields = array();
				$post_tags_list = '';
				$post_tags_array = get_the_tags( $post->ID );
				if ( $post_tags_array ) {
					foreach ( $post_tags_array as $tag ) {
						$post_tags_list .= sanitize_text_field( $tag->name ) . ', ';
					}
				}
				$post_tags_list = rtrim( $post_tags_list, ', ' );
				if ( $post_tags_list ) {
					$index = (int) $this->aiwp->config->options['ga_tag_dimindex'];
					$custom_dimensions[$index] = sanitize_text_field( $post_tags_list );
				}
			}
			if ( $this->aiwp->config->options['ga_user_dimindex'] ) {
				$fields = array();
				$index = (int) $this->aiwp->config->options['ga_user_dimindex'];
				$custom_dimensions[$index] = is_user_logged_in() ? 'registered' : 'guest';
			}
			return $custom_dimensions;
		}

		protected function is_event_tracking( $opt, $with_pagescrolldepth = true ) {
			if ( $this->aiwp->config->options['ga_event_tracking'] || $this->aiwp->config->options['ga_aff_tracking'] || $this->aiwp->config->options['ga_hash_tracking'] || $this->aiwp->config->options['ga_formsubmit_tracking'] ) {
				return true;
			}
			if ( $this->aiwp->config->options['ga_pagescrolldepth_tracking'] && $with_pagescrolldepth ) {
				return true;
			}
			return false;
		}
	}
}
if ( ! class_exists( 'AIWP_Tracking_Analytics_Common' ) ) {

	class AIWP_Tracking_Analytics_Common extends AIWP_Tracking_Analytics_Base {

		protected $commands;

		public function __construct() {
			parent::__construct();
			$this->load_scripts();
			if ( $this->aiwp->config->options['optimize_tracking'] && $this->aiwp->config->options['optimize_pagehiding'] && $this->aiwp->config->options['optimize_containerid'] ) {
				add_action( 'wp_head', array( $this, 'optimize_output' ), 99 );
			}
		}

		/**
		 * Styles & Scripts load
		 */
		private function load_scripts() {
			if ( $this->is_event_tracking( true ) ) {
				$root_domain = AIWP_Tools::get_root_domain();
				wp_enqueue_script( 'aiwp-tracking-analytics-events', AIWP_URL . 'front/js/tracking-analytics-events' . AIWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), AIWP_CURRENT_VERSION, $this->aiwp->config->options['trackingevents_infooter'] );
				if ( $this->aiwp->config->options['ga_pagescrolldepth_tracking'] ) {
					wp_enqueue_script( 'aiwp-pagescrolldepth-tracking', AIWP_URL . 'front/js/tracking-scrolldepth' . AIWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), AIWP_CURRENT_VERSION, $this->aiwp->config->options['trackingevents_infooter'] );
				}
				/* @formatter:off */
				wp_localize_script( 'aiwp-tracking-analytics-events', 'aiwpUAEventsData', array(
					'options' => array(
						'event_tracking' => $this->aiwp->config->options['ga_event_tracking'],
						'event_downloads' => sanitize_text_field($this->aiwp->config->options['ga_event_downloads']),
						'event_bouncerate' => $this->aiwp->config->options['ga_event_bouncerate'],
						'aff_tracking' => $this->aiwp->config->options['ga_aff_tracking'],
						'event_affiliates' =>  sanitize_text_field($this->aiwp->config->options['ga_event_affiliates']),
						'hash_tracking' =>  $this->aiwp->config->options ['ga_hash_tracking'],
						'root_domain' => sanitize_text_field( $root_domain ),
						'event_timeout' => apply_filters( 'aiwp_analyticsevents_timeout', 100 ),
						'event_precision' => $this->aiwp->config->options['ga_event_precision'],
						'event_formsubmit' =>  $this->aiwp->config->options ['ga_formsubmit_tracking'],
						'ga_pagescrolldepth_tracking' => $this->aiwp->config->options['ga_pagescrolldepth_tracking'],
						'global_site_tag' => 'ga4tracking' == $this->aiwp->config->options ['tracking_type'] || 'globalsitetag' == $this->aiwp->config->options ['tracking_type'] || 'dualtracking' == $this->aiwp->config->options ['tracking_type'],
					),
				)
				);
				/* @formatter:on */
			}
		}

		/**
		 * Outputs the Google Optimize tracking code
		 */
		public function optimize_output() {
			AIWP_Tools::load_view( 'front/views/optimize-code.php', array( 'containerid' => $this->aiwp->config->options['optimize_containerid'] ) );
		}

		/**
		 * Sanitizes the output of commands in the tracking code
		 * @param string $value
		 * @return string
		 */
		protected function filter( $value, $is_dim = false ) {
			if ( 'true' == $value || 'false' == $value || ( is_numeric( $value ) && ! $is_dim ) ) {
				return $value;
			}
			if ( substr( $value, 0, 1 ) == '[' && substr( $value, - 1 ) == ']' || substr( $value, 0, 1 ) == '{' && substr( $value, - 1 ) == '}' ) {
				return $value;
			}
			return "'" . $value . "'";
		}

		/**
		 * Retrieves the commands
		 */
		public function get() {
			return $this->commands;
		}

		/**
		 * Stores the commands
		 * @param array $commands
		 */
		public function set( $commands ) {
			$this->commands = $commands;
		}

		/**
		 * Formats the command before being added to the commands
		 * @param string $command
		 * @param array $fields
		 * @param string $fieldsobject
		 * @return array
		 */
		public function prepare( $command, $fields, $fieldsobject = null ) {
			return array( 'command' => $command, 'fields' => $fields, 'fieldsobject' => $fieldsobject );
		}

		/**
		 * Adds a formatted command to commands
		 * @param string $command
		 * @param array $fields
		 * @param string $fieldsobject
		 */
		protected function add( $command, $fields, $fieldsobject = null ) {
			$this->commands[] = $this->prepare( $command, $fields, $fieldsobject );
		}
	}
}
if ( ! class_exists( 'AIWP_Tracking_Analytics' ) ) {

	class AIWP_Tracking_Analytics extends AIWP_Tracking_Analytics_Common {

		public function __construct() {
			parent::__construct();
			if ( $this->aiwp->config->options['trackingcode_infooter'] ) {
				add_action( 'wp_footer', array( $this, 'output' ), 99 );
			} else {
				add_action( 'wp_head', array( $this, 'output' ), 99 );
			}
		}

		/**
		 * Builds the commands based on user's options
		 */
		private function build_commands() {
			$fields = array();
			$fieldsobject = array();
			$fields['trackingId'] = sanitize_text_field( $this->gaid );
			if ( 1 != $this->aiwp->config->options['ga_speed_samplerate'] ) {
				$fieldsobject['siteSpeedSampleRate'] = (int) $this->aiwp->config->options['ga_speed_samplerate'];
			}
			if ( 100 != $this->aiwp->config->options['ga_user_samplerate'] ) {
				$fieldsobject['sampleRate'] = (int) $this->aiwp->config->options['ga_user_samplerate'];
			}
			if ( $this->aiwp->config->options['ga_crossdomain_tracking'] && '' != $this->aiwp->config->options['ga_crossdomain_list'] ) {
				$fieldsobject['allowLinker'] = 'true';
			}
			if ( ! empty( $this->aiwp->config->options['ga_cookiedomain'] ) ) {
				$fieldsobject['cookieDomain'] = sanitize_text_field( $this->aiwp->config->options['ga_cookiedomain'] );
			} else {
				$fields['cookieDomain'] = 'auto';
			}
			if ( ! empty( $this->aiwp->config->options['ga_cookiename'] ) ) {
				$fieldsobject['cookieName'] = sanitize_text_field( $this->aiwp->config->options['ga_cookiename'] );
			}
			if ( ! empty( $this->aiwp->config->options['ga_cookieexpires'] ) ) {
				$fieldsobject['cookieExpires'] = (int) $this->aiwp->config->options['ga_cookieexpires'];
			}
			if ( $this->aiwp->config->options['amp_tracking_clientidapi'] ) {
				$fieldsobject['useAmpClientId'] = 'true';
			}
			$this->add( 'create', $fields, $fieldsobject );
			if ( $this->aiwp->config->options['ga_crossdomain_tracking'] && '' != $this->aiwp->config->options['ga_crossdomain_list'] ) {
				$fields = array();
				$fields['plugin'] = 'linker';
				$this->add( 'require', $fields );
				$fields = array();
				$domains = '';
				$domains = explode( ',', sanitize_text_field( $this->aiwp->config->options['ga_crossdomain_list'] ) );
				$domains = array_map( 'trim', $domains );
				$domains = strip_tags( implode( "','", $domains ) );
				$domains = "['" . $domains . "']";
				$fields['domains'] = $domains;
				$this->add( 'linker:autoLink', $fields );
			}
			if ( $this->aiwp->config->options['ga_remarketing'] ) {
				$fields = array();
				$fields['plugin'] = 'displayfeatures';
				$this->add( 'require', $fields );
			}
			if ( $this->aiwp->config->options['ga_enhanced_links'] ) {
				$fields = array();
				$fields['plugin'] = 'linkid';
				$this->add( 'require', $fields );
			}
			if ( $this->aiwp->config->options['ga_force_ssl'] ) {
				$fields = array();
				$fields['option'] = 'forceSSL';
				$fields['value'] = 'true';
				$this->add( 'set', $fields );
			}
			$custom_dimensions = $this->build_custom_dimensions();
			if ( ! empty( $custom_dimensions ) ) {
				foreach ( $custom_dimensions as $index => $value ) {
					$fields = array();
					$fields['aiwp_dimension'] = 'dimension' . $index;
					$fields['aiwp_dim_value'] = esc_js( $value );
					$this->add( 'set', $fields );
				}
			}
			if ( $this->aiwp->config->options['ga_anonymize_ip'] ) {
				$fields = array();
				$fields['option'] = 'anonymizeIp';
				$fields['value'] = 'true';
				$this->add( 'set', $fields );
			}
			if ( 'enhanced' == $this->aiwp->config->options['ecommerce_mode'] ) {
				$fields = array();
				$fields['plugin'] = 'ec';
				$this->add( 'require', $fields );
			} else if ( 'standard' == $this->aiwp->config->options['ecommerce_mode'] ) {
				$fields = array();
				$fields['plugin'] = 'ecommerce';
				$this->add( 'require', $fields );
			}
			if ( $this->aiwp->config->options['optimize_tracking'] && $this->aiwp->config->options['optimize_containerid'] ) {
				$fields = array();
				$fields['plugin'] = sanitize_text_field( $this->aiwp->config->options['optimize_containerid'] );
				$this->add( 'require', $fields );
			}
			$fields = array();
			$fields['hitType'] = 'pageview';
			$this->add( 'send', $fields );
			do_action( 'aiwp_analytics_commands', $this );
		}

		/**
		 * Outputs the Google Analytics tracking code
		 */
		public function output() {
			if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
				return;
			}
			$this->commands = array();
			$this->build_commands();
			$trackingcode = '';
			foreach ( $this->commands as $set ) {
				$command = $set['command'];
				$fields = '';
				foreach ( $set['fields'] as $fieldkey => $fieldvalue ) {
					if ( false === strpos( $fieldkey, 'aiwp_dim_value' ) ) {
						$fieldvalue = $this->filter( $fieldvalue );
					} else {
						$fieldvalue = $this->filter( $fieldvalue, true );
					}
					$fields .= ", " . $fieldvalue;
				}
				if ( $set['fieldsobject'] ) {
					$fieldsobject = ", {";
					foreach ( $set['fieldsobject'] as $fieldkey => $fieldvalue ) {
						$fieldvalue = $this->filter( $fieldvalue );
						$fieldkey = $this->filter( $fieldkey );
						$fieldsobject .= $fieldkey . ": " . $fieldvalue . ", ";
					}
					$fieldsobject = rtrim( $fieldsobject, ", " );
					$fieldsobject .= "}";
					$trackingcode .= "  ga('" . $command . "'" . $fields . $fieldsobject . ");\n";
				} else {
					$trackingcode .= "  ga('" . $command . "'" . $fields . ");\n";
				}
			}
			$tracking_script_path = apply_filters( 'aiwp_analytics_script_path', 'https://www.google-analytics.com/analytics.js' );
			if ( $this->aiwp->config->options['ga_optout'] || $this->aiwp->config->options['ga_dnt_optout'] ) {
				AIWP_Tools::load_view( 'front/views/analytics-optout-code.php', array( 'gaid' => $this->gaid, 'gaDntOptout' => $this->aiwp->config->options['ga_dnt_optout'], 'gaOptout' => $this->aiwp->config->options['ga_optout'] ) );
			}
			$globalsitetag = 'ga4tracking' == $this->aiwp->config->options['tracking_type'] || 'globalsitetag' == $this->aiwp->config->options['tracking_type'] || 'dualtracking' == $this->aiwp->config->options['tracking_type'];
			AIWP_Tools::load_view( 'front/views/analytics-code.php', array( 'trackingcode' => $trackingcode, 'tracking_script_path' => $tracking_script_path, 'gaid' => $this->gaid ), $globalsitetag );
		}
	}
}
if ( ! class_exists( 'AIWP_Tracking_GlobalSiteTag' ) ) {

	class AIWP_Tracking_GlobalSiteTag extends AIWP_Tracking_Analytics_Common {

		public function __construct() {
			parent::__construct();
			if ( $this->aiwp->config->options['trackingcode_infooter'] ) {
				add_action( 'wp_footer', array( $this, 'output' ), 99 );
			} else {
				add_action( 'wp_head', array( $this, 'output' ), 99 );
			}
		}

		/**
		 * Builds the commands based on user's options
		 */
		private function build_commands() {
			$fields = array();
			$fieldsobject = array();
			$fields['trackingId'] = sanitize_text_field( $this->gaid );
			$custom_dimensions = $this->build_custom_dimensions();

			if ( ! empty( $this->aiwp->config->options['ga_cookiedomain'] ) ) {
				$fieldsobject['cookie_domain'] = sanitize_text_field( $this->aiwp->config->options['ga_cookiedomain'] );
			}
			if ( ! empty( $this->aiwp->config->options['ga_cookiename'] ) ) {
				$fieldsobject['cookie_name'] = sanitize_text_field( $this->aiwp->config->options['ga_cookiename'] );
			}
			if ( ! empty( $this->aiwp->config->options['ga_cookieexpires'] ) ) {
				$fieldsobject['cookie_expires'] = (int) $this->aiwp->config->options['ga_cookieexpires'];
			}
			if ( $this->aiwp->config->options['ga_crossdomain_tracking'] && '' != $this->aiwp->config->options['ga_crossdomain_list'] ) {
				$domains = '';
				$domains = explode( ',', sanitize_text_field( $this->aiwp->config->options['ga_crossdomain_list'] ) );
				$domains = array_map( 'trim', $domains );
				$domains = strip_tags( implode( "','", $domains ) );
				$domains = "['" . $domains . "']";
				$fieldsobject['linker'] = "{ 'domains' : " . $domains . " }";
			}
			if ( ! $this->aiwp->config->options['ga_remarketing'] ) {
				$fieldsobject['allow_display_features'] = 'false';
			}
			if ( $this->aiwp->config->options['ga_enhanced_links'] ) {
				$fieldsobject['link_attribution'] = 'true';
			}
			if ( $this->aiwp->config->options['ga_anonymize_ip'] ) {
				$fieldsobject['anonymize_ip'] = 'true';
			}
			if ( $this->aiwp->config->options['optimize_tracking'] && $this->aiwp->config->options['optimize_containerid'] ) {
				$fieldsobject['optimize_id'] = sanitize_text_field( $this->aiwp->config->options['optimize_containerid'] );
			}
			if ( 100 != $this->aiwp->config->options['ga_user_samplerate'] ) {
				$fieldsobject['sample_rate'] = (int) $this->aiwp->config->options['ga_user_samplerate'];
			}
			if ( ! empty( $custom_dimensions ) ) {
				$fieldsobject['custom_map'] = "{\n\t\t";
				foreach ( $custom_dimensions as $index => $value ) {
					$fieldsobject['custom_map'] .= "'dimension" . $index . "': '" . "aiwp_dim_" . $index . "', \n\t\t";
				}
				$fieldsobject['custom_map'] = rtrim( $fieldsobject['custom_map'], ", \n\t\t" );
				$fieldsobject['custom_map'] .= "\n\t}";
			}

			$this->add( 'config', $fields, $fieldsobject );

			if ( 'dualtracking' == $this->aiwp->config->options['tracking_type'] && $this->mid ) {
				$this->add( 'config', array($this->mid) );
			}

			if ( ! empty( $custom_dimensions ) ) {
				$fields = array();
				$fieldsobject = array();
				$fields['event_name'] = 'aiwp_dimensions';
				foreach ( $custom_dimensions as $index => $value ) {
					$fieldsobject['aiwp_dim_' . $index] = esc_js( $value );
				}
				$this->add( 'event', $fields, $fieldsobject );
			}

			do_action( 'aiwp_gtag_commands', $this );
		}

		/**
		 * Outputs the Google Analytics tracking code
		 */
		public function output() {
			if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
				return;
			}
			$this->commands = array();
			$this->build_commands();
			$trackingcode = '';
			foreach ( $this->commands as $set ) {
				$command = $set['command'];
				$fields = '';
				foreach ( $set['fields'] as $fieldkey => $fieldvalue ) {
					$fieldvalue = $this->filter( $fieldvalue );
					$fields .= ", " . $fieldvalue;
				}
				if ( $set['fieldsobject'] ) {
					$fieldsobject = ", {\n\t";
					foreach ( $set['fieldsobject'] as $fieldkey => $fieldvalue ) {
						if ( false === strpos( $fieldkey, 'aiwp_' ) ) {
							$fieldvalue = $this->filter( $fieldvalue );
						} else {
							$fieldvalue = $this->filter( $fieldvalue, true );
						}
						$fieldkey = $this->filter( $fieldkey );
						$fieldsobject .= $fieldkey . ": " . $fieldvalue . ", \n\t";
					}
					$fieldsobject = rtrim( $fieldsobject, ", \n\t" );
					$fieldsobject .= "\n  }";
					$trackingcode .= "  gtag('" . $command . "'" . $fields . $fieldsobject . ");\n";
				} else {
					$trackingcode .= "  gtag('" . $command . "'" . $fields . ");\n";
				}
			}
			$tracking_script_path = apply_filters( 'aiwp_gtag_script_path', 'https://www.googletagmanager.com/gtag/js' );
			if ( $this->aiwp->config->options['ga_optout'] || $this->aiwp->config->options['ga_dnt_optout'] ) {
				AIWP_Tools::load_view( 'front/views/analytics-optout-code.php', array( 'gaid' => $this->gaid, 'gaDntOptout' => $this->aiwp->config->options['ga_dnt_optout'], 'gaOptout' => $this->aiwp->config->options['ga_optout'] ) );
			}
			$globalsitetag = 'ga4tracking' == $this->aiwp->config->options['tracking_type'] || 'globalsitetag' == $this->aiwp->config->options['tracking_type'] || 'dualtracking' == $this->aiwp->config->options['tracking_type'];
			AIWP_Tools::load_view( 'front/views/analytics-code.php', array( 'trackingcode' => $trackingcode, 'tracking_script_path' => $tracking_script_path, 'gaid' => $this->gaid ), $globalsitetag );
		}
	}
}
if ( ! class_exists( 'AIWP_Tracking_Analytics_AMP' ) ) {

	class AIWP_Tracking_Analytics_AMP extends AIWP_Tracking_Analytics_Base {

		private $config;

		public function __construct() {
			parent::__construct();
			add_filter( 'amp_post_template_data', array( $this, 'load_scripts' ) );
			add_action( 'amp_post_template_footer', array( $this, 'output' ) );
			add_filter( 'the_content', array( $this, 'add_data_attributes' ), 999, 1 );
			if ( $this->aiwp->config->options['amp_tracking_clientidapi'] ) {
				add_action( 'amp_post_template_head', array( $this, 'add_amp_client_id' ) );
			}
		}

		private function get_link_event_data( $link ) {
			if ( empty( $link ) ) {
				return false;
			}
			if ( $this->aiwp->config->options['ga_event_tracking'] ) {
				// on changes adjust the substr() length parameter
				if ( substr( $link, 0, 7 ) === "mailto:" ) {
					return array( 'email', 'send', $link );
				}
				// on changes adjust the substr() length parameter
				if ( substr( $link, 0, 4 ) === "tel:" ) {
					return array( 'telephone', 'call', $link );
				}
				// Add download data-vars
				if ( $this->aiwp->config->options['ga_event_downloads'] && preg_match( '/.*\.(' . $this->aiwp->config->options['ga_event_downloads'] . ')(\?.*)?$/i', $link, $matches ) ) {
					return array( 'download', 'click', $link );
				}
			}
			if ( $this->aiwp->config->options['ga_hash_tracking'] ) {
				// Add hashmark data-vars
				$root_domain = AIWP_Tools::get_root_domain();
				if ( $root_domain && ( strpos( $link, $root_domain ) > - 1 || strpos( $link, '://' ) === false ) && strpos( $link, '#' ) > - 1 ) {
					return array( 'hashmark', 'click', $link );
				}
			}
			if ( $this->aiwp->config->options['ga_aff_tracking'] ) {
				// Add affiliate data-vars
				if ( strpos( $link, $this->aiwp->config->options['ga_event_affiliates'] ) > - 1 ) {
					return array( 'affiliates', 'click', $link );
				}
			}
			if ( $this->aiwp->config->options['ga_event_tracking'] ) {
				// Add outbound data-vars
				$root_domain = AIWP_Tools::get_root_domain();
				if ( $root_domain && strpos( $link, $root_domain ) === false && strpos( $link, '://' ) > - 1 ) {
					return array( 'outbound', 'click', $link );
				}
			}
			return false;
		}

		public function add_data_attributes( $content ) {
			if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() && $this->is_event_tracking( false ) ) {
				$dom = AIWP_Tools::get_dom_from_content( $content );
				if ( $dom ) {
					$links = $dom->getElementsByTagName( 'a' );
					foreach ( $links as $item ) {
						$data_attributes = $this->get_link_event_data( $item->getAttribute( 'href' ) );
						if ( $data_attributes ) {
							if ( ! $item->hasAttribute( 'data-vars-ga-category' ) ) {
								$item->setAttribute( 'data-vars-ga-category', $data_attributes[0] );
							}
							if ( ! $item->hasAttribute( 'data-vars-ga-action' ) ) {
								$item->setAttribute( 'data-vars-ga-action', $data_attributes[1] );
							}
							if ( ! $item->hasAttribute( 'data-vars-ga-label' ) ) {
								$item->setAttribute( 'data-vars-ga-label', $data_attributes[2] );
							}
						}
					}
					if ( $this->aiwp->config->options['ga_formsubmit_tracking'] ) {
						$form_submits = $dom->getElementsByTagName( 'input' );
						foreach ( $form_submits as $item ) {
							if ( $item->getAttribute( 'type' ) == 'submit' ) {
								if ( ! $item->hasAttribute( 'data-vars-ga-category' ) ) {
									$item->setAttribute( 'data-vars-ga-category', 'form' );
								}
								if ( ! $item->hasAttribute( 'data-vars-ga-action' ) ) {
									$item->setAttribute( 'data-vars-ga-action', 'submit' );
								}
								if ( ! $item->hasAttribute( 'data-vars-ga-label' ) ) {
									if ( $item->getAttribute( 'value' ) ) {
										$label = $item->getAttribute( 'value' );
									}
									if ( $item->getAttribute( 'name' ) ) {
										$label = $item->getAttribute( 'name' );
									}
									$item->setAttribute( 'data-vars-ga-label', $label );
								}
							}
						}
					}
					return AIWP_Tools::get_content_from_dom( $dom );
				}
			}
			return $content;
		}

		/**
		 * Inserts the Analytics AMP script in the head section
		 */
		public function load_scripts( $data ) {
			if ( ! isset( $data['amp_component_scripts'] ) ) {
				$data['amp_component_scripts'] = array();
			}
			$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
			return $data;
		}

		/**
		 * Retrieves the AMP config array
		 */
		public function get() {
			return $this->config;
		}

		/**
		 * Stores the AMP config array
		 * @param array $config
		 */
		public function set( $config ) {
			$this->config = $config;
		}

		private function build_json() {
			$this->config = array();
			// Set the Tracking ID
			/* @formatter:off */
			$this->config['vars'] = array(
				'account' => sanitize_text_field ( $this->gaid ),
				'documentLocation' => '${canonicalUrl}',
			);
			/* @formatter:on */
			// Set Custom Dimensions as extraUrlParams
			$custom_dimensions = $this->build_custom_dimensions();
			if ( ! empty( $custom_dimensions ) ) {
				foreach ( $custom_dimensions as $index => $value ) {
					$dimension = 'cd' . $index;
					$this->config['extraUrlParams'][$dimension] = esc_js( $value );
				}
			}
			// Set Triggers
			/* @formatter:off */
			$this->config['triggers']['aiwpTrackPageview'] = array(
				'on' => 'visible',
				'request' => 'pageview',
			);
			/* @formatter:on */
			// Set Sampling Rate only if lower than 100%
			if ( 100 != $this->aiwp->config->options['ga_user_samplerate'] ) {
				/* @formatter:off */
				$this->config['triggers']['aiwpTrackPageview']['sampleSpec'] = array(
					'sampleOn' => '${clientId}',
					'threshold' => (int) $this->aiwp->config->options['ga_user_samplerate'],
				);
				/* @formatter:on */
			}
			// Set Scroll events
			if ( $this->aiwp->config->options['ga_pagescrolldepth_tracking'] ) {
				/* @formatter:off */
				$this->config['triggers']['aiwpScrollPings'] = array (
					'on' => 'scroll',
					'scrollSpec' => array(
						'verticalBoundaries' => '&#91;25, 50, 75, 100&#93;',
					),
					'request' => 'event',
					'vars' => array(
						'eventCategory' => 'Scroll Depth',
						'eventAction' => 'Percentage',
						'eventLabel' => '${verticalScrollBoundary}%',
					),
				);
				/* @formatter:on */
				$this->config['triggers']['aiwpScrollPings']['extraUrlParams'] = array( 'ni' => true );
			}
			if ( $this->is_event_tracking( false ) ) {
				// Set downloads, outbound links, affiliate links, hashmarks, e-mails, telephones, form submits events
				/* @formatter:off */
				$this->config['triggers']['aiwpEventTracking'] = array (
					'on' => 'click',
					'selector' => '[data-vars-ga-category][data-vars-ga-action][data-vars-ga-label]',
					'request' => 'event',
					'vars' => array(
						'eventCategory' => '${gaCategory}',
						'eventAction' => '${gaAction}',
						'eventLabel' => '${gaLabel}',
					),
				);
				/* @formatter:on */
				if ( $this->aiwp->config->options['ga_event_bouncerate'] ) {
					$this->config['triggers']['aiwpEventTracking']['extraUrlParams'] = array( 'ni' => (bool) $this->aiwp->config->options['ga_event_bouncerate'] );
				}
			}
			do_action( 'aiwp_analytics_amp_config', $this );
		}

		public function add_amp_client_id() {
			AIWP_Tools::load_view( 'front/views/analytics-amp-clientidapi.php' );
		}

		/**
		 * Outputs the Google Analytics tracking code for AMP
		 */
		public function output() {
			$this->build_json();
			if ( version_compare( phpversion(), '5.4.0', '<' ) ) {
				$json = json_encode( $this->config );
			} else {
				$json = json_encode( $this->config, JSON_PRETTY_PRINT );
			}
			$json = str_replace( array( '"&#91;', '&#93;"' ), array( '[', ']' ), $json ); // make verticalBoundaries a JavaScript array
			$data = array( 'json' => $json );
			$globalsitetag = 'ga4tracking' == $this->aiwp->config->options['tracking_type'] || 'globalsitetag' == $this->aiwp->config->options['tracking_type'] || 'dualtracking' == $this->aiwp->config->options['tracking_type'];
			AIWP_Tools::load_view( 'front/views/analytics-amp-code.php', $data, $globalsitetag );
		}
	}
}



//***************************************************************************************************************************


if ( ! class_exists( 'AIWP_Tracking_GlobalSiteTag_AMP' ) ) {

class AIWP_Tracking_GlobalSiteTag_AMP extends AIWP_Tracking_Analytics_Base {

	private $config;

	public function __construct() {
		parent::__construct();
		add_filter( 'amp_post_template_data', array( $this, 'load_scripts' ) );
		add_action( 'amp_post_template_footer', array( $this, 'output' ) );
		add_filter( 'the_content', array( $this, 'add_data_attributes' ), 999, 1 );
		if ( $this->aiwp->config->options['amp_tracking_clientidapi'] ) {
			add_action( 'amp_post_template_head', array( $this, 'add_amp_client_id' ) );
		}
	}

	private function get_link_event_data( $link ) {
		if ( empty( $link ) ) {
			return false;
		}
		if ( $this->aiwp->config->options['ga_event_tracking'] ) {
			// on changes adjust the substr() length parameter
			if ( substr( $link, 0, 7 ) === "mailto:" ) {
				return array( 'email', 'send', $link );
			}
			// on changes adjust the substr() length parameter
			if ( substr( $link, 0, 4 ) === "tel:" ) {
				return array( 'telephone', 'call', $link );
			}
			// Add download data-vars
			if ( $this->aiwp->config->options['ga_event_downloads'] && preg_match( '/.*\.(' . $this->aiwp->config->options['ga_event_downloads'] . ')(\?.*)?$/i', $link, $matches ) ) {
				return array( 'download', 'click', $link );
			}
		}
		if ( $this->aiwp->config->options['ga_hash_tracking'] ) {
			// Add hashmark data-vars
			$root_domain = AIWP_Tools::get_root_domain();
			if ( $root_domain && ( strpos( $link, $root_domain ) > - 1 || strpos( $link, '://' ) === false ) && strpos( $link, '#' ) > - 1 ) {
				return array( 'hashmark', 'click', $link );
			}
		}
		if ( $this->aiwp->config->options['ga_aff_tracking'] ) {
			// Add affiliate data-vars
			if ( strpos( $link, $this->aiwp->config->options['ga_event_affiliates'] ) > - 1 ) {
				return array( 'affiliates', 'click', $link );
			}
		}
		if ( $this->aiwp->config->options['ga_event_tracking'] ) {
			// Add outbound data-vars
			$root_domain = AIWP_Tools::get_root_domain();
			if ( $root_domain && strpos( $link, $root_domain ) === false && strpos( $link, '://' ) > - 1 ) {
				return array( 'outbound', 'click', $link );
			}
		}
		return false;
	}

	public function add_data_attributes( $content ) {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() && $this->is_event_tracking( false ) ) {
			$dom = AIWP_Tools::get_dom_from_content( $content );
			if ( $dom ) {
				$links = $dom->getElementsByTagName( 'a' );
				foreach ( $links as $item ) {
					$data_attributes = $this->get_link_event_data( $item->getAttribute( 'href' ) );
					if ( $data_attributes ) {
						if ( ! $item->hasAttribute( 'data-vars-ga-category' ) ) {
							$item->setAttribute( 'data-vars-ga-category', $data_attributes[0] );
						}
						if ( ! $item->hasAttribute( 'data-vars-ga-action' ) ) {
							$item->setAttribute( 'data-vars-ga-action', $data_attributes[1] );
						}
						if ( ! $item->hasAttribute( 'data-vars-ga-label' ) ) {
							$item->setAttribute( 'data-vars-ga-label', $data_attributes[2] );
						}
					}
				}
				if ( $this->aiwp->config->options['ga_formsubmit_tracking'] ) {
					$form_submits = $dom->getElementsByTagName( 'input' );
					foreach ( $form_submits as $item ) {
						if ( $item->getAttribute( 'type' ) == 'submit' ) {
							if ( ! $item->hasAttribute( 'data-vars-ga-category' ) ) {
								$item->setAttribute( 'data-vars-ga-category', 'form' );
							}
							if ( ! $item->hasAttribute( 'data-vars-ga-action' ) ) {
								$item->setAttribute( 'data-vars-ga-action', 'submit' );
							}
							if ( ! $item->hasAttribute( 'data-vars-ga-label' ) ) {
								if ( $item->getAttribute( 'value' ) ) {
									$label = $item->getAttribute( 'value' );
								}
								if ( $item->getAttribute( 'name' ) ) {
									$label = $item->getAttribute( 'name' );
								}
								$item->setAttribute( 'data-vars-ga-label', $label );
							}
						}
					}
				}
				return AIWP_Tools::get_content_from_dom( $dom );
			}
		}
		return $content;
	}

	/**
	 * Inserts the Analytics AMP script in the head section
	 */
	public function load_scripts( $data ) {
		if ( ! isset( $data['amp_component_scripts'] ) ) {
			$data['amp_component_scripts'] = array();
		}
		$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
		return $data;
	}

	/**
	 * Retrieves the AMP config array
	 */
	public function get() {
		return $this->config;
	}

	/**
	 * Stores the AMP config array
	 * @param array $config
	 */
	public function set( $config ) {
		$this->config = $config;
	}

	private function build_json() {
		$this->config = array();
		// Set the Tracking ID
		/* @formatter:off */
		$this->config['vars'] = array(
			'gtag_id' => sanitize_text_field ( $this->gaid ),
			'documentLocation' => '${canonicalUrl}',
		);
		$this->config['vars']['config'] = array(
			sanitize_text_field ( $this->gaid ) => array( 'groups' => 'default' ),
		);
		/* @formatter:on */
		// Set Custom Dimensions Map
		$custom_dimensions = $this->build_custom_dimensions();
		if ( ! empty( $custom_dimensions ) ) {
			$this->config['triggers']['dimension_event']['on'] = 'visible';
			$this->config['triggers']['dimension_event']['vars']['event_name'] = 'Custom Dimensions';
			foreach ( $custom_dimensions as $index => $value ) {
				$dimension = 'dimension' . $index;
				$this->config['vars']['config'][sanitize_text_field ( $this->gaid )]['custom_map'][$dimension] = 'aiwp_dim_'.$index;
				$this->config['triggers']['dimension_event']['vars']['aiwp_dim_'.$index] = esc_js( $value );
			}
		}
		/* @formatter:on */
		// Set Sampling Rate only if lower than 100%
		if ( 100 != $this->aiwp->config->options['ga_user_samplerate'] ) {
			/* @formatter:off */
			$this->config['triggers']['aiwpTrackPageview']['sampleSpec'] = array(
				'sampleOn' => '${clientId}',
				'threshold' => (int) $this->aiwp->config->options['ga_user_samplerate'],
			);
			/* @formatter:on */
		}
		// Set Scroll events
		if ( $this->aiwp->config->options['ga_pagescrolldepth_tracking'] ) {
			/* @formatter:off */
			$this->config['triggers']['aiwpScrollPings'] = array (
				'on' => 'scroll',
				'scrollSpec' => array(
					'verticalBoundaries' => '&#91;25, 50, 75, 100&#93;',
				),
				'request' => 'event',
				'vars' => array(
					'event_name' => 'Scroll',
					'event_category' => 'Scroll Depth',
					'event_action' => 'Percentage',
					'event_label' => '${verticalScrollBoundary}%',
				),
			);
			/* @formatter:on */
			$this->config['triggers']['aiwpScrollPings']['extraUrlParams'] = array( 'ni' => true );
		}
		if ( $this->is_event_tracking( false ) ) {
			// Set downloads, outbound links, affiliate links, hashmarks, e-mails, telephones, form submits events
			/* @formatter:off */
			$this->config['triggers']['aiwpEventTracking'] = array (
				'on' => 'click',
				'selector' => '[data-vars-ga-category][data-vars-ga-action][data-vars-ga-label]',
				'request' => 'event',
				'vars' => array(
					'event_name' => 'HTML Attributes',
					'event_category' => '${gaCategory}',
					'event_action' => '${gaAction}',
					'event_label' => '${gaLabel}',
				),
			);
			/* @formatter:on */
			if ( $this->aiwp->config->options['ga_event_bouncerate'] ) {
				$this->config['triggers']['aiwpEventTracking']['extraUrlParams'] = array( 'ni' => (bool) $this->aiwp->config->options['ga_event_bouncerate'] );
			}
		}
		do_action( 'aiwp_analytics_amp_config', $this );
	}

	public function add_amp_client_id() {
		AIWP_Tools::load_view( 'front/views/analytics-amp-clientidapi.php' );
	}

	/**
	 * Outputs the Google Analytics tracking code for AMP
	 */
	public function output() {
		$this->build_json();
		if ( version_compare( phpversion(), '5.4.0', '<' ) ) {
			$json = json_encode( $this->config );
		} else {
			$json = json_encode( $this->config, JSON_PRETTY_PRINT );
		}
		$json = str_replace( array( '"&#91;', '&#93;"' ), array( '[', ']' ), $json ); // make verticalBoundaries a JavaScript array
		$data = array( 'json' => $json );
		$globalsitetag = 'ga4tracking' == $this->aiwp->config->options['tracking_type'] || 'globalsitetag' == $this->aiwp->config->options['tracking_type'] || 'dualtracking' == $this->aiwp->config->options['tracking_type'];
		AIWP_Tools::load_view( 'front/views/analytics-amp-code.php', $data, $globalsitetag );
	}
}
}

