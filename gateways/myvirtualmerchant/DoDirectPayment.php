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
		'ssl_merchant_id'=>$myvirtualmerchant_settings['ssl_merchant_id'],
		'ssl_user_id'=>$myvirtualmerchant_settings['ssl_user_id'],
		'ssl_pin'=>$myvirtualmerchant_settings['ssl_pin'],
		'ssl_show_form'=>'false',//we just want to process the payment, not get the HTML to show a form or anything
		'ssl_card_number'=>$_POST['card_num'],
		'ssl_exp_date'=>$_POST['expmonth'] . $_POST['expyear'],
		'ssl_amount'=>$payment_data['total_cost'],
		'ssl_avs_zip'=>$_POST['zip'],
		'ssl_avs_address'=>$_POST['address'].", ".$_POST['city'].", ".$_POST['state'],
		'ssl_cvv2cvc2'=>$_POST['cvv'],
		'ssl_invoice_number'=>$_POST['invoice'],
//		'ssl_transaction_currency'=>$myvirtualmerchant_settings['currency_format'],
		'ssl_result_format'=>'ASCII',
		
	);
	$result = wp_remote_post($endpoint, array(
		'method'=>'POST',
		'body'=>$request));
	echo $result['body'];
	var_dump($result);die;
	
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'MyVirtualMerchant';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	$Errors = GetErrors($PayPalResult);
	if (!empty($PayPalResult)) {
		unset($PayPalResult['REQUESTDATA']['CREDITCARDTYPE']);
		unset($PayPalResult['REQUESTDATA']['ACCT']);
		unset($PayPalResult['REQUESTDATA']['EXPDATE']);
		unset($PayPalResult['REQUESTDATA']['CVV2']);
		unset($PayPalResult['RAWREQUEST']);
		
		$payment_data['txn_id'] = isset( $PayPalResult['TRANSACTIONID'] ) ? $PayPalResult['TRANSACTIONID'] : '';
		$payment_data['txn_details'] = serialize($PayPalResult);
		if (!APICallSuccessful($PayPalResult['ACK'])) {
			DisplayErrors($Errors);
		} else {
			$payment_data['payment_status'] = 'Completed';
		}
	} else {
		?>
		<p><?php _e('There was no response from MyVirtualMerchant.', 'event_espresso'); ?></p>
		<?php
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
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
