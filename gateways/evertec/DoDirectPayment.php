<?php

function espresso_transactions_evertec_get_attendee_id($attendee_id) {
	global $wpdb;
	if (empty($attendee_id)) {
		$attendee_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".EVENTS_ATTENDEE_TABLE." where registration_id = %s",$_REQUEST['r_id']));
	}
	return $attendee_id;
}

function espresso_process_evertec($payment_data) {
	$r=$_REQUEST;
	$evertec_settings = get_option('event_espresso_evertec_settings');
	//prepare request
	if($evertec_settings['use_sandbox']){
		$server_url = 'https://everpaycert.evertecinc.com/cpsh2h/serviceh2h.asmx';//'https://everpaycert.evertecinc.com/wscheckoutpayment/wsCheckoutPayment.asmx?op=MakePayment';
	}else{
		$server_url = 'https://mmpay.evertecinc.com/cpsh2h/serviceh2h.asmx';  // 'https://mmpay.evertecinc.com/webservicev2/wscheckoutpayment.asmx';
	}
	$params = array(
		'userName'=>$evertec_settings['username'],
		'passWord'=>$evertec_settings['password'],
		'customerName'=>$r['first_name']." ".$r['last_name'],
		'customerID'=>$payment_data['registration_id'],
		'customerEmail'=>$r['email'],//$payment_data['attendee_email'],
		'address1'=>$r['address'],
		'address2'=>isset($r['address2']) ? $r['address2'] : '',
		'city'=>$r['city'],
		'state'=>$r['state'],
		'zipCode'=>$r['zip'],
		'telephone'=>$r['phone'],
		'fax'=>'',
		'descriptionBuy'=>$payment_data['event_name'],
		'operatorId'=>'',
		'channel'=>6,//not sure what this is for
		'ignoreValues'=>'',  
		'language'=>$evertec_settings['evertec_pages_language'],
		'tax1'=>'',//0
		'tax2'=>'',
		'tax3'=>'',
		'tax4'=>'',
		'MerchantTransId'=>$payment_data['registration_id'],
		'amount'=>'10.00',//$payment_data['event_cost'],
		'filler1'=>'',
		'filler2'=>'',
		'filler3'=>'',
		'filler4'=>'',
		'Note'=>'',
		'paymentType'=>$r['evertec_payment_method'],	
			);
		$cardNumber = $r['card_num'];
		$securityCardCode = $r['cvv'];
		$expirationDate = $r['expmonth'].$r['expyear'];
		$bankRoutingNumber=$r['bankRoutingNumber'];
		$bankAccountNumber=$r['bankAccountNumber'];
		$bankClientName=$r['bankClientName'];
		$authorizationBit= isset($r['authorizationBit']) ? 1 : 0;
		$params = array_merge($params,array(
			'cardNumber'=>$cardNumber,
			'expirationDate'=>$expirationDate,
			'SecurityCardCode'=>$securityCardCode,
			'bankRoutingNumber'=>$bankRoutingNumber,
			'bankAccountNumber'=>$bankAccountNumber,
			'bankClientName'=>$bankClientName,
			'authorizationBit'=>$authorizationBit
		));
	$xml_params = '';
	foreach($params as $name=>$value){
		$xml_params.="<$name>$value</$name>";
	}
		$raw_xml_body = '<?xml version="1.0" encoding="utf-8" ?><soap:Envelope	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xmlns:xsd="http://www.w3.org/2001/XMLSchema"
			xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" ><soap:Body><SendTransactions xmlns="http://tempuri.org/cpsh2h/serviceh2h">'.$xml_params.'</SendTransactions></soap:Body></soap:Envelope>';
	
// 	echo "raw xml:".htmlentities($raw_xml_body)."<hr>";		
		//send request
		$response = wp_remote_post($server_url,
			array(
				'headers'=>array(
					'Content-Type'=>'text/xml; charset=utf-8;',
					"User-Agent"=> "PHP-SOAP/5.4.4",
					'Connection'=> 'Keep-Alive',
					'SOAPAction'=> 'http://tempuri.org/cpsh2h/serviceh2h/SendTransactions'), 
				'method'=>'POST',
				'httpversion' => '1.1',
				'body'=>$raw_xml_body,
				'sslverify' => false));
//		echo 'echodump of $response';
//		echo htmlentities($response['body']);
// 		die;
		
		//process response
		$payment_data['payment_status'] = 'Incomplete';
		$payment_data['txn_type'] = 'EverTec';
		$payment_data['txn_id'] = 0;
		$payment_data['txn_details'] = serialize($_REQUEST);
		
		if(isset($response['body'])){
			$xml   = simplexml_load_string($response['body']);
			//in order to get elements in the default namesapce, you have to register it (see comment by gkokmdam on http://php.net/manual/en/simplexml.examples-basic.php)
			$xml->registerXPathNamespace("def", "http://tempuri.org/cpsh2h/serviceh2h");
			$paymentResultElements = $xml->xpath('//StatusCode');
			$statusDescriptionElements = $xml->xpath('//StatusDescription');
			$confirmationNumberElements = $xml->xpath('//ConfirmationNumber');
			$authorizationNumberElements = $xml->xpath('//AuthorizationNumber');
			if(is_array($paymentResultElements) && is_array($statusDescriptionElements)){
				$paymentResult = $paymentResultElements[0];
				$statusDescription = $statusDescriptionElements[0];
				$confirmationNumber = is_array($confirmationNumberElements) ? $confirmationNumberElements[0] : NULL;
				$authorizationNumber = is_array($authorizationNumberElements) ? $authorizationNumberElements[0] : NULL;
				if($paymentResult == '0000' && $confirmationNumber && $authorizationNumber){
					$payment_data['payment_status'] = 'Completed';
					$payment_data['txn_id'] = 'conf:'.$confirmationNumber.",auth:".$authorizationNumber;
					$error_message = false;
				}else{
					//they sent back an error message
					$error_message = $statusDescription;
				}
			}else{
				$error_message = __("Did not receive a proper XML response from Evertec", "event_espresso");
				if(WP_DEBUG){
					$error_message.=print_r($response,true);
				}
			}
		}else{
				$error_message = __("Did not receive a proper XML response from Evertec", "event_espresso");
		}
	if( $error_message ){
		?><p><strong class="credit_card_failure"><?php _e("An error occurred processing your payment:", "event_espresso");?></strong><br/> <?php echo $error_message?></p>
		<?php
	}
	return $payment_data;
}