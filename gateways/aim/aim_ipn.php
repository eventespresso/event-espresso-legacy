<?php
echo '<!--Event Espresso Authorize.net AIM Gateway Version ' . $authnet_aim_gateway_version . '-->';

require_once 'AuthorizeNet.php';

define("AUTHORIZENET_SANDBOX", false);

$authnet_aim_settings = get_option('event_espresso_authnet_aim_settings');
$authnet_aim_login_id = $authnet_aim_settings['authnet_aim_login_id'];
$authnet_aim_transaction_key = $authnet_aim_settings['authnet_aim_transaction_key'];

// Enable test mode if needed
//4007000000027  <-- test successful visa
//4222222222222  <-- test failure card number
if ($authnet_aim_settings['use_sandbox'] == '1'){
	define("AUTHORIZENET_SANDBOX", true);
	define("AUTHORIZENET_LOG_FILE", true);
}

//start transaction
$transaction = new AuthorizeNetAIM($authnet_aim_login_id, $authnet_aim_transaction_key);
$transaction->amount = $_POST['amount'];
$transaction->card_num = $_POST['card_num'];
$transaction->exp_date = $_POST['exp_date'];
$transaction->first_name = $_POST['first_name'];
$transaction->last_name = $_POST['last_name'];
$transaction->email = $_POST['email'];
$transaction->address = $_POST['address'];
$transaction->city = $_POST['city'];
$transaction->state = $_POST['state'];
$transaction->zip = $_POST['zip'];
$transaction->cust_id = $_POST['x_cust_id'];
$transaction->invoice_num = $_POST['invoice_num'];

//Capture response
$response = $transaction->authorizeAndCapture();

if ($response->approved) {
?>
	<h2><?php _e('Thank You!','event_espresso'); ?></h2>
	<p><?php _e('Your transaction has been processed.','event_espresso'); ?></p>
	<p><?php __('Transaction ID:','event_espresso') . $response->transaction_id; ?></p>
<?php 
	$payment_status = 'Completed';
	$txn_id = $response->transaction_id;
	$attendee_id = $response->customer_id;
	$txn_type = $response->transaction_type;
	$payment_date = date("m-d-Y");
	
} else {
  print $response->error_message;
  $payment_status = 'Payment Declined';
}

//Add details to the DB
global $wpdb;
			
$sql = "UPDATE ". EVENTS_ATTENDEE_TABLE . " SET payment_status = '" . $payment_status . "', txn_type = '" . $txn_type . "', txn_id = '" . $txn_id . "', payment_date ='" . $payment_date . "', transaction_details = '" . serialize($response) . "'  WHERE registration_id ='" . espresso_registration_id($attendee_id) . "'";

$wpdb->query($sql);

//print_r($response);