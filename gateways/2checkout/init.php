<?php
// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
require_once($path . "/2checkout_vars.php");

// This is for the return from 2checkout's servers
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == '2co') {
	require_once($path . "/2checkoutpaymentprocess.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_2checkout_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_2checkout');
}