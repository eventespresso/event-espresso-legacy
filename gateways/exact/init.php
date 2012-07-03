<?php

// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
require_once($path . "/exact_vars.php");

// This is for the return from exact's server
if (!empty($_REQUEST['x_reference_3'])) {
	$temp_array = explode('|', $_REQUEST['x_reference_3']);
	$_GET['registration_id'] = $temp_array[0];
	$_REQUEST['type'] = $temp_array[1];
}
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'exact') {
	require_once($path . "/exact_ipn.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_exact_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_exact');
}
