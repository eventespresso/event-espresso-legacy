<?php
// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
require_once($path . "/realauth_vars.php");

// And this is for the return from realauth's server
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'realauth') {
	require_once($path . "/realauthprocesspayment.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_realauth_get_attendee_id');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_realauth');
}