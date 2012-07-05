<?php

// This is for the gateway display
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
event_espresso_require_gateway("paypal_pro/paypal_pro_vars.php");

// And this is for the thank you page to process the transaction
if (!empty($_REQUEST['paypal_pro'])) {
	event_espresso_require_gateway("paypal_pro/DoDirectPayment.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_paypal_pro_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_paypal_pro');

}