<?php

function espresso_transactions_firstdata_connect_2_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_firstdata_connect_2_get_attendee_id');

function espresso_process_firstdata_connect_2($payment_data) {
	if ($_REQUEST['status'] == 'APPROVED') {
		$attendee_id = $payment_data['attendee_id'];
		$payment_status = 'Completed';
		$sql = "SELECT ea.event_id, ed.event_name, ea.fname, ea.lname, ";
		$sql .= "ea.payment_date, ea.amount_pd total_cost, ";
		$sql .= "ea.registration_id att_registration_id FROM " . EVENTS_ATTENDEE_TABLE . " ea ";
		$sql .= "JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=ea.event_id ";
		$sql .= "WHERE ea.id = '" . $attendee_id . "'";
		$result = $wpdb->get_row($sql, ARRAY_A);
		extract($result);
		$event_link = '<a href="' . home_url() . '/?page_id=';
		$event_link .= $org_options['event_page_id'] . '&ee=' . $event_id . '">';
		$event_link .= $event_name . '</a>';
		$txn_type = 'Firstdata Connect 2.0';
		$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
		include("Fdggutil.php");
		$fdggutil = new Fdggutil($firstdata_connect_2_settings['storename'],
										$firstdata_connect_2_settings['sharedSecret']);
		$hash = $fdggutil->check_return_hash($payment_date);
		$txn_id = $_REQUEST['refnumber'];
		$payment_data['event_link'] = $event_link;
		$payment_data['fname'] = $fname;
		$payment_data['lname'] = $lname;
		$payment_data['txn_type'] = $txn_type;
		$payment_data['payment_date'] = $payment_date;
		$payment_data['total_cost'] = $total_cost;
		$payment_data['payment_status'] = $payment_status;
		$payment_data['att_registration_id'] = $att_registration_id;
		$payment_data['txn_id'] = $txn_id;
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_firstdata_connect_2');