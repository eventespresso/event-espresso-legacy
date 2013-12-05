<?php

function espresso_transactions_evertec_get_attendee_id($attendee_id) {
	global $wpdb;
	if (empty($attendee_id)) {
		$attendee_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".EVENTS_ATTENDEE_TABLE." where registration_id = %s",$_REQUEST['r_id']));
	}
	return $attendee_id;
}

function espresso_process_evertec($payment_data) {
	echo 'echodump of $payment_data';
	var_dump($payment_data);
	echo 'echodump of $_REQUEST';
	var_dump($_REQUEST);
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
		'password'=>$evertec_settings['password'],
		'CustomerName'=>$r['first_name']." ".$r['last_name'],
		'CustomerID'=>$payment_data['registration_id'],
		'CustomerEmail'=>$r['email'],//$payment_data['attendee_email'],
		'address1'=>$r['address'],
		'address2'=>isset($r['address2']) ? $r['address2'] : '',
		'city'=>$r['city'],
		'state'=>$r['state'],
		'zipcode'=>$r['zip'],
		'telephone'=>$r['phone'],
		'fax'=>'',
		'DescriptionBuy'=>$payment_data['event_name'],
		'channel'=>6,//not sure what this is for
		'ignoreValues'=>'',  
		'operatorId'=>'',
//		'language'=>$evertec_settings['evertec_pages_language'],
		'tax1'=>'',//0
		'tax2'=>'',
		'tax3'=>'',
		'tax4'=>'',
		'merchantTransId'=>$payment_data['registration_id'],
		'amount'=>'10.00',//$payment_data['event_cost'],
		'filler1'=>'',
		'filler2'=>'',
		'filler3'=>'',
		'filler4'=>'',
		'note'=>'',
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
			'securityCardCode'=>$securityCardCode,
			'bankRoutingNumber'=>$bankRoutingNumber,
			'bankAccountNumber'=>$bankAccountNumber,
			'bankClientName'=>$bankClientName,
			'authorizationBit'=>$authorizationBit
		));
echo 'echodump of $xml_params';
var_dump($params);
	$xml_params = '';
	foreach($params as $name=>$value){
		$xml_params.="<$name>$value</$name>";
	}
		$raw_xml_body = '<?xml version="1.0" encoding="utf-8" ?>
			<soap:Envelope	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xmlns:xsd="http://www.w3.org/2001/XMLSchema"
			xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" >
			<soap:Body>
			<SendTransactions xmlns="http://tempuri.org/cpsh2h/serviceh2h">
			'.$xml_params.'
			</SendTransactions>
			</soap:Body>
			</soap:Envelope>';
		echo htmlspecialchars($raw_xml_body);
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
	
//			$response = wp_remote_post($server_url."/SendTransactions",
//					array('headers'=>array(
//						'Content-Type'=>'application/x-www-form-urlencoded',
//						'Connection'=> 'Keep-Alive'),
//					'body'=>$params,
//					'method'=>'POST',
//					'sslverify'=>false));
					
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
		echo htmlspecialchars($response['body']);
		die;
		if(isset($response['body'])){
			$xml   = simplexml_load_string($response['body']);
			//in order to get elements in the default namesapce, you have to register it (see comment by gkokmdam on http://php.net/manual/en/simplexml.examples-basic.php)
			$xml->registerXPathNamespace("def", "http://tempuri.org/WebMerchant/MerchantService");
			$paymentResultElements = $xml->xpath('//def:MakePaymentResult');
			if($paymentResultElements){
				$paymentResult = $paymentResultElements[0];
				if(strpos($paymentResult, 'http') !== FALSE){
					//they sent back a URL. we can link the user ot taht
					$redirect_url = $paymentResult;
					$error_message = false;
				}else{
					//they sent back an error message
					$redirect_url = false;
					$error_message = $paymentResult;
				}
			}else{
				$redirect_url = false;
				$error_message = __("Did not receive a proper XML response from Evertec", "event_espresso");
				if(WP_DEBUG){
					$error_message.=print_r($response,true);
				}
			}
		}else{
				$error_message = __("Did not receive a proper XML response from Evertec", "event_espresso");
		}
	if( $error_message ){
		echo __("An error occurred communicating with Evertec:", "event_espresso").$error_message; return;
	}
	
	if (!empty($evertec_settings['bypass_payment_page']) && $evertec_settings['bypass_payment_page'] == 'Y') {
		wp_redirect($redirect_url);
	} else {
		if (empty($evertec_settings['button_url'])) {
			if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/evertec/btn_stdCheckout2.gif")) {
				$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/evertec/btn_stdCheckout2.gif";
			} else {
				$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/evertec/btn_stdCheckout2.gif";
			}
		} elseif (isset($evertec_settings['button_url'])) {
			$button_url = $evertec_settings['button_url'];
		} else {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/evertec/btn_stdCheckout2.gif";
		}
		?>
			 <div id="evertec-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
				 <a href='<?php echo $redirect_url?>'>
			<img class="payment-option-lnk allow-leave-page" src="<?php echo $button_url?>" alt="click to visit this payment gateway">
				</a>
			 </div>
		<?php
	
	}

	if ($use_sandbox) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Evertec Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myEvertec->dump_fields();
	}
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





