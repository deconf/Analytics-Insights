(function($) {
	$(window)
			.load(
					function() {
						
						var expresion = new RegExp('.*\\.(' + gadash_eventsdata.extensions + ')(\\?.*)?$');
						
						$('a')
								.filter(
										function() {
											return this.href.match(expresion);
										})
								.click(
										function(e) {
											ga('send', 'event', 'download', 'click', this.href, $.parseJSON(gadash_eventsdata.bouncerate));
											console.log('download');
										});
						$('a[href^="mailto"]')
								.click(
										function(e) {
											ga('send',	'event', 'email', 'send', this.href, $.parseJSON(gadash_eventsdata.bouncerate));
											console.log('email');
										});
						var loc = location.host.split('.');
						while (loc.length > 2) {
							loc.shift();
						}
						loc = loc.join('.');
						var localURLs = [ loc, gadash_eventsdata.siteurl ];
						$('a[href^="http"]').filter(function() {
							if (!this.href.match(expresion)){
								for ( var i = 0; i < localURLs.length; i++) {
									if (this.href.indexOf(localURLs[i]) == -1)
										return this.href;
								}
							}
						}).click(
							function(e) { 
								ga('send', 'event', 'outbound', 'click', this.href, {'nonInteraction' : 1});
								console.log('outbound');
						});
					});
})(jQuery);
