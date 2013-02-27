<?php

function espresso_transactions_wepay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_wepay($payment_data) {
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = 0;
	$payment_data['txn_type'] = 'WePay';
	$payment_data['payment_status'] = 'Incomplete';
	$wepay_settings = get_option('event_espresso_wepay_settings');
	include_once ('Wepay.php');
	if ($wepay_settings['use_sandbox']) {
		Espresso_Wepay::useStaging($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	} else {
		Espresso_Wepay::useProduction($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	}
	$wepay = new Espresso_Wepay($wepay_settings['access_token']);
	$raw = $wepay->request('checkout', array('checkout_id' => $_REQUEST['checkout_id']));
	if (!empty($raw)) {
		$payment_data['txn_details'] = serialize(get_object_vars($raw));
		$payment_data['txn_id'] = $raw->reference_id;
		$responsecode = $raw->state;
		if (($payment_data['payment_status'] != 'Completed') && ($responsecode == 'authorized'
						|| $responsecode == 'reserved'
						|| $responsecode == 'captured'
						|| $responsecode == 'settled')) {
			$payment_data['payment_status'] = 'Completed';
			if ($wepay_settings['use_sandbox']) {
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . " on " . date('m/d/Y');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				foreach ($raw as $key => $value) {
					$body .= $key . " = " . $value . "\n";
				}
				foreach ($payment_data as $key => $value) {
					$body .= $key . " = " . $value . "\n";
				}
				wp_mail($payment_data['contact'], $subject, $body);
			}
		} elseif ($responsecode == 'cancelled'
						|| $responsecode == 'failed') {
			$subject = 'Problem with your WePay payment';
			$body = "WePay has informed us that your payment for " . $payment_data['event_link'] . " has moved to a status of " . $responsecode . ".\n";
			$body .= "We have changed your payment status for the event to 'Incomplete'.\n";
			$body .= "Please look into the cause in your account interface at wepay.com.\nThank You.\n";
			wp_mail($payment_data['email'], $subject, $body);
		} elseif ($payment_status != 'Completed' && $responsecode != 'expired') {
			$payment_status = "Incomplete";
			echo "Response code = " . $responsecode;
			echo "\nResponse = ";
			var_dump($raw);
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($raw as $key => $value) {
				$body .= $key . " = " . $value . "\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}

function espresso_process_wepay_callback($payment_data) {
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_type'] = 'WePay';
	$wepay_settings = get_option('event_espresso_wepay_settings');
	include_once ('Wepay.php');
	if ($wepay_settings['use_sandbox']) {
		Espresso_Wepay::useStaging($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	} else {
		Espresso_Wepay::useProduction($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	}
	$wepay = new Espresso_Wepay($wepay_settings['access_token']);
	$raw = $wepay->request('checkout', array('checkout_id' => $_REQUEST['checkout_id']));
	if (!empty($raw)) {
		$payment_data['txn_details'] = serialize(get_object_vars($raw));
		$payment_data['txn_id'] = $raw->reference_id;
		$responsecode = $raw->state;
		if ($responsecode == 'cancelled'
						|| $responsecode == 'failed') {
			$payment_data['payment_status'] = 'Incomplete';
			$subject = 'Problem with your WePay payment';
			$body = "WePay has informed us that your payment for " . $payment_data['event_link'] . " has moved to a status of " . $responsecode . ".\n";
			$body .= "We have changed your payment status for the event to 'Incomplete'.\n";
			$body .= "Please look into the cause in your account interface at wepay.com.\nThank You.\n";
			wp_mail($payment_data['email'], $subject, $body);
		}
		return $payment_data;
	}
}
