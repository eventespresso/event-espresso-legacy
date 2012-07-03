<?php

function espresso_transactions_mwarrior_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_mwarrior_get_attendee_id');

function espresso_process_mwarrior($payment_data) {
	$attendee_id = $payment_data['attendee_id'];
// Include the mwarrior library
	include_once ('Mwarrior.php');
	echo '<!--Event Espresso Merchant Warrior Gateway Version ' . $mwarrior_gateway_version . '-->';
// Create an instance of the mwarrior library
	$mwarrior = new Mwarrior();
// Log the IPN results
	$mwarrior->ipnLog = TRUE;
	$mwarrior_settings = get_option('event_espresso_mwarrior_settings');
// Set merchant info
	$mwarrior->setMerchantInfo($mwarrior_settings['mwarrior_id'], $mwarrior_settings['mwarrior_apikey'], $mwarrior_settings['mwarrior_passphrase']);

// Enable test mode if needed
	if ($mwarrior_settings['use_sandbox']) {
		$mwarrior->enableTestMode();
		$email_transaction_dump = true;
	}

// Check validity and write down it
	if ($mwarrior->validateIpn()) {
		global $wpdb;
		$sql = "SELECT fname, lname FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = '" . $attendee_id . "'";
		$result = $wpdb->get_row($sql, ARRAY_A);
		extract($result);
		//store the results in reusable variables
		$txn_type = "CC";
		$txn_id = $mwarrior->response['responseData']['transactionID'];
		$amount_pd = $mwarrior->_getAmount();
		$payment_date = date("m-d-Y");

		//Check the payment status recieved from Merchant Warrior
		//wp_mail($contact,'IPN Debug Payment Status', 'Payment Status: ' . $mwarrior->ipnData['payment_status']);
		if ($mwarrior->response['status'] == 'approved') {
			?>
			<h2><?php _e('Thank You!', 'event_espresso'); ?></h2>
			<p><?php _e('Your transaction has been processed.', 'event_espresso'); ?></p>
			<?php
			$payment_status = 'Completed';
			$payment_data['event_link'] = $event_link;
			$payment_data['fname'] = $fname;
			$payment_data['lname'] = $lname;
			$payment_data['txn_type'] = $txn_type;
			$payment_data['payment_date'] = $payment_date;
			$payment_data['total_cost'] = $total_cost;
			$payment_data['payment_status'] = $payment_status;
			$payment_data['att_registration_id'] = $att_registration_id;
			$payment_data['txn_id'] = $txn_id;
		} else {
			if (!isset($mwarrior->response['result'])) { // Only for Redirect 302
				// Query here - find out why
				$resp = $mwarrior->queryCard($txn_id);
				?>
				<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
				<p>Transaction ID: <?php echo $txn_id; ?> </p>
				<p><strong>Error:</strong> (<?php echo $resp['responseCode']; ?> - <?php echo $resp['responseMessage']; ?>) - <?php echo urldecode($resp['authMessage']); ?></p>
				<?php
			}
			$payment_status = 'Declined';
		}

		global $wpdb;
		if (!isset($mwarrior->response['result'])) { // Redirect 302
			$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_type = '$txn_type', txn_id = '$txn_id', amount_pd = '$amount_pd',  payment_date ='$payment_date', transaction_details = '" . serialize($mwarrior) . "' WHERE registration_id ='" . $mwarrior->response['reg_id'] . "' ";
		} else { // XML Post notification
			$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_type = '$txn_type', txn_id = '$txn_id', amount_pd = '$amount_pd',  payment_date ='$payment_date' WHERE registration_id ='" . $mwarrior->response['reg_id'] . "' ";
		}

		$wpdb->query($sql);

		//Debugging option
		if ($email_transaction_dump == true) {
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification was successfully recieved\n";
			$body .= "from " . $mwarrior->fields['customerEmail'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($mwarrior->response as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $k => $v) {
						$body .= "\n$k: $v\n";
					}
				} else {
					$body .= "\n$key: $value\n";
				}
			}
			wp_mail($contact, $subject, $body);
		}

		// If XML Post Notification then exit - Workaround for not sending another registration email
		if (isset($mwarrior->response['result'])) {
			exit();
		}
	} else {
		?>
		<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
		<p><strong>Error:</strong> (<?php echo stripslashes($_GET['message']); ?>) </p>
		<?php
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . $mwarrior->fields['customerEmail'] . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		foreach ($mwarrior->response as $key => $value) {
			$body .= "\n$key: $value\n";
		}
		wp_mail($contact, $subject, $body);
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_mwarrior');