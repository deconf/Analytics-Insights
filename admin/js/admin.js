jQuery(document).ready(function(){
 
	jQuery(".log_data").hide();
	jQuery(".show_hide").show();
 
	jQuery('.show_hide').click(function(){
		jQuery(".log_data").slideToggle();
		jQuery(this).text(jQuery(this).text() == 'Show Log' ? 'Hide Log' : 'Show Log');
    });
 
});
