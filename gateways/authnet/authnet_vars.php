<?php

function espresso_display_authnet($payment_data) {
	extract($payment_data);
// Setup class
	include_once ('Authorize.php');

	global $org_options, $wpdb;
	$myAuthorize = new Espresso_Authorize(); // initiate an instance of the class
	echo '<!--Event Espresso Authorize.net Gateway Version ' . $myAuthorize->gateway_version . '-->';
	$authnet_settings = get_option('event_espresso_authnet_settings');
	$authnet_login_id = empty($authnet_settings['authnet_login_id']) ? '' : $authnet_settings['authnet_login_id'];
	$authnet_transaction_key = empty($authnet_settings['authnet_transaction_key']) ? '' : $authnet_settings['authnet_transaction_key'];
	$image_url = empty($authnet_settings['image_url']) ? '' : $authnet_settings['image_url'];
	$use_sandbox = $authnet_settings['use_sandbox'];
	$use_testmode = $authnet_settings['test_transactions'];
	if ($use_testmode) {
		// Enable test mode if needed
		$myAuthorize->enableTestMode();
	}
	if ($use_sandbox) {
		// Enable test mode if needed
		$myAuthorize->useTestServer();
	}

	$quantity = !empty($quantity) ? $quantity : espresso_count_attendees_for_registration($attendee_id);

	$myAuthorize->setUserInfo($authnet_login_id, $authnet_transaction_key);

//if in debug mode, use authorize.net's sandbox parnter id; otherwise use the Event Espresso partner id
	$myAuthorize->addField('x_solution_id', $use_sandbox ? 'AAA100302' : 'AAA105363');
	$myAuthorize->addField('x_Relay_URL',espresso_build_gateway_url('return_url', $payment_data, 'authnet') );
	$myAuthorize->addField('x_Description', espresso_sanitize_gateway_value( stripslashes_deep( $event_name ) . ' | ' . __('Reg. ID:', 'event_espresso') . ' ' . $attendee_id . ' | ' . __('Name:', 'event_espresso') . ' ' . stripslashes_deep($fname . ' ' . $lname) . ' | ' . __('Total Registrants:', 'event_espresso') . ' ' . $quantity ) );
	$myAuthorize->addField('x_Amount', number_format($event_cost, 2));
	$myAuthorize->addField('x_Logo_URL', $image_url);
	$myAuthorize->addField('x_Invoice_num', 'au-' . event_espresso_session_id());
//Post variables
	$myAuthorize->addField('x_cust_id', $attendee_id);
	$myAuthorize->addField('x_first_name', $fname);
	$myAuthorize->addField('x_last_name', $lname);

	$myAuthorize->addField('x_Email', $attendee_email);
	$myAuthorize->addField('x_Address', $address);
	$myAuthorize->addField('x_City', $city);
	$myAuthorize->addField('x_State', $state);
	$myAuthorize->addField('x_Zip', $zip);
	
	$sql = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'";
	$session_id = $wpdb->get_var($sql);
	$sql = "SELECT a.final_price, a.quantity, ed.event_name, a.price_option, a.fname, a.lname FROM " . EVENTS_ATTENDEE_TABLE . " a JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$sql .= " WHERE attendee_session='" . $session_id . "' ORDER BY a.id ASC";
	$items = $wpdb->get_results($sql);
	foreach ($items as $key=>$item) {
		$item_num=$key+1;
		$item_event_name = espresso_sanitize_gateway_value( $item->event_name );
		$item_fname = espresso_sanitize_gateway_value( $item->fname );
		$item_lname = espresso_sanitize_gateway_value( $item->lname );
		$item_price_option = espresso_sanitize_gateway_value ($item->price_option );
		$myAuthorize->addLineItem(
				$item_num,
				( strlen($item_event_name) > 30 ? substr_replace($item_event_name, '', 30) : $item_event_name ),
				substr_replace($item_price_option . ' for ' . $item_event_name . '. Attendee: '. $item_fname . ' ' . $item_lname, '', 255),
				$item->quantity,
				$item->final_price,
				FALSE
		);
	}
	


//Enable this function if you want to send payment notification before the person has paid.
//This function is copied on the payment processing page
//event_espresso_send_payment_notification($attendee_id, $txn_id, $amount_pd);
//Decide if you want to auto redirect to your payment website or display a payment button.
	if (!empty($authnet_settings['bypass_payment_page']) && $authnet_settings['bypass_payment_page'] == 'Y') {
		$myAuthorize->submitPayment(); //Enable auto redirect to payment site
	} else {
		$button_url = espresso_select_button_for_display($authnet_settings['button_url'], "authnet/btn_cc_vmad.gif");
		$myAuthorize->submitButton($button_url, 'authnet'); //Display payment button
	}

	if ($use_sandbox) {
		echo '<p>Test credit card # 4007000000027</p>';
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myAuthorize->dump_fields(); // for debugging, output a table of all the fields
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_authnet');
