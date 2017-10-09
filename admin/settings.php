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

final class GADWP_Settings {

	private static function update_options( $who ) {
		$gadwp = GADWP();
		$network_settings = false;
		$options = $gadwp->config->options; // Get current options
		if ( isset( $_POST['options']['ga_dash_hidden'] ) && isset( $_POST['options'] ) && ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) && 'Reset' != $who ) {
			$new_options = $_POST['options'];
			if ( 'tracking' == $who ) {
				$options['ga_dash_anonim'] = 0;
				$options['ga_event_tracking'] = 0;
				$options['ga_enhanced_links'] = 0;
				$options['ga_event_precision'] = 0;
				$options['ga_dash_remarketing'] = 0;
				$options['ga_event_bouncerate'] = 0;
				$options['ga_crossdomain_tracking'] = 0;
				$options['ga_aff_tracking'] = 0;
				$options['ga_hash_tracking'] = 0;
				$options['ga_formsubmit_tracking'] = 0;
				$options['ga_force_ssl'] = 0;
				$options['ga_pagescrolldepth_tracking'] = 0;
				$options['tm_pagescrolldepth_tracking'] = 0;
				$options['amp_tracking_analytics'] = 0;
				$options['amp_tracking_clientidapi'] = 0;
				$options['amp_tracking_tagmanager'] = 0;
				$options['optimize_pagehiding'] = 0;
				$options['optimize_tracking'] = 0;
				$options['trackingcode_infooter'] = 0;
				$options['trackingevents_infooter'] = 0;
				if ( isset( $_POST['options']['ga_tracking_code'] ) ) {
					$new_options['ga_tracking_code'] = trim( $new_options['ga_tracking_code'], "\t" );
				}
				if ( empty( $new_options['ga_track_exclude'] ) ) {
					$new_options['ga_track_exclude'] = array();
				}
			} elseif ( 'backend' == $who ) {
				$options['switch_profile'] = 0;
				$options['backend_item_reports'] = 0;
				$options['dashboard_widget'] = 0;
				if ( empty( $new_options['ga_dash_access_back'] ) ) {
					$new_options['ga_dash_access_back'][] = 'administrator';
				}
			} elseif ( 'frontend' == $who ) {
				$options['frontend_item_reports'] = 0;
				if ( empty( $new_options['ga_dash_access_front'] ) ) {
					$new_options['ga_dash_access_front'][] = 'administrator';
				}
			} elseif ( 'general' == $who ) {
				$options['ga_dash_userapi'] = 0;
				if ( ! is_multisite() ) {
					$options['automatic_updates_minorversion'] = 0;
				}
			} elseif ( 'network' == $who ) {
				$options['ga_dash_userapi'] = 0;
				$options['ga_dash_network'] = 0;
				$options['ga_dash_excludesa'] = 0;
				$options['automatic_updates_minorversion'] = 0;
				$network_settings = true;
			}
			$options = array_merge( $options, $new_options );
			$gadwp->config->options = $options;
			$gadwp->config->set_plugin_options( $network_settings );
		}
		return $options;
	}

	private static function navigation_tabs( $tabs ) {
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a class='nav-tab' id='tab-$tab' href='#top#gadwp-$tab'>$name</a>";
		}
		echo '</h2>';
	}

	public static function frontend_settings() {
		$gadwp = GADWP();
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'frontend' );
		if ( isset( $_POST['options']['ga_dash_hidden'] ) ) {
			$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Settings saved.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'google-analytics-dashboard-for-wp' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'google-analytics-dashboard-for-wp' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'google-analytics-dashboard-for-wp' ) ) ) );
		}
		?>
<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
	<div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Frontend Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff" class="gadwp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="gadwp-settings-options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Permissions", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles gadwp-settings-title">
									<label for="ga_dash_access_front"><?php _e("Show stats to:", 'google-analytics-dashboard-for-wp' ); ?>
									</label>
								</td>
								<td class="gadwp-settings-roles">
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
													<input type="checkbox" name="options[ga_dash_access_front][]" value="<?php echo $role; ?>" <?php if ( in_array($role,$options['ga_dash_access_front']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /><?php echo $name; ?>
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
								<td colspan="2" class="gadwp-settings-title">
									<div class="button-primary gadwp-settings-switchoo">
										<input type="checkbox" name="options[frontend_item_reports]" value="1" class="gadwp-settings-switchoo-checkbox" id="frontend_item_reports" <?php checked( $options['frontend_item_reports'], 1 ); ?>>
										<label class="gadwp-settings-switchoo-label" for="frontend_item_reports">
											<div class="gadwp-settings-switchoo-inner"></div>
											<div class="gadwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php echo " ".__("enable web page reports on frontend", 'google-analytics-dashboard-for-wp' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="submit">
									<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'google-analytics-dashboard-for-wp' ) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security');?>
</form>
<?php
		self::output_sidebar();
	}

	public static function backend_settings() {
		$gadwp = GADWP();
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'backend' );
		if ( isset( $_POST['options']['ga_dash_hidden'] ) ) {
			$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Settings saved.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'google-analytics-dashboard-for-wp' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'google-analytics-dashboard-for-wp' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'google-analytics-dashboard-for-wp' ) ) ) );
		}
		?>
<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
	<div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Backend Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff" class="gadwp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="gadwp-settings-options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Permissions", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles gadwp-settings-title">
									<label for="ga_dash_access_back"><?php _e("Show stats to:", 'google-analytics-dashboard-for-wp' ); ?>
									</label>
								</td>
								<td class="gadwp-settings-roles">
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
													<input type="checkbox" name="options[ga_dash_access_back][]" value="<?php echo $role; ?>" <?php if ( in_array($role,$options['ga_dash_access_back']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /> <?php echo $name; ?>
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
								<td colspan="2" class="gadwp-settings-title">
									<div class="button-primary gadwp-settings-switchoo">
										<input type="checkbox" name="options[switch_profile]" value="1" class="gadwp-settings-switchoo-checkbox" id="switch_profile" <?php checked( $options['switch_profile'], 1 ); ?>>
										<label class="gadwp-settings-switchoo-label" for="switch_profile">
											<div class="gadwp-settings-switchoo-inner"></div>
											<div class="gadwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "enable Switch View functionality", 'google-analytics-dashboard-for-wp' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="gadwp-settings-title">
									<div class="button-primary gadwp-settings-switchoo">
										<input type="checkbox" name="options[backend_item_reports]" value="1" class="gadwp-settings-switchoo-checkbox" id="backend_item_reports" <?php checked( $options['backend_item_reports'], 1 ); ?>>
										<label class="gadwp-settings-switchoo-label" for="backend_item_reports">
											<div class="gadwp-settings-switchoo-inner"></div>
											<div class="gadwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "enable reports on Posts List and Pages List", 'google-analytics-dashboard-for-wp' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="gadwp-settings-title">
									<div class="button-primary gadwp-settings-switchoo">
										<input type="checkbox" name="options[dashboard_widget]" value="1" class="gadwp-settings-switchoo-checkbox" id="dashboard_widget" <?php checked( $options['dashboard_widget'], 1 ); ?>>
										<label class="gadwp-settings-switchoo-label" for="dashboard_widget">
											<div class="gadwp-settings-switchoo-inner"></div>
											<div class="gadwp-settings-switchoo-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "enable the main Dashboard Widget", 'google-analytics-dashboard-for-wp' );?></div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "Real-Time Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="gadwp-settings-title"> <?php _e("Maximum number of pages to display on real-time tab:", 'google-analytics-dashboard-for-wp'); ?>
									<input type="number" name="options[ga_realtime_pages]" id="ga_realtime_pages" value="<?php echo (int)$options['ga_realtime_pages']; ?>" size="3">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "Location Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="gadwp-settings-title">
									<?php echo __("Target Geo Map to country:", 'google-analytics-dashboard-for-wp'); ?>
									<input type="text" style="text-align: center;" name="options[ga_target_geomap]" value="<?php echo esc_attr($options['ga_target_geomap']); ?>" size="3">
								</td>
							</tr>
							<tr>
								<td colspan="2" class="gadwp-settings-title">
									<?php echo __("Maps API Key:", 'google-analytics-dashboard-for-wp'); ?>
									<input type="text" style="text-align: center;" name="options[maps_api_key]" value="<?php echo esc_attr($options['maps_api_key']); ?>" size="50">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "404 Errors Report", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="gadwp-settings-title">
									<?php echo __("404 Page Title contains:", 'google-analytics-dashboard-for-wp'); ?>
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
									<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'google-analytics-dashboard-for-wp' ) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>
</form>
<?php
		self::output_sidebar();
	}

	public static function tracking_settings() {
		$gadwp = GADWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'tracking' );
		if ( isset( $_POST['options']['ga_dash_hidden'] ) ) {
			$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Settings saved.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'google-analytics-dashboard-for-wp' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'google-analytics-dashboard-for-wp' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'google-analytics-dashboard-for-wp' ) ) ) );
		}
		?>
