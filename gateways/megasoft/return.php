<?php

function espresso_transactions_megasoft_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['cust_id'])) {
		$attendee_id = $_REQUEST['cust_id'];
	}
	return $attendee_id;
}
function espresso_process_megasoft($payment_data){
	global $wpdb, $org_options;
	$megasoft_settings = get_option('event_espresso_megasoft_settings');
	$use_sandbox = $megasoft_settings['use_sandbox'];
	if ($use_sandbox) {
		$url = "https://200.71.151.226:8443/payment/action/procesar-compra";// "https://paytest.megasoft.com.ve:8443/payment/action/procesar-compra";
	} else {
		$url = "https://payment.megasoft.com.ve/payment/action/procesar-compra";
	}
	$Request = "?cod_afiliacion=".$megasoft_settings['megasoft_login_id'];
	$Request .= "&transcode=0141";
	$Request .= "&pan=".$_POST['card_num'];
	$Request .= "&cvv2=".$_POST['ccv_code'];
	$Request .= "&cid=".$_POST['cid_code'].$_POST['cid'];
	$Request .= "&expdate=".$_POST['exp_date_month'].$_POST['exp_date_year'];
	$Request .= "&amount=".number_format(100*$payment_data['total_cost'], 0, '', '');
	$Request .= "&client=".$_POST['first_name']." ".$_POST['last_name'];
	$Request .= "&factura=".$_POST['invoice_num'];
	
	$fullUrl=$url.$Request;
	//echo "full url: $fullUrl";
	$response=wp_remote_get($fullUrl,array('timeout'=>15,'sslverify'=>false));
	
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'Megasoft';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($_REQUEST);
	if(is_array($response) && array_key_exists('body',$response)){
		//ignore xml parsing errors. If Megasoft doesn't properly escape their XML, we'll just try our best to use it
		$xml= @simplexml_load_string($response['body']);
		if($xml){
			//it's xml alright. but was it a successful charge, or were there errors?
			if($xml->codigo=='00'){//success!
				$payment_data['txn_id'] = $xml->factura;
				$payment_data['txn_details'] = $response['body'];
				$payment_data['payment_status'] = 'Completed';
			}else{
				$payment_data['txn_details'] = $response['body'];
			}
			
		}
	}
	if(empty($xml)){
		?>
		<p><?php _e('There was no response from MegaSoft', 'event_espresso'); ?>
		</p>
		<?php		
	}elseif(isset($xml) && $xml->codigo!='00'){
		?>
		<p><?php _e('There was an error processing your payment:', 'event_espresso'); ?>
			<?php _e($xml->descripcion)?>
		</p>
		<?php
	}
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}