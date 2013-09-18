<?php

function espresso_transactions_mwarrior_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_mwarrior($payment_data) {
	$payment_data['txn_type'] = 'Mwarrior';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);

	include_once ('Mwarrior.php');
	$mwarrior = new Espresso_Mwarrior();
	echo '<!--Event Espresso Merchant Warrior Gateway Version ' . $mwarrior->gateway_version . '-->';
	$mwarrior->ipnLog = TRUE;
	$mwarrior_settings = get_option('event_espresso_mwarrior_settings');
	$mwarrior->setMerchantInfo($mwarrior_settings['mwarrior_id'], $mwarrior_settings['mwarrior_apikey'], $mwarrior_settings['mwarrior_passphrase']);

// Enable test mode if needed
	if ($mwarrior_settings['use_sandbox']) {
		$mwarrior->enableTestMode();
		$email_transaction_dump = true;
	}

	if ($mwarrior->validateIpn()) {
		$payment_data['txn_details'] = serialize($mwarrior->response);
		$payment_data['txn_id'] = $mwarrior->response['responseData']['transactionID'];
		if ($mwarrior->response['status'] == 'approved') {
			?>
			
			<p><?php _e('Your transaction has been processed.', 'event_espresso'); ?></p>
			<?php
			$payment_data['payment_status'] = 'Completed';
		} else {
			if (!isset($mwarrior->response['result'])) { // Only for Redirect 302
				// Query here - find out why
				$resp = $mwarrior->queryCard($payment_data['txn_id']);
				?>
				<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
				<p>Transaction ID: <?php echo $payment_data['txn_id']; ?> </p>
				<p><strong>Error:</strong> (<?php echo $resp['responseCode']; ?> - <?php echo $resp['responseMessage']; ?>) - <?php echo urldecode($resp['authMessage']); ?></p>
				<?php
			}
			$payment_data['payment_status'] = 'Payment Declined';
		}

		//Debugging option
		if ($email_transaction_dump == true) {
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification was successfully recieved\n";
			$body .= "from " . $payment_data['email'] . " on " . date('m/d/Y');
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
			wp_mail($payment_data['contact'], $subject, $body);
		}
	} else {
		$payment_data['payment_status'] = "Incomplete";
		?>
		<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
		<p><strong>Error:</strong> (<?php echo stripslashes($_GET['message']); ?>) </p>
		<?php
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . $payment_data['email'] . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		foreach ($mwarrior->response as $key => $value) {
			$body .= "\n$key: $value\n";
		}
		wp_mail($payment_data['contact'], $subject, $body);
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
