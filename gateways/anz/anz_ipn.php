<?php

function espresso_transactions_anz_get_attendee_id($attendee_id) {
	global $wpdb;
	if (!empty($_REQUEST['r_id'])) {
		$reg_id = $_REQUEST['r_id'];
		$attendee_id=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}events_attendee WHERE registration_id=%s LIMIT 1",$reg_id));
	}
	return $attendee_id;
}

/**
 * Helper function for remove $_GET parameters for formulating the hash key
 * @param string $key like 'page_id'
 * @param array $array like $_GEt
 * @return array $array with key matching $key removed from it
 */
function espresso_anz_remove_from_array($key,$array){
	if(array_key_exists($key,$array)){
		unset($array[$key]);
	}
	return $array;
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
		
	$hash_inputs = $_GET;
	$hash_inputs = espresso_anz_remove_from_array('vpc_SecureHash',$hash_inputs);
	$hash_inputs = espresso_anz_remove_from_array('vpc_SecureHashType',$hash_inputs);
	$hash_inputs = espresso_anz_remove_from_array('page_id',$hash_inputs);
	$hash_inputs = espresso_anz_remove_from_array('type',$hash_inputs);
	$hash_inputs = espresso_anz_remove_from_array('r_id', $hash_inputs);
	// set a flag to indicate if hash has been validated
	$errorExists = false;
	
	//copied almost verbatim from ANZ php sample code
	if (strlen($secure_secret) > 0 && $hash_inputs["vpc_TxnResponseCode"] != "7" && $hash_inputs["vpc_TxnResponseCode"] != "No Value Returned") {
		
		//Build a string to hash from all of the values passed with the transaction.
		$values_to_hash = '';
		
		foreach( $hash_inputs as $key => $value ) {
			if ( ! in_array( $key, array_keys( array("vpc_SecureHash", "vpc_SecureHashType", 'page_id', 'r_id', 'type')))  or strlen($value) > 0 ) {
				$values_to_hash .= $key . '=' . $value . '&';
			}
		}

		//Remove any traigning '&' from the string.
		$values_to_hash = rtrim( $values_to_hash, '&' );

		//Generate the SHA256 hash from the transaction values using the 'secure_secret' value as the key.
		$hash = strtoupper( hash_hmac( 'SHA256', $values_to_hash, pack("H*", $secure_secret ) ) );

		// Validate the Secure Hash provided within the transaction matches the hash from above.
	   	// The hash check is all about detecting if the data has changed in transit.
	   	if (strtoupper($_GET["vpc_SecureHash"]) == $hash ) {
		   // Secure Hash validation succeeded, add a data field to be displayed
		   // later.
		   $authenticated_response = true;
		   $hashValidated = "<FONT color='#00AA00'><strong>CORRECT</strong></FONT>";
	    } else {
		   // Secure Hash validation failed, add a data field to be displayed
		   // later.
		   $authenticated_response = false;
		   $hashValidated = "<FONT color='#FF0066'><strong>INVALID HASH</strong></FONT>";
		   $errorExists = true;
	    }
   } else {
	   // Secure Hash was not validated, add a data field to be displayed later.
	   $authenticated_response = false;
	   $hashValidated = "<FONT color='orange'><strong>Not Calculated - No 'SECURE_SECRET' present.</strong></FONT>";
   }
   
   if($authenticated_response){
		$payment_data['txn_type'] = 'anz';
		$payment_data['payment_status'] = 'Incomplete';
		$payment_data['txn_details'] = serialize($_REQUEST);
		$payment_data['txn_id'] = $_GET['vpc_MerchTxnRef'];
	   if($_GET['vpc_TxnResponseCode'] == '0' && $authenticated_response){
			$payment_data['payment_status'] = 'Completed';
	   }else{
		   //error or rejection of payment within ANZ
		   ?>
			<h2><?php _e('Payment Declined','event_espresso')?></h2>
			<strong class="credit_card_failure"><?php _e('Response from ANZ:','event_espresso');?><?php echo $_GET['vpc_Message']?></strong>
			<p><?php _e('Please try your payment again.','event_espresso');?></p>
			<?php
	   }
   }else{
	   //the HASH didn't pass... they may have forged the request!
	   $anz_response = array_key_exists('vpc_Message',$_GET)?$_GET['vpc_Message']:$hashValidated;
	   ?>
		<h2><?php _e('Payment Error Occured','event_espresso')?></h2>
	    <?php echo $anz_response;?>
	   <?php 
   }
	return $payment_data;
}