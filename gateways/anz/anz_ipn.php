<?php

function espresso_transactions_anz_get_attendee_id($attendee_id) {
	global $wpdb;
	if (!empty($_REQUEST['r_id'])) {
		$reg_id = $_REQUEST['r_id'];
		$attendee_id=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}events_attendee WHERE registration_id=%s LIMIT 1",$reg_id));
	}
	return $attendee_id;
}

function espresso_process_anz($payment_data) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	
	//on all requests, GET parameters present should be:
	//r_id which is the attendee's registration_id. we shouldn't use this thuogh, as we already ahve the registration id in $payment_data['registration_id']
	//type which should be set to anz. we should have already checked that this is set to 'anz'
	if(!array_key_exists('type',$_GET) || 'anz'!=$_GET['type']){
		return $payment_data;
	}
	
	$anz_settings = get_option('event_espresso_anz_settings');
	$secure_secret=$anz_settings['anz_secure_secret'];
	
	
	
	
	
	
	
	
	$vpc_Txn_Secure_Hash = $_GET["vpc_SecureHash"];
	unset($_GET["vpc_SecureHash"]); 

	// set a flag to indicate if hash has been validated
	$errorExists = false;

	if (strlen($secure_secret) > 0 && $_GET["vpc_TxnResponseCode"] != "7" && $_GET["vpc_TxnResponseCode"] != "No Value Returned") {
		$md5HashData = $secure_secret;
		echo "GESTS:".implode(",", array_keys($_GET))."<br><br>";
		// sort all the incoming vpc response fields and leave out any with no value
		foreach($_GET as $key => $value) {
			if ( ! in_array( $key, array_keys( array("vpc_SecureHash", 'page_id', 'r_id', 'type')))  or strlen($value) > 0) {
				$md5HashData .= $value;
			}
		}
		echo $md5HashData;
		// Validate the Secure Hash (remember MD5 hashes are not case sensitive)
	   // This is just one way of displaying the result of checking the hash.
	   // In production, you would work out your own way of presenting the result.
	   // The hash check is all about detecting if the data has changed in transit.
	   if (strtoupper($vpc_Txn_Secure_Hash) == strtoupper(md5($md5HashData))) {
		   // Secure Hash validation succeeded, add a data field to be displayed
		   // later.
		   $hashValidated = "<FONT color='#00AA00'><strong>CORRECT</strong></FONT>";
	   } else {
		   // Secure Hash validation failed, add a data field to be displayed
		   // later.
		   $hashValidated = "<FONT color='#FF0066'><strong>INVALID HASH</strong></FONT>";
		   $errorExists = true;
	   }
   } else {
	   // Secure Hash was not validated, add a data field to be displayed later.
	   $hashValidated = "<FONT color='orange'><strong>Not Calculated - No 'SECURE_SECRET' present.</strong></FONT>";
   }
	echo $hashValidated;
	die;
	
	
	
	
	
	
	
	
	//order_id which is a string which is totally unique for every request sent to anz
	$order_id= $_GET['order_id'];
	//success which is set to eitehr 1 or 0, depending on whether the payment was successful or not
	$success = $_GET['success'];
	//to teh failure url, we expect to receive
	//LKPRC primary error code
	//LKSRC secondary error code
	//LKMSGTXT basic error message text
	$anz_settings = get_option('event_espresso_anz_settings');
	

	$payment_data['txn_type'] = 'anz';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	//http://monkey.com/?TransTime=Thu%20Feb%2021%2013:48:45%20EST%202013&OrderID=2013022113484500514&TransactionType=SALE&Approved=APPROVED&ReturnCode=Y:123456:0abcdef:::NYY&ErrMsg=&TaxTotal=15.00&ShipTotal=6.00&SubTotal=1305.00&FullTotal=1326.00&PaymentType=CC&CardNumber=......1111&TransRefNumber=1befb0f25f3fee22&CardIDResult=&AVSResult=&CardAuthNumber=123456&CardRefNumber=0abcdef&CardType=VISA&IPResult=NYY&IPCountry=CA&IPRegion=Ontario&IPCity=Toronto&CustomerRefNo=123456789
	//check that 'Err' is empty
	
	if(!$success){
		$display_text='2900'!=$_GET['LKSRC'] ? $_GET['LKMSGTXT'] : __('The card number, validity, or card verification code is invalid. This may also appear when double-authorization has occurred.','event_espresso');?>
		
<h2><?php _e('Payment Declined','event_espresso')?></h2>
		<p><strong class="credit_card_failure"><?php echo $display_text?></strong></p>
		<p><?php echo espresso_anz_detailed_error_message($_GET['LKPRC'],$_GET['LKSRC']);//already internationalized?>
		<p><strong class="credit_card_failure"><?php _e('Please try again','event_espresso')?></strong></p>
		<p><?php printf(__("Order ID: %s, Primary Error code: %s, Secondary Error code: %s",'event_espresso'), $order_id, $_GET['LKPRC'],$_GET['LKSRC'])?></p>
		<p><?php _e("If this error persists, you may want to contact the site owners and provide them with the above data.",'event_espresso');?></p>
		<?php
		
	//if the request says it was successful, check the mac calculations (if teh settings indicate we should)
	}elseif($success && 'Y' == $anz_settings['anz_uses_mac_key']){
		//$locally_calculated_mac_string = generate_mac_string($payment_data);
		if( array_key_exists('LKMAC',$_GET) && $locally_calculated_mac_string == $_GET['LKMAC'] ){
			$payment_data['txn_id']=$order_id;
			$payment_data['payment_status']='Completed';
		}else{
			if(!array_key_exists('LKMAC',$_GET)){
				$mac_error_message = __('The site admin seems to have malconfigured their anz MAC Security Check Settings. They are not sending the \'MAC check\' the Success URL','event_espresso');
			}else{
				$mac_error_message = sprintf(__('The MAC Security code sent from anz (%s) does not match the one in Event Espresso (%s)','event_espresso'),$_GET['LKMAC'],$locally_calculated_mac_string);
			}
			?>
				<h2><?php _e('Payment Declined','event_espresso')?></h2>
			<p><strong class="credit_card_failure"><?php echo $mac_error_message?></strong></p>
		<?php }
	}else{
		
		$payment_data['txn_id']=$order_id;
		$payment_data['payment_status']='Completed';
	}
	return $payment_data;
}