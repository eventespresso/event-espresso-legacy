<?php

function espresso_display_wepay($payment_data) {
	extract($payment_data);
// Setup class
	if (empty($event_name)) $event_name = "Event number #" . $event_id;
	include_once ('Wepay.php');
	echo '<!-- Event Espresso WePay Gateway Version ' . Espresso_Wepay::$version . '-->';
	$wepay_settings = get_option('event_espresso_wepay_settings');
	global $org_options;
	if ($wepay_settings['use_sandbox']) {
		Espresso_Wepay::useStaging($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	} else {
		Espresso_Wepay::useProduction($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	}
	$quantity = isset($quantity) && $quantity > 0 ? $quantity : espresso_count_attendees_for_registration($attendee_id);
	$fields['account_id'] = $wepay_settings['account_id'];
	$fields['short_description'] = stripslashes_deep($event_name);
	$fields['long_description'] = stripslashes_deep($event_name) . ' | ' . __('Name:', 'event_espresso') . ' ' . stripslashes_deep($fname . ' ' . $lname) . ' | ' . __('Registrant Email:', 'event_espresso') . ' ' . $attendee_email . ' | ' . __('Total Registrants:', 'event_espresso') . ' ' . $quantity;
	$fields['type'] = 'SERVICE';
	$fields['reference_id'] = $attendee_id;
	$fields['amount'] = number_format($event_cost, 2, '.', '');
	
	$fields['redirect_uri'] = add_query_arg(array('id'=>$attendee_id,'r_id'=>$registration_id,'event_id'=>$event_id,'attendee_action'=>'post_payment','form_action'=>'payment','type'=>'wepay'),  get_permalink($org_options['return_url']));
	$fields['callback_uri'] = add_query_arg(array('id'=>$attendee_id,'r_id'=>$registration_id,'event_id'=>$event_id,'attendee_action'=>'post_payment','form_action'=>'payment','type'=>'wepay'),  get_permalink($org_options['notify_url']));
//	$fields['redirect_uri'] = $home . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=wepay';
//	$fields['callback_uri'] = $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=wepay';

	if ($wepay_settings['force_ssl_return']) {
		$fields['redirect_uri'] = str_replace("http://", "https://", $fields['redirect_uri']);
		$fields['callback_uri'] = str_replace("http://", "https://", $fields['callback_uri']);
	}
	if (empty($wepay_settings['access_token'])) return;
	try{
	$wepay = new Espresso_Wepay($wepay_settings['access_token']);
	$raw = $wepay->request('checkout/create', $fields);
	}catch(Exception $e){
		printf(__("WePay seems to be misconfigured. Error: %s", "event_espresso"),$e->getMessage());
		return;
	}
	if (empty($raw->checkout_uri)) return;
	$uri = $raw->checkout_uri;
	if ($wepay_settings['bypass_payment_page'] == 'Y') {
		$wepay->submitPayment($uri); //Enable auto redirect to payment site
	} else {
		$wepay->submitButton($uri, $wepay_settings['button_url'], 'wepay'); //Display payment button
		wp_deregister_script('jquery.validate.pack');
	}
	if ($wepay_settings['use_sandbox']) {
		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$wepay->dump_fields($fields); // for debugging, output a table of all the fields
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_wepay');