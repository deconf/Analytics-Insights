<?php
/**
 * Author: Alin Marcu
 * Copyright 2017 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
?>

<!-- BEGIN AIWP v<?php echo AIWP_CURRENT_VERSION; ?> Google Analytics 4 - https://deconf.com/analytics-insights-for-wordpress/ -->
<script async src="<?php echo esc_url( $data['tracking_script_path'] )?>?id=<?php echo esc_js( $data['gaid'] )?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
<?php
do_action('aiwp_gtag_output_before');
echo wp_kses( $data['trackingcode'], array() );
do_action('aiwp_gtag_output_after');
?>
  if (window.performance) {
    var timeSincePageLoad = Math.round(performance.now());
    gtag('event', 'timing_complete', {
      'name': 'load',
      'value': timeSincePageLoad,
      'event_category': 'JS Dependencies'
    });
  }
</script>
<!-- END AIWP Google Analytics 4 -->
