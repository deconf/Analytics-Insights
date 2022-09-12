<?php
/**
 * Author: Alin Marcu
 * Copyright 2018 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
?>
<amp-analytics config="https://www.googletagmanager.com/amp.json?id=<?php echo esc_js( $data['containerid'] ); ?>&gtm.url=SOURCE_URL" data-credentials="include"> <script type="application/json">
<?php echo wp_kses( $data['json'], array() ); ?>
	</script> </amp-analytics>