<?php

function espresso_get_total_cost($payment_data) {
	global $wpdb;
	$sql = "SELECT ac.cost, ac.quantity FROM " . EVENTS_ATTENDEE_TABLE . " a ";
	$sql .= " JOIN " . EVENTS_ATTENDEE_COST_TABLE . " ac ON a.id=ac.attendee_id ";
	$sql .= " WHERE a.attendee_session='" . $payment_data['attendee_session'] . "'";
	$tickets = $wpdb->get_results($sql, ARRAY_A);
	$total_cost = 0;
	foreach ($tickets as $ticket) {
		$total_cost += $ticket['quantity']*$ticket['cost'];
	}
	$payment_data['total_cost'] = $total_cost;
	return $payment_data;
}

add_filter('filter_hook_espresso_get_total_cost', 'espresso_get_total_cost');

function espresso_update_attendee_payment_status_in_db($payment_data) {
	global $wpdb;
	$payment_data['payment_date'] = date("m-d-Y");
	$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET amount_pd = '" . $payment_data['total_cost'] . "' WHERE id ='" . $payment_data['attendee_id'] . "' ";
	$wpdb->query($sql);

	$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '" . $payment_data['$payment_status'] . "', txn_type = '" . $payment_data['txn_type'] . "', txn_id = '" . $payment_data['txn_id'] . "', payment_date ='" . $payment_data['payment_date'] . "', transaction_details = '" . $payment_data['txn_details'] . "' WHERE attendee_session ='" . $payment_data['attendee_session'] . "' ";
	$wpdb->query($sql);
	return $payment_data;
}

add_filter('filter_hook_espresso_update_attendee_payment_data_in_db', 'espresso_update_attendee_payment_status_in_db');

function espresso_prepare_event_link($payment_data) {
	$event_url = espresso_reg_url($payment_data['event_id']);
	$payment_data['event_link'] = '<a href="' . $event_url . '">' . $payment_data['event_name'] . '</a>';
	return $payment_data;
}

add_filter('filter_hook_espresso_prepare_event_link', 'espresso_prepare_event_link');

function espresso_update_attendee_payment_status_by_session_id($payment_data) {
	global $wpdb;
	extract($payment_data);
	$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET
                payment_status = '$payment_status',
                txn_id = '" . $txn_id . "',
                txn_type = '$txn_type',
                amount_pd = '" . $total_cost . "',
                payment_date ='" . $payment_date . "'
                WHERE attendee_session ='" . $attendee_session . "' ";

	$wpdb->query($sql);
}

function espresso_prepare_payment_data_for_gateways($payment_data) {
	global $wpdb, $org_options;
	$sql = "SELECT ea.email, ea.event_id, ea.registration_id as att_registration_id,";
	$sql .= " ea.attendee_session, ed.event_name, ea.lname, ea.fname,";
	$sql .= "	ea.payment_status, ea.payment_date FROM " . EVENTS_ATTENDEE_TABLE . " ea";
	$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=ea.event_id";
	$sql .= " WHERE ea.id='" . $payment_data['attendee_id'] . "'";
	$temp_data = $wpdb->get_row($sql, ARRAY_A);
	$payment_data = array_merge($payment_data, $temp_data);
	$payment_data['contact'] = $org_options['contact_email'];
	return $payment_data;
}

add_filter('filter_hook_espresso_prepare_payment_data_for_gateways', 'espresso_prepare_payment_data_for_gateways');

function event_espresso_txn() {
	global $wpdb, $org_options;
	if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
		espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => ''));
	}
	$active_gateways = get_option('event_espresso_active_gateways', array());
	if (empty($active_gateways)) {
		$subject = __('Website Payment IPN Not Setup', 'event_espresso');
		$body = sprintf(__('The IPN for %s at %s has not been properly setup and is not working. Date/time %s', 'event_espresso'), $org_options['organization'], home_url(), date('g:i A'));
		wp_mail($org_options['contact_email'], $subject, $body);
		return;
	}
	foreach ($active_gateways as $gateway => $path) {
		require_once($path . "/init.php");
	}
	$payment_data['attendee_id'] = apply_filters('filter_hook_espresso_transactions_get_attendee_id', '');
	if ($payment_data['attendee_id'] == "") {
		echo "ID not supplied.";
	} else {
		$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_transactions_get_payment_data', $payment_data);

		extract($payment_data);
		if (!empty($payment_status) && $payment_status == 'Completed') {
			event_espresso_send_payment_notification(array('attendee_id' => $attendee_id));
			if ($org_options['email_before_payment'] == 'N') {
				event_espresso_email_confirmations(array('attendee_id' => $attendee_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
			}
			if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php")) {
				require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php"); //This is the path to the template file if available
			} else {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/payment_overview.php");
			}
		}
	}
	$_REQUEST['page_id'] = $org_options['return_url'];
	ee_init_session();
}
