<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
//@formatter:off
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

use Deconf\AIWP\Google\Service\Exception as GoogleServiceException;

final class AIWP_Settings {

	private static function update_options( $who ) {
		$aiwp = AIWP();
		$message = '';
		$network_settings = false;
		$options = $aiwp->config->options; // Get current options
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) && isset( $_REQUEST['options'] ) && ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) && 'Reset' != $who ) {
			$new_options = $aiwp->config->validate_data( $_REQUEST['options'] );
			if ( 'tracking' == $who ) {
				$options['ga_anonymize_ip'] = 0;
				$options['ga_optout'] = 0;
				$options['ga_dnt_optout'] = 0;
				$options['ga_event_tracking'] = 0;
				$options['ga_enhanced_links'] = 0;
				$options['ga_event_precision'] = 0;
				$options['ga_event_bouncerate'] = 0;
				$options['ga_crossdomain_tracking'] = 0;
				$options['ga_aff_tracking'] = 0;
				$options['ga_hash_tracking'] = 0;
				$options['ga_formsubmit_tracking'] = 0;
				$options['ga_force_ssl'] = 0;
				$options['ga_pagescrolldepth_tracking'] = 0;
				$options['ga_samesite'] = '';
				$options['tm_pagescrolldepth_tracking'] = 0;
				$options['tm_optout'] = 0;
				$options['tm_dnt_optout'] = 0;
				$options['amp_tracking_analytics'] = 0;
				$options['amp_tracking_clientidapi'] = 0;
				$options['amp_tracking_tagmanager'] = 0;
				$options['optimize_pagehiding'] = 0;
				$options['optimize_tracking'] = 0;
				$options['trackingcode_infooter'] = 0;
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
			} elseif ( 'network' == $who ) {
				$options['user_api'] = 0;
				$options['network_mode'] = 0;
				$options['superadmin_tracking'] = 0;
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
			echo "<a class='nav-tab' id='tab-".esc_attr( $tab )."' href='#top#aiwp-".esc_attr( $tab )."'>". esc_html( $name ) ."</a>";
		}
		echo '</h2>';
	}

	private static function html_form_begin( $text, $action, $message ) {
		?>
<form name="aiwp_form" method="post" action="<?php echo esc_url( $action ); ?>">
	<div class="wrap">
			<?php echo "<h2>" . esc_html( $text ) . "</h2>"; ?>
	  <?php if (isset($message)) echo wp_kses( $message, array( 'div' => array( 'class' => array(), 'id' => array() ), 'p' => array(), 'a' => array( 'href' => array() ) ) ); ?>
	  <hr>
	</div>
	<div id="poststuff" class="aiwp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
						<input type="hidden" name="options[aiwp_hidden]" value="Y">
						<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>
	<?php
}

	private static function html_form_end() {
		?>


</form>
<?php
	}

	private static function html_switch_button( $option_name, $option_value, $option_id, $checked, $option_text, $disabled = false, $onchange = false ) {
		if ( $disabled ){
			return;
		}
		?>
<tr>
	<td colspan="2" class="aiwp-settings-title">
		<div class="button-primary aiwp-settings-switchoo">
			<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $option_value ); ?>" class="aiwp-settings-switchoo-checkbox" id="<?php echo esc_attr( $option_id ); ?>" <?php checked( $checked, 1 ); ?> <?php disabled( $disabled, true );?> <?php if ($onchange) {echo ' onchange="this.form.submit()"'; } ?>>
			<label class="aiwp-settings-switchoo-label" for="<?php echo esc_attr( $option_id ); ?>">
				<div class="aiwp-settings-switchoo-inner"></div>
				<div class="aiwp-settings-switchoo-switch"></div>
			</label>
		</div>
		<div class="switch-desc"><?php echo " " . esc_html( $option_text );?></div>
	</td>
