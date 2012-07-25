<?php
// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("paypal/paypal_vars.php");


// This is for the transaction processing
event_espresso_require_gateway("paypal/paypal_ipn.php");
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'paypal') {
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_paypal_get_attendee_id');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_paypal');
}