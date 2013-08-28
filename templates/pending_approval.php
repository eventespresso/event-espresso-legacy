<div id="espresso_confirmation_display" class="event-display-boxes <?php echo apply_filters('espresso_filter_hook_registration_css_event-display-boxes','ui-widget'); ?>">	
<?php //Pending Approval Page Template ?>
	<h3 class="section-heading <?php echo apply_filters('espresso_filter_hook_registration_css_section-heading','ui-widget-header ui-corner-top'); ?> "><?php echo $fname ?>,</h3>
		<p class="instruct <?php echo apply_filters('espresso_filter_hook_registration_css_instruct',''); ?>"><?php _e('Your registration is not complete until admin approves.', 'event_espresso'); ?></p>
		<p>
			<span class="event_espresso_name section-title <?php echo apply_filters('espresso_filter_hook_registration_css_section-title',''); ?>"><?php _e('Amount due: ', 'event_espresso'); ?></span> 
			<span class="event_espresso_value <?php echo apply_filters('espresso_filter_hook_registration_css_event_espresso_value',''); ?>"><?php echo $org_options['currency_symbol']?><?php echo $event_cost; ?></span>
		</p>
		<p><span class="section-title <?php echo apply_filters('espresso_filter_hook_registration_css_section-title',''); ?>"><?php _e('Your Registration ID:', 'event_espresso'); ?> </span><?php echo $registration_id ?></p>
</div>