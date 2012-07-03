<?php

// This is for the gateway display
add_action('action_hook_espresso_display_onsite_payment_header', 'espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer', 'espresso_display_onsite_payment_footer');
require_once($path . "/nab_vars.php");

// This is for the transaction processing
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'nab') {
	require_once($path . "/nabpaymentprocess.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_nab_get_attendee_id');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_nab');
}