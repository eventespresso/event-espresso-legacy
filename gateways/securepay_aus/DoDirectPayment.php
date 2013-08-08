<?php

function espresso_transactions_securepay_aus_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_securepay_aus($payment_data) {
	extract($payment_data);

	$securepay_aus_settings = get_option('event_espresso_securepay_aus_settings');

	$sandbox = $securepay_aus_settings['securepay_aus_use_sandbox'];
	if($sandbox){
		$payment_url = 'https://test.securepay.com.au/xmlapi/payment';
	}else{
		$payment_url = 'https://api.securepay.com.au/xmlapi/payment';
	}
$messageId= wp_generate_password(15,false);
$messageTimeStamp = date("YdmHis",strtotime(current_time('mysql'))).substr(microtime(false),-3,3).'000'.sprintf("%+04d",get_option('gmt_offset'));
$merchantID = $securepay_aus_settings['merchant_id'];
$merchant_password = $securepay_aus_settings['mechant_password'];
$currency = $securepay_aus_settings['currency_format'];
if($currency == 'JPY'){
	$amount = intval($payment_data['total_cost']) ; 
}else{
	$amount = intval( floatval($payment_data['total_cost']) * 100);
}
$cardType = $_POST['creditcardtype'];   //Visa
$cardNumber = $_POST['card_num'];
$cardExpiry = $_POST['expmonth'].'/'.$_POST['expyear'];
$cardCvv = $_POST['cvv'];
$purchaseOrderNum = $payment_data['registration_id'];
	$xml_string = "<?xml version='1.0' encoding='UTF-8'?>
<SecurePayMessage> 
<MessageInfo>
<messageID>$messageId</messageID>
<messageTimestamp>$messageTimeStamp</messageTimestamp> 
<timeoutValue>60</timeoutValue> 
<apiVersion>xml-4.2</apiVersion> 
</MessageInfo> 
<MerchantInfo> 
<merchantID>$merchantID</merchantID> 
<password>$merchant_password</password> 
</MerchantInfo> 
<RequestType>Payment</RequestType> 
<Payment> 
<TxnList count='1'> 
<Txn ID='1'> 
<txnType>0</txnType>
<txnSource>23</txnSource> 
<amount>$amount</amount> 
<currency>$currency</currency> 
<purchaseOrderNo>$purchaseOrderNum</purchaseOrderNo> 
<CreditCardInfo> 
<cardNumber>$cardNumber</cardNumber> 
<expiryDate>$cardExpiry</expiryDate> 
<cvv>$cardCvv</cvv>
</CreditCardInfo> 
</Txn> 
</TxnList> 
</Payment> 
</SecurePayMessage>";
	
	$results = wp_remote_post($payment_url,
			array(
				'headers'=>array('content-type'=>'text/xml'),
				'body'=>$xml_string,
				'redirection'=>5,
				'timeout'=>15,
				'sslverify'=>false
			));
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'securepay_aus';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($results);
	if( ! $results instanceof WP_Error && isset($results['body']) && $payment_response = simplexml_load_string ($results['body'])){
		$transaction_result = $payment_response->Payment->TxnList->Txn;
		//here is a var_dump of a typical $transaction_result
//		object(SimpleXMLElement)[214]
//  public '@attributes' => 
//    array (size=1)
//      'ID' => string '1' (length=1)
//  public 'txnType' => string '0' (length=1)
//  public 'txnSource' => string '23' (length=2)
//  public 'amount' => string '1005' (length=4)
//  public 'currency' => string 'AUD' (length=3)
//  public 'purchaseOrderNo' => string '20-5202c20692bd1' (length=16)
//  public 'approved' => string 'No' (length=2)
//  public 'responseCode' => string '101' (length=3)
//  public 'responseText' => string 'Invalid Credit Card Number' (length=26)
//  public 'thinlinkResponseCode' => string '300' (length=3)
//  public 'thinlinkResponseText' => string '000' (length=3)
//  public 'thinlinkEventStatusCode' => string '980' (length=3)
//  public 'thinlinkEventStatusText' => string 'Error - Bad Card Number' (length=23)
//  public 'settlementDate' => 
//    object(SimpleXMLElement)[215]
//  public 'txnID' => 
//    object(SimpleXMLElement)[218]
//  public 'CreditCardInfo' => 
//    object(SimpleXMLElement)[219]
//      public 'pan' => string '444433...111' (length=12)
//      public 'expiryDate' => 
//        object(SimpleXMLElement)[220]
//      public 'cardType' => string '6' (length=1)
//      public 'cardDescription' => string 'Visa' (length=4)
		if($transaction_result->approved == 'Yes'){
			//oh wonderful payment was approved
			$payment_data['payment_status'] = 'Completed';
		}else{
			$payment_data['paymnet_status'] = 'Payment Declined';
			echo '<p><strong class="credit_card_failure">Attention: Your transaction was declined for the following reason:</strong><br />';
			echo $transaction_result->responseText."</p>";
		}
	}elseif($results instanceof WP_Error ){
		echo '<p><strong class="credit_card_failure">'.__("Attention: There was an error communicating with SecurePay",'event_espresso').'</strong><br />';
		echo "<ul>";
		foreach($results->errors as $short_desc => $reasons){
			echo "<li>$short_desc:".implode(", ",$reasons)."</li>";
			
		}
		echo "</ul></p>";
	}else{
		echo '<p><strong class="credit_card_failure">'.__("Attention: an unknown error occurred. Results of request to SecurePay:",'event_espresso').'</strong><br />';
		var_dump($results);
	}
	
	
//// Setup SecurePay object
//	$SecurePayConfig = array('Sandbox' => $sandbox, 'APIUsername' => $securepay_aus_settings['securepay_aus_api_username'], 'APIPassword' => $securepay_aus_settings['securepay_aus_api_password'], 'APISignature' => $securepay_aus_settings['securepay_aus_api_signature']);
//	$SecurePay = new Espresso_SecurePay($SecurePayConfig);
//
//// Populate data arrays with order data.
//	$DPFields = array(
//			'paymentaction' => 'Sale', // How you want to obtain payment.  Authorization indidicates the payment is a basic auth subject to settlement with Auth & Capture.  Sale indicates that this is a final sale for which you are requesting payment.  Default is Sale.
//			'ipaddress' => $_SERVER['REMOTE_ADDR'], // Required.  IP address of the payer's browser.
//			'returnfmfdetails' => '1' // Flag to determine whether you want the results returned by FMF.  1 or 0.  Default is 0.
//	);
//
//	$CCDetails = array(
//			'creditcardtype' => $_POST['creditcardtype'], // Required. Type of credit card.  Visa, MasterCard, Discover, Amex, Maestro, Solo.  If Maestro or Solo, the currency code must be GBP.  In addition, either start date or issue number must be specified.
//			'acct' => $_POST['card_num'], // Required.  Credit card number.  No spaces or punctuation.
//			'expdate' => $_POST['expmonth'] . $_POST['expyear'], // Required.  Credit card expiration date.  Format is MMYYYY
//			'cvv2' => $_POST['cvv'], // Requirements determined by your SecurePay account settings.  Security digits for credit card.
//			'startdate' => '', // Month and year that Maestro or Solo card was issued.  MMYYYY
//			'issuenumber' => '' // Issue number of Maestro or Solo card.  Two numeric digits max.
//	);
//
//	$PayerInfo = array(
//			'email' => $_POST['email'], // Email address of payer.
//			'payerid' => '', // Unique SecurePay customer ID for payer.
//			'payerstatus' => '', // Status of payer.  Values are verified or unverified
//			'business' => '' // Payer's business name.
//	);
//
//	$PayerName = array(
//			'salutation' => '', // Payer's salutation.  20 char max.
//			'firstname' => $_POST['first_name'], // Payer's first name.  25 char max.
//			'middlename' => '', // Payer's middle name.  25 char max.
//			'lastname' => $_POST['last_name'], // Payer's last name.  25 char max.
//			'suffix' => '' // Payer's suffix.  12 char max.
//	);
//
//	$BillingAddress = array(
//			'street' => $_POST['address'], // Required.  First street address.
//			'street2' => '', // Second street address.
//			'city' => $_POST['city'], // Required.  Name of City.
//			'state' => $_POST['state'], // Required. Name of State or Province.
//			'countrycode' => 'US', // Required.  Country code.
//			'zip' => $_POST['zip'], // Required.  Postal code of payer.
//			'phonenum' => empty($_POST['phone']) ? '' : $_POST['phone'] // Phone Number of payer.  20 char max.
//	);
//
//	$ShippingAddress = array(
//			'shiptoname' => '', // Required if shipping is included.  Person's name associated with this address.  32 char max.
//			'shiptostreet' => '', // Required if shipping is included.  First street address.  100 char max.
//			'shiptostreet2' => '', // Second street address.  100 char max.
//			'shiptocity' => '', // Required if shipping is included.  Name of city.  40 char max.
//			'shiptostate' => '', // Required if shipping is included.  Name of state or province.  40 char max.
//			'shiptozip' => '', // Required if shipping is included.  Postal code of shipping address.  20 char max.
//			'shiptocountrycode' => '', // Required if shipping is included.  Country code of shipping address.  2 char max.
//			'shiptophonenum' => '' // Phone number for shipping address.  20 char max.
//	);
//
//	$PaymentDetails = array(
//			'amt' => $payment_data['total_cost'], // Required.  Total amount of order, including shipping, handling, and tax.
//			'currencycode' => $securepay_aus_settings['currency_format'], // Required.  Three-letter currency code.  Default is USD.
//			'itemamt' => '', // Required if you include itemized cart details. (L_AMTn, etc.)  Subtotal of items not including S&H, or tax.
//			'shippingamt' => '', // Total shipping costs for the order.  If you specify shippingamt, you must also specify itemamt.
//			'handlingamt' => '', // Total handling costs for the order.  If you specify handlingamt, you must also specify itemamt.
//			'taxamt' => '', // Required if you specify itemized cart tax details. Sum of tax for all items on the order.  Total sales tax.
//			'desc' => stripslashes_deep($event_name), // Description of the order the customer is purchasing.  127 char max.
//			'custom' => '', // Free-form field for your own use.  256 char max.
//			'invnum' => '', // Your own invoice or tracking number
//			'notifyurl' => '' // URL for receiving Instant Payment Notifications.  This overrides what your profile is set to use.
//	);
//
//	$OrderItems = array();
//	$Item = array(
//			'l_name' => stripslashes_deep($event_name), // Item Name.  127 char max.
//			'l_desc' => stripslashes_deep($event_name), // Item description.  127 char max.
//			'l_amt' => $_POST['amount'], // Cost of individual item.
//			'l_number' => '', // Item Number.  127 char max.
//			'l_qty' => '1', // Item quantity.  Must be any positive integer.
//			'l_taxamt' => '', // Item's sales tax amount.
//			'l_ebayitemnumber' => '', // eBay auction number of item.
//			'l_ebayitemauctiontxnid' => '', // eBay transaction ID of purchased item.
//			'l_ebayitemorderid' => '' // eBay order ID for the item.
//	);
//	array_push($OrderItems, $Item);
//
//
//// Wrap all data arrays into a single, "master" array which will be passed into the class function.
//	$SecurePayRequestData = array(
//			'DPFields' => $DPFields,
//			'CCDetails' => $CCDetails,
//			'PayerName' => $PayerName,
//			'BillingAddress' => $BillingAddress,
//			'PaymentDetails' => $PaymentDetails,
//			'OrderItems' => $OrderItems
//	);
//	$SecurePayResult = $SecurePay->DoDirectPayment($SecurePayRequestData);
//	$payment_data['payment_status'] = 'Incomplete';
//	$payment_data['txn_type'] = 'SecurePay Pro';
//	$payment_data['txn_id'] = 0;
//	$payment_data['txn_details'] = serialize($_REQUEST);
//	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
//	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
//	$Errors = GetErrors($SecurePayResult);
//	if (!empty($SecurePayResult)) {
//		unset($SecurePayResult['REQUESTDATA']['CREDITCARDTYPE']);
//		unset($SecurePayResult['REQUESTDATA']['ACCT']);
//		unset($SecurePayResult['REQUESTDATA']['EXPDATE']);
//		unset($SecurePayResult['REQUESTDATA']['CVV2']);
//		unset($SecurePayResult['RAWREQUEST']);
//		
//		$payment_data['txn_id'] = isset( $SecurePayResult['TRANSACTIONID'] ) ? $SecurePayResult['TRANSACTIONID'] : '';
//		$payment_data['txn_details'] = serialize($SecurePayResult);
//		if (!APICallSuccessful($SecurePayResult['ACK'])) {
//			DisplayErrors($Errors);
//		} else {
//			$payment_data['payment_status'] = 'Completed';
//		}
//	} else {
//		
//		
//		
//		
//		
//	}
//	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
