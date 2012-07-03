<?php
//ob_start();
//Payment processing - Used for onsite payment processing. Used with the [ESPRESSO_TXN_PAGE] tag
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
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/index.php") || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/index.php")){
			//Load Plug N Pay IPN
			if ((get_option('events_plugnpay_active') == 'true' ) && $_REQUEST['easycart'] == '1' ){
				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/plugnpay_ipn.php")){
					//Moved files
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/plugnpay/plugnpay_ipn.php");
				}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/plugnpay_ipn.php")){
					//Default files
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/plugnpay/plugnpay_ipn.php");
				}
			//Load iDEAL (Mollie)
			}elseif ((get_option('events_ideal_active') == 'true' || get_option('event_espresso_payment_gateway') == NULL) && $_REQUEST['ideal'] == 1 && $_REQUEST['id'] != '' ){
                                //Ideal works a little differently.
                                //on the EE payment page, there is a dropdown with a list of banks that is pulled from Mollie.
                                //The customer selects the bank, submits and is redirected to the payment page
                                //Once returns, there is a transaction_id in the variable

                                $ideal_folder = EVENT_ESPRESSO_PLUGINFULLPATH."gateways/ideal/";

				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/ideal/ideal_vars.php")){
					//Moved files
					$ideal_folder = EVENT_ESPRESSO_GATEWAY_DIR."/ideal/";
                                }
                                //if the transaction id is not set, then they selected a bank and clicked on the button on ee payment page
                                if (!isset($_GET['transaction_id'])) {

                                        require_once($ideal_folder . "ideal_vars.php");
                                }
                                else
                                {
                                        require_once($ideal_folder . "report.php");
                                }


			//Load PayPal IPN
			}elseif ((get_option('events_paypal_pro_active') == 'true' || get_option('event_espresso_payment_gateway') == NULL) && $_REQUEST['paypal_pro'] == 'true' && $_REQUEST['id'] != '' ){

				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal-pro/DoDirectPayment.php")){
					//Moved files
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal-pro/DoDirectPayment.php");
				}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal-pro/DoDirectPayment.php")){
					//Default files
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal-pro/DoDirectPayment.php");
				}
                        //Process Firstdata
			}elseif ((get_option('events_firstdata_active') == 'true' || get_option('event_espresso_payment_gateway') == NULL) && $_REQUEST['firstdata'] == '1' && $_REQUEST['id'] != '' ){

				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/Firstdata.php")){
					//Moved files
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/firstdata/Firstdata.php");
				}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/firstdata/Firstdata.php")){
					//Default files
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/firstdata/Firstdata.php");
				}
			//Load Authorize.net AIM IPN
			}elseif (get_option('events_authnet_aim_active') == 'true' && ($_REQUEST['x_cust_id'] != '' && $_REQUEST['authnet_aim'] == 'true')){
				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/aim/aim_ipn.php")){
					//Moved files
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/aim/aim_ipn.php");
				}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/aim/aim_ipn.php")){
					//Default files
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/aim/aim_ipn.php");
				}
			//Load Authorize.net SIM IPN
			}elseif (get_option('events_authnet_active') == 'true' && $_REQUEST['x_cust_id'] != ''){
				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet_ipn.php")){
					//Moved files
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/authnet/authnet_ipn.php");
				}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/authnet/authnet_ipn.php")){
					//Default files
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/authnet/authnet_ipn.php");
				}
			}elseif ((get_option('events_paypal_active') == 'true' || get_option('event_espresso_payment_gateway') == NULL) && $_REQUEST['x_cust_id'] == '' ){
				if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/paypal_ipn.php")){
					//Moved files
					require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/paypal_ipn.php");
				}elseif (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal/paypal_ipn.php")){
					//Default files
					require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/paypal/paypal_ipn.php");
				}
                        //Process PayPal PRO
			}else{
				//Send an email if payment gaeways are not found.
				$subject = __('Problem With Your Website Payment IPN','event_espresso');
				$body = __('The IPN for ' . $Organization. ' at ' . home_url() . ' is not working properly or has not been setup correctly. Date/time' . date('g:i A'),'event_espresso');
				wp_mail($contact, $subject, $body);
			}
			
		}else{
			//Send an email if the payemnt gateway is not set up.
			$subject = __('Website Payment IPN Not Setup','event_espresso');
			$body = __('The IPN for ' . $Organization. ' at ' . home_url() . ' has not been properly setup and is not working. Date/time' . date('g:i A'),'event_espresso');
			wp_mail($contact, $subject, $body);
		}
		
		

		//Sends users to the thank you page if they try to access this page directly
		if ($payment_status == 'Completed'){

                        $espresso_session_id = $_SESSION['espresso_session_id'];
                        $registration_id = espresso_registration_id( $attendee_id );
                        //Adding this query for multi event
                        //Since the registration id is used for marking the event as paid in the above files,
                        //using this query to make sure that other events in the cart are also marked as paid

                        //At this point the session id has changed
                        //find the old session_id based on reg id

                        $s = $wpdb->get_row("SELECT attendee_session, txn_id, txn_type, payment_date FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='$registration_id' ORDER BY id LIMIT 1 ");

                        $old_session_id = $s->attendee_session;

                        //update the records
                        $SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_id='$s->txn_id', txn_type='$s->txn_type', payment_date='$s->payment_date' WHERE attendee_session='$old_session_id'";

                        $wpdb->query($SQL);

                        
			//Send payment confirmation emails
			event_espresso_send_payment_notification(array('attendee_id'=>$attendee_id));
				
			//Send the email confirmation 
			//@params $attendee_id, $send_admin_email, $send_attendee_email
			if ($email_before_payment == 'N'){
				event_espresso_email_confirmations(array('attendee_id' => $attendee_id,'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
			}
			wp_redirect(home_url().'/?page_id='.$org_options['return_url'] . "&attendee_id=$attendee_id&espresso_session_id=$espresso_session_id&registration_id=$registration_id" );
			exit;
		}
	}
	
}