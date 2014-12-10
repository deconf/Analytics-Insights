jQuery(document).ready(function(){
 
	jQuery('#gadwp-content').hide();
 
	jQuery('#gadwp-title').click(function(){
		jQuery('#gadwp-content').slideToggle();
		jQuery('#gadwp-title #gadwp-arrow').html( jQuery('#gadwp-title #gadwp-arrow').html() == '▲' ? '▼' : '▲');
    });
	
	jQuery("#gadwp-title a").click(function(event) {
		 event.preventDefault(); 
	});
});