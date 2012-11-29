<?php

// This is for the display gateways
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("wepay/wepay_vars.php");


// This is for returns / callbacks from wepay's server
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'wepay') {
	event_espresso_require_gateway("wepay/wepay_ipn.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_wepay_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_wepay');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_wepay_callback');
}