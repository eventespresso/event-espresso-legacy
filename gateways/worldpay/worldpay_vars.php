<?php

function espresso_display_worldpay($payment_data) {
	extract($payment_data);
	global $wpdb, $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	include_once ('Worldpay.php');
	$myworldpay = new worldpay(); // initiate an instance of the class
	echo '<!-- Event Espresso worldpay Gateway Version ' . $myworldpay->worldpay_gateway_version . '-->';
	$worldpay_settings = get_option('event_espresso_worldpay_settings');
	$use_sandbox = $worldpay_settings['use_sandbox'];
	if ($use_sandbox) {
		$myworldpay->enableTestMode();
	}

	$attendees = array();
	$primary_registration_id = "";
	$amount_pd = 0.00;
	$multi_reg = false;
	$event_ids = array();
	$event_link = "";

	if (isset($attendee_id) && is_numeric($attendee_id) && $attendee_id > 0) {
		$tmp_row = $wpdb->get_row("select registration_id from " . EVENTS_ATTENDEE_TABLE . " where id = $attendee_id");
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

	if ($attendee_id > 0 && !empty($primary_registration_id) && strlen($primary_registration_id) > 0) {
		$registration_ids = array();
		$rs = $wpdb->get_results("select * from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " where primary_registration_id = '{$primary_registration_id}' ");
		if ($wpdb->num_rows > 0) {
			foreach ($rs as $row) {
				$registration_ids[] = $row->registration_id;
			}
		} else {
			$registration_ids[] = $primary_registration_id;
		}

		$total_cost = 0.00;
		$amount_pd = 0.00;
		foreach ($registration_ids as $registration_id) {

			$sql = "select ea.registration_id, ea.id as attendee_id, ea.amount_pd, ed.id as event_id, ed.event_name, ed.start_date, ea.fname, ea.lname, eac.quantity, eac.cost from " . EVENTS_ATTENDEE_TABLE . " ea
				inner join " . EVENTS_ATTENDEE_COST_TABLE . " eac on ea.id = eac.attendee_id
				inner join " . EVENTS_DETAIL_TABLE . " ed on ea.event_id = ed.id
				where ea.registration_id = '" . $registration_id . "' order by ed.event_name ";

			$tmp_attendees = $wpdb->get_results($sql, ARRAY_A);

			foreach ($tmp_attendees as $tmp_attendee) {
				$sub_total = 0.00;
				$sub_total = $tmp_attendee["cost"] * $tmp_attendee["quantity"];
				$attendees[] = array("attendee_info" => $tmp_attendee["event_name"] . "[" . date('m-d-Y', strtotime($tmp_attendee['start_date'])) . "]" . " -- " . $tmp_attendee["fname"] . " " . $tmp_attendee["lname"],
						"quantity" => $tmp_attendee["quantity"],
						"cost" => doubleval($tmp_attendee["cost"]),
						"sub_total" => doubleval($sub_total),
						"unit_price" => $tmp_attendee["cost"]);
				$amount_pd += $tmp_attendee["amount_pd"];
				$total_cost += $sub_total;
				if (!in_array($tmp_attendee['event_id'], $event_ids)) {
					$event_ids[] = $tmp_attendee['event_id'];
				}
			}
		}
		$discount = 0;
		if ($amount_pd < $total_cost) {
			$discount = $total_cost - $amount_pd;
		}
	}

#############################

	if ($use_sandbox) {
		// Enable test mode if needed
		$myworldpay->addField('testMode', '100');
	}
	$myworldpay->addField('instId', $worldpay_settings['worldpay_id']);
	$myworldpay->addField('cartId', $attendees[0]['attendee_info']);
	$myworldpay->addField('amount', $total_cost);
	$myworldpay->addField('MC_id', $attendee_id);
	$myworldpay->addField('MC_type', 'worldpay');
	$myworldpay->addField('currency', $worldpay_settings['currency_format']);

	if (!empty($worldpay_settings['bypass_payment_page']) && $worldpay_settings['bypass_payment_page'] == 'Y') {
		$myworldpay->submitPayment(); //Enable auto redirect to payment site
	} else {
		if (empty($worldpay_settings['button_url'])) {
			if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/worldpay/WorldPaylogoBluetrans.png")) {
				$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/worldpay/WorldPaylogoBluetrans.png";
			} else {
				$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/worldpay/WorldPaylogoBluetrans.png";
			}
		} elseif (file_exists($worldpay_settings['button_url'])) {
			$button_url = $worldpay_settings['button_url'];
		} else {
			//If no other buttons exist, then use the default location
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/worldpay/WorldPaylogoBluetrans.png";
		}
		$myworldpay->submitButton($button_url, 'worldpay'); //Display payment button
	}

	if ($use_sandbox) {

		echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('worldpay Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myworldpay->dump_fields(); // for debugging, output a table of all the fields
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_worldpay');