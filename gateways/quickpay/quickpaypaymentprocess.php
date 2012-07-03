<?php

function espresso_transactions_quickpay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_quickpay($payment_data) {
	global $wpdb;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = trim(stripslashes($_GET['transaction_id']));
	$payment_data['txn_type'] = 'Quickpay';
	$payment_data['payment_status'] = 'Incomplete';
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	$sql = "SELECT txn_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $payment_data['attendee_id'] . "' ";
	$txn_id = $wpdb->get_var($sql);

	if ($_GET['chronopay_callback'] == 'true') {
		$sessionid = trim(stripslashes($_GET['sessionid']));
		if (md5($payment_data['txn_id'] . $quickpay_settings['quickpay_md5secret']) == $txn_id) {
			$payment_data['payment_status'] = 'Completed';
		}
	}
	add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
