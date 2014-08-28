<?php

function espresso_transactions_firstdata_e4_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['x_cust_id'])) {
		$attendee_id = $_REQUEST['x_cust_id'];
	}
	if (!empty($_REQUEST['x_reference_3'])) {
		$temp_array = explode(' ', $_REQUEST['x_reference_3']);
		$_REQUEST['registration_id'] = $temp_array[0];
		$_REQUEST['type'] = $temp_array[1];
	}
	return $attendee_id;
}

function espresso_process_firstdata_e4($payment_data) {
	include_once ('FirstDataE4.php');

	$myE4 = new Espresso_E4();

	echo '<!--Event Espresso FirstData E4 Gateway Version ' . $myE4->gateway_version . '-->';
// Log the IPN results
	$myE4->ipnLog = TRUE;

	$firstdata_e4_settings = get_option('event_espresso_firstdata_e4_settings');
	$firstdata_e4_login_id = $firstdata_e4_settings['firstdata_e4_login_id'];
	$firstdata_e4_transaction_key = $firstdata_e4_settings['firstdata_e4_transaction_key'];

// Enable test mode if needed
//4007000000027  <-- test successful visa
//4222222222222  <-- test failure card number
	if ($firstdata_e4_settings['use_sandbox']) {
		$myE4->enableTestMode();
		$email_transaction_dump = true;
	}

	$payment_data['txn_type'] = 'FirstData E4';
	$payment_data['payment_status'] = "Incomplete";
	if (!empty($_REQUEST['x_trans_id'])) {
		$payment_data['txn_id'] = $_REQUEST['x_trans_id'];
	} else {
		$payment_data['txn_id'] = 0;
	}
//	$payment_data['txn_details'] = serialize($_REQUEST);
//	$curl_session_id = uniqid('', true);
//	global $wpdb;
//	$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET attendee_session = '" . $curl_session_id . "' WHERE attendee_session ='" . $payment_data['attendee_session'] . "' ";
//	$wpdb->query($sql);
//	$payment_data['attendee_session'] = $curl_session_id;

// Specify your authorize login and secret
	$myE4->setUserInfo($firstdata_e4_login_id, $firstdata_e4_transaction_key);

// Check validity and write down it
	if ($myE4->validateIpn()) {

		$payment_data['txn_id'] = $myE4->ipnData['x_trans_id'];
		$payment_data['txn_details'] = serialize($myE4->ipnData);

//file_put_contents('authorize.txt', 'SUCCESS' . date("m-d-Y")); //Used for debugging purposes
//Be sure to echo something to the screen so authent knows that the ipn works
//store the results in reusable variables
		if ($myE4->ipnData['x_response_code'] == 1) {
			?>
			
			<p><?php _e('Your transaction has been processed.', 'event_espresso'); ?></p>
			<?php
			$payment_data['payment_status'] = 'Completed';
		} else {
			?>
			<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
			<p><strong>Error:</strong> (<?php echo $response_reason_code; ?> - <?php echo $response_reason_code; ?>) - <?php echo $response_reason_text; ?></p>
			<?php
			$payment_data['payment_status'] = 'Payment Declined';
		}


//Debugging option
		if ($email_transaction_dump == true) {
// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'First Data e4 Notification - Gateway Variable Dump';
			$body = "A Firstdata e4 payment notification was successfully recieved\n";
			$body .= "from " . $myE4->ipnData['x_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myE4->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	} else {
		?>
		<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2> <?php
		if (is_writable('authorize.txt'))
			file_put_contents('authorize.txt', "FAILURE\n\n" . $myE4->ipnData);
//echo something to the screen so authent knows that the ipn works
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . $myE4->ipnData['x_email'] . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		foreach ($myE4->ipnData as $key => $value) {
			$body .= "\n$key: $value\n";
		}
		wp_mail($payment_data['contact'], $subject, $body);
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
