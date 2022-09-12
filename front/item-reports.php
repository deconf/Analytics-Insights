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
if ( ! class_exists( 'AIWP_Frontend_Item_Reports' ) ) {

	final class AIWP_Frontend_Item_Reports {

		private $aiwp;

		public function __construct() {
			$this->aiwp = AIWP();
			add_action( 'admin_bar_menu', array( $this, 'custom_adminbar_node' ), 999 );
		}

		function custom_adminbar_node( $wp_admin_bar ) {
			if ( AIWP_Tools::check_roles( $this->aiwp->config->options['access_front'] ) && $this->aiwp->config->options['frontend_item_reports'] ) {
				/* @formatter:off */
				$args = array( 	'id' => 'aiwp-1',
								'title' => '<span class="ab-icon"></span><span class="">' . __( "Analytics", 'analytics-insights' ) . '</span>',
								'href' => '#1',
								);
				/* @formatter:on */
				$wp_admin_bar->add_node( $args );
			}
		}
	}
}
