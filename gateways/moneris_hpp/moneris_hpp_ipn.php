<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
//echo '<h3>'. basename( __FILE__ ) . ' LOADED <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';

function espresso_transactions_moneris_hpp_get_attendee_id( $attendee_id ) {
	$attendee_id = isset( $_POST['id1'] ) && ! empty( $_POST['id1'] ) ? absint( $_POST['id1'] ) : '';
	$_REQUEST['registration_id'] = isset( $_POST['cust_id'] ) && ! empty( $_POST['cust_id'] ) ? sanitize_key( $_POST['cust_id'] ) : FALSE;
	return $attendee_id;
}

function espresso_process_moneris_hpp( $payment_data ) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	if( ! class_exists( 'EE_Moneris_HPP' )) {
		event_espresso_require_gateway( 'moneris_hpp/EE_Moneris_HPP.class.php');
	}
	
	$EE_Moneris_HPP = new EE_Moneris_HPP();
	$EE_Moneris_HPP->ipnLog = TRUE;
	$EE_Moneris_HPP->ipnLog = FALSE;
	
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
	
//	printr( $payment_data, '$payment_data  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	if ( $EE_Moneris_HPP->validateIpn() ) {
		
//		printr( $EE_Moneris_HPP->ipnData, '$EE_Moneris_HPP->ipnData  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		
		$payment_data['txn_details'] = serialize( $EE_Moneris_HPP->ipnData );
		$payment_data['txn_id'] = $EE_Moneris_HPP->ipnData['bank_transaction_id'];
		
		$totals_match = $EE_Moneris_HPP->ipnData['charge_total'] == $payment_data['total_cost'] ? TRUE : FALSE;
		$txn_approved = $EE_Moneris_HPP->ipnData['response_code'] <= 50 ? TRUE : FALSE;
		
		if ( $totals_match && $txn_approved ) {
		
			$payment_data['payment_status'] = 'Completed';
			
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
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}



//    [response_order_id] => 3-50f3357213bb2-r01
//    [date_stamp] => 2013-01-13
//    [time_stamp] => 17:51:04
//    [bank_transaction_id] => 660109290010139850
//    [charge_total] => 100.00
//    [bank_approval_code] => 002375
//    [response_code] => 027
//    [iso_code] => 01
//    [message] => APPROVED           *                    =
//    [trans_name] => purchase
//    [cardholder] => Brent Christensen
//    [f4l4] => 5454***5454
//    [card] => M
//    [expiry_date] => 1701
//    [result] => 1
//    [eci] => 7
//    [txn_num] => 18738-0_8
//    [rvar_moneris_hpp] => 1358117428
//    [quantity1] => 1
//    [description1] => General Admission for Test Event. Attendee: Brent Christensen
//    [id1] => 29
//    [price1] => 100.00
//    [shipping_cost] => 
//    [hst] => 
//    [pst] => 
//    [gst] => 
//    [bill_first_name] => Brent
//    [bill_last_name] => Christensen
//    [bill_company_name] => 
//    [bill_address_one] => 
//    [bill_city] => 
//    [bill_state_or_province] => 
//    [bill_postal_code] => 
//    [bill_country] => 
//    [bill_phone] => 
//    [bill_fax] => 
//    [email] => brent@pyfo.ca
//    [cust_id] => 3-50f3357213bb2
//    [note] => 