<?php
// This is for gateway display
add_action('action_hook_espresso_display_offline_payment_header', 'espresso_display_offline_payment_header');
add_action('action_hook_espresso_display_offline_payment_footer', 'espresso_display_offline_payment_footer');
event_espresso_require_gateway("purchase_order/po_payment_vars.php");

// This is for the thank you page
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'purchase_order') {
	event_espresso_require_gateway("purchase_order/po_ipn.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_purchase_order_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_purchase_order');
}
