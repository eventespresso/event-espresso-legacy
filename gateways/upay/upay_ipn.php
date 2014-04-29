<?php

function espresso_transactions_paypal_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_paypal($payment_data) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$payment_data['txn_type'] = 'PayPal';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	include_once ('Paypal.php');
	$myPaypal = new EE_Paypal();
	echo '<!--Event Espresso PayPal Gateway Version ' . $myPaypal->gateway_version . '-->';
	$myPaypal->ipnLog = TRUE;
	$paypal_settings = get_option('event_espresso_paypal_settings');
	if ($paypal_settings['use_sandbox']) {
		$myPaypal->enableTestMode();
	}
	if ($myPaypal->validateIpn()) {
		$payment_data['txn_details'] = serialize($myPaypal->ipnData);
		$payment_data['txn_id'] = $myPaypal->ipnData['txn_id'];
		if ($myPaypal->ipnData['mc_gross'] >= $payment_data['total_cost'] && ($myPaypal->ipnData['payment_status'] == 'Completed' || $myPaypal->ipnData['payment_status'] == 'Pending')) {
			$payment_data['payment_status'] = 'Completed';
			if ($paypal_settings['use_sandbox']) {
				// For this, we'll just email ourselves ALL the data as plain text output.
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . $myPaypal->ipnData['payer_email'] . " on " . date('m/d/Y');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				foreach ($myPaypal->ipnData as $key => $value) {
					$body .= "\n$key: $value\n";
				}
				wp_mail($payment_data['contact'], $subject, $body);
			}
		} elseif(in_array($myPaypal->ipnData['payment_status'],array( 'Refunded', 'Reversed','Canceled_Reversal')) ){
			/*$subject = 'Payment Refund Notice from PayPal';
			$body = "A payment has been refunded or reversed:\n";
			$body .= "Payer's Email: " . $myPaypal->ipnData['payer_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myPaypal->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			$body .= "Event Espresso does not handle payment refunds automatically. You will want to verify that the registration for this
				user has been cancelled here ".site_url()."/wp-admin/";
			wp_mail($payment_data['contact'], $subject, $body);*/
			die;
		}else {
			
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . $myPaypal->ipnData['payer_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myPaypal->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
