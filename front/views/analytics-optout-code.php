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
var dnt = false;
var gaProperty = '<?php echo $data['uaid']?>';
var gaDntOptout =  '<?php echo $data['gaDntOptout']?>';
var gaOptout =  '<?php echo $data['gaOptout']?>';
var disableStr = 'ga-disable-' + gaProperty;
if(gaDntOptout && (window.doNotTrack === "1" || navigator.doNotTrack === "1" || navigator.doNotTrack === "yes" || navigator.msDoNotTrack === "1")) {
	dnt = true;
}
if (dnt || (document.cookie.indexOf(disableStr + '=true') > -1 && gaOptout)) {
	window[disableStr] = true;
}
function gaOptout() {
	document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
	window[disableStr] = true;
}
</script>