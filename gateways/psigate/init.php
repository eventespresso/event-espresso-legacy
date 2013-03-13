<?php
// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("psigate/psigate_vars.php");


// This is for the transaction processing
event_espresso_require_gateway("psigate/psigate_ipn.php");
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'psigate') {
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_psigate_get_attendee_id');
	//add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_psigate');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_psigate');
}

function espresso_psigate_formal_name( $gateway_formal_names ) {
	$gateway_formal_names['psigate'] = 'PSiGate';
}
add_filter( 'action_hook_espresso_gateway_formal_name', 'espresso_psigate_formal_name', 10, 1 );

function espresso_psigate_payment_type( $gateway_payment_types ) {
	$gateway_payment_types['psigate'] = 'PSiGate / Credit Card';
}
add_filter( 'action_hook_espresso_gateway_payment_type', 'espresso_psigate_payment_type', 10, 1 );
