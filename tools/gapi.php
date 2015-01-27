<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists('GADASH_GAPI')) {

    final class GADASH_GAPI
    {

        public $client, $service;

        public $country_codes;

        public $timeshift;

        private $error_timeout;

        private $managequota;

        function __construct()
        {
            global $GADASH_Config;
            
            include_once ($GADASH_Config->plugin_path . '/tools/autoload.php');;
            
            $config = new Google_Config();
            $config->setCacheClass('Google_Cache_Null');
            if (function_exists('curl_version')){
                $curlversion = curl_version();
            
                if (isset($curlversion['version']) AND version_compare($curlversion['version'], '7.10.8') >= 0 AND defined('GADWP_IP_VERSION') AND GADWP_IP_VERSION){
                    $config->setClassConfig('Google_IO_Curl', array('options' => array(CURLOPT_IPRESOLVE => GADWP_IP_VERSION))); // Force CURL_IPRESOLVE_V4 OR CURL_IPRESOLVE_V6
                }
            }            
            
            $this->client = new Google_Client($config);
            $this->client->setScopes('https://www.googleapis.com/auth/analytics.readonly');
            $this->client->setAccessType('offline');
            $this->client->setApplicationName('Google Analytics Dashboard');
            $this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            
            $this->set_error_timeout();
            $this->managequota = 'u' . get_current_user_id() . 's' . get_current_blog_id();
            
            if ($GADASH_Config->options['ga_dash_userapi']) {
                $this->client->setClientId($GADASH_Config->options['ga_dash_clientid']);
                $this->client->setClientSecret($GADASH_Config->options['ga_dash_clientsecret']);
                $this->client->setDeveloperKey($GADASH_Config->options['ga_dash_apikey']);
            } else {
                $this->client->setClientId('65556128781.apps.googleusercontent.com');
                $this->client->setClientSecret('Kc7888wgbc_JbeCpbFjnYpwE');
                $this->client->setDeveloperKey('AIzaSyBG7LlUoHc29ZeC_dsShVaBEX15SfRl_WY');
            }
            
            $this->service = new Google_Service_Analytics($this->client);
            
            if ($GADASH_Config->options['ga_dash_token']) {
                $token = $GADASH_Config->options['ga_dash_token'];
                $token = $this->ga_dash_refresh_token();
                if ($token) {
                    $this->client->setAccessToken($token);
                }
            }
        }

        private function set_error_timeout()
        {
            $midnight = strtotime("tomorrow 00:00:00"); // UTC midnight
            $midnight = $midnight + 8 * 3600; // UTC 8 AM
            $this->error_timeout = $midnight - time();
            return;
        }

        private function prepare_json($value)
        {
            return esc_html(str_replace('\\', '&#92;', stripslashes($value)));
        }

        /**
         * Handles errors returned by GAPI
         *
         * @return int|boolean
         */
        function gapi_errors_handler()
        {
            $errors = get_transient('ga_dash_gapi_errors');
            
            if (isset($errors[1][0]['reason'])) {
                
                if ($errors[1][0]['reason'] == 'dailyLimitExceeded') {
                    return TRUE;
                }
                
                if ($errors[1][0]['reason'] == 'invalidCredentials' || $errors[1][0]['reason'] == 'authError' || $errors[1][0]['reason'] == 'insufficientPermissions') {
                    $this->ga_dash_reset_token(false);
                    return TRUE;
                }
                
                if ($errors[1][0]['reason'] == 'invalidParameter' or $errors[1][0]['reason'] == 'badRequest') {
                    return TRUE;
                }
            }
            
            return FALSE;
        }

        /**
         * Calculates proper timeouts for each GAPI query
         *
         * @param
         *            $daily
         * @return number
         */
        function get_timeouts($daily)
        {
            $local_time = time() + $this->timeshift;
            if ($daily) {
                $nextday = explode('-', date('n-j-Y', strtotime(' +1 day', $local_time)));
                $midnight = mktime(0, 0, 0, $nextday[0], $nextday[1], $nextday[2]);
                return $midnight - $local_time;
            } else {
                $nexthour = explode('-', date('H-n-j-Y', strtotime(' +1 hour', $local_time)));
                $newhour = mktime($nexthour[0], 0, 0, $nexthour[1], $nexthour[2], $nexthour[3]);
                return $newhour - $local_time;
            }
        }

        function token_request()
        {
            $authUrl = $this->client->createAuthUrl();
            
            ?>
<form name="input"
	action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

	<table class="options">
		<tr>
			<td colspan="2" class="info">
						<?php echo __( "Use this link to get your access code:", 'ga-dash' ) . ' <a href="' . $authUrl . '" id="gapi-access-code" target="_blank">' . __ ( "Get Access Code", 'ga-dash' ) . '</a>.'; ?>
					</td>
		</tr>
		<tr>
			<td class="title"><label for="ga_dash_code"
				title="<?php _e("Use the red link to get your access code!",'ga-dash')?>"><?php echo _e( "Access Code:", 'ga-dash' ); ?></label>
			</td>
			<td><input type="text" id="ga_dash_code" name="ga_dash_code" value=""
				size="61" required="required"
				title="<?php _e("Use the red link to get your access code!",'ga-dash')?>"></td>
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

        /**
         * Retrives all Google Analytics Views with details
         *
         * @return array|string
         */
        function refresh_profiles()
        {
            try {
                $profiles = $this->service->management_profiles->listManagementProfiles('~all', '~all');
                $items = $profiles->getItems();
                if (count($items) != 0) {
                    $ga_dash_profile_list = array();
                    foreach ($items as $profile) {
                        $timetz = new DateTimeZone($profile->getTimezone());
                        $localtime = new DateTime('now', $timetz);
                        $timeshift = strtotime($localtime->format('Y-m-d H:i:s')) - time();
                        $ga_dash_profile_list[] = array(
                            $profile->getName(),
                            $profile->getId(),
                            $profile->getwebPropertyId(),
                            $profile->getwebsiteUrl(),
                            $timeshift,
                            $profile->getTimezone()
                        );
                    }
                    set_transient('gadash_lasterror', 'None');
                    return $ga_dash_profile_list;
                } else {
                    set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': No properties were found in this account!', $this->error_timeout);
                    return '';
                }
            } catch (Google_IO_Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return '';
            } catch (Google_Service_Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                set_transient('ga_dash_gapi_errors', array($e->getCode(),(array)$e->getErrors()), $this->error_timeout);
            } catch (Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e),$this->error_timeout);
                return '';
            }
        }

        /**
         * Handles the token refresh process
         *
         * @return token|boolean
         */
        private function ga_dash_refresh_token()
        {
            global $GADASH_Config;
            try {
                if (is_multisite() && $GADASH_Config->options['ga_dash_network']) {
                    $transient = get_site_transient("ga_dash_refresh_token");
                } else {
                    $transient = get_transient("ga_dash_refresh_token");
                }
                if (empty($transient)) {
                    
                    if (! $GADASH_Config->options['ga_dash_refresh_token']) {
                        $google_token = json_decode($GADASH_Config->options['ga_dash_token']);
                        $GADASH_Config->options['ga_dash_refresh_token'] = $google_token->refresh_token;
                        $this->client->refreshToken($google_token->refresh_token);
                    } else {
                        $this->client->refreshToken($GADASH_Config->options['ga_dash_refresh_token']);
                    }
                    
                    $token = $this->client->getAccessToken();
                    $google_token = json_decode($token);
                    $GADASH_Config->options['ga_dash_token'] = $token;
                    if (is_multisite() && $GADASH_Config->options['ga_dash_network']) {
                        set_site_transient("ga_dash_refresh_token", $token, $google_token->expires_in);
                        $GADASH_Config->set_plugin_options(true);
                    } else {
                        set_transient("ga_dash_refresh_token", $token, $google_token->expires_in);
                        $GADASH_Config->set_plugin_options();
                    }
                    return $token;
                } else {
                    return $transient;
                }
            } catch (Google_IO_Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return false;
            } catch (Google_Service_Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                set_transient('ga_dash_gapi_errors', array($e->getCode(),(array)$e->getErrors()), $this->error_timeout);
                return $e->getCode();
            } catch (Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return false;
            }
        }

        /**
         * Handles the token reset process
         *
         * @param
         *            $all
         */
        function ga_dash_reset_token($all = true)
        {
            global $GADASH_Config;
            if (is_multisite() && $GADASH_Config->options['ga_dash_network']) {
                delete_site_transient('ga_dash_refresh_token');
            } else {
                delete_transient('ga_dash_refresh_token');
            }
            $GADASH_Config->options['ga_dash_token'] = "";
            $GADASH_Config->options['ga_dash_refresh_token'] = "";
            
            if ($all) {
                $GADASH_Config->options['ga_dash_tableid'] = "";
                $GADASH_Config->options['ga_dash_tableid_jail'] = "";
                $GADASH_Config->options['ga_dash_profile_list'] = "";
                try {
                    $this->client->revokeToken();
                } catch (Exception $e) {
                    if (is_multisite() && $GADASH_Config->options['ga_dash_network']) {
                        $GADASH_Config->set_plugin_options(true);
                    } else {
                        $GADASH_Config->set_plugin_options();
                    }
                }
            }
            
            if (is_multisite() && $GADASH_Config->options['ga_dash_network']) {
                $GADASH_Config->set_plugin_options(true);
            } else {
                $GADASH_Config->set_plugin_options();
            }
        }

        /**
         * Get and cache Core Reports
         *
         * @param unknown $projecId            
         * @param unknown $from            
         * @param unknown $to            
         * @param unknown $metrics            
         * @param unknown $options            
         * @param unknown $serial            
         * @return number|Google_Service_Analytics_GaData
         */
        private function handle_corereports($projectId, $from, $to, $metrics, $options, $serial)
        {
            try {
                
                if ($from == "today") {
                    $timeouts = 0;
                } else {
                    $timeouts = 1;
                }
                
                if (strlen($serial) > 44) {
                    $serial = substr($serial, 0, 43); // keep a safe length
                }
                
                $transient = get_transient($serial);
                if (empty($transient)) {
                    
                    if ($this->gapi_errors_handler()) {
                        return - 23;
                    }
                    
                    $data = $this->service->data_ga->get('ga:' . $projectId, $from, $to, $metrics, $options);
                    set_transient($serial, $data, $this->get_timeouts($timeouts));
                } else {
                    $data = $transient;
                }
            } catch (Google_Service_Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                set_transient('ga_dash_gapi_errors', array($e->getCode(),(array)$e->getErrors()), $this->error_timeout);
                return $e->getCode();
            } catch (Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return $e->getCode();
            }
            
            if (isset($data['rows'])) {
                return $data;
            } else {
                return - 21;
            }
        }

        /**
         * Analytics data for backend reports (Admin Widget main report)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @return string|int
         */
        function get_mainreport($projectId, $from, $to, $query)
        {
            switch ($query) {
                
                case 'users':
                    $title = __("Users", 'ga-dash');
                    break;
                
                case 'pageviews':
                    $title = __("Page Views", 'ga-dash');
                    break;
                
                case 'visitBounceRate':
                    $title = __("Bounce Rate", 'ga-dash');
                    break;
                
                case 'organicSearches':
                    $title = __("Organic Searches", 'ga-dash');
                    break;
                
                default:
                    $title = __("Sessions", 'ga-dash');
            }
            
            $metrics = 'ga:' . $query;
            
            if ($from == "today" or $from == "yesterday") {
                $dimensions = 'ga:hour';
                $dayorhour = __("Hour", 'ga-dash');
            } else {
                $dimensions = 'ga:date,ga:dayOfWeekName';
                $dayorhour = __("Date", 'ga-dash');
            }
            
            $serial = 'gadash_qr2' . str_replace(array(
                'ga:',
                ',',
                '-'
            ), "", $projectId . $from . $metrics);
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            
            if ($from == "today" or $from == "yesterday") {
                for ($i = 0; $i < $data['totalResults']; $i ++) {
                    $ga_dash_data .= '["' . (int) $data["rows"][$i][0] . ':00",' . round($data["rows"][$i][1], 2) . '],';
                }
            } else {
                for ($i = 0; $i < $data['totalResults']; $i ++) {
                    $ga_dash_data .= '["' . esc_html(ucfirst(__($data["rows"][$i][1]))) . ", " . esc_html(substr_replace(substr_replace($data["rows"][$i][0], "-", 4, 0), "-", 7, 0)) . '",' . round($data["rows"][$i][2], 2) . '],';
                }
            }
            
            if ($ga_dash_data) {
                return '[["' . $dayorhour . '","' . $title . '"],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for backend reports (bottom stats main report)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @return array|int
         */
        function get_bottomstats($projectId, $from, $to)
        {
            $metrics = 'ga:sessions,ga:users,ga:pageviews,ga:BounceRate,ga:organicSearches,ga:pageviewsPerSession';
            
            $serial = 'gadash_qr3' . $projectId . $from;
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => NULL,
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                if ($data == - 21) {
                    return array_fill(0, 6, 0);
                } else {
                    return $data;
                }
            }
            
            $ga_dash_data = $data['rows'][0];
            array_map('floatval', $ga_dash_data);
            
            return $ga_dash_data;
        }

        /**
         * Analytics data for backend reports (contentpages)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @return string|int
         */
        function get_contentpages($projectId, $from, $to)
        {
            $metrics = 'ga:pageviews';
            $dimensions = 'ga:pageTitle';
            
            $serial = 'gadash_qr4' . $projectId . $from;
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'sort' => '-ga:pageviews',
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            $i = 0;
            
            while (isset($data['rows'][$i][0])) {
                $ga_dash_data .= '["' . $this->prepare_json($data['rows'][$i][0]) . '",' . (int) $data['rows'][$i][1] . '],';
                $i ++;
            }
            
            if ($ga_dash_data) {
                return '[["' . __("Pages", 'ga-dash') . '","' . __("Views", 'ga-dash') . '"],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for backend reports (referrers)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @return string|int
         */
        function get_referrers($projectId, $from, $to)
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:source';
            
            $serial = 'gadash_qr5' . $projectId . $from;
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'filters' => 'ga:medium==referral',
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            $i = 0;
            while (isset($data['rows'][$i][0])) {
                $ga_dash_data .= '["' . $this->prepare_json($data["rows"][$i][0]) . '",' . (int) $data["rows"][$i][1] . '],';
                $i ++;
            }
            
            if ($ga_dash_data) {
                return '[["' . __("Referrers", 'ga-dash') . '","' . __("Sessions", 'ga-dash') . '"],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for backend reports (searches)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @return string|int
         */
        function get_searches($projectId, $from, $to)
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:keyword';
            
            $serial = 'gadash_qr6' . $projectId . $from;
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            $i = 0;
            while (isset($data['rows'][$i][0])) {
                if ($data['rows'][$i][0] != "(not set)") {
                    $ga_dash_data .= '["' . $this->prepare_json($data["rows"][$i][0]) . '",' . (int) $data["rows"][$i][1] . '],';
                }
                $i ++;
            }
            
            if ($ga_dash_data) {
                return '[["' . __("Searches", 'ga-dash') . '","' . __("Sessions", 'ga-dash') . '"],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for backend reports (location reports)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @return string|int
         */
        function get_locations($projectId, $from, $to)
        {
            global $GADASH_Config;
            
            $metrics = 'ga:sessions';
            $options = "";
            
            $title = __("Countries", 'ga-dash');
            
            $serial = 'gadash_qr7' . $projectId . $from;
            
            $dimensions = 'ga:country';
            $filters = "";
            
            $options = array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'quotaUser' => $this->managequota . 'p' . $projectId
            );
            
            if ($GADASH_Config->options['ga_target_geomap']) {
                $dimensions = 'ga:city, ga:region';
                $this->getcountrycodes();
                if (isset($this->country_codes[$GADASH_Config->options['ga_target_geomap']])) {
                    $filters = 'ga:country==' . ($this->country_codes[$GADASH_Config->options['ga_target_geomap']]);
                    $title = __("Cities from", 'ga-dash') . ' ' . __($this->country_codes[$GADASH_Config->options['ga_target_geomap']]);
                    $serial = 'gadash_qr7' . $projectId . $from . $GADASH_Config->options['ga_target_geomap'];
                    $options = array(
                        'dimensions' => $dimensions,
                        'filters' => $filters,
                        'sort' => '-ga:sessions',
                        'quotaUser' => $this->managequota . 'p' . $projectId
                    );
                }
            }
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            $i = 0;
            while (isset($data['rows'][$i][1])) {
                if (isset($data['rows'][$i][2])) {
                    $ga_dash_data .= '["' . $this->prepare_json($data["rows"][$i][0]) . ', ' . $this->prepare_json($data["rows"][$i][1]) . '",' . (int) $data["rows"][$i][2] . '],';
                } else {
                    $ga_dash_data .= '["' . $this->prepare_json($data["rows"][$i][0]) . '",' . (int) $data["rows"][$i][1] . '],';
                }
                $i ++;
            }
            
            if ($ga_dash_data) {
                return '[["' . $title . '", "' . __("Sessions", 'ga-dash') . '"],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for backend reports (traffic channels)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @return string|int
         */
        function get_trafficchannels($projectId, $from, $to)
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:channelGrouping';
            
            $serial = 'gadash_qr8' . $projectId . $from;
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $title = __("Channels", 'ga-dash');
            $ga_dash_data = "";
            
            for ($i = 0; $i < $data['totalResults']; $i ++) {
                $shrink = explode(" ", $data["rows"][$i][0]);
                $ga_dash_data .= '["' . '<div style=\\"color:black; font-size:1.1em\\">' . esc_html($shrink[0]) . '</div><div style=\\"color:darkblue; font-size:1.2em\\">' . (int) $data["rows"][$i][1] . '</div>","' . '<div style=\\"color:black; font-size:1.1em\\">' . $title . '</div><div style=\\"color:darkblue; font-size:1.2em\\">' . (int) $data['totalsForAllResults']["ga:sessions"] . '</div>"],';
            }
            
            if ($ga_dash_data) {
                return '[["' . '<div style=\\"color:black; font-size:1.1em\\">' . $title . '</div><div style=\\"color:darkblue; font-size:1.2em\\">' . (int) $data['totalsForAllResults']["ga:sessions"] . '</div>", ""],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for backend reports (traffic mediums, type, serach engines, social networks)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @return string|int
         */
        function get_trafficdetails($projectId, $from, $to, $query)
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:' . $query;
            
            if ($query == 'source') {
                $options = array(
                    'dimensions' => $dimensions,
                    'filters' => 'ga:medium==organic',
                    'quotaUser' => $this->managequota . 'p' . $projectId
                );
            } else {
                $options = array(
                    'dimensions' => $dimensions,
                    'quotaUser' => $this->managequota . 'p' . $projectId
                );
            }
            
            $serial = 'gadash_qr10' . $projectId . $from . $query;
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, $options, $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            
            for ($i = 0; $i < $data['totalResults']; $i ++) {
                if ($data['rows'][$i][0] != '(not set)') {
                    $ga_dash_data .= '["' . str_replace("(none)", "direct", esc_html($data['rows'][$i][0])) . '",' . (int) $data["rows"][$i][1] . '],';
                }
            }
            
            if ($ga_dash_data) {
                return '[["' . __("Type", 'ga-dash') . '", "Sessions"],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for frontend Widget (chart data and totals)
         *
         * @param
         *            $projectId
         * @param
         *            $period
         * @param
         *            $anonim
         * @return array|int
         */
        function frontend_widget_stats($projectId, $from, $anonim)
        {
            $content = '';
            $to = 'yesterday';
            $metrics = 'ga:sessions';
            $dimensions = 'ga:date,ga:dayOfWeekName';
            
            $serial = 'gadash_qr2' . str_replace(array(
                'ga:',
                ',',
                '-'
            ), "", $projectId . $from . $metrics);
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            
            $max_array = array();
            foreach ($data['rows'] as $item) {
                $max_array[] = $item[2];
            }
            
            $max = max($max_array) ? max($max_array) : 1;
            
            for ($i = 0; $i < $data['totalResults']; $i ++) {
                $ga_dash_data .= '["' . ucfirst(esc_html((__($data["rows"][$i][1])))) . ", " . esc_html(substr_replace(substr_replace($data["rows"][$i][0], "-", 4, 0), "-", 7, 0)) . '",' . ($anonim ? str_replace(",", ".", round($data["rows"][$i][2] * 100 / $max, 2)) : (int) $data["rows"][$i][2]) . '],';
            }
            
            if ($ga_dash_data) {
                return array(
                    '[["' . __("Date", 'ga-dash') . '", "' . __("Sessions", 'ga-dash') . ($anonim ? "' " . __("trend", 'ga-dash') : '') . '"],' . rtrim($ga_dash_data, ",") . "]",
                    (int) $data['totalsForAllResults']['ga:sessions']
                );
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for frontend reports (pagviews and unique pageviews per page)
         *
         * @param
         *            $projectId
         * @param
         *            $page_url
         * @param
         *            $post_id
         * @return string|int
         */
        function frontend_afterpost_pageviews($projectId, $page_url, $post_id)
        {
            $from = '30daysAgo';
            $to = 'yesterday';
            $metrics = 'ga:pageviews,ga:uniquePageviews';
            $dimensions = 'ga:date,ga:dayOfWeekName';
            
            $serial = 'gadash_qr21' . $post_id . 'stats';
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'filters' => 'ga:pagePath==' . $page_url,
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            for ($i = 0; $i < $data['totalResults']; $i ++) {
                $ga_dash_data .= '["' . ucfirst(esc_html(__($data['rows'][$i][1]))) . ", " . esc_html(substr_replace(substr_replace($data['rows'][$i][0], "-", 4, 0), "-", 7, 0)) . '",' . round($data['rows'][$i][2], 2) . ',' . round($data['rows'][$i][3], 2) . '],';
            }
            
            if ($ga_dash_data) {
                return '[["' . __('Date', "ga-dash") . '", "' . __('Views', "ga-dash") . '", "' . __('UniqueViews', "ga-dash") . '"],' . rtrim($ga_dash_data, ',') . ']';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for frontend reports (searches per page)
         *
         * @param
         *            $projectId
         * @param
         *            $page_url
         * @param
         *            $post_id
         * @return string|int
         */
        function frontend_afterpost_searches($projectId, $page_url, $post_id)
        {
            $from = '30daysAgo';
            $to = 'yesterday';
            $metrics = 'ga:sessions';
            $dimensions = 'ga:keyword';
            
            $serial = 'gadash_qr22' . $post_id . 'search';
            
            $data = $this->handle_corereports($projectId, $from, $to, $metrics, array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'filters' => 'ga:pagePath==' . $page_url,
                'quotaUser' => $this->managequota . 'p' . $projectId
            ), $serial);
            
            if (is_numeric($data)) {
                return $data;
            }
            
            $ga_dash_data = "";
            $i = 0;
            while (isset($data['rows'][$i][0])) {
                if ($data['rows'][$i][0] != "(not set)") {
                    $ga_dash_data .= '["' . $this->prepare_json($data['rows'][$i][0]) . '",' . (int) $data['rows'][$i][1] . '],';
                }
                $i ++;
            }
            
            if ($ga_dash_data) {
                return '[["' . __('Searches', "ga-dash") . '", "' . __('Sessions', "ga-dash") . '"],' . rtrim($ga_dash_data, ',') . ' ]';
            } else {
                return - 22;
            }
        }

        /**
         * Analytics data for backend reports (Real-Time)
         *
         * @param
         *            $projectId
         * @return string|int
         */
        function gadash_realtime_data($projectId)
        {
            $metrics = 'rt:activeUsers';
            $dimensions = 'rt:pagePath,rt:source,rt:keyword,rt:trafficType,rt:visitorType,rt:pageTitle';
            try {
                $serial = "gadash_realtimecache_" . $projectId;
                $transient = get_transient($serial);
                if (empty($transient)) {
                    
                    if ($this->gapi_errors_handler()) {
                        return - 23;
                    }
                    
                    $data = $this->service->data_realtime->get('ga:' . $projectId, $metrics, array(
                        'dimensions' => $dimensions,
                        'quotaUser' => $this->managequota . 'p' . $projectId
                    ));
                    set_transient($serial, $data, 55);
                } else {
                    $data = $transient;
                }
            } catch (Google_Service_Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $this->error_timeout);
                set_transient('ga_dash_gapi_errors', array($e->getCode(),(array)$e->getErrors()), $this->error_timeout);
                return $e->getCode();
            } catch (Exception $e) {
                set_transient('gadash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return $e->getCode();
            }
            
            if (! isset($data['rows'])) {
                return - 21;
            }
            
            $i = 0;
            
            $ga_dash_data = $data;
            
            while (isset($data->rows[$i])) {
                $ga_dash_data->rows[$i] = array_map('esc_html', $data->rows[$i]);
                $i ++;
            }
            
            return print_r(json_encode($ga_dash_data), true);
        }

        public function getcountrycodes()
        {
            include_once 'iso3166.php';
        }
    }
}

if (! isset($GLOBALS['GADASH_GAPI'])) {
    $GLOBALS['GADASH_GAPI'] = new GADASH_GAPI();
}
