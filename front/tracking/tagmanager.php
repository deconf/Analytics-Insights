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

		private $containerid;

		private $datalayer;

		public function __construct( $options ) {
			$this->containerid = $options['tm_containerid'];

			add_action( 'wp_head', array( $this, 'output' ), 99 );
		}

		private function add_var( $name, $value ) {
			$this->datalayer[$name] = $this->datalayer[$value];
		}

		private function build_datalayer( $options ) {
			if ( $options['tm_author_var'] && ( is_single() || is_page() ) ) {
				global $post;
				$author_id = $post->post_author;
				$author_name = get_the_author_meta( 'display_name', $author_id );
				$this->add_var( 'author', esc_attr( $author_name ) );
			}

			if ( $options['tm_pubyear_var'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y', $post->ID );
				$this->add_var( 'publicationYear', (int) $date );
			}

			if ( $options['tm_pubyearmonth_var'] && is_single() ) {
				global $post;
				$date = get_the_date( 'Y-m', $post->ID );
				$this->add_var( 'publicationYearMonth', esc_attr( $date ) );
			}

			if ( $options['tm_category_var'] && is_category() ) {
				$this->add_var( 'category', esc_attr( single_tag_title() ) );
			}
			if ( $options['tm_category_var'] && is_single() ) {
				global $post;
				$categories = get_the_category( $post->ID );
				foreach ( $categories as $category ) {
					$this->add_var( 'category', esc_attr( $category->name ) );
					break;
				}
			}

			if ( $options['tm_tag_var'] && is_single() ) {
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
					$this->add_var( 'tag', esc_attr( $post_tags_list ) );
				}
			}

			if ( $options['tm_user_var'] ) {
				$usertype = is_user_logged_in() ? 'registered' : 'guest';
				$this->add_var( 'user', $usertype );
			}

			do_action( 'gadwp_tagmanager_datalayer' );
		}

		private function output() {
			?>
<!-- BEGIN GADWP v<?php echo GADWP_CURRENT_VERSION; ?> Tag Manager - https://deconf.com/google-analytics-dashboard-wordpress/ -->
<?php if ( is_array( $this->datalayer ) ) :	?>
<script>
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push(<?php echo json_encode($this->datalayer); ?>);
</script>
<?php endif; ?>
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo $this->containerid; ?>');
</script>
<!-- END GADWP Tag Manager Tracking -->
<?php
		}
	}
}