<?php

function espresso_transactions_nab_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_nab_get_attendee_id');

function espresso_process_nab ($payment_data) {
	global $wpdb;
	$eway_settings = get_option('event_espresso_eway_settings');
	$payment_data['txn_type'] = 'NAB';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);

	if ($_REQUEST['rescode'] == '00' || $_REQUEST['rescode'] == '08') {
		$payment_data['payment_status'] = 'Completed';
		$payment_data['txn_id'] = $_REQUEST['txnid'];

		//Debugging option
		if ($eway_settings['use_sandbox']) {
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
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);
	do_action('action_hook_espresso_email_after_payment', $payment_data);
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_nab');