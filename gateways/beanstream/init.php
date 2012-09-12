<?php

// This is for the gateway display
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
event_espresso_require_gateway("beanstream/payment.php");

// And this is for the thank you page to process the transaction
if (!empty($_REQUEST['beanstream'])) {
	event_espresso_require_gateway("beanstream/return.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_beanstream_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_beanstream');

}