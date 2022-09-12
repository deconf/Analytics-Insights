/*-
 * Author: Alin Marcu Author 
 * URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery(function () {
      jQuery('#aiwp-widget *').tooltip({
		  items: "[data-aiwp]",
          content: function () {
              return jQuery(this).attr("data-aiwp");
          }
      });
  });
