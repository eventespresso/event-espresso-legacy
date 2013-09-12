<?php

function espresso_display_evertec($payment_data) {
	global $wpdb;
	
	
	global $org_options;
	$evertec_settings = get_option('event_espresso_evertec_settings');
	
//	$xml_request = "
//	<?xml version='1.0' encoding='utf-8'	
//	";;

	
	
	////////////////////////////////////////
	$params = array(
		'Username'=>$evertec_settings['username'],
		'Password'=>$evertec_settings['password'],
		'CustomerName'=>$payment_data['fname']." ".$payment_data['lname'],
		'CustomerID'=>$payment_data['registration_id'],
		'CustomerEmail'=>'monkey',//$payment_data['attendee_email'],
		'Total'=>'10.00',//$payment_data['event_cost'],
		'DescriptionBuy'=>$payment_data['event_name'],
		'TaxAmount1'=>0,
		'address1'=>$payment_data['address'],
		'address2'=>isset($payment_data['address2']) ? $payment_data['address2'] : '',
		'city'=>$payment_data['city'],
		'zipcode'=>$payment_data['zip'],
		'telephone'=>$payment_data['phone'],
		'fax'=>'',
		'ignoreValues'=>'',  
		'language'=>$evertec_settings['evertec_pages_language'],
		'TaxAmount2'=>'',
		'TaxAmount3'=>'',
		'TaxAmount4'=>'',
		'TaxAmount5'=>'',
		'filler1'=>'',
		'filler2'=>'',
		'filler3'=>''
			);
	$xml_params = '';
	foreach($params as $name=>$value){
		$xml_params.="<$name>$value</$name>";
	}
		$raw_xml_body = '<?xml version="1.0" encoding="utf-8" ?>
			<soap:Envelope	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xmlns:xsd="http://www.w3.org/2001/XMLSchema"
			xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" >
			<soap:Body>
			<MakePayment xmlns="http://tempuri.org/WebMerchant/MerchantService">
			'.$xml_params.'
			</MakePayment>
			</soap:Body>
			</soap:Envelope>';
		
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
	
			$soap_client_req = '<?xml version="1.0" encoding="UTF-8"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:ns1="http://tempuri.org/WebMerchant/MerchantService"><env:Body><ns1:MakePayment/></env:Body></env:Envelope>';
//	echo 'echodump of $client->__getLastRequest ()';
//	var_dump($client->__getLastRequest ());
//	echo 'echodump of $client->__getLastRequestHeaders();';
//	var_dump($client->__getLastRequestHeaders());
	
			
			
			$response = wp_remote_post('https://everpaycert.evertecinc.com/wscheckoutpayment/wsCheckoutPayment.asmx?op=MakePayment',//https://mmpay.evertecinc.com/webservicev2/wscheckoutpayment.asmx', 
				array(
					'headers'=>array(
						'Content-Type'=>'text/xml; charset=utf-8;',
						"User-Agent"=> "PHP-SOAP/5.4.4",
						'Connection'=> 'Keep-Alive',
						'SOAPAction'=>'http://tempuri.org/WebMerchant/MerchantService/MakePayment'),
					'method'=>'POST',
					'httpversion' => '1.1',
					'body'=>$raw_xml_body,
					'sslverify' => false));
//		echo 'echodump of $response';
//		var_dump($response);
		if(isset($response['body'])){
			$xml   = simplexml_load_string($response['body']);
			//in order to get elements in the default namesapce, you have to register it (see comment by gkokmdam on http://php.net/manual/en/simplexml.examples-basic.php)
			$xml->registerXPathNamespace("def", "http://tempuri.org/WebMerchant/MerchantService");
			$paymentResultElements = $xml->xpath('//def:MakePaymentResult');
			if($paymentResultElements){
				$paymentResult = $paymentResultElements[0];
				if(strpos($paymentResult, 'http')){
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
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_evertec');