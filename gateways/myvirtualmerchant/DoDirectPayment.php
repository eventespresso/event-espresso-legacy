<?php

function espresso_transactions_myvirtualmerchant_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_myvirtualmerchant($payment_data) {
	extract($payment_data);
	$myvirtualmerchant_settings = get_option('event_espresso_myvirtualmerchant_settings');
	$endpoint = $myvirtualmerchant_settings['myvirtualmerchant_use_sandbox'] ? 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do' : 'https://www.myvirtualmerchant.com/VirtualMerchant/process.do';
	
	$request = array(
		'ssl_transaction_type'=>'ccsale',
		'ssl_result_format'=>'ASCII',
		'ssl_merchant_id'=>$myvirtualmerchant_settings['ssl_merchant_id'],
		'ssl_user_id'=>$myvirtualmerchant_settings['ssl_user_id'],
		'ssl_pin'=>$myvirtualmerchant_settings['ssl_pin'],
		'ssl_show_form'=>'false',//we just want to process the payment, not get the HTML to show a form or anything
		'ssl_card_number'=>$_POST['card_num'],
		'ssl_exp_date'=>$_POST['expmonth'] . $_POST['expyear'],
		'ssl_amount'=>$payment_data['total_cost'],
		'ssl_first_name'=>$_POST['first_name'],
		'ssl_last_name'=>$_POST['last_name'],
		'ssl_email'=>$_POST['email'],
		'ssl_avs_address'=>$_POST['address'],
		'ssl_city'=>$_POST['city'],
		'ssl_state'=>$_POST['state'],
		'ssl_country'=>$_POST['country'],
		'ssl_avs_zip'=>$_POST['zip'],
		'ssl_cvv2cvc2'=>$_POST['cvv'],
		'ssl_invoice_number'=>$_POST['invoice'],
		'ssl_description'=>  sprintf(__("Registration %s for event %s", "event_espresso"),$payment_data['registration_id'],$payment_data['event_name']),
		'event_name'=>$payment_data['event_name'],
		'registration_id'=>$payment_data['registration_id']
		
	);
	//if they have mutli-currency enabled in my virtual merchant, use it
	if($myvirtualmerchant_settings['use_custom_currency']){
		$request['ssl_transaction_currency'] = $myvirtualmerchant_settings['currency_format'];
	}
	$result = wp_remote_post($endpoint, array(
		'method'=>'POST',
		'body'=>$request));
	
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'MyVirtualMerchant';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($result['body']);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	
	if (!empty($result['body'])) {
		$txn_details = espresso_myvirtualmerchant_parse_response($result['body']);
		$payment_data['txn_id'] = isset( $txn_details['ssl_txn_id'] ) ? $txn_details['ssl_txn_id'] : '';
		if( ! isset($txn_details['errorCode'])){//no validation error, so at least mvm attempted to process the payment...
			if ( $txn_details['ssl_result'] == '0') {
				$payment_data['payment_status'] = 'Completed';
			} else {
				$payment_data['payment_status'] = 'Payment Declined';
				?><p><?php	printf(__("Payment failed because: %s", 'event_espresso'),$txn_details['ssl_result_message']);?></p><?php
			}
		}else{//there was an error
			$payment_data['payment_status'] = 'Payment Declined';
			?><p><?php printf(__("An error occurred processing your payment: %s (%s), %s", 'event_espresso'),$txn_details['errorName'],$txn_details['errorCode'],$txn_details['errorMessage']); ?></p> <?php
		}
	} else {
		?>
		<p><?php _e('There was no response from MyVirtualMerchant.', 'event_espresso'); ?></p>
		<?php
	}
	return $payment_data;
}
/**
 * Converts myvritualmercahtn's ASCII response (which looks like "name1=val1\nname2=val2\n...") into an associative array
 * @param string $response_string
 * @return array
 */
function espresso_myvirtualmerchant_parse_response($response_string){
	$response_array = array();
	$lines = explode("\n",$response_string);
	foreach($lines as $line){
		list($name,$value) = explode("=",$line,2);
		$response_array[$name] = $value;
	}
	return $response_array;
}