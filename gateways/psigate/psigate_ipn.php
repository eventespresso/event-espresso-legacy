<?php

function espresso_transactions_psigate_get_attendee_id($attendee_id) {
	global $wpdb;
	if (!empty($_REQUEST['r_id'])) {
		$reg_id = $_REQUEST['r_id'];
		$attendee_id=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}events_attendee WHERE registration_id=%s LIMIT 1",$reg_id));
	}
	return $attendee_id;
}

function espresso_process_psigate($payment_data) {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$payment_data['txn_type'] = 'PSiGate';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize($_REQUEST);
	//http://monkey.com/?TransTime=Thu%20Feb%2021%2013:48:45%20EST%202013&OrderID=2013022113484500514&TransactionType=SALE&Approved=APPROVED&ReturnCode=Y:123456:0abcdef:::NYY&ErrMsg=&TaxTotal=15.00&ShipTotal=6.00&SubTotal=1305.00&FullTotal=1326.00&PaymentType=CC&CardNumber=......1111&TransRefNumber=1befb0f25f3fee22&CardIDResult=&AVSResult=&CardAuthNumber=123456&CardRefNumber=0abcdef&CardType=VISA&IPResult=NYY&IPCountry=CA&IPRegion=Ontario&IPCity=Toronto&CustomerRefNo=123456789
	
	$psigate_settings = get_option('event_espresso_psigate_settings');
	//check that 'Err' is empty
	if(!empty($_REQUEST['ErrMsg'])){?>
		<h2>Payment Declined</h2>
		<p><strong class="credit_card_failure"><?php echo $_REQUEST['ErrMsg']?></strong></p>
		<p><strong class="credit_card_failure">Please try again</strong></p>
		<?php
	}else{
		$payment_data['txn_details']=serialize($_REQUEST);
		$payment_data['txn_id']=$_REQUEST['OrderID'];
		if('APPROVED'==$_REQUEST['Approved']){
			$payment_data['payment_status']='Completed';
		}elseif('DECLINED'==$_REQUEST['Approved']){
			$payment_data['payment_status']='Payment Declined';
		}elseif('ERROR'==$_REQUEST['Approved']){
			$payment_data['payment_status']='Incomplete';
		}
	}
	/*if ($myPaypal->validateIpn()) {
		$payment_data['txn_details'] = serialize($myPaypal->ipnData);
		$payment_data['txn_id'] = $myPaypal->ipnData['txn_id'];
		if ($myPaypal->ipnData['mc_gross'] >= $payment_data['total_cost'] && ($myPaypal->ipnData['payment_status'] == 'Completed' || $myPaypal->ipnData['payment_status'] == 'Pending')) {
			$payment_data['payment_status'] = 'Completed';
			if ($psigate_settings['use_sandbox']) {
				// For this, we'll just email ourselves ALL the data as plain text output.
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . $myPaypal->ipnData['payer_email'] . " on " . date('m/d/Y');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				foreach ($myPaypal->ipnData as $key => $value) {
					$body .= "\n$key: $value\n";
				}
				wp_mail($payment_data['contact'], $subject, $body);
			}
		} else {
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . $myPaypal->ipnData['payer_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($myPaypal->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	}*/
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
