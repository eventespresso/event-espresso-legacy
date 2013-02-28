<?php
require_once('library/googleresponse.php');
require_once('library/googlenotificationhistory.php');
require_once('library/googlerequest.php');

/**
 * for use in google ipn, for prasing out the attendee and registration IDs
 * from the google  ipn's private merchant data field. We sent this data to
 * google in google_checkout_vars when we set the private merchant data 
 * on the cart we submitted
 * @param string $privateDataString like key=val,key2=val2,key3=val3
 * @return array like array(key=>val,key2=>val2)
 */
function espresso_google_parse_private_data($privateDataString) {

	$keyAndValStrings = explode(",", $privateDataString);
	$privateData = array();
	foreach ($keyAndValStrings as $keyAndValString) {
		$keyAndVal = explode("=", $keyAndValString);
		$privateData[$keyAndVal[0]] = $keyAndVal[1];
	}
	return $privateData;
}

/**
 * uses the posted info from google to determine teh attendee and registration
 * ids relating to the current transaction
 * @global type $wpdb
 * @return array like array('attendee_id'=>12,'registration_id'=>'14123s3wr') 
 */
function espresso_google_checkout_get_attendee_and_registration_id() {
	if(isset($_GET['r_id']) && isset($_GET['id'])){
		return array('attendee_id'=>$_GET['id'], 'registration_id'=>$_GET['r_id']);
	}
	list($root, $data) = espresso_google_checkout_get_response();
	
	if (isset($data) && isset($data[$root]) && isset($data[$root]['order-summary']) && 
			isset($data[$root]['order-summary']['shopping-cart']) && isset($data[$root]['order-summary']['shopping-cart']['merchant-private-data'])
			&& isset($data[$root]['order-summary']['shopping-cart']['merchant-private-data']['VALUE'])) {
		$privateDataString = $data[$root]['order-summary']['shopping-cart']['merchant-private-data']['VALUE'];
		$privateData = espresso_google_parse_private_data($privateDataString);
		
		if (array_key_exists('attendee_id', $privateData) && array_key_exists('registration_id', $privateData))
			return array('attendee_id' => $privateData['attendee_id'], 'registration_id' => $privateData['registration_id']);
	}elseif (isset($data) && isset($data[$root]) && isset($data[$root]['google-order-number']) && isset($data[$root]['google-order-number']['VALUE'])) {
		//check our database for the google serial number in this xml, as google only sends our 
		//private merchant data during the first notification. So, during thatfirst
		//notification, we saved an attendee row with the transaction ID=google serial number
		global $wpdb;
		$attendeeRow = $wpdb->get_row("SELECT id, registration_id FROM {$wpdb->prefix}events_attendee WHERE txn_id='{$data[$root]['google-order-number']['VALUE']}'", ARRAY_A);
		if ($attendeeRow) {
			return array('attendee_id' => $attendeeRow['id'], 'registration_id' => $attendeeRow['registration_id']);
		}
	}
	return array('attendee_id' => null, 'registration_id' => null);
}

function espresso_transactions_google_checkout_get_attendee_id($attendee_id) {
	$attendeeAndRegistrationIds = espresso_google_checkout_get_attendee_and_registration_id();
	return array_key_exists('attendee_id', $attendeeAndRegistrationIds) ? $attendeeAndRegistrationIds['attendee_id'] : $attendee_id;
}

function espresso_transactions_google_checkout_prepare_payment_data($payment_data) {
	$attendeeAndRegistrationIds = espresso_google_checkout_get_attendee_and_registration_id();
	$payment_data['registration_id'] = $attendeeAndRegistrationIds['registration_id'];
	$_REQUEST['registration_id'] = $attendeeAndRegistrationIds['registration_id'];
	return $payment_data;
}

/**
 * gets google payment data from XML posted input, or retrieves that XML
 * from google using the posted 'serial number' from google.
 * @return array(rootXmlElement,xmlTree) 
 */
