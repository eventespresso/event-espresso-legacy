<?php

add_action('action_hook_espresso_display_offsite_payment_header', 'espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer', 'espresso_display_offsite_payment_footer');
require_once($path . "/worldpay_vars.php");
if (!empty($_REQUEST['MC_type']) && $_REQUEST['MC_type'] == 'worldpay') {
	require_once($path . "/worldpay_ipn.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_worldpay_get_attendee_id');
	add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_worldpay');
}