<?php

function espresso_transactions_realauth_get_attendee_id($attendee_id) {
	if ($_REQUEST['ORDER_ID']) {
		$attendee_id = $_REQUEST['ORDER_ID'];
	}
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_realauth_get_attendee_id');

function espresso_process_realauth($payment_data) {
	global $wpdb;
	if ($_REQUEST['RESULT'] == '00') {
		$attendee_id = $payment_data['attendee_id'];
		$payment_status = 'Completed';
		$payment_date = date("d-m-Y");
		$total_cost = $_REQUEST['total'];
		$txn_type = 'Realauth';
		$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "' ";
		$sql .= " ORDER BY id LIMIT 0,1";

		$attendees = $wpdb->get_results($sql);

		foreach ($attendees as $attendee) {
			$attendee_id = $attendee->id;
			$att_registration_id = $attendee->registration_id;
			$lname = $attendee->lname;
			$fname = $attendee->fname;
			$event_id = $attendee->event_id;
		}

		$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");

		foreach ($events as $event) {
			$event_id = $event->id;
			$event_name = $event->event_name;
			$event_desc = $event->event_desc;
			$event_description = $event->event_desc;
			$event_identifier = $event->event_identifier;
			$cost = $event->event_cost;
			$active = $event->is_active;
		}


		$event_url = home_url() . "/?page_id=" . $org_options['event_page_id'] . "&regevent_action=register&event_id=" . $event_id;
		$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';

		$session_id = $wpdb->get_var("SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'");

		$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET
                payment_status = '$payment_status',
                txn_id = '" . $_REQUEST['PASREF'] . "',
                txn_type = '$txn_type',
                payment_date ='" . $payment_date . "'
                WHERE attendee_session ='" . $session_id . "' ";

		$wpdb->query($sql);
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
		if ($email_transaction_dump == true) {
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification was successfully recieved\n";
			$body .= "from " . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			wp_mail($payment_data['contact'], $subject, $body);
		}
	} else {
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		var_dump($body);
		var_dump($_REQUEST);
		//wp_mail($payment_data['contact'], $subject, $body);
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_realauth');
