<?php

function espresso_display_realauth($payment_data) {
	$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	extract($payment_data);
	global $org_options, $wpdb;
	$payment_settings = get_option('event_espresso_realauth_settings');
	include("Realauth.php");

	$total_cost = number_format($total_cost, 2, '', '');
	$realauth = new Realauth($payment_settings['merchant_id'],
									$payment_settings['shared_secret']);
	$realauth->set_amount($total_cost);
	$realauth->set_currency($payment_settings['currency_format']);
	$realauth->set_sandbox($payment_settings['use_sandbox']);
	$realauth->set_attendee_id($attendee_id);
	$realauth->set_registration_id($registration_id);
	$realauth->set_timestamp();
	$realauth->set_auto_settle_flag($payment_settings['auto_settle']);
	$button_url = $payment_settings['button_url'];
	if (!empty($payment_settings['bypass_payment_page']) && $payment_settings['bypass_payment_page'] == 'Y') {
		echo $realauth->submitPayment();
	} else {
		echo $realauth->submitButton($button_url);
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_realauth');
