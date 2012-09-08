<?php
// This is for the payment display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("quickpay/quickpay_vars.php");

// This is for the return from QuickPay's servers
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'quickpay') {
	event_espresso_require_gateway("quickpay/quickpaypaymentprocess.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_quickpay_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_quickpay');
}