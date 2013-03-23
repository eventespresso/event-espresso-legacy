<?php
function espresso_display_anz($payment_data){
	global $wpdb;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'ANZ';
	
	//$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	
	global $wpdb, $org_options;
	$anz_settings = get_option('event_espresso_anz_settings');
	$merchant_id=$anz_settings['anz_id'];
	$access_code=$anz_settings['anz_access_code'];
	$secure_secret=$anz_settings['anz_secure_secret'];
	$bypass_payment_page = ($anz_settings['bypass_payment_page'] == 'Y')?true:false;
	$button_url = $anz_settings['button_url'];
	
	if ($anz_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	$return_url= $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $payment_data['registration_id']. '&type=anz';
	$server_url="https://migs.mastercard.com.au/vpcpay";
	
	if (empty($anz_settings['button_url'])) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/anz/anz.gif")) {
			$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/anz/anz.gif";
		}
	} elseif (isset($anz_settings['button_url'])) {
		$button_url = $anz_settings['button_url'];
	} else {
		$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/anz/anz.gif";
	}
	if(!empty($button_url)){
		$submit_html="<img src='$button_url'/>";
	}else{
		$submit_html="Purchase with ANZ";
	}
	if($bypass_payment_page){
		$bypass_payment_page_js="<script>document.getElementById('anz_form').submit();</script>";
	}else{
		$bypass_payment_page_js="";
	}
	$txn_id = $payment_data['registration_id'];
	$amount_in_cents=$payment_data['total_cost']*100;
	//as per eGate Virtual Payment Clietn Guide Rev 1.2.0, all inputs must be hashed, in ascending alphabetical order
	$hash_data = array(
		'01_secret_must_come_first'=>$secure_secret,
		'vpc_AccessCode'=>$access_code,
		'vpc_Amount'=>$amount_in_cents,
		'vpc_Command'=>'pay',
		'vpc_Locale'=>'en',
		'vpc_Merchant'=>$merchant_id,
		'vpc_MerchTxnRef'=>$txn_id,
		'vpc_OrderInfo'=>'VPC Example',
		'vpc_ReturnURL'=>$return_url,
		'Title'=>'PHP VPC 3-Party',
		'vpc_Version'=>1);
	$success = ksort($hash_data);
	$url_encoded_hash_values=array();
	foreach($hash_data as $field_name => $field_value){
		$url_encoded_hash_values[ urlencode( $field_name ) ] = urlencode( $field_value );
	}
	$md5_data = implode( "", $hash_data );
	$hash_string = strtoupper( md5( $md5_data ));
//	echo "fields used in hash:".implode( "", $hash_fields );
	//remove our super-secret thing from the list, because we're about to 
	//send each of them as a GET parameter to ANZ
	unset($url_encoded_hash_values['01_secret_must_come_first']);
	unset($hash_data['01_secret_must_come_first']);
	$full_url = add_query_arg(array('vpc_SecureHash'=>$hash_string), add_query_arg($url_encoded_hash_values,$server_url));
	?><a href='<?php echo $full_url?>'><?php echo $submit_html?></a>
	<?php echo $bypass_payment_page_js;?>
<?php



	return $payment_data;
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_anz');