function espresso_google_checkout_get_response() {
	global $gResponseRootAndData;
	if (isset($gResponseRootAndData)) {
		return $gResponseRootAndData;
	}
	$debugInfoMsg = '';
	$google_checkout_settings = get_option('event_espresso_google_checkout_settings');
	$google_checkout_id = empty($google_checkout_settings['google_checkout_id']) ? '' : $google_checkout_settings['google_checkout_id'];
	$google_checkout_key = empty($google_checkout_settings['google_checkout_key']) ? '' : $google_checkout_settings['google_checkout_key'];
	$use_sandbox = $google_checkout_settings['use_sandbox'] ? 'sandbox' : 'production';
	$gResponse = new Espresso_GoogleResponse($google_checkout_id, $google_checkout_key);
	$gResponse->SetLogFiles('google_checkout_error_logs', 'google_checkout_notice_logs', Espresso_L_ALL);  //Change this to L_ON to log
	$fakeData=false;
	if($fakeData){
		$xml_response=<<<HEREDOC
<?xml version="1.0" encoding="UTF-8"?>
<authorization-amount-notification xmlns="http://checkout.google.com/schema/2" serial-number="142933266433673-00005-6">
  <avs-response>Y</avs-response>
  <authorization-expiration-date>2012-12-18T18:54:49.000Z</authorization-expiration-date>
  <cvn-response>M</cvn-response>
  <authorization-amount currency="USD">1.0</authorization-amount>
  <order-summary>
    <total-refund-amount currency="USD">0.0</total-refund-amount>
    <risk-information>
      <eligible-for-protection>false</eligible-for-protection>
      <avs-response>Y</avs-response>
      <cvn-response>M</cvn-response>
      <partial-cc-number>4478</partial-cc-number>
      <buyer-account-age>10</buyer-account-age>
      <ip-address>96.50.22.94</ip-address>
      <billing-address>
        <fax></fax>
        <address1>AUTH+OK</address1>
        <address2></address2>
        <phone>250) 777-9876</phone>
        <email>gatewaysbuyer@eventespresso.com</email>
        <contact-name>AUTH+OK</contact-name>
        <company-name></company-name>
        <postal-code>V5H3S5</postal-code>
        <country-code>CA</country-code>
        <city>okk</city>
        <region>BC</region>
      </billing-address>
    </risk-information>
    <total-charge-amount currency="USD">0.0</total-charge-amount>
    <purchase-date>2012-12-11T18:54:49.000Z</purchase-date>
    <authorization>
      <authorization-expiration-date>2012-12-18T18:54:49.000Z</authorization-expiration-date>
      <authorization-amount currency="USD">1.0</authorization-amount>
    </authorization>
    <archived>false</archived>
    <google-order-number>142933266433673</google-order-number>
    <total-chargeback-amount currency="USD">0.0</total-chargeback-amount>
    <buyer-marketing-preferences>
      <email-allowed>false</email-allowed>
    </buyer-marketing-preferences>
    <buyer-shipping-address>
      <fax></fax>
      <structured-name>
        <first-name>mikey</first-name>
        <last-name>G</last-name>
      </structured-name>
      <address1>awsf</address1>
      <address2></address2>
      <phone>250 234-3456</phone>
      <email>gatewaysbuyer@eventespresso.com</email>
      <contact-name>mikey G</contact-name>
      <company-name></company-name>
      <postal-code>V9E2A5</postal-code>
      <country-code>CA</country-code>
      <city>wef</city>
      <region>BC</region>
    </buyer-shipping-address>
    <fulfillment-order-state>NEW</fulfillment-order-state>
    <financial-order-state>CHARGEABLE</financial-order-state>
    <order-total currency="USD">1.0</order-total>
    <buyer-id>267852635795564</buyer-id>
    <promotions />
    <shopping-cart>
      <merchant-private-data>attendee_id=1,registration_id=1-50c67a6f172e1</merchant-private-data>
      <items>
        <item>
          <item-name>General Admission</item-name>
          <item-description>General Admission for test event 1. Attendee: wef fwe</item-description>
          <quantity>1</quantity>
          <unit-price currency="USD">1.0</unit-price>
        </item>
      </items>
    </shopping-cart>
    <order-adjustment>
      <merchant-codes />
      <adjustment-total currency="USD">0.0</adjustment-total>
      <total-tax currency="USD">0.0</total-tax>
    </order-adjustment>
  </order-summary>
  <google-order-number>142933266433673</google-order-number>
  <timestamp>2012-12-11T18:55:52.456Z</timestamp>
</authorization-amount-notification>		
		
HEREDOC;
	}else{
	$xml_response = isset($HTTP_RAW_POST_DATA) ?
			$HTTP_RAW_POST_DATA : file_get_contents("php://input");
	}
	$debugInfoMsg.="message received from google:$xml_response\n\n";
	if(strlen($xml_response)==0){//no post body
		
		return array("","");
	}else{
		if (strpos($xml_response, "xml") == FALSE) {
			$serial_array = array();
			parse_str($xml_response, $serial_array);
			$serial_number = $serial_array["serial-number"];
			if (isset($serial_number)) {
				$debugInfoMsg.="\n\nthey sent us a serial code\n\n";
				$gRequest = new Espresso_GoogleNotificationHistoryRequest($google_checkout_id, $google_checkout_key, $use_sandbox);
				$sleepTime = 0;
				do {
					$raw_xml_array = $gRequest->SendNotificationHistoryRequest($serial_number);
					$debugInfoMsg.="xml received:" . print_r($raw_xml_array, true) . "\n";
					sleep($sleepTime++);
				} while ($raw_xml_array[0] != 200 && $sleepTime < 5);
				$raw_xml = $raw_xml_array[1];

				$gResponse->SendAck($serial_number, false);
				if($google_checkout_settings['use_sandbox'])wp_mail($org_options['contact_email'],__("Event Espresso-Google Wallet debug info","event_espresso"),sprintf(__("we received these request params:%s, and are responding with an ACK to google containing the serial number %s. They, in turn, responded with payment data xml of %s","event_espresso"),print_r($_REQUEST,true),$serial_number,$raw_xml));
				
				
			} else {
				global $org_options;
				wp_mail($org_options['contact_email'], __("Event Espresso-Google Wallet gateway IPN problem", "event_espresso"), sprintf(__("We received a message from google wallet, but were unable to get any payment data from the message:%s", "event_espresso"), print_r($xml_response, false)));
				return array(null, null);
			}
		} else {
			/* $gResponse->SetMerchantAuthentication($google_checkout_id, $google_checkout_key);
			$status = $gResponse->HttpAuthentication();
			if(! $status) {
			die('authentication failed');
			} */
			$debugInfoMsg.="\nThey sent us xml!\n";
			$raw_xml = $xml_response;
			$gResponse->SendAck(null, false);
		}
	}



	if (get_magic_quotes_gpc()) {
		$raw_xml = stripslashes($raw_xml);
	}
	$gResponseRootAndData = $gResponse->GetParsedXML($raw_xml);
	$debugInfoMsg.="final xml response root and data:" . print_r($gResponseRootAndData, true);
	return $gResponseRootAndData;
}

