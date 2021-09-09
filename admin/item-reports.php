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
if ( ! class_exists( 'AIWP_Backend_Item_Reports' ) ) {

	final class AIWP_Backend_Item_Reports {

		private $aiwp;

		public function __construct() {
			$this->aiwp = AIWP();
			if ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_back'] ) && 1 == $this->aiwp->config->options['backend_item_reports'] ) {
				// Add custom column in Posts List
				add_filter( 'manage_posts_columns', array( $this, 'add_columns' ) );
				// Populate custom column in Posts List
				add_action( 'manage_posts_custom_column', array( $this, 'add_icons' ), 10, 2 );
				// Add custom column in Pages List
				add_filter( 'manage_pages_columns', array( $this, 'add_columns' ) );
				// Populate custom column in Pages List
				add_action( 'manage_pages_custom_column', array( $this, 'add_icons' ), 10, 2 );
			}
		}

		public function add_icons( $column, $id ) {
			global $wp_version;
			if ( 'aiwp_stats' != $column ) {
				return;
			}
			if ( version_compare( $wp_version, '3.8.0', '>=' ) ) {
				echo '<a id="aiwp-' . esc_attr( $id ) . '" title="' . get_the_title( $id ) . '" href="#' . esc_attr( $id ) . '" class="aiwp-icon dashicons-before dashicons-chart-area">&nbsp;</a>';
			} else {
				echo '<a id="aiwp-' . esc_attr( $id ) . '" title="' . get_the_title( $id ) . '" href="#' . esc_attr( $id ) . '"><img class="aiwp-icon-oldwp" src="' . AIWP_URL . 'admin/images/aiwp-icon.png"</a>';
			}
		}

		public function add_columns( $columns ) {
			return array_merge( $columns, array( 'aiwp_stats' => __( 'Analytics', 'analytics-insights' ) ) );
		}
	}
}
