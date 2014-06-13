<?php

function espresso_transactions_eway_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_eway($payment_data) {
	global $wpdb;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'EW';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$eway_settings = get_option('event_espresso_eway_settings');
	switch ($eway_settings['region']) {
		case 'NZ':
			$results_request = 'https://nz.ewaygateway.com/Result/';
			break;
		case 'AU':
			$results_request = 'https://au.ewaygateway.com/Result/';
			break;
		case 'UK':
			$results_request = 'https://payment.ewaygateway.com/Result/';
			break;
	}
	if ($eway_settings['use_sandbox']) {
		$results_request .= "?CustomerID=" . '87654321';
		$results_request .= "&UserName=" . 'TestAccount';
	} else {
		$results_request .= "?CustomerID=" . $eway_settings['eway_id'];
		$results_request .= "&UserName=" . $eway_settings['eway_username'];
	}
	$results_request .= "&AccessPaymentCode=" . $_REQUEST['AccessPaymentCode'];
	$results_request = str_replace(" ", "%20", $results_request);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $results_request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	if (defined('CURL_PROXY_REQUIRED') && CURL_PROXY_REQUIRED == 'True') {
		$proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
	}

	$response = curl_exec($ch);

	if (!empty($response)) {
		$payment_data['txn_details'] = $response;
		$responsecode = espresso_eway_fetch_data($response, '<responsecode>', '</responsecode>');
		$payment_data['txn_id'] = espresso_eway_fetch_data($response, '<trxnnumber>', '</trxnnumber>');

		if ($responsecode == '00' || $responsecode == '08') {

			$payment_data['payment_status'] = 'Completed';

			//Debugging option
			if ($eway_settings['use_sandbox']) {
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debugging / Sandbox output', 'event_espresso') . '</h3><br />';
				echo "Response code = " . $responsecode;
				echo "\nResponse = ";
				var_dump($response);
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('End of Debugging / Sandbox output (this will go away when you switch to live transactions)', 'event_espresso') . '</h3>';
				// For this, we'll just email ourselves ALL the data as plain text output.
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . " on " . date('m/d/Y');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				$body .= $response;
				wp_mail($payment_data['contact'], $subject, $body);
			}
		} else {
			echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debugging / Sandbox output', 'event_espresso') . '</h3><br />';
			echo "Response code = " . $responsecode;
			echo "\nResponse = ";
			var_dump($response);
			echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('End of Debugging / Sandbox output (this will go away when you switch to live transactions)', 'event_espresso') . '</h3>';
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			$body .= $response;
			wp_mail($payment_data['contact'], $subject, $body);
		}
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}

	function espresso_eway_fetch_data($string, $start_tag, $end_tag) {

		$position = stripos($string, $start_tag);
		$str = substr($string, $position);
		$str_second = substr($str, strlen($start_tag));
		$second_positon = stripos($str_second, $end_tag);
		$str_third = substr($str_second, 0, $second_positon);
		$fetch_data = trim($str_third);
		return $fetch_data;
	}
