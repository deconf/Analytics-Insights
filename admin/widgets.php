<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Backend_Widgets' ) ) {

	class GADWP_Backend_Widgets {

		private $gadwp;

		public function __construct() {
			$this->gadwp = GADWP();
			if ( GADWP_Tools::check_roles( $this->gadwp->config->options['ga_dash_access_back'] ) && ( 1 == $this->gadwp->config->options['dashboard_widget'] ) ) {
				add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
			}
		}

		public function add_widget() {
			wp_add_dashboard_widget( 'gadwp-widget', __( "Google Analytics Dashboard", 'google-analytics-dashboard-for-wp' ), array( $this, 'dashboard_widget' ), $control_callback = null );
		}

		public function dashboard_widget() {
			if ( empty( $this->gadwp->config->options['ga_dash_token'] ) ) {
				echo '<p>' . __( "This plugin needs an authorization:", 'google-analytics-dashboard-for-wp' ) . '</p><form action="' . menu_page_url( 'gadash_settings', false ) . '" method="POST">' . get_submit_button( __( "Authorize Plugin", 'google-analytics-dashboard-for-wp' ), 'secondary' ) . '</form>';
				return;
			}

			if ( current_user_can( 'manage_options' ) ) {
				if ( isset( $_REQUEST['gadwp_selected_profile'] ) ) {
					$this->gadwp->config->options['ga_dash_tableid'] = $_REQUEST['gadwp_selected_profile'];
				}
				$profiles = $this->gadwp->config->options['ga_dash_profile_list'];
				$profile_switch = '';
				if ( ! empty( $profiles ) ) {
					if ( ! $this->gadwp->config->options['ga_dash_tableid'] ) {
						if ( $this->gadwp->config->options['ga_dash_tableid_jail'] ) {
							$this->gadwp->config->options['ga_dash_tableid'] = $this->gadwp->config->options['ga_dash_tableid_jail'];
						} else {
							$this->gadwp->config->options['ga_dash_tableid'] = GADWP_Tools::guess_default_domain( $profiles );
						}
					} else
						if ( $this->gadwp->config->options['switch_profile'] == 0 && $this->gadwp->config->options['ga_dash_tableid_jail'] ) {
							$this->gadwp->config->options['ga_dash_tableid'] = $this->gadwp->config->options['ga_dash_tableid_jail'];
						}
					$profile_switch .= '<select id="gadwp_selected_profile" name="gadwp_selected_profile" onchange="this.form.submit()">';
					foreach ( $profiles as $profile ) {
						if ( ! $this->gadwp->config->options['ga_dash_tableid'] ) {
							$this->gadwp->config->options['ga_dash_tableid'] = $profile[1];
						}
						if ( isset( $profile[3] ) ) {
							$profile_switch .= '<option value="' . esc_attr( $profile[1] ) . '" ';
							$profile_switch .= selected( $profile[1], $this->gadwp->config->options['ga_dash_tableid'], false );
							$profile_switch .= ' title="' . __( "View Name:", 'google-analytics-dashboard-for-wp' ) . ' ' . esc_attr( $profile[0] ) . '">' . esc_attr( GADWP_Tools::strip_protocol( $profile[3] ) ) . '</option>';
						}
					}
					$profile_switch .= "</select>";
				} else {
					echo '<p>' . __( "Something went wrong while retrieving profiles list.", 'google-analytics-dashboard-for-wp' ) . '</p><form action="' . menu_page_url( 'gadash_settings', false ) . '" method="POST">' . get_submit_button( __( "More details", 'google-analytics-dashboard-for-wp' ), 'secondary' ) . '</form>';
					return;
				}
			}
			$this->gadwp->config->set_plugin_options();

			if ( current_user_can( 'manage_options' ) ) {
				if ( $this->gadwp->config->options['switch_profile'] == 0 ) {
					if ( $this->gadwp->config->options['ga_dash_tableid_jail'] ) {
						$projectId = $this->gadwp->config->options['ga_dash_tableid_jail'];
					} else {
						echo '<p>' . __( "An admin should asign a default Google Analytics Profile.", 'google-analytics-dashboard-for-wp' ) . '</p><form action="' . menu_page_url( 'gadash_settings', false ) . '" method="POST">' . get_submit_button( __( "Select Domain", 'google-analytics-dashboard-for-wp' ), 'secondary' ) . '</form>';
						return;
					}
				} else {
					echo $profile_switch;
					$projectId = $this->gadwp->config->options['ga_dash_tableid'];
				}
			} else {
				if ( $this->gadwp->config->options['ga_dash_tableid_jail'] ) {
					$projectId = $this->gadwp->config->options['ga_dash_tableid_jail'];
				} else {
					echo '<p>' . __( "An admin should asign a default Google Analytics Profile.", 'google-analytics-dashboard-for-wp' ) . '</p><form action="' . menu_page_url( 'gadash_settings', false ) . '" method="POST">' . get_submit_button( __( "Select Domain", 'google-analytics-dashboard-for-wp' ), 'secondary' ) . '</form>';
					return;
				}
			}
			if ( ! ( $projectId ) ) {
				echo '<p>' . __( "Something went wrong while retrieving property data. You need to create and properly configure a Google Analytics account:", 'google-analytics-dashboard-for-wp' ) . '</p> <form action="https://deconf.com/how-to-set-up-google-analytics-on-your-website/" method="POST">' . get_submit_button( __( "Find out more!", 'google-analytics-dashboard-for-wp' ), 'secondary' ) . '</form>';
				return;
			}
		?>
		<div id="gadwp-window-1"></div>
		<?php
		}
	}
}
