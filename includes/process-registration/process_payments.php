<?php
//Payment processing - Used for onsite payment processing. Used with the {ESPRESSO_TXN_PAGE} tag
function event_espresso_txn(){
	global $wpdb, $org_options;
	$attendee_id="";
	/*foreach ($_REQUEST as $k => $v){
		print "   $k = $v\n";

	}*/
	$attendee_id=$_REQUEST['id'];//This is the id of the registrant
	if ($_REQUEST['x_cust_id'] != ''){
		$attendee_id=$_REQUEST['x_cust_id'];
	}
	if ($attendee_id ==""){
		echo "ID not supplied.";
	}else{
		$email_subject = $org_options['payment_subject'];
		$email_body = $org_options['payment_message'];
		$default_mail=$org_options['default_mail'];
		$Organization =$org_options['organization'];
		$contact =$org_options['contact_email'];
		$email_before_payment = $org_options['email_before_payment'];
		
		//Load the payment gateways
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php")){
			if ((get_option('events_paypal_active') == 'true' || get_option('event_espresso_payment_gateway') == NULL) && $_REQUEST['x_cust_id'] == '' ){
				require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/paypal_ipn.php"); //Load PayPal ipn
			}elseif (get_option('events_authnet_active') == 'true' && $_REQUEST['x_cust_id'] != ''){
				require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet_ipn.php"); //Load Authorize.net ipn
			}else{
				//Send an email if payment gaeways are not found.
				$subject = __('Problem With Your Website Payment IPN','event_espresso');
				$body = __('The IPN for ' . $Organization. ' at ' . get_option('siteurl') . ' is not working properly or has not been setup correctly.\r\nDate/time' . date('g:i A'),'event_espresso');
				wp_mail($contact, $subject, $body);
			}
			
		}else{
			//Send an email if the payemnt gateway is not set up.
			$subject = __('Website Payment IPN Not Setup','event_espresso');
			$body = __('The IPN for ' . $Organization. ' at ' . get_option('siteurl') . ' has not been properly setup and is not working.\r\nDate/time' . date('g:i A'),'event_espresso');
			wp_mail($contact, $subject, $body);
		}
		
		//Send payment confirmation emails
		event_espresso_send_payment_notification(array('attendee_id'=>$attendee_id));
			
		//Send the email confirmation 
		//@params $attendee_id, $send_admin_email, $send_attendee_email
		if ($email_before_payment == 'N' || $email_before_payment == '' ){
			//event_espresso_email_confirmations($attendee_id, 'true', 'true' );
			event_espresso_email_confirmations(array('attendee_id' => $attendee_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
		}
		//event_espresso_email_confirmations($attendee_id, 'true', 'true' );
	}
}