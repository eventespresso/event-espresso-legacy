<div id="espresso_confirmation_display" class="<?php echo apply_filters('espresso_filter_hook_css_event_display_boxes','event-display-boxes ui-widget'); ?>">	
<?php //Pending Approval Page Template ?>
	<h3 class="<?php echo apply_filters('espresso_filter_hook_css_section_heading','section-heading ui-widget-header ui-corner-top'); ?> "><?php echo $fname ?>,</h3>
		<p class="<?php echo apply_filters('espresso_filter_hook_css_instruct','instruct'); ?>"><?php _e('Your registration is not complete until admin approves.', 'event_espresso'); ?></p>
		<p>
			<span class="<?php echo apply_filters('espresso_filter_hook_css_section_title','event_espresso_name section-title'); ?>"><?php _e('Amount due: ', 'event_espresso'); ?></span> 
			<span class="<?php echo apply_filters('espresso_filter_hook_css_value','event_espresso_value'); ?>"><?php echo $org_options['currency_symbol']?><?php echo $event_cost; ?></span>
		</p>
		<p><span class="<?php echo apply_filters('espresso_filter_hook_css_section_title','section-title'); ?>"><?php _e('Your Registration ID:', 'event_espresso'); ?> </span><?php echo $registration_id ?></p>
</div>