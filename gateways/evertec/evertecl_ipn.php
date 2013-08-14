<?php

function espresso_transactions_evertec_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_evertec($payment_data) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$payment_data['txn_type'] = 'Evertec';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	include_once ('Evertec.php');
	$myEvertec = new EE_Evertec();
	echo '<!--Event Espresso Evertec Gateway Version ' . $myEvertec->gateway_version . '-->';
	$myEvertec->ipnLog = TRUE;
	$evertec_settings = get_option('event_espresso_evertec_settings');
	if ($evertec_settings['use_sandbox']) {
		$myEvertec->enableTestMode();
	}
	if ($myEvertec->validateIpn()) {
		$payment_data['txn_details'] = serialize($myEvertec->ipnData);
		$payment_data['txn_id'] = $myEvertec->ipnData['txn_id'];
		if ($myEvertec->ipnData['mc_gross'] >= $payment_data['total_cost'] && ($myEvertec->ipnData['payment_status'] == 'Completed' || $myEvertec->ipnData['payment_status'] == 'Pending')) {
			$payment_data['payment_status'] = 'Completed';
			if ($evertec_settings['use_sandbox']) {
				// For this, we'll just email ourselves ALL the data as plain text output.
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . $myEvertec->ipnData['payer_email'] . " on " . date('m/d/Y');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				foreach ($myEvertec->ipnData as $key => $value) {
					$body .= "\n$key: $value\n";
				}
				wp_mail($payment_data['contact'], $subject, $body);
			}
		} elseif(in_array($myEvertec->ipnData['payment_status'],array( 'Refunded', 'Reversed','Canceled_Reversal')) ){
			/*$subject = 'Payment Refund Notice from Evertec';
			$body = "A payment has been refunded or reversed:\n";
			$body .= "Payer's Email: " . $myEvertec->ipnData['payer_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myEvertec->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			$body .= "Event Espresso does not handle payment refunds automatically. You will want to verify that the registration for this
				user has been cancelled here ".site_url()."/wp-admin/";
			wp_mail($payment_data['contact'], $subject, $body);*/
			die;
		}else {
			
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . $myEvertec->ipnData['payer_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myEvertec->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