<form name="ga_dash_form" method="post" action="<?php  esc_url($_SERVER['REQUEST_URI']); ?>">
	<div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Tracking Code", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?>
	</div>
	<div id="poststuff" class="gadwp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
						<?php if ( 'universal' == $options['ga_dash_tracking_type'] ) :?>
						<?php $tabs = array( 'basic' => __( "Basic Settings", 'google-analytics-dashboard-for-wp' ), 'events' => __( "Events Tracking", 'google-analytics-dashboard-for-wp' ), 'custom' => __( "Custom Definitions", 'google-analytics-dashboard-for-wp' ), 'exclude' => __( "Exclude Tracking", 'google-analytics-dashboard-for-wp' ), 'advanced' => __( "Advanced Settings", 'google-analytics-dashboard-for-wp' ), 'integration' => __( "Integration", 'google-analytics-dashboard-for-wp' ) );?>
						<?php elseif ( 'tagmanager' == $options['ga_dash_tracking_type'] ) :?>
						<?php $tabs = array( 'basic' => __( "Basic Settings", 'google-analytics-dashboard-for-wp' ), 'tmdatalayervars' => __( "DataLayer Variables", 'google-analytics-dashboard-for-wp' ), 'exclude' => __( "Exclude Tracking", 'google-analytics-dashboard-for-wp' ), 'tmintegration' => __( "Integration", 'google-analytics-dashboard-for-wp' ) );?>
						<?php else :?>
						<?php $tabs = array( 'basic' => __( "Basic Settings", 'google-analytics-dashboard-for-wp' ) );?>
						<?php endif; ?>
						<?php self::navigation_tabs( $tabs ); ?>
						<?php if ( isset( $message ) ) : ?>
							<?php echo $message; ?>
						<?php endif; ?>
						<div id="gadwp-basic">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Tracking Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_dash_tracking_type"><?php _e("Tracking Type:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="ga_dash_tracking_type" name="options[ga_dash_tracking_type]" onchange="this.form.submit()">
											<option value="universal" <?php selected( $options['ga_dash_tracking_type'], 'universal' ); ?>><?php _e("Analytics", 'google-analytics-dashboard-for-wp');?></option>
											<option value="tagmanager" <?php selected( $options['ga_dash_tracking_type'], 'tagmanager' ); ?>><?php _e("Tag Manager", 'google-analytics-dashboard-for-wp');?></option>
											<option value="disabled" <?php selected( $options['ga_dash_tracking_type'], 'disabled' ); ?>><?php _e("Disabled", 'google-analytics-dashboard-for-wp');?></option>
										</select>
									</td>
								</tr>
								<?php if ( 'universal' == $options['ga_dash_tracking_type'] ) : ?>
								<tr>
									<td class="gadwp-settings-title"></td>
									<td>
										<?php $profile_info = GADWP_Tools::get_selected_profile($gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail']); ?>
										<?php echo '<pre>' . __("View Name:", 'google-analytics-dashboard-for-wp') . "\t" . esc_html($profile_info[0]) . "<br />" . __("Tracking ID:", 'google-analytics-dashboard-for-wp') . "\t" . esc_html($profile_info[2]) . "<br />" . __("Default URL:", 'google-analytics-dashboard-for-wp') . "\t" . esc_html($profile_info[3]) . "<br />" . __("Time Zone:", 'google-analytics-dashboard-for-wp') . "\t" . esc_html($profile_info[5]) . '</pre>';?>
									</td>
								</tr>
								<?php elseif ( 'tagmanager' == $options['ga_dash_tracking_type'] ) : ?>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_dash_tracking_type"><?php _e("Web Container ID:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="options[web_containerid]" value="<?php echo esc_attr($options['web_containerid']); ?>" size="15">
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td class="gadwp-settings-title">
										<label for="trackingcode_infooter"><?php _e("Code Placement:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="trackingcode_infooter" name="options[trackingcode_infooter]">
											<option value="0" <?php selected( $options['trackingcode_infooter'], 0 ); ?>><?php _e("HTML Head", 'google-analytics-dashboard-for-wp');?></option>
											<option value="1" <?php selected( $options['trackingcode_infooter'], 1 ); ?>><?php _e("HTML Body", 'google-analytics-dashboard-for-wp');?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>
						<div id="gadwp-events">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Events Tracking", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_event_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_event_tracking" <?php checked( $options['ga_event_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_event_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track downloads, mailto, telephone and outbound links", 'google-analytics-dashboard-for-wp' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_aff_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_aff_tracking" <?php checked( $options['ga_aff_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_aff_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track affiliate links", 'google-analytics-dashboard-for-wp' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_hash_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_hash_tracking" <?php checked( $options['ga_hash_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_hash_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track fragment identifiers, hashmarks (#) in URI links", 'google-analytics-dashboard-for-wp' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_formsubmit_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_formsubmit_tracking" <?php checked( $options['ga_formsubmit_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_formsubmit_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track form submit actions", 'google-analytics-dashboard-for-wp' ); ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_pagescrolldepth_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_pagescrolldepth_tracking" <?php checked( $options['ga_pagescrolldepth_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_pagescrolldepth_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("track page scrolling depth", 'google-analytics-dashboard-for-wp' ); ?></div>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_event_downloads"><?php _e("Downloads Regex:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_event_downloads" name="options[ga_event_downloads]" value="<?php echo esc_attr($options['ga_event_downloads']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_event_affiliates"><?php _e("Affiliates Regex:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_event_affiliates" name="options[ga_event_affiliates]" value="<?php echo esc_attr($options['ga_event_affiliates']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="trackingevents_infooter"><?php _e("Code Placement:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="trackingevents_infooter" name="options[trackingevents_infooter]">
											<option value="0" <?php selected( $options['trackingevents_infooter'], 0 ); ?>><?php _e("HTML Head", 'google-analytics-dashboard-for-wp');?></option>
											<option value="1" <?php selected( $options['trackingevents_infooter'], 1 ); ?>><?php _e("HTML Body", 'google-analytics-dashboard-for-wp');?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>
						<div id="gadwp-custom">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Custom Dimensions", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_author_dimindex"><?php _e("Authors:", 'google-analytics-dashboard-for-wp' ); ?>
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
									<td class="gadwp-settings-title">
										<label for="ga_pubyear_dimindex"><?php _e("Publication Year:", 'google-analytics-dashboard-for-wp' ); ?>
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
									<td class="gadwp-settings-title">
										<label for="ga_pubyearmonth_dimindex"><?php _e("Publication Month:", 'google-analytics-dashboard-for-wp' ); ?>
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
									<td class="gadwp-settings-title">
										<label for="ga_category_dimindex"><?php _e("Categories:", 'google-analytics-dashboard-for-wp' ); ?>
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
									<td class="gadwp-settings-title">
										<label for="ga_user_dimindex"><?php _e("User Type:", 'google-analytics-dashboard-for-wp' ); ?>
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
									<td class="gadwp-settings-title">
										<label for="ga_tag_dimindex"><?php _e("Tags:", 'google-analytics-dashboard-for-wp' ); ?>
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
						<div id="gadwp-tmdatalayervars">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Main Variables", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="tm_author_var"><?php _e("Authors:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_author_var" name="options[tm_author_var]">
											<option value="1" <?php selected( $options['tm_author_var'], 1 ); ?>>gadwpAuthor</option>
											<option value="0" <?php selected( $options['tm_author_var'], 0 ); ?>><?php _e( "Disabled", 'google-analytics-dashboard-for-wp' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="tm_pubyear_var"><?php _e("Publication Year:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_pubyear_var" name="options[tm_pubyear_var]">
											<option value="1" <?php selected( $options['tm_pubyear_var'], 1 ); ?>>gadwpPublicationYear</option>
											<option value="0" <?php selected( $options['tm_pubyear_var'], 0 ); ?>><?php _e( "Disabled", 'google-analytics-dashboard-for-wp' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="tm_pubyearmonth_var"><?php _e("Publication Month:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_pubyearmonth_var" name="options[tm_pubyearmonth_var]">
											<option value="1" <?php selected( $options['tm_pubyearmonth_var'], 1 ); ?>>gadwpPublicationYearMonth</option>
											<option value="0" <?php selected( $options['tm_pubyearmonth_var'], 0 ); ?>><?php _e( "Disabled", 'google-analytics-dashboard-for-wp' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="tm_category_var"><?php _e("Categories:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_category_var" name="options[tm_category_var]">
											<option value="1" <?php selected( $options['tm_category_var'], 1 ); ?>>gadwpCategory</option>
											<option value="0" <?php selected( $options['tm_category_var'], 0 ); ?>><?php _e( "Disabled", 'google-analytics-dashboard-for-wp' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="tm_user_var"><?php _e("User Type:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_user_var" name="options[tm_user_var]">
											<option value="1" <?php selected( $options['tm_user_var'], 1 ); ?>>gadwpUser</option>
											<option value="0" <?php selected( $options['tm_user_var'], 0 ); ?>><?php _e( "Disabled", 'google-analytics-dashboard-for-wp' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="tm_tag_var"><?php _e("Tags:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="tm_tag_var" name="options[tm_tag_var]">
											<option value="1" <?php selected( $options['tm_tag_var'], 1 ); ?>>gadwpTag</option>
											<option value="0" <?php selected( $options['tm_tag_var'], 0 ); ?>><?php _e( "Disabled", 'google-analytics-dashboard-for-wp' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Page Scrolling Depth Variables", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[tm_pagescrolldepth_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="tm_pagescrolldepth_tracking" <?php checked( $options['tm_pagescrolldepth_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="tm_pagescrolldepth_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable page scrolling depth variables: ", 'google-analytics-dashboard-for-wp' ) . "<strong>{{event}} = ScrollDistance, {{eventCategory}}, {{eventAction}}, {{eventLabel}}, {{eventValue}}, {{eventNonInteraction}}</strong>"?></div>
									</td>
								</tr>
							</table>
						</div>
						<div id="gadwp-advanced">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Advanced Tracking", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_speed_samplerate"><?php _e("Speed Sample Rate:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="number" id="ga_speed_samplerate" name="options[ga_speed_samplerate]" value="<?php echo (int)($options['ga_speed_samplerate']); ?>" max="100" min="1">
										%
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_user_samplerate"><?php _e("User Sample Rate:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="number" id="ga_user_samplerate" name="options[ga_user_samplerate]" value="<?php echo (int)($options['ga_user_samplerate']); ?>" max="100" min="1">
										%
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_dash_anonim]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_anonim" <?php checked( $options['ga_dash_anonim'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_dash_anonim">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("anonymize IPs while tracking", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_dash_remarketing]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_remarketing" <?php checked( $options['ga_dash_remarketing'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_dash_remarketing">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable remarketing, demographics and interests reports", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_event_bouncerate]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_event_bouncerate" <?php checked( $options['ga_event_bouncerate'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_event_bouncerate">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("exclude events from bounce-rate and time on page calculation", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_enhanced_links]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_enhanced_links" <?php checked( $options['ga_enhanced_links'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_enhanced_links">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable enhanced link attribution", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_event_precision]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_event_precision" <?php checked( $options['ga_event_precision'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_event_precision">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("use hitCallback to increase event tracking accuracy", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_force_ssl]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_force_ssl" <?php checked( $options['ga_force_ssl'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_force_ssl">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Force SSL", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Cross-domain Tracking", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[ga_crossdomain_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_crossdomain_tracking" <?php checked( $options['ga_crossdomain_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="ga_crossdomain_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable cross domain tracking", 'google-analytics-dashboard-for-wp' ); ?></div>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_crossdomain_list"><?php _e("Cross Domains:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_crossdomain_list" name="options[ga_crossdomain_list]" value="<?php echo esc_attr($options['ga_crossdomain_list']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Cookie Customization", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_cookiedomain"><?php _e("Cookie Domain:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_cookiedomain" name="options[ga_cookiedomain]" value="<?php echo esc_attr($options['ga_cookiedomain']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_cookiename"><?php _e("Cookie Name:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_cookiename" name="options[ga_cookiename]" value="<?php echo esc_attr($options['ga_cookiename']); ?>" size="50">
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_cookieexpires"><?php _e("Cookie Expires:", 'google-analytics-dashboard-for-wp'); ?>
										</label>
									</td>
									<td>
										<input type="text" id="ga_cookieexpires" name="options[ga_cookieexpires]" value="<?php echo esc_attr($options['ga_cookieexpires']); ?>" size="10">
										<?php _e("seconds", 'google-analytics-dashboard-for-wp' ); ?>
									</td>
								</tr>
							</table>
						</div>
						<div id="gadwp-integration">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Accelerated Mobile Pages (AMP)", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[amp_tracking_analytics]" value="1" class="gadwp-settings-switchoo-checkbox" id="amp_tracking_analytics" <?php checked( $options['amp_tracking_analytics'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="amp_tracking_analytics">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable tracking for Accelerated Mobile Pages (AMP)", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[amp_tracking_clientidapi]" value="1" class="gadwp-settings-switchoo-checkbox" id="amp_tracking_clientidapi" <?php checked( $options['amp_tracking_clientidapi'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="amp_tracking_clientidapi">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Google AMP Client Id API", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Ecommerce", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_dash_tracking_type"><?php _e("Ecommerce Tracking:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<select id="ecommerce_mode" name="options[ecommerce_mode]">
											<option value="disabled" <?php selected( $options['ecommerce_mode'], 'disabled' ); ?>><?php _e("Disabled", 'google-analytics-dashboard-for-wp');?></option>
											<option value="standard" <?php selected( $options['ecommerce_mode'], 'standard' ); ?>><?php _e("Ecommerce Plugin", 'google-analytics-dashboard-for-wp');?></option>
											<option value="enhanced" <?php selected( $options['ecommerce_mode'], 'enhanced' ); ?>><?php _e("Enhanced Ecommerce Plugin", 'google-analytics-dashboard-for-wp');?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Optimize", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[optimize_tracking]" value="1" class="gadwp-settings-switchoo-checkbox" id="optimize_tracking" <?php checked( $options['optimize_tracking'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="optimize_tracking">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Optimize tracking", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[optimize_pagehiding]" value="1" class="gadwp-settings-switchoo-checkbox" id="optimize_pagehiding" <?php checked( $options['optimize_pagehiding'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="optimize_pagehiding">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable Page Hiding support", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_dash_tracking_type"><?php _e("Container ID:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="options[optimize_containerid]" value="<?php echo esc_attr($options['optimize_containerid']); ?>" size="15">
									</td>
								</tr>
							</table>
						</div>
						<div id="gadwp-tmintegration">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Accelerated Mobile Pages (AMP)", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td colspan="2" class="gadwp-settings-title">
										<div class="button-primary gadwp-settings-switchoo">
											<input type="checkbox" name="options[amp_tracking_tagmanager]" value="1" class="gadwp-settings-switchoo-checkbox" id="amp_tracking_tagmanager" <?php checked( $options['amp_tracking_tagmanager'], 1 ); ?>>
											<label class="gadwp-settings-switchoo-label" for="amp_tracking_tagmanager">
												<div class="gadwp-settings-switchoo-inner"></div>
												<div class="gadwp-settings-switchoo-switch"></div>
											</label>
										</div>
										<div class="switch-desc"><?php echo " ".__("enable tracking for Accelerated Mobile Pages (AMP)", 'google-analytics-dashboard-for-wp' );?></div>
									</td>
								</tr>
								<tr>
									<td class="gadwp-settings-title">
										<label for="ga_dash_tracking_type"><?php _e("AMP Container ID:", 'google-analytics-dashboard-for-wp' ); ?>
										</label>
									</td>
									<td>
										<input type="text" name="options[amp_containerid]" value="<?php echo esc_attr($options['amp_containerid']); ?>" size="15">
									</td>
								</tr>
							</table>
						</div>
						<div id="gadwp-exclude">
							<table class="gadwp-settings-options">
								<tr>
									<td colspan="2"><?php echo "<h2>" . __( "Exclude Tracking", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
								</tr>
								<tr>
									<td class="roles gadwp-settings-title">
										<label for="ga_track_exclude"><?php _e("Exclude tracking for:", 'google-analytics-dashboard-for-wp' ); ?></label>
									</td>
									<td class="gadwp-settings-roles">
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
														<input type="checkbox" name="options[ga_track_exclude][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['ga_track_exclude'])) echo 'checked="checked"'; ?> /> <?php echo $name; ?>
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
						<table class="gadwp-settings-options">
							<tr>
								<td colspan="2">
									<hr>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="submit">
									<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'google-analytics-dashboard-for-wp' ) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>
</form>
<?php
		self::output_sidebar();
	}

	public static function errors_debugging() {
		global $wp_version;

		$gadwp = GADWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$anonim = $gadwp->config->options;
		$anonim['wp_version'] = $wp_version;
		$anonim['gadwp_version'] = GADWP_CURRENT_VERSION;
		if ( $anonim['ga_dash_token'] ) {
			$anonim['ga_dash_token'] = 'HIDDEN';
		}
		if ( $anonim['ga_dash_clientid'] ) {
			$anonim['ga_dash_clientid'] = 'HIDDEN';
		}
		if ( $anonim['ga_dash_clientsecret'] ) {
			$anonim['ga_dash_clientsecret'] = 'HIDDEN';
		}
		$options = self::update_options( 'frontend' );
		if ( ! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'google-analytics-dashboard-for-wp' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'google-analytics-dashboard-for-wp' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'google-analytics-dashboard-for-wp' ) ) ) );
		}
		?>
<div class="wrap">
		<?php echo "<h2>" . __( "Google Analytics Errors & Debugging", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?>
</div>
<div id="poststuff" class="gadwp">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<div class="settings-wrapper">
				<div class="inside">
						<?php if (isset($message)) echo $message; ?>
						<?php $tabs = array( 'errors' => __( "Errors & Details", 'google-analytics-dashboard-for-wp' ), 'config' => __( "Plugin Settings", 'google-analytics-dashboard-for-wp' ) ); ?>
						<?php self::navigation_tabs( $tabs ); ?>
						<div id="gadwp-errors">
						<table class="gadwp-settings-options">
							<tr>
								<td>
									<?php echo "<h2>" . __( "Last Error detected", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php $errors = print_r( GADWP_Tools::get_cache( 'last_error' ), true ) ? esc_html( print_r( GADWP_Tools::get_cache( 'last_error' ), true ) ) : __( "None", 'google-analytics-dashboard-for-wp' ); ?>
									<pre class="gadwp-settings-logdata"><?php echo __("Last Error: ", 'google-analytics-dashboard-for-wp') . $errors;?></pre>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "Error Details", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td>
									<pre class="gadwp-settings-logdata"><?php _e("Error Details: ", 'google-analytics-dashboard-for-wp'); $error_details = print_r( GADWP_Tools::get_cache( 'gapi_errors' ), true ) ? "\n" . esc_html( print_r( GADWP_Tools::get_cache( 'last_error' ), true ) ) : __( "None", 'google-analytics-dashboard-for-wp' ); echo $error_details; ?></pre>
									<br />
									<hr>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo "<h2>" . __( "Sampled Data", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php $sampling = GADWP_TOOLS::get_cache( 'sampleddata' ); ?>
									<?php if ( $sampling ) :?>
									<?php printf( __( "Last Detected on %s.", 'google-analytics-dashboard-for-wp' ), '<strong>'. $sampling['date'] . '</strong>' );?>
									<br />
									<?php printf( __( "The report was based on %s of sessions.", 'google-analytics-dashboard-for-wp' ), '<strong>'. $sampling['percent'] . '</strong>' );?>
									<br />
									<?php printf( __( "Sessions ratio: %s.", 'google-analytics-dashboard-for-wp' ), '<strong>'. $sampling['sessions'] . '</strong>' ); ?>
									<?php else :?>
									<?php _e( "None", 'google-analytics-dashboard-for-wp' ); ?>
									<?php endif;?>
								</td>
							</tr>
						</table>
					</div>
					<div id="gadwp-config">
						<table class="gadwp-settings-options">
							<tr>
								<td><?php echo "<h2>" . __( "Plugin Configuration", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td>
									<pre class="gadwp-settings-logdata"><?php echo esc_html(print_r($anonim, true));?></pre>
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
		$gadwp = GADWP();

		global $wp_version;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'general' );
		printf( '<div id="gapi-warning" class="updated"><p>%1$s <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=gadwp">%2$s</a></p></div>', __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'google-analytics-dashboard-for-wp' ), __( 'Library conflicts between WordPress plugins', 'google-analytics-dashboard-for-wp' ) );
		if ( null === $gadwp->gapi_controller ) {
			$gadwp->gapi_controller = new GADWP_GAPI_Controller();
		}
		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
		if ( isset( $_POST['gadwp_access_code'] ) ) {
			if ( 1 == ! stripos( 'x' . $_POST['gadwp_access_code'], 'UA-', 1 ) ) {
				try {
					$gadwp->gapi_controller->client->authenticate( $_POST['gadwp_access_code'] );
					$gadwp->config->options['ga_dash_token'] = $gadwp->gapi_controller->client->getAccessToken();
					$gadwp->config->options['automatic_updates_minorversion'] = 1;
					$gadwp->config->set_plugin_options();
					$options = self::update_options( 'general' );
					$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Plugin authorization succeeded.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
					GADWP_Tools::delete_cache( 'gapi_errors' );
					GADWP_Tools::delete_cache( 'last_error' );
					if ( $gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken() ) {
						if ( ! empty( $gadwp->config->options['ga_dash_profile_list'] ) ) {
							$profiles = $gadwp->config->options['ga_dash_profile_list'];
						} else {
							$profiles = $gadwp->gapi_controller->refresh_profiles();
						}
						if ( $profiles ) {
							$gadwp->config->options['ga_dash_profile_list'] = $profiles;
							if ( ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
								$profile = GADWP_Tools::guess_default_domain( $profiles );
								$gadwp->config->options['ga_dash_tableid_jail'] = $profile;
								// $gadwp->config->options['ga_dash_tableid'] = $profile;
							}
							$gadwp->config->set_plugin_options();
							$options = self::update_options( 'general' );
						}
					}
				} catch ( Deconf_IO_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ), $gadwp->gapi_controller->error_timeout );
					return false;
				} catch ( Deconf_Service_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( "(" . $e->getCode() . ") " . $e->getMessage() ), $gadwp->gapi_controller->error_timeout );
					GADWP_Tools::set_cache( 'gapi_errors', $e->getErrors(), $gadwp->gapi_controller->error_timeout );
					return $e->getCode();
				} catch ( Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ) . "\nResponseHttpCode:" . $e->getCode(), $gadwp->gapi_controller->error_timeout );
					$gadwp->gapi_controller->reset_token( false );
				}
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "The access code is <strong>NOT</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X). Try again, and use the red link to get your access code", 'google-analytics-dashboard-for-wp' ) . ".</p></div>";
			}
		}
		if ( isset( $_POST['Clear'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				GADWP_Tools::clear_cache();
				$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Cleared Cache.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Reset'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$gadwp->gapi_controller->reset_token( true );
				GADWP_Tools::clear_cache();
				$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Token Reseted and Revoked.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Reset_Err'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				GADWP_Tools::delete_cache( 'last_error' );
				GADWP_Tools::delete_cache( 'gapi_errors' );
				$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "All errors reseted.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['options']['ga_dash_hidden'] ) && ! isset( $_POST['Clear'] ) && ! isset( $_POST['Reset'] ) && ! isset( $_POST['Reset_Err'] ) ) {
			$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Settings saved.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Hide'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$message = "<div class='updated' id='gadwp-action'><p>" . __( "All other domains/properties were removed.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
				$lock_profile = GADWP_Tools::get_selected_profile( $gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail'] );
				$gadwp->config->options['ga_dash_profile_list'] = array( $lock_profile );
				$options = self::update_options( 'general' );
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		?>
	<div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?>
					<hr>
					</div>
					<div id="poststuff" class="gadwp">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content">
								<div class="settings-wrapper">
									<div class="inside">
										<?php if ( $gadwp->gapi_controller->gapi_errors_handler() || GADWP_Tools::get_cache( 'last_error' ) ) : ?>
													<?php $message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'google-analytics-dashboard-for-wp' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'google-analytics-dashboard-for-wp' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'google-analytics-dashboard-for-wp' ) ) ) );?>
										<?php endif;?>
										<?php if ( isset( $_POST['Authorize'] ) ) : ?>
											<?php GADWP_Tools::clear_cache(); ?>
											<?php $gadwp->gapi_controller->token_request(); ?>
											<div class="updated">
											<p><?php _e( "Use the red link (see below) to generate and get your access code!", 'google-analytics-dashboard-for-wp' )?></p>
										</div>
										<?php else : ?>
										<?php if ( isset( $message ) ) :?>
											<?php echo $message;?>
										<?php endif; ?>
										<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
											<input type="hidden" name="options[ga_dash_hidden]" value="Y">
											<?php wp_nonce_field('gadash_form','gadash_security'); ?>
											<table class="gadwp-settings-options">
												<tr>
													<td colspan="2">
														<?php echo "<h2>" . __( "Plugin Authorization", 'google-analytics-dashboard-for-wp' ) . "</h2>";?>
													</td>
												</tr>
												<tr>
													<td colspan="2" class="gadwp-settings-info">
														<?php printf(__('You should watch the %1$s and read this %2$s before proceeding to authorization. This plugin requires a properly configured Google Analytics account!', 'google-analytics-dashboard-for-wp'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_video&utm_campaign=gadwp', __("video", 'google-analytics-dashboard-for-wp')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=gadwp', __("tutorial", 'google-analytics-dashboard-for-wp')));?>
													</td>
												</tr>
												  <?php if (! $options['ga_dash_token'] || $options['ga_dash_userapi']) : ?>
												<tr>
													<td colspan="2" class="gadwp-settings-info">
														<input name="options[ga_dash_userapi]" type="checkbox" id="ga_dash_userapi" value="1" <?php checked( $options['ga_dash_userapi'], 1 ); ?> onchange="this.form.submit()" <?php echo ($options['ga_dash_network'])?'disabled="disabled"':''; ?> /><?php echo " ".__("developer mode (requires advanced API knowledge)", 'google-analytics-dashboard-for-wp' );?>
													</td>
												</tr>
												  <?php endif; ?>
												  <?php if ($options['ga_dash_userapi']) : ?>
												<tr>
													<td class="gadwp-settings-title">
														<label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'google-analytics-dashboard-for-wp'); ?></label>
													</td>
													<td>
														<input type="text" name="options[ga_dash_clientid]" value="<?php echo esc_attr($options['ga_dash_clientid']); ?>" size="40" required="required">
													</td>
												</tr>
												<tr>
													<td class="gadwp-settings-title">
														<label for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'google-analytics-dashboard-for-wp'); ?></label>
													</td>
													<td>
														<input type="text" name="options[ga_dash_clientsecret]" value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>" size="40" required="required">
														<input type="hidden" name="options[ga_dash_hidden]" value="Y">
														<?php wp_nonce_field('gadash_form','gadash_security'); ?>
													</td>
												</tr>
												  <?php endif; ?>
												  <?php if ( $options['ga_dash_token'] ) : ?>
												<tr>
													<td colspan="2">
														<input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Clear Authorization", 'google-analytics-dashboard-for-wp' ); ?>" <?php echo $options['ga_dash_network']?'disabled="disabled"':''; ?> />
														<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'google-analytics-dashboard-for-wp' ); ?>" />
														<input type="submit" name="Reset_Err" class="button button-secondary" value="<?php _e( "Reset Errors", 'google-analytics-dashboard-for-wp' ); ?>" />
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr>
													</td>
												</tr>
												<tr>
													<td colspan="2"><?php echo "<h2>" . __( "General Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
												</tr>
												<tr>
													<td class="gadwp-settings-title">
														<label for="ga_dash_tableid_jail"><?php _e("Select View:", 'google-analytics-dashboard-for-wp' ); ?></label>
													</td>
													<td>
														<select id="ga_dash_tableid_jail" <?php disabled(empty($options['ga_dash_profile_list']) || 1 == count($options['ga_dash_profile_list']), true); ?> name="options[ga_dash_tableid_jail]">
															<?php if ( ! empty( $options['ga_dash_profile_list'] ) ) : ?>
																	<?php foreach ( $options['ga_dash_profile_list'] as $items ) : ?>
																		<?php if ( $items[3] ) : ?>
																			<option value="<?php echo esc_attr( $items[1] ); ?>" <?php selected( $items[1], $options['ga_dash_tableid_jail'] ); ?> title="<?php _e( "View Name:", 'google-analytics-dashboard-for-wp' ); ?> <?php echo esc_attr( $items[0] ); ?>">
																				<?php echo esc_html( GADWP_Tools::strip_protocol( $items[3] ) )?> &#8658; <?php echo esc_attr( $items[0] ); ?>
																			</option>
																		<?php endif; ?>
																	<?php endforeach; ?>
															<?php else : ?>
																	<option value=""><?php _e( "Property not found", 'google-analytics-dashboard-for-wp' ); ?></option>
															<?php endif; ?>
														</select>
														<?php if ( count( $options['ga_dash_profile_list'] ) > 1 ) : ?>
														&nbsp;<input type="submit" name="Hide" class="button button-secondary" value="<?php _e( "Lock Selection", 'google-analytics-dashboard-for-wp' ); ?>" />
														<?php endif; ?>
													 </td>
												</tr>
												<?php if ( $options['ga_dash_tableid_jail'] ) :	?>
												<tr>
													<td class="gadwp-settings-title"></td>
													<td>
													<?php $profile_info = GADWP_Tools::get_selected_profile( $gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail'] ); ?>
														<pre><?php echo __( "View Name:", 'google-analytics-dashboard-for-wp' ) . "\t" . esc_html( $profile_info[0] ) . "<br />" . __( "Tracking ID:", 'google-analytics-dashboard-for-wp' ) . "\t" . esc_html( $profile_info[2] ) . "<br />" . __( "Default URL:", 'google-analytics-dashboard-for-wp' ) . "\t" . esc_html( $profile_info[3] ) . "<br />" . __( "Time Zone:", 'google-analytics-dashboard-for-wp' ) . "\t" . esc_html( $profile_info[5] );?></pre>
													</td>
												</tr>
												<?php endif; ?>
												 <tr>
													<td class="gadwp-settings-title">
														<label for="ga_dash_style"><?php _e("Theme Color:", 'google-analytics-dashboard-for-wp' ); ?></label>
													</td>
													<td>
														<input type="text" id="ga_dash_style" class="ga_dash_style" name="options[ga_dash_style]" value="<?php echo esc_attr($options['ga_dash_style']); ?>" size="10">
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr>
													</td>
												</tr>
												<?php if ( !is_multisite()) :?>
												<tr>
													<td colspan="2"><?php echo "<h2>" . __( "Automatic Updates", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
												</tr>
												<tr>
													<td colspan="2" class="gadwp-settings-title">
														<div class="button-primary gadwp-settings-switchoo">
															<input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="gadwp-settings-switchoo-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>>
															<label class="gadwp-settings-switchoo-label" for="automatic_updates_minorversion">
																<div class="gadwp-settings-switchoo-inner"></div>
																<div class="gadwp-settings-switchoo-switch"></div>
															</label>
														</div>
														<div class="switch-desc"><?php echo " ".__( "automatic updates for minor versions (security and maintenance releases only)", 'google-analytics-dashboard-for-wp' );?></div>
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
														<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'google-analytics-dashboard-for-wp' ) ?>" />
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
														<input type="submit" name="Authorize" class="button button-secondary" id="authorize" value="<?php _e( "Authorize Plugin", 'google-analytics-dashboard-for-wp' ); ?>" <?php echo $options['ga_dash_network']?'disabled="disabled"':''; ?> />
														<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'google-analytics-dashboard-for-wp' ); ?>" />
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
			<?php endif; ?>
			<?php

		self::output_sidebar();
	}

	// Network Settings
	public static function general_settings_network() {
		$gadwp = GADWP();
		global $wp_version;

		if ( ! current_user_can( 'manage_network_options' ) ) {
			return;
		}
		$options = self::update_options( 'network' );
		/*
		 * Include GAPI
		 */
		echo '<div id="gapi-warning" class="updated"><p>' . __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'google-analytics-dashboard-for-wp' ) . ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=gadwp">Library conflicts between WordPress plugins</a></p></div>';

		if ( null === $gadwp->gapi_controller ) {
			$gadwp->gapi_controller = new GADWP_GAPI_Controller();
		}

		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
		if ( isset( $_POST['gadwp_access_code'] ) ) {
			if ( 1 == ! stripos( 'x' . $_POST['gadwp_access_code'], 'UA-', 1 ) ) {
				try {
					$gadwp->gapi_controller->client->authenticate( $_POST['gadwp_access_code'] );
					$gadwp->config->options['ga_dash_token'] = $gadwp->gapi_controller->client->getAccessToken();
					$gadwp->config->options['automatic_updates_minorversion'] = 1;
					$gadwp->config->set_plugin_options( true );
					$options = self::update_options( 'network' );
					$message = "<div class='updated' id='gadwp-action'><p>" . __( "Plugin authorization succeeded.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
					if ( is_multisite() ) { // Cleanup errors on the entire network
						foreach ( GADWP_Tools::get_sites( array( 'number' => apply_filters( 'gadwp_sites_limit', 100 ) ) ) as $blog ) {
							switch_to_blog( $blog['blog_id'] );
							GADWP_Tools::delete_cache( 'gapi_errors' );
							restore_current_blog();
						}
					} else {
						GADWP_Tools::delete_cache( 'gapi_errors' );
					}
					if ( $gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken() ) {
						if ( ! empty( $gadwp->config->options['ga_dash_profile_list'] ) ) {
							$profiles = $gadwp->config->options['ga_dash_profile_list'];
						} else {
							$profiles = $gadwp->gapi_controller->refresh_profiles();
						}
						if ( $profiles ) {
							$gadwp->config->options['ga_dash_profile_list'] = $profiles;
							if ( isset( $gadwp->config->options['ga_dash_tableid_jail'] ) && ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
								$profile = GADWP_Tools::guess_default_domain( $profiles );
								$gadwp->config->options['ga_dash_tableid_jail'] = $profile;
								// $gadwp->config->options['ga_dash_tableid'] = $profile;
							}
							$gadwp->config->set_plugin_options( true );
							$options = self::update_options( 'network' );
						}
					}
				} catch ( Deconf_IO_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ), $gadwp->gapi_controller->error_timeout );
					return false;
				} catch ( Deconf_Service_Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( "(" . $e->getCode() . ") " . $e->getMessage() ), $gadwp->gapi_controller->error_timeout );
					GADWP_Tools::set_cache( 'gapi_errors', $e->getErrors(), $gadwp->gapi_controller->error_timeout );
					return $e->getCode();
				} catch ( Exception $e ) {
					GADWP_Tools::set_cache( 'last_error', date( 'Y-m-d H:i:s' ) . ': ' . esc_html( $e ) . "\nResponseHttpCode:" . $e->getCode(), $gadwp->gapi_controller->error_timeout );
					$gadwp->gapi_controller->reset_token( false );
				}
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "The access code is <strong>NOT</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X). Try again, and use the red link to get your access code", 'google-analytics-dashboard-for-wp' ) . ".</p></div>";
			}
		}
		if ( isset( $_POST['Refresh'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$gadwp->config->options['ga_dash_profile_list'] = array();
				$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Properties refreshed.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
				$options = self::update_options( 'network' );
				if ( $gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken() ) {
					if ( ! empty( $gadwp->config->options['ga_dash_profile_list'] ) ) {
						$profiles = $gadwp->config->options['ga_dash_profile_list'];
					} else {
						$profiles = $gadwp->gapi_controller->refresh_profiles();
					}
					if ( $profiles ) {
						$gadwp->config->options['ga_dash_profile_list'] = $profiles;
						if ( isset( $gadwp->config->options['ga_dash_tableid_jail'] ) && ! $gadwp->config->options['ga_dash_tableid_jail'] ) {
							$profile = GADWP_Tools::guess_default_domain( $profiles );
							$gadwp->config->options['ga_dash_tableid_jail'] = $profile;
							// $gadwp->config->options['ga_dash_tableid'] = $profile;
						}
						$gadwp->config->set_plugin_options( true );
						$options = self::update_options( 'network' );
					}
				}
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Clear'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				GADWP_Tools::clear_cache();
				$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Cleared Cache.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Reset'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$gadwp->gapi_controller->reset_token( true );
				GADWP_Tools::clear_cache();
				$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Token Reseted and Revoked.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['options']['ga_dash_hidden'] ) && ! isset( $_POST['Clear'] ) && ! isset( $_POST['Reset'] ) && ! isset( $_POST['Refresh'] ) ) {
			$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "Settings saved.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			if ( ! ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) ) {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		if ( isset( $_POST['Hide'] ) ) {
			if ( isset( $_POST['gadash_security'] ) && wp_verify_nonce( $_POST['gadash_security'], 'gadash_form' ) ) {
				$message = "<div class='updated' id='gadwp-autodismiss'><p>" . __( "All other domains/properties were removed.", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
				$lock_profile = GADWP_Tools::get_selected_profile( $gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail'] );
				$gadwp->config->options['ga_dash_profile_list'] = array( $lock_profile );
				$options = self::update_options( 'network' );
			} else {
				$message = "<div class='error' id='gadwp-autodismiss'><p>" . __( "Cheating Huh?", 'google-analytics-dashboard-for-wp' ) . "</p></div>";
			}
		}
		?>
<div class="wrap">
											<h2><?php _e( "Google Analytics Settings", 'google-analytics-dashboard-for-wp' );?></h2>
											<hr>
										</div>
										<div id="poststuff" class="gadwp">
											<div id="post-body" class="metabox-holder columns-2">
												<div id="post-body-content">
													<div class="settings-wrapper">
														<div class="inside">
					<?php if ( $gadwp->gapi_controller->gapi_errors_handler() || GADWP_Tools::get_cache( 'last_error' ) ) : ?>
						<?php $message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'google-analytics-dashboard-for-wp' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_errors_debugging', false ), __( 'Errors & Debug', 'google-analytics-dashboard-for-wp' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'gadash_settings', false ), __( 'authorize the plugin', 'google-analytics-dashboard-for-wp' ) ) ) );?>
					<?php endif; ?>
					<?php if ( isset( $_POST['Authorize'] ) ) : ?>
						<?php GADWP_Tools::clear_cache();?>
						<?php $gadwp->gapi_controller->token_request();?>
					<div class="updated">
																<p><?php _e( "Use the red link (see below) to generate and get your access code!", 'google-analytics-dashboard-for-wp' );?></p>
															</div>
					<?php else : ?>
						<?php if ( isset( $message ) ) : ?>
							<?php echo $message; ?>
						<?php endif; ?>
					<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
																<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>
						<table class="gadwp-settings-options">
																	<tr>
																		<td colspan="2">
								<?php echo "<h2>" . __( "Network Setup", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?>
								</td>
																	</tr>
																	<tr>
																		<td colspan="2" class="gadwp-settings-title">
																			<div class="button-primary gadwp-settings-switchoo">
																				<input type="checkbox" name="options[ga_dash_network]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_network" <?php checked( $options['ga_dash_network'], 1); ?> onchange="this.form.submit()">
																				<label class="gadwp-settings-switchoo-label" for="ga_dash_network">
																					<div class="gadwp-settings-switchoo-inner"></div>
																					<div class="gadwp-settings-switchoo-switch"></div>
																				</label>
																			</div>
																			<div class="switch-desc"><?php echo " ".__("use a single Google Analytics account for the entire network", 'google-analytics-dashboard-for-wp' );?></div>
																		</td>
																	</tr>
							<?php if ($options['ga_dash_network']) : ?>
							<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2"><?php echo "<h2>" . __( "Plugin Authorization", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
																	</tr>
																	<tr>
																		<td colspan="2" class="gadwp-settings-info">
								<?php printf(__('You should watch the %1$s and read this %2$s before proceeding to authorization. This plugin requires a properly configured Google Analytics account!', 'google-analytics-dashboard-for-wp'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_video&utm_campaign=gadwp', __("video", 'google-analytics-dashboard-for-wp')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=gadwp', __("tutorial", 'google-analytics-dashboard-for-wp')));?>
								</td>
																	</tr>
								<?php if ( ! $options['ga_dash_token'] || $options['ga_dash_userapi'] ) : ?>
								<tr>
																		<td colspan="2" class="gadwp-settings-info">
																			<input name="options[ga_dash_userapi]" type="checkbox" id="ga_dash_userapi" value="1" <?php checked( $options['ga_dash_userapi'], 1 ); ?> onchange="this.form.submit()" /><?php echo " ".__("developer mode (requires advanced API knowledge)", 'google-analytics-dashboard-for-wp' );?>
								</td>
																	</tr>
								<?php endif; ?>
							<?php if ( $options['ga_dash_userapi'] ) : ?>
							<tr>
																		<td class="gadwp-settings-title">
																			<label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'google-analytics-dashboard-for-wp'); ?>
									</label>
																		</td>
																		<td>
																			<input type="text" name="options[ga_dash_clientid]" value="<?php echo esc_attr($options['ga_dash_clientid']); ?>" size="40" required="required">
																		</td>
																	</tr>
																	<tr>
																		<td class="gadwp-settings-title">
																			<label for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'google-analytics-dashboard-for-wp'); ?>
									</label>
																		</td>
																		<td>
																			<input type="text" name="options[ga_dash_clientsecret]" value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>" size="40" required="required">
																			<input type="hidden" name="options[ga_dash_hidden]" value="Y">
																			<?php wp_nonce_field('gadash_form','gadash_security'); ?>
								</td>
																	</tr>
							<?php endif; ?>
							<?php if ( $options['ga_dash_token'] ) : ?>
							<tr>
																		<td colspan="2">
																			<input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Clear Authorization", 'google-analytics-dashboard-for-wp' ); ?>" />
																			<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'google-analytics-dashboard-for-wp' ); ?>" />
																			<input type="submit" name="Refresh" class="button button-secondary" value="<?php _e( "Refresh Properties", 'google-analytics-dashboard-for-wp' ); ?>" />
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
								<?php echo "<h2>" . __( "Properties/Views Settings", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?>
								</td>
																	</tr>
							<?php if ( isset( $options['ga_dash_tableid_network'] ) ) : ?>
								<?php $options['ga_dash_tableid_network'] = json_decode( json_encode( $options['ga_dash_tableid_network'] ), false ); ?>
							<?php endif; ?>
							<?php foreach ( GADWP_Tools::get_sites( array( 'number' => apply_filters( 'gadwp_sites_limit', 100 ) ) ) as $blog ) : ?>
							<tr>
																		<td class="gadwp-settings-title-s">
																			<label for="ga_dash_tableid_network"><?php echo '<strong>'.$blog['domain'].$blog['path'].'</strong>: ';?></label>
																		</td>
																		<td>
																			<select id="ga_dash_tableid_network" <?php disabled(!empty($options['ga_dash_profile_list']),false);?> name="options[ga_dash_tableid_network][<?php echo $blog['blog_id'];?>]">
									<?php if ( ! empty( $options['ga_dash_profile_list'] ) ) : ?>
										<?php foreach ( $options['ga_dash_profile_list'] as $items ) : ?>
											<?php if ( $items[3] ) : ?>
												<?php $temp_id = $blog['blog_id']; ?>
												<option value="<?php echo esc_attr( $items[1] );?>" <?php selected( $items[1], isset( $options['ga_dash_tableid_network']->$temp_id ) ? $options['ga_dash_tableid_network']->$temp_id : '');?> title="<?php echo __( "View Name:", 'google-analytics-dashboard-for-wp' ) . ' ' . esc_attr( $items[0] );?>">
													 <?php echo esc_html( GADWP_Tools::strip_protocol( $items[3] ) );?> &#8658; <?php echo esc_attr( $items[0] );?>
												</option>
											<?php endif; ?>
										<?php endforeach; ?>
									<?php else : ?>
												<option value="">
													<?php _e( "Property not found", 'google-analytics-dashboard-for-wp' );?>
												</option>
									<?php endif; ?>
									</select>
																			<br />
																		</td>
																	</tr>
							<?php endforeach; ?>
							<tr>
																		<td colspan="2">
																			<h2><?php echo _e( "Automatic Updates", 'google-analytics-dashboard-for-wp' );?></h2>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2" class="gadwp-settings-title">
																			<div class="button-primary gadwp-settings-switchoo">
																				<input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="gadwp-settings-switchoo-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>>
																				<label class="gadwp-settings-switchoo-label" for="automatic_updates_minorversion">
																					<div class="gadwp-settings-switchoo-inner"></div>
																					<div class="gadwp-settings-switchoo-switch"></div>
																				</label>
																			</div>
																			<div class="switch-desc"><?php echo " ".__( "automatic updates for minor versions (security and maintenance releases only)", 'google-analytics-dashboard-for-wp' );?></div>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
																			<hr><?php echo "<h2>" . __( "Exclude Tracking", 'google-analytics-dashboard-for-wp' ) . "</h2>"; ?></td>
																	</tr>
																	<tr>
																		<td colspan="2" class="gadwp-settings-title">
																			<div class="button-primary gadwp-settings-switchoo">
																				<input type="checkbox" name="options[ga_dash_excludesa]" value="1" class="gadwp-settings-switchoo-checkbox" id="ga_dash_excludesa"<?php checked( $options['ga_dash_excludesa'], 1); ?>">
																				<label class="gadwp-settings-switchoo-label" for="ga_dash_excludesa">
																					<div class="gadwp-settings-switchoo-inner"></div>
																					<div class="gadwp-settings-switchoo-switch"></div>
																				</label>
																			</div>
																			<div class="switch-desc"><?php echo " ".__("exclude Super Admin tracking for the entire network", 'google-analytics-dashboard-for-wp' );?></div>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2">
																			<hr>
																		</td>
																	</tr>
																	<tr>
																		<td colspan="2" class="submit">
																			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'google-analytics-dashboard-for-wp' ) ?>" />
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
																			<input type="submit" name="Authorize" class="button button-secondary" id="authorize" value="<?php _e( "Authorize Plugin", 'google-analytics-dashboard-for-wp' ); ?>" />
																			<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'google-analytics-dashboard-for-wp' ); ?>" />
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
		<?php endif; ?>
		<?php

		self::output_sidebar();
	}

	public static function output_sidebar() {
		global $wp_version;

		$gadwp = GADWP();
		?>
				</div>
													</div>
												</div>
												<div id="postbox-container-1" class="postbox-container">
													<div class="meta-box-sortables">
														<div class="postbox">
															<h3>
																<span><?php _e("Setup Tutorial & Demo",'google-analytics-dashboard-for-wp') ?></span>
															</h3>
															<div class="inside">
																<a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=video&utm_campaign=gadwp" target="_blank"><img src="<?php echo plugins_url( 'images/google-analytics-dashboard.png' , __FILE__ );?>" width="100%" alt="" /></a>
															</div>
														</div>
														<div class="postbox">
															<h3>
																<span><?php _e("Follow & Review",'google-analytics-dashboard-for-wp')?></span>
															</h3>
															<div class="inside">
																<div class="gadash-desc">
																	<div style="margin-left: -10px;">
																		<div class="g-page" data-width="273" data-href="//plus.google.com/+Deconfcom" data-layout="landscape" data-showtagline="false" data-showcoverphoto="false" data-rel="publisher"></div>
																	</div>
																	<script type="text/javascript">
																	  (function() {
																		var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
																		po.src = 'https://apis.google.com/js/platform.js';
																		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
																	  })();
																	</script>
																</div>
																<br />
																<div class="gadash-desc">
																	<a href="https://twitter.com/deconfcom" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @deconfcom</a>
																	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
																</div>
																<br />
																<div class="gadash-title">
																	<a href="http://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp#plugin-info"><img src="<?php echo plugins_url( 'images/star.png' , __FILE__ ); ?>" /></a>
																</div>
																<div class="gadash-desc">
																	<?php printf(__('Your feedback and review are both important, %s!', 'google-analytics-dashboard-for-wp'), sprintf('<a href="http://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp#plugin-info">%s</a>', __('rate this plugin', 'google-analytics-dashboard-for-wp')));?>
																</div>
															</div>
														</div>
														<div class="postbox">
															<h3>
																<span><?php _e("Further Reading",'google-analytics-dashboard-for-wp')?></span>
															</h3>
															<div class="inside">
																<div class="gadash-title">
																	<a href="https://deconf.com/clicky-web-analytics-review/?utm_source=gadwp_config&utm_medium=link&utm_content=clicky&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/clicky.png' , __FILE__ ); ?>" /></a>
																</div>
																<div class="gadash-desc">
																	<?php printf(__('%s service with users tracking at IP level.', 'google-analytics-dashboard-for-wp'), sprintf('<a href="https://deconf.com/clicky-web-analytics-review/?utm_source=gadwp_config&utm_medium=link&utm_content=clicky&utm_campaign=gadwp">%s</a>', __('Web Analytics', 'google-analytics-dashboard-for-wp')));?>
																</div>
																<br />
																<div class="gadash-title">
																	<a href="https://deconf.com/move-website-https-ssl/?utm_source=gadwp_config&utm_medium=link&utm_content=ssl&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/ssl.png' , __FILE__ ); ?>" /></a>
																</div>
																<div class="gadash-desc">
																	<?php printf(__('%s by moving your website to HTTPS/SSL.', 'google-analytics-dashboard-for-wp'), sprintf('<a href="https://deconf.com/move-website-https-ssl/?utm_source=gadwp_config&utm_medium=link&utm_content=ssl&utm_campaign=gadwp">%s</a>', __('Improve search rankings', 'google-analytics-dashboard-for-wp')));?>
																</div>
																<br />
																<div class="gadash-title">
																	<a href="https://deconf.com/wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=plugins&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/wp.png' , __FILE__ ); ?>" /></a>
																</div>
																<div class="gadash-desc">
																	<?php printf(__('Other %s written by the same author', 'google-analytics-dashboard-for-wp'), sprintf('<a href="https://deconf.com/wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=plugins&utm_campaign=gadwp">%s</a>', __('WordPress Plugins', 'google-analytics-dashboard-for-wp')));?>
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
			delete_option( 'gadwp_got_updated' );
		}
	}
}
