<?php
/**
 * Author: Alin Marcu
 * Copyright 2018 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
?>
<script>
var gadwpDnt = false;
var gadwpProperty = '<?php echo $data['uaid']?>';
var gadwpDntFollow =  '<?php echo $data['gaDntOptout']?>';
var gadwpOptout =  '<?php echo $data['gaOptout']?>';
var disableStr = 'ga-disable-' + gaProperty;
if(gadwpDntFollow && (window.doNotTrack === "1" || navigator.doNotTrack === "1" || navigator.doNotTrack === "yes" || navigator.msDoNotTrack === "1")) {
	gadwpDnt = true;
}
if (gadwpDnt || (document.cookie.indexOf(disableStr + '=true') > -1 && gadwpOptout)) {
	window[disableStr] = true;
}
function gaOptout() {
	document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
	window[disableStr] = true;
}
</script>
