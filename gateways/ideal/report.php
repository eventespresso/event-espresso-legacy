<?php

function espresso_process_ideal_report($payment_data) {
	$ideal_mollie_settings = get_option('event_espresso_ideal_mollie_settings');
	$payment_data['txn_type'] = 'iDeal Mollie';
	$payment_data['txn_details'] = serialize($_REQUEST);
	if ($payment_data['payment_status'] != 'Completed') {
		$payment_data['payment_status'] = 'Incomplete';
		$payment_data['txn_id'] = 0;
		require_once('ideal.class.php');
		$partner_id = $ideal_mollie_settings['ideal_mollie_partner_id']; // Uw mollie partner ID
		if (isset($_GET['transaction_id'])) {
			$payment_data['txn_id'] = $_GET['transaction_id'];
			$iDEAL = new Espresso_iDEAL_Payment($partner_id);
			$iDEAL->checkPayment($_GET['transaction_id']);
			if ($iDEAL->getPaidStatus() == true) {
				$payment_data['payment_status'] = "Completed";
			} else {
				?>
				<h2 style="color:#F00;"><?php _e('There was an error processing your transaction!', 'event_espresso'); ?></h2> <?php
			}
		}
	}
//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
