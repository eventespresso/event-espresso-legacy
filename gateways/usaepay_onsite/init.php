<?php

// This is for the gateway display
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
event_espresso_require_gateway("usaepay_onsite/usaepay_onsite_vars.php");

// And this is for the thank you page to process the transaction
if (!empty($_REQUEST['usaepay_onsite'])) {
	event_espresso_require_gateway("usaepay_onsite/DoDirectPayment.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_usaepay_onsite_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_usaepay_onsite');

}