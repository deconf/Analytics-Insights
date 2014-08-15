<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
$profile = $tools->get_selected_profile ( $GADASH_Config->options ['ga_dash_profile_list'], $GADASH_Config->options ['ga_dash_tableid_jail'] );
$rootdomain = $tools->get_root_domain ( $profile [3] );
?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  
  ga('create', '<?php echo esc_html($profile[2]); ?>', 'auto'<?php	if ($GADASH_Config->options ['ga_speed_samplerate']<>1) {?>, {'siteSpeedSampleRate': <?php echo (int)$GADASH_Config->options ['ga_speed_samplerate']; ?>} <?php }?>);
<?php	if ($GADASH_Config->options ['ga_dash_remarketing']) {?>
  ga('require', 'displayfeatures');
<?php }?>
<?php	if ($GADASH_Config->options ['ga_enhanced_links']) {?>
  ga('require', 'linkid', 'linkid.js');
<?php }?>
<?php if ($GADASH_Config->options ['ga_dash_anonim']) {?>  ga('send', 'pageview', {'anonymizeIp': true});<?php } else {?>  ga('send', 'pageview');
<?php }?>

<?php if ($GADASH_Config->options ['ga_dash_adsense']) {?>
  window.google_analytics_uacct = "<?php echo esc_html($profile[2]); ?>";
<?php }?>  
</script>