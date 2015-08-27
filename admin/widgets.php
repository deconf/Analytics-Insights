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
			wp_add_dashboard_widget( 'gadash-widget', __( "Google Analytics Dashboard", 'google-analytics-dashboard-for-wp' ), array( $this, 'dashboard_widget' ), $control_callback = null );
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
			?>
<form id="ga-dash" method="POST">
						<?php
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
			if ( isset( $_REQUEST['gadwpquery'] ) ) {
				$query = $_REQUEST['gadwpquery'];
			} else {
				$default_metric = GADWP_Tools::get_cookie( 'default_metric' );
				$query = $default_metric ? $default_metric : 'sessions';
			}
			if ( isset( $_REQUEST['gadwpperiod'] ) ) {
				$period = $_REQUEST['gadwpperiod'];
			} else {
				$default_dimension = GADWP_Tools::get_cookie( 'default_dimension' );
				$period = $default_dimension ? $default_dimension : '30daysAgo';
			}

			?>
				<select id="ga_dash_period" name="gadwpperiod" onchange="this.form.submit()">
        <option value="realtime" <?php selected ( "realtime", $period, true ); ?>><?php _e("Real-Time",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="today" <?php selected ( "today", $period, true ); ?>><?php _e("Today",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="yesterday" <?php selected ( "yesterday", $period, true ); ?>><?php _e("Yesterday",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="7daysAgo" <?php selected ( "7daysAgo", $period, true ); ?>><?php printf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 7 ); ?></option>
        <option value="14daysAgo" <?php selected ( "14daysAgo", $period, true ); ?>><?php printf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 14 ); ?></option>
        <option value="30daysAgo" <?php selected ( "30daysAgo", $period, true ); ?>><?php printf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 30 ); ?></option>
        <option value="90daysAgo" <?php selected ( "90daysAgo", $period, true ); ?>><?php printf( __( "Last %d Days", 'google-analytics-dashboard-for-wp' ), 90 ); ?></option>
        <option value="365daysAgo" <?php selected ( "365daysAgo", $period, true ); ?>><?php printf( _n( "%s Year", "%s Years", 1, 'google-analytics-dashboard-for-wp' ), __('One', 'google-analytics-dashboard-for-wp') ); ?></option>
        <option value="1095daysAgo" <?php selected ( "1095daysAgo", $period, true ); ?>><?php printf( _n( "%s Year", "%s Years", 3, 'google-analytics-dashboard-for-wp' ), __('Three', 'google-analytics-dashboard-for-wp') ); ?></option>
    </select>

				<?php if ($period != 'realtime') {?>
					<select id="ga_dash_query" name="gadwpquery" onchange="this.form.submit()">
        <option value="sessions" <?php selected ( "sessions", $query, true ); ?>><?php _e("Sessions",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="users" <?php selected ( "users", $query, true ); ?>><?php _e("Users",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="organicSearches" <?php selected ( "organicSearches", $query, true ); ?>><?php _e("Organic",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="pageviews" <?php selected ( "pageviews", $query, true ); ?>><?php _e("Page Views",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="visitBounceRate" <?php selected ( "visitBounceRate", $query, true ); ?>><?php _e("Bounce Rate",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="locations" <?php selected ( "locations", $query, true ); ?>><?php _e("Location",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="contentpages" <?php selected ( "contentpages", $query, true ); ?>><?php _e("Pages",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="referrers" <?php selected ( "referrers", $query, true ); ?>><?php _e("Referrers",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="searches" <?php selected ( "searches", $query, true ); ?>><?php _e("Searches",'google-analytics-dashboard-for-wp'); ?></option>
        <option value="trafficdetails" <?php selected ( "trafficdetails", $query, true ); ?>><?php _e("Traffic Details",'google-analytics-dashboard-for-wp'); ?></option>
    </select>
				<?php }?>
	</form>
<div id="gadash-progressbar"></div>
<?php
			switch ( $period ) {
				case 'today' :
					$from = 'today';
					$to = 'today';
					$haxis = 4;
					break;
				case 'yesterday' :
					$from = 'yesterday';
					$to = 'yesterday';
					$haxis = 4;
					break;
				case '7daysAgo' :
					$from = '7daysAgo';
					$to = 'yesterday';
					$haxis = 2;
					break;
				case '14daysAgo' :
					$from = '14daysAgo';
					$to = 'yesterday';
					$haxis = 3;
					break;
				case '90daysAgo' :
					$from = '90daysAgo';
					$to = 'yesterday';
					$haxis = 16;
					break;
				case '365daysAgo' :
					$from = '365daysAgo';
					$to = 'yesterday';
					$haxis = 5;
					break;
				case '1095daysAgo' :
					$from = '1095daysAgo';
					$to = 'yesterday';
					$haxis = 5;
					break;
				default :
					$from = '30daysAgo';
					$to = 'yesterday';
					$haxis = 5;
					break;
			}
			if ( $query == 'visitBounceRate' ) {
				$formater = "var formatter = new google.visualization.NumberFormat({
				  suffix: '%',
				  fractionDigits: 2
				});

				formatter.format(data, 1);	";
			} else {
				$formater = '';
			}

			if ( isset( $this->gadwp->config->options['ga_dash_style'] ) ) {
				$light_color = GADWP_Tools::colourVariator( $this->gadwp->config->options['ga_dash_style'], 40 );
				$dark_color = GADWP_Tools::colourVariator( $this->gadwp->config->options['ga_dash_style'], - 20 );
				$css = "colors:['" . $this->gadwp->config->options['ga_dash_style'] . "','" . GADWP_Tools::colourVariator( $this->gadwp->config->options['ga_dash_style'], - 20 ) . "'],";
				$color = $this->gadwp->config->options['ga_dash_style'];
			} else {
				$css = "";
				$color = "#3366CC";
			}
			if ( $period == 'realtime' ) {
				wp_register_style( 'jquery-ui-tooltip-html', GADWP_URL . 'realtime/jquery/jquery.ui.tooltip.html.css' );
				wp_enqueue_style( 'jquery-ui-tooltip-html' );
				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
				}
				if ( ! wp_script_is( 'jquery-ui-tooltip' ) ) {
					wp_enqueue_script( "jquery-ui-tooltip" );
				}
				if ( ! wp_script_is( 'jquery-ui-core' ) ) {
					wp_enqueue_script( "jquery-ui-core" );
				}
				if ( ! wp_script_is( 'jquery-ui-position' ) ) {
					wp_enqueue_script( "jquery-ui-position" );
				}
				if ( ! wp_script_is( 'jquery-ui-position' ) ) {
					wp_enqueue_script( "jquery-ui-position" );
				}
				wp_register_script( "jquery-ui-tooltip-html", GADWP_URL . 'realtime/jquery/jquery.ui.tooltip.html.js' );
				wp_enqueue_script( "jquery-ui-tooltip-html" );
			}
			if ( $period == 'realtime' ) {
				?>
<div class="realtime">
    <div class="gadash-rt-box">
        <div class='gadash-tdo-left'>
            <div class='gadash-online' id='gadash-online'>0</div>
        </div>
        <div class='gadash-tdo-right' id='gadash-tdo-right'>
            <div class="gadash-bigtext">
                <div class="gadash-bleft"><?php _e( "REFERRAL", 'google-analytics-dashboard-for-wp' );?></div>
                <div class="gadash-bright">0</div>
            </div>
            <div class="gadash-bigtext">
                <div class="gadash-bleft"><?php _e( "ORGANIC", 'google-analytics-dashboard-for-wp' );?></div>
                <div class="gadash-bright">0</div>
            </div>
            <div class="gadash-bigtext">
                <div class="gadash-bleft"><?php _e( "SOCIAL", 'google-analytics-dashboard-for-wp' );?></div>
                <div class="gadash-bright">0</div>
            </div>
            <div class="gadash-bigtext">
                <div class="gadash-bleft"><?php _e( "CAMPAIGN", 'google-analytics-dashboard-for-wp' );?></div>
                <div class="gadash-bright">0</div>
            </div>
            <div class="gadash-bigtext">
                <div class="gadash-bleft"><?php _e( "DIRECT", 'google-analytics-dashboard-for-wp' );?></div>
                <div class="gadash-bright">0</div>
            </div>
            <div class="gadash-bigtext">
                <div class="gadash-bleft"><?php _e( "NEW", 'google-analytics-dashboard-for-wp' );?></div>
                <div class="gadash-bright">0</div>
            </div>
        </div>
    </div>
    <div>
        <div id='gadash-pages' class='gadash-pages'>&nbsp;</div>
    </div>
</div>
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
            		jQuery('#gadash-widget *').tooltip();
            	});

            	function onlyUniqueValues(value, index, self) {
            		return self.indexOf(value) === index;
            	 }

            	function countsessions(data, searchvalue) {
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
            				table += "<tr><td class='gadash-pgdetailsl'>"+data[i].value+"</td><td class='gadash-pgdetailsr'>"+data[ i ].count+"</td></tr>";
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
            		var countcpg = 0;
            		var tablerfr = "";
            		var tablekwd = "";
            		var tablescl = "";
            		var tablecpg = "";
            		var tabledrt = "";
            		for ( var i = 0; i < newdata.length; i = i + 1 ) {
            			if (newdata[i][0] == searchvalue){
            				var pagetitle = newdata[i][5];

            				switch (newdata[i][3]){

            					case "REFERRAL": 	countrfr += parseInt(newdata[ i ][6]);
            										tablerfr +=	"<tr><td class='gadash-pgdetailsl'>"+newdata[i][1]+"</td><td class='gadash-pgdetailsr'>"+newdata[ i ][6]+"</td></tr>";
            										break;
            					case "ORGANIC": 	countkwd += parseInt(newdata[ i ][6]);
            										tablekwd +=	"<tr><td class='gadash-pgdetailsl'>"+newdata[i][2]+"</td><td class='gadash-pgdetailsr'>"+newdata[ i ][6]+"</td></tr>";
            										break;
            					case "SOCIAL": 		countscl += parseInt(newdata[ i ][6]);
            										tablescl +=	"<tr><td class='gadash-pgdetailsl'>"+newdata[i][1]+"</td><td class='gadash-pgdetailsr'>"+newdata[ i ][6]+"</td></tr>";
            										break;
            					case "CUSTOM": 		countcpg += parseInt(newdata[ i ][6]);
                    								tablecpg +=	"<tr><td class='gadash-pgdetailsl'>"+newdata[i][1]+"</td><td class='gadash-pgdetailsr'>"+newdata[ i ][6]+"</td></tr>";
                    								break;
            					case "DIRECT": 		countdrt += parseInt(newdata[ i ][6]);
            										break;
            				};
            			};
            		};
            		if (countrfr){
            			tablerfr = "<table><tr><td><?php _e("REFERRALS", 'google-analytics-dashboard-for-wp');?> ("+countrfr+")</td></tr>"+tablerfr+"</table><br />";
            		}
            		if (countkwd){
            			tablekwd = "<table><tr><td><?php _e("KEYWORDS", 'google-analytics-dashboard-for-wp');?> ("+countkwd+")</td></tr>"+tablekwd+"</table><br />";
            		}
            		if (countscl){
            			tablescl = "<table><tr><td><?php _e("SOCIAL", 'google-analytics-dashboard-for-wp');?> ("+countscl+")</td></tr>"+tablescl+"</table><br />";
            		}
            		if (countcpg){
            			tablecpg = "<table><tr><td><?php _e("CAMPAIGN", 'google-analytics-dashboard-for-wp');?> ("+countcpg+")</td></tr>"+tablecpg+"</table><br />";
            		}
            		if (countdrt){
            			tabledrt = "<table><tr><td><?php _e("DIRECT", 'google-analytics-dashboard-for-wp');?> ("+countdrt+")</td></tr></table><br />";
            		}
            		return ("<p><center><strong>"+pagetitle+"</strong></center></p>"+tablerfr+tablekwd+tablescl+tablecpg+tabledrt);
            	 }

            	 function online_refresh(){
            		if (focusFlag){

            		jQuery.post(ajaxurl, {action: "gadash_get_widgetreports",projectId: "<?php echo $projectId; ?>",from: false,to: false,query: "realtime",gadash_security_widget_reports: "<?php echo wp_create_nonce('gadash_get_widgetreports'); ?>"}, function(results){

						data = results[0];

                        if (jQuery.isNumeric(data) || typeof data === "undefined"){
                            data = [];
                            data["totalsForAllResults"] = []
                            data["totalsForAllResults"]["rt:activeUsers"] = "0";
                            data["rows"]= [];
                        }

            			if (data["totalsForAllResults"]["rt:activeUsers"]!==document.getElementById("gadash-online").innerHTML){
            				jQuery("#gadash-online").fadeOut("slow");
            				jQuery("#gadash-online").fadeOut(500);
            				jQuery("#gadash-online").fadeOut("slow", function() {
            					if ((parseInt(data["totalsForAllResults"]["rt:activeUsers"]))<(parseInt(document.getElementById("gadash-online").innerHTML))){
            						jQuery("#gadash-online").css({'background-color' : '#FFE8E8'});
            					}else{
            						jQuery("#gadash-online").css({'background-color' : '#E0FFEC'});
            					}
            					document.getElementById("gadash-online").innerHTML = data["totalsForAllResults"]["rt:activeUsers"];
            				});
            				jQuery("#gadash-online").fadeIn("slow");
            				jQuery("#gadash-online").fadeIn(500);
            				jQuery("#gadash-online").fadeIn("slow", function() {
            					jQuery("#gadash-online").css({'background-color' : '#FFFFFF'});
            				});
            			};

            			if (data["totalsForAllResults"]["rt:activeUsers"] == 0){
            				data["rows"]= [];
            			};

            			var pagepath = [];
            			var referrals = [];
            			var keywords = [];
            			var social = [];
            			var visittype = [];
            			var custom = [];
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
            				if (data["rows"][i][3]=="CUSTOM"){
            					custom.push( data["rows"][ i ][1] );
            				}
            				visittype.push( data["rows"][ i ][3] );
            			}

            			var upagepathstats = [];
               			var upagepath = pagepath.filter(onlyUniqueValues);
            			for ( var i = 0; i < upagepath.length; i = i + 1 ) {
            				upagepathstats[i]={"pagepath":upagepath[i],"count":countsessions(data,upagepath[i])};
            			}
            			upagepathstats.sort( function(a,b){ return b.count - a.count } );

            			var pgstatstable = "";
            			for ( var i = 0; i < upagepathstats.length; i = i + 1 ) {
            				if (i < <?php echo $this->gadwp->config->options['ga_realtime_pages']; ?>){
            					pgstatstable += '<div class="gadash-pline"><div class="gadash-pleft"><a href="#" data-tooltip="'+gadash_pagedetails(data, upagepathstats[i].pagepath)+'">'+upagepathstats[i].pagepath.substring(0,70)+'</a></div><div class="gadash-pright">'+upagepathstats[i].count+'</div></div>';
            				}
            			}
            			document.getElementById("gadash-pages").innerHTML='<br /><div class="gadash-pg">'+pgstatstable+'</div>';

            			var ureferralsstats = [];
            			var ureferrals = referrals.filter(onlyUniqueValues);
            			for ( var i = 0; i < ureferrals.length; i = i + 1 ) {
            				ureferralsstats[i]={"value":ureferrals[i],"count":countsessions(data,ureferrals[i])};
            			}
            			ureferralsstats.sort( function(a,b){ return b.count - a.count } );

            			var ukeywordsstats = [];
            			var ukeywords = keywords.filter(onlyUniqueValues);
            			for ( var i = 0; i < ukeywords.length; i = i + 1 ) {
            				ukeywordsstats[i]={"value":ukeywords[i],"count":countsessions(data,ukeywords[i])};
            			}
            			ukeywordsstats.sort( function(a,b){ return b.count - a.count } );

            			var usocialstats = [];
            			var usocial = social.filter(onlyUniqueValues);
            			for ( var i = 0; i < usocial.length; i = i + 1 ) {
            				usocialstats[i]={"value":usocial[i],"count":countsessions(data,usocial[i])};
            			}
            			usocialstats.sort( function(a,b){ return b.count - a.count } );

            			var ucustomstats = [];
            			var ucustom = custom.filter(onlyUniqueValues);
            			for ( var i = 0; i < ucustom.length; i = i + 1 ) {
            				ucustomstats[i]={"value":ucustom[i],"count":countsessions(data,ucustom[i])};
            			}
            			ucustomstats.sort( function(a,b){ return b.count - a.count } );

            			var uvisittype = ["REFERRAL","ORGANIC","SOCIAL","CUSTOM"];
            			document.getElementById("gadash-tdo-right").innerHTML = '<div class="gadash-bigtext"><a href="#" data-tooltip="'+gadash_generatetooltip(ureferralsstats)+'"><div class="gadash-bleft">'+'<?php _e("REFERRAL", 'google-analytics-dashboard-for-wp');?>'+'</a></div><div class="gadash-bright">'+countsessions(data,uvisittype[0])+'</div></div>';
            			document.getElementById("gadash-tdo-right").innerHTML += '<div class="gadash-bigtext"><a href="#" data-tooltip="'+gadash_generatetooltip(ukeywordsstats)+'"><div class="gadash-bleft">'+'<?php _e("ORGANIC", 'google-analytics-dashboard-for-wp');?>'+'</a></div><div class="gadash-bright">'+countsessions(data,uvisittype[1])+'</div></div>';
            			document.getElementById("gadash-tdo-right").innerHTML += '<div class="gadash-bigtext"><a href="#" data-tooltip="'+gadash_generatetooltip(usocialstats)+'"><div class="gadash-bleft">'+'<?php _e("SOCIAL", 'google-analytics-dashboard-for-wp');?>'+'</a></div><div class="gadash-bright">'+countsessions(data,uvisittype[2])+'</div></div>';
            			document.getElementById("gadash-tdo-right").innerHTML += '<div class="gadash-bigtext"><a href="#" data-tooltip="'+gadash_generatetooltip(ucustomstats)+'"><div class="gadash-bleft">'+'<?php _e("CAMPAIGN", 'google-analytics-dashboard-for-wp');?>'+'</a></div><div class="gadash-bright">'+countsessions(data,uvisittype[3])+'</div></div>';

            			var uvisitortype = ["DIRECT","NEW"];
            			document.getElementById("gadash-tdo-right").innerHTML += '<div class="gadash-bigtext"><div class="gadash-bleft">'+'<?php _e("DIRECT", 'google-analytics-dashboard-for-wp');?>'+'</div><div class="gadash-bright">'+countsessions(data,uvisitortype[0])+'</div></div>';
            			document.getElementById("gadash-tdo-right").innerHTML += '<div class="gadash-bigtext"><div class="gadash-bleft">'+'<?php _e("NEW", 'google-analytics-dashboard-for-wp');?>'+'</div><div class="gadash-bright">'+countsessions(data,uvisitortype[1])+'</div></div>';

            		});
               };
               };
               online_refresh();
               setInterval(online_refresh, 60000);
            </script>
<?php } else if (array_search($query, array('referrers','contentpages','searches')) !== false) {?>
<div id="gadash-trafficchannels"></div>
<div id="gadash-prs"></div>
<script type="text/javascript">
            	google.load("visualization", "1", {packages:["table","orgchart"]});

        		try {
        	    	NProgress.configure({ parent: "#gadash-progressbar" });
        	        NProgress.configure({ showSpinner: false });
        	        NProgress.start();
        		} catch(e) {
        			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
        			jQuery("#gadash-progressbar").html("<?php _e("A JavaScript Error is blocking plugin resources!", 'google-analytics-dashboard-for-wp'); ?>");
        		}

                jQuery.post(ajaxurl, {action: "gadash_get_widgetreports",projectId: "<?php echo $projectId; ?>",from: "<?php echo $from; ?>",to: "<?php echo $to; ?>",query: "<?php echo 'trafficchannels,' . $query; ?>",gadash_security_widget_reports: "<?php echo wp_create_nonce('gadash_get_widgetreports'); ?>"}, function(response){
                	if ( jQuery.isArray( response ) ) {

                        if (!jQuery.isNumeric(response[0])){
                        	if (jQuery.isArray(response[0])){
                            	gadash_trafficchannels=response[0];
                            	google.setOnLoadCallback(ga_dash_drawtrafficchannels(gadash_trafficchannels));
                             } else {
                     			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
                     			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
                     			console.log("\n********************* GADWP Log ********************* \n\n"+response[0]);
                     		}
                    	}else{
                            jQuery("#gadash-trafficchannels").css({"background-color":"#F7F7F7","height":"auto","padding-top":"125px","padding-bottom":"125px","color":"#000","text-align":"center"});
                            jQuery("#gadash-trafficchannels").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[0]+")");
                        }

	                    if (!jQuery.isNumeric(response[1])){
	                        if (jQuery.isArray(response[1])){
	                    	   gadash_prs=response[1];
	                    	   google.setOnLoadCallback(ga_dash_drawprs(gadash_prs));
	                        } else {
	                			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	                			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	                			console.log("\n********************* GADWP Log ********************* \n\n"+response[1]);
	                		}
	                	}else{
	                        jQuery("#gadash-prs").css({"background-color":"#F7F7F7","height":"auto","padding-top":"125px","padding-bottom":"125px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-prs").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[1]+")");
	                    }

                    }else{
             			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
             			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
             			console.log("\n********************* GADWP Log ********************* \n\n"+response);
                    }
					NProgress.done();
                });

            	function ga_dash_drawprs(gadash_prs) {
                	var data = google.visualization.arrayToDataTable(gadash_prs);
                	var options = {
                		page: 'enable',
                		pageSize: 10,
                		width: '100%',
                        allowHtml: true
                	};

                	var chart = new google.visualization.Table(document.getElementById('gadash-prs'));
                	chart.draw(data, options);
            	};

            	function ga_dash_drawtrafficchannels(gadash_trafficchannels) {
                	var data = google.visualization.arrayToDataTable(gadash_trafficchannels);
                	var options = {
                	    allowCollapse:true,
                		allowHtml:true
                	};

                	var chart = new google.visualization.OrgChart(document.getElementById('gadash-trafficchannels'));
                	chart.draw(data, options);
            	};
            </script>
<?php } else if ($query == 'trafficdetails') {?>
<div id="gadash-trafficchannels"></div>
<div class="gadash-floatwraper">
    <div id="gadash-trafficmediums"></div>
    <div id="gadash-traffictype"></div>
</div>
<div class="gadash-floatwraper">
    <div id="gadash-trafficorganic"></div>
    <div id="gadash-socialnetworks"></div>
</div>
<script type="text/javascript">
            	google.load("visualization", "1", {packages:["corechart","orgchart"]});

        		try {
        	    	NProgress.configure({ parent: "#gadash-progressbar" });
        	        NProgress.configure({ showSpinner: false });
        	        NProgress.start();
        		} catch(e) {
        			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
        			jQuery("#gadash-progressbar").html("<?php _e("A JavaScript Error is blocking plugin resources!", 'google-analytics-dashboard-for-wp'); ?>");
        		}

                jQuery.post(ajaxurl, {action: "gadash_get_widgetreports",projectId: "<?php echo $projectId; ?>",from: "<?php echo $from; ?>",to: "<?php echo $to; ?>",query: "trafficchannels,medium,visitorType,source,socialNetwork",gadash_security_widget_reports: "<?php echo wp_create_nonce('gadash_get_widgetreports'); ?>"}, function(response){
                	if ( jQuery.isArray( response ) ) {

	                    if (!jQuery.isNumeric(response[0])){
	                    	if (jQuery.isArray(response[0])){
	                        	gadash_trafficchannels=response[0];
	                   		    google.setOnLoadCallback(ga_dash_drawtrafficchannels(gadash_trafficchannels));
	                         } else {
	                 			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	                 			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	                 			console.log("\n********************* GADWP Log ********************* \n\n"+response[0]);
	                 		}
	                	}else{
	                        jQuery("#gadash-trafficchannels").css({"background-color":"#F7F7F7","height":"auto","padding-top":"125px","padding-bottom":"125px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-trafficchannels").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[0]+")");
	                    }

	                    if (!jQuery.isNumeric(response[1])){
	                    	if (jQuery.isArray(response[1])){
	                        	gadash_trafficmediums=response[1];
	                   		    google.setOnLoadCallback(ga_dash_drawtrafficmediums(gadash_trafficmediums));
	                         } else {
	                 			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	                 			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	                 			console.log("\n********************* GADWP Log ********************* \n\n"+response[1]);
	                 		}
	                	}else{
	                        jQuery("#gadash-trafficmediums").css({"background-color":"#F7F7F7","height":"auto","padding-top":"80px","padding-bottom":"80px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-trafficmediums").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[1]+")");
	                    }

	                    if (!jQuery.isNumeric(response[2])){
	                    	if (jQuery.isArray(response[2])){
	                    		gadash_traffictype=response[2];
	                    		google.setOnLoadCallback(ga_dash_drawtraffictype(gadash_traffictype));
	                         } else {
	                 			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	                 			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	                 			console.log("\n********************* GADWP Log ********************* \n\n"+response[2]);
	                 		}
	                	}else{
	                        jQuery("#gadash-traffictype").css({"background-color":"#F7F7F7","height":"auto","padding-top":"80px","padding-bottom":"80px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-traffictype").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[2]+")");
	                    }

	                    if (!jQuery.isNumeric(response[3])){
	                    	if (jQuery.isArray(response[3])){
	                        	gadash_trafficorganic=response[3];
	                        	google.setOnLoadCallback(ga_dash_drawtrafficorganic(gadash_trafficorganic));
	                         } else {
	                 			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	                 			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	                 			console.log("\n********************* GADWP Log ********************* \n\n"+response[3]);
	                 		}
	                	}else{
	                        jQuery("#gadash-trafficorganic").css({"background-color":"#F7F7F7","height":"auto","padding-top":"80px","padding-bottom":"80px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-trafficorganic").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[3]+")");
	                    }

	                    if (!jQuery.isNumeric(response[4])){
	                    	if (jQuery.isArray(response[4])){
	                        	gadash_socialnetworks=response[4];
	                   		    google.setOnLoadCallback(ga_dash_drawsocialnetworks(gadash_socialnetworks));
	                         } else {
	                 			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	                 			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	                 			console.log("\n********************* GADWP Log ********************* \n\n"+response[4]);
	                 		}
	                	}else{
	                        jQuery("#gadash-socialnetworks").css({"background-color":"#F7F7F7","height":"auto","padding-top":"80px","padding-bottom":"80px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-socialnetworks").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[4]+")");
	                    }

                    }else{
             			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
             			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
             			console.log("\n********************* GADWP Log ********************* \n\n"+response);
                    }
					NProgress.done();
                });

            	function ga_dash_drawtrafficmediums(gadash_trafficmediums) {
                	var data = google.visualization.arrayToDataTable(gadash_trafficmediums);
                	var options =  {
							is3D: false,
							tooltipText: 'percentage',
							legend: 'none',
							chartArea: {width: '99%',height: '80%'},
							title: '<?php _e( "Traffic Mediums", 'google-analytics-dashboard-for-wp' ); ?>',
							colors:['<?php echo esc_html($this->gadwp->config->options ['ga_dash_style']); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 20 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 20 )); ?>']
						};

                	var chart = new google.visualization.PieChart(document.getElementById('gadash-trafficmediums'));
                	chart.draw(data, options);
            	};

            	function ga_dash_drawtraffictype(gadash_traffictype) {
                	var data = google.visualization.arrayToDataTable(gadash_traffictype);
                	var options =  {
							is3D: false,
							tooltipText: 'percentage',
							legend: 'none',
							chartArea: {width: '99%',height: '80%'},
							title: '<?php _e( "Visitor Type", 'google-analytics-dashboard-for-wp' ); ?>',
							colors:['<?php echo esc_html($this->gadwp->config->options ['ga_dash_style']); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 20 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 20 )); ?>']
						};

                	var chart = new google.visualization.PieChart(document.getElementById('gadash-traffictype'));
                	chart.draw(data, options);
            	};

            	function ga_dash_drawsocialnetworks(gadash_socialnetworks) {
                	var data = google.visualization.arrayToDataTable(gadash_socialnetworks);
                	var options =  {
							is3D: false,
							tooltipText: 'percentage',
							legend: 'none',
							chartArea: {width: '99%',height: '80%'},
							title: '<?php _e( "Social Networks", 'google-analytics-dashboard-for-wp' ); ?>',
							colors:['<?php echo esc_html($this->gadwp->config->options ['ga_dash_style']); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 20 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 20 )); ?>']
						};

                	var chart = new google.visualization.PieChart(document.getElementById('gadash-socialnetworks'));
                	chart.draw(data, options);
            	};

            	function ga_dash_drawtrafficorganic(gadash_trafficorganic) {
                	var data = google.visualization.arrayToDataTable(gadash_trafficorganic);
                	var options =  {
							is3D: false,
							tooltipText: 'percentage',
							legend: 'none',
							chartArea: {width: '99%',height: '80%'},
							title: '<?php _e( "Search Engines", 'google-analytics-dashboard-for-wp' ); ?>',
							colors:['<?php echo esc_html($this->gadwp->config->options ['ga_dash_style']); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 20 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], + 10 )); ?>','<?php echo esc_html(GADWP_Tools::colourVariator ( $this->gadwp->config->options ['ga_dash_style'], - 20 )); ?>']
						};

                	var chart = new google.visualization.PieChart(document.getElementById('gadash-trafficorganic'));
                	chart.draw(data, options);
            	};

            	function ga_dash_drawtrafficchannels(gadash_trafficchannels) {
                	var data = google.visualization.arrayToDataTable(gadash_trafficchannels);
                	var options = {
                	    allowCollapse:true,
                		allowHtml:true
                	};

                	var chart = new google.visualization.OrgChart(document.getElementById('gadash-trafficchannels'));
                	chart.draw(data, options);
            	};
            </script>
<?php } else if ($query == 'locations') {?>
<div id="gadash-map"></div>
<div id="gadash-locations"></div>
<script type="text/javascript">
            	google.load("visualization", "1", {packages:["geochart","table"]});

        		try {
        	    	NProgress.configure({ parent: "#gadash-progressbar" });
        	        NProgress.configure({ showSpinner: false });
        	        NProgress.start();
        		} catch(e) {
        			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
        			jQuery("#gadash-progressbar").html("<?php _e("A JavaScript Error is blocking plugin resources!", 'google-analytics-dashboard-for-wp'); ?>");
        		}

                jQuery.post(ajaxurl, {action: "gadash_get_widgetreports",projectId: "<?php echo $projectId; ?>",from: "<?php echo $from; ?>",to: "<?php echo $to; ?>",query: "<?php echo $query; ?>",gadash_security_widget_reports: "<?php echo wp_create_nonce('gadash_get_widgetreports'); ?>"}, function(response){

                	if ( jQuery.isArray( response ) ) {
	                    if (!jQuery.isNumeric(response[0])){
	                    	if (jQuery.isArray(response[0])){
	                        	gadash_locations=response[0];
	                    		google.setOnLoadCallback(ga_dash_drawmaplocations(gadash_locations));
	                    		google.setOnLoadCallback(ga_dash_drawlocations(gadash_locations));
	                         } else {
	                 			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	                 			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	                 			console.log("\n********************* GADWP Log ********************* \n\n"+response[0]);
	                 		}
	                	}else{
	                        jQuery("#gadash-map").css({"background-color":"#F7F7F7","height":"auto","padding-top":"125px","padding-bottom":"125px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-map").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[0]+")");
	                        jQuery("#gadash-locations").css({"background-color":"#F7F7F7","height":"auto","padding-top":"125px","padding-bottom":"125px","color":"#000","text-align":"center"});
	                        jQuery("#gadash-locations").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[0]+")");
	                    }

                    }else{
             			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
             			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
             			console.log("\n********************* GADWP Log ********************* \n\n"+response);
                    }
					NProgress.done();
                });

            	function ga_dash_drawmaplocations(gadash_locations) {

            		var data = google.visualization.arrayToDataTable(gadash_locations);

            		var options = {
            			chartArea: {width: '99%',height: '90%'},
            			colors: ['<?php echo $light_color; ?>', '<?php echo $dark_color; ?>'],
            			<?php

							$country_codes = GADWP_Tools::get_countrycodes();
							if ( $this->gadwp->config->options['ga_target_geomap'] && isset( $country_codes[$this->gadwp->config->options['ga_target_geomap']] ) ) {
								?>
        				region : '<?php echo esc_html($this->gadwp->config->options ['ga_target_geomap']); ?>',
        				displayMode : 'markers',
        				datalessRegionColor : 'EFEFEF'
            			<?php } ?>
            			}
            		var chart = new google.visualization.GeoChart(document.getElementById('gadash-map'));
            		chart.draw(data, options);
            	}

            	function ga_dash_drawlocations(gadash_locations) {
                	var data = google.visualization.arrayToDataTable(gadash_locations);
                	var options = {
                		page: 'enable',
                		pageSize: 10,
                		width: '100%'
                	};

                	var chart = new google.visualization.Table(document.getElementById('gadash-locations'));
                	chart.draw(data, options);
            	};
            </script>
<?php } else {?>
<div id="gadash-mainchart"></div>
<div id="gadash-bottomstats" class="gadash-wrapper">
    <div class="inside">
        <div class="small-box">
            <h3><?php _e( "Sessions", 'google-analytics-dashboard-for-wp' );?></h3>
            <p id="gdsessions">&nbsp;</p>
        </div>
        <div class="small-box">
            <h3><?php _e( "Users", 'google-analytics-dashboard-for-wp' );?></h3>
            <p id="gdusers">&nbsp;</p>
        </div>
        <div class="small-box">
            <h3><?php _e( "Page Views", 'google-analytics-dashboard-for-wp' );?></h3>
            <p id="gdpageviews">&nbsp;</p>
        </div>
        <div class="small-box">
            <h3><?php _e( "Bounce Rate", 'google-analytics-dashboard-for-wp' );?></h3>
            <p id="gdbouncerate">&nbsp;</p>
        </div>
        <div class="small-box">
            <h3><?php _e( "Organic Search", 'google-analytics-dashboard-for-wp' );?></h3>
            <p id="gdorganicsearch">&nbsp;</p>
        </div>
        <div class="small-box">
            <h3><?php _e( "Pages/Session", 'google-analytics-dashboard-for-wp' );?></h3>
            <p id="gdpagespervisit">&nbsp;</p>
        </div>
    </div>
</div>
<script type="text/javascript">

    google.load("visualization", "1", {packages:["corechart"], 'language': '<?php echo get_bloginfo( 'language' ); ?>'});

	try {
    	NProgress.configure({ parent: "#gadash-progressbar" });
        NProgress.configure({ showSpinner: false });
        NProgress.start();
	} catch(e) {
		jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
		jQuery("#gadash-progressbar").html("<?php _e("A JavaScript Error is blocking plugin resources!", 'google-analytics-dashboard-for-wp'); ?>");
	}

    jQuery.post(ajaxurl, {action: "gadash_get_widgetreports",projectId: "<?php echo $projectId; ?>",from: "<?php echo $from; ?>",to: "<?php echo $to; ?>",query: "<?php echo $query . ',bottomstats'; ?>",gadash_security_widget_reports: "<?php echo wp_create_nonce('gadash_get_widgetreports'); ?>"}, function(response){
    	if ( jQuery.isArray( response ) ) {

	        if (!jQuery.isNumeric(response[0])){
	            if (jQuery.isArray(response[0])){
	            	gadash_mainchart=response[0];
	       		    google.setOnLoadCallback(ga_dash_drawmainchart(gadash_mainchart));
	             } else {
	     			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	     			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	     			console.log("\n********************* GADWP Log ********************* \n\n"+response[0]);
	     		}
	    	}else{
	            jQuery("#gadash-mainchart").css({"background-color":"#F7F7F7","height":"auto","padding-top":"125px","padding-bottom":"125px","color":"#000","text-align":"center"});
	            jQuery("#gadash-mainchart").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[0]+")");
	        }

	        if (!jQuery.isNumeric(response[1])){
	        	if (jQuery.isArray(response[1])){
	            	gadash_bottomstats=response[1];
	       		    ga_dash_drawbottomstats(gadash_bottomstats);
	             } else {
	     			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
	     			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
	     			console.log("\n********************* GADWP Log ********************* \n\n"+response[1]);
	     		}
	    	}else{
	            jQuery("#gadash-bottomstats").css({"background-color":"#F7F7F7","height":"auto","padding-top":"40px","padding-bottom":"40px","color":"#000","text-align":"center","width": "98%"});
	            jQuery("#gadash-bottomstats").html("<?php _e("This report is unavailable", 'google-analytics-dashboard-for-wp'); ?> ("+response[1]+")");
	        }

        }else{
 			jQuery("#gadash-progressbar").css({"margin-top":"3px","padding-left":"5px","height":"auto","color":"#000","border-left":"5px solid red"});
 			jQuery("#gadash-progressbar").html("<?php _e("Invalid response, more details in JavaScript Console (F12).", 'google-analytics-dashboard-for-wp'); ?>");
 			console.log("\n********************* GADWP Log ********************* \n\n"+response);
        }
		NProgress.done();
    });

	function ga_dash_drawbottomstats(gadash_bottomstats) {
        jQuery("#gadash-bottomstats #gdsessions").text(gadash_bottomstats[0]);
        jQuery("#gadash-bottomstats #gdusers").text(gadash_bottomstats[1]);
        jQuery("#gadash-bottomstats #gdpageviews").text(gadash_bottomstats[2]);
        jQuery("#gadash-bottomstats #gdbouncerate").text(gadash_bottomstats[3] + "%");
        jQuery("#gadash-bottomstats #gdorganicsearch").text(gadash_bottomstats[4]);
        jQuery("#gadash-bottomstats #gdpagespervisit").text(gadash_bottomstats[5]);
	}

	function ga_dash_drawmainchart(gadash_mainchart) {

    var data = google.visualization.arrayToDataTable(gadash_mainchart);

    var options = {
	  legend: {position: 'none'},
	  pointSize: 3,<?php echo $css;?>
	  chartArea: {width: '99%',height: '90%'},
	  vAxis: { textPosition: "in", minValue: 0},
	  hAxis: { textPosition: 'none' }
	};
	<?php echo $formater?>
    var chart = new google.visualization.AreaChart(document.getElementById('gadash-mainchart'));
	chart.draw(data, options);
	};
</script>
<?php
						}
		}
	}
}
