<?php
function espresso_transactions_2checkout_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter( 'filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_2checkout_get_attendee_id');

function espresso_process_2checkout($payment_data) {
	if ($_REQUEST['credit_card_processed'] == 'Y') {

		$payment_status = 'Completed';
		$payment_date = date("d-m-Y");
		$total_cost = $_REQUEST['total'];
		$txn_type = '2CO';
		$txn_id = $_REQUEST['invoice_id'];
		$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . espresso_registration_id($payment_data['attendee_id']) . "' ";
		$sql .= " ORDER BY id LIMIT 0,1";

		$attendees = $wpdb->get_results($sql);

		foreach ($attendees as $attendee) {
			$attendee_id = $attendee->id;
			$payment_data['att_registration_id'] = $attendee->registration_id;
			$payment_data['lname'] = $attendee->lname;
			$payment_data['fname'] = $attendee->fname;
			$amount_pd = $attendee->amount_pd;
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
		$payment_data['event_link'] = '<a href="' . $event_url . '">' . $event_name . '</a>';

		$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET
                payment_status = '$payment_status',
                txn_id = '" . $txn_id . "',
                txn_type = '$txn_type',
                amount_pd = '" . $total_cost . "',
                payment_date ='" . $payment_date . "'
                WHERE registration_id ='" . espresso_registration_id($_GET['id']) . "' ";

		$wpdb->query($sql);

		$payment_data['payment_status'] = $payment_status;
		$payment_data['txn_type'] = $txn_type;
		$payment_data['payment_date'] = $payment_date;
		$payment_data['total_cost'] = $total_cost;
		$payment_data['txn_id'] = $txn_id;

		//Debugging option
		if ($email_transaction_dump == true) {
			// For this, we'll just email ourselves ALL the data as plain text output.
			$subject = 'Instant Payment Notification - Gateway Variable Dump';
			$body = "An instant payment notification was successfully recieved\n";
			$body .= "from " . " on " . date('m/d/Y');
			$body .= " at " . date('g:i A') . "\n\nDetails:\n";
			foreach ($xml as $key => $value) {
				$body .= "\n$key: $value\n";
			}
			wp_mail($contact, $subject, $body);
		}
	} else {
		$subject = 'Instant Payment Notification - Gateway Variable Dump';
		$body = "An instant payment notification failed\n";
		$body .= "from " . " on " . date('m/d/Y');
		$body .= " at " . date('g:i A') . "\n\nDetails:\n";
		foreach ($xml as $key => $value) {
			$body .= "\n$key: $value\n";
		}
		//wp_mail($contact, $subject, $body);
	}
	return $payment_data;
}

add_filter( 'filter_hook_espresso_transactions_get_payment_data', 'espresso_process_2checkout');