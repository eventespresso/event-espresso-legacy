<?php

function espresso_transactions_evertec_get_attendee_id($attendee_id) {
	global $wpdb;
	if (empty($attendee_id)) {
		$attendee_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".EVENTS_ATTENDEE_TABLE." where registration_id = %s",$_REQUEST['r_id']));
	}
	return $attendee_id;
}

function espresso_process_evertec($payment_data) {
// 	echo 'echodump of $payment_data';
// 	var_dump($payment_data);
// 	echo 'echodump of $_REQUEST';
// 	var_dump($_REQUEST);
	$r=$_REQUEST;
	$evertec_settings = get_option('event_espresso_evertec_settings');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($evertec_settings['use_sandbox']){
		$server_url = 'https://everpaycert.evertecinc.com/cpsh2h/serviceh2h.asmx';//'https://everpaycert.evertecinc.com/wscheckoutpayment/wsCheckoutPayment.asmx?op=MakePayment';
	}else{
		$server_url = 'https://mmpay.evertecinc.com/cpsh2h/serviceh2h.asmx';  // 'https://mmpay.evertecinc.com/webservicev2/wscheckoutpayment.asmx';
	}
	

	
	
	////////////////////////////////////////
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
	//was it ac redit card purchase?
//	if(in_array($r['evertec_payment_method'],array('A','V','M','X'))){
		$cardNumber = $r['card_num'];
		$securityCardCode = $r['cvv'];
		$expirationDate = $r['expmonth'].$r['expyear'];
		$bankRoutingNumber=$r['bankRoutingNumber'];
		$bankAccountNumber=$r['bankAccountNumber'];
		$bankClientName=$r['bankClientName'];
		$authorizationBit= isset($r['authorizationBit']) ? 1 : 0;
//	}
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
		
//		echo htmlentities($raw_xml_body);
//		echo $response['body'];die;
//		$client = new SoapClient('https://mmpay.evertecinc.com/webservicev2/wscheckoutpayment.asmx?wsdl', array('trace'=>true,'soap_version' => SOAP_1_2));
//			echo 'echodump of $client->__getFunctions()';
//			var_dump($client->__getFunctions());
	
//			echo 'echodump of $client->__getTypes()';
//			var_dump($client->__getTypes());
//			try{
//				$makePaymentResponse = $client->MakePayment(array(array('MakePayment'=>$params)));
//				echo 'echodump of $makePaymentResponse';
//				var_dump($makePaymentResponse);
//			}catch(Exception $e){
//				echo 'echodump of $e';
//				var_dump($e);
//			}
	
 			$soap_client_req = '<?xml version="1.0" encoding="UTF-8"?><env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:ns1="http://tempuri.org/WebMerchant/MerchantService"><env:Body><ns1:MakePayment/></env:Body></env:Envelope>';
//	echo 'echodump of $client->__getLastRequest ()';
//	var_dump($client->__getLastRequest ());
//	echo 'echodump of $client->__getLastRequestHeaders();';
//	var_dump($client->__getLastRequestHeaders());
	
// 	echo "raw xml:".htmlentities($raw_xml_body)."<hr>";		
			$response = wp_remote_post($server_url,
				array(
					'headers'=>array(
						'Content-Type'=>'text/xml; charset=utf-8;',
						"User-Agent"=> "PHP-SOAP/5.4.4",
						'Connection'=> 'Keep-Alive',
						'SOAPAction'=> 'http://tempuri.org/cpsh2h/serviceh2h/SendTransactions'),//  'http://tempuri.org/WebMerchant/MerchantService/MakePayment'), 
					'method'=>'POST',
					'httpversion' => '1.1',
					'body'=>$raw_xml_body,
					'sslverify' => false));
		echo 'echodump of $response';
		echo htmlentities($response['body']);
// 		die;
		
		$payment_data['payment_status'] = 'Incomplete';
		$payment_data['txn_type'] = 'evertec';
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
					$payment_data['txn_id'] = 'conf:'.$confirmationNumber.",auth:'".$authorizationNumber;
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
		printf(__("An error occurred processing your payment: %s", "event_espresso"),$error_message);
	}

//	if ($use_sandbox) {
//
//		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Evertec Debug Mode Is Turned On', 'event_espresso') . '</h3>';
//		$myEvertec->dump_fields();
//	}
	return $payment_data;
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





