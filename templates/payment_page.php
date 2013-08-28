<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	
//Confirmation Page Template
?>

<div class="<?php echo apply_filters('espresso_filter_hook_css_payment_overview','espresso_payment_overview event-display-boxes ui-widget'); ?>" >
  <h3 class="<?php echo apply_filters('espresso_filter_hook_css_section_heading','section-heading ui-widget-header ui-corner-top'); ?> ">
		<?php _e('Payment Overview', 'event_espresso'); ?>
  </h3>
	<div class="<?php echo apply_filters('espresso_filter_hook_css_event_data_display','event-data-display ui-widget-content ui-corner-bottom'); ?>" >
	<?php do_action('action_hook_espresso_payment_page_top', $event_id, $event_meta, $all_meta);?>

<?php
	if ( $total_cost == 0 ) {
		unset($_SESSION['espresso_session']['id']);
?>
		<h2><?php echo $fname ?> <?php echo $lname ?>,</h2>
		
		<div class="<?php echo apply_filters('espresso_filter_hook_css_event_messages','event-messages ui-state-highlight'); ?> ">
			<span class="<?php echo apply_filters('espresso_filter_hook_css_icon_alert','ui-icon ui-icon-alert'); ?>"></span>	  
			<p class="<?php echo apply_filters('espresso_filter_hook_css_instruct','instruct'); ?>">
				<?php _e('Thank you! Your registration is confirmed for', 'event_espresso'); ?>
	    			<b><?php echo stripslashes_deep($event_name) ?></b>
			</p>
		</div>
	  	<p>
			<span class="<?php echo apply_filters('espresso_filter_hook_css_section_title','section-title'); ?>"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span> <?php echo $registration_id ?>
	 	</p>
	  	<p class="<?php echo apply_filters('espresso_filter_hook_css_instruct','instruct'); ?>">
			<?php _e('A confirmation email has been sent with additional details of your registration.', 'event_espresso'); ?>
	  	</p>

<?php }else{ ?>

		<h2><?php echo $fname ?> <?php echo $lname ?>,</h2>
	  
		<div class="<?php echo apply_filters('espresso_filter_hook_css_event_messages','event-messages ui-state-highlight'); ?>">
			<span class="<?php echo apply_filters('espresso_filter_hook_css_icon_alert','ui-icon ui-icon-alert'); ?>"></span>
			<p class="<?php echo apply_filters('espresso_filter_hook_css_instruct','instruct'); ?>">
				<?php _e('Your registration is not complete until payment is received.', 'event_espresso'); ?>
			</p>
		</div>
		
	  	<p>
			<span class="<?php echo apply_filters('espresso_filter_hook_css_section_title','event_espresso_name section-title'); ?>"><?php _e('Amount due: ', 'event_espresso'); ?></span> 
			<span class="<?php echo apply_filters('espresso_filter_hook_css_value','event_espresso_value'); ?>"><?php echo isset($org_options['currency_symbol']) ? $org_options['currency_symbol'] : ''; ?><?php echo number_format($total_cost,2); ?></span>
		</p>
	  	
		<p>
			<span class="<?php echo apply_filters('espresso_filter_hook_css_section_title','section-title'); ?>"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span><?php echo $registration_id ?>
		</p>
		
	  	<p>
			<?php echo $org_options['email_before_payment'] == 'Y' ? __('A confirmation email has been sent with additional details of your registration.', 'event_espresso') : ''; ?>
		</p>

<?php
}
?>
<?php do_action('action_hook_espresso_payment_page_bottom', $event_id, $event_meta, $all_meta);?>
	</div><!-- / .event-data-display -->
</div><!-- / .event-display-boxes -->
