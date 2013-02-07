<?php

function espresso_transactions_paytrace_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_paytrace($payment_data) {
	global $wpdb;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = 0;
	$payment_data['txn_type'] = 'PayTrace';
	$payment_data['payment_status'] = 'Incomplete';
	require_once(dirname(__FILE__) . '/paytrace.class.php');
	$cls_paytrace = new Espresso_ClsPaytrace();
	$paytrace_settings = get_option('event_espresso_paytrace_settings');

	$primary_registration_id = "";
	$registration_id = "";
	$amount_pd = 0.00;
	$multi_reg = false;
	$event_ids = array();
	$event_link = "";

	if (is_numeric($payment_data['attendee_id']) && $payment_data['attendee_id'] > 0) {
		$tmp_row = $wpdb->get_row("select registration_id from " . EVENTS_ATTENDEE_TABLE . " where id = '" . $payment_data['attendee_id'] . "'");

		if ($tmp_row !== NULL) {
			$tmp_registration_id = $tmp_row->registration_id;
			$tmp_row = $wpdb->get_row("select * from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " where registration_id = '{$tmp_registration_id}' ");
			if ($tmp_row !== NULL) {
				$primary_registration_id = $tmp_row->primary_registration_id;
				$multi_reg = true;
			} else {
				$primary_registration_id = $tmp_registration_id;
			}
		}
	}

	if ($payment_data['attendee_id'] > 0 && !empty($primary_registration_id) && strlen($primary_registration_id) > 0) {
		$registration_ids = array();
		$rs = $wpdb->get_results("select * from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " where primary_registration_id = '{$primary_registration_id}' ");
		if ($wpdb->num_rows > 0) {
			foreach ($rs as $row) {
				$registration_ids[] = $row->registration_id;
			}
		} else {
			$registration_ids[] = $primary_registration_id;
		}

		$attendees = array();
		$total = 0.00;
		$amount_pd = 0.00;
		$line_item = "";
		foreach ($registration_ids as $registration_id) {

			$sql = "select ea.registration_id, ea.id as attendee_id, ea.amount_pd, ed.id as event_id, ed.event_name, ed.start_date, ea.fname, ea.lname, ea.quantity, ea.final_price ";
			$sql .= " from " . EVENTS_ATTENDEE_TABLE . " ea ";
			//$sql .= " inner join " . EVENTS_ATTENDEE_COST_TABLE . " eac on ea.id = eac.attendee_id ";
			$sql .= " inner join " . EVENTS_DETAIL_TABLE . " ed on ea.event_id = ed.id ";
			$sql .= " where ea.registration_id = '" . $registration_id . "' order by ed.event_name ";

			$tmp_attendees = $wpdb->get_results($sql, ARRAY_A);

			$total_cost = 0;
			foreach ($tmp_attendees as $tmp_attendee) {
				$sub_total = $tmp_attendee["final_price"] * $tmp_attendee["quantity"];
				$attendees[] = array("attendee_info" => $tmp_attendee["event_name"] . "[" . date('m-d-Y', strtotime($tmp_attendee['start_date'])) . "]" . " >> " . $tmp_attendee["fname"] . " " . $tmp_attendee["lname"],
						"quantity" => $tmp_attendee["quantity"],
						"final_price" => doubleval($tmp_attendee["final_price"]),
						"sub_total" => doubleval($sub_total));
				$line_item .= "LINEITEM~PRODUCTID=" . $tmp_attendee['attendee_id'] . "+DESCRIPTION=" . $tmp_attendee["event_name"] . "[" . date('m-d-Y', strtotime($tmp_attendee['start_date'])) . "]" . " >> " . $tmp_attendee["fname"] . " " . $tmp_attendee["lname"] . "
							QUANTITY=" . $tmp_attendee['quantity'] . "UNITCOST=" . $tmp_attendee['final_price'] . "+AMOUNTLI=" . $sub_total . "+|";
				$amount_pd += $tmp_attendee["amount_pd"];
				$total_cost += $sub_total;
				if (!in_array($tmp_attendee['event_id'], $event_ids)) {
					$event_ids[] = $tmp_attendee['event_id'];
				}
			}
		}
		$discount = 0;
		$amount_pd=$total_cost;
		/*if ($amount_pd < $total_cost) {
			$discount = $total_cost - $amount_pd;
		}*/
		//echo "do_transaction: amount:$amount_pd, total cost: $total_cost, discount:$discount";
		$cc = $_POST['cc'];
		$exp_month = $_POST['exp_month'];
		$exp_year = $_POST['exp_year'];
		$csc = $_POST['csc'];
		$bname = $_POST['first_name'] . " " . $_POST['last_name'];
		$baddress = $_POST['address'];
		$bcity = $_POST['city'];
		$bzip = $_POST['zip'];
		$email = $_POST['email'];
		$state = $_POST['state'];


		$response = $cls_paytrace->do_transaction($amount_pd, $discount, $line_item, $cc, $csc, $exp_month, $exp_year, $csc, $bname, $baddress, $bcity, $state, $bzip, $email);
		if (!empty($response)) {
			$payment_data['txn_details'] = serialize($response);
			if (isset($response['status'])) {
				echo "<div id='paytrace_response'>";
				if ($response['status'] > 0) {
					$payment_data['txn_id'] = $response['transaction_data']['TRANSACTIONID'];
					echo "<div class='paytrace_status'>" . $response['msg'] . "</div>";
					$payment_data['payment_status'] = 'Completed';
				}
				if (isset($response['error_msg']) && strlen(trim($response['error_msg'])) > 0) {
					echo "<div class='paytrace_error'>ERROR: " . $response['error_msg'] . "  </div>";
				}
				echo "</div>";
				$att_registration_id = $primary_registration_id;
				$row = $wpdb->get_row("select * from " . EVENTS_ATTENDEE_TABLE . " where registration_id = '{$att_registration_id}' order by id limit 1 ");
			}
		}
	}
	if ($payment_data['payment_status'] != 'Completed') {
		echo "<div id='paytrace_response' class='paytrace_error'>Looks like something went wrong.  Please try again or notify the website administrator.</div>";
	}
	//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
