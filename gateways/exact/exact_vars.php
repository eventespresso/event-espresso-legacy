<?php

function espresso_display_exact($payment_data) {
	extract($payment_data);
// Setup class
	include_once ('Exact.php');

	//sanatize values for gateways
	$exact_event_name = espresso_sanitize_gateway_value( $event_name );
	$exact_fname = espresso_sanitize_gateway_value( $fname );
	$exact_lname = espresso_sanitize_gateway_value( $lname );

	global $org_options;
	$myExact = new Espresso_Exact(); // initiate an instance of the class
	echo '<!--Event Espresso Exact.com Gateway Version ' . $myExact->gateway_version . '-->';
	$exact_settings = get_option('event_espresso_exact_settings');
	$exact_login_id = empty($exact_settings['exact_login_id']) ? '' : $exact_settings['exact_login_id'];
	$exact_transaction_key = empty($exact_settings['exact_transaction_key']) ? '' : $exact_settings['exact_transaction_key'];
	$button_type = empty($exact_settings['button_type']) ? '' : $exact_settings['button_type'];
//$button_url = $exact_settings['button_url'];
	$image_url = empty($exact_settings['image_url']) ? '' : $exact_settings['image_url'];
	$use_sandbox = $exact_settings['use_sandbox'];
	$use_testmode = $exact_settings['test_transactions'];
	if ($use_testmode == true) {
		// Enable test mode if needed
		$myExact->enableTestMode();
	}
	if ($use_sandbox) {
		// Enable test mode if needed
		$myExact->useTestServer();
	}

	$quantity = isset($quantity) && $quantity > 0 ? $quantity : espresso_count_attendees_for_registration($attendee_id);

	$myExact->setUserInfo($exact_login_id, $exact_transaction_key);
	$myExact->addField('x_amount', number_format($event_cost, 2));
	$myExact->addField('x_show_form', 'PAYMENT_FORM');
	$myExact->addField('registration_id', $registration_id );
	$myExact->addField('x_relay_response', 'TRUE');
	if ($exact_settings['force_ssl_return']) {
		$home = str_replace("http://", "https://", home_url());
	} else {
		$home = home_url();
	}
	$myExact->addField('x_relay_url', $home . '/?type=exact&page_id=' . $org_options['return_url']);
	$myExact->addField('x_description', stripslashes_deep($exact_event_name) . ' | ' . __('Reg. ID:', 'event_espresso') . ' ' . $attendee_id . ' | ' . __('Name:', 'event_espresso') . ' ' . stripslashes_deep($exact_fname . ' ' . $exact_lname) . ' | ' . __('Total Registrants:', 'event_espresso') . ' ' . $quantity);
	$myExact->addField('x_logo_url', $image_url);
	$myExact->addField('x_invoice_num', event_espresso_session_id());
//Post variables
	$myExact->addField('x_cust_id', $attendee_id);

	$myExact->addField('x_first_name', $exact_fname);
	$myExact->addField('x_last_name', $exact_lname);
	$myExact->addField('x_email', $attendee_email);
	$myExact->addField('x_address', $address);
	$myExact->addField('x_city', $city);
	$myExact->addField('x_state', $state);
	$myExact->addField('x_zip', $zip);
	$myExact->addField('x_fp_sequence', $attendee_id);



//Enable this function if you want to send payment notification before the person has paid.
//This function is copied on the payment processing page
//event_espresso_send_payment_notification($attendee_id, $txn_id, $amount_pd);
//Decide if you want to auto redirect to your payment website or display a payment button.
	if (!empty($exact_settings['bypass_payment_page']) && $exact_settings['bypass_payment_page'] == 'Y') {
		$myExact->submitPayment(); //Enable auto redirect to payment site
	} else {
		$button_url = espresso_select_button_for_display($exact_settings['button_url'], "exact/exact-logo.png");
		$myExact->submitButton($button_url, 'exact'); //Display payment button
	}

	if ($use_sandbox) {
		echo '<p>Test credit card # 4007000000027</p>';
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myExact->dump_fields(); // for debugging, output a table of all the fields
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_exact');
