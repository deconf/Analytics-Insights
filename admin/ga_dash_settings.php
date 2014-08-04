<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class GADASH_Settings {
	private static function set_get_options($who) {
		global $GADASH_Config;
		$network_settings = false;
		$GADASH_Config->get_plugin_options (); // Retrieve current options
		$options = $GADASH_Config->options; // Get current options
		if (isset ( $_POST ['options'] ['ga_dash_hidden'] ) and isset ( $_POST ['options'] ) and (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) and $who != 'Reset') {
			
			$new_options = $_POST ['options'];
			
			if ($who == 'tracking') {
				$options ['ga_dash_anonim'] = 0;
				$options ['ga_event_tracking'] = 0;
				$options ['ga_enhanced_links'] = 0;
				$options ['ga_dash_remarketing'] = 0;
				if (isset ( $_POST ['options'] ['ga_tracking_code'] )) {
					$new_options ['ga_tracking_code'] = trim ( $new_options ['ga_tracking_code'], "\t" );
				}
				if (empty ( $new_options ['ga_track_exclude'] )) {
					$new_options ['ga_track_exclude'] = array ();
				}
			} else if ($who == 'backend') {
				$options ['ga_dash_jailadmins'] = 0;
				$options ['ga_dash_map'] = 0;
				$options ['ga_dash_traffic'] = 0;
				$options ['ga_dash_pgd'] = 0;
				$options ['ga_dash_rd'] = 0;
				$options ['ga_dash_sd'] = 0;
				if (empty ( $new_options ['ga_dash_access_back'] )) {
					$new_options ['ga_dash_access_back'] [] = 'administrator';
				}
			} else if ($who == 'frontend') {
				$options ['ga_dash_frontend_stats'] = 0;
				$options ['ga_dash_frontend_keywords'] = 0;
				if (empty ( $new_options ['ga_dash_access_front'] )) {
					$new_options ['ga_dash_access_front'] [] = 'administrator';
				}
			} else if ($who == 'general') {
				$options ['ga_dash_userapi'] = 0;
			} else if ($who == 'network' or is_network_admin()) {
				$options ['ga_dash_userapi'] = 0;
				$options ['ga_dash_network'] = 0;
				$network_settings = true;
			}
			$options = array_merge ( $options, $new_options );
			$GADASH_Config->options = $options;
			$GADASH_Config->set_plugin_options ( $network_settings );
		}
		
		return $options;
	}
	public static function frontend_settings() {
		global $GADASH_Config;
		
		if (! current_user_can ( 'manage_options' )) {
			return;
		}
		
		$options = self::set_get_options ( 'frontend' );
		
		if (isset ( $_POST ['options'] ['ga_dash_hidden'] )) {
			$message = "<div class='updated'><p><strong>" . __ ( "Options saved.", 'ga-dash' ) . "</strong></p></div>";
			if (! (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' ))) {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (! $GADASH_Config->options ['ga_dash_tableid_jail'] or ! $GADASH_Config->options ['ga_dash_token']) {
			$message = "<div class='error'><p><strong>" . __ ( "Something went wrong, you need to", 'ga-dash' ) . "</strong> <a href='" . menu_page_url ( 'gadash_settings', false ) . "'>" . __ ( 'auhorize the plugin', 'ga-dash' ) . "</a><strong> " . __ ( "or properly configure your", 'ga-dash' ) . '</strong> <a href="http://deconf.com/how-to-set-up-google-analytics-on-your-website/" target="_blank">' . __ ( 'Google Analytics account', 'ga-dash' ) . "</a>" . "<stong>!</strong></p></div>";
		}
		
		?>
<form name="ga_dash_form" method="post"
	action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">

	<div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Frontend Settings", 'ga-dash' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "General Settings", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles title"><label for="ga_dash_access_front"><?php _e("Show stats to: ", 'ga-dash' ); ?></label></td>
								<td class="roles">
                               		<?php
		if (! isset ( $wp_roles )) {
			$wp_roles = new WP_Roles ();
		}
		$i = 0;
		?>
									<table>
										<tr>
									<?php
		foreach ( $wp_roles->role_names as $role => $name ) {
			if ($role != 'subscriber') {
				$i ++;
				?>
		                                    	<td><label> <input
													type="checkbox" name="options[ga_dash_access_front][]"
													value="<?php echo $role; ?>"
													<?php if (in_array($role,$options['ga_dash_access_front']) OR $role=='administrator') echo 'checked="checked"'; if ($role=='administrator') echo 'disabled="disabled"';?> />
		                                        	<?php echo $name; ?>
												</label></td>
		                                    <?php
			}
			if ($i % 4 == 0) {
				?>
                                    			</tr>
										<tr>
                                    		<?php
			}
		}
		?>
                                    
									
									
									
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_frontend_stats]"
											value="1" class="onoffswitch-checkbox"
											id="ga_dash_frontend_stats"
											<?php checked( $options['ga_dash_frontend_stats'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_frontend_stats">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " show page visits and visitors in frontend (after each article)", 'ga-dash' );?></div>

								</td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox"
											name="options[ga_dash_frontend_keywords]" value="1"
											class="onoffswitch-checkbox" id="ga_dash_frontend_keywords"
											<?php checked( $options['ga_dash_frontend_keywords'], 1 ); ?>>
										<label class="onoffswitch-label"
											for="ga_dash_frontend_keywords">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " show page searches (after each article)", 'ga-dash' );?></div>

								</td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>

							</tr>
							<tr>
								<td colspan="2" class="submit"><input type="submit"
									name="Submit" class="button button-primary"
									value="<?php _e('Update Options', 'ga-dash' ) ?>" /></td>
							</tr>
						</table>
						<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>





</form>
<?php
		self::output_sidebar ();
	}
	public static function backend_settings() {
		global $GADASH_Config;
		
		if (! current_user_can ( 'manage_options' )) {
			return;
		}
		
		$options = self::set_get_options ( 'backend' );
		
		if (isset ( $_POST ['options'] ['ga_dash_hidden'] )) {
			$message = "<div class='updated'><p><strong>" . __ ( "Options saved.", 'ga-dash' ) . "</strong></p></div>";
			if (! (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' ))) {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (! $GADASH_Config->options ['ga_dash_tableid_jail'] or ! $GADASH_Config->options ['ga_dash_token']) {
			$message = "<div class='error'><p><strong>" . __ ( "Something went wrong, you need to", 'ga-dash' ) . "</strong> <a href='" . menu_page_url ( 'gadash_settings', false ) . "'>" . __ ( 'auhorize the plugin', 'ga-dash' ) . "</a><strong> " . __ ( "or properly configure your", 'ga-dash' ) . '</strong> <a href="http://deconf.com/how-to-set-up-google-analytics-on-your-website/" target="_blank">' . __ ( 'Google Analytics account', 'ga-dash' ) . "</a>" . "<stong>!</strong></p></div>";
		}
		
		?>
<form name="ga_dash_form" method="post"
	action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">

	<div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Dashboard Settings", 'ga-dash' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "General Settings", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles title"><label for="ga_dash_access_back"><?php _e("Show stats to: ", 'ga-dash' ); ?></label></td>
								<td class="roles">
									<?php
		if (! isset ( $wp_roles )) {
			$wp_roles = new WP_Roles ();
		}
		$i = 0;
		?>
									<table>
										<tr>
									<?php
		foreach ( $wp_roles->role_names as $role => $name ) {
			if ($role != 'subscriber') {
				$i ++;
				?>
		                                    	<td><label> <input
													type="checkbox" name="options[ga_dash_access_back][]"
													value="<?php echo $role; ?>"
													<?php if (in_array($role,$options['ga_dash_access_back']) OR $role=='administrator') echo 'checked="checked"'; if ($role=='administrator') echo 'disabled="disabled"';?> />
		                                        	<?php echo $name; ?>
												</label></td>
		                                    <?php
			}
			if ($i % 4 == 0) {
				?>
                                    			</tr>
										<tr>
                                    		<?php
			}
		}
		?>
                                    
									
									
									
									</table>
								</td>
							</tr>

							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_jailadmins]"
											value="1" class="onoffswitch-checkbox"
											id="ga_dash_jailadmins"
											<?php checked( $options['ga_dash_jailadmins'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_jailadmins">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( "disable Switch Profile/View functionality", 'ga-dash' );?></div>

								</td>
							</tr>
							<tr>
								<td colspan="2"><hr><?php echo "<h2>" . __( "Real-Time Settings", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="title"> <?php _e("Maximum number of pages to display on real-time tab:", 'ga-dash'); ?>
								<input type="text" style="text-align: center;"
									name="options[ga_realtime_pages]"
									value="<?php echo (int)$options['ga_realtime_pages']; ?>"
									size="3">
								<?php _e("(find out more", 'ga-dash')?>	<a
									href="http://deconf.com/google-analytics-dashboard-real-time-reports/"
									target="_blank"><?php _e("about this feature", 'ga-dash') ?></a>
								<?php _e(")", 'ga-dash')?></td>
							</tr>
							<tr>
								<td colspan="2"><hr><?php echo "<h2>" . __( "Additional Stats & Charts", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_map]" value="1"
											class="onoffswitch-checkbox" id="ga_dash_map"
											<?php checked( $options['ga_dash_map'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_map">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " show Geo Map chart for visits", 'ga-dash' );?></div>

								</td>
							</tr>
							<tr>
								<td colspan="2" class="title">
									<?php echo __("Target Geo Map to region:", 'ga-dash'); ?>
									<input type="text" style="text-align: center;"
									name="options[ga_target_geomap]"
									value="<?php echo esc_attr($options['ga_target_geomap']); ?>"
									size="3">
									<?php _e("and render top",'ga-dash'); ?>
									<input type="text" style="text-align: center;"
									name="options[ga_target_number]"
									value="<?php echo (int)$options['ga_target_number']; ?>"
									size="3">
									<?php _e("cities (find out more", 'ga-dash')?>
									<a
									href="http://deconf.com/country-codes-for-google-analytics-dashboard/"
									target="_blank"><?php _e("about this feature", 'ga-dash') ?></a>
									<?php _e(")", 'ga-dash')?>								
								</td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_traffic]"
											value="1" class="onoffswitch-checkbox" id="ga_dash_traffic"
											<?php checked( $options['ga_dash_traffic'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_traffic">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " show traffic overview", 'ga-dash' );?></div>

								</td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_pgd]" value="1"
											class="onoffswitch-checkbox" id="ga_dash_pgd"
											<?php checked( $options['ga_dash_pgd'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_pgd">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " show top pages", 'ga-dash' );?></div>

								</td>
							</tr>

							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_rd]" value="1"
											class="onoffswitch-checkbox" id="ga_dash_rd"
											<?php checked( $options['ga_dash_rd'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_rd">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " show top referrers", 'ga-dash' );?></div>

								</td>
							</tr>

							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_sd]" value="1"
											class="onoffswitch-checkbox" id="ga_dash_sd"
											<?php checked( $options['ga_dash_sd'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_sd">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " show top searches", 'ga-dash' );?></div>

								</td>
							</tr>


							<tr>
								<td colspan="2"><hr></td>

							</tr>
							<tr>
								<td colspan="2" class="submit"><input type="submit"
									name="Submit" class="button button-primary"
									value="<?php _e('Update Options', 'ga-dash' ) ?>" /></td>
							</tr>
						</table>
						<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>





</form>
<?php
		self::output_sidebar ();
	}
	public static function tracking_settings() {
		global $GADASH_Config;
		
		/*
		 * Include Tools
		 */
		include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
		$tools = new GADASH_Tools ();
		
		if (! current_user_can ( 'manage_options' )) {
			return;
		}
		
		$options = self::set_get_options ( 'tracking' );
		
		if (isset ( $_POST ['options'] ['ga_dash_hidden'] )) {
			$message = "<div class='updated'><p><strong>" . __ ( "Options saved.", 'ga-dash' ) . "</strong></p></div>";
			if (! (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' ))) {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (! $GADASH_Config->options ['ga_dash_tableid_jail'] or ! $GADASH_Config->options ['ga_dash_token']) {
			$message = "<div class='error'><p><strong>" . __ ( "Something went wrong, you need to", 'ga-dash' ) . "</strong> <a href='" . menu_page_url ( 'gadash_settings', false ) . "'>" . __ ( 'auhorize the plugin', 'ga-dash' ) . "</a><strong> " . __ ( "or properly configure your", 'ga-dash' ) . '</strong> <a href="http://deconf.com/how-to-set-up-google-analytics-on-your-website/" target="_blank">' . __ ( 'Google Analytics account', 'ga-dash' ) . "</a>" . "<stong>!</strong></p></div>";
		}
		
		?>
<form name="ga_dash_form" method="post"
	action="<?php  esc_url($_SERVER['REQUEST_URI']); ?>">

	<div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Tracking Code", 'ga-dash' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Tracking Settings", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="title"><label for="ga_dash_tracking"><?php _e("Tracking Options: ", 'ga-dash' ); ?></label></td>
								<td><select id="ga_dash_tracking"
									name="options[ga_dash_tracking]" onchange="this.form.submit()">
										<option value="0"
											<?php selected( $options['ga_dash_tracking'], 0 ); ?>><?php _e("Disabled", 'ga-dash');?></option>
										<option value="1"
											<?php selected( $options['ga_dash_tracking'], 1 ); ?>><?php _e("Enabled", 'ga-dash');?></option>
								</select></td>
							</tr>
							<?php
		if ($options ['ga_dash_tracking'] == 1) {
			?>
							<tr>
								<td class="title"></td>
								<td><?php
			$profile_info = $tools->get_selected_profile ( $GADASH_Config->options ['ga_dash_profile_list'], $GADASH_Config->options ['ga_dash_tableid_jail'] );
			echo '<pre>' . __ ( "View Name:", 'ga-dash' ) . "\t" . esc_html ( $profile_info [0] ) . "<br />" . __ ( "Tracking ID:", 'ga-dash' ) . "\t" . esc_html ( $profile_info [2] ) . "<br />" . __ ( "Default URL:", 'ga-dash' ) . "\t" . esc_html ( $profile_info [3] ) . "<br />" . __ ( "Time Zone:", 'ga-dash' ) . "\t" . $profile_info [5] . '</pre>';
			?></td>
							</tr>							
							<?php
		}
		if ($options ['ga_dash_tracking']) {
			?>									
							<tr>
								<td colspan="2"><hr><?php echo "<h2>" . __( "Tracking Code", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<?php
			if ($options ['ga_dash_tracking'] == 1) {
				?>														
							<tr>
								<td class="title"><label for="ga_dash_tracking_type"><?php _e("Tracking Type: ", 'ga-dash' ); ?></label></td>
								<td><select id="ga_dash_tracking_type"
									name="options[ga_dash_tracking_type]">
										<option value="classic"
											<?php selected( $options['ga_dash_tracking_type'], 'classic' ); ?>><?php _e("Classic Analytics", 'ga-dash');?></option>
										<option value="universal"
											<?php selected( $options['ga_dash_tracking_type'], 'universal' ); ?>><?php _e("Universal Analytics", 'ga-dash');?></option>
								</select></td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_anonim]"
											value="1" class="onoffswitch-checkbox" id="ga_dash_anonim"
											<?php checked( $options['ga_dash_anonim'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_anonim">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " anonymize IPs while tracking", 'ga-dash' );?></div>

								</td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_enhanced_links]"
											value="1" class="onoffswitch-checkbox" id="ga_enhanced_links"
											<?php checked( $options['ga_enhanced_links'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_enhanced_links">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " enable enhanced link attribution", 'ga-dash' );?></div>

								</td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_dash_remarketing]"
											value="1" class="onoffswitch-checkbox"
											id="ga_dash_remarketing"
											<?php checked( $options['ga_dash_remarketing'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_dash_remarketing">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e ( " enable remarketing, demographics and interests reports", 'ga-dash' );?></div>

								</td>
							</tr>														
							<?php
			}
			?>							
							<tr>
								<td colspan="2"><hr><?php echo "<h2>" . __( "Events Tracking", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="title">

									<div class="onoffswitch">
										<input type="checkbox" name="options[ga_event_tracking]"
											value="1" class="onoffswitch-checkbox" id="ga_event_tracking"
											<?php checked( $options['ga_event_tracking'], 1 ); ?>> <label
											class="onoffswitch-label" for="ga_event_tracking">
											<div class="onoffswitch-inner"></div>
											<div class="onoffswitch-switch"></div>
										</label>
									</div>
									<div class="switch-desc"><?php _e(" track downloads, mailto and outbound links", 'ga-dash' ); ?></div>

								</td>
							</tr>
							<tr>
								<td class="title"><label for="ga_event_downloads"><?php _e("Download Filters:", 'ga-dash'); ?></label></td>
								<td><input type="text" id="ga_event_downloads"
									name="options[ga_event_downloads]"
									value="<?php echo esc_attr($options['ga_event_downloads']); ?>"
									size="50"></td>
							</tr>
							<tr>
								<td colspan="2"><hr><?php echo "<h2>" . __( "Exclude Tracking", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles title"><label for="ga_track_exclude"><?php _e("Exclude tracking for: ", 'ga-dash' ); ?></label></td>
								<td class="roles">
                               		<?php
			if (! isset ( $wp_roles )) {
				$wp_roles = new WP_Roles ();
			}
			$i = 0;
			?>
									<table>
										<tr>
									<?php
			foreach ( $wp_roles->role_names as $role => $name ) {
				$i ++;
				?>
	                                    	<td><label> <input type="checkbox"
													name="options[ga_track_exclude][]"
													value="<?php echo $role; ?>"
													<?php if (in_array($role,$options['ga_track_exclude'])) echo 'checked="checked"'; ?> />
	                                        	<?php echo $name; ?>
											</label></td>
	                                    <?php
				if ($i % 4 == 0) {
					?>
                                    			</tr>
										<tr>
                                    		<?php
				}
			}
			?>
                                    
									
									
									
									</table>
								</td>
							</tr>
							<?php
		}
		?>									
							<tr>
								<td colspan="2"><hr></td>

							</tr>
							<tr>
								<td colspan="2" class="submit"><input type="submit"
									name="Submit" class="button button-primary"
									value="<?php _e('Update Options', 'ga-dash' ) ?>" /></td>
							</tr>
						</table>
						<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>





</form>
<?php
		self::output_sidebar ();
	}
	public static function general_settings() {
		global $GADASH_Config;
		global $wp_version;
		
		/*
		 * Include Tools
		 */
		include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
		$tools = new GADASH_Tools ();
		
		if (! current_user_can ( 'manage_options' )) {
			return;
		}
		
		$options = self::set_get_options ( 'general' );
		
		/*
		 * Include GAPI
		 */
		include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
		global $GADASH_GAPI;
		
		if (isset ( $_POST ['ga_dash_code'] )) {
			try {
				$GADASH_GAPI->client->authenticate ( $_POST ['ga_dash_code'] );
				$GADASH_Config->options ['ga_dash_token'] = $GADASH_GAPI->client->getAccessToken ();
				$google_token = json_decode ( $GADASH_GAPI->client->getAccessToken () );
				$GADASH_Config->options ['ga_dash_refresh_token'] = $google_token->refresh_token;
				$GADASH_Config->set_plugin_options ();
				$message = "<div class='updated'><p><strong>" . __ ( "Plugin authorization succeeded.", 'ga-dash' ) . "</strong></p></div>";
				$options = self::set_get_options ( 'general' );
			} catch ( Google_IO_Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return false;
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) . "\nResponseHttpCode:" . $e->getCode () );
				$GADASH_GAPI->ga_dash_reset_token ( false );
			}
		}
		
		if (function_exists ( 'curl_version' )) {
			if ($GADASH_GAPI->client->getAccessToken ()) {
				if ($GADASH_Config->options ['ga_dash_profile_list']) {
					$profiles = $GADASH_Config->options ['ga_dash_profile_list'];
				} else {
					$profiles = $GADASH_GAPI->refresh_profiles ();
				}
				if ($profiles) {
					$GADASH_Config->options ['ga_dash_profile_list'] = $profiles;
					if (! $GADASH_Config->options ['ga_dash_tableid_jail']) {
						$profile = $tools->guess_default_domain ( $profiles );
						$GADASH_Config->options ['ga_dash_tableid_jail'] = $profile;
						$GADASH_Config->options ['ga_dash_tableid'] = $profile;
					}
					$GADASH_Config->set_plugin_options ();
					$options = self::set_get_options ( 'general' );
				}
			}
		}
		
		if (isset ( $_POST ['Clear'] )) {
			if (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) {
				$tools->ga_dash_clear_cache ();
				$message = "<div class='updated'><p><strong>" . __ ( "Cleared Cache.", 'ga-dash' ) . "</strong></p></div>";
			} else {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (isset ( $_POST ['Reset'] )) {
			if (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) {
				$GADASH_GAPI->ga_dash_reset_token ( true );
				$tools->ga_dash_clear_cache ();
				$message = "<div class='updated'><p><strong>" . __ ( "Token Reseted and Revoked.", 'ga-dash' ) . "</strong></p></div>";
				$options = self::set_get_options ( 'Reset' );
			} else {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (isset ( $_POST ['Log'] )) {
			$message = "<div class='updated'><p><strong>" . __ ( "Dumping log data.", 'ga-dash' ) . "</strong></p></div>";
		}
		
		if (isset ( $_POST ['options'] ['ga_dash_hidden'] ) and ! isset ( $_POST ['Clear'] ) and ! isset ( $_POST ['Reset'] ) and ! isset ( $_POST ['Log'] )) {
			$message = "<div class='updated'><p><strong>" . __ ( "Options saved.", 'ga-dash' ) . "</strong></p></div>";
			if (! (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' ))) {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (isset ( $_POST ['Hide'] )) {
			if (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) {
				$message = "<div class='updated'><p><strong>" . __ ( "All other domains/properties were removed.", 'ga-dash' ) . "</strong></p></div>";
				$lock_profile = $tools->get_selected_profile ( $GADASH_Config->options ['ga_dash_profile_list'], $GADASH_Config->options ['ga_dash_tableid_jail'] );
				$GADASH_Config->options ['ga_dash_profile_list'] = array (
						$lock_profile 
				);
				$options = self::set_get_options ( 'general' );
			} else {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (! function_exists ( 'curl_version' )) {
			$message = "<div class='error'><p><strong>" . __ ( "PHP CURL is required. Please install/enable PHP CURL!", 'ga-dash' ) . "</strong></p></div>";
		}
		
		?>
<div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Settings", 'ga-dash' ) . "</h2>"; ?><hr>
</div>

<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<div class="settings-wrapper">
				<div class="inside">
				
					<?php
		
		if (isset ( $_POST ['Authorize'] )) {
			$tools->ga_dash_clear_cache ();
			$GADASH_GAPI->token_request ();
		} else {
			if (isset ( $message ))
				echo $message;
			
			?>
					<form name="ga_dash_form" method="post"
						action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
						<input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>
						<table class="options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Plugin Authorization", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="info">
						<?php echo __("You should watch the",'ga-dash')." <a href='http://deconf.com/google-analytics-dashboard-wordpress/' target='_blank'>". __("video",'ga-dash')."</a> ".__("and read this", 'ga-dash')." <a href='http://deconf.com/google-analytics-dashboard-wordpress/' target='_blank'>". __("tutorial",'ga-dash')."</a> ".__("before proceeding to authorization. This plugin requires a properly configured Google Analytics account", 'ga-dash')."!";?>
						</td>
							</tr>
						<?php
			if (! $options ['ga_dash_token'] or $options ['ga_dash_userapi']) {
				?>
						<tr>
								<td colspan="2" class="info"><input
									name="options[ga_dash_userapi]" type="checkbox"
									id="ga_dash_userapi" value="1"
									<?php checked( $options['ga_dash_userapi'], 1 ); ?>
									onchange="this.form.submit()"
									<?php echo ($options['ga_dash_network'])?'disabled="disabled"':''; ?> /><?php _e ( " use your own API Project credentials", 'ga-dash' );?>
							</td>
							</tr>						
						
						<?php
			}
			if ($options ['ga_dash_userapi']) {
				?>
						<tr>
								<td class="title"><label for="options[ga_dash_apikey]"><?php _e("API Key:", 'ga-dash'); ?></label>
								</td>
								<td><input type="text" name="options[ga_dash_apikey]"
									value="<?php echo esc_attr($options['ga_dash_apikey']); ?>"
									size="40"></td>
							</tr>
							<tr>
								<td class="title"><label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'ga-dash'); ?></label>
								</td>
								<td><input type="text" name="options[ga_dash_clientid]"
									value="<?php echo esc_attr($options['ga_dash_clientid']); ?>"
									size="40"></td>
							</tr>
							<tr>
								<td class="title"><label for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'ga-dash'); ?></label>
								</td>
								<td><input type="text" name="options[ga_dash_clientsecret]"
									value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>"
									size="40"> <input type="hidden" name="options[ga_dash_hidden]"
									value="Y">
									<?php wp_nonce_field('gadash_form','gadash_security'); ?>
								</td>
							</tr>
						<?php
			}
			?>
							<?php
			if ($options ['ga_dash_token']) {
				?>
					<tr>
								<td colspan="2"><input type="submit" name="Reset"
									class="button button-secondary"
									value="<?php _e( "Clear Authorization", 'ga-dash' ); ?>"
									<?php echo $options['ga_dash_network']?'disabled="disabled"':''; ?> /> <input
									type="submit" name="Clear" class="button button-secondary"
									value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /></td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>

							</tr>
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "General Settings", 'ga-dash' ) . "</h2>"; ?></td>
							</tr>

							<tr>
								<td class="title"><label for="ga_dash_tableid_jail"><?php _e("Select Domain: ", 'ga-dash' ); ?></label></td>
								<td><select id="ga_dash_tableid_jail"
									name="options[ga_dash_tableid_jail]">
								<?php
				foreach ( $options ['ga_dash_profile_list'] as $items ) {
					if ($items [3]) {
						echo '<option value="' . esc_attr ( $items [1] ) . '" ' . selected ( $items [1], $options ['ga_dash_tableid_jail'] );
						echo ' title="' . __ ( "View Name:", 'ga-dash' ) . ' ' . esc_attr ( $items [0] ) . '">' . esc_html ( $tools->ga_dash_get_profile_domain ( $items [3] ) ) .' &#8658; '.esc_attr ( $items [0] ).'</option>';
					}
				}
				?>
							</select> 
							
							<?php
				if (count ( $options ['ga_dash_profile_list'] ) > 1) {
					_e ( "and/or hide all other domains", 'ga-dash' );
					?> 
								<input type="submit" name="Hide" class="button button-secondary"
									value="<?php _e( "Hide Now", 'ga-dash' ); ?>" />
							<?php
				}
				?>
							</td>
							</tr>
							<?php
				if ($options ['ga_dash_tableid_jail']) {
					?>
							<tr>
								<td class="title"></td>
								<td><?php
					$profile_info = $tools->get_selected_profile ( $GADASH_Config->options ['ga_dash_profile_list'], $GADASH_Config->options ['ga_dash_tableid_jail'] );
					echo '<pre>' . __ ( "View Name:", 'ga-dash' ) . "\t" . esc_html ( $profile_info [0] ) . "<br />" . __ ( "Tracking ID:", 'ga-dash' ) . "\t" . esc_html ( $profile_info [2] ) . "<br />" . __ ( "Default URL:", 'ga-dash' ) . "\t" . esc_html ( $profile_info [3] ) . "<br />" . __ ( "Time Zone:", 'ga-dash' ) . "\t" . esc_html ( $profile_info [5] ) . '</pre>';
					?></td>
							</tr>							
							<?php
				}
				?>

							<tr>
								<td class="title"><label for="ga_dash_style"><?php _e("Theme Color: ", 'ga-dash' ); ?></label></td>
								<td><input type="text" id="ga_dash_style" class="ga_dash_style"
									name="options[ga_dash_style]"
									value="<?php echo esc_attr($options['ga_dash_style']); ?>"
									size="10"></td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>
							</tr>
							<tr>
								<td colspan="2"><?php echo __('A new frontend widget is available! To enable it, go to','ga-dash').' <a href="widgets.php">'.__('Appearance -> Widgets').'</a> '.__('and look for Google Analytics Dashboard.','ga-dash').' '.''; ?></td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>

							</tr>
							<tr>
								<td class="debugging"><?php echo "<h2>" . __( "Debugging Data", 'ga-dash' ) . "</h2></td>".'<td><a href="#" id="show_hide" class="show_hide">Show Log</a>'; ?></td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="log_data">
								<?php
				echo '<pre class="log_data">************************************* Start Log *************************************<br/><br/>';
				$anonim = $GADASH_Config->options;
				$anonim['wp_version'] = $wp_version;
				if ($anonim ['ga_dash_token']) {
					$anonim ['ga_dash_token'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_refresh_token']) {
					$anonim ['ga_dash_refresh_token'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_clientid']) {
					$anonim ['ga_dash_clientid'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_clientsecret']) {
					$anonim ['ga_dash_clientsecret'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_apikey']) {
					$anonim ['ga_dash_apikey'] = 'HIDDEN';
				}
				echo esc_html ( print_r ( $anonim, true ) );
				echo '<br/>Last Error: ';
				echo esc_html ( print_r ( get_option ( 'gadash_lasterror', 'N/A' ), true ) );
				echo '<br/><br/>************************************* End Log *************************************</pre>';
				?>
								</div>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>
							</tr>
							<tr>
								<td colspan="2" class="submit"><input type="submit"
									name="Submit" class="button button-primary"
									value="<?php _e('Update Options', 'ga-dash' ) ?>" /></td>
							</tr>
					
		<?php
			} else {
				?>
							<tr>
								<td colspan="2"><hr></td>

							</tr>
							<tr>
								<td colspan="2"><input type="submit" name="Authorize"
									class="button button-secondary" id="authorize"
									value="<?php _e( "Authorize Plugin", 'ga-dash' ); ?>"
									<?php echo ((!function_exists('curl_version') OR ($options['ga_dash_network']))?'disabled="disabled"':''); ?> />
									<input type="submit" name="Clear"
									class="button button-secondary"
									value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /></td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>

							</tr>
							<tr>
								<td class="debugging"><?php echo "<h2>" . __( "Debugging Data", 'ga-dash' ) . "</h2></td>".'<td><a href="#" id="show_hide" class="show_hide">Show Log</a>'; ?></td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="log_data">
								<?php
				echo '<pre class="log_data">************************************* Start Log *************************************<br/><br/>';
				$anonim = $GADASH_Config->options;
				$anonim['wp_version'] = $wp_version;
				if ($anonim ['ga_dash_token']) {
					$anonim ['ga_dash_token'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_refresh_token']) {
					$anonim ['ga_dash_refresh_token'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_clientid']) {
					$anonim ['ga_dash_clientid'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_clientsecret']) {
					$anonim ['ga_dash_clientsecret'] = 'HIDDEN';
				}
				if ($anonim ['ga_dash_apikey']) {
					$anonim ['ga_dash_apikey'] = 'HIDDEN';
				}
				print_r ( $anonim );
				echo '<br/>Last Error: ';
				echo esc_html ( print_r ( get_option ( 'gadash_lasterror', 'N/A' ), true ) );
				echo '<br/><br/>************************************* End Log *************************************</pre>';
				?>
								</div>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr></td>

							</tr>
						</table>
					</form>
			<?php
				self::output_sidebar ();
				return;
			}
			?>						

					</table>
					</form>

<?php
		}
		self::output_sidebar ();
	}
	
	// Network Settings
	public static function general_settings_network() {
		global $GADASH_Config;
		global $wp_version;
		/*
		 * Include Tools
		 */
		include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
		$tools = new GADASH_Tools ();
		
		if (! current_user_can ( 'manage_network_options' )) {
			return;
		}
		
		$options = self::set_get_options ( 'network' );
		
		/*
		 * Include GAPI
		 */
		include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
		global $GADASH_GAPI;
		
		if (isset ( $_POST ['ga_dash_code'] )) {
			try {
				$GADASH_GAPI->client->authenticate ( $_POST ['ga_dash_code'] );
				$GADASH_Config->options ['ga_dash_token'] = $GADASH_GAPI->client->getAccessToken ();
				$google_token = json_decode ( $GADASH_GAPI->client->getAccessToken () );
				$GADASH_Config->options ['ga_dash_refresh_token'] = $google_token->refresh_token;
				$GADASH_Config->set_plugin_options ( true );
				$message = "<div class='updated'><p><strong>" . __ ( "Plugin authorization succeeded.", 'ga-dash' ) . "</strong></p></div>";
				$options = self::set_get_options ( 'network' );
			} catch ( Google_IO_Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return false;
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) . "\nResponseHttpCode:" . $e->getCode () );
				$GADASH_GAPI->ga_dash_reset_token ( false );
			}
		}
		
		if (isset ( $_POST ['Refresh'] )) {
			if (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) {
				$GADASH_Config->options ['ga_dash_profile_list'] = '';
				$message = "<div class='updated'><p><strong>" . __ ( "Properties refreshed.", 'ga-dash' ) . "</strong></p></div>";
				$options = self::set_get_options ( 'network' );
			} else {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (function_exists ( 'curl_version' )) {
			if ($GADASH_GAPI->client->getAccessToken ()) {
				if ($GADASH_Config->options ['ga_dash_profile_list']) {
					$profiles = $GADASH_Config->options ['ga_dash_profile_list'];
				} else {
					$profiles = $GADASH_GAPI->refresh_profiles ();
				}
				if ($profiles) {
					$GADASH_Config->options ['ga_dash_profile_list'] = $profiles;
					if (isset($GADASH_Config->options ['ga_dash_tableid_jail']) AND ! $GADASH_Config->options ['ga_dash_tableid_jail']) {
						$profile = $tools->guess_default_domain ( $profiles );
						$GADASH_Config->options ['ga_dash_tableid_jail'] = $profile;
						$GADASH_Config->options ['ga_dash_tableid'] = $profile;
					}
					$GADASH_Config->set_plugin_options ( true );
					$options = self::set_get_options ( 'network' );
				}
			}
		}
		
		if (isset ( $_POST ['Clear'] )) {
			if (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) {
				$tools->ga_dash_clear_cache ();
				$message = "<div class='updated'><p><strong>" . __ ( "Cleared Cache.", 'ga-dash' ) . "</strong></p></div>";
			} else {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (isset ( $_POST ['Reset'] )) {
			if (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) {
				$GADASH_GAPI->ga_dash_reset_token ( true );
				$tools->ga_dash_clear_cache ();
				$message = "<div class='updated'><p><strong>" . __ ( "Token Reseted and Revoked.", 'ga-dash' ) . "</strong></p></div>";
				$options = self::set_get_options ( 'Reset' );
			} else {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (isset ( $_POST ['Log'] )) {
			$message = "<div class='updated'><p><strong>" . __ ( "Dumping log data.", 'ga-dash' ) . "</strong></p></div>";
		}
		
		if (isset ( $_POST ['options'] ['ga_dash_hidden'] ) and ! isset ( $_POST ['Clear'] ) and ! isset ( $_POST ['Reset'] ) and ! isset ( $_POST ['Log'] ) and ! isset ( $_POST ['Refresh'] )) {
			$message = "<div class='updated'><p><strong>" . __ ( "Options saved.", 'ga-dash' ) . "</strong></p></div>";
			if (! (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' ))) {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (isset ( $_POST ['Hide'] )) {
			if (isset ( $_POST ['gadash_security'] ) && wp_verify_nonce ( $_POST ['gadash_security'], 'gadash_form' )) {
				$message = "<div class='updated'><p><strong>" . __ ( "All other domains/properties were removed.", 'ga-dash' ) . "</strong></p></div>";
				$lock_profile = $tools->get_selected_profile ( $GADASH_Config->options ['ga_dash_profile_list'], $GADASH_Config->options ['ga_dash_tableid_jail'] );
				$GADASH_Config->options ['ga_dash_profile_list'] = array (
						$lock_profile 
				);
				$options = self::set_get_options ( 'network' );
			} else {
				$message = "<div class='error'><p><strong>" . __ ( "Cheating Huh?", 'ga-dash' ) . "</strong></p></div>";
			}
		}
		
		if (! function_exists ( 'curl_version' )) {
			$message = "<div class='error'><p><strong>" . __ ( "PHP CURL is required. Please install/enable PHP CURL!", 'ga-dash' ) . "</strong></p></div>";
		}
		
		?>
	<div class="wrap">
		<?php echo "<h2>" . __( "Google Analytics Settings", 'ga-dash' ) . "</h2>"; ?><hr>
					</div>

					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content">
								<div class="settings-wrapper">
									<div class="inside">
					
						<?php
		
		if (isset ( $_POST ['Authorize'] )) {
			$tools->ga_dash_clear_cache ();
			$GADASH_GAPI->token_request ();
		} else {
			if (isset ( $message ))
				echo $message;
			
			?>
						<form name="ga_dash_form" method="post"
											action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
											<input type="hidden" name="options[ga_dash_hidden]" value="Y">
							<?php wp_nonce_field('gadash_form','gadash_security'); ?>
							<table class="options">
												<tr>
													<td colspan="2"><?php echo "<h2>" . __( "Network Setup", 'ga-dash' ) . "</h2>"; ?></td>
												</tr>
												<tr>
													<td colspan="2" class="title">

														<div class="onoffswitch">
															<input type="checkbox" name="options[ga_dash_network]"
																value="1" class="onoffswitch-checkbox"
																id="ga_dash_network"
																<?php checked( $options['ga_dash_network'], 1); ?>
																onchange="this.form.submit()"> <label
																class="onoffswitch-label" for="ga_dash_network">
																<div class="onoffswitch-inner"></div>
																<div class="onoffswitch-switch"></div>
															</label>
														</div>
														<div class="switch-desc"><?php _e ( " use a single Google Analytics account for the entire network", 'ga-dash' );?></div>

													</td>
												</tr>
								<?php if ($options['ga_dash_network']){  //Network Mode check?>																	
								<tr>
													<td colspan="2"><hr></td>

												</tr>

												<tr>
													<td colspan="2"><?php echo "<h2>" . __( "Plugin Authorization", 'ga-dash' ) . "</h2>"; ?></td>
												</tr>
												<tr>
													<td colspan="2" class="info">
							<?php echo __("You should watch the",'ga-dash')." <a href='http://deconf.com/google-analytics-dashboard-wordpress/' target='_blank'>". __("video",'ga-dash')."</a> ".__("and read this", 'ga-dash')." <a href='http://deconf.com/google-analytics-dashboard-wordpress/' target='_blank'>". __("tutorial",'ga-dash')."</a> ".__("before proceeding to authorization. This plugin requires a properly configured Google Analytics account", 'ga-dash')."!";?>
							</td>
												</tr>
							<?php
				if (! $options ['ga_dash_token'] or $options ['ga_dash_userapi']) {
					?>
							<tr>
													<td colspan="2" class="info"><input
														name="options[ga_dash_userapi]" type="checkbox"
														id="ga_dash_userapi" value="1"
														<?php checked( $options['ga_dash_userapi'], 1 ); ?>
														onchange="this.form.submit()" /><?php _e ( " use your own API Project credentials", 'ga-dash' );?>
								</td>
												</tr>
								
							<?php
				}
				if ($options ['ga_dash_userapi']) {
					?>
							<tr>
													<td class="title"><label for="options[ga_dash_apikey]"><?php _e("API Key:", 'ga-dash'); ?></label>
													</td>
													<td><input type="text" name="options[ga_dash_apikey]"
														value="<?php echo esc_attr($options['ga_dash_apikey']); ?>"
														size="40"></td>
												</tr>
												<tr>
													<td class="title"><label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'ga-dash'); ?></label>
													</td>
													<td><input type="text" name="options[ga_dash_clientid]"
														value="<?php echo esc_attr($options['ga_dash_clientid']); ?>"
														size="40"></td>
												</tr>
												<tr>
													<td class="title"><label
														for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'ga-dash'); ?></label>
													</td>
													<td><input type="text" name="options[ga_dash_clientsecret]"
														value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>"
														size="40"> <input type="hidden"
														name="options[ga_dash_hidden]" value="Y">
										<?php wp_nonce_field('gadash_form','gadash_security'); ?>
									</td>
												</tr>
							<?php
				}
				?>
								<?php
				if ($options ['ga_dash_token']) {
					?>
						<tr>
													<td colspan="2"><input type="submit" name="Reset"
														class="button button-secondary"
														value="<?php _e( "Clear Authorization", 'ga-dash' ); ?>" />
														<input type="submit" name="Clear"
														class="button button-secondary"
														value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /> <input
														type="submit" name="Refresh"
														class="button button-secondary"
														value="<?php _e( "Refresh Properties", 'ga-dash' ); ?>" /></td>
												</tr>

												<tr>
													<td colspan="2"><hr></td>

												</tr>
												<tr>
													<td colspan="2"><?php echo "<h2>" . __( "Properties/Views Settings", 'ga-dash' ) . "</h2>"; ?></td>
												</tr>
								<?php
					if (isset($options ['ga_dash_tableid_network'])){			
						$options ['ga_dash_tableid_network'] = json_decode ( json_encode ( $options ['ga_dash_tableid_network'] ), FALSE );
					}	
					foreach ( wp_get_sites () as $blog ) {
						?>
							<tr>
													<td class="title-select"><label
														for="ga_dash_tableid_network"><?php echo '<strong>'.$blog['domain'].$blog['path'].'</strong>: ';?></label></td>
													<td><select id="ga_dash_tableid_network"
														name="options[ga_dash_tableid_network][<?php echo $blog['blog_id'];?>]">
									<?php
						foreach ( $options ['ga_dash_profile_list'] as $items ) {
							if ($items [3]) {
								echo '<option value="' . esc_attr ( $items [1] ) . '" ' . selected ( $items [1], isset($options ['ga_dash_tableid_network']->$blog ['blog_id'])?$options ['ga_dash_tableid_network']->$blog ['blog_id']:'' );
								echo ' title="' . __ ( "View Name:", 'ga-dash' ) . ' ' . esc_attr ( $items [0] ) . '">' . $tools->ga_dash_get_profile_domain ( $items [3] ) .' &#8658; '.esc_attr ( $items [0] ).'</option>';
							}
						}
						?>
								</select><br /></td>
												</tr>								
							<?php
					}
					?>								
								<tr>
													<td colspan="2"><hr></td>

												</tr>
												<tr>
													<td class="debugging"><?php echo "<h2>" . __( "Debugging Data", 'ga-dash' ) . "</h2></td>".'<td><a href="#" id="show_hide" class="show_hide">Show Log</a>'; ?></td>
												</tr>
												<tr>
													<td colspan="2">
														<div class="log_data">
									<?php
					echo '<pre class="log_data">************************************* Start Log *************************************<br/><br/>';
					$anonim = $GADASH_Config->options;
					$anonim['wp_version'] = $wp_version;
					if ($anonim ['ga_dash_token']) {
						$anonim ['ga_dash_token'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_refresh_token']) {
						$anonim ['ga_dash_refresh_token'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_clientid']) {
						$anonim ['ga_dash_clientid'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_clientsecret']) {
						$anonim ['ga_dash_clientsecret'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_apikey']) {
						$anonim ['ga_dash_apikey'] = 'HIDDEN';
					}
					print_r ( $anonim );
					echo '<br/>Last Error: ';
					echo esc_html ( print_r ( get_option ( 'gadash_lasterror', 'N/A' ), true ) );
					echo '<br/><br/>************************************* End Log *************************************</pre>';
					?>
									</div>
													</td>
												</tr>
												<tr>
													<td colspan="2"><hr></td>
												</tr>
												<tr>
													<td colspan="2" class="submit"><input type="submit"
														name="Submit" class="button button-primary"
														value="<?php _e('Update Options', 'ga-dash' ) ?>" /></td>
												</tr>
						
			<?php
				} else {
					?>
					
								<tr>
													<td colspan="2"><hr></td>

												</tr>

												<tr>
													<td colspan="2"><input type="submit" name="Authorize"
														class="button button-secondary" id="authorize"
														value="<?php _e( "Authorize Plugin", 'ga-dash' ); ?>"
														<?php echo (!function_exists('curl_version')?'disabled="disabled"':''); ?> />
														<input type="submit" name="Clear"
														class="button button-secondary"
														value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /></td>
												</tr>
												<tr>
													<td colspan="2"><hr></td>

												</tr>
												<tr>
													<td class="debugging"><?php echo "<h2>" . __( "Debugging Data", 'ga-dash' ) . "</h2></td>".'<td><a href="#" id="show_hide" class="show_hide">Show Log</a>'; ?></td>
												</tr>
												<tr>
													<td colspan="2">
														<div class="log_data">
									<?php
					echo '<pre class="log_data">************************************* Start Log *************************************<br/><br/>';
					$anonim = $GADASH_Config->options;
					$anonim['wp_version'] = $wp_version;
					if ($anonim ['ga_dash_token']) {
						$anonim ['ga_dash_token'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_refresh_token']) {
						$anonim ['ga_dash_refresh_token'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_clientid']) {
						$anonim ['ga_dash_clientid'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_clientsecret']) {
						$anonim ['ga_dash_clientsecret'] = 'HIDDEN';
					}
					if ($anonim ['ga_dash_apikey']) {
						$anonim ['ga_dash_apikey'] = 'HIDDEN';
					}
					echo esc_html ( print_r ( $anonim, true ) );
					echo '<br/>Last Error: ';
					echo esc_html ( print_r ( get_option ( 'gadash_lasterror', 'N/A' ), true ) );
					echo '<br/><br/>************************************* End Log *************************************</pre>';
					?>
									</div>
													</td>
												</tr>
								   <?php }  //Network Mode check?>									
									<tr>
													<td colspan="2"><hr></td>

												</tr>
											</table>
										</form>
				<?php
				self::output_sidebar ();
				return;
			}
			?>						
	
						</table>
										</form>
	
	<?php
		}
		self::output_sidebar ();
	}
	public static function output_sidebar() {
		?>
</div>
								</div>
							</div>

							<div id="postbox-container-1" class="postbox-container">
								<div class="meta-box-sortables">
									<div class="postbox">
										<h3>
											<span><?php _e("Setup Tutorial & Demo",'ga-dash') ?></span>
										</h3>
										<div class="inside">
											<a
												href="http://deconf.com/google-analytics-dashboard-wordpress/"
												target="_blank"><img
												src="<?php echo plugins_url( 'images/google-analytics-dashboard.png' , __FILE__ );?>"
												width="100%" alt="" /></a>
										</div>
									</div>
									<div class="postbox">
										<h3>
											<span><?php _e("Support & Reviews",'ga-dash')?></span>
										</h3>
										<div class="inside">
											<div class="gadash-title">
												<a
													href="http://deconf.com/google-analytics-dashboard-wordpress/"><img
													src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" /></a>
											</div>
											<div class="gadash-desc"><?php echo  __('You can find support on', 'ga-dash') . ' <a href="http://deconf.com/ask/">'.__('DeConf Help Center', 'ga-dash').'</a>.'; ?></div>
											<br />
											<div class="gadash-title">
												<a
													href="http://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp#plugin-info"><img
													src="<?php echo plugins_url( 'images/star.png' , __FILE__ ); ?>" /></a>
											</div>
											<div class="gadash-desc"><?php echo  __('Your feedback and review are both important,', 'ga-dash').' <a href="http://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp#plugin-info">'.__('rate this plugin', 'ga-dash').'</a>!'; ?></div>
										</div>
									</div>
									<div class="postbox">
										<h3>
											<span><?php _e("Further Reading",'ga-dash')?></span>
										</h3>
										<div class="inside">
											<div class="gadash-title">
												<a href="http://deconf.com/wordpress/"><img
													src="<?php echo plugins_url( 'images/wp.png' , __FILE__ ); ?>" /></a>
											</div>
											<div class="gadash-desc"><?php echo  __('Other', 'ga-dash').' <a href="http://deconf.com/wordpress/">'.__('WordPress Plugins', 'ga-dash').'</a> '.__('written by the same author', 'ga-dash').'.'; ?></div>
											<br />
											<div class="gadash-title">
												<a href="http://deconf.com/clicky-web-analytics-review/"><img
													src="<?php echo plugins_url( 'images/clicky.png' , __FILE__ ); ?>" /></a>
											</div>
											<div class="gadash-desc"><?php echo  '<a href="http://deconf.com/clicky-web-analytics-review/">'.__('Web Analytics', 'ga-dash').'</a> '.__('service with visitors tracking at IP level.', 'ga-dash'); ?></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
<?php
	}
}
