<?php

// This is for the gateway display
add_action('action_hook_espresso_display_offsite_payment_header','espresso_display_offsite_payment_header');
add_action('action_hook_espresso_display_offsite_payment_footer','espresso_display_offsite_payment_footer');
event_espresso_require_gateway("mwarrior/mwarrior_vars.php");

// This is for the return from mwarrior
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'mwarrior') {
	event_espresso_require_gateway("mwarrior/mwarrior_ipn.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_mwarrior_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_mwarrior');
}