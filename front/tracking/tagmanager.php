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

if ( ! class_exists( 'GADWP_Tracking_TagManager' ) ) {

	class GADWP_Tracking_TagManager {

		private $gadwp;

		private $datalayer;

		public function __construct() {
			$this->gadwp = GADWP();
			$this->gadwp->config->options['amp_containerid'] = "GTM-MDK2CZK";

			if ($this->gadwp->config->options['trackingcode_infooter']) {
				add_action( 'wp_footer', array( $this, 'output' ), 99 );
			} else {
				add_action( 'wp_head', array( $this, 'output' ), 99 );
			}

			if ( $this->gadwp->config->options['amp_tracking_tagmanager'] && $this->gadwp->config->options['amp_containerid'] ) {
				add_action( 'amp_post_template_head', array( $this, 'amp_add_analytics_script' ) );
				add_action( 'amp_post_template_footer', array( $this, 'amp_output' ) );
			}
		}

		private function add_var( $name, $value ) {
			$this->datalayer[$name] = $value;
		}

		private function build_datalayer() {
			global $post;

			if ( $this->gadwp->config->options['tm_author_var'] && ( is_single() || is_page() ) ) {
				global $post;
				$author_id = $post->post_author;
				$author_name = get_the_author_meta( 'display_name', $author_id );
				$this->add_var( 'gadwpAuthor', esc_attr( $author_name ) );
			}

			if ( $this->gadwp->config->options['tm_pubyear_var'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y', $post->ID );
				$this->add_var( 'gadwpPublicationYear', (int) $date );
			}

			if ( $this->gadwp->config->options['tm_pubyearmonth_var'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y-m', $post->ID );
				$this->add_var( 'gadwpPublicationYearMonth', esc_attr( $date ) );
			}

			if ( $this->gadwp->config->options['tm_category_var'] && is_category() ) {
				$this->add_var( 'gadwpCategory', esc_attr( single_tag_title() ) );
			}
			if ( $this->gadwp->config->options['tm_category_var'] && is_single() ) {
				global $post;
				$categories = get_the_category( $post->ID );
				foreach ( $categories as $category ) {
					$this->add_var( 'gadwpCategory', esc_attr( $category->name ) );
					break;
				}
			}

			if ( $this->gadwp->config->options['tm_tag_var'] && is_single() ) {
				global $post;
				$post_tags_list = '';
				$post_tags_array = get_the_tags( $post->ID );
				if ( $post_tags_array ) {
					foreach ( $post_tags_array as $tag ) {
						$post_tags_list .= $tag->name . ', ';
					}
				}
				$post_tags_list = rtrim( $post_tags_list, ', ' );
				if ( $post_tags_list ) {
					$this->add_var( 'gadwpTag', esc_attr( $post_tags_list ) );
				}
			}

			if ( $this->gadwp->config->options['tm_user_var'] ) {
				$usertype = is_user_logged_in() ? 'registered' : 'guest';
				$this->add_var( 'gadwpUser', $usertype );
			}

			do_action( 'gadwp_tagmanager_datalayer' );
		}

		public function output() {
			$this->build_datalayer();

			?>
<!-- BEGIN GADWP v<?php echo GADWP_CURRENT_VERSION; ?> Tag Manager - https://deconf.com/google-analytics-dashboard-wordpress/ -->
<?php
			if ( is_array( $this->datalayer ) ) {
				$vars = "{";
				foreach ( $this->datalayer as $var => $value ) {
					$vars .= "'" . $var . "': '" . $value . "', ";
				}
				$vars = rtrim( $vars, ", " );
				$vars .= "}";
				?>
<script>
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push(<?php echo $vars; ?>);
</script>
<?php } ?>
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo $this->gadwp->config->options['web_containerid']; ?>');
</script>
<!-- END GADWP Tag Manager -->
<?php
		}

		public function amp_add_analytics_script() {
			?><script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script><?php
		}

		public function amp_output() {
			?><amp-analytics config="https://www.googletagmanager.com/amp.json?id=<?php echo $this->gadwp->config->options['amp_containerid']; ?>&gtm.url=SOURCE_URL" data-credentials="include"></amp-analytics><?php
		}
	}
}