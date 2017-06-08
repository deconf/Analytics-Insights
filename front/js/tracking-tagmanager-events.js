( function ( $ ) {
	$( window ).on('load', function () {
		// Track Page Scroll Depth
		$.scrollDepth( {
			percentage : true,
			userTiming : false,
			pixelDepth : false,
			gtmOverride : false,
		} );
	} );
} )( jQuery );