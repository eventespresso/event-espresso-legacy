<?php
// This is for gateway display
add_action('action_hook_espresso_display_offsite_payment_header','espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer','espresso_display_offsite_payment_footer');
event_espresso_require_gateway("eway/eway_vars.php");

// This is for return from eWay
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'eway') {
	event_espresso_require_gateway("eway/ewaypaymentprocess.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_eway_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_eway');
}