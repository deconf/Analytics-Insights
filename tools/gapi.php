<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists ( 'GADASH_GAPI' )) {
	// set_include_path(get_include_path() . PATH_SEPARATOR . dirname ( __FILE__ ));
	class GADASH_GAPI {
		public $client, $service;
		public $country_codes;
		public $timeshift;
		function __construct() {
			global $GADASH_Config;
			if (! function_exists ( 'curl_version' )) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': CURL disabled. Please enable CURL!' );
				return;
			}
			// If at least PHP 5.3.2 use the autoloader, if not try to edit the include_path
			if (version_compare ( PHP_VERSION, '5.3.2' ) >= 0) {
				require 'vendor/autoload.php';
			} else {
				set_include_path ( $GADASH_Config->plugin_path . '/tools/src/' . PATH_SEPARATOR . get_include_path () );
				// Include GAPI client
				if (! class_exists ( 'Google_Client' )) {
					require_once 'Google/Client.php';
				}
				// Include GAPI Analytics Service
				if (! class_exists ( 'Google_Service_Analytics' )) {
					require_once 'Google/Service/Analytics.php';
				}
			}

			$this->client = new Google_Client ();
			$this->client->setScopes ( 'https://www.googleapis.com/auth/analytics.readonly' );
			$this->client->setAccessType ( 'offline' );
			$this->client->setApplicationName ( 'Google Analytics Dashboard' );
			$this->client->setRedirectUri ( 'urn:ietf:wg:oauth:2.0:oob' );

			if ($GADASH_Config->options ['ga_dash_userapi']) {
				$this->client->setClientId ( $GADASH_Config->options ['ga_dash_clientid'] );
				$this->client->setClientSecret ( $GADASH_Config->options ['ga_dash_clientsecret'] );
				$this->client->setDeveloperKey ( $GADASH_Config->options ['ga_dash_apikey'] );
			} else {
				$this->client->setClientId ( '65556128781.apps.googleusercontent.com' );
				$this->client->setClientSecret ( 'Kc7888wgbc_JbeCpbFjnYpwE' );
				$this->client->setDeveloperKey ( 'AIzaSyBG7LlUoHc29ZeC_dsShVaBEX15SfRl_WY' );
			}

			$this->service = new Google_Service_Analytics ( $this->client );

			if ($GADASH_Config->options ['ga_dash_token']) {
				$token = $GADASH_Config->options ['ga_dash_token'];
				$token = $this->ga_dash_refresh_token ();
				if ($token) {
					$this->client->setAccessToken ( $token );
				}
			}
		}
		function get_timeouts($daily) {
			$local_time = time () + $this->timeshift;
			if ($daily) {
				$nextday = explode ( '-', date ( 'n-j-Y', strtotime ( ' +1 day', $local_time ) ) );
				$midnight = mktime ( 0, 0, 0, $nextday [0], $nextday [1], $nextday [2] );
				return $midnight - $local_time;
			} else {
				$nexthour = explode ( '-', date ( 'H-n-j-Y', strtotime ( ' +1 hour', $local_time ) ) );
				$newhour = mktime ( $nexthour [0], 0, 0, $nexthour [1], $nexthour [2], $nexthour [3] );
				return $newhour - $local_time;
			}
		}
		function token_request() {
			$authUrl = $this->client->createAuthUrl ();

			?>
<form name="input"
	action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
	<table class="options">
		<tr>
			<td colspan="2" class="info">
						<?php echo __( "Use this link to get your access code:", 'ga-dash' ) . ' <a href="' . $authUrl . '" target="_blank">' . __ ( "Get Access Code", 'ga-dash' ) . '</a>'; ?>
					</td>
		</tr>
		<tr>
			<td class="title"><label for="ga_dash_code"><?php echo _e( "Access Code:", 'ga-dash' ); ?></label>
			</td>
			<td><input type="text" id="ga_dash_code" name="ga_dash_code" value=""
				size="61"></td>
		</tr>
		<tr>
			<td colspan="2"><hr></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" class="button button-secondary"
				name="ga_dash_authorize"
				value="<?php _e( "Save Access Code", 'ga-dash' ); ?>" /></td>
		</tr>
	</table>
</form>
<?php
		}
		function refresh_profiles() {
			try {
				$profiles = $this->service->management_profiles->listManagementProfiles ( '~all', '~all' );
				$items = $profiles->getItems ();
				if (count ( $items ) != 0) {
					$ga_dash_profile_list = array ();
					foreach ( $items as $profile ) {
						$timetz = new DateTimeZone ( $profile->getTimezone () );
						$localtime = new DateTime ( 'now', $timetz );
						$timeshift = strtotime ( $localtime->format ( 'Y-m-d H:i:s' ) ) - time ();
						$ga_dash_profile_list [] = array (
								$profile->getName (),
								$profile->getId (),
								$profile->getwebPropertyId (),
								$profile->getwebsiteUrl (),
								$timeshift,
								$profile->getTimezone ()
						);
					}
					update_option ( 'gadash_lasterror', 'N/A' );
					return ($ga_dash_profile_list);
				} else {
					update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': No properties were found in this account!' );
				}
			} catch ( Google_IO_Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return false;
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				$this->ga_dash_reset_token ( true );
			}
		}
		function ga_dash_refresh_token() {
			global $GADASH_Config;
			try {
				if (is_multisite () && $GADASH_Config->options ['ga_dash_network']) {
					$transient = get_site_transient ( "ga_dash_refresh_token" );
				} else {
					$transient = get_transient ( "ga_dash_refresh_token" );
				}
				if (empty ( $transient )) {

					if (! $GADASH_Config->options ['ga_dash_refresh_token']) {
						$google_token = json_decode ( $GADASH_Config->options ['ga_dash_token'] );
						$GADASH_Config->options ['ga_dash_refresh_token'] = $google_token->refresh_token;
						$this->client->refreshToken ( $google_token->refresh_token );
					} else {
						$this->client->refreshToken ( $GADASH_Config->options ['ga_dash_refresh_token'] );
					}

					$token = $this->client->getAccessToken ();
					$google_token = json_decode ( $token );
					$GADASH_Config->options ['ga_dash_token'] = $token;
					if (is_multisite () && $GADASH_Config->options ['ga_dash_network']) {
						set_site_transient ( "ga_dash_refresh_token", $token, $google_token->expires_in );
						$GADASH_Config->set_plugin_options ( true );
					} else {
						set_transient ( "ga_dash_refresh_token", $token, $google_token->expires_in );
						$GADASH_Config->set_plugin_options ();
					}
					return $token;
				} else {
					return $transient;
				}
			} catch ( Google_IO_Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return false;
			} catch ( Exception $e ) {
				$this->ga_dash_reset_token ( false );
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return false;
			}
		}
		function ga_dash_reset_token($all = true) {
			global $GADASH_Config;
			if (is_multisite () && $GADASH_Config->options ['ga_dash_network']) {
				delete_site_transient ( 'ga_dash_refresh_token' );
			} else {
				delete_transient ( 'ga_dash_refresh_token' );
			}
			$GADASH_Config->options ['ga_dash_token'] = "";
			$GADASH_Config->options ['ga_dash_refresh_token'] = "";

			if ($all) {
				$GADASH_Config->options ['ga_dash_tableid'] = "";
				$GADASH_Config->options ['ga_dash_tableid_jail'] = "";
				$GADASH_Config->options ['ga_dash_profile_list'] = "";
				try {
					$this->client->revokeToken ();
				} catch ( Exception $e ) {
					if (is_multisite () && $GADASH_Config->options ['ga_dash_network']) {
						$GADASH_Config->set_plugin_options ( true );
					} else {
						$GADASH_Config->set_plugin_options ();
					}
				}
			}

			if (is_multisite () && $GADASH_Config->options ['ga_dash_network']) {
				$GADASH_Config->set_plugin_options ( true );
			} else {
				$GADASH_Config->set_plugin_options ();
			}
		}

		// Get Main Chart
		function ga_dash_main_charts($projectId, $period, $from, $to, $query) {
			global $GADASH_Config;

			$metrics = 'ga:' . $query;

			if ($period == "today") {
				$dimensions = 'ga:hour';
				$timeouts = 0;
			} else if ($period == "yesterday") {
				$dimensions = 'ga:hour';
				$timeouts = 1;
			} else {
				$dimensions = 'ga:date,ga:dayOfWeekName';
				$timeouts = 1;
			}

			try {
				$serial = 'gadash_qr2' . str_replace ( array (
						'ga:',
						',',
						'-'
				), "", $projectId . $from . $metrics );
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}

			$ga_dash_statsdata = "";

			if ($period == "today" or $period == "yesterday") {
				for($i = 0; $i < $data ['totalResults']; $i ++) {
					$ga_dash_statsdata .= "['" . $data ['rows'] [$i] [0] . ":00'," . round ( $data ['rows'] [$i] [1], 2 ) . "],";
				}
			} else {
				for($i = 0; $i < $data ['totalResults']; $i ++) {
					$ga_dash_statsdata .= "['" . ucfirst(__($data ['rows'] [$i] [1])) . ', ' . substr_replace(substr_replace($data ['rows'] [$i] [0] , '-', 4, 0), '-', 7, 0) . "'," . round ( $data ['rows'] [$i] [2], 2 ) . "],";
				}
			}

			return wp_kses ( rtrim ( $ga_dash_statsdata, ',' ), $GADASH_Config->allowed_html );
		}

		// Get bottom Stats
		function ga_dash_bottom_stats($projectId, $period, $from, $to) {
			global $GADASH_Config;

			if ($period == "today") {
				$timeouts = 0;
			} else {
				$timeouts = 1;
			}

			$metrics = 'ga:visits,ga:visitors,ga:pageviews,ga:visitBounceRate,ga:organicSearches,ga:timeOnSite';
			$dimensions = 'ga:year';
			try {
				$serial = 'gadash_qr3' . $projectId . $from;
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}

			if (isset ( $data ['rows'] [1] [1] )) {
				for($i = 1; $i < 6; $i ++) {
					$data ['rows'] [0] [$i] += $data ['rows'] [1] [$i];
					if ($i == 4) {
						$data ['rows'] [0] [$i] = $data ['rows'] [0] [$i] / 2;
					}
				}
			}

			return $data;
		}

		// Get Top Pages
		function ga_dash_top_pages($projectId, $from, $to) {
			global $GADASH_Config;

			$metrics = 'ga:pageviews';
			$dimensions = 'ga:pageTitle,ga:hostname,ga:pagePath';

			if ($from == "today") {
				$timeouts = 0;
			} else {
				$timeouts = 1;
			}

			try {
				$serial = 'gadash_qr4' . $projectId . $from;
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions,
							'sort' => '-ga:pageviews',
							'max-results' => '24'
					) ); // 'filters' => 'ga:pagePath!=/'
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}
			if (! isset ( $data ['rows'] )) {
				return 0;
			}

			$ga_dash_data = "";
			$i = 0;
			// print_r($data ['rows'] );
			while ( isset ( $data ['rows'] [$i] [0] ) ) {
				$ga_dash_data .= "['<a href=\"http://" . addslashes ( $data ['rows'] [$i] [1] . $data ['rows'] [$i] [2] ) . "\" target=\"_blank\">" . addslashes ( $data ['rows'] [$i] [0] ) . "</a>'," . $data ['rows'] [$i] [3] . "],";
				$i ++;
			}
			return wp_kses ( rtrim ( $ga_dash_data, ',' ), $GADASH_Config->allowed_html );
		}

		// Get Top referrers
		function ga_dash_top_referrers($projectId, $from, $to) {
			global $GADASH_Config;

			$metrics = 'ga:visits';
			$dimensions = 'ga:source,ga:fullReferrer,ga:medium';

			if ($from == "today") {
				$timeouts = 0;
			} else {
				$timeouts = 1;
			}

			try {
				$serial = 'gadash_qr5' . $projectId . $from;
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions,
							'sort' => '-ga:visits',
							'max-results' => '24',
							'filters' => 'ga:medium==referral'
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}
			if (! isset ( $data ['rows'] )) {
				return 0;
			}

			$ga_dash_data = "";
			$i = 0;
			while ( isset ( $data ['rows'] [$i] [0] ) ) {
				$ga_dash_data .= "['<a href=\"http://" . addslashes ( $data ['rows'] [$i] [1] ) . "\" target=\"_blank\">" . addslashes ( $data ['rows'] [$i] [0] ) . "</a>'," . $data ['rows'] [$i] [3] . "],";
				$i ++;
			}

			return wp_kses ( rtrim ( $ga_dash_data, ',' ), $GADASH_Config->allowed_html );
		}

		// Get Top searches
		function ga_dash_top_searches($projectId, $from, $to) {
			global $GADASH_Config;

			$metrics = 'ga:visits';
			$dimensions = 'ga:keyword';

			if ($from == "today") {
				$timeouts = 0;
			} else {
				$timeouts = 1;
			}

			try {
				$serial = 'gadash_qr6' . $projectId . $from;
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions,
							'sort' => '-ga:visits',
							'max-results' => '24'
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}
			if (! isset ( $data ['rows'] )) {
				return 0;
			}

			$ga_dash_data = "";
			$i = 0;
			while ( isset ( $data ['rows'] [$i] [0] ) ) {
				if ($data ['rows'] [$i] [0] != "(not set)") {
					$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
				}
				$i ++;
			}

			return wp_kses ( rtrim ( $ga_dash_data, ',' ), $GADASH_Config->allowed_html );
		}
		// Get Visits by Country
		function ga_dash_visits_country($projectId, $from, $to) {
			global $GADASH_Config;

			$metrics = 'ga:visits';
			$options = "";

			if ($from == "today") {
				$timeouts = 0;
			} else {
				$timeouts = 1;
			}

			if ($GADASH_Config->options ['ga_target_geomap']) {
				$dimensions = 'ga:city, ga:region';
				$this->getcountrycodes ();
				$filters = 'ga:country==' . ($this->country_codes [$GADASH_Config->options ['ga_target_geomap']]);
			} else {
				$dimensions = 'ga:country';
				$filters = "";
			}
			try {
				if ($GADASH_Config->options ['ga_target_geomap']) {
					$serial = 'gadash_qr7' . $projectId . $from . $GADASH_Config->options ['ga_target_geomap'] . $GADASH_Config->options ['ga_target_number'];
				} else {
					$serial = 'gadash_qr7' . $projectId . $from;
				}
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					if ($filters){
						$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
								'dimensions' => $dimensions,
								'filters' => $filters,
								'sort' => '-ga:visits',
								'max-results' => $GADASH_Config->options ['ga_target_number']
						) );
					}else{
						$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
								'dimensions' => $dimensions
						) );
					}
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}
			if (! isset ( $data ['rows'] )) {
				return 0;
			}

			$ga_dash_data = "";
			$i = 0;
			while ( isset ( $data ['rows'] [$i] [1] ) ) {
				if (isset($data ['rows'] [$i] [2])){
					$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) .", ". addslashes ( $data ['rows'] [$i] [1] ) ."'," . $data ['rows'] [$i] [2] . "],";
				}else{
					$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
				}
				$i ++;
			}

			return rtrim ( $ga_dash_data, ',' );
		}
		// Get Traffic Sources
		function ga_dash_traffic_sources($projectId, $from, $to) {
			global $GADASH_Config;

			$metrics = 'ga:visits';
			$dimensions = 'ga:medium';

			if ($from == "today") {
				$timeouts = 0;
			} else {
				$timeouts = 1;
			}

			try {
				$serial = 'gadash_qr8' . $projectId . $from;
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}
			if (! isset ( $data ['rows'] )) {
				return 0;
			}

			$ga_dash_data = "";
			for($i = 0; $i < $data ['totalResults']; $i ++) {
				$ga_dash_data .= "['" . str_replace ( "(none)", "direct", $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
			}

			return wp_kses ( rtrim ( $ga_dash_data, ',' ), $GADASH_Config->allowed_html );
		}

		// Get New vs. Returning
		function ga_dash_new_return($projectId, $from, $to) {
			global $GADASH_Config;

			$metrics = 'ga:visits';
			$dimensions = 'ga:visitorType';

			if ($from == "today") {
				$timeouts = 0;
			} else {
				$timeouts = 1;
			}

			try {
				$serial = 'gadash_qr9' . $projectId . $from;
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( $timeouts ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return 0;
			}
			if (! isset ( $data ['rows'] )) {
				return 0;
			}

			$ga_dash_data = "";
			for($i = 0; $i < $data ['totalResults']; $i ++) {
				$ga_dash_data .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
			}

			return wp_kses ( rtrim ( $ga_dash_data, ',' ), $GADASH_Config->allowed_html );
		}

		// Frontend Widget Stats
		function frontend_widget_stats($projectId, $period, $anonim, $display) {
			global $GADASH_Config;

			$content = '';
			$from = $period;
			$to = 'yesterday';
			$metrics = 'ga:visits';
			$dimensions = 'ga:date,ga:dayOfWeekName';

			$title = __ ( "Visits", 'ga-dash' ) . ($anonim ? __ ( "&#39; trend", 'ga-dash' ) : '');

			/*
			 * Include Tools
			 */
			include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
			$tools = new GADASH_Tools ();

			if (isset ( $GADASH_Config->options ['ga_dash_style'] )) {
				$css = "colors:['" . $GADASH_Config->options ['ga_dash_style'] . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 20 ) . "'],";
				$color = $GADASH_Config->options ['ga_dash_style'];
			} else {
				$css = "";
				$color = "#3366CC";
			}

			try {

				$serial = 'gadash_qr2' . str_replace ( array (
						'ga:',
						',',
						'-'
				), "", $projectId . $from . $metrics );

				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( 1 ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return '';
			}
			if (! isset ( $data ['rows'] )) {
				return '';
			}

			$ga_dash_statsdata = "";

			$max_array = array ();
			foreach ( $data ['rows'] as $item ) {
				$max_array [] = $item [2];
			}

			$max = max ( $max_array ) ? max ( $max_array ) : 1;

			for($i = 0; $i < $data ['totalResults']; $i ++) {
				$ga_dash_statsdata .= "['" . ucfirst(__($data ['rows'] [$i] [1])) . ', ' . substr_replace(substr_replace($data ['rows'] [$i] [0] , '-', 4, 0), '-', 7, 0) . "'," . ($anonim ? str_replace ( ',', '.', round ( $data ['rows'] [$i] [2] * 100 / $max, 2 ) ) : $data ['rows'] [$i] [2]) . "],";
			}

			$ga_dash_statsdata = wp_kses ( rtrim ( $ga_dash_statsdata, ',' ), $GADASH_Config->allowed_html );

			if ($ga_dash_statsdata) {
				if ($display != 3) {
					if ($anonim) {
						$formater = "var formatter = new google.visualization.NumberFormat({
					  suffix: '%',
					  fractionDigits: 2
					});

					formatter.format(data, 1);	";
					} else {
						$formater = '';
					}

					$content = '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
						<script type="text/javascript">
						  google.setOnLoadCallback(ga_dash_callback);

						  function ga_dash_callback(){

								if(typeof ga_dash_drawwidgetstats == "function"){
									ga_dash_drawwidgetstats();
								}

						}';

					$content .= '
					google.load("visualization", "1", {packages:["corechart"]});
					function ga_dash_drawwidgetstats() {
					var data = google.visualization.arrayToDataTable([' . "
					  ['" . __ ( "Date", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . ($anonim ? __ ( "&#39; trend", 'ga-dash' ) : '') . "']," . $ga_dash_statsdata . "
					]);

					var options = {
					  legend: {position: 'none'},
					  pointSize: 3," . $css . "
					  title: '" . $title . "',
					  titlePosition: 'in',
					  chartArea: {width: '95%',height:'75%'},
					  hAxis: { textPosition: 'none'},
					  vAxis: { textPosition: 'none', minValue: 0}
				 	};

					var chart = new google.visualization.AreaChart(document.getElementById('ga_dash_widgetstatsdata'));

					" . $formater . "

					chart.draw(data, options);

					}";
				}

				$content .= "</script>";

				$content .= '<div id="ga_dash_widgetstatsdata" style="width:100%;"></div>';
			}
			if ($display != 2 and isset ( $data ['totalsForAllResults'] ['ga:visits'] )) {
				switch ($period) {
					case '7daysAgo' :
						$periodtext = __ ( 'Last 7 Days', 'ga-dash' );
						break;
					case '14daysAgo' :
						$periodtext = __ ( 'Last 14 Days', 'ga-dash' );
						break;
					default :
						$periodtext = __ ( 'Last 30 Days', 'ga-dash' );
						break;
				}

				$content .= '<style>table#gadwp-stats, table#gadwp-stats td{border:none;}</style><table id="gadwp-stats"><tr><td style="font-weight:bold;padding:' . ($display == 3 ? '15px' : '0') . ' 0 10px 0;">' . __ ( "Period:", 'ga-dash' ) . '</td><td style="padding:' . ($display == 3 ? '15px' : '0') . ' 0 10px 20px;">' . $periodtext . '</td></tr>
				<tr><td style="font-weight:bold;padding:0 0 15px 0;">' . __ ( 'Total Visits:', 'ga-dash' ) . '</td><td style="padding:0 0 15px 20px;">' . ($data ['totalsForAllResults'] ['ga:visits']) . '</td></tr>
				</table>';
			}

			return apply_filters ( 'gadash_frontend_content', $content );
		}

		// Frontend Visists
		function frontend_afterpost_visits($projectId, $page_url, $post_id) {
			global $GADASH_Config;
			$content = '';
			$from = '30daysAgo';
			$to = 'yesterday';
			$metrics = 'ga:pageviews,ga:uniquePageviews';
			$dimensions = 'ga:date,ga:dayOfWeekName';

			$title = __ ( "Views vs UniqueViews", 'ga-dash' );

			/*
			 * Include Tools
			 */
			include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
			$tools = new GADASH_Tools ();

			if (isset ( $GADASH_Config->options ['ga_dash_style'] )) {
				$css = "colors:['" . $GADASH_Config->options ['ga_dash_style'] . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 20 ) . "'],";
				$color = $GADASH_Config->options ['ga_dash_style'];
			} else {
				$css = "";
				$color = "#3366CC";
			}

			try {
				$serial = 'gadash_qr21' . $post_id . 'stats';
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions,
							'filters' => 'ga:pagePath==' . $page_url
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( 1 ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return '';
			}
			if (! isset ( $data ['rows'] )) {
				return '';
			}

			$ga_dash_statsdata = "";
			for($i = 0; $i < $data ['totalResults']; $i ++) {
				$ga_dash_statsdata .= "['" . ucfirst(__($data ['rows'] [$i] [1])) . ', ' . substr_replace(substr_replace($data ['rows'] [$i] [0] , '-', 4, 0), '-', 7, 0) . "'," . round ( $data ['rows'] [$i] [2], 2 ) . "," . round ( $data ['rows'] [$i] [3], 2 ) . "],";
			}

			$ga_dash_statsdata = wp_kses ( rtrim ( $ga_dash_statsdata, ',' ), $GADASH_Config->allowed_html );

			if ($ga_dash_statsdata) {
				$content .= '
				google.load("visualization", "1", {packages:["corechart"]});
				function ga_dash_drawstats() {
				var data = google.visualization.arrayToDataTable([' . "
				  ['" . __ ( "Date", 'ga-dash' ) . "', '" . __ ( "Views", 'ga-dash' ) . "', '" . __ ( "UniqueViews", 'ga-dash' ) . "']," . $ga_dash_statsdata . "
				]);

				var options = {
				  legend: {position: 'none'},
				  pointSize: 3," . $css . "
				  title: '" . $title . "',
		  		  vAxis: {minValue: 0},
				  chartArea: {width: '100%'},
				  hAxis: { textPosition: 'none'}
				};

				var chart = new google.visualization.AreaChart(document.getElementById('ga_dash_statsdata'));
				chart.draw(data, options);

				}";
			}

			return $content;
		}

		// Frontend searches
		function frontend_afterpost_searches($projectId, $page_url, $post_id) {
			global $GADASH_Config;
			$content = '';
			$from = '30daysAgo';
			$to = 'yesterday';
			$metrics = 'ga:visits';
			$dimensions = 'ga:keyword';
			try {
				$serial = 'gadash_qr22' . $post_id . 'search';
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_ga->get ( 'ga:' . $projectId, $from, $to, $metrics, array (
							'dimensions' => $dimensions,
							'sort' => '-ga:visits',
							'max-results' => '24',
							'filters' => 'ga:pagePath==' . $page_url
					) );
					set_transient ( $serial, $data, $this->get_timeouts ( 1 ) );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return '';
			}

			$ga_dash_organicdata = "";
			if (! isset ( $data ['rows'] )) {
				return '';
			}
			$i = 0;
			while ( isset ( $data ['rows'] [$i] [0] ) ) {
				if ($data ['rows'] [$i] [0] != "(not set)") {
					$ga_dash_organicdata .= "['" . addslashes ( $data ['rows'] [$i] [0] ) . "'," . $data ['rows'] [$i] [1] . "],";
				}
				$i ++;
			}

			$ga_dash_organicdata = wp_kses ( rtrim ( $ga_dash_organicdata, ',' ), $GADASH_Config->allowed_html );

			if ($ga_dash_organicdata) {
				$content .= '
					google.load("visualization", "1", {packages:["table"]})
					function ga_dash_drawsd() {

					var datas = google.visualization.arrayToDataTable([' . "
					  ['" . __ ( "Top Searches", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . "']," . $ga_dash_organicdata . "
					]);

					var options = {
						page: 'enable',
						pageSize: 6,
						width: '100%',
					};

					var chart = new google.visualization.Table(document.getElementById('ga_dash_sdata'));
					chart.draw(datas, options);

				  }";
			}

			return $content;
		}

		// Realtime Ajax Response
		function gadash_realtime_data($projectId) {
			$metrics = 'rt:activeVisitors';
			$dimensions = 'rt:pagePath,rt:source,rt:keyword,rt:trafficType,rt:visitorType,rt:pageTitle';
			try {
				$serial = "gadash_realtimecache_" . $projectId;
				$transient = get_transient ( $serial );
				if (empty ( $transient )) {
					$data = $this->service->data_realtime->get ( 'ga:' . $projectId, $metrics, array (
							'dimensions' => $dimensions
					) );
					set_transient ( $serial, $data, 55 );
				} else {
					$data = $transient;
				}
			} catch ( Exception $e ) {
				update_option ( 'gadash_lasterror', date ( 'Y-m-d H:i:s' ) . ': ' . esc_html ( $e ) );
				return '';
			}

			$i = 0;
			while ( isset ( $data->rows[$i] ) ) {
				$data->rows[$i] = str_replace('"',"'",$data->rows[$i]); //remove all double quotes before sending data
				$i ++;
			}

			return $data;
		}

		// Realtime Stats
		function ga_realtime() {
			global $GADASH_Config;

			$code = '

				<script type="text/javascript">

				var focusFlag = 1;

				jQuery(document).ready(function(){
					jQuery(window).bind("focus",function(event){
						focusFlag = 1;
					}).bind("blur", function(event){
						focusFlag = 0;
					});
				});

				jQuery(function() {
					jQuery( document ).tooltip();
				});

				function onlyUniqueValues(value, index, self) {
					return self.indexOf(value) === index;
				 }

				function countvisits(data, searchvalue) {
					var count = 0;
					for ( var i = 0; i < data["rows"].length; i = i + 1 ) {
						if (jQuery.inArray(searchvalue, data["rows"][ i ])>-1){
							count += parseInt(data["rows"][ i ][6]);
						}
		 			}
					return count;
				 }

				function gadash_generatetooltip(data) {
					var count = 0;
					var table = "";
					for ( var i = 0; i < data.length; i = i + 1 ) {
							count += parseInt(data[ i ].count);
							table += "<tr><td class=\'gadash-pgdetailsl\'>"+data[i].value+"</td><td class=\'gadash-pgdetailsr\'>"+data[ i ].count+"</td></tr>";
					};
					if (count){
						return("<table>"+table+"</table>");
					}else{
						return("");
					}
				}

				function gadash_pagedetails(data, searchvalue) {
					var newdata = [];
					for ( var i = 0; i < data["rows"].length; i = i + 1 ){
						var sant=1;
						for ( var j = 0; j < newdata.length; j = j + 1 ){
							if (data["rows"][i][0]+data["rows"][i][1]+data["rows"][i][2]+data["rows"][i][3]==newdata[j][0]+newdata[j][1]+newdata[j][2]+newdata[j][3]){
								newdata[j][6] = parseInt(newdata[j][6]) + parseInt(data["rows"][i][6]);
								sant = 0;
							}
						}
						if (sant){
							newdata.push(data["rows"][i].slice());
						}
					}

					var countrfr = 0;
					var countkwd = 0;
					var countdrt = 0;
					var countscl = 0;
					var tablerfr = "";
					var tablekwd = "";
					var tablescl = "";
					var tabledrt = "";
					for ( var i = 0; i < newdata.length; i = i + 1 ) {
						if (newdata[i][0] == searchvalue){
							var pagetitle = newdata[i][5];
							switch (newdata[i][3]){
								case "REFERRAL": 	countrfr += parseInt(newdata[ i ][6]);
													tablerfr +=	"<tr><td class=\'gadash-pgdetailsl\'>"+newdata[i][1]+"</td><td class=\'gadash-pgdetailsr\'>"+newdata[ i ][6]+"</td></tr>";
													break;
								case "ORGANIC": 	countkwd += parseInt(newdata[ i ][6]);
													tablekwd +=	"<tr><td class=\'gadash-pgdetailsl\'>"+newdata[i][2]+"</td><td class=\'gadash-pgdetailsr\'>"+newdata[ i ][6]+"</td></tr>";
													break;
								case "SOCIAL": 		countscl += parseInt(newdata[ i ][6]);
													tablescl +=	"<tr><td class=\'gadash-pgdetailsl\'>"+newdata[i][1]+"</td><td class=\'gadash-pgdetailsr\'>"+newdata[ i ][6]+"</td></tr>";
													break;
								case "DIRECT": 		countdrt += parseInt(newdata[ i ][6]);
													break;
							};
						};
		 			};
					if (countrfr){
						tablerfr = "<table><tr><td>' . __ ( "REFERRALS", 'ga-dash' ) . ' ("+countrfr+")</td></tr>"+tablerfr+"</table><br />";
					}
					if (countkwd){
						tablekwd = "<table><tr><td>' . __ ( "KEYWORDS", 'ga-dash' ) . ' ("+countkwd+")</td></tr>"+tablekwd+"</table><br />";
					}
					if (countscl){
						tablescl = "<table><tr><td>' . __ ( "SOCIAL", 'ga-dash' ) . ' ("+countscl+")</td></tr>"+tablescl+"</table><br />";
					}
					if (countdrt){
						tabledrt = "<table><tr><td>' . __ ( "DIRECT", 'ga-dash' ) . ' ("+countdrt+")</td></tr></table><br />";
					}
					return ("<p><center><strong>"+pagetitle+"</strong></center></p>"+tablerfr+tablekwd+tablescl+tabledrt);
				 }

				 function online_refresh(){
					if (focusFlag){

					jQuery.post(ajaxurl, {action: "gadash_get_online_data", gadash_security: "' . wp_create_nonce ( 'gadash_get_online_data' ) . '"}, function(response){
						var data = jQuery.parseJSON(response);

						if (data["totalsForAllResults"]["rt:activeVisitors"]!==document.getElementById("gadash-online").innerHTML){
							jQuery("#gadash-online").fadeOut("slow");
							jQuery("#gadash-online").fadeOut(500);
							jQuery("#gadash-online").fadeOut("slow", function() {
								if ((parseInt(data["totalsForAllResults"]["rt:activeVisitors"]))<(parseInt(document.getElementById("gadash-online").innerHTML))){
									jQuery("#gadash-online").css({\'background-color\' : \'#FFE8E8\'});
								}else{
									jQuery("#gadash-online").css({\'background-color\' : \'#E0FFEC\'});
								}
								document.getElementById("gadash-online").innerHTML = data["totalsForAllResults"]["rt:activeVisitors"];
							});
							jQuery("#gadash-online").fadeIn("slow");
							jQuery("#gadash-online").fadeIn(500);
							jQuery("#gadash-online").fadeIn("slow", function() {
								jQuery("#gadash-online").css({\'background-color\' : \'#FFFFFF\'});
							});
						};

						if (data["totalsForAllResults"]["rt:activeVisitors"] == 0){
							data["rows"]= [];
						};

						var pagepath = [];
						var referrals = [];
						var keywords = [];
						var social = [];
						var visittype = [];
						for ( var i = 0; i < data["rows"].length; i = i + 1 ) {
							pagepath.push( data["rows"][ i ][0] );
							if (data["rows"][i][3]=="REFERRAL"){
								referrals.push( data["rows"][ i ][1] );
							}
							if (data["rows"][i][3]=="ORGANIC"){
								keywords.push( data["rows"][ i ][2] );
							}
							if (data["rows"][i][3]=="SOCIAL"){
								social.push( data["rows"][ i ][1] );
							}
							visittype.push( data["rows"][ i ][3] );
		 				}

						var upagepath = pagepath.filter(onlyUniqueValues);
						var upagepathstats = [];
						for ( var i = 0; i < upagepath.length; i = i + 1 ) {
							upagepathstats[i]={"pagepath":upagepath[i],"count":countvisits(data,upagepath[i])};
		 				}
						upagepathstats.sort( function(a,b){ return b.count - a.count } );

						var pgstatstable = "";
						for ( var i = 0; i < upagepathstats.length; i = i + 1 ) {
							if (i < ' . $GADASH_Config->options ['ga_realtime_pages'] . '){
								pgstatstable += "<tr class=\"gadash-pline\"><td class=\"gadash-pleft\"><a href=\"#\" title=\""+gadash_pagedetails(data, upagepathstats[i].pagepath)+"\">"+upagepathstats[i].pagepath.substring(0,70)+"</a></td><td class=\"gadash-pright\">"+upagepathstats[i].count+"</td></tr>";
							}
		 				}
						document.getElementById("gadash-pages").innerHTML="<br /><table class=\"gadash-pg\">"+pgstatstable+"</table>";

						var ureferralsstats = [];
						var ureferrals = referrals.filter(onlyUniqueValues);
						for ( var i = 0; i < ureferrals.length; i = i + 1 ) {
							ureferralsstats[i]={"value":ureferrals[i],"count":countvisits(data,ureferrals[i])};
		 				}
						ureferralsstats.sort( function(a,b){ return b.count - a.count } );

						var ukeywordsstats = [];
						var ukeywords = keywords.filter(onlyUniqueValues);
						for ( var i = 0; i < ukeywords.length; i = i + 1 ) {
							ukeywordsstats[i]={"value":ukeywords[i],"count":countvisits(data,ukeywords[i])};
		 				}
						ukeywordsstats.sort( function(a,b){ return b.count - a.count } );

						var usocialstats = [];
						var usocial = social.filter(onlyUniqueValues);
						for ( var i = 0; i < usocial.length; i = i + 1 ) {
							usocialstats[i]={"value":usocial[i],"count":countvisits(data,usocial[i])};
		 				}
						usocialstats.sort( function(a,b){ return b.count - a.count } );

						var uvisittype = ["REFERRAL","ORGANIC","SOCIAL"];
						document.getElementById("gadash-tdo-right").innerHTML = "<span class=\"gadash-bigtext\"><a href=\"#\" title=\""+gadash_generatetooltip(ureferralsstats)+"\">"+\'' . __ ( "REFERRAL", 'ga-dash' ) . '\'+"</a>: "+countvisits(data,uvisittype[0])+"</span><br /><br />";
						document.getElementById("gadash-tdo-right").innerHTML += "<span class=\"gadash-bigtext\"><a href=\"#\" title=\""+gadash_generatetooltip(ukeywordsstats)+"\">"+\'' . __ ( "ORGANIC", 'ga-dash' ) . '\'+"</a>: "+countvisits(data,uvisittype[1])+"</span><br /><br />";
						document.getElementById("gadash-tdo-right").innerHTML += "<span class=\"gadash-bigtext\"><a href=\"#\" title=\""+gadash_generatetooltip(usocialstats)+"\">"+\'' . __ ( "SOCIAL", 'ga-dash' ) . '\'+"</a>: "+countvisits(data,uvisittype[2])+"</span><br /><br />";

						var uvisitortype = ["DIRECT","NEW","RETURN"];
						document.getElementById("gadash-tdo-rights").innerHTML = "<span class=\"gadash-bigtext\">"+\'' . __ ( "DIRECT", 'ga-dash' ) . '\'+": "+countvisits(data,uvisitortype[0])+"</span><br /><br />";
						document.getElementById("gadash-tdo-rights").innerHTML += "<span class=\"gadash-bigtext\">"+\'' . __ ( "NEW", 'ga-dash' ) . '\'+": "+countvisits(data,uvisitortype[1])+"</span><br /><br />";
						document.getElementById("gadash-tdo-rights").innerHTML += "<span class=\"gadash-bigtext\">"+\'' . __ ( "RETURN", 'ga-dash' ) . '\'+": "+countvisits(data,uvisitortype[2])+"</span><br /><br />";

					});
			   };
			   };
			   online_refresh();
			   setInterval(online_refresh, 60000);
			   </script>';
			return $code;
		}
		public function getcountrycodes() {
			include_once 'iso3166.php';
		}
	}
}
if (! isset ( $GLOBALS ['GADASH_GAPI'] )) {
	$GLOBALS ['GADASH_GAPI'] = new GADASH_GAPI ();
}
