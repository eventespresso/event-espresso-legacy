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
		'TaxAmount'=>0,
		'address1'=>$payment_data['address'],
		'city'=>$payment_data['city'],
		'zipcode'=>$payment_data['zip'],
		'telephone'=>$payment_data['phone'],
		'language'=>$evertec_settings['evertec_pages_language'],
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
		
		
//		echo $response['body'];die;
		$client = new SoapClient('https://mmpay.evertecinc.com/webservicev2/wscheckoutpayment.asmx?wsdl', array('trace'=>true,'soap_version' => SOAP_1_2));
//			echo 'echodump of $client->__getFunctions()';
//			var_dump($client->__getFunctions());
			
//			echo 'echodump of $client->__getTypes()';
//			var_dump($client->__getTypes());
			try{
				$makePaymentResponse = $client->MakePayment(array(array('MakePayment'=>$params)));
//				echo 'echodump of $makePaymentResponse';
//				var_dump($makePaymentResponse);
			}catch(Exception $e){
//				echo 'echodump of $e';
//				var_dump($e);
			}
	
			$soap_client_req = '<?xml version="1.0" encoding="UTF-8"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:ns1="http://tempuri.org/WebMerchant/MerchantService"><env:Body><ns1:MakePayment/></env:Body></env:Envelope>';
//	echo 'echodump of $client->__getLastRequest ()';
//	var_dump($client->__getLastRequest ());
//	echo 'echodump of $client->__getLastRequestHeaders();';
//	var_dump($client->__getLastRequestHeaders());
	
			
			
			$response = wp_remote_post('https://mmpay.evertecinc.com/webservicev2/wscheckoutpayment.asmx', 
				array(
					'headers'=>array('Content-Type'=>'application/soap+xml; charset=utf-8; action="http://tempuri.org/WebMerchant/MerchantService/MakePayment"'),
					'method'=>'POST',
					"User-Agent"=> "PHP-SOAP/5.4.4",
					'httpversion' => '1.1',
					'Connection'=> 'Keep-Alive',
					'body'=>$raw_xml_body,
					'sslverify' => false));
		echo 'echodump of $response';
		var_dump($response);
	die;
	if (!empty($evertec_settings['bypass_payment_page']) && $evertec_settings['bypass_payment_page'] == 'Y') {
		$myEvertec->submitPayment();
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
		$myEvertec->submitButton($button_url, 'evertec');
	}

	if ($use_sandbox) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Evertec Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myEvertec->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_evertec');