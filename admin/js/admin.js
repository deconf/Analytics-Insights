jQuery(document).ready(function(){
 
	jQuery(".log_data").hide();
	jQuery(".show_hide").show();
 
	jQuery('.show_hide').click(function(){
		jQuery(".log_data").slideToggle();
		jQuery(this).text(jQuery(this).text() == 'Show Log' ? 'Hide Log' : 'Show Log');
    });
 
});


jQuery(window).resize(function(){
	if(typeof ga_dash_drawstats == "function"){
		ga_dash_drawstats();
	}
	if(typeof ga_dash_drawmap == "function"){
		ga_dash_drawmap();
	}
	if(typeof ga_dash_drawpgd == "function"){
		ga_dash_drawpgd();
	}
	if(typeof ga_dash_drawrd == "function"){
		ga_dash_drawrd();
	}
	if(typeof ga_dash_drawsd == "function"){
		ga_dash_drawsd();
	}
	if(typeof ga_dash_drawtraffic == "function"){
		ga_dash_drawtraffic();
	}
});