</tr>
<?php
	}

	private static function html_section_delimiter( $title = false, $withhr = true, $withspan = true, $disabled = false ) {
		?>
<tr>
	<td <?php if ( $withspan ) echo 'colspan="2"'; ?>>
			<?php if ( $withhr ) echo "<hr>";	if ( $title ) echo "<h2>" . esc_html( $title ) . "</h2>";?>
	</td>
</tr>
<?php
	}

	public static function frontend_settings() {
		$aiwp = AIWP();
		$message = '';
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'frontend' );
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		?>
<?php self::html_form_begin(__( "Google Analytics Frontend Settings", 'analytics-insights' ), $_SERVER['REQUEST_URI'], $message)?>
<table class="aiwp-settings-options">
	<?php self::html_section_delimiter(__( "Permissions", 'analytics-insights' ), false); ?>
	<tr>
		<td class="roles aiwp-settings-title">
			<label for="access_front"><?php _e("Show stats to:", 'analytics-insights' ); ?></label>
		</td>
		<td class="aiwp-settings-roles">
			<table>
				<tr>
				<?php if ( ! isset( $wp_roles ) ) : $wp_roles = new WP_Roles(); endif; ?>
				<?php $i = 0; ?>
				<?php foreach ( $wp_roles->role_names as $role => $name ) : ?>
				<?php if ( 'subscriber' != $role ) : ?>
				<?php $i++; ?>
					<td>
						<label>
							<input type="checkbox" name="options[access_front][]" value="<?php echo esc_attr( $role ); ?>" <?php if ( in_array($role,$options['access_front']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /><?php echo esc_attr( $name ); ?></label>
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
	<?php self::html_switch_button('options[frontend_item_reports]', 1, 'frontend_item_reports', $options['frontend_item_reports'], __("enable web page reports on frontend", 'analytics-insights') ); ?>
	<?php self::html_section_delimiter(); ?>
	<tr>
		<td colspan="2" class="submit">
			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
		</td>
	</tr>
</table>
<?php self::html_form_end(); ?>
<?php	AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}

	public static function backend_settings() {
		$aiwp = AIWP();
		$message = '';
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'backend' );
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		?>
<?php self::html_form_begin(__( "Google Analytics Backend Settings", 'analytics-insights' ), $_SERVER['REQUEST_URI'], $message)?>
<table class="aiwp-settings-options">
	<?php self::html_section_delimiter(__( "Permissions", 'analytics-insights' ), false); ?>
	<tr>
		<td class="roles aiwp-settings-title">
			<label for="access_back"><?php _e("Show stats to:", 'analytics-insights' ); ?></label>
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
							<input type="checkbox" name="options[access_back][]" value="<?php echo esc_attr( $role ); ?>" <?php if ( in_array($role,$options['access_back']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /> <?php echo esc_attr( $name ); ?></label>
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
 <?php self::html_switch_button('options[switch_profile]', 1, 'switch_profile', $options['switch_profile'], __( "enable Switch Property functionality", 'analytics-insights') ); ?>
 <?php self::html_switch_button('options[backend_item_reports]', 1, 'backend_item_reports', $options['backend_item_reports'], __( "enable reports on Posts List and Pages List", 'analytics-insights') ); ?>
 <?php self::html_switch_button('options[dashboard_widget]', 1, 'dashboard_widget', $options['dashboard_widget'], __( "enable the main Dashboard Widget", 'analytics-insights') ); ?>
 <?php self::html_section_delimiter(__( "Real-Time Settings", 'analytics-insights' )); ?>
 <tr>
		<td colspan="2" class="aiwp-settings-title"> <?php _e("Maximum number of pages to display on real-time tab:", 'analytics-insights'); ?>
									<input type="number" name="options[ga_realtime_pages]" id="ga_realtime_pages" value="<?php echo (int)$options['ga_realtime_pages']; ?>" size="3">
		</td>
	</tr>
			<?php self::html_section_delimiter(__( "Location Settings", 'analytics-insights' )); ?>
	<tr>
		<td class="aiwp-settings-title">
			<label for="ga_target_geomap"><?php _e("Target Map Country:", 'analytics-insights' ); ?></label>
		</td>
		<td>
			<select id="ga_target_geomap" name="options[ga_target_geomap]">
			<?php $country_codes = AIWP_Tools::get_countrycodes(); ?>
			<?php foreach ( $country_codes as $key => $value ) : ?>
			<?php if ( $value ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $options['ga_target_geomap'] ); ?> >
	  		<?php echo esc_html( $value ) ?>
				</option>
			<?php endif; ?>
			<?php endforeach; ?>
			</select>
		</td>
	</tr>
	</tr>
			<?php self::html_section_delimiter(__( "Google Maps Settings", 'analytics-insights' )); ?>
	<tr>
	<tr>
		<td colspan="2" class="aiwp-settings-title">
				<?php echo __("API Key:", 'analytics-insights'); ?>
				<input type="text" style="text-align: center;" name="options[maps_api_key]" value="<?php echo esc_attr($options['maps_api_key']); ?>" size="50">
		</td>
	</tr>
	<?php self::html_section_delimiter(__( "404 Errors Report", 'analytics-insights' )); ?>
	<tr>
		<td colspan="2" class="aiwp-settings-title">
	 	<?php echo __("404 Page Title contains:", 'analytics-insights'); ?>
			<input type="text" style="text-align: center;" name="options[pagetitle_404]" value="<?php echo esc_attr($options['pagetitle_404']); ?>" size="20">
		</td>
	</tr>
 <?php self::html_section_delimiter(); ?>
	<tr>
		<td colspan="2" class="submit">
			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
		</td>
	</tr>
</table>
<?php self::html_form_end(); ?>
<?php
		AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}

	public static function tracking_settings() {
		$aiwp = AIWP();
		$message = '';
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'tracking' );
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( 'ga4tracking' == $options['tracking_type'] ) {
			$tabs = array( 'basic' => __( "Basic Settings", 'analytics-insights' ), 'events' => __( "Events Tracking", 'analytics-insights' ), 'custom' => __( "Custom Definitions", 'analytics-insights' ), 'exclude' => __( "Exclude Tracking", 'analytics-insights' ), 'advanced' => __( "Advanced Settings", 'analytics-insights' ), 'integration' => __( "Integration", 'analytics-insights' ) );
		} else if ( 'tagmanager' == $options['tracking_type'] ) {
			$tabs = array( 'basic' => __( "Basic Settings", 'analytics-insights' ), 'tmdatalayervars' => __( "DataLayer Variables", 'analytics-insights' ), 'exclude' => __( "Exclude Tracking", 'analytics-insights' ), 'tmadvanced' => __( "Advanced Settings", 'analytics-insights' ), 'tmintegration' => __( "Integration", 'analytics-insights' ) );
		} else {
			$tabs = array( 'basic' => __( "Basic Settings", 'analytics-insights' ) );
		}
		?>
<?php self::html_form_begin(__( "Google Analytics Tracking Code", 'analytics-insights' ), '', $message)?>
<?php self::navigation_tabs( $tabs ); ?>
<div id="aiwp-basic">
	<table class="aiwp-settings-options">
		<?php self::html_section_delimiter(__( "Tracking Settings", 'analytics-insights' ), false); ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="tracking_type"><?php _e("Tracking Type:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="tracking_type" name="options[tracking_type]" onchange="this.form.submit()">
					<?php if ( $aiwp->config->options['webstream_jail'] ) : ?>
					 <option value="ga4tracking" <?php selected( $options['tracking_type'], 'ga4tracking' ); ?>><?php _e("Google Analytics 4", 'analytics-insights');?></option>
					<?php endif; ?>
					<option value="tagmanager" <?php selected( $options['tracking_type'], 'tagmanager' ); ?>><?php _e("Tag Manager", 'analytics-insights');?></option>
					<option value="disabled" <?php selected( $options['tracking_type'], 'disabled' ); ?>><?php _e("Disabled", 'analytics-insights');?></option>
				</select>
			</td>
		</tr>
	 <?php if ( 'ga4tracking' == $options['tracking_type'] ) : ?>
		<tr>
			<td class="aiwp-settings-title"></td>
			<td>
			<?php if ( 'ga4tracking' == $options['tracking_type'] && $aiwp->config->options['webstream_jail'] ) : ?>
				<?php $webstream_info = AIWP_Tools::get_selected_profile( $aiwp->config->options['ga4_webstreams_list'], $aiwp->config->options['webstream_jail'] ); ?>
				<?php $webstream_info[5] = isset( $webstream_info[5] ) ? $webstream_info[5] : '' ?>
				<pre><?php echo __( "Stream Name:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[0] ) . "<br />" . __( "Stream ID:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[1] ) . "<br />" . __( "Stream URL:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[2] ) . "<br />" . __( "Measurement ID:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[3] ) . "<br />" . __( "Time Zone:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[5] );?></pre>
				<?php endif; ?>
			</td>
		</tr>
			<?php elseif ( 'tagmanager' == $options['tracking_type'] ) : ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="tracking_type"><?php _e("Web Container ID:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<input type="text" name="options[web_containerid]" value="<?php echo esc_attr($options['web_containerid']); ?>" size="15">
			</td>
		</tr>
			<?php endif; ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="trackingcode_infooter"><?php _e("Code Placement:", 'analytics-insights' ); ?></label>
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
		<?php self::html_section_delimiter(__( "Events Tracking", 'analytics-insights' ), false); ?>
		<?php self::html_switch_button('options[ga_event_tracking]', 1, 'ga_event_tracking', $options['ga_event_tracking'], __( "track downloads, mailto, telephone and outbound links", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[ga_aff_tracking]', 1, 'ga_aff_tracking', $options['ga_aff_tracking'], __( "track affiliate links", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[ga_hash_tracking]', 1, 'ga_hash_tracking', $options['ga_hash_tracking'], __( "track fragment identifiers, hashmarks (#) in URI links", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[ga_formsubmit_tracking]', 1, 'ga_formsubmit_tracking', $options['ga_formsubmit_tracking'], __( "track form submit actions", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[ga_pagescrolldepth_tracking]', 1, 'ga_pagescrolldepth_tracking', $options['ga_pagescrolldepth_tracking'], __( "track page scrolling depth", 'analytics-insights') ); ?>
	 <tr>
			<td class="aiwp-settings-title">
				<label for="ga_event_downloads"><?php _e("Downloads Regex:", 'analytics-insights'); ?></label>
			</td>
			<td>
				<input type="text" id="ga_event_downloads" name="options[ga_event_downloads]" value="<?php echo esc_attr($options['ga_event_downloads']); ?>" size="50">
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_event_affiliates"><?php _e("Affiliates Regex:", 'analytics-insights'); ?></label>
			</td>
			<td>
				<input type="text" id="ga_event_affiliates" name="options[ga_event_affiliates]" value="<?php echo esc_attr($options['ga_event_affiliates']); ?>" size="50">
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="trackingevents_infooter"><?php _e("Code Placement:", 'analytics-insights' ); ?></label>
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
		<?php self::html_section_delimiter(__( "Custom Dimensions", 'analytics-insights' ), false); ?>
		<?php $dimprefix = 'ga4tracking' == $options['tracking_type'] ? 'aiwp_dim_' : 'dimension' ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_author_dimindex"><?php _e("Authors:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="ga_author_dimindex" name="options[ga_author_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo (int) $i;?>" <?php selected( $options['ga_author_dimindex'], $i ); ?>><?php echo 0 == $i ?'Disabled':$dimprefix . ' ' .(int) $i; ?></option>
										<?php endfor; ?>
			 </select>
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_pubyear_dimindex"><?php _e("Publication Year:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="ga_pubyear_dimindex" name="options[ga_pubyear_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo (int) $i;?>" <?php selected( $options['ga_pubyear_dimindex'], $i ); ?>><?php echo 0 == $i ?'Disabled':$dimprefix . ' ' .(int) $i; ?></option>
										<?php endfor; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_pubyearmonth_dimindex"><?php _e("Publication Month:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="ga_pubyearmonth_dimindex" name="options[ga_pubyearmonth_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo (int) $i;?>" <?php selected( $options['ga_pubyearmonth_dimindex'], $i ); ?>><?php echo 0 == $i ?'Disabled':$dimprefix . ' ' .(int) $i; ?></option>
										<?php endfor; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_category_dimindex"><?php _e("Categories:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="ga_category_dimindex" name="options[ga_category_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo (int) $i;?>" <?php selected( $options['ga_category_dimindex'], $i ); ?>><?php echo 0 == $i ? 'Disabled':$dimprefix . ' ' .(int) $i; ?></option>
										<?php endfor; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_user_dimindex"><?php _e("User Type:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="ga_user_dimindex" name="options[ga_user_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
											<option value="<?php echo (int) $i;?>" <?php selected( $options['ga_user_dimindex'], $i ); ?>><?php echo 0 == $i ? 'Disabled':$dimprefix . ' ' .(int) $i; ?></option>
										<?php endfor; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_tag_dimindex"><?php _e("Tags:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="ga_tag_dimindex" name="options[ga_tag_dimindex]">
										<?php for ($i=0;$i<21;$i++) : ?>
										<option value="<?php echo (int) $i;?>" <?php selected( $options['ga_tag_dimindex'], $i ); ?>><?php echo 0 == $i ? 'Disabled':$dimprefix . ' ' .(int) $i; ?></option>
										<?php endfor; ?>
				</select>
			</td>
		</tr>
	</table>
</div>
<div id="aiwp-tmdatalayervars">
	<table class="aiwp-settings-options">
							 <?php self::html_section_delimiter(__( "Main Variables", 'analytics-insights' ), false); ?>
	 <tr>
			<td class="aiwp-settings-title">
				<label for="tm_author_var"><?php _e("Authors:", 'analytics-insights' ); ?></label>
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
				<label for="tm_pubyear_var"><?php _e("Publication Year:", 'analytics-insights' ); ?></label>
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
				<label for="tm_pubyearmonth_var"><?php _e("Publication Month:", 'analytics-insights' ); ?></label>
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
				<label for="tm_category_var"><?php _e("Categories:", 'analytics-insights' ); ?></label>
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
				<label for="tm_user_var"><?php _e("User Type:", 'analytics-insights' ); ?></label>
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
				<label for="tm_tag_var"><?php _e("Tags:", 'analytics-insights' ); ?></label>
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
			<?php self::html_section_delimiter(__( "Advanced Tracking", 'analytics-insights' ), false); ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_user_samplerate"><?php _e("User Sample Rate:", 'analytics-insights'); ?></label>
			</td>
			<td>
				<input type="number" id="ga_user_samplerate" name="options[ga_user_samplerate]" value="<?php echo (int)($options['ga_user_samplerate']); ?>" max="100" min="1">
				%
			</td>
		</tr>
		<?php self::html_switch_button('options[ga_anonymize_ip]', 1, 'ga_anonymize_ip', $options['ga_anonymize_ip'], __( "anonymize IPs while tracking", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[ga_event_bouncerate]', 1, 'ga_event_bouncerate', $options['ga_event_bouncerate'], __( "exclude events from bounce-rate and time on page calculation", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[ga_enhanced_links]', 1, 'ga_enhanced_links', $options['ga_enhanced_links'], __( "enable enhanced link attribution", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[ga_event_precision]', 1, 'ga_event_precision', $options['ga_event_precision'], __( "use hitCallback to increase event tracking accuracy", 'analytics-insights') ); ?>
		<?php self::html_section_delimiter(__( "Cross-domain Tracking", 'analytics-insights' ), false); ?>
		<?php self::html_switch_button('options[ga_crossdomain_tracking]', 1, 'ga_crossdomain_tracking', $options['ga_crossdomain_tracking'], __( "enable cross domain tracking", 'analytics-insights') ); ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_crossdomain_list"><?php _e("Cross Domains:", 'analytics-insights'); ?></label>
			</td>
			<td>
				<input type="text" id="ga_crossdomain_list" name="options[ga_crossdomain_list]" value="<?php echo esc_attr($options['ga_crossdomain_list']); ?>" size="50">
			</td>
		</tr>
		<?php self::html_section_delimiter(__( "Cookie Customization", 'analytics-insights' ), false); ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_cookiedomain"><?php _e("Cookie Domain:", 'analytics-insights'); ?></label>
			</td>
			<td>
				<input type="text" id="ga_cookiedomain" name="options[ga_cookiedomain]" value="<?php echo esc_attr($options['ga_cookiedomain']); ?>" size="50">
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_cookiename"><?php _e("Cookie Name:", 'analytics-insights'); ?></label>
			</td>
			<td>
				<input type="text" id="ga_cookiename" name="options[ga_cookiename]" value="<?php echo esc_attr($options['ga_cookiename']); ?>" size="50">
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_cookieexpires"><?php _e("Cookie Expires:", 'analytics-insights'); ?></label>
			</td>
			<td>
				<input type="text" id="ga_cookieexpires" name="options[ga_cookieexpires]" value="<?php echo esc_attr($options['ga_cookieexpires']); ?>" size="10">
										<?php _e("seconds", 'analytics-insights' ); ?>
			</td>
		</tr>
		<tr>
			<td class="aiwp-settings-title">
				<label for="ga_samesite"><?php _e("SameSite Attribute:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<select id="ga_samesite" name="options[ga_samesite]">
					<option value="" <?php selected( $options['ga_samesite'], '' ); ?>><?php _e("Disabled", 'analytics-insights');?></option>
					<option value="Strict" <?php selected( $options['ga_samesite'], 'Strict' ); ?>><?php _e("Strict", 'analytics-insights');?></option>
					<option value="Lax" <?php selected( $options['ga_samesite'], 'Lax' ); ?>><?php _e("Lax", 'analytics-insights');?></option>
					<option value="None" <?php selected( $options['ga_samesite'], 'None' ); ?>><?php _e("None", 'analytics-insights');?></option>
				</select>
			</td>
		</tr>
	</table>
</div>
<div id="aiwp-integration">
	<table class="aiwp-settings-options">
		<?php self::html_section_delimiter(__( "Accelerated Mobile Pages (AMP)", 'analytics-insights' ), false); ?>
		<?php self::html_switch_button('options[amp_tracking_analytics]', 1, 'amp_tracking_analytics', $options['amp_tracking_analytics'], __( "enable tracking for Accelerated Mobile Pages (AMP)", 'analytics-insights') ); ?>
		<?php self::html_section_delimiter(__( "Optimize", 'analytics-insights' ), false); ?>
		<?php self::html_switch_button('options[optimize_tracking]', 1, 'optimize_tracking', $options['optimize_tracking'], __( "enable Optimize tracking", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[optimize_pagehiding]', 1, 'optimize_pagehiding', $options['optimize_pagehiding'], __( "enable Page Hiding support", 'analytics-insights') ); ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="tracking_type"><?php _e("Container ID:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<input type="text" name="options[optimize_containerid]" value="<?php echo esc_attr($options['optimize_containerid']); ?>" size="15">
			</td>
		</tr>
	</table>
</div>
<div id="aiwp-tmadvanced">
	<table class="aiwp-settings-options">
		<?php self::html_section_delimiter(__( "Advanced Tracking", 'analytics-insights' ), false); ?>
		<?php self::html_switch_button('options[tm_optout]', 1, 'tm_optout', $options['tm_optout'], __( "enable support for user opt-out", 'analytics-insights') ); ?>
		<?php self::html_switch_button('options[tm_dnt_optout]', 1, 'tm_dnt_optout', $options['tm_dnt_optout'], __( "exclude tracking for users sending Do Not Track header", 'analytics-insights') ); ?>
	</table>
</div>
<div id="aiwp-tmintegration">
	<table class="aiwp-settings-options">
 	<?php self::html_section_delimiter(__( "Accelerated Mobile Pages (AMP)", 'analytics-insights' ), false); ?>
		<?php self::html_switch_button('options[amp_tracking_tagmanager]', 1, 'amp_tracking_tagmanager', $options['amp_tracking_tagmanager'], __( "enable tracking for Accelerated Mobile Pages (AMP)", 'analytics-insights') ); ?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="tracking_type"><?php _e("AMP Container ID:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<input type="text" name="options[amp_containerid]" value="<?php echo esc_attr($options['amp_containerid']); ?>" size="15">
			</td>
		</tr>
	</table>
</div>
<div id="aiwp-exclude">
	<table class="aiwp-settings-options">
		<?php self::html_section_delimiter(__( "Exclude Tracking", 'analytics-insights' ), false); ?>
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
								<input type="checkbox" name="options[track_exclude][]" value="<?php echo esc_attr( $role ); ?>" <?php if (in_array($role,$options['track_exclude'])) echo 'checked="checked"'; ?> /> <?php echo esc_attr( $name ); ?></label>
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
	<?php self::html_section_delimiter(); ?>
	<tr>
		<td colspan="2" class="submit">
			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
		</td>
	</tr>
</table>
<?php self::html_form_end(); ?>
<?php
		AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}

	public static function errors_debugging() {
		$aiwp = AIWP();
		$message = '';
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$anonim = AIWP_Tools::anonymize_options( $aiwp->config->options );
		$options = self::update_options( 'frontend' );
		?>
<?php self::html_form_begin(__( "Google Analytics Errors & Debugging", 'analytics-insights' ), $_SERVER['REQUEST_URI'], $message)?>
<?php $tabs = array( 'errors' => __( "Errors & Details", 'analytics-insights' ), 'config' => __( "Plugin Settings", 'analytics-insights' ), 'sysinfo' => __( "System", 'analytics-insights' ) ); ?>
<?php self::navigation_tabs( $tabs ); ?>
<div id="aiwp-errors">
	<table class="aiwp-settings-logdata">
		<?php self::html_section_delimiter(__( "Detected Errors", 'analytics-insights' ), false, false); ?>
		<tr>
			<td>
				<?php $errors_count = AIWP_Tools::get_cache( 'errors_count' ); ?>
				<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("Count: ", 'analytics-insights') . '</span>' . (int)$errors_count;?></pre>
				<?php $error = AIWP_Tools::get_cache( 'api_errors' ) ?>
				<?php $ajax_error = AIWP_Tools::get_cache( 'ajax_errors' ) ?>
				<?php $error_code = isset( $error[0] ) ? $error[0] : 'None' ?>
				<?php $error_reason = ( isset( $error[1] ) && !empty($error[1]) ) ? print_r( $error[1], true) : 'None' ?>
				<?php $error_ajax = $ajax_error ? print_r( $ajax_error , true) : 'None' ?>
				<?php $error_details = isset( $error[2] ) ? print_r( $error[2], true) : 'None' ?>
				<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("Ajax Error Code: ", 'analytics-insights') . '</span>' . esc_html( $error_ajax );?></pre>
				<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("API Error Code: ", 'analytics-insights') . '</span>' . esc_html( $error_code );?></pre>
				<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("API Error Reason: ", 'analytics-insights') . '</span>' . "\n" . esc_html( $error_reason );?></pre>
				<?php $error_details = str_replace( 'Deconf_', 'Google_', $error_details); ?>
				<pre class="aiwp-settings-logdata"><?php echo '<span>' . __("API Error Details: ", 'analytics-insights') . '</span>' . "\n" . esc_html( $error_details );?></pre>
				<br />
				<hr>
			</td>
		</tr>
		<?php self::html_section_delimiter(__( "Sampled Data", 'analytics-insights' ), false, false); ?>
		<tr>
			<td>
				<?php $sampling = AIWP_TOOLS::get_cache( 'sampleddata' ); ?>
				<?php if ( $sampling ) :?>
					<?php printf( __( "Last Detected on %s.", 'analytics-insights' ), '<strong>'. esc_html( $sampling['date'] ) . '</strong>' );?><br />
					<?php printf( __( "The report was based on %s of sessions.", 'analytics-insights' ), '<strong>'. esc_html( $sampling['percent'] ) . '</strong>' );?><br />
					<?php printf( __( "Sessions ratio: %s.", 'analytics-insights' ), '<strong>'. esc_html( $sampling['sessions'] ) . '</strong>' ); ?>
				<?php else :?>
					<?php _e( "None", 'analytics-insights' ); ?>
			 <?php endif;?>
			</td>
		</tr>
	</table>
</div>
<div id="aiwp-config">
	<table class="aiwp-settings-options">
		<?php self::html_section_delimiter(__( "Plugin Configuration", 'analytics-insights' ), false, false); ?>
		<tr>
			<td>
				<pre class="aiwp-settings-logdata"><?php echo esc_html( print_r( $anonim, true ) );?></pre>
				<br />
				<hr>
			</td>
		</tr>
	</table>
</div>
<div id="aiwp-sysinfo">
	<table class="aiwp-settings-options">
		<?php self::html_section_delimiter(__( "System Information", 'analytics-insights' ), false, false); ?>
		<tr>
			<td>
				<pre class="aiwp-settings-logdata"><?php echo esc_html( AIWP_Tools::system_info() );?></pre>
				<br />
				<hr>
			</td>
		</tr>
	</table>
</div>
<?php self::html_form_end(); ?>
<?php
		AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}

	public static function general_settings() {
		$aiwp = AIWP();
		$message = '';
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'general' );
		printf( '<div id="gapi-warning" class="updated"><p>%1$s <a href="https://deconf.com/analytics-insights-google-analytics-dashboard-wordpress/?utm_source=aiwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=aiwp">%2$s</a></p></div>', __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'analytics-insights' ), __( 'Library conflicts between WordPress plugins', 'analytics-insights' ) );
		if ( null === $aiwp->gapi_controller ) {
			$aiwp->gapi_controller = new AIWP_GAPI_Controller();
		}
		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';

		if ( isset( $_REQUEST['aiwp_access_code'] ) || isset( $_REQUEST['code'] ) ){

			if ( isset( $_REQUEST['state'] ) && wp_verify_nonce( $_REQUEST['state'], 'aiwp_state' ) ) {

			if ( isset( $_REQUEST['code'] ) ){
				$aiwp_access_code = sanitize_text_field( $_REQUEST['code'] );
			} else {
				$aiwp_access_code = sanitize_text_field( $_REQUEST['aiwp_access_code'] );
			}

			AIWP_Tools::delete_cache( 'api_errors' );
			AIWP_Tools::delete_cache( 'ajax_errors' );

			$token = $aiwp->gapi_controller->authenticate( $aiwp_access_code );

			$aiwp->config->options['token'] = $token;

			$aiwp->config->set_plugin_options();

			$options = self::update_options( 'general' );
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Plugin authorization succeeded.", 'analytics-insights' ) . "</p></div>";
			if ( $aiwp->config->options['token'] ) {

				$webstreams = $aiwp->gapi_controller->refresh_profiles_ga4();
				if ( is_array( $webstreams ) && ! empty( $webstreams ) ) {
						$aiwp->config->options['ga4_webstreams_list'] = $webstreams;
						if ( ! $aiwp->config->options['webstream_jail'] ) {
							$property = AIWP_Tools::guess_default_domain( $webstreams, 2 );
							$aiwp->config->options['webstream_jail'] = $property;
						}
						$aiwp->config->set_plugin_options();
						$options = self::update_options( 'general' );
				}

			}

			}
		}

		if ( isset( $_REQUEST['Clear'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Cleared Cache.", 'analytics-insights' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Reset'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$aiwp->gapi_controller->reset_token( true, true );
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Token Reseted and Revoked.", 'analytics-insights' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Reset_Err'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				AIWP_Tools::delete_cache( 'api_errors' );
				AIWP_Tools::delete_cache( 'ajax_errors' );
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "All errors reseted.", 'analytics-insights' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) && ! isset( $_REQUEST['Clear'] ) && ! isset( $_REQUEST['Reset'] ) && ! isset( $_REQUEST['Reset_Err'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Hide'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$message = "<div class='updated' id='aiwp-action'><p>" . __( "All other domains/properties were removed.", 'analytics-insights' ) . "</p></div>";
				$lock_property = AIWP_Tools::get_selected_profile( $aiwp->config->options['ga4_webstreams_list'], $aiwp->config->options['webstream_jail'] );
				if ( empty( $lock_property ) ) {
					$aiwp->config->options['ga4_webstreams_list'] = '';
				} else {
					$aiwp->config->options['ga4_webstreams_list'] = array( $lock_property );
				}
				$options = self::update_options( 'general' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		?>
<?php self::html_form_begin(__( "Google Analytics Settings", 'analytics-insights' ), admin_url( 'admin.php?page=aiwp_settings' ), $message)?>
<table class="aiwp-settings-options">
	<?php self::html_section_delimiter(__( "Plugin Authorization", 'analytics-insights' ), false); ?>
	<tr>
		<td colspan="2" class="aiwp-settings-info">
			<?php printf(__('New to Google Analytics? You should follow this %1$s before proceeding to authorization.', 'analytics-insights'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/how-to-add-google-analytics-to-wordpress-easy-way/?utm_source=aiwp_config&utm_medium=link&utm_content=authorization-tutorial&utm_campaign=aiwp', __("step-by-step tutorial", 'analytics-insights')));?>
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
			<input type="text" name="options[client_id]" value="<?php echo esc_attr( $options['client_id'] ); ?>" size="40" required="required">
		</td>
	</tr>
	<tr>
		<td class="aiwp-settings-title">
			<label for="options[client_secret]"><?php _e("Client Secret:", 'analytics-insights'); ?></label>
		</td>
		<td>
			<input type="text" name="options[client_secret]" value="<?php echo esc_attr( $options['client_secret'] ); ?>" size="40" required="required">
	 	<?php if ( !$options['token'] ) : ?>
			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Credentials', 'analytics-insights' ) ?>" />
			<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>
	<?php if ( $options['token'] ) : ?>
	<tr>
		<td colspan="2">
			<button type="submit" name="Reset" class="button button-secondary" <?php echo $options['network_mode']?'disabled="disabled"':''; ?>><?php _e( "Clear Authorization", 'analytics-insights' ); ?></button>
			<button type="submit" name="Clear" class="button button-secondary"><?php _e( "Clear Cache", 'analytics-insights' ); ?></button>
			<button type="submit" name="Reset_Err" class="button button-secondary"><?php _e( "Reset Errors", 'analytics-insights' ); ?></button>
		</td>
	</tr>
	<?php self::html_section_delimiter(); ?>
	<?php self::html_section_delimiter(__( "General Settings", 'analytics-insights' ), false); ?>
		<tr>
		<td class="aiwp-settings-title">
			<label for="webstream_jail"><?php _e("Google Analaytics 4:", 'analytics-insights' ); ?></label>
		</td>
		<td>
			<select id="webstream_jail" <?php disabled(empty($options['ga4_webstreams_list']) || 1 == count($options['ga4_webstreams_list']), true); ?> name="options[webstream_jail]">
			<?php if ( ! empty( $options['ga4_webstreams_list'] ) ) : ?>
			<?php foreach ( $options['ga4_webstreams_list'] as $items ) : ?>
			<?php if ( $items[2] ) : ?>
				<option value="<?php echo esc_attr( $items[1] ); ?>" <?php selected( $items[1], $options['webstream_jail'] ); ?> title="<?php _e( "Stream Name:", 'analytics-insights' ); ?> <?php echo esc_attr( $items[0] ); ?>">
	  		<?php echo esc_html( AIWP_Tools::strip_protocol( $items[2] ) )?> &#8658; <?php echo esc_attr( $items[0] ); ?>
				</option>
			<?php endif; ?>
			<?php endforeach; ?>
			<?php endif; ?>
			</select>
		</td>
	</tr>
	<?php if ( $options['webstream_jail'] && !empty( $aiwp->config->options['ga4_webstreams_list'] ) ) :	?>
	<tr>
		<td class="aiwp-settings-title"></td>
		<td>
			<?php $webstream_info = AIWP_Tools::get_selected_profile( $aiwp->config->options['ga4_webstreams_list'], $aiwp->config->options['webstream_jail'] ); ?>
			<?php $webstream_info[5] = isset( $webstream_info[5] ) ? $webstream_info[5] : '' ?>
			<pre><?php echo __( "Stream Name:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[0] ) . "<br />" . __( "Stream ID:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[1] ) . "<br />" . __( "Stream URL:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[2] ) . "<br />" . __( "Measurement ID:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[3] ) . "<br />" . __( "Time Zone:", 'analytics-insights' ) . "\t" . esc_html( $webstream_info[5] );?></pre>
		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="aiwp-settings-title">
			<label for="theme_color"><?php _e("Theme Color:", 'analytics-insights' ); ?></label>
		</td>
		<td>
			<input type="text" id="theme_color" class="theme_color" name="options[theme_color]" value="<?php echo esc_attr( $options['theme_color'] ); ?>" size="10">
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" class="submit">
			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
			<?php if ( ( is_array( $options['ga4_webstreams_list'] ) && count( $options['ga4_webstreams_list'] ) > 1 ) ): ?>
				<input type="submit" name="Hide" class="button button-secondary" " value="<?php _e( "Lock Selection", 'analytics-insights' ); ?>" />
			<?php endif; ?>
		</td>
	</tr>
	<?php else : ?>
	<?php self::html_section_delimiter(); ?>
	<tr>
		<td colspan="2">
	  <?php $auth = $aiwp->gapi_controller->createAuthUrl();?>
			<button type="submit" class="button button-secondary" formaction="<?php echo esc_url_raw( $auth ); ?>" <?php echo $options['network_mode']?'disabled="disabled"':''; ?>><?php _e( "Authorize Plugin", 'analytics-insights' ); ?></button>
			<button type="submit" name="Clear" class="button button-secondary"><?php _e( "Clear Cache", 'analytics-insights' ); ?></button>
		</td>
	</tr>
	<?php self::html_section_delimiter(); ?>
</table>
<?php self::html_form_end(); ?>
<?php AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() ); ?>
<?php return; ?>
<?php endif; ?>
</table>
<?php
		AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}

	// Network Settings
	public static function general_settings_network() {
		$aiwp = AIWP();
		$message = '';
		if ( ! current_user_can( 'manage_network_options' ) ) {
			return;
		}
		$options = self::update_options( 'network' );
		/*
		 * Include GAPI
		 */
		echo '<div id="gapi-warning" class="updated"><p>' . __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'analytics-insights' ) . ' <a href="https://deconf.com/analytics-insights-google-analytics-dashboard-wordpress/?utm_source=aiwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=aiwp">Library conflicts between WordPress plugins</a></p></div>';
		if ( null === $aiwp->gapi_controller ) {
			$aiwp->gapi_controller = new AIWP_GAPI_Controller();
		}
		echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
		if ( isset( $_REQUEST['aiwp_access_code'] ) || isset( $_REQUEST['code'] ) ){

			if ( isset( $_REQUEST['state'] ) && wp_verify_nonce( $_REQUEST['state'], 'aiwp_state' ) ) {

				if ( isset( $_REQUEST['code'] ) ){
					$aiwp_access_code = sanitize_text_field( $_REQUEST['code'] );
				} else {
					$aiwp_access_code = sanitize_text_field( $_REQUEST['aiwp_access_code'] );
				}

					$aiwp_access_code = sanitize_text_field( $_REQUEST['aiwp_access_code'] );

					$token = $aiwp->gapi_controller->authenticate( $aiwp_access_code );
					$aiwp->config->options['token'] = $token;
					$aiwp->config->set_plugin_options( true );
					$options = self::update_options( 'network' );
					$message = "<div class='updated' id='aiwp-action'><p>" . __( "Plugin authorization succeeded.", 'analytics-insights' ) . "</p></div>";
					if ( is_multisite() ) { // Cleanup errors on the entire network
						foreach ( AIWP_Tools::get_sites( array( 'number' => apply_filters( 'aiwp_sites_limit', 100 ) ) ) as $blog ) {
							switch_to_blog( $blog['blog_id'] );
							AIWP_Tools::delete_cache( 'api_errors' );
							AIWP_Tools::delete_cache( 'ajax_errors' );
							restore_current_blog();
						}
					} else {
						AIWP_Tools::delete_cache( 'api_errors' );
						AIWP_Tools::delete_cache( 'ajax_errors' );
					}
					if ( $aiwp->config->options['token'] ) {
						$webstreams = $aiwp->gapi_controller->refresh_profiles_ga4();
						if ( is_array( $webstreams ) && ! empty( $webstreams ) ) {
							$aiwp->config->options['ga4_webstreams_list'] = $webstreams;
							if ( isset( $aiwp->config->options['webstream_jail'] ) && ! $aiwp->config->options['webstream_jail'] ) {
								// $property = AIWP_Tools::guess_default_domain( $webstreams, 2 );
								// $aiwp->config->options['webstream_jail'] = $property;
							}
							$aiwp->config->set_plugin_options( true );
							$options = self::update_options( 'network' );
						}
					}
			}
		}
		if ( isset( $_REQUEST['Refresh'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$aiwp->config->options['ga4_webstreams_list'] = array();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Properties refreshed.", 'analytics-insights' ) . "</p></div>";
				$options = self::update_options( 'network' );
				if ( $aiwp->config->options['token'] ) {
					if ( ! empty( $aiwp->config->options['ga4_webstreams_list'] ) ) {
						$webstreams = $aiwp->config->options['ga4_webstreams_list'];
					} else {
						$webstreams = $aiwp->gapi_controller->refresh_profiles_ga4();
					}
					if ( $webstreams ) {
						$aiwp->config->options['ga4_webstreams_list'] = $webstreams;
						if ( isset( $aiwp->config->options['webstream_jail'] ) && ! $aiwp->config->options['webstream_jail'] ) {
							// $property = AIWP_Tools::guess_default_domain( $webstreams, 2 );
							// $aiwp->config->options['webstream_jail'] = $property;
						}
						$aiwp->config->set_plugin_options( true );
						$options = self::update_options( 'network' );
					}
				}
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Clear'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Cleared Cache.", 'analytics-insights' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Reset'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$aiwp->gapi_controller->reset_token( true, true );
				AIWP_Tools::clear_cache();
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Token Reseted and Revoked.", 'analytics-insights' ) . "</p></div>";
				$options = self::update_options( 'Reset' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['options']['aiwp_hidden'] ) && ! isset( $_REQUEST['Clear'] ) && ! isset( $_REQUEST['Reset'] ) && ! isset( $_REQUEST['Refresh'] ) ) {
			$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "Settings saved.", 'analytics-insights' ) . "</p></div>";
			if ( ! ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) ) {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		if ( isset( $_REQUEST['Hide'] ) ) {
			if ( isset( $_REQUEST['aiwp_security'] ) && wp_verify_nonce( $_REQUEST['aiwp_security'], 'aiwp_form' ) ) {
				$message = "<div class='updated' id='aiwp-autodismiss'><p>" . __( "All other domains/properties were removed.", 'analytics-insights' ) . "</p></div>";
				$lock_property = AIWP_Tools::get_selected_profile( $aiwp->config->options['ga4_webstreams_list'], $aiwp->config->options['webstream_jail'] );
				if ( empty( $lock_property ) ) {
					$aiwp->config->options['ga4_webstreams_list'] = array();
				} else {
					$aiwp->config->options['ga4_webstreams_list'] = array( $lock_property );
				}
				$options = self::update_options( 'network' );
			} else {
				$message = "<div class='error' id='aiwp-autodismiss'><p>" . __( "You do not have sufficient permissions to access this page.", 'analytics-insights' ) . "</p></div>";
			}
		}
		?>
<?php self::html_form_begin(__( "Google Analytics Settings", 'analytics-insights' ), network_admin_url( 'admin.php?page=aiwp_settings' ), $message)?>
<table class="aiwp-settings-options">
 <?php self::html_section_delimiter(__( "Network Setup", 'analytics-insights' ), false); ?>
	<?php self::html_switch_button('options[network_mode]', 1, 'network_mode', $options['network_mode'], __( "use a single Google Analytics account for the entire network", 'analytics-insights'), false, true ); ?>
	<?php if ( $options['network_mode'] ) : ?>
	<?php self::html_section_delimiter(); ?>
	<?php self::html_section_delimiter(__( "Plugin Authorization", 'analytics-insights' ), false); ?>
	<tr>
		<td colspan="2" class="aiwp-settings-info">
			<?php printf(__('New to Google Analytics? You should follow this %1$s before proceeding to authorization.', 'analytics-insights'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/how-to-add-google-analytics-to-wordpress-easy-way/?utm_source=aiwp_config&utm_medium=link&utm_content=authorizarion-screen&utm_campaign=aiwp', __("step-by-step tutorial", 'analytics-insights')));?>
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
			<label for="options[client_id]"><?php _e("Client ID:", 'analytics-insights'); ?></label>
		</td>
		<td>
			<input type="text" name="options[client_id]" value="<?php echo esc_attr( $options['client_id'] ); ?>" size="40" required="required">
		</td>
	</tr>
	<tr>
		<td class="aiwp-settings-title">
			<label for="options[client_secret]"><?php _e("Client Secret:", 'analytics-insights'); ?></label>
		</td>
		<td>
			<input type="text" name="options[client_secret]" value="<?php echo esc_attr( $options['client_secret'] ); ?>" size="40" required="required">
			<input type="hidden" name="options[aiwp_hidden]" value="Y">
			<?php if ( !$options['token'] ) : ?>
			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Credentials', 'analytics-insights' ) ?>" />
			<?php endif; ?>
			<?php wp_nonce_field('aiwp_form','aiwp_security'); ?>
		</td>
	</tr>
	<?php endif; ?>
	<?php if ( $options['token'] ) : ?>
	<tr>
		<td colspan="2">
			<button type="submit" name="Reset" class="button button-secondary"><?php _e( "Clear Authorization", 'analytics-insights' ); ?></button>
			<button type="submit" name="Clear" class="button button-secondary"><?php _e( "Clear Cache", 'analytics-insights' ); ?></button>
			<button type="submit" name="Refresh" class="button button-secondary"><?php _e( "Refresh Properties", 'analytics-insights' ); ?></button>
		</td>
	</tr>
	<?php self::html_section_delimiter() ?>
	<?php self::html_section_delimiter(__( "Properties/Views Settings", 'analytics-insights' ), false) ?>
	<?php if ( isset( $options['network_webstream'] ) ) : ?>
	<?php $options['network_webstream'] = json_decode( json_encode( $options['network_webstream'] ), false ); ?>
	<?php endif; ?>
	<?php foreach ( AIWP_Tools::get_sites( array( 'number' => apply_filters( 'aiwp_sites_limit', 100 ) ) ) as $blog ) : ?>
	<tr>
		<td class="aiwp-settings-title-s">
			<label for="network_webstream"><?php echo '<strong>'. esc_html( $blog['domain'] ) . esc_url( $blog['path'] ) .'</strong>: ';?></label>
		</td>
		<td>
			<select id="network_webstreams-<?php echo $blog['blog_id'] ?>" class="network_webstreams" <?php disabled(empty($options['ga4_webstreams_list']) || 1 == count($options['ga4_webstreams_list']), true); ?> name="options[network_webstream][<?php echo esc_attr( $blog['blog_id'] );?>]">
			<?php if ( ! empty( $options['ga4_webstreams_list'] ) ) : ?>
			<?php $temp_id = $blog['blog_id']; ?>
			<?php foreach ( $options['ga4_webstreams_list'] as $items ) : ?>
			<?php if ( $items[2] ) : ?>
				<option value="<?php echo esc_attr( $items[1] ); ?>" <?php selected( $items[1], isset( $options['network_webstream']->$temp_id ) ? $options['network_webstream']->$temp_id : '');?> title="<?php _e( "Stream Name:", 'analytics-insights' ); ?> <?php echo esc_attr( $items[0] ); ?>">
	  		<?php echo esc_html( AIWP_Tools::strip_protocol( $items[2] ) )?> &#8658; <?php echo esc_attr( $items[0] ); ?>
				</option>
			<?php endif; ?>
			<?php endforeach; ?>
			<?php endif; ?>
			</select>
		</td>
	</tr>
	<?php endforeach; ?>
	<?php self::html_section_delimiter(); ?>
	<?php self::html_switch_button('options[superadmin_tracking]', 1, 'superadmin_tracking', $options['superadmin_tracking'], __( "exclude Super Admin tracking for the entire network", 'analytics-insights'), false, false ); ?>
	<?php self::html_section_delimiter(); ?>
	<tr>
		<td colspan="2" class="submit">
			<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'analytics-insights' ) ?>" />
		</td>
	</tr>
	<?php else : ?>
	<?php self::html_section_delimiter(); ?>
	<tr>
		<td colspan="2">
	  <?php $auth = $aiwp->gapi_controller->createAuthUrl();?>
			<button type="submit" class="button button-secondary" formaction="<?php echo esc_url_raw( $auth ); ?>"><?php _e( "Authorize Plugin", 'analytics-insights' ); ?></button>
			<button type="submit" name="Clear" class="button button-secondary"><?php _e( "Clear Cache", 'analytics-insights' ); ?></button>
		</td>
	</tr>
	<?php endif; ?>
	<?php self::html_section_delimiter(); ?>
</table>
<?php self::html_form_end(); ?>
<?php AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() ); ?>
<?php return; ?>
<?php endif;?>
</table>
<?php self::html_form_end(); ?>
<?php
		AIWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}
}
