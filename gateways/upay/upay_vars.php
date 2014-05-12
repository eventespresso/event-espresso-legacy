<?php

function espresso_display_upay($payment_data) {
	global $wpdb;
	extract($payment_data);
	var_dump($payment_data);
	include_once ('EE_uPay.php');
	$upay_settings = get_option('event_espresso_upay_settings');

	do_action('action_hook_espresso_use_add_on_functions');
	$myPaypal = new EE_uPay();
	
	//printr( $myPaypal, '$myPaypal  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	$myPaypal->gatewayUrl = $upay_settings['upay_site_url'];
	$myPaypal->addField('UPAY_SITE_ID', $upay_settings['upay_site_id']);
	$myPaypal->addField('BILL_NAME', $payment_data['fname'] . $payment_data['lname']);
	$myPaypal->addField('BILL_EMAIL_ADDRESS', $payment_data['email']);
	$myPaypal->addField('BILL_STREET1', $payment_data['address1']);
	$myPaypal->addField('BILL_STREET2', $payment_data['address2']);
	$myPaypal->addField('BILL_CITY', $payment_data['city']);
	$myPaypal->addField('BILL_STATE', $payment_data['state']);
	$myPaypal->addField('BILL_POSTAL_CODE', $payment_data['zip']);
	$myPaypal->addField('BILL_COUNTRY',$payment_data['country']);
	$myPaypal->addField(('EXT_TRANS_ID'), $payment_data['registration_id']);
	$myPaypal->addField('EXT_TRANS_ID_LABEL', __("Registration ID", 'event_espresso'));
	$myPaypal->addField('AMT', number_format($payment_data['event_cost'],2));
	$return_url = espresso_build_gateway_url('return_url', array('attendee_id'=>$payment_data['attendee_id'],'registration_id'=>$payment_data['registration_id']), 'upay');
	$myPaypal->addField('SUCCESS_LINK', $return_url);
	$myPaypal->addField('ERROR_LINK',$return_url);
	$myPaypal->addField('CANCEL_LINK', espresso_build_gateway_url('cancel_return', array('attendee_id'=>$payment_data['attendee_id'], 'registration_id'=>$payment_data['registration_id']), 'upay'));
	
//	$myPaypal->addField('charset', "utf-8");
//	$myPaypal->addField('return', $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id . '&type=upay');
//	$myPaypal->addField('cancel_return', $home . '/?page_id=' . $org_options['cancel_return']);
//	$myPaypal->addField('notify_url', $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=upay');
//	$event_name = $wpdb->get_var('SELECT event_name FROM ' . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
//	$myPaypal->addField('cmd', '_cart');
//	$myPaypal->addField('upload', '1');
//
//	$myPaypal->addField('currency_code', $upay_cur);
//	$myPaypal->addField('image_url', empty($upay_settings['image_url']) ? '' : $upay_settings['image_url']);
//	$myPaypal->addField('no_shipping ', $no_shipping);
//	$myPaypal->addField('first_name', $fname);
//	$myPaypal->addField('last_name', $lname);
//	$myPaypal->addField('email', $attendee_email);
//	$myPaypal->addField('address1', $address);
//	$myPaypal->addField('city', $city);
//	$myPaypal->addField('state', $state);
//	$myPaypal->addField('zip', $zip);

	if (!empty($upay_settings['bypass_payment_page']) && $upay_settings['bypass_payment_page'] == 'Y') {
		$myPaypal->submitPayment();
	} else {
		$button_url = espresso_select_button_for_display($upay_settings['button_url'], "upay/btn_stdCheckout2.gif");
		$myPaypal->submitButton($button_url, 'upay');
	}

	if ($upay_settings['debug_mode']) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('uPay Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myPaypal->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_upay');
