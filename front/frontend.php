<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists('GADASH_Frontend')) {

    final class GADASH_Frontend
    {

        function __construct()
        {
            add_filter('the_content', array(
                $this,
                'ga_dash_front_content'
            ));
            // Frontend Styles
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
            
            wp_enqueue_style('ga_dash-front', $GADASH_Config->plugin_url . '/front/css/content_stats.css');
            wp_enqueue_style('ga_dash-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.css');
            wp_enqueue_script('ga_dash-front', $GADASH_Config->plugin_url . '/front/js/content_stats.js', array(
                'jquery'
            ));
            wp_enqueue_script('ga_dash-nprogress', $GADASH_Config->plugin_url . '/tools/nprogress/nprogress.js', array(
                'jquery'
            ));
            wp_enqueue_script('googlejsapi', 'https://www.google.com/jsapi');
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
                
                $page_url = $_SERVER["REQUEST_URI"]; // str_replace(site_url(), "", get_permalink());
                
                $post_id = $post->ID;
                
                $content .= '<script type="text/javascript">
					gadash_firstclick = true;

					jQuery(document).ready(function(){
					 	jQuery("#gadwp-title").click(function(){
							  	if (gadash_firstclick){
            					   NProgress.configure({ parent: "#gadwp-content" });
            					   NProgress.configure({ showSpinner: false });
            					   NProgress.start();
									if(typeof ga_dash_drawpagesessions == "function"){
										jQuery.post("' . admin_url('admin-ajax.php') . '", {action: "gadash_get_frontendsessions_data",gadash_pageurl: "' . $page_url . '",gadash_postid: "' . $post_id . '",gadash_security_aaf: "' . wp_create_nonce('gadash_get_frontendsessions_data') . '"}, function(response){
										    gadash_pagesessions = jQuery.parseJSON(response);
										    if (!jQuery.isNumeric(gadash_pagesessions)){
		          							    google.setOnLoadCallback(ga_dash_drawpagesessions(gadash_pagesessions));
											}else{
										        jQuery("#gadwp-sessions").css({"background-color":"#F7F7F7","height":"auto","padding-top":"30px","padding-bottom":"30px","color":"#000","text-align":"center"});  
										        jQuery("#gadwp-sessions").html("' . __("This report is unavailable", 'ga-dash') . ' ("+response+")");
										        NProgress.done();    
                                            }	
										});
									}
									if(typeof ga_dash_drawpagesearches == "function"){
										jQuery.post("' . admin_url('admin-ajax.php') . '", {action: "gadash_get_frontendsearches_data",gadash_pageurl: "' . $page_url . '",gadash_postid: "' . $post_id . '",gadash_security_aas: "' . wp_create_nonce('gadash_get_frontendsearches_data') . '"}, function(response){
                                            gadash_pagesearches = jQuery.parseJSON(response); 
										    if (!jQuery.isNumeric(gadash_pagesearches)){
												google.setOnLoadCallback(ga_dash_drawpagesearches(gadash_pagesearches));
											}else{
										        jQuery("#gadwp-searches").css({"background-color":"#F7F7F7","height":"auto","padding-top":"30px","padding-bottom":"30px","color":"#000","text-align":"center"});
										        jQuery("#gadwp-searches").html("' . __("This report is unavailable", 'ga-dash') . ' ("+response+")");
										        NProgress.done();
                                            }	
										});
									}
    							gadash_firstclick = false;
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
			function ga_dash_drawpagesessions(gadash_pagesessions) {
	
			var data = google.visualization.arrayToDataTable(gadash_pagesessions);

			var options = {
			  legend: {position: "none"},
			  pointSize: 3,' . $css . '
			  title: "' . $title . '",
	  		  vAxis: { textPosition: "none", minValue: 0, gridlines: {color: "transparent"}, baselineColor: "transparent"},
			  chartArea: {width: "100%", height: "80%"},
			  hAxis: { textPosition: "none"}
			};

			var chart = new google.visualization.AreaChart(document.getElementById("gadwp-sessions"));
			chart.draw(data, options);
            NProgress.done();      
			}';
                }
                
                if ($GADASH_Config->options['ga_dash_frontend_keywords']) {
                    
                    $content .= '
				google.load("visualization", "1", {packages:["table"]})
				function ga_dash_drawpagesearches(gadash_pagesearches) {

				var datas = google.visualization.arrayToDataTable(gadash_pagesearches);

				var options = {
					page: "enable",
					pageSize: 6,
					width: "100%",
					allowHtml:true
				};

				var chart = new google.visualization.Table(document.getElementById("gadwp-searches"));
				chart.draw(datas, options);
				NProgress.done();
			  }';
                }
                
                $content .= "</script>";
                $content .= '<p>
								<div id="gadwp">
									<div id="gadwp-title">
									<a href="#gadwp">' . __('Google Analytics Reports', "ga-dash") . ' <span id="gadwp-arrow">&#x25BC;</span></a>
									</div>
									<div id="gadwp-content">
										' . ($GADASH_Config->options['ga_dash_frontend_stats'] ? '<div id="gadwp-sessions" class="gadwp-spinner"></div>' : '') . ($GADASH_Config->options['ga_dash_frontend_keywords'] ? '<div id="gadwp-searches" class="gadwp-spinner"></div>' : '') . '
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
