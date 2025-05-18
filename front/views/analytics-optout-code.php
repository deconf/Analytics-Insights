<?php
/**
 * Author: Alin Marcu
 * Copyright 2018 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
?>
<script>
var aiwpProperty = '<?php echo esc_js ( $data['gaid'] )?>';
var aiwpOptout = <?php echo $data['gaOptout'] ? 'true' : 'false'?>;
var disableStr = 'ga-disable-' + aiwpProperty;
if (document.cookie.indexOf(disableStr + '=true') > -1 && aiwpOptout) {
	window[disableStr] = true;
}
function gaOptout() {
	var expDate = new Date;
	expDate.setFullYear(expDate.getFullYear( ) + 10);
	document.cookie = disableStr + '=true; expires=' + expDate.toGMTString( ) + '; path=/';
	window[disableStr] = true;
}
</script>
