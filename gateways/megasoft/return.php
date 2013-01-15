<?php

function espresso_transactions_megasoft_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['cust_id'])) {
		$attendee_id = $_REQUEST['cust_id'];
	}
	return $attendee_id;
}
function espresso_process_megasoft($payment_data){
	echo "get contents of google:";
echo "begin process megasoft";
	global $wpdb, $org_options;
	$megasoft_settings = get_option('event_espresso_megasoft_settings');
	$use_sandbox = $megasoft_settings['use_sandbox'];
	if ($use_sandbox) {
		$url = "https://paytest.megasoft.com.ve:8443/payment/action/procesar-compra";
	} else {
		$url = "https://payment.megasoft.com.ve:8443/payment/action/procesar-compra";
	}
	$Request = "?cod_afiliacion=".$megasoft_settings['megasoft_login_id'];
	$Request .= "&transcode=0141";
	$Request .= "&pan=".$_POST['card_num'];
	$Request .= "&cvv2=".$_POST['ccv_code'];
	$Request .= "&cid=".$_POST['cid_code'].$_POST['cid'];
	$Request .= "&expdate=".$_POST['exp_date'];
	$Request .= "&amount=".number_format(100*$payment_data['total_cost'], 0, '', '');
	$Request .= "&client=".$_POST['first_name']." ".$_POST['last_name'];
	$Request .= "&factura=".$_POST['invoice_num'];
	
	$fullUrl=$url.$Request;
	echo "request sent to:".$fullUrl;
	$response=wp_remote_get($fullUrl,array('timeout'=>15,'sslverify'=>false,'user-agent'=>'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'));
	var_dump($response);
}
function espresso_process_megasoft_old($payment_data) {
echo "begin old process megasoft";
	extract($payment_data);
	global $wpdb, $org_options;
	$megasoft_settings = get_option('event_espresso_megasoft_settings');
	$use_sandbox = $megasoft_settings['use_sandbox'];
	$curl = curl_init();
	if ($use_sandbox) {
		$url = "https://paytest.megasoft.com.ve:8443/payment/action/procesar-compra";
		$errors = "set_port=" . curl_setopt($curl, CURLOPT_PORT, 8443) . "&";
	} else {
//@todo: cant see in documentation whether this needs to be port 8443 too or not...
		$url = "https://payment.megasoft.com.ve:8443/payment/action/procesar-compra";
		$errors = '';
	}
	$Request = "?cod_afiliacion=".$megasoft_settings['megasoft_login_id'];
	$Request .= "&transcode=0141";
	$Request .= "&pan=".$_POST['card_num'];
	$Request .= "&cvv2=".$_POST['ccv_code'];
	$Request .= "&cid=".$_POST['cid_code'].$_POST['cid'];
	$Request .= "&expdate=".$_POST['exp_date'];
	$Request .= "&amount=".number_format(100*$payment_data['total_cost'], 0, '', '');
	$Request .= "&client=".$_POST['first_name']." ".$_POST['last_name'];
	$Request .= "&factura=".$_POST['invoice_num'];
	
	$errors .= "set_verbose=" . curl_setopt($curl, CURLOPT_VERBOSE, 1) . "&";
	$errors .= "set_verifypeer=" . curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE) . "&";
	$errors .= "set_timeout=" . curl_setopt($curl, CURLOPT_TIMEOUT, 45) . "&";
	$errors .= "set_url=" . $url.$Request . "," . curl_setopt($curl, CURLOPT_URL, $url.$Request) . "&";
	$errors .= "set_returntransfer=" . curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1) . "&";
	//$errors .= "set_postfields=" . curl_setopt($curl, CURLOPT_POSTFIELDS, urlencode($Request)) . "&";
	
	//execute the curl POST
	$Response = curl_exec($curl);
	$ver_array = curl_version();
	foreach ($ver_array as $key=>$value) {
		if (is_array($value)) $value = implode(',', $value);
		$errors .= "curl_ver_" . $key . "=" . urlencode($value) . "&";
	}
	$errors .= "curl_error=" . urlencode(curl_error($curl));
	curl_close($curl);
	if (!empty($Response)) {
		$Response .= '&';
	}
	$Response .= $errors;
	var_dump($Response);
}