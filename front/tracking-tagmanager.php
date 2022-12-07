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
if ( ! class_exists( 'AIWP_Tracking_TagManager_Base' ) ) {

	class AIWP_Tracking_TagManager_Base {

		protected $aiwp;

		protected $datalayer;

		protected $gaid;

		public function __construct() {
			$this->aiwp = AIWP();
			$profile = AIWP_Tools::get_selected_profile( $this->aiwp->config->options['ga_profiles_list'], $this->aiwp->config->options['tableid_jail'] );
			$this->gaid = sanitize_text_field( $profile[2] );
		}

		/**
		 * Retrieves the datalayer variables
		 */
		public function get() {
			return $this->datalayer;
		}

		/**
		 * Stores the datalayer variables
		 * @param array $datalayer
		 */
		public function set( $datalayer ) {
			$this->datalayer = $datalayer;
		}

		/**
		 * Adds a variable to the datalayer
		 * @param string $name
		 * @param string $value
		 */
		private function add_var( $name, $value ) {
			$this->datalayer[$name] = $value;
		}

		/**
		 * Builds the datalayer based on user's options
		 */
		public function build_custom_dimensions() {
			global $post;
			if ( $this->aiwp->config->options['tm_author_var'] && ( is_single() || is_page() ) ) {
				global $post;
				$author_id = $post->post_author;
				$author_name = get_the_author_meta( 'display_name', $author_id );
				$this->add_var( 'aiwpAuthor', esc_attr( $author_name ) );
			}
			if ( $this->aiwp->config->options['tm_pubyear_var'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y', $post->ID );
				$this->add_var( 'aiwpPublicationYear', (int) $date );
			}
			if ( $this->aiwp->config->options['tm_pubyearmonth_var'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y-m', $post->ID );
				$this->add_var( 'aiwpPublicationYearMonth', esc_attr( $date ) );
			}
			if ( $this->aiwp->config->options['tm_category_var'] && is_category() ) {
				$this->add_var( 'aiwpCategory', esc_attr( single_tag_title( '', false ) ) );
			}
			if ( $this->aiwp->config->options['tm_category_var'] && is_single() ) {
				global $post;
				$categories = get_the_category( $post->ID );
				foreach ( $categories as $category ) {
					$this->add_var( 'aiwpCategory', esc_attr( $category->name ) );
					break;
				}
			}
			if ( $this->aiwp->config->options['tm_tag_var'] && is_single() ) {
				global $post;
				$post_tags_list = '';
				$post_tags_array = get_the_tags( $post->ID );
				if ( $post_tags_array ) {
					foreach ( $post_tags_array as $tag ) {
						$post_tags_list .= esc_attr( $tag->name ) . ', ';
					}
				}
				$post_tags_list = rtrim( $post_tags_list, ', ' );
				if ( $post_tags_list ) {
					$this->add_var( 'aiwpTag', esc_attr( $post_tags_list ) );
				}
			}
			if ( $this->aiwp->config->options['tm_user_var'] ) {
				$usertype = is_user_logged_in() ? 'registered' : 'guest';
				$this->add_var( 'aiwpUser', $usertype );
			}
			do_action( 'aiwp_tagmanager_datalayer', $this );
		}
	}
}

if ( ! class_exists( 'AIWP_Tracking_TagManager' ) ) {

	class AIWP_Tracking_TagManager extends AIWP_Tracking_TagManager_Base{

		public function __construct() {
			parent::__construct();
			$this->load_scripts();
		}

		private function load_scripts(){
			if ( $this->aiwp->config->options['trackingcode_infooter'] ) {
				add_action( 'wp_footer', array( $this, 'output' ), 99 );
			} else {
				add_action( 'wp_head', array( $this, 'output' ), 99 );
			}
		}

		/**
		 * Outputs the Google Tag Manager tracking code
		 */
		public function output() {
			if ( AIWP_Tools::is_amp() ) {
				return;
			}
			$this->build_custom_dimensions();
			if ( is_array( $this->datalayer ) ) {
				$vars = "{";
				foreach ( $this->datalayer as $var => $value ) {
					$vars .= "'" . $var . "': '" . $value . "', ";
				}
				$vars = rtrim( $vars, ", " );
				$vars .= "}";
			} else {
				$vars = "{}";
			}
			if ( ( $this->aiwp->config->options['tm_optout'] || $this->aiwp->config->options['tm_dnt_optout'] ) && ! empty( $this->gaid ) ) {
				AIWP_Tools::load_view( 'front/views/analytics-optout-code.php', array( 'gaid' => $this->gaid, 'gaDntOptout' => $this->aiwp->config->options['tm_dnt_optout'], 'gaOptout' => $this->aiwp->config->options['tm_optout'] ) );
			}
			AIWP_Tools::load_view( 'front/views/tagmanager-code.php', array( 'containerid' => $this->aiwp->config->options['web_containerid'], 'vars' => $vars ) );
		}
	}
}

if ( ! class_exists( 'AIWP_Tracking_TagManager_AMP' ) ) {

	class AIWP_Tracking_TagManager_AMP extends AIWP_Tracking_TagManager_Base{

		public function __construct() {
			parent::__construct();
			$this->load_scripts();
		}

		private function load_scripts(){
				add_filter( 'amp_post_template_data', array( $this, 'amp_add_analytics_script' ) );
				// For all AMP modes, AMP plugin version >=1.3.
				//add_action( 'amp_print_analytics', array( $this, 'amp_output' ) );
				// For AMP Standard and Transitional, AMP plugin version <1.3.
				add_action( 'wp_footer', array( $this, 'amp_output' ) );
				// For AMP Reader, AMP plugin version <1.3.
				add_action( 'amp_post_template_footer', array( $this, 'amp_output' ) );
		}

		/**
		 * Inserts the Analytics AMP script in the head section
		 */
		public function amp_add_analytics_script( $data ) {
			if ( ! isset( $data['amp_component_scripts'] ) ) {
				$data['amp_component_scripts'] = array();
			}
			$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
			return $data;
		}

		/**
		 * Outputs the Tag Manager code for AMP
		 */
		public function amp_output() {
			$this->build_custom_dimensions();
			$vars = array( 'vars' => $this->datalayer );
			if ( version_compare( phpversion(), '5.4.0', '<' ) ) {
				$json = json_encode( $vars );
			} else {
				$json = json_encode( $vars, JSON_PRETTY_PRINT );
			}
			$amp_containerid = $this->aiwp->config->options['amp_containerid'];
			$json = str_replace( array( '"&#91;', '&#93;"' ), array( '[', ']' ), $json ); // make verticalBoundaries a JavaScript array
			AIWP_Tools::load_view( 'front/views/tagmanager-amp-code.php', array( 'json' => $json, 'containerid' => $amp_containerid ) );
		}
	}
}
