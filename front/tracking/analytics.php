<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2017 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Tracking_Analytics' ) ) {

	class GADWP_Tracking_Analytics {

		private $gadwp;

		private $uaid;

		private $commands;

		public function __construct() {
			$this->gadwp = GADWP();

			$profile = GADWP_Tools::get_selected_profile( $this->gadwp->config->options['ga_dash_profile_list'], $this->gadwp->config->options['ga_dash_tableid_jail'] );

			$this->uaid = esc_html( $profile[2] );

			$this->load_scripts();

			if ($this->gadwp->config->options['trackingcode_infooter']) {
				add_action( 'wp_footer', array( $this, 'output' ), 99 );
			} else {
				add_action( 'wp_head', array( $this, 'output' ), 99 );
			}

			if ( $this->gadwp->config->options['amp_tracking_analytics'] ) {
				add_action( 'amp_post_template_head', array( $this, 'amp_add_analytics_script' ) );
				add_action( 'amp_post_template_footer', array( $this, 'amp_output' ) );
			}
		}

		private function load_scripts() {
			if ( $this->gadwp->config->options['ga_event_tracking'] || $this->gadwp->config->options['ga_aff_tracking'] || $this->gadwp->config->options['ga_hash_tracking'] ) {

				$domaindata = GADWP_Tools::get_root_domain( esc_html( get_option( 'siteurl' ) ) );
				$root_domain = $domaindata['domain'];

				wp_enqueue_script( 'gadwp-tracking-analytics-events', GADWP_URL . 'front/tracking/js/analytics-events.js', array( 'jquery' ), GADWP_CURRENT_VERSION, $this->gadwp->config->options['trackingcode_infooter'] );

				/* @formatter:off */
				wp_localize_script( 'gadwp-tracking-analytics-events', 'gadwpUAEventsData', array(
					'options' => array(
						'event_tracking' => $this->gadwp->config->options['ga_event_tracking'],
						'event_downloads' => esc_js($this->gadwp->config->options['ga_event_downloads']),
						'event_bouncerate' => $this->gadwp->config->options['ga_event_bouncerate'],
						'aff_tracking' => $this->gadwp->config->options['ga_aff_tracking'],
						'event_affiliates' =>  esc_js($this->gadwp->config->options['ga_event_affiliates']),
						'hash_tracking' =>  $this->gadwp->config->options ['ga_hash_tracking'],
						'root_domain' => $root_domain,
						'event_timeout' => apply_filters( 'gadwp_uaevent_timeout', 100 ),
					),
				)
				);
				/* @formatter:on */
			}
		}

		private function add( $command, $fields, $fieldsobject = null ) {
			$this->commands[] = array( 'command' => $command, 'fields' => $fields, 'fieldsobject' => $fieldsobject );
		}

		private function filter( $value ) {
			if ( $value == 'true' || $value == 'false' ) {
				return $value;
			}

			if ( substr( $value, 0, 1 ) == '[' && substr( $value, - 1 ) == ']' ) {
				return $value;
			}

			return "'" . $value . "'";
		}

		private function build_commands() {
			$fields = array();
			$fieldsobject = array();
			$fields['trackingId'] = $this->uaid;
			if ( $this->gadwp->config->options['ga_speed_samplerate'] != 1 ) {
				$fieldsobject['siteSpeedSampleRate'] = (int) $this->gadwp->config->options['ga_speed_samplerate'];
			}
			if ( $this->gadwp->config->options['ga_crossdomain_tracking'] && $this->gadwp->config->options['ga_crossdomain_list'] != '' ) {
				$fieldsobject['allowLinker'] = 'true';
			}
			if ( ! empty( $this->gadwp->config->options['ga_cookiedomain'] ) ) {
				$fieldsobject['cookieDomain'] = $this->gadwp->config->options['ga_cookiedomain'];
			} else {
				$fields['cookieDomain'] = 'auto';
			}
			if ( ! empty( $this->gadwp->config->options['ga_cookiename'] ) ) {
				$fieldsobject['cookieName'] = $this->gadwp->config->options['ga_cookiename'];
			}
			if ( ! empty( $this->gadwp->config->options['ga_cookieexpires'] ) ) {
				$fieldsobject['cookieExpires'] = (int) $this->gadwp->config->options['ga_cookieexpires'];
			}
			$this->add( 'create', $fields, $fieldsobject );

			if ( $this->gadwp->config->options['ga_crossdomain_tracking'] && $this->gadwp->config->options['ga_crossdomain_list'] != '' ) {
				$fields = array();
				$fields['plugin'] = 'linker';
				$this->add( 'require', $fields );

				$fields = array();
				$domains = '';
				$domains = explode( ',', $this->gadwp->config->options['ga_crossdomain_list'] );
				$domains = array_map( 'trim', $domains );
				$domains = strip_tags( implode( "','", $domains ) );
				$domains = "['" . $domains . "']";
				$fields['domains'] = $domains;
				$this->add( 'linker:autoLink', $fields );
			}

			if ( $this->gadwp->config->options['ga_dash_remarketing'] ) {
				$fields = array();
				$fields['plugin'] = 'displayfeatures';
				$this->add( 'require', $fields );
			}

			if ( $this->gadwp->config->options['ga_enhanced_links'] ) {
				$fields = array();
				$fields['plugin'] = 'linkid';
				$this->add( 'require', $fields );
			}

			if ( $this->gadwp->config->options['ga_author_dimindex'] && ( is_single() || is_page() ) ) {
				$fields = array();
				global $post;
				$author_id = $post->post_author;
				$author_name = get_the_author_meta( 'display_name', $author_id );
				$fields['dimension'] = 'dimension' . (int) $this->gadwp->config->options['ga_author_dimindex'];
				$fields['value'] = esc_attr( $author_name );
				$this->add( 'set', $fields );
			}

			if ( $this->gadwp->config->options['ga_pubyear_dimindex'] && is_single() ) {
				$fields = array();
				global $post;
				$date = get_the_date( 'Y', $post->ID );
				$fields['dimension'] = 'dimension' . (int) $this->gadwp->config->options['ga_pubyear_dimindex'];
				$fields['value'] = (int) $date;
				$this->add( 'set', $fields );
			}

			if ( $this->gadwp->config->options['ga_pubyearmonth_dimindex'] && is_single() ) {
				$fields = array();
				global $post;
				$date = get_the_date( 'Y-m', $post->ID );
				$fields['dimension'] = 'dimension' . (int) $this->gadwp->config->options['ga_pubyearmonth_dimindex'];
				$fields['value'] = esc_attr( $date );
				$this->add( 'set', $fields );
			}

			if ( $this->gadwp->config->options['ga_category_dimindex'] && is_category() ) {
				$fields = array();
				$fields['dimension'] =  'dimension' . (int) $this->gadwp->config->options['ga_category_dimindex'];
				$fields['value'] = esc_attr( single_tag_title() );
				$this->add( 'set', $fields );
			}
			if ( $this->gadwp->config->options['ga_category_dimindex'] && is_single() ) {
				$fields = array();
				global $post;
				$categories = get_the_category( $post->ID );
				foreach ( $categories as $category ) {
					$fields['dimension'] = 'dimension' . (int) $this->gadwp->config->options['ga_category_dimindex'];
					$fields['value'] = esc_attr( $category->name );
					$this->add( 'set', $fields );
					break;
				}
			}

			if ( $this->gadwp->config->options['ga_tag_dimindex'] && is_single() ) {
				global $post;
				$fields = array();
				$post_tags_list = '';
				$post_tags_array = get_the_tags( $post->ID );
				if ( $post_tags_array ) {
					foreach ( $post_tags_array as $tag ) {
						$post_tags_list .= $tag->name . ', ';
					}
				}
				$post_tags_list = rtrim( $post_tags_list, ', ' );
				if ( $post_tags_list ) {
					$fields['dimension'] = 'dimension' . (int) $this->gadwp->config->options['ga_tag_dimindex'];
					$fields['value'] = esc_attr( $post_tags_list );
					$this->add( 'set', $fields );
				}
			}

			if ( $this->gadwp->config->options['ga_user_dimindex'] ) {
				$fields = array();
				$fields['dimension'] = 'dimension' . (int) $this->gadwp->config->options['ga_user_dimindex'];
				$fields['value'] = is_user_logged_in() ? 'registered' : 'guest';
				$this->add( 'set', $fields );
			}

			if ( $this->gadwp->config->options['ga_dash_anonim'] ) {
				$fields = array();
				$fields['option'] = 'anonymizeIp';
				$fields['value'] = 'true';
				$this->add( 'set', $fields );
			}

			$fields = array();
			$fields['hitType'] = 'pageview';
			$this->add( 'send', $fields );

			do_action( 'gadwp_analytics_commands' );
		}

		public function output() {
			$this->commands = array();

			$this->build_commands();

			?>
<!-- BEGIN GADWP v<?php echo GADWP_CURRENT_VERSION; ?> Universal Analytics - https://deconf.com/google-analytics-dashboard-wordpress/ -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
<?php
			foreach ( $this->commands as $set ) {
				$command = $set['command'];

				$fields = '';
				foreach ( $set['fields'] as $fieldkey => $fieldvalue ) {
					$fieldvalue = $this->filter( $fieldvalue );
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
					echo "  ga('" . $command . "'" . $fields . $fieldsobject . ");\n";
				} else {
					echo "  ga('" . $command . "'" . $fields . ");\n";
				}
			}
			?>
</script>
<!-- END GADWP Universal Analytics -->
<?php
		}

		public function amp_add_analytics_script() {
			?><script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script><?php
		}

		public function amp_output() {
			?><amp-analytics type="googleanalytics" id="gadwp-googleanalytics"> <script type="application/json">{"vars": { "account" : "<?php echo $this->uaid; ?>"}, "triggers": { "trackPageview": { "on": "visible", "request": "pageview" }}}</script> </amp-analytics><?php
		}
	}
}