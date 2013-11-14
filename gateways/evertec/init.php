<?php
event_espresso_require_gateway("evertec/evertec_vars.php");


// This is for the transaction processing
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'evertec') {
	event_espresso_require_gateway("evertec/DoDirectPayment.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_evertec_get_attendee_id');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_evertec');
}

function espresso_evertec_formal_name( $gateway_formal_names ) {
	$gateway_formal_names['evertec'] = 'EverTec';
}
add_filter( 'action_hook_espresso_gateway_formal_name', 'espresso_evertec_formal_name', 10, 1 );

function espresso_evertec_payment_type( $gateway_payment_types ) {
	$gateway_payment_types['evertec'] = 'EverTec';
}
add_filter( 'action_hook_espresso_gateway_payment_type', 'espresso_evertec_payment_type', 10, 1 );