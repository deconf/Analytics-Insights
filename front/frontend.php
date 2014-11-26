<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists ( 'GADASH_Frontend' )) {
	class GADASH_Frontend {
		function __construct() {
			add_filter ( 'the_content', array (
					$this,
					'ga_dash_front_content'
			) );
		}
		function ga_dash_front_content($content) {
			global $post;
			global $GADASH_Config;

			/*
			 * Include Tools
			 */
			include_once ($GADASH_Config->plugin_path . '/tools/tools.php');
			$tools = new GADASH_Tools ();

			if (! $tools->check_roles ( $GADASH_Config->options ['ga_dash_access_front'] ) or ! ($GADASH_Config->options ['ga_dash_frontend_stats'] or $GADASH_Config->options ['ga_dash_frontend_keywords'])) {
				return $content;
			}

			if ((is_page () || is_single ()) && ! is_preview ()) {

				wp_enqueue_script ( 'gadash-general-settings', plugins_url ( 'admin/js/admin.js', dirname ( __FILE__ ) ), array (
						'jquery'
				) );

				/*
				 * Include GAPI
				 */
				if (function_exists ( 'curl_version' ) and $GADASH_Config->options ['ga_dash_tableid_jail'] and $GADASH_Config->options ['ga_dash_token']) {
					include_once ($GADASH_Config->plugin_path . '/tools/gapi.php');
					global $GADASH_GAPI;
				} else {
					return $content;
				}

				if (! $GADASH_GAPI->client->getAccessToken ()) {
					return $content;
				}

				if (isset ( $GADASH_Config->options ['ga_dash_tableid_jail'] )) {
					$projectId = $GADASH_Config->options ['ga_dash_tableid_jail'];
					$profile_info = $tools->get_selected_profile ( $GADASH_Config->options ['ga_dash_profile_list'], $projectId );
					if (isset ( $profile_info [4] )) {
						$GADASH_GAPI->timeshift = $profile_info [4];
					} else {
						$GADASH_GAPI->timeshift = ( int ) current_time ( 'timestamp' ) - time ();
					}
				} else {
					return $content;
				}

				$page_url = $_SERVER ["REQUEST_URI"]; // str_replace(site_url(), "", get_permalink());

				$post_id = $post->ID;

				if ($GADASH_Config->options ['ga_dash_frontend_stats']) {
					$data_visits = $GADASH_GAPI->frontend_afterpost_visits ( $projectId, $page_url, $post_id );
				}

				if ($GADASH_Config->options ['ga_dash_frontend_keywords']) {
					$data_keywords = $GADASH_GAPI->frontend_afterpost_searches ( $projectId, $page_url, $post_id );
				}

				if ($data_visits or $data_keywords) {
					$content .= '<style>
				#ga_dash_sdata td{
					line-height:1.5em;
					padding:2px;
					font-size:1em;
				}
				#ga_dash_sdata{
					line-height:10px;
				}
				</style>';

					$content .= '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
				<script type="text/javascript">
				  google.setOnLoadCallback(ga_dash_callback);

				  function ga_dash_callback(){

						if(typeof ga_dash_drawstats == "function"){
							ga_dash_drawstats();
						}
						if(typeof ga_dash_drawsd == "function"){
							ga_dash_drawsd();
						}
				}';

					if ($GADASH_Config->options ['ga_dash_frontend_stats']) {
						$content .= $data_visits;
					}

					if ($GADASH_Config->options ['ga_dash_frontend_keywords']) {
						$content .= $data_keywords;
					}

					$content .= "</script>";
					$content .= '<p><div id="ga_dash_statsdata"></div><div id="ga_dash_sdata" ></div></p>';
				}
			}

			return $content;
		}
	}
}
if (! is_admin ()) {
	$GLOBALS ['GADASH_Frontend'] = new GADASH_Frontend ();
}
