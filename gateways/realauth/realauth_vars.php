<?php

function espresso_display_realauth($payment_data) {
	extract($payment_data);
	global $org_options, $wpdb;
	$payment_settings = get_option('event_espresso_realauth_settings');
	include("Realauth.php");
	$sql = "SELECT amount_pd FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = '" . $attendee_id . "'";
	$session_id = $wpdb->get_var("SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'");
	$sql = "SELECT ac.cost, ac.quantity FROM " . EVENTS_ATTENDEE_TABLE . " a";
	$sql .= " JOIN " . EVENTS_ATTENDEE_COST_TABLE . " ac ON a.id=ac.attendee_id ";
	$sql .= " WHERE a.attendee_session='" . $session_id . "'";
	$attendees = $wpdb->get_results($sql, ARRAY_A);
	$total_cost = 0;
	foreach ($attendees as $attendee) {
		$total_cost += $attendee['cost'] * $attendee['quantity'];
	}
	$total_cost = number_format($total_cost, 2, '', '');
	$realauth = new Realauth($payment_settings['merchant_id'],
									$payment_settings['shared_secret']);
	$realauth->set_amount($total_cost);
	$realauth->set_currency($payment_settings['currency_format']);
	$realauth->set_sandbox($payment_settings['use_sandbox']);
	$realauth->set_attendee_id($attendee_id);
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
