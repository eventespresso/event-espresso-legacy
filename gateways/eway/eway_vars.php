<?php

function espresso_display_eway($payment_data) {
	extract($payment_data);
// Setup class
	include_once ('Eway.php');
	echo '<!-- Event Espresso eWay Gateway Version ' . $eway_gateway_version . '-->';
	echo '
 <div id="eway-payment-option-dv" class="off-site-payment-gateway payment-option-dv">
	<img class="off-site-payment-gateway-img" width="16" height="16" src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/icons/external-link.png" alt="click to visit this payment gateway">';
	$myeway = new Espresso_Eway(); // initiate an instance of the class
	global $org_options;
//global $attendee_id;
	$eway_settings = get_option('event_espresso_eway_settings');
	$eway_id = $eway_settings['eway_id'];
	$eway_username = $eway_settings['eway_username'];
//$image_url = $eway_settings['button_url'];
	$eway_cur = $eway_settings['currency_format'];
	$use_sandbox = $eway_settings['use_sandbox'];

	$quantity = isset($quantity) && $quantity > 0 ? $quantity : espresso_count_attendees_for_registration($attendee_id);
	if ($use_sandbox) {
		// Enable test mode if needed
		$myeway->enableTestMode();
		$myeway->addField('CustomerID', '87654321');
		$myeway->addField('UserName', 'TestAccount');
	} else {
		$myeway->addField('CustomerID', $eway_id);
		$myeway->addField('UserName', $eway_username);
	}
	$myeway->addField('Amount', number_format($event_cost, 2, '.', ''));
	$myeway->addField('Currency', $eway_cur);
	$myeway->addField('PageTitle', '');
	$myeway->addField('PageDescription', '');
	$myeway->addField('PageFooter', '');
	$myeway->addField('Language', '');
	$myeway->addField('CompanyName', str_replace("&", "%26", $org_options['organization']));
	$myeway->addField('CustomerFirstName', $fname);
	$myeway->addField('CustomerLastName', $lname);
	$myeway->addField('CustomerAddress', $address);
	$myeway->addField('CustomerCity', $city);
	$myeway->addField('CustomerState', $state);
	$myeway->addField('CustomerPostCode', $zip);
	$myeway->addField('CustomerCountry', '');
	$myeway->addField('CustomerEmail', $attendee_email);
	$myeway->addField('CustomerPhone', $phone);
	$myeway->addField('InvoiceDescription', stripslashes_deep($event_name) . ' | ' . __('Name:', 'event_espresso') . ' ' . stripslashes_deep($fname . ' ' . $lname) . ' | ' . __('Registrant Email:', 'event_espresso') . ' ' . $attendee_email . ' | ' . __('Total Registrants:', 'event_espresso') . ' ' . $quantity);
	$myeway->addField('CancelURL', str_replace("&", "%26", get_permalink($org_options['cancel_return'])));

	$return_url = str_replace("&","%26", espresso_build_gateway_url('return_url', $payment_data, 'eway'));
	$myeway->addField('ReturnURL', $return_url);
	$myeway->addField('CompanyLogo', $eway_settings['image_url']);
	$myeway->addField('PageBanner', '');
	$myeway->addField('MerchantReference', '');
	$myeway->addField('MerchantInvoice', '');
	$myeway->addField('MerchantOption1', '');
	$myeway->addField('MerchantOption2', '');
	$myeway->addField('MerchantOption3', '');
	$myeway->addField('ModifiableCustomerDetails', 'false');

	if ($eway_settings['bypass_payment_page'] == 'Y') {
		$myeway->submitPayment(); //Enable auto redirect to payment site
	} else {
		$button_url = espresso_select_button_for_display($eway_settings['button_url'], "eway/eway-logo.png");
		$myeway->submitButton($button_url, 'eway'); //Display payment button
		wp_deregister_script('jquery.validate.pack');
	}
	if ($use_sandbox) {
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myeway->dump_fields(); // for debugging, output a table of all the fields
	}

	echo '
</div>';
	
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_eway');
