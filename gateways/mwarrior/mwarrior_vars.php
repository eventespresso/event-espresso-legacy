<?php

function espresso_display_mwarrior($payment_data) {
	extract($payment_data);
// Setup class
	include_once ('Mwarrior.php');
	$mwarrior = new Espresso_Mwarrior(); // initiate an instance of the class
	echo '<!--Event Espresso Merchant Warrior Gateway Version ' . $mwarrior->gateway_version . '-->';
	global $org_options;
//global $attendee_id;
	$mwarrior_settings = get_option('event_espresso_mwarrior_settings');
	$mwarrior_id = empty($mwarrior_settings['mwarrior_id']) ? '' : $mwarrior_settings['mwarrior_id'];
	$mwarrior_apikey = empty($mwarrior_settings['mwarrior_apikey']) ? '' : $mwarrior_settings['mwarrior_apikey'];
	$mwarrior_passphrase = empty($mwarrior_settings['mwarrior_passphrase']) ? '' : $mwarrior_settings['mwarrior_passphrase'];
	$mwarrior_cur = empty($mwarrior_settings['currency_format']) ? '' : $mwarrior_settings['currency_format'];
	$logo_url = empty($mwarrior_settings['image_url']) ? '' : $mwarrior_settings['image_url'];
	$use_sandbox = $mwarrior_settings['use_sandbox'];
	if ($use_sandbox) {
		// Enable test mode if needed
		$mwarrior->enableTestMode();
	}
	$salt = $mwarrior->_generateHashSalt();

	$mwarrior->setMerchantInfo($mwarrior_id, $mwarrior_apikey, $mwarrior_passphrase);

//Post variables
	$mwarrior->addField('method', 'processCard');
	$mwarrior->addField('merchantUUID', $mwarrior_id);
	$mwarrior->addField('apiKey', $mwarrior_apikey);

	$mwarrior->addField('customerName', $fname . " " . $lname);
	$mwarrior->addField('customerEmail', $attendee_email);
	$mwarrior->addField('customerAddress', $address);
	$mwarrior->addField('customerCity', $city);
	$mwarrior->addField('customerPostCode', $zip);
	$mwarrior->addField('customerState', $state);
	$mwarrior->addField('customerCountry', empty($country) ? '' : $country);
	$mwarrior->addField('customerPhone', $phone);
	$mwarrior->addField('transactionProduct', stripslashes_deep($event_name));
	$mwarrior->addField('transactionAmount', number_format($event_cost, 2, '.', ''));
	$mwarrior->addField('transactionCurrency', $mwarrior_cur);

	$mwarrior->addField('logoURL', $logo_url);
	$returnURL = espresso_build_gateway_url('return_url', $payment_data, 'mwarrior');
	//notify is the thank yuo page... is that right? mike july 19 2013, editing code...
	$notifyURL = espresso_build_gateway_url('return_url', $payment_data, 'mwarrior');
	
	$mwarrior->addField('returnURL',$returnURL);
	$mwarrior->addField('notifyURL',$notifyURL);
//	$mwarrior->addField('returnURL', $home . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=mwarrior');
//$mwarrior->addField('cancel_return', $home.'/?page_id='.$org_options['cancel_return']);
//	$mwarrior->addField('notifyURL', $home . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=mwarrior');
	$mwarrior->addField('urlHash', $mwarrior->_calculateHash($mwarrior->fields, "url"));
	$mwarrior->addField('hash', $mwarrior->_calculateHash($mwarrior->fields, "transaction"));
	$mwarrior->addField('hashSalt', $salt);

//Enable this function if you want to send payment notification before the person has paid.
//This function is copied on the payment processing page
//event_espresso_send_payment_notification($attendee_id, $txn_id, $amount_pd);
//Decide if you want to auto redirect to your payment website or display a payment button.
	if (!empty($mwarrior_settings['bypass_payment_page']) && $mwarrior_settings['bypass_payment_page'] == 'Y') {
		$mwarrior->submitPayment(); //Enable auto redirect to payment site
	} else {
		$button_url = espresso_select_button_for_display($mwarrior_settings['button_url'], "mwarrior/mwarrior-logo.png");
		$mwarrior->submitButton($button_url, 'mwarrior'); //Display payment button
	}

// Store the salt temporarily in the database
	$mwarrior->_storeHashSalt($salt, espresso_registration_id($attendee_id));

	if ($use_sandbox) {
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$mwarrior->dump_fields(); // for debugging, output a table of all the fields
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_mwarrior');