function espresso_process_google_checkout_ipn($payment_data) {
	global $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$google_checkout_settings = get_option('event_espresso_google_checkout_settings');
	list($root, $data) = espresso_google_checkout_get_response();
	switch ($root) {
		case "new-order-notification": {
				//notifies that a payment process has been initiated on google checkout,
				//but not necessarily finished. Don't save a payment as complete yet...
				$payment_data['txn_type'] = 'Google Checkout';
				$payment_data['txn_id'] = $data[$root]['google-order-number']['VALUE'];
				$payment_data['payment_status'] = $google_checkout_settings['default_payment_status'];
				$payment_data['txn_details'] = serialize($_REQUEST);
				if($google_checkout_settings['use_sandbox']){
					wp_mail($org_options['contact_email'], __("Event Espresso-Google Wallet gateway IPN notification", "event_espresso"), sprintf(__("We received a 'new-order-notificaiton' from 
						Google Wallet, indicating that they've begun to verify the payment info of registration %s. ", "event_espresso"), $payment_data['registration_id'], $response));
			
				}
				
				//_e("Google Checkout is verifying your payment. You will receive an email in about 1-15 minutes notifying you of your purchase status.",'event_espresso');

				break;
			}
		case "risk-information-notification": {
				break;
			}
		case "charge-amount-notification": {
				break;
			}
		case "authorization-amount-notification": {
				if($payment_data['payment_status'] != 'Completed' && $payment_data['txn_id']==$data[$root]['google-order-number']['VALUE']){//only attempt to charge if payment 
					$google_checkout_id = empty($google_checkout_settings['google_checkout_id']) ? '' : $google_checkout_settings['google_checkout_id'];
					$google_checkout_key = empty($google_checkout_settings['google_checkout_key']) ? '' : $google_checkout_settings['google_checkout_key'];
					$use_sandbox = $google_checkout_settings['use_sandbox'] ? 'sandbox' : 'production';

					$google_order_number = $data[$root]['google-order-number']['VALUE'];
					//$tracking_data = array("Z12345" => "UPS", "Y12345" => "Fedex");
					$GChargeRequest = new Espresso_GoogleRequest($google_checkout_id, $google_checkout_key, $use_sandbox);
					//$GRequest->SetCertificatePath($certificate_path);
					//$GChargeRequest->SendChargeAndShipOrder($google_order_number, $tracking_data);
					list($status, $response) = $GChargeRequest->SendChargeOrder($google_order_number);
					if ($status == 200) {
						$payment_data['payment_status'] = 'Completed';
					} else {
						wp_mail($org_options['contact_email'], __("Event Espresso-Google Wallet gateway IPN problem", "event_espresso"), sprintf(__("We received an 'authorization-amount-notification from Google Wallet, 
									indicating we were ready to charge for a payment from %s, but when doing so we received this error message from Google Wallet. The message had status %s, and contained %s. 
									Note, you can login to google wallet using your merchant account and manually charge it, and also manually approve the registration in Event Espresso on the event's attendee page.", "event_espresso"), $payment_data['registration_id'], $status,print_r($data,true)));
					}
				}
				break;
			}
		case "refund-amount-notification": {
				break;
			}
		case "chargeback-amount-notification": {
				break;
			}
		case "order-numbers": {
				break;
			}
		case "invalid-order-numbers": {
				break;
			}
		case "order-state-change-notification": {
			wp_mail($org_options['contact_email'], __("Event Espresso-Google Wallet gateway IPN Order State Changed", "event_espresso"), sprintf(__("Received an 'order-state-change-notification' form Google checkout,
				but wasn't sure how to handle it... This could mean a payment was declined. Here's the exact message:%s", "event_espresso"), $payment_data['registration_id'], print_r($data,true)));
				
			
				break;
			}
		default: {
				break;
			}
	}

	return $payment_data;
}

/**
 * function called only when the user returns from google checkout.
 * The payment may not have been approved, so this is where we explain why they're payment
 * is 'pending' (if such is the case)
 * @param type $payment_data 
 */
function espresso_process_google_checkout_done_payment($payment_data){

	if($payment_data['payment_status']=='Pending'){
		?>
<p>
	<?php _e("Google Wallet is processing your payment. This usually takes several minutes. You will be sent a notification email when
	the payment is fully approved.","event_espresso"); ?>
</p>
	<?php
	}
}

/**
 * handles payment IPN info from google just like it would be handled in the
 * transactiosn shortcode, but this function should be hoooked to the plugins_loaded
 * hook, thus executing much before any other output is generated.
 * This is necessary because google requires us to send a very specific xml resposne
 * to their ipn, not html (which is what we'd ahve to return if this were called
 * during a shortcode)
 * @return type 
 */

function espresso_google_run_transaction_code_before_shortcode() {
	if(array_key_exists('type',$_REQUEST) && array_key_exists('ipn',$_REQUEST) && 
			$_REQUEST['type']=='google_checkout' && $_REQUEST['ipn']=='true'){
		event_espresso_require_gateway("process_payments.php");
	
		$payment_data['attendee_id'] = apply_filters('filter_hook_espresso_transactions_get_attendee_id', '');
		if (isset($payment_data['attendee_id'])) {
				
			$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
			$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
			$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);

			if (espresso_return_reg_id() == false || $payment_data['registration_id'] != espresso_return_reg_id()) {
				return;
			}
			$payment_data = espresso_process_google_checkout_ipn($payment_data);
			$payment_data['txn_details'] = serialize($_REQUEST);
			espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => 'Payment for: ' . $payment_data['lname'] . ', ' . $payment_data['fname'] . '|| registration id: ' . $payment_data['registration_id'] . '|| transaction details: ' . $payment_data['txn_details']));
			
			
			$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);

			//add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');//this line shouldn't be necessary because ofa refactor I did where this
			//is added process_payments file, but apparently it hasn't gotten integrated into trunk yet...
			do_action('action_hook_espresso_email_after_payment', $payment_data);
			die;
		}
	}
}