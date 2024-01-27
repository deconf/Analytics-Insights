<?php
/**
 * Author: Alin Marcu
 * Copyright 2017 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
?>

<!-- BEGIN Analytics Insights v<?php echo AIWP_CURRENT_VERSION; ?> - https://deconf.com/analytics-insights-google-analytics-dashboard-wordpress/ -->
<script>
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push(<?php echo wp_kses( $data['vars'], array() ); ?>);
</script>
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo esc_js( $data['containerid'] ); ?>');
</script>
<!-- END Analytics Insights -->
