<?php

function espresso_transactions_worldpay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['MC_id']))
		$attendee_id = $_REQUEST['MC_id'];
	return $attendee_id;
}

function espresso_process_worldpay($payment_data) {
	global $wpdb;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	echo "<WPDISPLAY ITEM=banner>";
	if ($_REQUEST['transStatus'] == 'Y') {
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
		$txn_id = $_REQUEST['transId'];
		$txn_type = 'WorldPay';
		$payment_date = date("m-d-Y");
		$SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_id='$txn_id', txn_type='$txn_type', payment_date='$payment_date' WHERE id='$attendee_id'";
		$wpdb->query($SQL);
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
