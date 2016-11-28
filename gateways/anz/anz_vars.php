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
	
	$button_url = espresso_select_button_for_display($anz_settings['button_url'], "anz/anz.gif");
	if(!empty($button_url)){
		$submit_html="<img class='payment-option-lnk allow-leave-page' src='$button_url'/>";
	}
	
	//http://localhost/eetrunk31/?page_id=5&r_id=42-5150cf9c1f748&type=anz&Title=PHP+VPC+3-Party&vpc_3DSECI=01&vpc_3DSXID=7S%2BXbvLUbBrsxTkYaXJMxjx0yhM%3D&vpc_3DSenrolled=Y&vpc_3DSstatus=A&vpc_AVSResultCode=Unsupported&vpc_AcqAVSRespCode=Unsupported&vpc_AcqCSCRespCode=N&vpc_AcqResponseCode=04&vpc_Amount=1000&vpc_BatchNo=20130326&vpc_CSCResultCode=N&vpc_Card=MC&vpc_Command=pay&vpc_Locale=en&vpc_MerchTxnRef=425150cf9c1f748&vpc_Merchant=ANZCAZALYS&vpc_Message=Expired+Card&vpc_OrderInfo=VPC+Example2&vpc_ReceiptNo=130326378001&vpc_SecureHash=329FC69DA1F03B3F7B896C97BF488E45&vpc_TransactionNo=2000000187&vpc_TxnResponseCode=4&vpc_VerSecurityLevel=06&vpc_VerStatus=M&vpc_VerToken=how5CsZD%2BBZwCAEAAAJ1AhUAAAA%3D&vpc_VerType=3DS&vpc_Version=1
	$txn_id = str_replace("-","",$payment_data['registration_id']);
	$amount_in_cents=$payment_data['total_cost']*100;
	
	//as per eGate Virtual Payment Client Guide Rev 1.2.0, all inputs must be hashed, in ascending alphabetical order
	$transaction_data = array(
		'vpc_AccessCode'=>$access_code,
		'vpc_Amount'=>$amount_in_cents,
		'vpc_Command'=>'pay',
		'vpc_Locale'=>'en',
		'vpc_MerchTxnRef'=>$txn_id,
		'vpc_Merchant'=>$merchant_id,
		'vpc_OrderInfo'=>'VPC Example2',
		'vpc_ReturnURL'=>$return_url,
		//'Title'=>'PHP-VPC-3-Party',
		'vpc_Version'=>1,
	);

	//Sort the transaction values, they must be order alphabetically asc.
	//ksort($hash_data);
	
	//Build a string to hash from all the transaction data
	$values_to_hash = '';
	
	foreach( $transaction_data as $key => $value ) {
		if( strlen( $value ) > 0 ) {
			$values_to_hash .= $key . '=' . $value . '&';
		}
	}

	//Remove any traigning '&' from the string.
	$values_to_hash = rtrim( $values_to_hash, '&' );

	//Generate a SHA256 hash from the transaction data.
	$hash_string = strtoupper( hash_hmac( 'SHA256', $values_to_hash, pack( "H*",$secure_secret ) ) );

	//URL encode all of the transaction data values and key for use in the payment link.
	$url_encoded_hash_values=array();
	foreach($transaction_data as $field_name => $field_value){
		$url_encoded_hash_values[ urlencode( $field_name ) ] = urlencode( $field_value );
	}
	
	//Build the payment link, adding the hash_string value to the link.	
	$full_url = add_query_arg( array(
					'vpc_SecureHash'=>$hash_string,
	 				'vpc_SecureHashType'=>'SHA256' ), 
					add_query_arg($url_encoded_hash_values,$server_url));
	?>

		 <div id="anz-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
			<img class="off-site-payment-gateway-img" width="16" height="16" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL;?>/images/icons/external-link.png" alt="click to visit this payment gateway">
			<a href="<?php echo $full_url?>" class="payment-option-lnk allow-leave-page"><?php echo $submit_html?></a>
		</div>

	<?php
	//only redirect immediately if they didnt just return from ANZ
	//otherwise, we want them to see the error message
	if($bypass_payment_page && !array_key_exists('vpc_Message',$_GET)){
		
		 echo "<form>\n";
		 echo "<input type=\"hidden\" id=\"bypass_payment_page\" name=\"bypass_payment_page\" value=\"true\"/>\n";
		 echo "</form>\n";
			
		echo "<script>window.location = '$full_url';</script>";
	}



	return $payment_data;
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_anz');