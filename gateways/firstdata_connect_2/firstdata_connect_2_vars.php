<?php

function espresso_display_firstdata_connect_2($payment_data) {
	extract($payment_data);
	global $org_options, $wpdb;
	$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
	include("Fdggutil.php");
	$sql = "SELECT amount_pd FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = '" . $attendee_id . "'";
	$total_cost = $wpdb->get_var($sql);
	$fdggutil = new Fdggutil($firstdata_connect_2_settings['storename'],
									$firstdata_connect_2_settings['sharedSecret']);
	$fdggutil->set_timezone($firstdata_connect_2_settings['timezone']);
	$fdggutil->set_chargetotal($total_cost);
	$fdggutil->set_sandbox($firstdata_connect_2_settings['sandbox']);
	$fdggutil->set_returnUrl($org_options['notify_url']);
	$fdggutil->set_cancelUrl($org_options['cancel_return']);
	$fdggutil->set_attendee_id($attendee_id);
	$fdggutil->set_dateTime();
	$button_url = $firstdata_connect_2_settings['button_url'];
	if (!empty($firstdata_connect_2_settings['bypass_payment_page']) && $firstdata_connect_2_settings['bypass_payment_page'] == 'Y') {
		echo $fdggutil->submitPayment();
	} else {
		echo $fdggutil->submitButton($button_url);
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_firstdata_connect_2');