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

function espresso_paypal_formal_name( $gateway_formal_names ) {
	$gateway_formal_names['paypal'] = 'PayPal';
}
add_filter( 'action_hook_espresso_gateway_formal_name', 'espresso_paypal_formal_name', 10, 1 );

function espresso_paypal_payment_type( $gateway_payment_types ) {
	$gateway_payment_types['paypal'] = 'PayPal / Credit Card';
}
add_filter( 'action_hook_espresso_gateway_payment_type', 'espresso_paypal_payment_type', 10, 1 );
