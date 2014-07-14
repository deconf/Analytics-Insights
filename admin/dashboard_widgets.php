<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists ( 'GADASH_Widgets' )) {
	class GADASH_Widgets {
		function __construct() {
			global $GADASH_Config;
			add_action ( 'wp_dashboard_setup', array (
					$this,
					'ga_dash_setup' 
			) );
			// Admin Styles
			add_action ( 'admin_enqueue_scripts', array (
					$this,
					'ga_dash_admin_enqueue_styles' 
			) );
			// Admin Menu
			add_action ( 'admin_menu', array (
					$this,
					'ga_dash_admin_actions' 
			) );
			// Network Menu
			add_action ( 'network_admin_menu', array (
					$this,
					'ga_dash_network_actions' 
			) );
			// Plugin Settings link
			add_filter ( "plugin_action_links_" . plugin_basename ( $GADASH_Config->plugin_path ) . '/gadwp.php', array (
					$this,
					'ga_dash_settings_link' 
			) );
			// Realtime action
			add_action ( 'wp_ajax_gadash_get_online_data', array (
					$this,
					'gadash_realtime_data' 
			) );
		}
		// Realtime Ajax Response
		function gadash_realtime_data() {
			global $GADASH_Config;
			
			if (! isset ( $_REQUEST ['gadash_security'] ) or ! wp_verify_nonce ( $_REQUEST ['gadash_security'], 'gadash_get_online_data' )) {
				return;
			}
			
			if ($GADASH_Config->options ['ga_dash_token'] and function_exists ( 'curl_version' )) {
				include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
				global $GADASH_GAPI;
			} else {
				die ();
			}
			
			if (current_user_can ( 'manage_options' ) AND !$GADASH_Config->options ['ga_dash_jailadmins'] ) {
				print_r ( stripslashes ( json_encode ( $GADASH_GAPI->gadash_realtime_data ( $GADASH_Config->options ['ga_dash_tableid'] ) ) ) );
			} else	{				
				print_r ( stripslashes ( json_encode ( $GADASH_GAPI->gadash_realtime_data ( $GADASH_Config->options ['ga_dash_tableid_jail'] ) ) ) );
			}	
			
			die ();
		}
		function ga_dash_admin_actions() {
			global $GADASH_Config;
			global $wp_version;
			
			if (current_user_can ( 'manage_options' )) {
				include ($GADASH_Config->plugin_path . '/admin/ga_dash_settings.php');
				
				add_menu_page ( __ ( "Google Analytics", 'ga-dash' ), __ ( "Google Analytics", 'ga-dash' ), 'manage_options', 'gadash_settings', array (
						'GADASH_Settings',
						'general_settings' 
				), version_compare ( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : $GADASH_Config->plugin_url . '/admin/images/gadash-icon.png' );
				add_submenu_page ( 'gadash_settings', __ ( "General Settings", 'ga-dash' ), __ ( "General Settings", 'ga-dash' ), 'manage_options', 'gadash_settings', array (
						'GADASH_Settings',
						'general_settings' 
				) );
				add_submenu_page ( 'gadash_settings', __ ( "Backend Settings", 'ga-dash' ), __ ( "Backend Settings", 'ga-dash' ), 'manage_options', 'gadash_backend_settings', array (
						'GADASH_Settings',
						'backend_settings' 
				) );
				add_submenu_page ( 'gadash_settings', __ ( "Frontend Settings", 'ga-dash' ), __ ( "Frontend Settings", 'ga-dash' ), 'manage_options', 'gadash_frontend_settings', array (
						'GADASH_Settings',
						'frontend_settings' 
				) );
				add_submenu_page ( 'gadash_settings', __ ( "Tracking Code", 'ga-dash' ), __ ( "Tracking Code", 'ga-dash' ), 'manage_options', 'gadash_tracking_settings', array (
						'GADASH_Settings',
						'tracking_settings' 
				) );
			}
		}
		function ga_dash_network_actions() {
			global $GADASH_Config;
			global $wp_version;
			
			if (current_user_can ( 'manage_netwrok' )) {
				include ($GADASH_Config->plugin_path . '/admin/ga_dash_settings.php');
				
				add_menu_page ( __ ( "Google Analytics", 'ga-dash' ), __ ( "Google Analytics", 'ga-dash' ), 'manage_netwrok', 'gadash_settings', array (
						'GADASH_Settings',
						'general_settings_network' 
				), version_compare ( $wp_version, '3.8.0', '>=' ) ? 'dashicons-chart-area' : $GADASH_Config->plugin_url . '/admin/images/gadash-icon.png' );
				add_submenu_page ( 'gadash_settings', __ ( "General Settings", 'ga-dash' ), __ ( "General Settings", 'ga-dash' ), 'manage_netwrok', 'gadash_settings', array (
						'GADASH_Settings',
						'general_settings_network' 
				) );
			}
		}
		
		/*
		 * Include styles
		 */
		function ga_dash_admin_enqueue_styles($hook) {
			global $GADASH_Config;
			$valid_hooks = array (
					'toplevel_page_gadash_settings',
					'google-analytics_page_gadash_backend_settings',
					'google-analytics_page_gadash_frontend_settings',
					'google-analytics_page_gadash_tracking_settings' 
			);
			
			if (! in_array ( $hook, $valid_hooks ) and 'index.php' != $hook)
				return;
			
			wp_register_style ( 'ga_dash', $GADASH_Config->plugin_url . '/admin/css/ga_dash.css' );
			
			wp_enqueue_style ( 'ga_dash' );
			wp_enqueue_style ( 'wp-color-picker' );
			wp_enqueue_script ( 'wp-color-picker' );
			wp_enqueue_script ( 'wp-color-picker-script-handle', plugins_url ( 'js/wp-color-picker-script.js', __FILE__ ), array (
					'wp-color-picker' 
			), false, true );
			wp_enqueue_script ( 'gadash-general-settings', plugins_url ( 'js/admin.js', __FILE__ ), array (
					'jquery' 
			) );
		}
		function ga_dash_settings_link($links) {
			$settings_link = '<a href="' . get_admin_url ( null, 'admin.php?page=gadash_settings' ) . '">' . __ ( "Settings", 'ga-dash' ) . '</a>';
			array_unshift ( $links, $settings_link );
			return $links;
		}
		function ga_dash_setup() {
			global $GADASH_Config;
			
			/*
			 * Include Tools
			 */
			include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
			$tools = new GADASH_Tools ();
			
			if ($tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] )) {
				wp_add_dashboard_widget ( 'ga-dash-widget', __ ( "Google Analytics Dashboard", 'ga-dash' ), array (
						$this,
						'gadash_dashboard_widgets' 
				), $control_callback = null );
			}
		}
		function gadash_dashboard_widgets() {
			global $GADASH_Config;
			
			/*
			 * Include GAPI
			 */
			if ($GADASH_Config->options ['ga_dash_token'] and function_exists ( 'curl_version' )) {
				include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
				global $GADASH_GAPI;
			} else {
				echo '<p>' . __ ( "This plugin needs an authorization:", 'ga-dash' ) . '</p><form action="' . menu_page_url ( 'gadash_settings', false ) . '" method="POST">' . get_submit_button ( __ ( "Authorize Plugin", 'ga-dash' ), 'secondary' ) . '</form>';
				return;
			}
			
			/*
			 * Include Tools
			 */
			include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
			$tools = new GADASH_Tools ();
			
			$tools->ga_dash_cleanup_timeouts ();
			
			if (! $GADASH_GAPI->client->getAccessToken ()) {
				echo '<p>' . __ ( "Something went wrong. Please check the Debugging Data section for possible errors", 'ga-dash' ) . '</p><form action="' . menu_page_url ( 'gadash_settings', false ) . '" method="POST">' . get_submit_button ( __ ( "Error Log", 'ga-dash' ), 'secondary' ) . '</form>';
				return;
			}
			
			if (current_user_can ( 'manage_options' )) {
				
				if (isset ( $_REQUEST ['ga_dash_profile_select'] )) {
					$GADASH_Config->options ['ga_dash_tableid'] = $_REQUEST ['ga_dash_profile_select'];
				}
				
				$profiles = $GADASH_Config->options ['ga_dash_profile_list'];
				$profile_switch = '';
				
				if (is_array ( $profiles )) {
					if (! $GADASH_Config->options ['ga_dash_tableid']) {
						if ($GADASH_Config->options ['ga_dash_tableid_jail']) {
							$GADASH_Config->options ['ga_dash_tableid'] = $GADASH_Config->options ['ga_dash_tableid_jail'];
						} else {
							$GADASH_Config->options ['ga_dash_tableid'] = $tools->guess_default_domain ( $profiles );
						}
					} else if ($GADASH_Config->options ['ga_dash_jailadmins'] and $GADASH_Config->options ['ga_dash_tableid_jail']) {
						$GADASH_Config->options ['ga_dash_tableid'] = $GADASH_Config->options ['ga_dash_tableid_jail'];
					}
					
					$profile_switch .= '<select id="ga_dash_profile_select" name="ga_dash_profile_select" onchange="this.form.submit()">';
					foreach ( $profiles as $profile ) {
						if (! $GADASH_Config->options ['ga_dash_tableid']) {
							$GADASH_Config->options ['ga_dash_tableid'] = $profile [1];
						}
						if (isset ( $profile [3] )) {
							$profile_switch .= '<option value="' . esc_attr($profile [1]) . '" ';
							$profile_switch .= selected ( $profile [1], $GADASH_Config->options ['ga_dash_tableid'], false );
							$profile_switch .= ' title="' . __ ( "View Name:", 'ga-dash' ) . ' ' . esc_attr($profile [0]) . '">' . esc_attr($tools->ga_dash_get_profile_domain ( $profile [3] )) . '</option>';
						}
					}
					$profile_switch .= "</select>";
				} else {
					echo '<p>' . __ ( "Something went wrong while retrieving profiles list.", 'ga-dash' ) . '</p><form action="' . menu_page_url ( 'gadash_settings', false ) . '" method="POST">' . get_submit_button ( __ ( "More details", 'ga-dash' ), 'secondary' ) . '</form>';
					return;
				}
			}
			
			$GADASH_Config->set_plugin_options ();
			
			?>
<form id="ga-dash" method="POST">
			<?php
			
			if (current_user_can ( 'manage_options' )) {
				if ($GADASH_Config->options ['ga_dash_jailadmins']) {
					if ($GADASH_Config->options ['ga_dash_tableid_jail']) {
						$projectId = $GADASH_Config->options ['ga_dash_tableid_jail'];
					} else {
						echo '<p>' . __ ( "An admin should asign a default Google Analytics Profile.", 'ga-dash' ) . '</p><form action="' . menu_page_url ( 'gadash_settings', false ) . '" method="POST">' . get_submit_button ( __ ( "Select Domain", 'ga-dash' ), 'secondary' ) . '</form>';
						return;
					}
				} else {
					echo $profile_switch;
					$projectId = $GADASH_Config->options ['ga_dash_tableid'];
				}
			} else {
				if ($GADASH_Config->options ['ga_dash_tableid_jail']) {
					$projectId = $GADASH_Config->options ['ga_dash_tableid_jail'];
				} else {
					echo '<p>' . __ ( "An admin should asign a default Google Analytics Profile.", 'ga-dash' ) . '</p><form action="' . menu_page_url ( 'gadash_settings', false ) . '" method="POST">' . get_submit_button ( __ ( "Select Domain", 'ga-dash' ), 'secondary' ) . '</form>';
					return;
				}
			}
			
			if (! ($projectId)) {
				echo '<p>' . __ ( "Something went wrong while retrieving property data. You need to create and properly configure a Google Analytics account:", 'ga-dash' ) . '</p> <form action="http://deconf.com/how-to-set-up-google-analytics-on-your-website/" method="POST">' . get_submit_button ( __ ( "Find out more!", 'ga-dash' ), 'secondary' ) . '</form>';
				return;
			} else {
				$profile_info = $tools->get_selected_profile ( $GADASH_Config->options ['ga_dash_profile_list'], $projectId );
				if (isset ( $profile_info [4] )) {
					$GADASH_GAPI->timeshift = $profile_info [4];
				} else {
					$GADASH_GAPI->timeshift = ( int ) current_time ( 'timestamp' ) - time ();
				}
			}
			
			if (isset ( $_REQUEST ['query'] )) {
				$query = $_REQUEST ['query'];
				$GADASH_Config->options ['ga_dash_default_metric'] = $query;
				$GADASH_Config->set_plugin_options ();
			} else {
				$query = isset ( $GADASH_Config->options ['ga_dash_default_metric'] ) ? $GADASH_Config->options ['ga_dash_default_metric'] : 'visits';
			}
			
			if (isset ( $_REQUEST ['period'] )) {
				$period = $_REQUEST ['period'];
				$GADASH_Config->options ['ga_dash_default_dimension'] = $period;
				$GADASH_Config->set_plugin_options ();
			} else {
				$period = isset ( $GADASH_Config->options ['ga_dash_default_dimension'] ) ? $GADASH_Config->options ['ga_dash_default_dimension'] : '30daysAgo';
			}
			
			if ($period == "realtime") {
				$realtime = "realtime";
				$period = '';
			} else {
				$realtime = '';
			}
			
			?>

	<select id="ga_dash_period" name="period" onchange="this.form.submit()">
		<option value="realtime"
			<?php selected ( "realtime", $period, true ); ?>><?php _e("Real-Time",'ga-dash'); ?></option>
		<option value="today" <?php selected ( "today", $period, true ); ?>><?php _e("Today",'ga-dash'); ?></option>
		<option value="yesterday"
			<?php selected ( "yesterday", $period, true ); ?>><?php _e("Yesterday",'ga-dash'); ?></option>
		<option value="7daysAgo"
			<?php selected ( "7daysAgo", $period, true ); ?>><?php _e("Last 7 Days",'ga-dash'); ?></option>
		<option value="14daysAgo"
			<?php selected ( "14daysAgo", $period, true ); ?>><?php _e("Last 14 Days",'ga-dash'); ?></option>
		<option value="30daysAgo"
			<?php selected ( "30daysAgo", $period, true ); ?>><?php _e("Last 30 Days",'ga-dash'); ?></option>
		<option value="90daysAgo"
			<?php selected ( "90daysAgo", $period, true ); ?>><?php _e("Last 90 Days",'ga-dash'); ?></option>
	</select>
	<?php if (!$realtime) {?>
	<select id="ga_dash_query" name="query" onchange="this.form.submit()">
		<option value="visits" <?php selected ( "visits", $query, true ); ?>><?php _e("Visits",'ga-dash'); ?></option>
		<option value="visitors"
			<?php selected ( "visitors", $query, true ); ?>><?php _e("Visitors",'ga-dash'); ?></option>
		<option value="organicSearches"
			<?php selected ( "organicSearches", $query, true ); ?>><?php _e("Organic",'ga-dash'); ?></option>
		<option value="pageviews"
			<?php selected ( "pageviews", $query, true ); ?>><?php _e("Page Views",'ga-dash'); ?></option>
		<option value="visitBounceRate"
			<?php selected ( "visitBounceRate", $query, true ); ?>><?php _e("Bounce Rate",'ga-dash'); ?></option>
	</select> 	
	<?php }?>
</form>
<?php
			switch ($period) {
				
				case 'today' :
					$from = 'today';
					$to = 'today';
					$haxis = 4;
					$dh = __ ( "Hour", 'ga-dash' );
					break;
				
				case 'yesterday' :
					$from = 'yesterday';
					$to = 'yesterday';
					$haxis = 4;
					$dh = __ ( "Hour", 'ga-dash' );
					break;
				
				case '7daysAgo' :
					$from = '7daysAgo';
					$to = 'yesterday';
					$haxis = 2;
					$dh = __ ( "Date", 'ga-dash' );
					break;
				
				case '14daysAgo' :
					$from = '14daysAgo';
					$to = 'yesterday';
					$haxis = 3;
					$dh = __ ( "Date", 'ga-dash' );
					break;
				
				case '30daysAgo' :
					$from = '30daysAgo';
					$to = 'yesterday';
					$haxis = 5;
					$dh = __ ( "Date", 'ga-dash' );
					break;
				
				default :
					$from = '90daysAgo';
					$to = 'yesterday';
					$haxis = 16;
					$dh = __ ( "Date", 'ga-dash' );
					break;
			}
			
			if ($realtime == "realtime") {
				
				wp_register_style ( 'jquery-ui-tooltip-html', $GADASH_Config->plugin_url . '/realtime/jquery/jquery.ui.tooltip.html.css' );
				wp_enqueue_style ( 'jquery-ui-tooltip-html' );
				
				if (! wp_script_is ( 'jquery' )) {
					wp_enqueue_script ( 'jquery' );
				}
				if (! wp_script_is ( 'jquery-ui-tooltip' )) {
					wp_enqueue_script ( "jquery-ui-tooltip" );
				}
				if (! wp_script_is ( 'jquery-ui-core' )) {
					wp_enqueue_script ( "jquery-ui-core" );
				}
				if (! wp_script_is ( 'jquery-ui-position' )) {
					wp_enqueue_script ( "jquery-ui-position" );
				}
				if (! wp_script_is ( 'jquery-ui-position' )) {
					wp_enqueue_script ( "jquery-ui-position" );
				}
				
				wp_register_script ( "jquery-ui-tooltip-html", $GADASH_Config->plugin_url . '/realtime/jquery/jquery.ui.tooltip.html.js' );
				wp_enqueue_script ( "jquery-ui-tooltip-html" );
			} else {
				
				switch ($query) {
					
					case 'visitors' :
						$title = __ ( "Visitors", 'ga-dash' );
						break;
					
					case 'pageviews' :
						$title = __ ( "Page Views", 'ga-dash' );
						break;
					
					case 'visitBounceRate' :
						$title = __ ( "Bounce Rate", 'ga-dash' );
						break;
					
					case 'organicSearches' :
						$title = __ ( "Organic Searches", 'ga-dash' );
						break;
					
					default :
						$title = __ ( "Visits", 'ga-dash' );
				}
			}
			
			if ($query == 'visitBounceRate') {
				$formater = "var formatter = new google.visualization.NumberFormat({
				  pattern: '#,##%',
				  fractionDigits: 2
				});
			
				formatter.format(data, 1);	";
			} else {
				$formater = '';
			}
			
			$ga_dash_statsdata = $GADASH_GAPI->ga_dash_main_charts ( $projectId, $period, $from, $to, $query );
			if (! $ga_dash_statsdata) {
				echo '<p>' . __ ( "No stats available. Please check the Debugging Data section for possible errors", 'ga-dash' ) . '</p><form action="' . menu_page_url ( 'gadash_settings', false ) . '" method="POST">' . get_submit_button ( __ ( "Error Log", 'ga-dash' ), 'secondary' ) . '</form>';
				return;
			}
			
			$ga_dash_bottom_stats = $GADASH_GAPI->ga_dash_bottom_stats ( $projectId, $period, $from, $to );
			if (! $ga_dash_bottom_stats) {
				echo '<p>' . __ ( "No stats available. Please check the Debugging Data section for possible errors", 'ga-dash' ) . '</p><form action="' . menu_page_url ( 'gadash_settings', false ) . '" method="POST">' . get_submit_button ( __ ( "Error Log", 'ga-dash' ), 'secondary' ) . '</form>';
				return;
			}
			
			if (isset ( $GADASH_Config->options ['ga_dash_style'] )) {
				$light_color = $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], 40 );
				$dark_color = $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 20 );
				$css = "colors:['" . $GADASH_Config->options ['ga_dash_style'] . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 20 ) . "'],";
				$color = $GADASH_Config->options ['ga_dash_style'];
			} else {
				$css = "";
				$color = "#3366CC";
			}
			
			$code = '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(ga_dash_callback);
		
	  function ga_dash_callback(){
			if(typeof ga_dash_drawstats == "function"){
				ga_dash_drawstats();
			}
			if(typeof ga_dash_drawmap == "function"){
				ga_dash_drawmap();
			}
			if(typeof ga_dash_drawpgd == "function"){
				ga_dash_drawpgd();
			}
			if(typeof ga_dash_drawrd == "function"){
				ga_dash_drawrd();
			}
			if(typeof ga_dash_drawsd == "function"){
				ga_dash_drawsd();
			}
			if(typeof ga_dash_drawtraffic == "function"){
				ga_dash_drawtraffic();
			}
	  }';
			
			if ($realtime != "realtime") {
				
				$code .= 'function ga_dash_drawstats() {
        var data = google.visualization.arrayToDataTable([' . "
          ['" . $dh . "', '" . $title . "']," . $ga_dash_statsdata . "
        ]);
		
        var options = {
		  legend: {position: 'none'},
		  pointSize: 3," . $css . "
          title: '" . $title . "',
		  chartArea: {width: '85%'},
		  vAxis: {minValue: 0},
          hAxis: { title: '" . $dh . "',  titleTextStyle: {color: '" . $dark_color . "'}, showTextEvery: " . $haxis . "}
		};
		" . $formater . "
        var chart = new google.visualization.AreaChart(document.getElementById('ga_dash_statsdata'));
		chart.draw(data, options);
		
      }";
			}
			$ga_dash_visits_country = '';
			if ($GADASH_Config->options ['ga_dash_map'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] )) {
				$ga_dash_visits_country = $GADASH_GAPI->ga_dash_visits_country ( $projectId, $from, $to );
				if ($ga_dash_visits_country) {
					
					$code .= '
			google.load("visualization", "1", {packages:["geochart"]})
			function ga_dash_drawmap() {
			var data = google.visualization.arrayToDataTable([' . "
			  ['" . __ ( "Country/City", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . "']," . $ga_dash_visits_country . "
			]);
		
			var options = {";
					
					$code .= "colors: ['" . $light_color . "', '" . $dark_color . "'],";
					
					if ($GADASH_Config->options ['ga_target_geomap']) {
						$code .= "\nregion : '" . $GADASH_Config->options ['ga_target_geomap'] . "',";
						$code .= "\ndisplayMode : 'markers',";
						$code .= "\ndatalessRegionColor : 'EFEFEF'";
					}
					
					$code .= "\n};\nvar chart = new google.visualization.GeoChart(document.getElementById('ga_dash_mapdata'));
			chart.draw(data, options);
		
		  }";
				}
			}
			$ga_dash_traffic_sources = '';
			$ga_dash_new_return = '';
			if ($GADASH_Config->options ['ga_dash_traffic'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] )) {
				$ga_dash_traffic_sources = $GADASH_GAPI->ga_dash_traffic_sources ( $projectId, $from, $to );
				$ga_dash_new_return = $GADASH_GAPI->ga_dash_new_return ( $projectId, $from, $to );
				if ($ga_dash_traffic_sources and $ga_dash_new_return) {
					$code .= '
			google.load("visualization", "1", {packages:["corechart"]})
			function ga_dash_drawtraffic() {
			var data = google.visualization.arrayToDataTable([' . "
			  ['" . __ ( "Source", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . "']," . $ga_dash_traffic_sources . '
			]);
		
			var datanvr = google.visualization.arrayToDataTable([' . "
			  ['" . __ ( "Type", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . "']," . $ga_dash_new_return . "
			]);
		
			var chart = new google.visualization.PieChart(document.getElementById('ga_dash_trafficdata'));
			chart.draw(data, {
				is3D: false,
				tooltipText: 'percentage',
				legend: 'none',
				title: '" . __ ( "Traffic Sources", 'ga-dash' ) . "',
				colors:['" . $GADASH_Config->options ['ga_dash_style'] . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 10 ) . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], + 20 ) . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], + 10 ) . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 20 ) . "']
			});
		
			var chart1 = new google.visualization.PieChart(document.getElementById('ga_dash_nvrdata'));
			chart1.draw(datanvr,  {
				is3D: false,
				tooltipText: 'percentage',
				legend: 'none',
				title: '" . __ ( "New vs. Returning", 'ga-dash' ) . "',
				colors:['" . $GADASH_Config->options ['ga_dash_style'] . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 10 ) . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], + 20 ) . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], + 10 ) . "','" . $tools->colourVariator ( $GADASH_Config->options ['ga_dash_style'], - 20 ) . "']				
			});
		
		  }";
				}
			}
			$ga_dash_top_pages = '';
			if ($GADASH_Config->options ['ga_dash_pgd'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] )) {
				$ga_dash_top_pages = $GADASH_GAPI->ga_dash_top_pages ( $projectId, $from, $to );
				if ($ga_dash_top_pages) {
					$code .= '
			google.load("visualization", "1", {packages:["table"]})
			function ga_dash_drawpgd() {
			var data = google.visualization.arrayToDataTable([' . "
			  ['" . __ ( "Top Pages", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . "']," . $ga_dash_top_pages . "
			]);
		
			var options = {
				page: 'enable',
				pageSize: 6,
				width: '100%',
				allowHtml:true			
			};
		
			var chart = new google.visualization.Table(document.getElementById('ga_dash_pgddata'));
			chart.draw(data, options);
		
		  }";
				}
			}
			$ga_dash_top_referrers = '';
			if ($GADASH_Config->options ['ga_dash_rd'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] )) {
				$ga_dash_top_referrers = $GADASH_GAPI->ga_dash_top_referrers ( $projectId, $from, $to );
				if ($ga_dash_top_referrers) {
					$code .= '
			google.load("visualization", "1", {packages:["table"]})
			function ga_dash_drawrd() {
			var datar = google.visualization.arrayToDataTable([' . "
			  ['" . __ ( "Top Referrers", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . "']," . $ga_dash_top_referrers . "
			]);
		
			var options = {
				page: 'enable',
				pageSize: 6,
				width: '100%',
				allowHtml:true	
			};
		
			var chart = new google.visualization.Table(document.getElementById('ga_dash_rdata'));
			chart.draw(datar, options);
		
		  }";
				}
			}
			$ga_dash_top_searches = '';
			if ($GADASH_Config->options ['ga_dash_sd'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] )) {
				$ga_dash_top_searches = $GADASH_GAPI->ga_dash_top_searches ( $projectId, $from, $to );
				if ($ga_dash_top_searches) {
					$code .= '
			google.load("visualization", "1", {packages:["table"]})
			function ga_dash_drawsd() {
		
			var datas = google.visualization.arrayToDataTable([' . "
			  ['" . __ ( "Top Searches", 'ga-dash' ) . "', '" . __ ( "Visits", 'ga-dash' ) . "']," . $ga_dash_top_searches . "
			]);
		
			var options = {
				page: 'enable',
				pageSize: 6,
				width: '100%'
			};
		
			var chart = new google.visualization.Table(document.getElementById('ga_dash_sdata'));
			chart.draw(datas, options);
		
		  }";
				}
			}
			$code .= "</script>";
			$code .= "</script>";
			
			if ($realtime != "realtime") {
				$code .= '<div id="ga_dash_statsdata" class="widefat" style="height: 350px;"></div>
		<div id="details_div">
		
			<table class="gatable" cellpadding="4">
			<tr>
			<td width="24%">' . __ ( "Visits:", 'ga-dash' ) . '</td>
			<td width="12%" class="gavalue"><a href="?query=visits&period=' . $period . '" class="gatable">' . (int)$ga_dash_bottom_stats ['rows'] [0] [1] . '</td>
			<td width="24%">' . __ ( "Visitors:", 'ga-dash' ) . '</td>
			<td width="12%" class="gavalue"><a href="?query=visitors&period=' . $period . '" class="gatable">' . (int)$ga_dash_bottom_stats ['rows'] [0] [2] . '</a></td>
			<td width="24%">' . __ ( "Page Views:", 'ga-dash' ) . '</td>
			<td width="12%" class="gavalue"><a href="?query=pageviews&period=' . $period . '" class="gatable">' . (int)$ga_dash_bottom_stats ['rows'] [0] [3] . '</a></td>
			</tr>
			<tr>
			<td>' . __ ( "Bounce Rate:", 'ga-dash' ) . '</td>
			<td class="gavalue"><a href="?query=visitBounceRate&period=' . $period . '" class="gatable">' . (double)round ( $ga_dash_bottom_stats ['rows'] [0] [4], 2 ) . '%</a></td>
			<td>' . __ ( "Organic Search:", 'ga-dash' ) . '</td>
			<td class="gavalue"><a href="?query=organicSearches&period=' . $period . '" class="gatable">' . (int)$ga_dash_bottom_stats ['rows'] [0] [5] . '</a></td>
			<td>' . __ ( "Pages per Visit:", 'ga-dash' ) . '</td>
			<td class="gavalue"><a href="#" class="gatable">' . (double)(($ga_dash_bottom_stats ['rows'] [0] [1]) ? round ( $ga_dash_bottom_stats ['rows'] [0] [3] / $ga_dash_bottom_stats ['rows'] [0] [1], 2 ) : '0') . '</a></td>
			</tr>
			</table>
			
		</div>';
			} else {
				
				if ($GADASH_Config->options ['ga_dash_userapi']) {
					$code .= "<p style='padding:100px;line-height:2em;'>" . __ ( "This is a beta feature and is only available when using my Developer Key! (", 'ga-dash' ) . '<a href="http://deconf.com/google-analytics-dashboard-real-time-reports/" target="_blank">' . __ ( "more about this feature", 'ga-dash' ) . '</a>' . __ ( ")", 'ga-dash' ) . "</p>";
				} else {
					
					$code .= "<table width='90%' class='realtime'>
						<tr>
							<td class='gadash-tdo-left'>
								<div><span class='gadash-online' id='gadash-online'>&nbsp;</span></div>
							</td>
							<td class='gadash-tdo-right' id='gadash-tdo-right'>
								<span class=\"gadash-bigtext\">" . __ ( "REFERRAL", 'ga-dash' ) . ": 0</span><br /><br />
								<span class=\"gadash-bigtext\">" . __ ( "ORGANIC", 'ga-dash' ) . ": 0</span><br /><br />
								<span class=\"gadash-bigtext\">" . __ ( "SOCIAL", 'ga-dash' ) . ": 0</span><br /><br />
							</td>
							<td class='gadash-tdo-rights' id='gadash-tdo-rights'>
								<span class=\"gadash-bigtext\">" . __ ( "DIRECT", 'ga-dash' ) . ": 0</span><br /><br />
								<span class=\"gadash-bigtext\">" . __ ( "NEW", 'ga-dash' ) . ": 0</span><br /><br />
								<span class=\"gadash-bigtext\">" . __ ( "RETURN", 'ga-dash' ) . ": 0</span><br /><br />
							</td>
						</tr>
						<tr>
						<td id='gadash-pages' class='gadash-pages' colspan='3'>&nbsp;</td>
						</tr>
					</table>";
					$code .= $GADASH_GAPI->ga_realtime ();
				}
			}
			if ($GADASH_Config->options ['ga_dash_map'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] ) and $ga_dash_visits_country) {
				$code .= '<br /><h3>';
				if ($GADASH_Config->options ['ga_target_geomap']) {
					$GADASH_GAPI->getcountrycodes ();
					$code .= __ ( "Visits from ", 'ga-dash' ) . $GADASH_GAPI->country_codes [$GADASH_Config->options ['ga_target_geomap']];
				} else {
					$code .= __ ( "Visits by Country", 'ga-dash' );
				}
				$code .= '</h3>
		<div id="ga_dash_mapdata"></div>';
			}
			
			if ($GADASH_Config->options ['ga_dash_traffic'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] ) and ($ga_dash_top_referrers or $ga_dash_top_pages or ($ga_dash_traffic_sources and $ga_dash_new_return))) {
				$code .= '<br /><h3>' . __ ( "Traffic Overview", 'ga-dash' ) . '</h3>
				<div style="width:100%;clear:both;"><div style="width:50%; float:left;"><div id="ga_dash_trafficdata"></div></div><div style="width:50%;float:left;"><div id="ga_dash_nvrdata"></div></div></div><div style="clear:both;"></div>';
			}
			
			if ($GADASH_Config->options ['ga_dash_pgd'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] ))
				$code .= '<div id="ga_dash_pgddata"></div>';
			if ($GADASH_Config->options ['ga_dash_rd'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] ))
				$code .= '<div id="ga_dash_rdata"></div>';
			if ($GADASH_Config->options ['ga_dash_sd'] and $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_back'] ))
				$code .= '<div id="ga_dash_sdata"></div>';
			
			echo $code;
			
		}
	}
}

if (is_admin ()) {
	$GLOBALS ['GADASH_Widgets'] = new GADASH_Widgets ();
}
