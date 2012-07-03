<?php

function espresso_transactions_firstdata_connect_2_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_firstdata_connect_2_get_attendee_id');

function espresso_process_firstdata_connect_2($payment_data) {
	//Needs to set payment_status, txn_type, txn_id, txn_details
	$payment_data['txn_type'] = 'Firstdata Connect 2.0';
	$payment_data['txn_details'] = serialize($_REQUEST);
	if ($_REQUEST['status'] == 'APPROVED') {
		$attendee_id = $payment_data['attendee_id'];
		$payment_data['payment_status'] = 'Completed';
		$sql = "SELECT ea.event_id, ed.event_name, ea.fname, ea.lname, ";
		$sql .= "ea.payment_date, ea.amount_pd total_cost, ";
		$sql .= "ea.registration_id att_registration_id FROM " . EVENTS_ATTENDEE_TABLE . " ea ";
		$sql .= "JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=ea.event_id ";
		$sql .= "WHERE ea.id = '" . $attendee_id . "'";
		$result = $wpdb->get_row($sql, ARRAY_A);
		extract($result);
		$payment_data['txn_id'] = $_REQUEST['refnumber'];
		$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
		$payment_data = apply_filters('action_hook_espresso_update_attendee_payment_data', $payment_data);
		$payment_data['att_registration_id'] = $att_registration_id;
		$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
		include("Fdggutil.php");
		$fdggutil = new Fdggutil($firstdata_connect_2_settings['storename'],
										$firstdata_connect_2_settings['sharedSecret']);
		$hash = $fdggutil->check_return_hash($payment_data['payment_date']);
	} else {
		$payment_data['payment_status'] = 'NOT APPROVED';
		$payment_data['txn_id'] = 0;
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_firstdata_connect_2');