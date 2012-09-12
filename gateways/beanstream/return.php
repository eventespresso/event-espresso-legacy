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
	$country_data = getCountryFullData($org_options['organization_country']);
	$data['ordCountry'] = 'ordCountry='.$country_data['iso_code_2'];
	$post_data = implode('&', $data);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_URL,'https://www.beanstream.com/scripts/process_transaction.asp');
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	$txnResult = curl_exec($ch);
	echo "Result:<br>";
	echo $txnResult;
	curl_close($ch);
	
	return $payment_data;
}