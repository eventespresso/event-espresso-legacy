<?php

function espresso_process_ideal_report($payment_data) {
	$ideal_mollie_settings = get_option('event_espresso_ideal_mollie_settings');
	$payment_data['txn_type'] = 'iDeal Mollie';
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['payment_status'] = 'Incomplete';
	$payment_data['txn_id'] = 0;
	require_once('ideal.class.php');
	$partner_id = $ideal_mollie_settings['ideal_mollie_partner_id']; // Uw mollie partner ID
	if (isset($_GET['transaction_id'])) {
		$payment_data['txn_id'] = $_GET['transaction_id'];
		$iDEAL = new iDEAL_Payment($partner_id);
		$iDEAL->checkPayment($_GET['transaction_id']);
		if ($iDEAL->getPaidStatus() == true) {
			$payment_data['payment_status'] = "Completed";
		} else {
			?>
			<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2> <?php
		}
	}
	$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
	$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);
	do_action('action_hook_espresso_email_after_payment', $payment_data);
	return $payment_data;
}

add_filter('filter_hook_espresso_thank_you_get_payment_data', 'espresso_process_ideal_report');