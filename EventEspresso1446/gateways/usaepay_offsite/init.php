<?php
// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
event_espresso_require_gateway("usaepay_offsite/payment.php");

// This is for the return from 2Checkout's servers
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'usaepay_offsite') {
	event_espresso_require_gateway("usaepay_offsite/return.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_usaepay_offsite_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_usaepay_offsite');
}