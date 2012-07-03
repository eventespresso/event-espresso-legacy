<?php

// This is for the display gateways
add_action('action_hook_espresso_display_onsite_payment_header', 'espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer', 'espresso_display_onsite_payment_footer');
add_action('action_hook_espresso_display_onsite_payment_gateway', 'espresso_process_ideal');

require_once($path . "/ideal_vars.php");
add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_ideal_get_attendee_id');

if (!empty($_GET['transaction_id']) && !empty($_REQUEST['type']) && $_REQUEST['type'] == 'ideal') {
	require_once($path . "/report.php");
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_ideal_report');
}

function espresso_transactions_ideal_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}