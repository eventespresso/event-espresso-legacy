<?php

// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("firstdata_e4/e4_vars.php");

// This is for the return from firstdata_e4's server
if (!empty($_REQUEST['x_reference_3'])) {
	$temp_array = explode(' ', $_REQUEST['x_reference_3']);
	$_REQUEST['registration_id'] = $temp_array[0];
	$_REQUEST['type'] = $temp_array[1];
}
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'FDe4') {
	event_espresso_require_gateway("firstdata_e4/e4_ipn.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_firstdata_e4_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_firstdata_e4');
}
