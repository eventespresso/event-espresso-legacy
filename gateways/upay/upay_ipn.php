<?php

function espresso_transactions_upay_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['EXT_TRANS_ID'])) {
		global $wpdb;
		$reg_id = $_REQUEST['EXT_TRANS_ID'];
		$attendee_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".$wpdb->prefix."events_attendee WHERE registration_id=%s LIMIT 1",$reg_id));
		$_REQUEST['registration_id'] = $reg_id;
		$_REQUEST['id'] = $attendee_id;
	}
	return $attendee_id;
}


function espresso_process_upay($payment_data) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$payment_data['txn_type'] = 'uPay';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	include_once ('EE_uPay.php');
	$myuPay = new EE_uPay();
	echo '<!--Event Espresso uPay Gateway Version ' . $myuPay->gateway_version . '-->';
	$myuPay->ipnLog = TRUE;
	$upay_settings = get_option('event_espresso_upay_settings');
	if ($upay_settings['debug_mode']) {
		$myuPay->enableTestMode();
	}
	if ($myuPay->validateIpn()) {
		$payment_data['txn_details'] = serialize($myuPay->ipnData);
		$payment_data['txn_id'] = $myuPay->ipnData['sys_tracking_id'];
		if ($myuPay->ipnData['pmt_amt'] >= $payment_data['total_cost'] && ($myuPay->ipnData['pmt_status'] == 'success')) {
			$payment_data['payment_status'] = 'Completed';
			if ($upay_settings['debug_mode']) {
				// For this, we'll just email ourselves ALL the data as plain text output.
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . $myuPay->ipnData['payer_email'] . " on " . date('m/d/Y');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				foreach ($myuPay->ipnData as $key => $value) {
					$body .= "\n$key: $value\n";
				}
				wp_mail($payment_data['contact'], $subject, $body);
			}
		}else {
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . $myuPay->ipnData['acct_email_address'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myuPay->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
