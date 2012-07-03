<?php

function espresso_transactions_wepay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_wepay_get_attendee_id');

function espresso_process_wepay($payment_data) {
	global $wpdb;
	extract($payment_data);
	$wepay_settings = get_option('event_espresso_wepay_settings');
	include_once ('Wepay.php');
	if ($wepay_settings['use_sandbox']) {
		Wepay::useStaging($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	} else {
		Wepay::useProduction($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	}
	$wepay = new Wepay($wepay_settings['access_token']);
	$raw = $wepay->request('checkout', array('checkout_id'=>$_REQUEST['checkout_id']));
	$responsecode = $raw->state;
	if (($payment_status != 'Completed') && ($responsecode == 'authorized'
					||$responsecode == 'reserved'
					|| $responsecode == 'captured'
					|| $responsecode == 'settled')) {

		$payment_data['payment_status'] = 'Completed';
		$payment_data['payment_date'] = date("d-m-Y");
		$payment_data['txn_type'] = 'Wepay';
		$payment_data['txn_id'] = $raw->reference_id;
		$payment_data['total_cost'] = $raw->amount;
		$event_url = espresso_reg_url($event_id);
		$payment_data['event_link'] = '<a href="' . $event_url . '">' . $event_name . '</a>';

		espresso_update_attendee_payment_status_by_session_id($payment_data);

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
			wp_mail($contact, $subject, $body);
		}
	} elseif ($responsecode == 'cancelled'
					|| $responsecode == 'failed') {
		$payment_data['payment_status'] = 'Not Completed';
		$payment_data['payment_date'] = date("d-m-Y");
		$payment_data['txn_type'] = 'Wepay';
		$payment_data['txn_id'] = $raw->reference_id;
		$payment_data['total_cost'] = $raw->amount;
		$event_url = espresso_reg_url($event_id);
		$payment_data['event_link'] = '<a href="' . $event_url . '">' . $event_name . '</a>';
		espresso_update_attendee_payment_status_by_session_id($payment_data);

		$subject = 'Problem with your WePay payment';
		$body = "WePay has informed us that your payment for " . $payment_data['event_link'] . " has moved to a status of " . $responsecode . ".\n";
		$body .= "We have changed your payment status for the event to 'Not Completed'.\n";
		$body .= "Please look into the cause in your account interface at wepay.com.\nThank You.\n";
		wp_mail($email, $subject, $body);
	} elseif ($payment_status != 'Completed' && $responsecode != 'expired') {
		$payment_status = "Not Completed";
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
		wp_mail($contact, $subject, $body);
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_wepay');
add_action('action_hook_espresso_display_thankyou_page_payment_gateway', 'espresso_process_wepay');