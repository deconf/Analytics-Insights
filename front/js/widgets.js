/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery( window ).on("resize",  function () {
	if ( typeof aiwp_drawFrontWidgetChart == "function" && typeof aiwpFrontWidgetData !== 'undefined' && !(!isNaN(parseFloat(aiwpFrontWidgetData)) && isFinite(aiwpFrontWidgetData)) ) {
		aiwp_drawFrontWidgetChart( aiwpFrontWidgetData );
	}
} );
