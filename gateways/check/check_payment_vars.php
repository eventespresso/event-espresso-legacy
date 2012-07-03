<?php

function espresso_display_check($payment_data) {
	global $org_options;
	extract($payment_data);
// Setup class
	$default_gateway_version = empty($default_gateway_version) ? '' : $default_gateway_version;
	echo '<!-- Event Espresso Default Gateway Version ' . $default_gateway_version . '-->';

	$check_payment_settings = get_option('event_espresso_check_payment_settings');
	?>
	<div class="event_espresso_attention event-messages ui-state-highlight">
		<span class="ui-icon ui-icon-alert"></span>
		<p><strong><?php _e('Attention!', 'event_espresso'); ?></strong><br />
	<?php _e('Since you are using the check payment option, please make note of the information below, then', 'event_espresso'); ?>
			<a id="finalize_check" href="<?php echo home_url() . '/?page_id=' . $org_options['return_url']; ?>&amp;payment_type=cash_check&amp;id=<?php echo $attendee_id . '&registration_id=' . $registration_id ?>&type=check" class="inline-link" title="<?php _e('Finalize your registration', 'event_espresso'); ?>"><?php _e('click here to finalize your registration', 'event_espresso'); ?></a>
		</p>
	</div>
	<div class="event-display-boxes">
		<h4 id="check_title" class="payment_type_title section-heading"><?php echo stripslashes_deep(empty($check_payment_settings['check_title']) ? '' : $check_payment_settings['check_title']) ?></h4>
		<p class="instruct"><?php echo stripslashes_deep(empty($check_payment_settings['check_instructions']) ? '' : $check_payment_settings['check_instructions'] ); ?></p>
		<p>
			<span class="section-title"><?php _e('Payable to:', 'event_espresso'); ?></span>
			<span class="highlight"><?php echo stripslashes_deep(empty($check_payment_settings['payable_to']) ? '' : $check_payment_settings['payable_to']); ?></span>
		</p>
		<p class="section-title"><?php _e('Payment Address: ', 'event_espresso'); ?></p>
		<div class="address-block">
	<?php echo wpautop(stripslashes_deep(empty($check_payment_settings['payment_address']) ? '' : $check_payment_settings['payment_address'])); ?>
		</div>
	</div>
	<?php
}

add_action('action_hook_espresso_display_offline_payment_gateway_2', 'espresso_display_check');
