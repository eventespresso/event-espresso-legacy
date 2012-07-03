<?php
function espresso_transactions_ideal_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_ideal_get_attendee_id');

add_action('action_hook_espresso_display_onsite_payment_header','espresso_display_onsite_payment_header');
add_action('action_hook_espresso_display_onsite_payment_footer','espresso_display_onsite_payment_footer');
if (!isset($_GET['transaction_id'])) {
	require_once($path . "/ideal_vars.php");
} else {
	require_once($path . "/report.php");
}