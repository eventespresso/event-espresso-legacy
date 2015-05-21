<?php

function espresso_display_firstdata_e4($payment_data) {
	extract($payment_data);
// Setup class
	include_once ('FirstDataE4.php');

	//sanatize values for gateways
	$e4_event_name = espresso_sanitize_gateway_value( $event_name );
	$e4_fname = espresso_sanitize_gateway_value( $fname );
	$e4_lname = espresso_sanitize_gateway_value( $lname );

	global $org_options;
	$myE4 = new Espresso_E4(); // initiate an instance of the class
	echo '<!--Event Espresso E4.com Gateway Version ' . $myE4->gateway_version . '-->';
	$firstdata_e4_settings = get_option('event_espresso_firstdata_e4_settings');
	$firstdata_e4_login_id = empty($firstdata_e4_settings['firstdata_e4_login_id']) ? '' : $firstdata_e4_settings['firstdata_e4_login_id'];
	$firstdata_e4_transaction_key = empty($firstdata_e4_settings['firstdata_e4_transaction_key']) ? '' : $firstdata_e4_settings['firstdata_e4_transaction_key'];
	$button_type = empty($firstdata_e4_settings['button_type']) ? '' : $firstdata_e4_settings['button_type'];
	$image_url = empty($firstdata_e4_settings['image_url']) ? '' : $firstdata_e4_settings['image_url'];
	$use_sandbox = $firstdata_e4_settings['use_sandbox'];
	$use_testmode = $firstdata_e4_settings['test_transactions'];
	if ($use_testmode == true) {
		// Enable test mode if needed
		$myE4->enableTestMode();
	}
	if ($use_sandbox) {
		// Enable test mode if needed
		$myE4->useTestServer();
	}

	$quantity = isset($quantity) && $quantity > 0 ? $quantity : espresso_count_attendees_for_registration($attendee_id);

	$myE4->setUserInfo($firstdata_e4_login_id, $firstdata_e4_transaction_key);
	$myE4->addField('x_amount', number_format($event_cost, 2));
	$myE4->addField('x_show_form', 'PAYMENT_FORM');
	$myE4->addField('x_reference_3', $registration_id . ' FDe4');
	$myE4->addField('x_relay_response', 'TRUE');
	if ($firstdata_e4_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	$myE4->addField('x_relay_url', $home . '/?type=firstdata_e4&page_id=' . $org_options['return_url']);
	$myE4->addField('x_description', stripslashes_deep($e4_event_name) . ' ' . __('Reg. ID:', 'event_espresso') . ' ' . $attendee_id . ' ' . __('Name:', 'event_espresso') . ' ' . stripslashes_deep($e4_fname . ' ' . $e4_lname) . ' ' . __('Total Registrants:', 'event_espresso') . ' ' . $quantity);
	$myE4->addField('x_logo_url', $image_url);
	//$myE4->addField('x_invoice_num', event_espresso_session_id());
//Post variables
	$myE4->addField('x_cust_id', $attendee_id);

	$myE4->addField('x_first_name', $e4_fname);
	$myE4->addField('x_last_name', $e4_lname);
	$myE4->addField('x_email', $attendee_email);
	$myE4->addField('x_address', $address);
	$myE4->addField('x_city', $city);
	$myE4->addField('x_state', $state);
	$myE4->addField('x_zip', $zip);
	$myE4->addField('x_fp_sequence', $attendee_id);



//Enable this function if you want to send payment notification before the person has paid.
//This function is copied on the payment processing page
//event_espresso_send_payment_notification($attendee_id, $txn_id, $amount_pd);
//Decide if you want to auto redirect to your payment website or display a payment button.
	if (!empty($firstdata_e4_settings['bypass_payment_page']) && $firstdata_e4_settings['bypass_payment_page'] == 'Y') {
		$myE4->submitPayment(); //Enable auto redirect to payment site
	} else {
		$firstdata_e4_settings['button_url'] = espresso_select_button_for_display($firstdata_e4_settings['button_url'], "firstdata_e4/firstdata-logo.png");
		$myE4->submitButton($firstdata_e4_settings['button_url'], 'firstdata_e4'); //Display payment button
	}


	if ($use_sandbox) {
		echo '<p>Test credit card # 4007000000027</p>';
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myE4->dump_fields(); // for debugging, output a table of all the fields
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_firstdata_e4');
