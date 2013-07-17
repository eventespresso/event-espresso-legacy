<?php

function espresso_display_realauth($payment_data) {
	$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	extract($payment_data);
	global $org_options, $wpdb;
	$realauth_settings = get_option('event_espresso_realauth_settings');
	include("Realauth.php");

	$total_cost = number_format($total_cost, 2, '', '');
	$realauth = new Espresso_Realauth($realauth_settings['merchant_id'],
									$realauth_settings['shared_secret']);
	$realauth->set_amount($total_cost);
	$realauth->set_currency($realauth_settings['currency_format']);
	$realauth->set_sandbox($realauth_settings['use_sandbox']);
	$realauth->set_attendee_id($attendee_id);
	$realauth->set_registration_id($registration_id);
	$realauth->set_timestamp();
	$realauth->set_auto_settle_flag($realauth_settings['auto_settle']);
	
	if ( empty( $realauth_settings['button_url']) || ! file_exists( $realauth_settings['button_url'] )) {
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/realauth/realauth-logo.png")) {
			$realauth_settings['button_url'] = EVENT_ESPRESSO_GATEWAY_URL . "/realauth/realauth-logo.png";
		} else {
			$realauth_settings['button_url'] = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/realauth/realauth-logo.png";
		}
	} 
			
	if (!empty($realauth_settings['bypass_payment_page']) && $realauth_settings['bypass_payment_page'] == 'Y') {
		echo $realauth->submitPayment();
	} else {
		echo $realauth->submitButton( $realauth_settings['button_url'] );
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_realauth');
