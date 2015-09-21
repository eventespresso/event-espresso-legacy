<?php
// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("2checkout/2checkout_vars.php");

// This is for the return from 2Checkout's servers
if (!empty($_REQUEST['payment_method']) && $_REQUEST['payment_method'] == '2checkout') {
	event_espresso_require_gateway("2checkout/2checkoutpaymentprocess.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_2checkout_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_2checkout');
}