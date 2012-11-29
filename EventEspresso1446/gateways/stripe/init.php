<?php
// This is for the display gateways
add_action('action_hook_espresso_display_onsite_payment_header', 'espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer', 'espresso_display_onsite_payment_footer');
event_espresso_require_gateway("stripe/stripe_vars.php");

// And this is for the thank you page to process the transaction
if (!empty($_REQUEST['stripe'])) {
	event_espresso_require_gateway("stripe/do_transaction.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_stripe_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_stripe');
}