<?php

function espresso_transactions_firstdata_connect_2_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_firstdata_connect_2($payment_data) {
	global $wpdb;
	$payment_data['txn_type'] = 'Firstdata Connect 2.0';
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_id'] = $_REQUEST['oid'];
	if ($_REQUEST['status'] == 'APPROVED') {
		$payment_data['payment_status'] = 'Completed';

		$firstdata_connect_2_settings = get_option('event_espresso_firstdata_connect_2_settings');
		include("Fdggutil.php");
		$fdggutil = new Espresso_Fdggutil($firstdata_connect_2_settings['storename'],
										$firstdata_connect_2_settings['sharedSecret']);
		$hash = $fdggutil->check_return_hash($payment_data['payment_date']);
	} else {
		?>
			<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2>
			<p><strong>Error:</strong> (<?php echo $_REQUEST['status']; ?> - <?php echo $_REQUEST['fail_reason']; ?>)</p>
			<?php
	}
	add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
