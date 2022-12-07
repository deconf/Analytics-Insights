<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

final class AIWP_Frontend_Widget extends WP_Widget {

	private $aiwp;

	public function __construct() {
		$this->aiwp = AIWP();
		parent::__construct( 'aiwp-frontwidget-report', __( 'Analytics Insights', 'analytics-insights' ), array( 'description' => __( "Will display your google analytics stats in a widget", 'analytics-insights' ) ) );
		// Frontend Styles
		if ( is_active_widget( false, false, $this->id_base, true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
		}
	}

	public function load_styles_scripts() {
		if ( AIWP_Tools::is_amp() ){
			return;
		}
		$lang = get_bloginfo( 'language' );
		$lang = explode( '-', $lang );
		$lang = $lang[0];
		wp_enqueue_style( 'aiwp-front-widget', AIWP_URL . 'front/css/widgets' . AIWP_Tools::script_debug_suffix() . '.css', null, AIWP_CURRENT_VERSION );
		wp_enqueue_script( 'aiwp-front-widget', AIWP_URL . 'front/js/widgets' . AIWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), AIWP_CURRENT_VERSION );
		wp_enqueue_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), null );
	}

	public function widget( $args, $instance ) {
		if ( AIWP_Tools::is_amp() ){
			return;
		}
		$widget_title = apply_filters( 'widget_title', $instance['title'] );
		$title = __( "Sessions", 'analytics-insights' );
		echo "\n<!-- BEGIN AIWP v" . AIWP_CURRENT_VERSION . " Widget - https://deconf.com/analytics-insights-for-wordpress/ -->\n";
		echo $args['before_widget'];
		if ( ! empty( $widget_title ) ) {
			echo $args['before_title'] . esc_attr( $widget_title ) . $args['after_title'];
		}
		if ( isset( $this->aiwp->config->options['theme_color'] ) ) {
			$css = "colors:['" . esc_attr( $this->aiwp->config->options['theme_color'] ) . "','" . esc_attr( AIWP_Tools::colourVariator( $this->aiwp->config->options['theme_color'], - 20 ) ) . "'],\n";
			// $color = $this->aiwp->config->options['theme_color'];
		} else {
			$css = "";
			// $color = "#3366CC";
		}
		ob_start();
		if ( $instance['anonim'] ) {
			$formater = "var formatter = new google.visualization.NumberFormat({
					  suffix: '%',
					  fractionDigits: 2
					});

					formatter.format(data, 1);";
		} else {
			$formater = '';
		}
		$periodtext = "";
		switch ( $instance['period'] ) {
			case '7daysAgo' :
				$periodtext = sprintf( __( 'Last %d Days', 'analytics-insights' ), 7 );
				break;
			case '14daysAgo' :
				$periodtext = sprintf( __( 'Last %d Days', 'analytics-insights' ), 14 );
				break;
			case '30daysAgo' :
				$periodtext = sprintf( __( 'Last %d Days', 'analytics-insights' ), 30 );
				break;
			default :
				$periodtext = "";
				break;
		}
		switch ( $instance['display'] ) {
			case '1' :
				echo '<div id="aiwp-widget"><div id="aiwp-widgetchart"></div><div id="aiwp-widgettotals"></div></div>';
				break;
			case '2' :
				echo '<div id="aiwp-widget"><div id="aiwp-widgetchart"></div></div>';
				break;
			case '3' :
				echo '<div id="aiwp-widget"><div id="aiwp-widgettotals"></div></div>';
				break;
		}
		?>
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback( AIWPWidgetLoad );
	function AIWPWidgetLoad (){
		jQuery.post("<?php echo admin_url( 'admin-ajax.php' ); ?>", {action: "ajax_frontwidget_report", aiwp_number: "<?php echo esc_js( $this->number ); ?>", aiwp_optionname: "<?php  echo esc_js( $this->option_name ); ?>" }, function(response){
			if (!jQuery.isNumeric(response) && jQuery.isArray(response)){
				if (jQuery("#aiwp-widgetchart")[0]){
					aiwpFrontWidgetData = response[0];
					aiwp_drawFrontWidgetChart(aiwpFrontWidgetData);
				}
				if (jQuery("#aiwp-widgettotals")[0]){
					aiwp_drawFrontWidgetTotals(response[1]);
				}
			}else{
				jQuery("#aiwp-widgetchart").css({"background-color":"#F7F7F7","height":"auto","padding-top":"50px","padding-bottom":"50px","color":"#000","text-align":"center"});
				jQuery("#aiwp-widgetchart").html("<?php __( "This report is unavailable", 'analytics-insights' ); ?> ("+response+")");
			}
		});
	}
	function aiwp_drawFrontWidgetChart(response) {
		var data = google.visualization.arrayToDataTable(response);
		var options = {
			legend: { position: "none" },
			pointSize: "3",
			<?php echo wp_kses( $css, array() ); ?>
			title: "<?php echo esc_js( $title ); ?>",
			titlePosition: "in",
			chartArea: { width: "95%", height: "75%" },
			hAxis: { textPosition: "none"},
			vAxis: { textPosition: "none", minValue: 0, gridlines: { color: "transparent" }, baselineColor: "transparent"}
		}
		var chart = new google.visualization.AreaChart(document.getElementById("aiwp-widgetchart"));
		<?php echo wp_kses( $formater, array() ); ?>
		chart.draw(data, options);
	}
	function aiwp_drawFrontWidgetTotals(response) {
		if ( null == response ){
			response = 0;
		}
		jQuery("#aiwp-widgettotals").html('<div class="aiwp-left"><?php _e( "Period:", 'analytics-insights' ); ?></div> <div class="aiwp-right"><?php echo esc_html( $periodtext ); ?> </div><div class="aiwp-left"><?php _e( "Sessions:", 'analytics-insights' ); ?></div> <div class="aiwp-right">'+response+'</div>');
	}
