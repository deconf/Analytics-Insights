<script type="text/javascript">
(function($){
    $(window).load(function() {
            $('a').filter(function() {
                return this.href.match(/.*\.(<?php echo esc_js($GADASH_Config->options['ga_event_downloads']);?>)(\?.*)?$/);
            }).click(function(e) {
                ga('send','event', 'download', 'click', this.href<?php if(isset($GADASH_Config->options['ga_event_bouncerate']) && $GADASH_Config->options['ga_event_bouncerate']){echo ", {'nonInteraction': 1}";}?>);
            });
            $('a[href^="mailto"]').click(function(e) {
                ga('send','event', 'email', 'send', this.href<?php if(isset($GADASH_Config->options['ga_event_bouncerate']) && $GADASH_Config->options['ga_event_bouncerate']){echo ", {'nonInteraction': 1}";}?>);
             });
            var loc = location.host.split('.');
            while (loc.length > 2) { loc.shift(); }
            loc = loc.join('.');
            var localURLs = [
                              loc,
                              '<?php echo esc_html(get_option('siteurl'));?>'
                            ];
            $('a[href^="http"]').filter(function() {
				if (!this.href.match(/.*\.(<?php echo esc_js($GADASH_Config->options['ga_event_downloads']);?>)(\?.*)?$/)){
	                for (var i = 0; i < localURLs.length; i++) {
	                    if (this.href.indexOf(localURLs[i]) == -1) return this.href;
	                }
				}    
            }).click(function(e) {
                ga('send','event', 'outbound', 'click', this.href, {'nonInteraction': 1});
            });
    });
})(jQuery);
</script>
