<?php

function espresso_transactions_upay_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['EXT_TRANS_ID'])) {
		$attendee_id = $_REQUEST['EXT_TRANS_ID'];
	}
	return $attendee_id;
}

//get the registration ID and pretend it iwas in the request all along
function espresso_transactions_upay_get_reg_id($payment_data){
	if(isset($payment_data['attendee_id'])){
		global $wpdb;
		$reg_id = $wpdb->get_var($wpdb->prepare("SELECT registration_id FROM ".$wpdb->prefix."events_attendee WHERE id=%d LIMIT 1",$payment_data['attendee_id']));
		$payment_data['registration_id'] = $reg_id;
		$_REQUEST['registration_id'] = $reg_id;
	}
	return $payment_data;
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
	if ($upay_settings['use_sandbox']) {
		$myuPay->enableTestMode();
	}
	if ($myuPay->validateIpn()) {
		$payment_data['txn_details'] = serialize($myuPay->ipnData);
		$payment_data['txn_id'] = $myuPay->ipnData['EXT_TRANS_ID'];
		if ($myuPay->ipnData['pmt_amt'] >= $payment_data['total_cost'] && ($myuPay->ipnData['pmt_status'] == 'success')) {
			$payment_data['payment_status'] = 'Completed';
			if ($upay_settings['use_sandbox']) {
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
