<?php
// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("google_checkout/google_checkout_vars.php");

// This is for the transaction processing

if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'google_checkout') {
	event_espresso_require_gateway("google_checkout/google_checkout_ipn.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_google_checkout_get_attendee_id');
	add_filter('filter_hook_espresso_prepare_payment_data_for_gateways','espresso_transactions_google_checkout_prepare_payment_data');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_google_checkout_done_payment');
	
	add_action('action_hook_espresso_before_payment_overview','espresso_process_google_checkout_done_payment');
	add_action('init','espresso_google_run_transaction_code_before_shortcode');
}

function espresso_google_checkout_formal_name( $gateway_formal_names ) {
	$gateway_formal_names['google_checkout'] = 'Google Checkout';
}
add_filter( 'action_hook_espresso_gateway_formal_name', 'espresso_google_checkout_formal_name', 10, 1 );

function espresso_google_checkout_payment_type( $gateway_payment_types ) {
	$gateway_payment_types['google_checkout'] = 'Google Checkout / Credit Card';
}
add_filter( 'action_hook_espresso_gateway_payment_type', 'espresso_google_checkout_payment_type', 10, 1 );



