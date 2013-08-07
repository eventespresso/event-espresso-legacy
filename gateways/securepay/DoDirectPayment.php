<?php

function espresso_transactions_securepay_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_securepay($payment_data) {
	extract($payment_data);

	$securepay_settings = get_option('event_espresso_securepay_settings');

	$sandbox = $securepay_settings['securepay_use_sandbox'];
	if($sandbox){
		$payment_url = 'https://test.securepay.com.au/xmlapi/payment';
	}else{
		$payment_url = 'https://api.securepay.com.au/xmlapi/payment';
	}
		
	echo 'echodump of $payment_data';
	var_dump($payment_data);
$messageId= wp_generate_password(15,false);
//$messageTimeStamp = date("YdmHs").substrmicrotime(false)
$merchantID = $securepay_settings['merchant_id'];
$merchant_password = $securepay_settings['mechant_password'];
$orderRef = "TestSoapOrder";
$amount = '1000000';
$currency = $securepay_settings['currency_format'];
$cardType = $_POST['creditcardtype'];   //Visa
$cardNumber = $_POST['card_num'];
$cardExpiry = $_POST['expmonth'].'/'.$_POST['expyear'];
$cardHolder = $_POST['first_name']." ".$_POST['last_name'];
$purchaseOrderNum = $payment_data['registration_id'];
	$xml_string = "<?xml version='1.0' encoding='UTF-8'?>
<SecurePayMessage> 
<MessageInfo>
<messageID>$messageId</messageID>
<messageTimestamp>20042303111214383000+660</messageTimestamp> 
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
</CreditCardInfo> 
</Txn> 
</TxnList> 
</Payment> 
</SecurePayMessage>";
try {
	
} catch (SoapFault $f) {
	echo $f->getMessage();
	echo "<br />";
	echo $f->faultcode;
	echo "<br />";
	echo $f->faultstring;
	echo "<br />";
	print_r($f->getTrace());
}

echo "resultCode: " . $result->resultCode . "<br />";
echo "merchTxnRef: " . $result->merchTxnRef . "<br />";
echo "transactionNo: " . $result->transactionNo . "<br />";
echo "receiptNo:  " . $result->receiptNo . "<br />";
echo "authorizationID: " . $result->authorizationID . "<br />";
echo "batchNo: " . $result->batchNo . "<br />";
echo "failReason: " . $result->failReason . "<br />";
echo "<br />";
	
//// Setup SecurePay object
//	$SecurePayConfig = array('Sandbox' => $sandbox, 'APIUsername' => $securepay_settings['securepay_api_username'], 'APIPassword' => $securepay_settings['securepay_api_password'], 'APISignature' => $securepay_settings['securepay_api_signature']);
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
//			'currencycode' => $securepay_settings['currency_format'], // Required.  Three-letter currency code.  Default is USD.
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
//		?>
		<p>//<?php _e('There was no response from SecurePay.', 'event_espresso'); ?></p>
		//<?php
//	}
//	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
//	return $payment_data;
}

function APICallSuccessful($ack) {
	if (strtoupper($ack) != 'SUCCESS' && strtoupper($ack) != 'SUCCESSWITHWARNING' && strtoupper($ack) != 'PARTIALSUCCESS')
		return false;
	else
		return true;
}

function GetErrors($DataArray) {

	$Errors = array();
	$n = 0;
	while (isset($DataArray['L_ERRORCODE' . $n . ''])) {
		$LErrorCode = isset($DataArray['L_ERRORCODE' . $n . '']) ? $DataArray['L_ERRORCODE' . $n . ''] : '';
		$LShortMessage = isset($DataArray['L_SHORTMESSAGE' . $n . '']) ? $DataArray['L_SHORTMESSAGE' . $n . ''] : '';
		$LLongMessage = isset($DataArray['L_LONGMESSAGE' . $n . '']) ? $DataArray['L_LONGMESSAGE' . $n . ''] : '';
		$LSeverityCode = isset($DataArray['L_SEVERITYCODE' . $n . '']) ? $DataArray['L_SEVERITYCODE' . $n . ''] : '';

		$CurrentItem = array(
				'L_ERRORCODE' => $LErrorCode,
				'L_SHORTMESSAGE' => $LShortMessage,
				'L_LONGMESSAGE' => $LLongMessage,
				'L_SEVERITYCODE' => $LSeverityCode
		);

		array_push($Errors, $CurrentItem);
		$n++;
	}

	return $Errors;
}

function DisplayErrors($Errors) {
	echo '<p><strong class="credit_card_failure">Attention: Your transaction was declined for the following reason(s):</strong><br />';
	foreach ($Errors as $ErrorVar => $ErrorVal) {
		$CurrentError = $Errors[$ErrorVar];
		foreach ($CurrentError as $CurrentErrorVar => $CurrentErrorVal) {
			if ($CurrentErrorVar == 'L_ERRORCODE')
				$CurrentVarName = 'Error Code';
			elseif ($CurrentErrorVar == 'L_SHORTMESSAGE')
				$CurrentVarName = 'Short Message';
			elseif ($CurrentErrorVar == 'L_LONGMESSAGE')
				$CurrentVarName == 'Long Message';
			elseif ($CurrentErrorVar == 'L_SEVERITYCODE')
				$CurrentVarName = 'Severity Code';

			echo $CurrentVarName . ': ' . $CurrentErrorVal . '<br />';
		}
		echo '<br />';
	}
}
