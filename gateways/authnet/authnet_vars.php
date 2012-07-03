<?php
// Setup class
			include_once ('Authorize.php');
			echo '<!--Advanced Events Registration Authorize.net Gateway Version ' . $authnet_gateway_version . '-->';
			
			$myAuthorize = new Authorize();// initiate an instance of the class
		
			$authnet_settings = get_option('event_espresso_authnet_settings');
      $authnet_login_id = $authnet_settings['authnet_login_id'];
      $authnet_transaction_key = $authnet_settings['authnet_transaction_key'];
      $use_sandbox = $authnet_settings['use_sandbox'];
      $button_type = $authnet_settings['button_type'];
      $button_url = $authnet_settings['button_url'];
      $image_url = $authnet_settings['image_url'];
			
			if ($use_sandbox == 1) {
				// Enable test mode if needed
				$myAuthorize->enableTestMode();
			}
			$myAuthorize->setUserInfo($authnet_login_id, $authnet_transaction_key);
			
			$myAuthorize->addField('x_Relay_URL', get_option('siteurl').'/?page_id='.$notify_url);
			$myAuthorize->addField('x_Description', $event_name . ' | '.__('Reg. ID:','event_espresso').' '.$attendee_id. ' | '.__('Name:','event_espresso').' '. $attendee_name .' | '.__('Total Registrants:','event_espresso').' '.$num_people);
			$myAuthorize->addField('x_Amount', number_format($event_cost,2));
			$myAuthorize->addField('x_Logo_URL', $image_url);
			$myAuthorize->addField('x_Invoice_num', event_espresso_session_id());
			//Post variables
			$myAuthorize->addField('x_Cust_ID', $attendee_id);
			$myAuthorize->addField('x_first_name', $attendee_first);
			$myAuthorize->addField('x_last_name', $attendee_last);

			$myAuthorize->addField('x_Email', $attendee_email);
			$myAuthorize->addField('x_Address', $attendee_address);
			$myAuthorize->addField('x_City', $attendee_city);
			$myAuthorize->addField('x_State', $attendee_state);
			$myAuthorize->addField('x_Zip', $attendee_zip);
				
			
				//Enable this function if you want to send payment notification before the person has paid. 
				//This function is copied on the payment processing page
				//event_espresso_send_payment_notification($attendee_id, $txn_id, $amount_pd);
				
				//Decide if you want to auto redirect to your payment website or display a payment button.
				if ($authnet_settings['bypass_payment_page'] == 'Y'){
					$myAuthorize->submitPayment();//Enable auto redirect to payment site
				}else{
					if ($button_url==''){
						$button_url = EVENT_ESPRESSO_GATEWAY_URL . "authnet/btn_cc_vmad.gif";
					}
					$myAuthorize->submitButton($button_url, 'authnet');//Display payment button
				}
				
			if ($use_sandbox == true) {
				echo '<p>Test credit card # 4007000000027</p>';
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">'.__('Debug Mode Is Turned On','event_espresso').'</h3>';
				$myAuthorize->dump_fields(); // for debugging, output a table of all the fields
			}