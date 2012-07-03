<?php

function espresso_transactions_quickpay_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_quickpay_get_attendee_id');

function espresso_process_quickpay($payment_data) {
	global $wpdb;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = trim(stripslashes($_GET['transaction_id']));
	$payment_data['txn_type'] = 'Quickpay';
	$payment_data['payment_status'] = 'Incomplete';
	$quickpay_settings = get_option('event_espresso_quickpay_settings');
	$sql = "SELECT txn_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $payment_data['attendee_id'] . "' ";
	$txn_id = $wpdb->get_var($sql);

	if ($_GET['chronopay_callback'] == 'true') {
		$sessionid = trim(stripslashes($_GET['sessionid']));
		if (md5($payment_data['txn_id'] . $quickpay_settings['quickpay_md5secret']) == $txn_id) {
			$payment_data['payment_status'] = 'Completed';
		}
	}
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);
	do_action('action_hook_espresso_email_after_payment', $payment_data);
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_quickpay');

function espresso_quickpay_thankyou_page($payment_data) {
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	return $payment_data;
}

add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_quickpay_thankyou_page');