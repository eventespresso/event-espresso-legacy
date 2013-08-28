<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	
//Confirmation Page Template
?>

<div class="espresso_payment_overview event-display-boxes <?php echo apply_filters('espresso_filter_hook_registration_css_espresso_payment_overview','ui-widget'); ?>" >
  <h3 class="section-heading <?php echo apply_filters('espresso_filter_hook_registration_css_section-heading','ui-widget-header ui-corner-top'); ?> ">
		<?php _e('Payment Overview', 'event_espresso'); ?>
  </h3>
	<div class="event-data-display <?php echo apply_filters('espresso_filter_hook_registration_css_event-data-display','ui-widget-content ui-corner-bottom'); ?>" >
	<?php do_action('action_hook_espresso_payment_page_top', $event_id, $event_meta, $all_meta);?>

<?php
	if ( $total_cost == 0 ) {
		unset($_SESSION['espresso_session']['id']);
?>
		<h2><?php echo $fname ?> <?php echo $lname ?>,</h2>
		
		<div class="event-messages <?php echo apply_filters('espresso_filter_hook_registration_css_event-data-display','ui-state-highlight'); ?> ">
			<span class="<?php echo apply_filters('espresso_filter_hook_registration_css_icon-alert','ui-icon ui-icon-alert'); ?>"></span>	  
			<p class="instruct <?php echo apply_filters('espresso_filter_hook_registration_css_instruct',''); ?>">
				<?php _e('Thank you! Your registration is confirmed for', 'event_espresso'); ?>
	    			<b><?php echo stripslashes_deep($event_name) ?></b>
			</p>
		</div>
	  	<p>
			<span class="section-title <?php echo apply_filters('espresso_filter_hook_registration_css_section-title',''); ?>"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span> <?php echo $registration_id ?>
	 	</p>
	  	<p class="instruct <?php echo apply_filters('espresso_filter_hook_registration_css_instruct',''); ?>">
			<?php _e('A confirmation email has been sent with additional details of your registration.', 'event_espresso'); ?>
	  	</p>

<?php }else{ ?>

		<h2><?php echo $fname ?> <?php echo $lname ?>,</h2>
	  
		<div class="event-messages <?php echo apply_filters('espresso_filter_hook_registration_css_event-messages','ui-state-highlight'); ?>">
			<span class="<?php echo apply_filters('espresso_filter_hook_registration_css_icon-alert','ui-icon ui-icon-alert'); ?>"></span>
			<p class="instruct <?php echo apply_filters('espresso_filter_hook_registration_css_instruct',''); ?>">
				<?php _e('Your registration is not complete until payment is received.', 'event_espresso'); ?>
			</p>
		</div>
		
	  	<p>
			<span class="event_espresso_name section-title <?php echo apply_filters('espresso_filter_hook_registration_css_section-title',''); ?>"><?php _e('Amount due: ', 'event_espresso'); ?></span> 
			<span class="event_espresso_value <?php echo apply_filters('espresso_filter_hook_registration_css_value',''); ?>"><?php echo isset($org_options['currency_symbol']) ? $org_options['currency_symbol'] : ''; ?><?php echo number_format($total_cost,2); ?></span>
		</p>
	  	
		<p>
			<span class="section-title <?php echo apply_filters('espresso_filter_hook_registration_css_section-title',''); ?>"><?php _e('Your Registration ID: ', 'event_espresso'); ?></span><?php echo $registration_id ?>
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
