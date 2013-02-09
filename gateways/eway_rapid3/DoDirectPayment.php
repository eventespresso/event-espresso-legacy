<?php

function espresso_transactions_eway_rapid3_get_attendee_id($attendee_id) {
	if (!empty($_REQUEST['id'])) {
		$attendee_id = $_REQUEST['id'];
	}
	return $attendee_id;
}

function espresso_process_eway_rapid3($payment_data) {
	global $org_options;
	extract($payment_data);
// Included required files.

	//require_once('includes/paypal.nvp.class.php');
	require_once('includes/EWayRapid3Client.class.php');
	$eway_rapid3_settings = get_option('event_espresso_eway_rapid3_settings');
	$rapid3Client=new Espresso_EWayRapid3Client(
		array(
			'apiKey'=>$eway_rapid3_settings['eway_rapid3_api_key'],
			'apiPassword'=>$eway_rapid3_settings['eway_rapid3_api_password'],
			'useSandbox'=>$eway_rapid3_settings['eway_rapid3_use_sandbox']
		));
	$eway_rapid3Result=$rapid3Client->getAccessCodeResult();
	//echo "payment result:";
	//var_dump($paymentResult);
	
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_type'] = 'Eway Rapid 3.0';
	$payment_data['txn_id'] = 0;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	//$Errors = GetErrors($eway_rapid3Result);
	if (!empty($eway_rapid3Result)) {
		$payment_data['txn_id'] = $eway_rapid3Result->TransactionID;
		$payment_data['txn_details'] = serialize($eway_rapid3Result);
		if (!espresso_transaction_was_successful($eway_rapid3Result)) {
			espresso_display_transaction_errors($eway_rapid3Result);
		} else {
			$payment_data['payment_status'] = 'Completed';
		}
	} else {
		?>
		<p><?php _e('There was no response from Eway Rapid 3.0.', 'event_espresso'); ?></p>
		<?php
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}

/**
 * analyzes response from eway rapid3, and determines if the transaction was successful or not
 * @param type $response
 * @return boolean 
 */
function espresso_transaction_was_successful($response) {
	$statusCode=$response->ResponseMessage;
	if(in_array($statusCode,array(//for full list of response messages and meanings, see http://www.eway.com.au/docs/api-documentation/rapid3-0documentation.pdf?sfvrsn=2, appendix G
			//and http://www.eway.com.au/developers/resources/response-codes, except you add 'A20' to approved statuses,
			//and 'A44' to denied statuses
			'A2000',//transaction aproved
			'A2008',//honour with identification (ignored)
			'A2010',//approved for partial amount
			'A2011',//approved for VIP
			'A2016',//approved, 'update track 3'
			)))
		return true;
	else
		return false;
}

/**
 * takes the responsecodes 
 * @param type $rapid3Response 
 */
function espresso_display_transaction_errors($rapid3Response) {	
	$validationErrorCodes=explode(",",$rapid3Response->ResponseMessage);
	$validationErrors=array();
	foreach($validationErrorCodes as $validationErrorCode){
		switch($validationErrorCode){
		case 'D4401': 
		case 'D4402':
		case 'D4403':
		case 'D4404':	
		case 'D4405':	
		case 'D4407':
		case 'D4412':	
		case 'D4434':	
		case 'D4435':
		case 'D4436':	
		case 'D4437':
			$validationErrors[]='Denied';break;	
		case 'D4406': 
		case 'D4415':	
		case 'D4421':
			$validationErrors[]='Error	';break;
		case 'D4409': 
			$validationErrors[]='Request In Progress	';break;
		case 'D4413': $validationErrors[]='Invalid Amount	';break;
		case 'D4414': $validationErrors[]='Invalid Card Number	';break;
		case 'D4419': $validationErrors[]='Re-enter Last Transaction	';break;
		case 'D4422': $validationErrors[]='Suspected Malfunction	';break;
		case 'D4423': $validationErrors[]='Unacceptable Transaction Fee	';break;
		case 'D4425': $validationErrors[]='Unable to Locate Record On File	';break;
		case 'D4430': $validationErrors[]='Format Error	';break;
		case 'D4431': $validationErrors[]='Bank Not Supported By Switch	';break;
		case 'D4433': $validationErrors[]='Expired Card, Capture	';break;
		case 'D4438': $validationErrors[]='PIN Tries Exceeded, Capture	';break;
		case 'D4439': $validationErrors[]='No Credit Account	';break;
		case 'D4440': $validationErrors[]='Function Not Supported	';break;
		case 'D4441': $validationErrors[]='Lost Card	';break;
		case 'D4442': $validationErrors[]='No Universal Account	';break;
		case 'D4443': $validationErrors[]='Stolen Card	';break;
		case 'D4444': $validationErrors[]='No Investment Account	';break;
		case 'D4451': $validationErrors[]='Insufficient Funds	';break;
		case 'D4452': $validationErrors[]='No Cheque Account	';break;
		case 'D4453': $validationErrors[]='No Savings Account	';break;
		case 'D4454': $validationErrors[]='Expired Card	';break;
		case 'D4455': $validationErrors[]='Incorrect PIN	';break;
		case 'D4456': $validationErrors[]='No Card Record	';break;
		case 'D4457': $validationErrors[]='Function Not Permitted to Cardholder	';break;
		case 'D4458': $validationErrors[]='Function Not Permitted to Terminal	';break;
		case 'D4459': $validationErrors[]='Suspected Fraud	';break;
		case 'D4460': $validationErrors[]='Acceptor Contact Acquirer	';break;
		case 'D4461': $validationErrors[]='Exceeds Withdrawal Limit	';break;
		case 'D4462': $validationErrors[]='Restricted Card	';break;
		case 'D4463': $validationErrors[]='Security Violation	';break;
		case 'D4464': $validationErrors[]='Original Amount Incorrect	';break;
		case 'D4466': $validationErrors[]='Acceptor Contact Acquirer, Security	';break;
		case 'D4467': $validationErrors[]='Capture Card	';break;
		case 'D4475': $validationErrors[]='PIN Tries Exceeded	';break;
		case 'D4482': $validationErrors[]='CVV Validation Error	';break;
		case 'D4490': $validationErrors[]='Cutoff In Progress	';break;
		case 'D4491': $validationErrors[]='Card Issuer Unavailable	';break;
		case 'D4492': $validationErrors[]='Unable To Route Transaction	';break;
		case 'D4493': $validationErrors[]='Cannot Complete, Violation Of The Law	';break;
		case 'D4494': $validationErrors[]='Duplicate Transaction	';break;
		case 'D4496': $validationErrors[]='System Error	';break;	
		case 'V6021':
		case 'V6100':
			$validationErrors[]='Invalid Cardholder Name';
			break;
		case 'V6022':
		case 'V6110':
			$validationErrors[]='Invalid Card Number';
			break;
		case 'V6023':
		case 'V6106':
			$validationErrors[]='Invalid CVN Number';
			break;
		case 'V6033':
		case 'V6101':
		case 'V6102':
			$validationErrors[]='Invalid Expiry Date';
			break;
		case 'V6042':
		case 'V6043':
		case 'V6051':
		case 'V6052':
			$validationErrors[]='Invalid Customer Name';
			break;
		default:
			$validationErrors[]='Credit Card Data Invalid';
	}
	}
	
	?>
	<p><strong class="credit_card_failure">Attention: Your transaction was declined. Message from Payment Processing:</strong><br />
	<ul>
		<?php foreach($validationErrors as $validationError){?>
		<li><?php echo $validationError?></li>
		<?php }?>
	</ul><?php
	
}
