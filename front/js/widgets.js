jQuery(window).resize(function(){
	if(typeof ga_dash_drawfwidgetsessions == "function" && typeof gadash_widgetsessions!=='undefined' && !jQuery.isNumeric(gadash_widgetsessions)){
		ga_dash_drawfwidgetsessions(gadash_widgetsessions);
	}
});