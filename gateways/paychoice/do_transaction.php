<?php

function espresso_transactions_paychoice_get_attendee_id($attendee_id) {
	if (isset($_REQUEST['id']))
		$attendee_id = $_REQUEST['id'];
	return $attendee_id;
}

function espresso_process_paychoice($payment_data) {
	global $wpdb;
	$payment_data['txn_details'] = serialize($_REQUEST);
	$payment_data['txn_id'] = 0;
	$payment_data['txn_type'] = 'PayChoice';
	$payment_data['payment_status'] = 'Incomplete';
	require_once(dirname(__FILE__) . '/paychoice.class.php');

	$paychoice_settings = get_option('event_espresso_paychoice_settings');	
	$paychoice = new PayChoice();
	$paychoice->setCredentials($paychoice_settings['paychoice_username'], $paychoice_settings['paychoice_password'], $paychoice_settings['use_sandbox']);
	
	$cc = $_POST['cc'];
	$cc_name = $_POST['cc_name'];
	$cc_type = $_POST['cc_type'];
	$exp_month = $_POST['exp_month'];
	$exp_year = $_POST['exp_year'];
	$csc = $_POST['csc'];
	$invoiceNumber = $_GET['r_id'];	
	$amount = $payment_data['total_cost'] / 100;
	echo "<div id='paychoice_response'>";
	try{
		$response = $paychoice->charge($invoiceNumber, $cc_name, $cc_type, $cc, $csc, $exp_month, $exp_year, $amount, $paychoice_settings['paychoice_currency_symbol']);
		if (!empty($response)) {
			$payment_data['txn_details'] = serialize($response);	
			if ($response->approved == true) {
				echo "<div class='paychoice_status'>" . $response->status . " (" . $response->errorCode . " " . $response->errorDescription . ")</div>";
				$payment_data['payment_status'] = 'Completed';
				$payment_data['txn_id'] = $response->transactionGuid;
			}
			else {
				echo "<div class='paychoice_error'>ERROR: " . $response->errorCode . " " . $response->errorDescription . "  </div>";
			}
			
		}
	}catch(PayChoiceException $e){
		echo "<div class='paychoice_error'>ERROR: " . $e->getMessage(). "  </div>";
	}
	echo "</div>";		
	if ($payment_data['payment_status'] != 'Completed') {
		echo "<div id='paychoice_response' class='paychoice_error'>Looks like something went wrong.  Please try again or notify the website administrator.</div>";
	}
	
	/* UNCOMMENT FOR DEBUGGING */
	/*
	echo "<h2>Charge Results</h2>";
	echo "<table>";
	echo "<tr><td>Approved:</td><td>{$response->approved}</td></tr>";
	echo "<tr><td>Status:</td><td>{$response->status}</td></tr>";	
	echo "<tr><td>Transaction Guid:</td><td>{$response->transactionGuid}</td></tr>";	
	echo "<tr><td>Error Code:</td><td>{$response->errorCode}</td></tr>";
	echo "<tr><td>Error Description:</td><td>{$response->errorDescription}</td></tr>";
	echo "</table>";
	*/
	
	add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');
	return $payment_data;
}
