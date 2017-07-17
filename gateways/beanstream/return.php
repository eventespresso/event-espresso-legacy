<?php

function espresso_transactions_beanstream_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_beanstream($payment_data) {
	global $org_options;
	$beanstream_settings = get_option('event_espresso_beanstream_settings');
	$data = array();
	$data['requestType'] = 'requestType='.'BACKEND';
	$data['merchant_id'] = 'merchant_id='.$beanstream_settings['merchant_id'];
	$data['trnOrderNumber'] = 'trnOrderNumber='.$payment_data['registration_id'];
	$data['trnAmount'] = 'trnAmount='.$payment_data['total_cost'];
	$data['trnCardOwner'] = 'trnCardOwner='.$_POST['first_name'].' '.$_POST['last_name'];
	$data['trnCardNumber'] = 'trnCardNumber='.$_POST['card_num'];
	$data['trnExpMonth'] = 'trnExpMonth='.$_POST['expmonth'];
	$data['trnExpYear'] = 'trnExpYear='.$_POST['expyear'];
	$data['trnCardCvd'] = 'trnCardCvd='.$_POST['cvv'];
	$data['ordName'] = 'ordName='.$payment_data['fname'].' '.$payment_data['lname'];
	$data['ordEmailAddress'] = 'ordEmailAddress='.$payment_data['email'];
	$data['ordPhoneNumber'] = 'ordPhoneNumber='.$_POST['phone'];
	$data['ordAddress1'] = 'ordAddress1='.$_POST['address'];
	$data['ordCity'] = 'ordCity='.$_POST['city'];
	$data['ordProvince'] = 'ordProvince='.$_POST['state'];
	$data['ordPostalCode'] = 'ordPostalCode='.$_POST['zip'];
	$data['ordCountry'] = 'ordCountry='.$_POST['country'];
	$post_data = implode('&', $data);
	
	if ( empty( $beanstream_settings['beanstream_url'] ) ) {
		$beanstream_settings['beanstream_url'] = 'https://web.na.bambora.com/scripts/process_transaction.asp';
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_URL,$beanstream_settings['beanstream_url']);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	$txnResult = curl_exec($ch);
	curl_close($ch);
	
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'Beanstream';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($_REQUEST);
	
	if(!empty($txnResult)) {
		$temp_results = explode('&', $txnResult);
		$results = array();
		foreach ($temp_results as $temp_result) {
			$temp_temp_result = explode('=', $temp_result);
			$results[$temp_temp_result[0]] = $temp_temp_result[1];
		}
		$payment_data['txn_id'] = $results['trnId'];
		$payment_data['txn_details'] = serialize($results);
		if ($results['trnApproved'] == 1) {
			$payment_data['payment_status'] = 'Completed';
		} else {
			echo '<p><strong class="credit_card_failure">Attention: Your transaction was declined for the following reason(s):</strong><br />';
			echo urldecode($results['messageText']);
		}
	}
	
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
    return $payment_data;
}