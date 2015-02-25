<?php

function espresso_transactions_authnet_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['x_cust_id'])) {
		$attendee_id = $_REQUEST['x_cust_id'];
	}
	return $attendee_id;
}

function espresso_process_authnet($payment_data) {
// Include the authorize.net library
	include_once ('Authorize.php');

// Create an instance of the authorize.net library
	$myAuthorize = new Espresso_Authorize();
	echo '<!--Event Espresso Authorize.net SIM Gateway Version ' . $myAuthorize->gateway_version . '-->';
// Log the IPN results
	$myAuthorize->ipnLog = TRUE;

	$authnet_settings = get_option('event_espresso_authnet_settings');
	$authnet_login_id = $authnet_settings['authnet_login_id'];
	$authnet_transaction_key = $authnet_settings['authnet_transaction_key'];

// Enable test mode if needed
//4007000000027  <-- test successful visa
//4222222222222  <-- test failure card number
	if ($authnet_settings['use_sandbox']) {
		$myAuthorize->enableTestMode();
		$email_transaction_dump = true;
	}
	
	if ($authnet_settings['use_md5']) {
		$myAuthorize->enableUseMD5();
		$myAuthorize->setMD5Value($authnet_settings['authnet_md5_value']);
	}

// Specify your authorize login and secret
	$myAuthorize->setUserInfo($authnet_login_id, $authnet_transaction_key);
	$payment_data['txn_type'] = 'authorize.net SIM';
	$payment_data['payment_status'] = "Incomplete";
	if (!empty($_REQUEST['x_trans_id'])) {
		$payment_data['txn_id'] = $_REQUEST['x_trans_id'];
	} else {
		$payment_data['txn_id'] = 0;
	}
	$payment_data['txn_details'] = serialize($_REQUEST);
//	$curl_session_id = uniqid('', true);
//	global $wpdb;
//	$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET attendee_session = '" . $curl_session_id . "' WHERE attendee_session ='" . $payment_data['attendee_session'] . "' ";
//	$wpdb->query($sql);
//	$payment_data['attendee_session'] = $curl_session_id;
// Check validity and write down it
	if ($myAuthorize->validateIpn()) {
		$payment_data['txn_id'] = $myAuthorize->ipnData['x_trans_id'];
		$payment_data['txn_details'] = serialize($myAuthorize->ipnData);

		//file_put_contents('authorize.txt', 'SUCCESS' . date("m-d-Y")); //Used for debugging purposes
		//Be sure to echo something to the screen so authent knows that the ipn works
		//store the results in reusable variables
		if ($myAuthorize->ipnData['x_response_code'] == 1) {
			$myAuthorize->logResults(true);
			?>
			<p><?php _e('Your transaction has been processed.', 'event_espresso'); ?></p>
			<?php
			$payment_data['payment_status'] = 'Completed';
		} else {
			$myAuthorize->lastError = $respmsg;
			$myAuthorize->logResults(false);
			?>
			<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
			<p><strong>Error:</strong> (Payment was declined)</p>
			<?php
			$payment_data['payment_status'] = 'Payment Declined';
		}

		//Debugging option
		if ($email_transaction_dump == true) {
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Authorize.net Notification - Gateway Variable Dump';
			$body = "An authorize.net payment notification was successfully recieved\n";
			$body .= "from " . $myAuthorize->ipnData['x_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myAuthorize->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	} else {
		$myAuthorize->lastError = 'MD5 mismatch';
		$myAuthorize->logResults(false);
		?>
		<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
		<p><strong>Error:</strong> (IPN response did not validate) </p>
		<?php
		if (is_writable('authorize.txt'))
			file_put_contents('authorize.txt', "FAILURE\n\n" . $myAuthorize->ipnData);
		//echo something to the screen so authent knows that the ipn works
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . $myAuthorize->ipnData['x_email'] . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		foreach ($myAuthorize->ipnData as $key => $value) {
			$body .= "\n$key: $value\n";
		}
		wp_mail($payment_data['contact'], $subject, $body);
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
