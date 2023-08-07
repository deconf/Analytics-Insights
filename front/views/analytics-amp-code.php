<?php
/**
 * Author: Alin Marcu
 * Copyright 2017 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
?>
<amp-analytics type="googleanalytics" config="https://amp.analytics-debugger.com/ga4.json" data-credentials="include"> <script type="application/json">
<?php echo wp_kses( $data['json'], array() ); ?>
</script> </amp-analytics>
