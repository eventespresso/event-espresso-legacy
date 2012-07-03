<?php

function espresso_transactions_nab_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_nab_get_attendee_id');

function espresso_process_nab ($payment_data) {
	global $wpdb;
	$eway_settings = get_option('event_espresso_eway_settings');

	if ($_REQUEST['rescode'] == '00' || $_REQUEST['rescode'] == '08') {
		$attendee_id = $payment_data['attendee_id'];
		$payment_status = 'Completed';
		$payment_date = date("d-m-Y");
		$txn_type = 'NAB';
		$txn_id = $_REQUEST['txnid'];
		$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . espresso_registration_id($attendee_id) . "' ";
		$sql .= $attendee_id == '' ? '' : " AND id= '" . $attendee_id . "' ";
		$sql .= " ORDER BY id LIMIT 0,1";

		$attendees = $wpdb->get_results($sql);

		foreach ($attendees as $attendee) {
			$attendee_id = $attendee->id;
			$att_registration_id = $attendee->registration_id;
			$lname = $attendee->lname;
			$fname = $attendee->fname;
			$amount_pd = $attendee->amount_pd;
			$total_cost = $attendee->amount_pd;
			$event_id = $attendee->event_id;
		}

		$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");

		foreach ($events as $event) {
			$event_id = $event->id;
			$event_name = $event->event_name;
			$event_desc = $event->event_desc;
			$event_description = $event->event_desc;
			$event_identifier = $event->event_identifier;
			$active = $event->is_active;
		}


		$event_url = espresso_reg_url($event_id);
		$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';

		$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET
				payment_status = '$payment_status',
				txn_id = '" . $txn_id . "',
				txn_type = '$txn_type',
				amount_pd = '" . $amount_pd . "',
				payment_date ='" . $payment_date . "'
				WHERE registration_id ='" . $att_registration_id . "' ";

		$wpdb->query($sql);
		$total_cost = $amount_pd;
		$payment_data['event_link'] = $event_link;
		$payment_data['fname'] = $fname;
		$payment_data['lname'] = $lname;
		$payment_data['txn_type'] = $txn_type;
		$payment_data['payment_date'] = $payment_date;
		$payment_data['total_cost'] = $total_cost;
		$payment_data['payment_status'] = $payment_status;
		$payment_data['att_registration_id'] = $att_registration_id;
		$payment_data['txn_id'] = $txn_id;
		//Debugging option
		if ($eway_settings['use_sandbox']) {
			var_dump($response);
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification was successfully recieved\n";
			$body .= "from " . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			$body .= $response;
			wp_mail($contact, $subject, $body);
		}
	} else {
		echo "Looks like there was a problem with your payment details. Please try again.";
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		$body .= $response;
		wp_mail($contact, $subject, $body);
		event_espresso_pay();
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_nab');