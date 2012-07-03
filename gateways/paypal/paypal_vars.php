<?php
// Setup class
			include_once ('Paypal.php');
			echo '<!--Event Espresso PayPal Gateway Version ' . $paypal_gateway_version . '-->';
			$myPaypal = new Paypal();// initiate an instance of the class
			global $org_options;
			//global $attendee_id;
			$paypal_settings = get_option('event_espresso_paypal_settings');
				$paypal_id = $paypal_settings['paypal_id'];
				//$image_url = $paypal_settings['button_url'];
				$paypal_cur = $paypal_settings['currency_format'];
				$no_shipping = $paypal_settings['no_shipping'];
				$use_sandbox = $paypal_settings['use_sandbox'];
			if ($use_sandbox == 1) {
				// Enable test mode if needed
				$myPaypal->enableTestMode();
			}
			$quantity = $quantity > 0 ? $quantity: espresso_count_attendees_for_registration($attendee_id);
			$myPaypal->addField('business', $paypal_id);
			$myPaypal->addField('return', get_option('siteurl').'/?page_id='.$org_options['return_url']);
			$myPaypal->addField('cancel_return', get_option('siteurl').'/?page_id='.$org_options['cancel_return']);
			$myPaypal->addField('notify_url', get_option('siteurl').'/?page_id='.$org_options['notify_url'].'&id='.$attendee_id.'&event_id='.$event_id.'&attendee_action=post_payment&form_action=payment');
			//$myPaypal->addField('item_name', $event_name . ' | '.__('Reg. ID:','event_espresso').' '.$attendee_id. ' | '.__('Name:','event_espresso').' '. $attendee_name .' | '.__('Total Registrants:','event_espresso').' '.$num_people);
			$myPaypal->addField('item_name', stripslashes_deep($event_name) . ' | '.__('Name:','event_espresso').' '. stripslashes_deep($fname . ' ' . $lname) .' | '.__('Registrant Email:','event_espresso').' '.$attendee_email.' | '.__('Total Registrants:','event_espresso').' '. $quantity);
			$myPaypal->addField('amount', number_format($event_cost,2, '.', ''));
			$myPaypal->addField('currency_code', $paypal_cur);
			$myPaypal->addField('image_url', $image_url);
			$myPaypal->addField('no_shipping ', $no_shipping );
							  
			//Post variables
			$myPaypal->addField('first_name', $fname);
			$myPaypal->addField('last_name', $lname);
			$myPaypal->addField('email', $attendee_email);
			$myPaypal->addField('address1', $address);
			$myPaypal->addField('city', $city);
			$myPaypal->addField('state', $state);
			$myPaypal->addField('zip', $zip);
				
			
				//Enable this function if you want to send payment notification before the person has paid. 
				//This function is copied on the payment processing page
				//event_espresso_send_payment_notification($attendee_id, $txn_id, $amount_pd);
				
				//Decide if you want to auto redirect to your payment website or display a payment button.
				if ($paypal_settings['bypass_payment_page'] == 'Y'){
					$myPaypal->submitPayment();//Enable auto redirect to payment site
				}else{
					if ($paypal_settings['button_url']==''){
						if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/btn_stdCheckout2.gif")){
							$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/paypal/btn_stdCheckout2.gif";
						}else{
							$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/paypal/btn_stdCheckout2.gif";
						}
					}else{
						$button_url = $paypal_settings['button_url'];
					}
					$myPaypal->submitButton($button_url, 'paypal');//Display payment button
				}
				
			if ($use_sandbox == true) {
				echo '<h3 style="color:#ff0000;" title="Payments will not be processed">'.__('Debug Mode Is Turned On','event_espresso').'</h3>';
				$myPaypal->dump_fields(); // for debugging, output a table of all the fields
			}