<?php

function espresso_display_wepay($payment_data) {
	extract($payment_data);
// Setup class
	include_once ('Wepay.php');
	echo '<!-- Event Espresso Wepay Gateway Version ' . Wepay::$version . '-->';
	$wepay_settings = get_option('event_espresso_wepay_settings');
	global $org_options;
	if ($wepay_settings['use_sandbox']) {
		Wepay::useStaging($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	} else {
		Wepay::useProduction($wepay_settings['wepay_client_id'], $wepay_settings['wepay_client_secret']);
	}
	$quantity = isset($quantity) && $quantity > 0 ? $quantity : espresso_count_attendees_for_registration($attendee_id);
	$fields['account_id'] = $wepay_settings['account_id'];
	$fields['short_description'] = stripslashes_deep($event_name);
	$fields['long_description'] = stripslashes_deep($event_name) . ' | ' . __('Name:', 'event_espresso') . ' ' . stripslashes_deep($fname . ' ' . $lname) . ' | ' . __('Registrant Email:', 'event_espresso') . ' ' . $attendee_email . ' | ' . __('Total Registrants:', 'event_espresso') . ' ' . $quantity;
	$fields['type'] = 'SERVICE';
	$fields['reference_id'] = $attendee_id;
	$fields['amount'] = number_format($event_cost, 2, '.', '');
	$fields['redirect_uri'] = home_url() . '/?page_id=' . $org_options['return_url'] . '&id=' . $attendee_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment';
	$fields['callback_uri'] = home_url() . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment';

	if (empty($wepay_settings['access_token'])) return;
	$wepay = new Wepay($wepay_settings['access_token']);
	$raw = $wepay->request('checkout/create', $fields);
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