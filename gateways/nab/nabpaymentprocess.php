<?php

function espresso_transactions_nab_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_nab ($payment_data) {
	global $wpdb;
	$nab_settings = get_option('event_espresso_nab_settings');
	$payment_data['txn_type'] = 'NAB';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);

	if ($_REQUEST['rescode'] == '00' || $_REQUEST['rescode'] == '08') {
		$payment_data['payment_status'] = 'Completed';
		$payment_data['txn_id'] = $_REQUEST['txnid'];

		//Debugging option
		if ($nab_settings['use_sandbox']) {
			var_dump($response);
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification was successfully recieved\n";
			$body .= "from " . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			$body .= $response;
			wp_mail($payment_data['contact'], $subject, $body);
		}
	} else {
		echo "Looks like there was a problem with your payment details. Please try again.";
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		$body .= $response;
		wp_mail($payment_data['contact'], $subject, $body);
		event_espresso_pay();
	}
	return $payment_data;
}
