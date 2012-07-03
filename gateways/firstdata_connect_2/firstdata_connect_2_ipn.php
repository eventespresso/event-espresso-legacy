<?php

function espresso_transactions_firstdata_connect_2_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

add_filter('filter_hook_espresso_transactions_get_attendee_id', 'espresso_transactions_firstdata_connect_2_get_attendee_id');

function espresso_process_firstdata_connect_2($payment_data) {
	global $wpdb;
	//Needs to set payment_status, txn_type, txn_id, txn_details
	$payment_data['txn_type'] = 'Firstdata Connect 2.0';
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_id'] = $_REQUEST['oid'];
	if ($_REQUEST['status'] == 'APPROVED') {
		$payment_data['payment_status'] = 'Completed';

		$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
		include("Fdggutil.php");
		$fdggutil = new Fdggutil($firstdata_connect_2_settings['storename'],
										$firstdata_connect_2_settings['sharedSecret']);
		$hash = $fdggutil->check_return_hash($payment_data['payment_date']);
	} else {
		?>
			<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
			<p><strong>Error:</strong> (<?php echo $_REQUEST['status']; ?> - <?php echo $_REQUEST['fail_reason']; ?>)</p>
			<?php
	}
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);
	do_action('action_hook_espresso_email_after_payment', $payment_data);
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_firstdata_connect_2');