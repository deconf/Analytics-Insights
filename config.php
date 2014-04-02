<?php
/**
 * Author: Alin Marcu
 * Author URI: http://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (! class_exists ( 'GADASH_Config' )) {
	class GADASH_Config {
		public $options;
		public $country_codes;
		public $plugin_path, $plugin_url;
		
		public function __construct() {
			$this->getPluginPath ();
			
			// get plugin options
			$this->get_plugin_options ();
		}

		public function set_plugin_options() {
			if (current_user_can ( 'manage_options' )){
				update_option ( 'gadash_options', json_encode ( $this->options ) );
			}	
		}
		
		public function getPluginPath() {
			/*
			 * Set Plugin Path
			 */
			$this->plugin_path = dirname ( __FILE__ );
			$this->plugin_url = plugins_url ( "", __FILE__ );
		}

		private function get_plugin_options() {
			/*
			 * Get plugin options
			 */
			if (!get_option ( 'gadash_options' )){
				GADASH_Install::install();
			}
			$this->options = ( array ) json_decode ( get_option ( 'gadash_options' ) );
			
			//Maintain Compatibility
			$this->maintain_compatibility();

		}
		
		private function maintain_compatibility(){
			
			if (!isset($this->options['ga_enhanced_links'])){
				$this->options['ga_enhanced_links'] = 0;
			}

			if (!is_array($this->options['ga_dash_access_front']) OR empty($this->options['ga_dash_access_front'])){
				$this->options['ga_dash_access_front'] = array();
				$this->options['ga_dash_access_front'][] = 'administrator';
			}

			if (!is_array($this->options['ga_dash_access_back']) OR empty($this->options['ga_dash_access_back'])){
				$this->options['ga_dash_access_back'] = array();
				$this->options['ga_dash_access_back'][] = 'administrator';
			}
			
			if (!is_array($this->options['ga_track_exclude'])){
				$this->options['ga_track_exclude'] = array();
				$this->options['ga_track_exclude'][] = 'administrator';
			}			
			
		}
		
	}
}

if (!isset($GLOBALS ['GADASH_Config'])){
	$GLOBALS ['GADASH_Config'] = new GADASH_Config ();
}

	
