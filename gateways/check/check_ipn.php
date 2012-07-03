<?php

function espresso_transactions_check_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_check($payment_data) {
	$payment_data['payment_status'] = 'Pending';
	$payment_data['txn_type'] = 'Check';
	$payment_data['txn_id'] = $payment_data['attendee_session'];
	$payment_data['txn_details'] = "paying by check";
	return $payment_data;
}
