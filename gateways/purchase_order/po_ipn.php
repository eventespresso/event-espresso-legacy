<?php

function espresso_transactions_purchase_order_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_purchase_order($payment_data) {
	$payment_data['payment_status'] = 'Pending';
	$payment_data['txn_type'] = 'P.O. - '.$_POST['po_number'];
	$payment_data['txn_id'] = $payment_data['attendee_session'];
	$payment_data['txn_details'] = "P.O.";
	return $payment_data;
}
