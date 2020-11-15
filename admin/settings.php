<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

final class AIWP_Settings {

	private static function update_options( $who ) {
		$aiwp = AIWP();
		$network_settings = false;
		$options = $aiwp->config->options; // Get current options
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) && isset( $_REQUEST['options'] ) && ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) && 'Reset' != $who ) {
			$new_options = $_REQUEST['options'];
			if ( 'tracking' == $who ) {
				$options['ga_anonymize_ip'] = 0;
				$options['ga_optout'] = 0;
				$options['ga_dnt_optout'] = 0;
				$options['ga_event_tracking'] = 0;
				$options['ga_enhanced_links'] = 0;
				$options['ga_event_precision'] = 0;
				$options['ga_remarketing'] = 0;
				$options['ga_event_bouncerate'] = 0;
				$options['ga_crossdomain_tracking'] = 0;
				$options['ga_aff_tracking'] = 0;
				$options['ga_hash_tracking'] = 0;
				$options['ga_formsubmit_tracking'] = 0;
				$options['ga_force_ssl'] = 0;
				$options['ga_pagescrolldepth_tracking'] = 0;
				$options['tm_pagescrolldepth_tracking'] = 0;
				$options['tm_optout'] = 0;
				$options['tm_dnt_optout'] = 0;
				$options['amp_tracking_analytics'] = 0;
				$options['amp_tracking_clientidapi'] = 0;
				$options['amp_tracking_tagmanager'] = 0;
				$options['optimize_pagehiding'] = 0;
				$options['optimize_tracking'] = 0;
				$options['trackingcode_infooter'] = 0;
				$options['trackingevents_infooter'] = 0;
				$options['ga_with_gtag'] = 0;
				if ( isset( $_REQUEST['options']['ga_tracking_code'] ) ) {
					$new_options['ga_tracking_code'] = trim( $new_options['ga_tracking_code'], "\t" );
				}
				if ( empty( $new_options['track_exclude'] ) ) {
					$new_options['track_exclude'] = array();
				}
			} elseif ( 'backend' == $who ) {
				$options['switch_profile'] = 0;
				$options['backend_item_reports'] = 0;
				$options['dashboard_widget'] = 0;
				$options['backend_realtime_report'] = 0;
				if ( empty( $new_options['access_back'] ) ) {
					$new_options['access_back'][] = 'administrator';
				}
			} elseif ( 'frontend' == $who ) {
				$options['frontend_item_reports'] = 0;
				if ( empty( $new_options['access_front'] ) ) {
					$new_options['access_front'][] = 'administrator';
				}
			} elseif ( 'general' == $who ) {
				$options['user_api'] = 0;
				if ( ! is_multisite() ) {
					$options['automatic_updates_minorversion'] = 0;
				}
			} elseif ( 'network' == $who ) {
				$options['user_api'] = 0;
				$options['network_mode'] = 0;
				$options['superadmin_tracking'] = 0;
				$options['automatic_updates_minorversion'] = 0;
				$network_settings = true;
			}
			$options = array_merge( $options, $new_options );
			$aiwp->config->options = $options;
			$aiwp->config->set_plugin_options( $network_settings );
		}
		return $options;
	}

	private static function navigation_tabs( $tabs ) {
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a class='nav-tab' id='tab-$tab' href='#top#aiwp-$tab'>$name</a>";
		}
		echo '</h2>';
	}

	public static function frontend_settings() {
		$aiwp = AIWP();
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'frontend' );
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( ! $aiwp->config->options['tableid_jail'] || ! $aiwp->config->options['token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'analytics-insights' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_errors_debugging', false ), __( 'Errors & Debug', 'analytics-insights' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_settings', false ), __( 'authorize the plugin', 'analytics-insights' ) ) ) );
		}
		?>
<form name="aiwp_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
	<div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Frontend Settings", 'analytics-insights' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff" class="aiwp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="aiwp-settings-options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Permissions", 'analytics-insights' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles aiwp-settings-title">
									<label for="access_front"><?php _e("Show stats to:", 'analytics-insights' ); ?>
									</label>
								</td>
								<td class="aiwp-settings-roles">
									<table>
										<tr>
										<?php if ( ! isset( $wp_roles ) ) : ?>
											<?php $wp_roles = new WP_Roles(); ?>
										<?php endif; ?>
										<?php $i = 0; ?>
										<?php foreach ( $wp_roles->role_names as $role => $name ) : ?>
											<?php if ( 'subscriber' != $role ) : ?>
												<?php $i++; ?>
												<td>
												<label>
													<input type="checkbox" name="options[access_front][]" value="<?php echo $role; ?>" <?php if ( in_array($role,$options['access_front']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /><?php echo $name; ?>
												  </label>
											</td>
											<?php endif; ?>
											<?php if ( 0 == $i % 4 ) : ?>
										 </tr>
										<tr>
											<?php endif; ?>
										<?php endforeach; ?>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<div class="button-primary aiwp-settings-switchoo">
										<input type="checkbox" name="options[frontend_item_reports]" value="1" class="aiwp-settings-switchoo-checkbox" id="frontend_item_reports" <?php checked( $options['frontend_item_reports'], 1 ); ?>>
										<label class="aiwp-settings-switchoo-label" for="frontend_item_reports">
											<div class="aiwp-settings-switchoo-inner"></div>
											<div class="aiwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php echo " ".__("enable web page reports on frontend", 'analytics-insights' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="submit">
									<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="options[aiwp_hidden]" value="Y">
						<?php wp_nonce_field('aiwp_form','aiwp_security');?>






</form>
<?php
		self::output_sidebar();
	}

	public static function backend_settings() {
		$aiwp = AIWP();
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'backend' );
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( ! $aiwp->config->options['tableid_jail'] || ! $aiwp->config->options['token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'analytics-insights' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_errors_debugging', false ), __( 'Errors & Debug', 'analytics-insights' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_settings', false ), __( 'authorize the plugin', 'analytics-insights' ) ) ) );
		}
		?>
<form name="aiwp_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
	<div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Backend Settings", 'analytics-insights' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff" class="aiwp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="aiwp-settings-options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Permissions", 'analytics-insights' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles aiwp-settings-title">
									<label for="access_back"><?php _e("Show stats to:", 'analytics-insights' ); ?>
									</label>
								</td>
								<td class="aiwp-settings-roles">
									<table>
										<tr>
										<?php if ( ! isset( $wp_roles ) ) : ?>
											<?php $wp_roles = new WP_Roles(); ?>
										<?php endif; ?>
										<?php $i = 0; ?>
										<?php foreach ( $wp_roles->role_names as $role => $name ) : ?>
											<?php if ( 'subscriber' != $role ) : ?>
												<?php $i++; ?>
											<td>
												<label>
													<input type="checkbox" name="options[access_back][]" value="<?php echo $role; ?>" <?php if ( in_array($role,$options['access_back']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /> <?php echo $name; ?>
												</label>
											</td>
											<?php endif; ?>
											<?php if ( 0 == $i % 4 ) : ?>
										</tr>
										<tr>
											<?php endif; ?>
										<?php endforeach; ?>






									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<div class="button-primary aiwp-settings-switchoo">
										<input type="checkbox" name="options[switch_profile]" value="1" class="aiwp-settings-switchoo-checkbox" id="switch_profile" <?php checked( $options['switch_profile'], 1 ); ?>>
										<label class="aiwp-settings-switchoo-label" for="switch_profile">
											<div class="aiwp-settings-switchoo-inner"></div>
											<div class="aiwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "enable Switch View functionality", 'analytics-insights' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<div class="button-primary aiwp-settings-switchoo">
										<input type="checkbox" name="options[backend_item_reports]" value="1" class="aiwp-settings-switchoo-checkbox" id="backend_item_reports" <?php checked( $options['backend_item_reports'], 1 ); ?>>
										<label class="aiwp-settings-switchoo-label" for="backend_item_reports">
											<div class="aiwp-settings-switchoo-inner"></div>
											<div class="aiwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "enable reports on Posts List and Pages List", 'analytics-insights' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<div class="button-primary aiwp-settings-switchoo">
										<input type="checkbox" name="options[dashboard_widget]" value="1" class="aiwp-settings-switchoo-checkbox" id="dashboard_widget" <?php checked( $options['dashboard_widget'], 1 ); ?>>
										<label class="aiwp-settings-switchoo-label" for="dashboard_widget">
											<div class="aiwp-settings-switchoo-inner"></div>
											<div class="aiwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "enable the main Dashboard Widget", 'analytics-insights' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "Real-Time Settings", 'analytics-insights' ) . "</h2>"; ?></td>
							</tr>
							<?php if ( $options['user_api'] ) : ?>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<div class="button-primary aiwp-settings-switchoo">
										<input type="checkbox" name="options[backend_realtime_report]" value="1" class="aiwp-settings-switchoo-checkbox" id="backend_realtime_report" <?php checked( $options['backend_realtime_report'], 1 ); ?>>
										<label class="aiwp-settings-switchoo-label" for="backend_realtime_report">
											<div class="aiwp-settings-switchoo-inner"></div>
											<div class="aiwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "enable Real-Time report (requires access to Real-Time Reporting API)", 'analytics-insights' );?></div>
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<td colspan="2" class="aiwp-settings-title"> <?php _e("Maximum number of pages to display on real-time tab:", 'analytics-insights'); ?>
									<input type="number" name="options[ga_realtime_pages]" id="ga_realtime_pages" value="<?php echo (int)$options['ga_realtime_pages']; ?>" size="3">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "Location Settings", 'analytics-insights' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<?php echo __("Target Geo Map to country:", 'analytics-insights'); ?>
									<input type="text" style="text-align: center;" name="options[ga_target_geomap]" value="<?php echo esc_attr($options['ga_target_geomap']); ?>" size="3">
								</td>
							</tr>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<?php echo __("Maps API Key:", 'analytics-insights'); ?>
									<input type="text" style="text-align: center;" name="options[maps_api_key]" value="<?php echo esc_attr($options['maps_api_key']); ?>" size="50">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "404 Errors Report", 'analytics-insights' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="aiwp-settings-title">
									<?php echo __("404 Page Title contains:", 'analytics-insights'); ?>
									<input type="text" style="text-align: center;" name="options[pagetitle_404]" value="<?php echo esc_attr($options['pagetitle_404']); ?>" size="20">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="submit">
									<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="options[aiwp_hidden]" value="Y">
						<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>






</form>
<?php
		self::output_sidebar();
	}

	public static function tracking_settings() {
		$aiwp = AIWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'tracking' );
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( ! $aiwp->config->options['tableid_jail'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'analytics-insights' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_errors_debugging', false ), __( 'Errors & Debug', 'analytics-insights' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_settings', false ), __( 'authorize the plugin', 'analytics-insights' ) ) ) );
		}
		?>
<form name="aiwp_form" method="post" action="<?php  esc_url($_SERVER['REQUEST_URI']); ?>">
	<div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Tracking Code", 'analytics-insights' ) . "</h2>"; ?>
	</div>
	<div id="poststuff" class="aiwp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
						<?php if ( 'universal' == $options['tracking_type'] ) :?>
						<?php $tabs = array( 'basic' => __( "Basic Settings", 'analytics-insights' ), 'events' => __( "Events Tracking", 'analytics-insights' ), 'custom' => __( "Custom Definitions", 'analytics-insights' ), 'exclude' => __( "Exclude Tracking", 'analytics-insights' ), 'advanced' => __( "Advanced Settings", 'analytics-insights' ), 'integration' => __( "Integration", 'analytics-insights' ) );?>
						<?php elseif ( 'tagmanager' == $options['tracking_type'] ) :?>
						<?php $tabs = array( 'basic' => __( "Basic Settings", 'analytics-insights' ), 'tmdatalayervars' => __( "DataLayer Variables", 'analytics-insights' ), 'exclude' => __( "Exclude Tracking", 'analytics-insights' ), 'tmadvanced' =>  __( "Advanced Settings", 'analytics-insights' ), 'tmintegration' => __( "Integration", 'analytics-insights' ) );?>
						<?php else :?>
						<?php $tabs = array( 'basic' => __( "Basic Settings", 'analytics-insights' ) );?>
						<?php endif; ?>
						<?php self::navigation_tabs( $tabs ); ?>
						<?php if ( isset( $message ) ) : ?>
							<?php echo $message; ?>
						<?php endif; ?>
						<div id="aiwp-basic">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Tracking Settings", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tracking_type"><?php _e("Tracking Type:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="tracking_type" name="options[tracking_type]" onchange="this.form.submit()">
											<option value="universal" <?php selected( $options['tracking_type'], 'universal' ); ?>><?php _e("Analytics", 'analytics-insights');?></option>
											<option value="tagmanager" <?php selected( $options['tracking_type'], 'tagmanager' ); ?>><?php _e("Tag Manager", 'analytics-insights');?></option>
											<option value="disabled" <?php selected( $options['tracking_type'], 'disabled' ); ?>><?php _e("Disabled", 'analytics-insights');?></option>
										</select>
									</td>
								</tr>
								<?php if ( 'universal' == $options['tracking_type'] ) : ?>
								<tr>
									<td class="aiwp-settings-title"></td>
									<td>
										<?php $profile_info = AIWP_Tools::get_selected_profile($aiwp->config->options['ga_profiles_list'], $aiwp->config->options['tableid_jail']); ?>
										<?php echo '<pre>' . __("View Name:", 'analytics-insights') . "\t" . esc_html($profile_info[0]) . "<br />" . __("Tracking ID:", 'analytics-insights') . "\t" . esc_html($profile_info[2]) . "<br />" . __("Default URL:", 'analytics-insights') . "\t" . esc_html($profile_info[3]) . "<br />" . __("Time Zone:", 'analytics-insights') . "\t" . esc_html($profile_info[5]) . '</pre>';?>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_with_gtag]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_with_gtag" <?php checked( $options['ga_with_gtag'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_with_gtag">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("use global site tag gtag.js (not recommended)", 'analytics-insights' );?></div>
									</td>
								</tr>
								<?php elseif ( 'tagmanager' == $options['tracking_type'] ) : ?>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tracking_type"><?php _e("Web Container ID:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="options[web_containerid]" value="<?php echo esc_attr($options['web_containerid']); ?>" size="15">
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td class="aiwp-settings-title">
										<label for="trackingcode_infooter"><?php _e("Code Placement:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="trackingcode_infooter" name="options[trackingcode_infooter]">
											<option value="0" <?php selected( $options['trackingcode_infooter'], 0 ); ?>><?php _e("HTML Head", 'analytics-insights');?></option>
											<option value="1" <?php selected( $options['trackingcode_infooter'], 1 ); ?>><?php _e("HTML Body", 'analytics-insights');?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-events">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Events Tracking", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_event_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_event_tracking" <?php checked( $options['ga_event_tracking'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_event_tracking">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track downloads, mailto, telephone and outbound links", 'analytics-insights' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_aff_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_aff_tracking" <?php checked( $options['ga_aff_tracking'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_aff_tracking">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track affiliate links", 'analytics-insights' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_hash_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_hash_tracking" <?php checked( $options['ga_hash_tracking'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_hash_tracking">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track fragment identifiers, hashmarks (#) in URI links", 'analytics-insights' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_formsubmit_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_formsubmit_tracking" <?php checked( $options['ga_formsubmit_tracking'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_formsubmit_tracking">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track form submit actions", 'analytics-insights' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_pagescrolldepth_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_pagescrolldepth_tracking" <?php checked( $options['ga_pagescrolldepth_tracking'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_pagescrolldepth_tracking">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track page scrolling depth", 'analytics-insights' ); ?></div>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_event_downloads"><?php _e("Downloads Regex:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_event_downloads" name="options[ga_event_downloads]" value="<?php echo esc_attr($options['ga_event_downloads']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_event_affiliates"><?php _e("Affiliates Regex:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_event_affiliates" name="options[ga_event_affiliates]" value="<?php echo esc_attr($options['ga_event_affiliates']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="trackingevents_infooter"><?php _e("Code Placement:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="trackingevents_infooter" name="options[trackingevents_infooter]">
											<option value="0" <?php selected( $options['trackingevents_infooter'], 0 ); ?>><?php _e("HTML Head", 'analytics-insights');?></option>
											<option value="1" <?php selected( $options['trackingevents_infooter'], 1 ); ?>><?php _e("HTML Body", 'analytics-insights');?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-custom">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Custom Dimensions", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_author_dimindex"><?php _e("Authors:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="ga_author_dimindex" name="options[ga_author_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo $i;?>" <?php selected( $options['ga_author_dimindex'], $i ); ?>><?php echo 0 == $i ?'Disabled':'dimension '.$i; ?></option>
										<?php endfor; ?>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_pubyear_dimindex"><?php _e("Publication Year:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="ga_pubyear_dimindex" name="options[ga_pubyear_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo $i;?>" <?php selected( $options['ga_pubyear_dimindex'], $i ); ?>><?php echo 0 == $i ?'Disabled':'dimension '.$i; ?></option>
										<?php endfor; ?>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_pubyearmonth_dimindex"><?php _e("Publication Month:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="ga_pubyearmonth_dimindex" name="options[ga_pubyearmonth_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo $i;?>" <?php selected( $options['ga_pubyearmonth_dimindex'], $i ); ?>><?php echo 0 == $i ?'Disabled':'dimension '.$i; ?></option>
										<?php endfor; ?>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_category_dimindex"><?php _e("Categories:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="ga_category_dimindex" name="options[ga_category_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo $i;?>" <?php selected( $options['ga_category_dimindex'], $i ); ?>><?php echo 0 == $i ? 'Disabled':'dimension '.$i; ?></option>
										<?php endfor; ?>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_user_dimindex"><?php _e("User Type:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="ga_user_dimindex" name="options[ga_user_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo $i;?>" <?php selected( $options['ga_user_dimindex'], $i ); ?>><?php echo 0 == $i ? 'Disabled':'dimension '.$i; ?></option>
										<?php endfor; ?>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_tag_dimindex"><?php _e("Tags:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="ga_tag_dimindex" name="options[ga_tag_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_tag_dimindex'], $i ); ?>><?php echo 0 == $i ? 'Disabled':'dimension '.$i; ?></option>
										<?php endfor; ?>
										</select>
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-tmdatalayervars">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Main Variables", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tm_author_var"><?php _e("Authors:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_author_var" name="options[tm_author_var]">
											<option value="1" <?php selected( $options['tm_author_var'], 1 ); ?>>aiwpAuthor</option>
											<option value="0" <?php selected( $options['tm_author_var'], 0 ); ?>><?php _e( "Disabled", 'analytics-insights' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tm_pubyear_var"><?php _e("Publication Year:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_pubyear_var" name="options[tm_pubyear_var]">
											<option value="1" <?php selected( $options['tm_pubyear_var'], 1 ); ?>>aiwpPublicationYear</option>
											<option value="0" <?php selected( $options['tm_pubyear_var'], 0 ); ?>><?php _e( "Disabled", 'analytics-insights' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tm_pubyearmonth_var"><?php _e("Publication Month:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_pubyearmonth_var" name="options[tm_pubyearmonth_var]">
											<option value="1" <?php selected( $options['tm_pubyearmonth_var'], 1 ); ?>>aiwpPublicationYearMonth</option>
											<option value="0" <?php selected( $options['tm_pubyearmonth_var'], 0 ); ?>><?php _e( "Disabled", 'analytics-insights' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tm_category_var"><?php _e("Categories:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_category_var" name="options[tm_category_var]">
											<option value="1" <?php selected( $options['tm_category_var'], 1 ); ?>>aiwpCategory</option>
											<option value="0" <?php selected( $options['tm_category_var'], 0 ); ?>><?php _e( "Disabled", 'analytics-insights' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tm_user_var"><?php _e("User Type:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_user_var" name="options[tm_user_var]">
											<option value="1" <?php selected( $options['tm_user_var'], 1 ); ?>>aiwpUser</option>
											<option value="0" <?php selected( $options['tm_user_var'], 0 ); ?>><?php _e( "Disabled", 'analytics-insights' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tm_tag_var"><?php _e("Tags:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_tag_var" name="options[tm_tag_var]">
											<option value="1" <?php selected( $options['tm_tag_var'], 1 ); ?>>aiwpTag</option>
											<option value="0" <?php selected( $options['tm_tag_var'], 0 ); ?>><?php _e( "Disabled", 'analytics-insights' ); ?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-advanced">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Advanced Tracking", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_speed_samplerate"><?php _e("Speed Sample Rate:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="number" id="ga_speed_samplerate" name="options[ga_speed_samplerate]" value="<?php echo (int)($options['ga_speed_samplerate']); ?>" max="100" min="1">
										%
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_user_samplerate"><?php _e("User Sample Rate:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="number" id="ga_user_samplerate" name="options[ga_user_samplerate]" value="<?php echo (int)($options['ga_user_samplerate']); ?>" max="100" min="1">
										%
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_anonymize_ip]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_anonymize_ip" <?php checked( $options['ga_anonymize_ip'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_anonymize_ip">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("anonymize IPs while tracking", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_optout]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_optout" <?php checked( $options['ga_optout'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_optout">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable support for user opt-out", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_dnt_optout]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_dnt_optout" <?php checked( $options['ga_dnt_optout'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_dnt_optout">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"> <?php _e( 'exclude tracking for users sending Do Not Track header', 'analytics-insights' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_remarketing]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_remarketing" <?php checked( $options['ga_remarketing'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_remarketing">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable remarketing, demographics and interests reports", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_event_bouncerate]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_event_bouncerate" <?php checked( $options['ga_event_bouncerate'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_event_bouncerate">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("exclude events from bounce-rate and time on page calculation", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_enhanced_links]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_enhanced_links" <?php checked( $options['ga_enhanced_links'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_enhanced_links">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable enhanced link attribution", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_event_precision]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_event_precision" <?php checked( $options['ga_event_precision'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_event_precision">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("use hitCallback to increase event tracking accuracy", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_force_ssl]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_force_ssl" <?php checked( $options['ga_force_ssl'] || $options['ga_with_gtag'], 1 ); ?>  <?php disabled( $options['ga_with_gtag'], true );?>>
											<label class="aiwp-settings-switchoo-label" for="ga_force_ssl">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Force SSL", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Cross-domain Tracking", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[ga_crossdomain_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="ga_crossdomain_tracking" <?php checked( $options['ga_crossdomain_tracking'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="ga_crossdomain_tracking">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable cross domain tracking", 'analytics-insights' ); ?></div>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_crossdomain_list"><?php _e("Cross Domains:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_crossdomain_list" name="options[ga_crossdomain_list]" value="<?php echo esc_attr($options['ga_crossdomain_list']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Cookie Customization", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_cookiedomain"><?php _e("Cookie Domain:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_cookiedomain" name="options[ga_cookiedomain]" value="<?php echo esc_attr($options['ga_cookiedomain']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_cookiename"><?php _e("Cookie Name:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_cookiename" name="options[ga_cookiename]" value="<?php echo esc_attr($options['ga_cookiename']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="ga_cookieexpires"><?php _e("Cookie Expires:", 'analytics-insights'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_cookieexpires" name="options[ga_cookieexpires]" value="<?php echo esc_attr($options['ga_cookieexpires']); ?>" size="10">
										<?php _e("seconds", 'analytics-insights' ); ?>
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-integration">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Accelerated Mobile Pages (AMP)", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[amp_tracking_analytics]" value="1" class="aiwp-settings-switchoo-checkbox" id="amp_tracking_analytics" <?php checked( $options['amp_tracking_analytics'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="amp_tracking_analytics">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable tracking for Accelerated Mobile Pages (AMP)", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[amp_tracking_clientidapi]" value="1" class="aiwp-settings-switchoo-checkbox" id="amp_tracking_clientidapi" <?php checked( $options['amp_tracking_clientidapi'] && !$options['ga_with_gtag'], 1 ); ?> <?php disabled( $options['ga_with_gtag'], true );?>>
											<label class="aiwp-settings-switchoo-label" for="amp_tracking_clientidapi">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Google AMP Client Id API", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Ecommerce", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tracking_type"><?php _e("Ecommerce Tracking:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<select id="ecommerce_mode" name="options[ecommerce_mode]" <?php disabled( $options['ga_with_gtag'], true );?>>
											<option value="disabled" <?php selected( $options['ecommerce_mode'], 'disabled' ); ?>><?php _e("Disabled", 'analytics-insights');?></option>
											<option value="standard" <?php selected( $options['ecommerce_mode'], 'standard' ); ?>><?php _e("Ecommerce Plugin", 'analytics-insights');?></option>
											<option value="enhanced" <?php selected( $options['ecommerce_mode'], 'enhanced' ); selected( $options['ga_with_gtag'], true );?>><?php _e("Enhanced Ecommerce Plugin", 'analytics-insights');?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Optimize", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[optimize_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="optimize_tracking" <?php checked( $options['optimize_tracking'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="optimize_tracking">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Optimize tracking", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[optimize_pagehiding]" value="1" class="aiwp-settings-switchoo-checkbox" id="optimize_pagehiding" <?php checked( $options['optimize_pagehiding'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="optimize_pagehiding">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Page Hiding support", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tracking_type"><?php _e("Container ID:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="options[optimize_containerid]" value="<?php echo esc_attr($options['optimize_containerid']); ?>" size="15">
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-tmadvanced">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Advanced Tracking", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[tm_optout]" value="1" class="aiwp-settings-switchoo-checkbox" id="tm_optout" <?php checked( $options['tm_optout'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="tm_optout">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable support for user opt-out", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[tm_dnt_optout]" value="1" class="aiwp-settings-switchoo-checkbox" id="tm_dnt_optout" <?php checked( $options['tm_dnt_optout'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="tm_dnt_optout">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"> <?php _e( 'exclude tracking for users sending Do Not Track header', 'analytics-insights' ); ?></div>
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-tmintegration">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Accelerated Mobile Pages (AMP)", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="aiwp-settings-title">
										<div class="button-primary aiwp-settings-switchoo">
											<input type="checkbox" name="options[amp_tracking_tagmanager]" value="1" class="aiwp-settings-switchoo-checkbox" id="amp_tracking_tagmanager" <?php checked( $options['amp_tracking_tagmanager'], 1 ); ?>>
											<label class="aiwp-settings-switchoo-label" for="amp_tracking_tagmanager">
												<div class="aiwp-settings-switchoo-inner"></div>
												<div class="aiwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable tracking for Accelerated Mobile Pages (AMP)", 'analytics-insights' );?></div>
									</td>
								</tr>
								<tr>
									<td class="aiwp-settings-title">
										<label for="tracking_type"><?php _e("AMP Container ID:", 'analytics-insights' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="options[amp_containerid]" value="<?php echo esc_attr($options['amp_containerid']); ?>" size="15">
									</td>
								</tr>
							</table>
						</div>
						<div id="aiwp-exclude">
							<table class="aiwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Exclude Tracking", 'analytics-insights' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="roles aiwp-settings-title">
										<label for="track_exclude"><?php _e("Exclude tracking for:", 'analytics-insights' ); ?></label>
									</td>
									<td class="aiwp-settings-roles">
										<table>
											<tr>
										<?php if ( ! isset( $wp_roles ) ) : ?>
											<?php $wp_roles = new WP_Roles(); ?>
										<?php endif; ?>
										<?php $i = 0; ?>
										<?php foreach ( $wp_roles->role_names as $role => $name ) : ?>
											<?php if ( 'subscriber' != $role ) : ?>
												<?php $i++; ?>
											<td>
													<label>
														<input type="checkbox" name="options[track_exclude][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['track_exclude'])) echo 'checked="checked"'; ?> /> <?php echo $name; ?>
											</label>
												</td>
											<?php endif; ?>
											<?php if ( 0 == $i % 4 ) : ?>
										 	</tr>
											<tr>
											<?php endif; ?>
										<?php endforeach; ?>
										</table>
									</td>
								</tr>
							</table>
						</div>
						<table class="aiwp-settings-options">
							<tr>
								<td colspan="2">
									<hr>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="submit">
									<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="options[aiwp_hidden]" value="Y">
						<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>






</form>
<?php
		self::output_sidebar();
	}

	public static function errors_debugging() {

		$aiwp = AIWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$anonim = AIWP_Tools::anonymize_options( $aiwp->config->options );

		$options = self::update_options( 'frontend' );
		if ( ! $aiwp->config->options['tableid_jail'] || ! $aiwp->config->options['token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'analytics-insights' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_errors_debugging', false ), __( 'Errors & Debug', 'analytics-insights' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_settings', false ), __( 'authorize the plugin', 'analytics-insights' ) ) ) );
		}
		?>
<div class="wrap">
		<?php echo "<h2>" . __( "Google Analytics Errors & Debugging", 'analytics-insights' ) . "</h2>"; ?>
</div>
<div id="poststuff" class="aiwp">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<div class="settings-wrapper">
				<div class="inside">
						<?php if (isset($message)) echo $message; ?>
						<?php $tabs = array( 'errors' => __( "Errors & Details", 'analytics-insights' ), 'config' => __( "Plugin Settings", 'analytics-insights' ), 'sysinfo' => __( "System", 'analytics-insights' ) ); ?>
						<?php self::navigation_tabs( $tabs ); ?>
						<div id="aiwp-errors">
						<table class="aiwp-settings-logdata">
							<tr>
								<td>
									<?php echo "<h2>" . __( "Error Details", 'analytics-insights' ) . "</h2>"; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php $errors_count = AIWP_Tools::get_cache( 'errors_count' ); ?>
									<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("Count: ", 'analytics-insights') . '</span>' . (int)$errors_count;?></pre>
									<?php $errors = print_r( AIWP_Tools::get_cache( 'last_error' ), true ) ? esc_html( print_r( AIWP_Tools::get_cache( 'last_error' ), true ) ) : ''; ?>
									<?php $errors = str_replace( 'Deconf_', 'Google_', $errors); ?>
									<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("Last Error: ", 'analytics-insights') . '</span>' . "\n" . $errors;?></pre>
									<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("GAPI Error: ", 'analytics-insights') . '</span>'; echo "\n" . esc_html( print_r( AIWP_Tools::get_cache( 'gapi_errors' ), true ) ) ?></pre>
									<br />
									<hr>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo "<h2>" . __( "Sampled Data", 'analytics-insights' ) . "</h2>"; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php $sampling = AIWP_TOOLS::get_cache( 'sampleddata' ); ?>
									<?php if ( $sampling ) :?>
									<?php printf( __( "Last Detected on %s.", 'analytics-insights' ), '<strong>'. $sampling['date'] . '</strong>' );?>
									<br />
									<?php printf( __( "The report was based on %s of sessions.", 'analytics-insights' ), '<strong>'. $sampling['percent'] . '</strong>' );?>
									<br />
									<?php printf( __( "Sessions ratio: %s.", 'analytics-insights' ), '<strong>'. $sampling['sessions'] . '</strong>' ); ?>
									<?php else :?>
									<?php _e( "None", 'analytics-insights' ); ?>
									<?php endif;?>
								</td>
							</tr>
						</table>
					</div>
					<div id="aiwp-config">
						<table class="aiwp-settings-options">
							<tr>
								<td><?php echo "<h2>" . __( "Plugin Configuration", 'analytics-insights' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td>
									<pre class="aiwp-settings-logdata"><?php echo esc_html(print_r($anonim, true));?></pre>
									<br />
									<hr>
								</td>
							</tr>
						</table>
					</div>
					<div id="aiwp-sysinfo">
						<table class="aiwp-settings-options">
							<tr>
								<td><?php echo "<h2>" . __( "System Information", 'analytics-insights' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td>
									<pre class="aiwp-settings-logdata"><?php echo esc_html(AIWP_Tools::system_info());?></pre>
									<br />
									<hr>
								</td>
							</tr>
						</table>
					</div>
	<?php
		self::output_sidebar();
	}

	public static function general_settings() {
		$aiwp = AIWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'general' );
		printf( '<div id="gapi-warning" class="updated"><p>%1$s <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=aiwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=aiwp">%2$s</a></p></div>', __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'analytics-insights' ), __( 'Library conflicts between WordPress plugins', 'analytics-insights' ) );
		if ( null === $aiwp->gapi_controller ) {
			$aiwp->gapi_controller = new AIWP_GAPI_Controller();
		}
		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
		if ( isset( $_REQUEST['aiwp_access_code'] ) ) {
			if ( 1 == ! stripos( 'x' . $_REQUEST['aiwp_access_code'], 'UA-', 1 ) && $_REQUEST['aiwp_access_code'] != get_option( 'aiwp_redeemed_code' ) ) {
				try {
					$aiwp_access_code = $_REQUEST['aiwp_access_code'];
					update_option( 'aiwp_redeemed_code', $aiwp_access_code );
					AIWP_Tools::delete_cache( 'gapi_errors' );
					AIWP_Tools::delete_cache( 'last_error' );
					$aiwp->gapi_controller->client->authenticate( $_REQUEST['aiwp_access_code'] );
					$aiwp->config->options['token'] = $aiwp->gapi_controller->client->getAccessToken();
					$aiwp->config->options['automatic_updates_minorversion'] = 1;
					$aiwp->config->set_plugin_options();
					$options = self::update_options( 'general' );
					$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Plugin authorization succeeded.", 'analytics-insights' ) . "</p></div>";
					if ( $aiwp->config->options['token'] && $aiwp->gapi_controller->client->getAccessToken() ) {
						$profiles = $aiwp->gapi_controller->refresh_profiles();
						if ( is_array ( $profiles ) && ! empty( $profiles ) ) {
							$aiwp->config->options['ga_profiles_list'] = $profiles;
							if ( ! $aiwp->config->options['tableid_jail'] ) {
								$profile = AIWP_Tools::guess_default_domain( $profiles );
								$aiwp->config->options['tableid_jail'] = $profile;
							}
							$aiwp->config->set_plugin_options();
							$options = self::update_options( 'general' );
						}
					}
				} catch ( Deconf_IO_Exception $e ) {
					$timeout = $aiwp->gapi_controller->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} catch ( Deconf_Service_Exception $e ) {
					$timeout = $aiwp->gapi_controller->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} catch ( Exception $e ) {
					$timeout = $aiwp->gapi_controller->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
					$aiwp->gapi_controller->reset_token();
				}
			} else {
				if ( 1 == stripos( 'x' . $_REQUEST['aiwp_access_code'], 'UA-', 1 ) ) {
					$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "The access code is <strong>not</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X) <strong>nor</strong> your <strong>email address</strong>!", 'analytics-insights' ) . ".</p></div>";
				} else {
					$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You can only use the access code <strong>once</strong>, please generate a <strong>new access</strong> code following the instructions!", 'analytics-insights' ) . ".</p></div>";
				}
			}
		}
		if ( isset( $_REQUEST['Clear'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Cleared Cache.", 'analytics-insights' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Reset'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$aiwp->gapi_controller->reset_token();
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Token Reseted and Revoked.", 'analytics-insights' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Reset_Err'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {

				if ( AIWP_Tools::get_cache( 'gapi_errors' ) || AIWP_Tools::get_cache( 'last_error' ) ) {

					$info = AIWP_Tools::system_info();
					$info .= 'AIWP Version: ' . AIWP_CURRENT_VERSION;

					$sep = "\n---------------------------\n";
					$error_report = AIWP_Tools::get_cache( 'last_error' );
					$error_report .= $sep . print_r( AIWP_Tools::get_cache( 'gapi_errors' ), true );
					$error_report .= $sep . AIWP_Tools::get_cache( 'errors_count' );
					$error_report .= $sep . $info;

					$error_report = urldecode( $error_report );

					$url = AIWP_ENDPOINT_URL . 'aiwp-report.php';
					/* @formatter:off */
					$response = wp_remote_post( $url, array(
							'method' => 'POST',
							'timeout' => 45,
							'redirection' => 5,
							'httpversion' => '1.0',
							'blocking' => true,
							'headers' => array(),
							'body' => array( 'error_report' => $error_report ),
							'cookies' => array()
						)
					);
				}

				/* @formatter:on */
				AIWP_Tools::delete_cache( 'last_error' );
				AIWP_Tools::delete_cache( 'gapi_errors' );
				delete_option( 'aiwp_got_updated' );
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "All errors reseted.", 'analytics-insights' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) && ! isset( $_REQUEST['Clear'] ) && ! isset( $_REQUEST['Reset'] ) && ! isset( $_REQUEST['Reset_Err'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Hide'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$message = "<div class='updated' id='aiwp-action'><p>" . __( "All other domains/properties were removed.", 'analytics-insights' ) . "</p></div>";
				$lock_profile = AIWP_Tools::get_selected_profile( $aiwp->config->options['ga_profiles_list'], $aiwp->config->options['tableid_jail'] );
				$aiwp->config->options['ga_profiles_list'] = array( $lock_profile );
				$options = self::update_options( 'general' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		?>
	<div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Settings", 'analytics-insights' ) . "</h2>"; ?>
					<hr>
					</div>
					<div id="poststuff" class="aiwp">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content">
								<div class="settings-wrapper">
									<div class="inside">
										<?php if ( $aiwp->gapi_controller->gapi_errors_handler() || AIWP_Tools::get_cache( 'last_error' ) ) : ?>
													<?php $message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'analytics-insights' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_errors_debugging', false ), __( 'Errors & Debug', 'analytics-insights' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_settings', false ), __( 'authorize the plugin', 'analytics-insights' ) ) ) );?>
										<?php endif;?>
										<?php if ( isset( $message ) ) :?>
											<?php echo $message;?>
										<?php endif; ?>
										<form name="aiwp_form" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
											<input type="hidden" name="options[aiwp_hidden]" value="Y">
											<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>
											<table class="aiwp-settings-options">
												<tr>
													<td colspan="2">
														<?php echo "<h2>" . __( "Plugin Authorization", 'analytics-insights' ) . "</h2>";?>
													</td>
												</tr>
												<tr>
													<td colspan="2" class="aiwp-settings-info">
														<?php printf(__('You need to create a %1$s and watch this %2$s before proceeding to authorization.', 'analytics-insights'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/creating-a-google-analytics-account/?utm_source=aiwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=aiwp', __("free analytics account", 'analytics-insights')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=aiwp_config&utm_medium=link&utm_content=top_video&utm_campaign=aiwp', __("video tutorial", 'analytics-insights')));?>
													</td>
												</tr>
												  <?php if (! $options['token'] || ($options['user_api']  && ! $options['network_mode'])) : ?>
												<tr>
													<td colspan="2" class="aiwp-settings-info">
														<input name="options[user_api]" type="checkbox" id="user_api" value="1" <?php checked( $options['user_api'], 1 ); ?> onchange="this.form.submit()" <?php echo ($options['network_mode'])?'disabled="disabled"':''; ?> /><?php echo " ".__("developer mode (requires advanced API knowledge)", 'analytics-insights' );?>
													</td>
												</tr>
												  <?php endif; ?>
												  <?php if ($options['user_api']  && ! $options['network_mode']) : ?>
												<tr>
													<td class="aiwp-settings-title">
														<label for="options[client_id]"><?php _e("Client ID:", 'analytics-insights'); ?></label>
													</td>
													<td>
														<input type="text" name="options[client_id]" value="<?php echo esc_attr($options['client_id']); ?>" size="40" required="required">
													</td>
												</tr>
												<tr>
													<td class="aiwp-settings-title">
														<label for="options[client_secret]"><?php _e("Client Secret:", 'analytics-insights'); ?></label>
													</td>
													<td>
														<input type="text" name="options[client_secret]" value="<?php echo esc_attr($options['client_secret']); ?>" size="40" required="required">
														<input type="hidden" name="options[aiwp_hidden]" value="Y">
														<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>
													</td>
												</tr>
												  <?php endif; ?>
												  <?php if ( $options['token'] ) : ?>
												<tr>
													<td colspan="2">
														<input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Clear Authorization", 'analytics-insights' ); ?>" <?php echo $options['network_mode']?'disabled="disabled"':''; ?> />
														<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'analytics-insights' ); ?>" />
														<input type="submit" name="Reset_Err" class="button button-secondary" value="<?php _e( "Report & Reset Errors", 'analytics-insights' ); ?>" />
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr>
													</td>
												</tr>
												<tr>
													<td colspan="2"><?php echo "<h2>" . __( "General Settings", 'analytics-insights' ) . "</h2>"; ?></td>
												</tr>
												<tr>
													<td class="aiwp-settings-title">
														<label for="tableid_jail"><?php _e("Select View:", 'analytics-insights' ); ?></label>
													</td>
													<td>
														<select id="tableid_jail" <?php disabled(empty($options['ga_profiles_list']) || 1 == count($options['ga_profiles_list']), true); ?> name="options[tableid_jail]">
															<?php if ( ! empty( $options['ga_profiles_list'] ) ) : ?>
																	<?php foreach ( $options['ga_profiles_list'] as $items ) : ?>
																		<?php if ( $items[3] ) : ?>
																			<option value="<?php echo esc_attr( $items[1] ); ?>" <?php selected( $items[1], $options['tableid_jail'] ); ?> title="<?php _e( "View Name:", 'analytics-insights' ); ?> <?php echo esc_attr( $items[0] ); ?>">
																				<?php echo esc_html( AIWP_Tools::strip_protocol( $items[3] ) )?> &#8658; <?php echo esc_attr( $items[0] ); ?>
																			</option>
																		<?php endif; ?>
																	<?php endforeach; ?>
															<?php else : ?>
																	<option value=""><?php _e( "Property not found", 'analytics-insights' ); ?></option>
															<?php endif; ?>
														</select>
														<?php if ( count( $options['ga_profiles_list'] ) > 1 ) : ?>
														&nbsp;<input type="submit" name="Hide" class="button button-secondary" value="<?php _e( "Lock Selection", 'analytics-insights' ); ?>" />
														<?php endif; ?>
													 </td>
												</tr>
												<?php if ( $options['tableid_jail'] ) :	?>
												<tr>
													<td class="aiwp-settings-title"></td>
													<td>
													<?php $profile_info = AIWP_Tools::get_selected_profile( $aiwp->config->options['ga_profiles_list'], $aiwp->config->options['tableid_jail'] ); ?>
														<pre><?php echo __( "View Name:", 'analytics-insights' ) . "\t" . esc_html( $profile_info[0] ) . "<br />" . __( "Tracking ID:", 'analytics-insights' ) . "\t" . esc_html( $profile_info[2] ) . "<br />" . __( "Default URL:", 'analytics-insights' ) . "\t" . esc_html( $profile_info[3] ) . "<br />" . __( "Time Zone:", 'analytics-insights' ) . "\t" . esc_html( $profile_info[5] );?></pre>
													</td>
												</tr>
												<?php endif; ?>
												 <tr>
													<td class="aiwp-settings-title">
														<label for="theme_color"><?php _e("Theme Color:", 'analytics-insights' ); ?></label>
													</td>
													<td>
														<input type="text" id="theme_color" class="theme_color" name="options[theme_color]" value="<?php echo esc_attr($options['theme_color']); ?>" size="10">
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr>
													</td>
												</tr>
												<?php if ( !is_multisite()) :?>
												<tr>
													<td colspan="2"><?php echo "<h2>" . __( "Automatic Updates", 'analytics-insights' ) . "</h2>"; ?></td>
												</tr>
												<tr>
													<td colspan="2" class="aiwp-settings-title">
														<div class="button-primary aiwp-settings-switchoo">
															<input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="aiwp-settings-switchoo-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>>
															<label class="aiwp-settings-switchoo-label" for="automatic_updates_minorversion">
																<div class="aiwp-settings-switchoo-inner"></div>
																<div class="aiwp-settings-switchoo-switch"></div>
															</label>
														</div>
														<div class="switch-desc"><?php echo " ".__( "automatic updates for minor versions (security and maintenance releases only)", 'analytics-insights' );?></div>
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr>
													</td>
												</tr>
												<?php endif; ?>
												<tr>
													<td colspan="2" class="submit">
														<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
													</td>
												</tr>
												<?php else : ?>
												<tr>
													<td colspan="2">
														<hr>
													</td>
												</tr>
												<tr>
													<td colspan="2">
													 <?php $auth = $aiwp->gapi_controller->client->createAuthUrl();?>
														<input type="button" class="button button-secondary" onclick="location.href='<?php echo $auth; ?>'" <?php echo $options['network_mode']?'disabled="disabled"':''; ?> value="Authorize Plugin" />
														<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'analytics-insights' ); ?>" />
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr>
													</td>
												</tr>
											</table>
										</form>
				<?php self::output_sidebar(); ?>
				<?php return; ?>
			<?php endif; ?>
											</table>
										</form>
			<?php

		self::output_sidebar();
	}

	// Network Settings
	public static function general_settings_network() {
		$aiwp = AIWP();

		if ( ! current_user_can( 'manage_network_options' ) ) {
			return;
		}
		$options = self::update_options( 'network' );
		/*
		 * Include GAPI
		 */
		echo '<div id="gapi-warning" class="updated"><p>' . __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'analytics-insights' ) . ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=aiwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=aiwp">Library conflicts between WordPress plugins</a></p></div>';

		if ( null === $aiwp->gapi_controller ) {
			$aiwp->gapi_controller = new AIWP_GAPI_Controller();
		}

		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
		if ( isset( $_REQUEST['aiwp_access_code'] ) ) {
			if ( 1 == ! stripos( 'x' . $_REQUEST['aiwp_access_code'], 'UA-', 1 ) && $_REQUEST['aiwp_access_code'] != get_option( 'aiwp_redeemed_code' ) ) {
				try {
					$aiwp_access_code = $_REQUEST['aiwp_access_code'];
					update_option( 'aiwp_redeemed_code', $aiwp_access_code );
					$aiwp->gapi_controller->client->authenticate( $_REQUEST['aiwp_access_code'] );
					$aiwp->config->options['token'] = $aiwp->gapi_controller->client->getAccessToken();
					$aiwp->config->options['automatic_updates_minorversion'] = 1;
					$aiwp->config->set_plugin_options( true );
					$options = self::update_options( 'network' );
					$message = "<div class='updated' id='aiwp-action'><p>" . __( "Plugin authorization succeeded.", 'analytics-insights' ) . "</p></div>";
					if ( is_multisite() ) { // Cleanup errors on the entire network
						foreach ( AIWP_Tools::get_sites( array( 'number' => apply_filters( 'aiwp_sites_limit', 100 ) ) ) as $blog ) {
							switch_to_blog( $blog['blog_id'] );
							AIWP_Tools::delete_cache( 'last_error' );
							AIWP_Tools::delete_cache( 'gapi_errors' );
							restore_current_blog();
						}
					} else {
						AIWP_Tools::delete_cache( 'last_error' );
						AIWP_Tools::delete_cache( 'gapi_errors' );
					}
					if ( $aiwp->config->options['token'] && $aiwp->gapi_controller->client->getAccessToken() ) {
						$profiles = $aiwp->gapi_controller->refresh_profiles();
						if ( is_array ( $profiles ) && ! empty( $profiles ) ) {
							$aiwp->config->options['ga_profiles_list'] = $profiles;
							if ( isset( $aiwp->config->options['tableid_jail'] ) && ! $aiwp->config->options['tableid_jail'] ) {
								$profile = AIWP_Tools::guess_default_domain( $profiles );
								$aiwp->config->options['tableid_jail'] = $profile;
							}
							$aiwp->config->set_plugin_options( true );
							$options = self::update_options( 'network' );
						}
					}
				} catch ( Deconf_IO_Exception $e ) {
					$timeout = $aiwp->gapi_controller->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} catch ( Deconf_Service_Exception $e ) {
					$timeout = $aiwp->gapi_controller->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
				} catch ( Exception $e ) {
					$timeout = $aiwp->gapi_controller->get_timeouts( 'midnight' );
					AIWP_Tools::set_error( $e, $timeout );
					$aiwp->gapi_controller->reset_token();
				}
			} else {
				if ( 1 == stripos( 'x' . $_REQUEST['aiwp_access_code'], 'UA-', 1 ) ) {
					$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "The access code is <strong>not</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X) <strong>nor</strong> your <strong>email address</strong>!", 'analytics-insights' ) . ".</p></div>";
				} else {
					$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You can only use the access code <strong>once</strong>, please generate a <strong>new access code</strong> using the red link", 'analytics-insights' ) . "!</p></div>";
				}
			}
		}
		if ( isset( $_REQUEST['Refresh'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$aiwp->config->options['ga_profiles_list'] = array();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Properties refreshed.", 'analytics-insights' ) . "</p></div>";
				$options = self::update_options( 'network' );
				if ( $aiwp->config->options['token'] && $aiwp->gapi_controller->client->getAccessToken() ) {
					if ( ! empty( $aiwp->config->options['ga_profiles_list'] ) ) {
						$profiles = $aiwp->config->options['ga_profiles_list'];
					} else {
						$profiles = $aiwp->gapi_controller->refresh_profiles();
					}
					if ( $profiles ) {
						$aiwp->config->options['ga_profiles_list'] = $profiles;
						if ( isset( $aiwp->config->options['tableid_jail'] ) && ! $aiwp->config->options['tableid_jail'] ) {
							$profile = AIWP_Tools::guess_default_domain( $profiles );
							$aiwp->config->options['tableid_jail'] = $profile;
						}
						$aiwp->config->set_plugin_options( true );
						$options = self::update_options( 'network' );
					}
				}
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Clear'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Cleared Cache.", 'analytics-insights' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Reset'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$aiwp->gapi_controller->reset_token();
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Token Reseted and Revoked.", 'analytics-insights' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) && ! isset( $_REQUEST['Clear'] ) && ! isset( $_REQUEST['Reset'] ) && ! isset( $_REQUEST['Refresh'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Hide'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "All other domains/properties were removed.", 'analytics-insights' ) . "</p></div>";
				$lock_profile = AIWP_Tools::get_selected_profile( $aiwp->config->options['ga_profiles_list'], $aiwp->config->options['tableid_jail'] );
				$aiwp->config->options['ga_profiles_list'] = array( $lock_profile );
				$options = self::update_options( 'network' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "Cheating Huh?", 'analytics-insights' ) . "</p></div>";
			}
		}
		?>
<div class="wrap">
											<h2><?php _e( "Google Analytics Settings", 'analytics-insights' );?></h2>
											<hr>
										</div>
										<div id="poststuff" class="aiwp">
											<div id="post-body" class="metabox-holder columns-2">
												<div id="post-body-content">
													<div class="settings-wrapper">
														<div class="inside">
					<?php if ( $aiwp->gapi_controller->gapi_errors_handler() || AIWP_Tools::get_cache( 'last_error' ) ) : ?>
						<?php $message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'analytics-insights' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_errors_debugging', false ), __( 'Errors & Debug', 'analytics-insights' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'aiwp_settings', false ), __( 'authorize the plugin', 'analytics-insights' ) ) ) );?>
					<?php endif; ?>
					<?php if ( isset( $message ) ) : ?>
							<?php echo $message; ?>
					<?php endif; ?>
					<form name="aiwp_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
																<input type="hidden" name="options[aiwp_hidden]" value="Y">
						<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>
						<table class="aiwp-settings-options">
																	<tr>
																		<td colspan="2">
								<?php echo "<h2>" . __( "Network Setup", 'analytics-insights' ) . "</h2>"; ?>
								</td>
																	</tr>
																	<tr>
																		<td colspan="2" class="aiwp-settings-title">
																			<div class="button-primary aiwp-settings-switchoo">
																				<input type="checkbox" name="options[network_mode]" value="1" class="aiwp-settings-switchoo-checkbox" id="network_mode" <?php checked( $options['network_mode'], 1); ?> onchange="this.form.submit()">
																				<label class="aiwp-settings-switchoo-label" for="network_mode">
																					<div class="aiwp-settings-switchoo-inner"></div>
																					<div class="aiwp-settings-switchoo-switch"></div>
																				</label>
																			</div>
																			<div class="switch-desc"><?php echo " ".__("use a single Google Analytics account for the entire network", 'analytics-insights' );?></div>
																		</td>
																	</tr>
							<?php if ($options['network_mode']) : ?>
							<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2"><?php echo "<h2>" . __( "Plugin Authorization", 'analytics-insights' ) . "</h2>"; ?></td>
																	</tr>
																	<tr>
																		<td colspan="2" class="aiwp-settings-info">
								<?php printf(__('You need to create a %1$s and watch this %2$s before proceeding to authorization.', 'analytics-insights'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/creating-a-google-analytics-account/?utm_source=aiwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=aiwp', __("free analytics account", 'analytics-insights')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=aiwp_config&utm_medium=link&utm_content=top_video&utm_campaign=aiwp', __("video tutorial", 'analytics-insights')));?>
								</td>
																	</tr>
								<?php if ( ! $options['token'] || $options['user_api'] ) : ?>
								<tr>
																		<td colspan="2" class="aiwp-settings-info">
																			<input name="options[user_api]" type="checkbox" id="user_api" value="1" <?php checked( $options['user_api'], 1 ); ?> onchange="this.form.submit()" /><?php echo " ".__("developer mode (requires advanced API knowledge)", 'analytics-insights' );?>
								</td>
																	</tr>
								<?php endif; ?>
							<?php if ( $options['user_api'] ) : ?>
							<tr>
																		<td class="aiwp-settings-title">
																			<label for="options[client_id]"><?php _e("Client ID:", 'analytics-insights'); ?>
									</label>
																		</td>
																		<td>
																			<input type="text" name="options[client_id]" value="<?php echo esc_attr($options['client_id']); ?>" size="40" required="required">
																		</td>
																	</tr>
																	<tr>
																		<td class="aiwp-settings-title">
																			<label for="options[client_secret]"><?php _e("Client Secret:", 'analytics-insights'); ?>
									</label>
																		</td>
																		<td>
																			<input type="text" name="options[client_secret]" value="<?php echo esc_attr($options['client_secret']); ?>" size="40" required="required">
																			<input type="hidden" name="options[aiwp_hidden]" value="Y">
																			<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>
								</td>
																	</tr>
							<?php endif; ?>
							<?php if ( $options['token'] ) : ?>
							<tr>
																		<td colspan="2">
																			<input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Clear Authorization", 'analytics-insights' ); ?>" />
																			<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'analytics-insights' ); ?>" />
																			<input type="submit" name="Refresh" class="button button-secondary" value="<?php _e( "Refresh Properties", 'analytics-insights' ); ?>" />
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
								<?php echo "<h2>" . __( "Properties/Views Settings", 'analytics-insights' ) . "</h2>"; ?>
								</td>
																	</tr>
							<?php if ( isset( $options['network_tableid'] ) ) : ?>
								<?php $options['network_tableid'] = json_decode( json_encode( $options['network_tableid'] ), false ); ?>
							<?php endif; ?>
							<?php foreach ( AIWP_Tools::get_sites( array( 'number' => apply_filters( 'aiwp_sites_limit', 100 ) ) ) as $blog ) : ?>
							<tr>
																		<td class="aiwp-settings-title-s">
																			<label for="network_tableid"><?php echo '<strong>'.$blog['domain'].$blog['path'].'</strong>: ';?></label>
																		</td>
																		<td>
																			<select id="network_tableid" <?php disabled(!empty($options['ga_profiles_list']),false);?> name="options[network_tableid][<?php echo $blog['blog_id'];?>]">
									<?php if ( ! empty( $options['ga_profiles_list'] ) ) : ?>
										<?php foreach ( $options['ga_profiles_list'] as $items ) : ?>
											<?php if ( $items[3] ) : ?>
												<?php $temp_id = $blog['blog_id']; ?>
												<option value="<?php echo esc_attr( $items[1] );?>" <?php selected( $items[1], isset( $options['network_tableid']->$temp_id ) ? $options['network_tableid']->$temp_id : '');?> title="<?php echo __( "View Name:", 'analytics-insights' ) . ' ' . esc_attr( $items[0] );?>">
													 <?php echo esc_html( AIWP_Tools::strip_protocol( $items[3] ) );?> &#8658; <?php echo esc_attr( $items[0] );?>
												</option>
											<?php endif; ?>
										<?php endforeach; ?>
									<?php else : ?>
												<option value="">
													<?php _e( "Property not found", 'analytics-insights' );?>
												</option>
									<?php endif; ?>
									</select>
																			<br />
																		</td>
																	</tr>
							<?php endforeach; ?>
							<tr>
																		<td colspan="2">
																			<h2><?php echo _e( "Automatic Updates", 'analytics-insights' );?></h2>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2" class="aiwp-settings-title">
																			<div class="button-primary aiwp-settings-switchoo">
																				<input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="aiwp-settings-switchoo-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>>
																				<label class="aiwp-settings-switchoo-label" for="automatic_updates_minorversion">
																					<div class="aiwp-settings-switchoo-inner"></div>
																					<div class="aiwp-settings-switchoo-switch"></div>
																				</label>
																			</div>
																			<div class="switch-desc"><?php echo " ".__( "automatic updates for minor versions (security and maintenance releases only)", 'analytics-insights' );?></div>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
																			<hr><?php echo "<h2>" . __( "Exclude Tracking", 'analytics-insights' ) . "</h2>"; ?></td>
																	</tr>
																	<tr>
																		<td colspan="2" class="aiwp-settings-title">
																			<div class="button-primary aiwp-settings-switchoo">
																				<input type="checkbox" name="options[superadmin_tracking]" value="1" class="aiwp-settings-switchoo-checkbox" id="superadmin_tracking"<?php checked( $options['superadmin_tracking'], 1); ?>">
																				<label class="aiwp-settings-switchoo-label" for="superadmin_tracking">
																					<div class="aiwp-settings-switchoo-inner"></div>
																					<div class="aiwp-settings-switchoo-switch"></div>
																				</label>
																			</div>
																			<div class="switch-desc"><?php echo " ".__("exclude Super Admin tracking for the entire network", 'analytics-insights' );?></div>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2" class="submit">
																			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
																		</td>
																	</tr>
							<?php else : ?>
							<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
																			<?php $auth = $aiwp->gapi_controller->client->createAuthUrl();?>
																			<input type="button" class="button button-secondary" onclick="location.href='<?php echo $auth; ?>'" value="Authorize Plugin" />
																			<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'analytics-insights' ); ?>" />
																		</td>
																	</tr>
							<?php endif; ?>
							<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																</table>
															</form>
		<?php self::output_sidebar(); ?>
				<?php return; ?>
			<?php endif;?>
						</table>
															</form>
		<?php

		self::output_sidebar();
	}

	public static function output_sidebar() {
		global $wp_version;

		$aiwp = AIWP();
		?>
				</div>
													</div>
												</div>
												<div id="postbox-container-1" class="postbox-container">
													<div class="meta-box-sortables">
														<div class="postbox">
															<h3>
																<span><?php _e("Setup Tutorial & Demo",'analytics-insights') ?></span>
															</h3>
															<div class="inside">
																<a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=aiwp_config&utm_medium=link&utm_content=video&utm_campaign=aiwp" target="_blank"><img src="<?php echo plugins_url( 'images/google-analytics-dashboard.png' , __FILE__ );?>" width="100%" alt="" /></a>
															</div>
														</div>
														<div class="postbox">
															<h3>
																<span><?php _e("Stay Updated",'analytics-insights')?></span>
															</h3>
															<div class="inside">
																<div class="aiwp-desc">
																	<div class="g-ytsubscribe" data-channel="TheDeConf" data-layout="default" data-count="default"></div>
																</div>
																<br />
																<div class="aiwp-desc">
																	<div class="g-follow" data-annotation="bubble" data-height="24" data-href="//plus.google.com/u/0/114149166432576972465" data-rel="publisher"></div>
																	<script src="https://apis.google.com/js/platform.js" async defer></script>
																</div>
																<br />
																<div class="aiwp-desc">
																	<a href="https://twitter.com/deconfcom" class="twitter-follow-button" data-show-screen-name="false"></a>
																	<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
																</div>
															</div>
														</div>
														<div class="postbox">
															<h3>
																<span><?php _e("Further Reading",'analytics-insights')?></span>
															</h3>
															<div class="inside">
																<div class="aiwp-title">
																	<a href="https://deconf.com/clicky-web-analytics-review/?utm_source=aiwp_config&utm_medium=link&utm_content=clicky&utm_campaign=aiwp"><img src="<?php echo plugins_url( 'images/clicky.png' , __FILE__ ); ?>" /></a>
																</div>
																<div class="aiwp-desc">
																	<?php printf(__('%s service with users tracking at IP level.', 'analytics-insights'), sprintf('<a href="https://deconf.com/clicky-web-analytics-review/?utm_source=aiwp_config&utm_medium=link&utm_content=clicky&utm_campaign=aiwp">%s</a>', __('Web Analytics', 'analytics-insights')));?>
																</div>
																<br />
																<div class="aiwp-title">
																	<a href="https://deconf.com/move-website-https-ssl/?utm_source=aiwp_config&utm_medium=link&utm_content=ssl&utm_campaign=aiwp"><img src="<?php echo plugins_url( 'images/ssl.png' , __FILE__ ); ?>" /></a>
																</div>
																<div class="aiwp-desc">
																	<?php printf(__('%s by moving your website to HTTPS/SSL.', 'analytics-insights'), sprintf('<a href="https://deconf.com/move-website-https-ssl/?utm_source=aiwp_config&utm_medium=link&utm_content=ssl&utm_campaign=aiwp">%s</a>', __('Improve search rankings', 'analytics-insights')));?>
																</div>
																<br />
																<div class="aiwp-title">
																	<a href="http://wordpress.org/support/view/plugin-reviews/analytics-insights#plugin-info"><img src="<?php echo plugins_url( 'images/star.png' , __FILE__ ); ?>" /></a>
																</div>
																<div class="aiwp-desc">
																	<?php printf(__('Your feedback and review are both important, %s!', 'analytics-insights'), sprintf('<a href="http://wordpress.org/support/view/plugin-reviews/analytics-insights#plugin-info">%s</a>', __('rate this plugin', 'analytics-insights')));?>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
<?php
		// Dismiss the admin update notice
		if ( version_compare( $wp_version, '4.2', '<' ) && current_user_can( 'manage_options' ) ) {
			delete_option( 'aiwp_got_updated' );
		}
	}
}
