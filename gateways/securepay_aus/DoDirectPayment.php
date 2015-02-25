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
			));
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'securepay_aus';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($results);
	if( ! $results instanceof WP_Error && 
			isset($results['body']) && 
			$payment_response = simplexml_load_string ($results['body'])){
		
		if($payment_response->Status->statusCode == '000'){
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
			$payment_data['txn_id'] = $payment_data->purchaseOrderNo;
			if($transaction_result->approved == 'Yes'){
				//oh wonderful payment was approved
				$payment_data['payment_status'] = 'Completed';
				$payment_data['txn_id'] = $transaction_result->txnID;
			}else{
				$payment_data['payment_status'] = 'Payment Declined';
				echo '<p><strong class="credit_card_failure">'.__('Attention: Your transaction was declined for the following reason:','event_espresso').'</strong><br />';
				echo $transaction_result->responseText."</p>";
			}
		}else{
			$payment_data['payment_status'] = 'Payment Declined';
			echo '<p><strong class="credit_card_failure">'.__('Attention: There was a server error when communicating with Securepay','event_espresso').'</strong><br />';
			echo $payment_response->Status->statusDescription."</p>";
		}
	}elseif($results instanceof WP_Error ){
		echo '<p><strong class="credit_card_failure">'.__("Attention: There was an error communicating with SecurePay",'event_espresso').'</strong><br />';
		echo "<ul>";
		foreach($results->errors as $short_desc => $reasons){
			echo "<li>$short_desc:".implode(", ",$reasons)."</li>";
			
		}
		echo "</ul></p>";
	}else{
		echo '<p><strong class="credit_card_failure">'.__("Attention: an unknown error occurred. Results of request to SecurePay:",'event_espresso').'</strong></p><br />';
		var_dump($results);
	}
	return $payment_data;
}
