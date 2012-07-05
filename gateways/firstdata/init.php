<?php
add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
event_espresso_require_gateway("firstdata/firstdata_vars.php");
if (!empty($_REQUEST['firstdata'])) {
	event_espresso_require_gateway("firstdata/Firstdata.php");
	add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_firstdata_get_attendee_id');
	add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_firstdata');
}