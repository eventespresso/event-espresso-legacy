<?php

function espresso_transactions_worldpay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['MC_id'])) {
		$attendee_id = $_REQUEST['MC_id'];
	}
	if (isset($_REQUEST['MC_registration_id'])) {
		$_REQUEST['registration_id'] = $_REQUEST['MC_registration_id'];
		$_GET['registration_id'] = $_REQUEST['MC_registration_id'];
	}
	return $attendee_id;
}

function espresso_process_worldpay($payment_data) {
	global $wpdb;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	echo "<WPDISPLAY ITEM=banner>";
	$payment_data['txn_type'] = 'WorldPay';
	$payment_data['payment_status'] = "Incomplete";
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($_REQUEST);
	//removed sidney's fix, as hopefully we'll have a more general fix that will work for all gateways
//	session_id($_REQUEST['MC_session_id']);
//	session_start();
//	session_destroy();
	if ($_REQUEST['transStatus'] == 'Y') {
		$attendee_id = $payment_data['attendee_id'];
		$payment_data['payment_status'] = 'Completed';
		$payment_data['txn_id'] = $_REQUEST['transId'];
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
