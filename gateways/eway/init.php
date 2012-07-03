<?php
// This is for gateway display
add_action('action_hook_espresso_display_offsite_payment_header','espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer','espresso_display_offsite_payment_footer');
require_once($path . "/eway_vars.php");

// This is for return from eway
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'eway') {
	require_once($path . "/ewaypaymentprocess.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_eway_get_attendee_id');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_eway');
}