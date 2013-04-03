<?php

function espresso_transactions_realauth_get_attendee_id($attendee_id) {
	if ($_REQUEST['ORDER_ID']) {
		$attendee_id = $_REQUEST['ORDER_ID'];
		$_REQUEST['registration_id'] = $_REQUEST['REG_ID'];
	}
	return $attendee_id;
}

function espresso_process_realauth($payment_data) {
	global $wpdb;
	$payment_data['txn_type'] = 'RealAuth';
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_id'] = $_REQUEST['PASREF'];
	$payment_data['txn_details'] = serialize($_REQUEST);
	if ($_REQUEST['RESULT'] == '00') {
		$payment_data['payment_status'] = 'Completed';
		$realauth_settings = get_option('event_espresso_realauth_settings');


		//Debugging option
		if ($realauth_settings['use_sandbox']) {
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification was successfully recieved\n";
			$body .= "from " . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			wp_mail($payment_data['contact'], $subject, $body);
		}
	} else {
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		//var_dump($body);
		//var_dump($_REQUEST);
		//wp_mail($payment_data['contact'], $subject, $body);
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
    return $payment_data;
}
