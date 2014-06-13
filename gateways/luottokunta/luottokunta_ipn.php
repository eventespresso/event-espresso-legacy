<?php

function espresso_transactions_luottokunta_get_attendee_id($attendee_id) {
	global $wpdb;
	if (!empty($_REQUEST['r_id'])) {
		$reg_id = $_REQUEST['r_id'];
		$attendee_id=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}events_attendee WHERE registration_id=%s LIMIT 1",$reg_id));
	}
	return $attendee_id;
}

function espresso_process_luottokunta($payment_data) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	
	//on all requests, GET parameters present should be:
	//r_id which is the attendee's registration_id. we shouldn't use this thuogh, as we already ahve the registration id in $payment_data['registration_id']
	//type which should be set to luottokunta. we should have already checked that this is set to 'luottokunta'
	if(!array_key_exists('type',$_GET) || 'luottokunta'!=$_GET['type']){
		return $payment_data;
	}
	
	
	//order_id which is a string which is totally unique for every request sent to luottokunta
	$order_id= $_GET['order_id'];
	//success which is set to either 1 or 0, depending on whether the payment was successful or not
	$success = $_GET['success'];
	//to the failure url, we expect to receive
	//LKPRC primary error code
	//LKSRC secondary error code
	//LKMSGTXT basic error message text
	$luottokunta_settings = get_option('event_espresso_luottokunta_settings');
	

	$payment_data['txn_type'] = 'Luottokunta';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	//http://monkey.com/?TransTime=Thu%20Feb%2021%2013:48:45%20EST%202013&OrderID=2013022113484500514&TransactionType=SALE&Approved=APPROVED&ReturnCode=Y:123456:0abcdef:::NYY&ErrMsg=&TaxTotal=15.00&ShipTotal=6.00&SubTotal=1305.00&FullTotal=1326.00&PaymentType=CC&CardNumber=......1111&TransRefNumber=1befb0f25f3fee22&CardIDResult=&AVSResult=&CardAuthNumber=123456&CardRefNumber=0abcdef&CardType=VISA&IPResult=NYY&IPCountry=CA&IPRegion=Ontario&IPCity=Toronto&CustomerRefNo=123456789
	//check that 'Err' is empty
	
	if(!$success){
		$display_text='2900'!=$_GET['LKSRC'] ? $_GET['LKMSGTXT'] : __('The card number, validity, or card verification code is invalid. This may also appear when double-authorization has occurred.','event_espresso');?>
		
<h2><?php _e('Payment Declined','event_espresso')?></h2>
		<p><strong class="credit_card_failure"><?php echo $display_text?></strong></p>
		<p><?php echo espresso_luottokunta_detailed_error_message($_GET['LKPRC'],$_GET['LKSRC']);//already internationalized?>
		<p><strong class="credit_card_failure"><?php _e('Please try again','event_espresso')?></strong></p>
		<p><?php printf(__("Order ID: %s, Primary Error code: %s, Secondary Error code: %s",'event_espresso'), $order_id, $_GET['LKPRC'],$_GET['LKSRC'])?></p>
		<p><?php _e("If this error persists, you may want to contact the site owners and provide them with the above data.",'event_espresso');?></p>
		<?php
		
	//if the request says it was successful, check the mac calculations (if the settings indicate we should)
	}elseif($success && 'Y' == $luottokunta_settings['luottokunta_uses_mac_key']){
		$locally_calculated_mac_string = generate_mac_string($payment_data);
		if( array_key_exists('LKMAC',$_GET) && $locally_calculated_mac_string == $_GET['LKMAC'] ){
			$payment_data['txn_id']=$order_id;
			$payment_data['payment_status']='Completed';
		}else{
			if(!array_key_exists('LKMAC',$_GET)){
				$mac_error_message = __('The site admin seems to have malconfigured their Luottokunta MAC Security Check Settings. They are not sending the \'MAC check\' the Success URL','event_espresso');
			}else{
				$mac_error_message = sprintf(__('The MAC Security code sent from Luottokunta (%s) does not match the one in Event Espresso (%s)','event_espresso'),$_GET['LKMAC'],$locally_calculated_mac_string);
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

/**
 * Calculates the MAC security string to verify that this request has not been altered
 * @param array $payment_data 
 * @return string
 */
function generate_mac_string($payment_data){
	$luottokunta_settings = get_option('event_espresso_luottokunta_settings');
	
	$mac_parts['mac_key'] = $luottokunta_settings['luottokunta_mac_key'];// 'KTLDJ546GDS';
	
	$mac_parts['transaction_type'] =  "0";//constant
	
	$mac_parts['currency_code'] =  "978";//constant
	
	$payment_data = espresso_get_total_cost($payment_data);
	//var_dump($payment_data);
	$mac_parts['amount'] =  $payment_data['total_cost'] * 100;//12345;//get from using the $payment_data, and passing it to espresso_get_total_cost(), and then multiplying $payment_data['total_cost'] by 100
	
	$mac_parts['order_id'] =  $_GET['order_id'];//'987654321';
	
	$mac_parts['merchant_number'] =  $luottokunta_settings['luottokunta_id'];//'7778883';
	//the instructions said to use these parameters from the get string, but they aren't being provided,
	//so I made a guess to just remove them, and it worked! yipee!
	//$mac_parts['country_code'] =  $_GET['LKBINCOUNTRY'];//'FI';//
	
	//$mac_parts['ip_country_code'] =  $_GET['LKIPCOUNTRY'];//'SE';//
	
	//$mac_parts['lkeci'] = $_GET['LKECI'];// '05';//$_GET, no idea what this is. the 'html ofrm interface v1.3 just says to add it)
	
	$mac_string = implode("&",$mac_parts);
	//echo "mac string :<br>$mac_string";
	$hashed_mac_string = hash('sha256',$mac_string);
	return $hashed_mac_string;
}


/**
 * Error messages from Luottokuta's 'return codes' pdf, available after logging into you luottokunta ccount, in the 'instructions' section
 * @param string $primary_error_code
 * @param string $secondary_error_code
 * @return string error message. Internationalized
 */
function espresso_luottokunta_detailed_error_message($primary_error_code,$secondary_error_code){

if($primary_error_code == '100' && $secondary_error_code == '101'){ $msg = __("Amount is invalid
The amount field or its content is formally incorrect or missing.
Please check that the amount contains only numbers.
In the HTML form interface and XML interface the amount is presented in cents without separators.
Also check that the amount field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '102'){ $msg = __("
Also check that the currency field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '103'){ $msg = __("Transmission method is invalid
The transmission method in question is not available for the merchant.
Please check that you are not using a transmission method (telephone, fax, mail, encrypted SSL, encrypted e-mail) that you are not allowed to use.
If necessary, please contact Luottokunta.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '104'){ $msg = __("Card number is invalid
The card number field or its content is formally incorrect or missing.
Please check that the card number contains only numbers. In the HTML payment form the card number can be entered with or without spaces. In the XML interface the card number should be entered without spaces.
Also check that the card number field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '105'){ $msg = __("
Also check that the card verification code field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '106'){ $msg = __("
Also check that the card validity field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '107'){ $msg = __("Order description is invalid
The order description field or its content is formally incorrect.
Please check that the order description does not exceed its maximum length. The maximum length of order description field is 3999 characters.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '110'){ $msg = __("MAC is invalid
HTML form interface's field 'Authentication_Mac' or its content is formally incorrect or missing.
Please check that parameters and the secret key provided by Luottokunta have been entered correctly and used in the correct order in the MAC calculation.
Also check that the Authentication_Mac field is not missing from the implementation.",'event_espresso');
}


elseif($primary_error_code == '100' && $secondary_error_code == '116'){ $msg = __("Merchant id is invalid
The merchant id field or its content is formally incorrect or missing.
Please check that the merchant id contains only numbers.
Also check that the merchant id field is not missing from the implementation.",'event_espresso');
}


elseif($primary_error_code == '100' && $secondary_error_code == '122'){ $msg = __("Language is invalid
HTML form interface's field 'Language' or its content is formally incorrect.
Please check that the language field contains one of the following values: EN, FI or SE.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '159'){ $msg = __("
Also check that the Card_Details_Transmit field is not missing from the implementation.",'event_espresso');
}


elseif($primary_error_code == '100' && $secondary_error_code == '160'){ $msg = __("
Also check that the Device_Category field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '161'){ $msg = __("
The order id distinguishes a transaction, and therefore each order id can be used only once.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '162'){ $msg = __("
Also check that the Success_Url field is not missing from the implementation.",'event_espresso');
}


elseif($primary_error_code == '100' && $secondary_error_code == '163'){ $msg = __("
Also check that the Failure_Url field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '164'){ $msg = __("
Also check that the Transaction_Type field is not missing from the implementation.",'event_espresso');
}


elseif($primary_error_code == '100' && $secondary_error_code == '165'){ $msg = __("
Also check that the Cancel_Url field is not missing from the implementation.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '168'){ $msg = __("Customer id is invalid
The customer id field or its content is formally incorrect.
Please check that the customer id contains only numbers and/or letters (a–z and/or A–Z) and is no longer than 64 characters.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '169'){ $msg = __("Dossier id is invalid
The dossier id field or its content is formally incorrect.
Please check that the dossier id contains only numbers and/or letters (a–z and/or A–Z) and is no longer than 64 characters.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '175'){ $msg = __("Customer IP is invalid
The IP address of the buyer’s browser connection or the anonymous proxy server is formally incorrect.
Please check that the IP address is accordant with IP protocol version 4 (IPv4). IP address is a 32-bit number, written as four eight-bit numbers, separated by dots.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '193'){ $msg = __("Card is not supported by the merchant
American Express or Diners Club card has not been added for the merchant and that is why buyers cannot pay with American Express or Diners Club cards in the distance selling.
Please contact Luottokunta and/or American Express or Diners Club if you wish to accept those cards in the distance selling.",'event_espresso');
}

		
elseif($primary_error_code == '100' && $secondary_error_code == '194'){ $msg = __("Invalid xml request
The XML request sent by the merchant is incorrect or the element used in the XML request contains formally incorrect information.
Please check that the XML request is made according to the XML schema.",'event_espresso');
}

		
elseif($primary_error_code == '300' && $secondary_error_code == '301'){ $msg = __("Order id already exists
The order id in question is already used by the merchant.
Please check that the order id is not the same as the order id you have used earlier.
Order id distinguishes a transaction, and therefore each order id can be used only once. The same order id cannot be used even if it has been deleted in the
web interface or through the XML interface.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '303'){ $msg = __("Order does not exist
The transaction is not found from Luottokunta ePayment Service with the order id in question.
Please check that you are not trying to debit or credit the transaction that has not been authorised yet, and therefore does not exist in Luottokunta ePayment Service.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '304'){ $msg = __("Amount too big
The amount is too big for the aimed function.
Please check that the amount is not too big for the function in question (authorisation, debit or credit). E.g. the amount to be credited cannot be bigger than the debited amount.
Maximum amount that can be authorised in Luottokunta ePayment Service at once is 50 000 Euros.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '305'){ $msg = __("Order state is invalid
The state of the transaction is incorrect for the aimed function.
Please check that you are not e.g. trying to credit an authorised transaction or a transaction that is still being processed.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '307'){ $msg = __("Order still in open batch
The transaction is still in the open batch.
Please check that the debit or credit transaction is not in the open batch. Batch is closed when the day changes.
Debit and credit transactions cannot be deleted until the batch is closed.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '311'){ $msg = __("Order authorisation too old
The authorisation has already expired.
Please check that the initial authorisation has not already expired.
An authorisation expires after 25 days from the initial authorisation date, after which it cannot be debited anymore.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '312'){ $msg = __("Amount overdraft too big
The amount in question is too big for the initial amount.
Please check that the amount is not too big for the aimed function. E.g. the amount to be debited cannot be more than 15% bigger than the initially authorised amount.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '316'){ $msg = __("Interface is not supported by the merchant
The interface in question is not available for the merchant.
Please check that the interface chosen in the contract is the same as the interface you are using for authorising a transaction.
If you wish to use another interface, please contact Luottokunta.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '317'){ $msg = __("Merchant is closed
The merchant id in question has been closed and merchant’s access to Luottokunta ePayment Service has been restricted.
Please contact Luottokunta.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '318'){ $msg = __("Merchant is passive
The merchant id in question is passivated and merchant’s access to Luottokunta ePayment Service has been restricted.
Please contact Luottokunta.",'event_espresso');
}

elseif($primary_error_code == '300' && $secondary_error_code == '319'){ $msg = __("Channel is not supported by the merchant
Payment channel mentioned in the HTML form interface's field 'Device_Category' is not available for the merchant.
Please check that the payment channel (internet, mobile device or digital-TV) chosen in the contract is the same as the channel you are using for authorising a transaction.
If you wish to use another payment channel, please contact Luottokunta.",'event_espresso');
}
elseif($primary_error_code == '300' && $secondary_error_code == '321'){ $msg = __("The 3DS service is not supported by the merchant
Verified by Visa and/or MasterCard SecureCode authentication services have not been added for the merchant.
Please contact Luottokunta.",'event_espresso');
}
elseif($primary_error_code == '300' && $secondary_error_code == '322'){ $msg = __("The 3DS is not supported by Amex
American Express card does not support Verified by Visa and MasterCard SecureCode authentication services.
Please check that you have not implemented the XML interface in a way that it is trying to execute Verified by Visa or MasterCard SecureCode authentication for American Express card.
",'event_espresso');
}
elseif($primary_error_code == '300' && $secondary_error_code == '323'){ $msg = __("3DS is not supported by Diners
Diners Club card does not support Verified by Visa and MasterCard SecureCode authentication services.
Please check that you have not implemented the XML interface in a way that it is trying to execute Verified by Visa or MasterCard SecureCode authentication for Diners Club card.
",'event_espresso');
}
elseif($primary_error_code == '1300' && $secondary_error_code == '1302'){ $msg = __("Bad credentials
The merchant id and/or XML password used in the XML interface is incorrect.
Please check that you have entered the correct merchant id in the XML interface's field <merchantNumber>.
Also check that you have entered the correct XML password in the XML interface's field <merchantPassword>.",'event_espresso');
}

elseif($primary_error_code == '1400' && $secondary_error_code == '1403'){ $msg = __("Reversal message is pending
Luottokunta ePayment Service is automatically reversing an authorisation that has not been completely processed or finished yet due to a temporary service break.
These error codes are only informative information and do not require any actions from the merchant.",'event_espresso');
}

elseif($primary_error_code == '1400' && $secondary_error_code == '1404'){ $msg = __("Failed to send reversal message
Luottokunta ePayment Service did not succeed to reverse an authorisation that was not completely processed or finished due to a temporary service break.
Please check that the authorisation with the order id in question doesn’t exist in Luottokunta ePayment Service.
If necessary, please contact Luottokunta.",'event_espresso');
}

elseif($primary_error_code == '1900' && $secondary_error_code == '1901'){ $msg = __("DMP internal error
If you already have a functioning implementation, these error codes most likely refer to a temporary service break in Luottokunta ePayment Service or its back-end systems.
If you already have a functioning implementation, you can advice the cardholder to retry the payment after a while. It is possible that retry will not be successful.
In case of a longer service break, merchants will receive a notification from Luottokunta via e-mail.
If you are just testing the payment interface, these error codes may also refer to an error in the interface implementation.
If you are only testing the implementation, please check that the implementation has been made according to interface descriptions.
In the HTML form interface the URL sent with GET method has to be encoded.
If necessary, please contact Luottokunta.",'event_espresso');
}

elseif($primary_error_code == '2000' && $secondary_error_code == '2100'){ $msg = __("The card issuer declined the authorisation
The card issuer has rejected the authorisation.
The reasons for this can be e.g. that the card limit has been exceeded, or the card is restricted so that it cannot be used in distance selling.
You can advice the cardholder to contact his/her card issuer for more detailed information.",'event_espresso');
}

elseif($primary_error_code == '2000' && $secondary_error_code == '2190'){ $msg = __("
Due to risk management related reasons Luottokunta ePayment Service does not accept this kind of transaction, but immediately sends the authorisation reversal to the card issuer.
Do not retry the transaction, as it will most likely result in the same outcome. The payment card cannot be used to pay through Luottokunta ePayment Service.
The time that authorisation reversal requires varies and is dependent on the card issuer. In case the payment card still has a money reservation, you can advice the cardholder to contact his/her card issuer for more detailed information.",'event_espresso');
}

elseif($primary_error_code == '2000' && $secondary_error_code == '2200'){ $msg = __("Card closed or suspicion of fraud
The payment card in question is closed or there is a suspicion of card misuse.
Do not retry the transaction.
Observe payments for new attempts. If necessary, please contact Luottokunta.",'event_espresso');
}

elseif($primary_error_code == '2000' && $secondary_error_code == '2900'){ $msg = __("System error
The card number, validity or card verification code is invalid.
This error might also appear e.g. when double authorisation has occurred.
You can advice the cardholder to retry the payment after a while. It is possible that retry will not be successful.",'event_espresso');
}
elseif($primary_error_code == '3000' && $secondary_error_code == '3002'){ $msg = __("The 3DS authentication failed
Verified by Visa or MasterCard SecureCode authentication failed. The reasons can be e.g. that the cardholder has given incorrect authentication password or cancelled the authentication by clicking the Cancel button in the authentication page.
You can advice the cardholder to retry the payment after a while. It is possible that retry will not be successful.",'event_espresso');
}
elseif($primary_error_code == '3000' && $secondary_error_code == '3003'){ $msg = __("The 3DS no connection to directory
Verified by Visa or MasterCard SecureCode authentication failed due to data communication error.
You can advice the cardholder to retry the payment after a while. It is possible that retry will not be successful.",'event_espresso');
}else{
	$msg = sprintf(__("No detailed error message for primary error code %s and secondary error code %d",'event_espresso'),$primary_error_code,$secondary_error_code);
}
return $msg;
}