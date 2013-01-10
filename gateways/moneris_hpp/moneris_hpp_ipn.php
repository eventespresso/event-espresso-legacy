<?php

function espresso_transactions_moneris_hpp_get_attendee_id( $attendee_id ) {
	if ( ! empty( $_REQUEST['id'] )) {
		$attendee_id = sanitize_key( $_REQUEST['id'] );
	}
	return $attendee_id;
}

function espresso_process_moneris_hpp( $payment_data ) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	require_once ( 'Moneris_HPP.class.php' );
	
	$payment_data['txn_type'] = 'Moneris Hosted Pay Page';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize( $_REQUEST );	
	
	$EE_Moneris_HPP = new EE_Moneris_HPP();
	$EE_Moneris_HPP->ipnLog = TRUE;
	// grab settings
	$moneris_hpp_settings = get_option('event_espresso_moneris_hpp_settings');
	// if TXN mode = Development, Debug or anything other than Production
	if ( $moneris_hpp_settings['moneris_hpp_txn_mode'] != 'prod' ) {
		$EE_Moneris_HPP->enableTestMode();
	}
	if ( $EE_Moneris_HPP->validateIpn() ) {
	
		$payment_data['txn_details'] = serialize($EE_Moneris_HPP->ipnData);
		$payment_data['txn_id'] = $EE_Moneris_HPP->ipnData['txn_id'];
		
		if ($EE_Moneris_HPP->ipnData['mc_gross'] == $payment_data['total_cost'] && ($EE_Moneris_HPP->ipnData['payment_status'] == 'Completed' || $EE_Moneris_HPP->ipnData['payment_status'] == 'Pending')) {
			$payment_data['payment_status'] = 'Completed';
			if ($moneris_hpp_settings['use_sandbox']) {
				// For this, we'll just email ourselves ALL the data as plain text output.
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . $EE_Moneris_HPP->ipnData['payer_email'] . " on " . date('m/d/Y');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				foreach ($EE_Moneris_HPP->ipnData as $key => $value) {
					$body .= "\n$key: $value\n";
				}
				wp_mail($payment_data['contact'], $subject, $body);
			}
		} else {
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . $EE_Moneris_HPP->ipnData['payer_email'] . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($EE_Moneris_HPP->ipnData as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($payment_data['contact'], $subject, $body);
		}
	}
	add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