</script>
<?php
		if ( 1 == $instance['give_credits'] ) :
			?>
<div style="text-align: right; width: 100%; font-size: 0.8em; clear: both; margin-right: 5px;"><?php _e( 'generated by', 'analytics-insights' ); ?> <a href="https://deconf.com/analytics-insights-for-wordpress/?utm_source=aiwp_report&utm_medium=link&utm_content=front_widget&utm_campaign=aiwp" rel="nofollow" style="text-decoration: none; font-size: 1em;">AIWP</a>&nbsp;
</div>
<?php
		endif;

		$widget_content = ob_get_contents();
		if ( ob_get_length() ) {
			ob_end_clean();
		}
		echo $widget_content;
		echo $args['after_widget'];
		echo "\n<!-- END AIWP Widget -->\n";
	}

	public function form( $instance ) {
		$widget_title = ( isset( $instance['title'] ) ? $instance['title'] : __( "Google Analytics Stats", 'analytics-insights' ) );
		$period = ( isset( $instance['period'] ) ? $instance['period'] : '7daysAgo' );
		$display = ( isset( $instance['display'] ) ? $instance['display'] : 1 );
		$give_credits = ( isset( $instance['give_credits'] ) ? $instance['give_credits'] : 1 );
		$anonim = ( isset( $instance['anonim'] ) ? $instance['anonim'] : 0 );
		/* @formatter:off */
?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( "Title:",'analytics-insights' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>">
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>"><?php _e( "Display:",'analytics-insights' ); ?></label>
	<select id="<?php echo esc_attr( $this->get_field_id('display') ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>">
		<option value="1" <?php selected( $display, 1 ); ?>><?php _e('Chart & Totals', 'analytics-insights');?></option>
		<option value="2" <?php selected( $display, 2 ); ?>><?php _e('Chart', 'analytics-insights');?></option>
		<option value="3" <?php selected( $display, 3 ); ?>><?php _e('Totals', 'analytics-insights');?></option>
	</select>
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'anonim' ) ); ?>"><?php _e( "Anonymize stats:",'analytics-insights' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'anonim' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'anonim' ) ); ?>" type="checkbox" <?php checked( $anonim, 1 ); ?> value="1">
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'period' ) ); ?>"><?php _e( "Stats for:",'analytics-insights' ); ?></label>
	<select id="<?php echo esc_attr( $this->get_field_id('period') ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'period' ) ); ?>">
		<option value="7daysAgo" <?php selected( $period, '7daysAgo' ); ?>><?php printf( __('Last %d Days', 'analytics-insights'), 7 );?></option>
		<option value="14daysAgo" <?php selected( $period, '14daysAgo' ); ?>><?php printf( __('Last %d Days', 'analytics-insights'), 14 );?></option>
		<option value="30daysAgo" <?php selected( $period, '30daysAgo' ); ?>><?php printf( __('Last %d Days', 'analytics-insights'), 30 );?></option>
	</select>
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'give_credits' ) ); ?>"><?php _e( "Give credits:",'analytics-insights' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'give_credits' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'give_credits' ) ); ?>" type="checkbox" <?php checked( $give_credits, 1 ); ?> value="1">
</p>
<?php
		/* @formatter:on */
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'Analytics Stats';
		$instance['period'] = ( ! empty( $new_instance['period'] ) ) ? strip_tags( $new_instance['period'] ) : '7daysAgo';
		$instance['display'] = ( ! empty( $new_instance['display'] ) ) ? strip_tags( $new_instance['display'] ) : 1;
		$instance['give_credits'] = ( ! empty( $new_instance['give_credits'] ) ) ? strip_tags( $new_instance['give_credits'] ) : 0;
		$instance['anonim'] = ( ! empty( $new_instance['anonim'] ) ) ? strip_tags( $new_instance['anonim'] ) : 0;
		return $instance;
	}
}
