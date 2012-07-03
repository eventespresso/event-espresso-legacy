<?php
function espresso_process_ideal_report ($payment_data) {
	$ideal_mollie_settings = get_option('event_espresso_ideal_mollie_settings');

	require_once('ideal.class.php');

	$partner_id = $ideal_mollie_settings['ideal_mollie_partner_id']; // Uw mollie partner ID

	if (isset($_GET['transaction_id'])) {
		$iDEAL = new iDEAL_Payment($partner_id);
// $iDEAL->setTestMode();

		$iDEAL->checkPayment($_GET['transaction_id']);

		if ($iDEAL->getPaidStatus() == true) {

			global $wpdb;

			$attendee_id = $payment_data['attendee_id'];
			$transaction_id = $_GET['transaction_id'];
			$payment_status = "Completed";
			$payment_date = date('Y-m-d');
			$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_type = 'Mollie', txn_id = '$transaction_id', payment_date ='$payment_date' WHERE registration_id ='" . espresso_registration_id($attendee_id) . "' ";
			$wpdb->query($sql);

			/*
			 * Payment ok
			 */
			/* De betaling is betaald, deze informatie kan opgeslagen worden (bijv. in de database).
			  Met behulp van $iDEAL->getConsumerInfo(); kunt u de consument gegevens ophalen (de
			  functie returned een array). Met behulp van $iDEAL->getAmount(); kunt u het betaalde
			  bedrag vergelijken met het bedrag dat afgerekend zou moeten worden. */
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
	}
	return $payment_data;
}

add_filter('filter_hook_espresso_transactions_get_payment_data', 'espresso_process_ideal_report');