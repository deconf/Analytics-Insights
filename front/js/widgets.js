/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

tools = {
	isNumeric : function (string) {
		return !isNaN(parseFloat(string)) && isFinite(string);
	}	
}

jQuery( window ).on("resize",  function () {
	if ( typeof aiwp_drawFrontWidgetChart == "function" && typeof aiwpFrontWidgetData !== 'undefined' && !tools.isNumeric( aiwpFrontWidgetData ) ) {
		aiwp_drawFrontWidgetChart( aiwpFrontWidgetData );
	}
} );