<?php
/**
 * Author: Alin Marcu
 * Copyright 2017 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
?>
<form name="input" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
	<table class="aiwp-settings-options">
		<tr>
			<td colspan="2" class="aiwp-settings-info">
						<?php $target = $data['user_api'] ? '_blank' : '' ?>
						<?php echo __( "Use this link to get your <strong>one-time-use</strong> access code:", 'analytics-insights' ) . ' <a href="' . $data['authUrl'] . '" id="gapi-access-code" target="' . $target . '">' . __ ( "Get Access Code", 'analytics-insights' ) . '</a>.'; ?>
			</td>
		</tr>
		<?php if ( $data['user_api'] ) :?>
		<tr>
			<td class="aiwp-settings-title">
				<label for="aiwp_access_code" title="<?php _e("Use the red link to get your access code! You need to generate a new one each time you authorize!",'analytics-insights')?>"><?php echo _e( "Access Code:", 'analytics-insights' ); ?></label>
			</td>
			<td>
				<input type="text" id="aiwp_access_code" name="aiwp_access_code" value="" size="61" autocomplete="off" pattern=".\/.{30,}" required="required" title="<?php _e("Use the red link to get your access code! You need to generate a new one each time you authorize!",'analytics-insights')?>">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" class="button button-secondary" name="aiwp_authorize" value="<?php _e( "Save Access Code", 'analytics-insights' ); ?>" />
			</td>
		</tr>
		<?php endif; ?>
	</table>
</form>
