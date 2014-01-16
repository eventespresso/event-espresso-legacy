<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
//echo '<h3>'. basename( __FILE__ ) . ' LOADED <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';

function espresso_transactions_moneris_hpp_get_attendee_id( $attendee_id ) {
	$_REQUEST['attendee_id'] = isset( $_POST['id1'] ) && ! empty( $_POST['id1'] ) ? absint( $_POST['id1'] ) : '';
	$_REQUEST['registration_id'] = isset( $_POST['cust_id'] ) && ! empty( $_POST['cust_id'] ) ? sanitize_key( $_POST['cust_id'] ) : FALSE;
	return $_REQUEST['attendee_id'];
}

function espresso_process_moneris_hpp( $payment_data ) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	if( ! class_exists( 'EE_Moneris_HPP' )) {
		event_espresso_require_gateway( 'moneris_hpp/EE_Moneris_HPP.class.php');
	}
	
	$EE_Moneris_HPP = new EE_Moneris_HPP();
	$EE_Moneris_HPP->ipnLog = FALSE;		//		TRUE		FALSE
	
	// if TXN mode = Development, Debug or anything other than Production
	if ( $EE_Moneris_HPP->settings['moneris_hpp_txn_mode'] != 'prod' ) {
		$EE_Moneris_HPP->enableTestMode();
	}
	
//	printr( $_POST, '$_POST  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

// SEE BELOW FOR SAMPLE POST RESPONSE
	
	$payment_data['txn_type'] = 'Moneris Hosted Pay Page';
	$payment_data['txn_id'] = 0;
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_details'] = serialize( $_REQUEST );	
	
//	if ( WP_DEBUG && current_user_can( 'update_core' )) {
//		$current_user = wp_get_current_user();
//		$user_id = $current_user->ID;
//		$payment_data['total_cost'] = $user_id < 3 ? 0.01 : $payment_data['total_cost'];
//	}
	
//	printr( $payment_data, '$payment_data  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	if ( $EE_Moneris_HPP->validateIpn() ) {
		
//		printr( $EE_Moneris_HPP->ipnData, '$EE_Moneris_HPP->ipnData  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		
		$payment_data['txn_details'] = serialize( $EE_Moneris_HPP->ipnData );
		$payment_data['txn_id'] = $EE_Moneris_HPP->ipnData['bank_transaction_id'];
		
//		$totals_match = (float)$EE_Moneris_HPP->ipnData['charge_total'] == (float)$payment_data['total_cost'] ? TRUE : FALSE;
		$txn_approved = (int)$EE_Moneris_HPP->ipnData['response_code'] <= 50 ? TRUE : FALSE;
		
		$log_entry = 'response_order_id = ' . $EE_Moneris_HPP->ipnData['response_order_id'] . ', & ';
		$log_entry .= 'charge_total = ' . $EE_Moneris_HPP->ipnData['charge_total'] . ', & ';
		$log_entry .= 'total_cost = ' . $payment_data['total_cost'] . ', & ';
		$log_entry .= 'response_code = ' . $EE_Moneris_HPP->ipnData['response_code'];
		$EE_Moneris_HPP->moneris_hpp_log( $log_entry );
		
		if ( /*$totals_match &&*/ $txn_approved ) {
		
			$payment_data['payment_status'] = 'Completed';
			$payment_data['txn_id'] = $EE_Moneris_HPP->ipnData['bank_transaction_id'];
			$payment_data['payment_date'] = $EE_Moneris_HPP->ipnData['date_stamp'] . ' ' . $EE_Moneris_HPP->ipnData['time_stamp'];
			
			if ( $EE_Moneris_HPP->testMode ) {
				// For this, we'll just email ourselves ALL the data as plain text output.
				$subject = 'Instant Payment Notification - Gateway Variable Dump';
				$body = "An instant payment notification was successfully recieved\n";
				$body .= "from " . $EE_Moneris_HPP->ipnData['email'] . " on " . date('Y-m-d');
				$body .= " at " . date('g:i A') . "\n\nDetails:\n";
				foreach ($EE_Moneris_HPP->ipnData as $key => $value) {
					$body .= "\n$key: $value\n";
				}
				wp_mail($payment_data['contact'], $subject, $body);
			}
			
		} else {
			
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification failed\n";
			$body .= "from " . $EE_Moneris_HPP->ipnData['email'] . " on " . date('Y-m-d');
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


