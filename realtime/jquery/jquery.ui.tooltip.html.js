jQuery(function () {
      jQuery('#gadash-widget *').tooltip({
		  items: "[data-tooltip]",
          content: function () {
              return jQuery(this).attr("data-tooltip");
          }
      });
  });
