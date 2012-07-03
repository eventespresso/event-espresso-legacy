<?php
// Include the authorize.net library
include_once ('Authorize.php');
echo '<!--Advanced Events Registration Authorize.net Gateway Version ' . $authnet_gateway_version . '-->';
// Create an instance of the authorize.net library
$myAuthorize = new Authorize();

// Log the IPN results
$myAuthorize->ipnLog = TRUE;

$authnet_settings = get_option('event_espresso_authnet_settings');
$authnet_login_id = $authnet_settings['authnet_login_id'];
$authnet_transaction_key = $authnet_settings['authnet_transaction_key'];

// Enable test mode if needed
//4007000000027  <-- test successful visa
//4222222222222  <-- test failure card number
if ($authnet_settings['use_sandbox'] == '1'){
	$myAuthorize->enableTestMode();
	$email_transaction_dump = true;
}

// Specify your authorize login and secret
$myAuthorize->setUserInfo($authnet_login_id, $authnet_transaction_key);

// Check validity and write down it
if ($myAuthorize->validateIpn()){
	
	$txn_type = $myAuthorize->ipnData['x_method'];
	$txn_id = $myAuthorize->ipnData['x_trans_id'];
	$amount_pd = $myAuthorize->ipnData['x_amount'];
	$attendee_id = $myAuthorize->ipnData['x_cust_id'];	
	$payment_date = date("m-d-Y");
	
    //file_put_contents('authorize.txt', 'SUCCESS' . date("m-d-Y")); //Used for debugging purposes
	//Be sure to echo something to the screen so authent knows that the ipn works
	//store the results in reusable variables	
	if ($myAuthorize->ipnData['x_response_code'] == 1){
?>
        <h2><?php _e('Thank You!','event_espresso'); ?></h2>
        <p><?php _e('Your transaction has been processed.','event_espresso'); ?></p>
<?php 
		$payment_status = 'Completed';
	}else{
?>
        <h2 style="color:#F00;"><?php _e('There was an error processing your transaction!','event_espresso'); ?></h2>
        <p><strong>Error:</strong> (<?php echo $response_reason_code;?> - <?php echo $response_reason_code;?>) - <?php echo $response_reason_text;?></p>
<?php
		$payment_status = 'Payment Declined';
	}
	global $wpdb;
			
	//$sql = "UPDATE ". EVENTS_ATTENDEE_TABLE . " SET payment_status = '" . $payment_status . "', txn_type = '" . $txn_type . "', txn_id = '" . $txn_id . "', amount_pd = '" . $amount_pd . "',  payment_date ='" . $payment_date . "' WHERE id ='" . $attendee_id . "'";
	
	//Ronalds changes
	// Get all attendees for the registration_ids that match attendee_id
	$registration_id = $wpdb->get_results("SELECT registration_id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE id ='" . $attendee_id . "'", ARRAY_N);
	$registration_id = $registration_id[0][0];		
			
	$sql = "UPDATE ". EVENTS_ATTENDEE_TABLE . " SET payment_status = '" . $payment_status . "', txn_type = '" . $txn_type . "', txn_id = '" . $txn_id . "', payment_date ='" . $payment_date . "', transaction_details = '" . serialize($myPaypal) . "'  WHERE registration_id ='" . $registration_id . "'";
	//Ronalds changes

	$wpdb->query($sql);
			
  //Debugging option
	$email_transaction_dump=true;
  if ($email_transaction_dump == true) {
     // For this, we'll just email ourselves ALL the data as plain text output.
     $subject = 'Authorize.net Notification - Gateway Variable Dump';
     $body =  "An authorize.net payment notification was successfully recieved\n";
     $body .= "from ".$myAuthorize->ipnData['x_email']." on ".date('m/d/Y');
     $body .= " at ".date('g:i A')."\n\nDetails:\n";
     foreach ($myAuthorize->ipnData as $key => $value) { $body .= "\n$key: $value\n"; }
     wp_mail($contact, $subject, $body);
  }
			
}
else
{
  file_put_contents('authorize.txt', "FAILURE\n\n" . $myAuthorize->ipnData);
  //echo something to the screen so authent knows that the ipn works
  $subject = 'Instant Payment Notification - Gateway Variable Dump';
  $body =  "An instant payment notification failed\n";
  $body .= "from ".$myAuthorize->ipnData['x_email']." on ".date('m/d/Y');
  $body .= " at ".date('g:i A')."\n\nDetails:\n";
  foreach ($myAuthorize->ipnData as $key => $value) { $body .= "\n$key: $value\n"; }
  wp_mail($contact, $subject, $body);
}
