<?php

function espresso_transactions_usaepay_offsite_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['UMinvoice']))
		$attendee_id = $_REQUEST['UMinvoice'];
	return $attendee_id;
}

function espresso_process_usaepay_offsite($payment_data) {
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_type'] = 'USAePay';
	$payment_data['txn_id'] = $_REQUEST['UMrefNum'];
	if ($_REQUEST['UMstatus'] == 'Approved') {
		$payment_data['payment_status'] = 'Completed';
	}
	return $payment_data;
}