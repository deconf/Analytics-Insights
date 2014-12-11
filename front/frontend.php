<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists('GADASH_Frontend')) {

    class GADASH_Frontend
    {

        function __construct()
        {
            add_filter('the_content', array(
                $this,
                'ga_dash_front_content'
            ));
            // Admin Styles
            add_action('wp_enqueue_scripts', array(
                $this,
                'ga_dash_front_enqueue_styles'
            ));
        }

        function ga_dash_front_enqueue_styles()
        {
            global $GADASH_Config;
            
            if ((! is_page() and ! is_single()) or is_preview() or ! is_user_logged_in()) {
                return;
            }
            
            wp_register_style('ga_dash-front', $GADASH_Config->plugin_url . '/front/css/content_stats.css');
            wp_register_style('ga_dash-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.css');
            wp_enqueue_style('ga_dash-front');
            wp_enqueue_style('ga_dash-nprogress');
            wp_enqueue_script('ga_dash-front', $GADASH_Config->plugin_url . '/front/js/content_stats.js', array(
                'jquery'
            ));
            wp_enqueue_script('ga_dash-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.js', array(
                'jquery'
            ));
            if (! wp_script_is('googlejsapi')) {
                wp_register_script('googlejsapi', 'https://www.google.com/jsapi');
                wp_enqueue_script('googlejsapi');
            }
        }

        function ga_dash_front_content($content)
        {
            global $post;
            global $GADASH_Config;
            
            /*
             * Include Tools
             */
            include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
            $tools = new GADASH_Tools();
            
            if (! $tools->check_roles($GADASH_Config->options['ga_dash_access_front']) or ! ($GADASH_Config->options['ga_dash_frontend_stats'] or $GADASH_Config->options['ga_dash_frontend_keywords'])) {
                return $content;
            }
            
            if ((is_page() || is_single()) && ! is_preview()) {
                
                wp_enqueue_script('gadash-general-settings', plugins_url('admin/js/admin.js', dirname(__FILE__)), array(
                    'jquery'
                ));
                
                $page_url = $_SERVER ["REQUEST_URI"]; // str_replace(site_url(), "", get_permalink());
                
                $post_id = $post->ID;
                
                $content .= '<script type="text/javascript">
					var firstclick = true;
                    function gadwp_chart_drawn(){
                        NProgress.done();
                    }
					jQuery(document).ready(function(){
					 	jQuery("#gadwp-title").click(function(){
							  function ga_dash_callback(){
									if(typeof ga_dash_drawstats == "function"){
										jQuery.post("' . admin_url('admin-ajax.php') . '", {action: "gadash_get_frontendvisits_data",gadash_pageurl: "' . $page_url . '",gadash_postid: "' . $post_id . '",gadash_security_aaf: "' . wp_create_nonce('gadash_get_frontendvisits_data') . '"}, function(response){
											if (response != 0 && response != 403){
												ga_dash_drawstats(JSON.parse(response));
											}else{
										        jQuery("#gadwp-visits").css({"background-color":"#F7F7F7","height":"auto","padding-top":"30px","padding-bottom":"30px"});  
										        jQuery("#gadwp-visits").html("'.__("This report is unavailable",'ga-dash').' ("+response+")");
										        gadwp_chart_drawn();      
                                            }	
										});
									}
									if(typeof ga_dash_drawsd == "function"){
										jQuery.post("' . admin_url('admin-ajax.php') . '", {action: "gadash_get_frontendsearches_data",gadash_pageurl: "' . $page_url . '",gadash_postid: "' . $post_id . '",gadash_security_aas: "' . wp_create_nonce('gadash_get_frontendsearches_data') . '"}, function(response){
											if (response != 0 && response != 403){
												ga_dash_drawsd(JSON.parse(response));
											}else{
										        jQuery("#gadwp-searches").css({"background-color":"#F7F7F7","height":"auto","padding-top":"30px","padding-bottom":"30px"});
										        jQuery("#gadwp-searches").html("'.__("This report unavailable",'ga-dash').' ("+response+")");
										        gadwp_chart_drawn();
                                            }	
										});
									}
							};
							if (firstclick){
            					NProgress.configure({ parent: "#gadwp-content" });
            					NProgress.configure({ showSpinner: false });
            					NProgress.start();							    
							    ga_dash_callback();
								firstclick = false;
							}
						});
					});';
                
                if ($GADASH_Config->options['ga_dash_frontend_stats']) {
                    
                    $title = __("Views vs UniqueViews", 'ga-dash');
                    
                    if (isset($GADASH_Config->options['ga_dash_style'])) {
                        $css = "colors:['" . $GADASH_Config->options['ga_dash_style'] . "','" . $tools->colourVariator($GADASH_Config->options['ga_dash_style'], - 20) . "'],";
                        $color = $GADASH_Config->options['ga_dash_style'];
                    } else {
                        $css = "";
                        $color = "#3366CC";
                    }
                    
                    $content .= '
			google.load("visualization", "1", {packages:["corechart"]});
			function ga_dash_drawstats(response) {
			
			var data = google.visualization.arrayToDataTable(response);

			var options = {
			  legend: {position: "none"},
			  pointSize: 3,' . $css . '
			  title: "' . $title . '",
	  		  vAxis: {minValue: 0},
			  chartArea: {width: "100%", height: "80%"},
			  hAxis: { textPosition: "none"}
			};

			var chart = new google.visualization.AreaChart(document.getElementById("gadwp-visits"));
			chart.draw(data, options);
            gadwp_chart_drawn();      
			}';
                }
                
                if ($GADASH_Config->options['ga_dash_frontend_keywords']) {
                    
                    $content .= '
				google.load("visualization", "1", {packages:["table"]})
				function ga_dash_drawsd(response) {

				var datas = google.visualization.arrayToDataTable(response);

				var options = {
					page: "enable",
					pageSize: 6,
					width: "100%",
					allowHtml:true
				};

				var chart = new google.visualization.Table(document.getElementById("gadwp-searches"));
				chart.draw(datas, options);
				gadwp_chart_drawn();
			  }';
                }
                
                $content .= "</script>";
                $content .= '<p>
								<div id="gadwp">
									<div id="gadwp-title">
									<a href="#gadwp">' . __('Google Analytics Reports', "ga-dash") . ' <span id="gadwp-arrow">&#x25BC;</span></a>
									</div>
									<div id="gadwp-content">
										' . ($GADASH_Config->options['ga_dash_frontend_stats'] ? '<div id="gadwp-visits" class="gadwp-spinner"></div>' : '') . ($GADASH_Config->options['ga_dash_frontend_keywords'] ? '<div id="gadwp-searches" class="gadwp-spinner"></div>' : '') . '
									</div>
								</div>
							</p>';
            }
            return $content;
        }
    }
}
if (! is_admin()) {
    $GADASH_Frontend = new GADASH_Frontend();
}
