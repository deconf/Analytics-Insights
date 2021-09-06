<?php
/**
 * Author: Alin Marcu
 * Copyright 2017 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
?>
<amp-analytics type="googleanalytics" id="aiwp-googleanalytics">
	<script type="application/json">
<?php echo wp_kses( $data['json'], array() ); ?>
	</script>
</amp-analytics>
