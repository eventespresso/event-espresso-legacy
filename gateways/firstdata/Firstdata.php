<?php

function espresso_transactions_firstdata_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_firstdata_get_attendee_id');

function espresso_process_firstdata($payment_data) {
	global $wpdb;
	$attendee_id = $payment_data['attendee_id'];
	$registration_id = espresso_registration_id($attendee_id);

	$sql = "SELECT ea.amount_pd, ed.event_name FROM " . EVENTS_ATTENDEE_TABLE . " ea ";
	$sql .= "JOIN " . EVENTS_DETAIL_TABLE . " ed ";
	$sql .= "ON ed.id = ea.event_id ";
	$sql .= " WHERE registration_id = '" . $registration_id . "' ";
	$sql .= " ORDER BY ea.id ASC LIMIT 1";

	$r = $wpdb->get_row($sql);

	if (!$r || $wpdb->num_rows == 0) {

		exit("Looks like something went wrong.  Please try again or notify the website administrator.");
	}


	$firstdata_settings = get_option('event_espresso_firstdata_settings');

	$pem_file = EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/firstdata/" . $firstdata_settings['firstdata_store_id'] . ".pem";

	if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "firstdata/" . $firstdata_settings['firstdata_store_id'] . ".pem")) {

		$pem_file = EVENT_ESPRESSO_GATEWAY_DIR . "firstdata/" . $firstdata_settings['firstdata_store_id'] . ".pem";
	}


	include"lphp.php";
	$mylphp = new lphp;
	$myorder["debugging"] = $firstdata_settings['use_sandbox'];
	$myorder["host"] = $myorder["debugging"] ? "staging.linkpt.net" : "secure.linkpt.net";
	$myorder["port"] = "1129";
	$myorder["keyfile"] = $pem_file; # Change this to the name and location of your certificate file
	$myorder["configfile"] = $firstdata_settings['firstdata_store_id'];		# Change this to your store number

	$myorder["ordertype"] = "SALE";
	$myorder["result"] = "LIVE"; # For a test, set result to GOOD, DECLINE, or DUPLICATE
	$myorder["cardnumber"] = $_POST['card_num'];
	$myorder["cardexpmonth"] = $_POST['expmonth'];
	$myorder["cardexpyear"] = $_POST['expyear'];
	$myorder["chargetotal"] = $r->amount_pd;

	$myorder["name"] = $_POST['first_name'] . ' ' . $_POST['last_name'];
	$myorder["address1"] = $_POST['address'];
	$myorder["city"] = $_POST["city"];
	$myorder["state"] = $_POST["state"];
	$myorder["email"] = $_POST["email"];

	/**
	 * It looks like firstdata requires addrnum, the beginning
	 * number of the address.  On their test forms, they have a specific
	 * field for this.  I am just going to grab the address, split it and grab
	 * index 0.  Will see how this goes before adding a new field.  If can't split the
	 * address, will pass it full.
	 */
	$addrnum = $_POST['address'];

	$temp_address = explode(" ", $_POST['address']);

	if (count($temp_address > 0))
		$addrnum = $temp_address[0];

	$myorder["addrnum"] = $addrnum;
	$myorder["zip"] = $_POST["zip"];

	$result = $mylphp->curl_process($myorder); # use curl methods
	if ($myorder["debugging"]) {
		echo "<p>var_dump of order data:</p> ";
		var_dump($myorder);

		echo "<br />";
		echo "<p>var_dump of result:</p> ";
		var_dump($result);
	}


	if ($result["r_approved"] != "APPROVED") {
		if ($result['r_approved'] != '<') echo "<br />Status: " . $result['r_approved'];
		if ($result['r_error'] != '<') echo "<br />Error: " . $result['r_error'];
		echo "<br />";
		var_dump($result);
	} else { // success
		$payment_status = 'Completed';
		$payment_date = date("d-m-Y");

		$txn_type = 'FD';
		$txn_id = $result["r_ordernum"];

		$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . espresso_registration_id($_POST['id']) . "' ";
		$sql .= $id == '' ? '' : " AND id= '" . $id . "' ";
		$sql .= " ORDER BY id LIMIT 0,1";

		$attendees = $wpdb->get_results($sql);
		foreach ($attendees as $attendee) {
			$attendee_id = $attendee->id;
			$att_registration_id = $attendee->registration_id;
			$lname = $attendee->lname;
			$fname = $attendee->fname;
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
		//Build links
		$event_url = espresso_reg_url($event_id);
		$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';

		$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET
                payment_status = '$payment_status',
                txn_id = '" . $result["r_ordernum"] . "',
                txn_type = '$txn_type',
                amount_pd = '" . $r->amount_pd . "',
                payment_date ='" . $result["r_tdate"] . "'
                WHERE registration_id ='" . $registration_id . "' ";

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
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_firstdata');