<?php
//Payment Page/PayPal Buttons - Used to display the payment options and the payment link in the email. Used with the {ESPRESSO_PAYMENTS} tag

//This is the initial PayPal button
function events_payment_page($attendee_id){

	$today = date("m-d-Y");
	global $wpdb, $org_options, $simpleMath;

	$Organization =$org_options['organization'];
	$Organization_street1 =$org_options['organization_street1'];
	$Organization_street2=$org_options['organization_street2'];
	$Organization_city =$org_options['organization_city'];
	$Organization_state=$org_options['organization_state'];
	$Organization_zip =$org_options['organization_zip'];
	$contact =$org_options['contact_email'];
	$registrar = $org_options['contact_email'];
	$currency_format = $org_options['currency_format'];
	$events_listing_type =$org_options['events_listing_type'];
	$message =$org_options['message'];
	$return_url = $org_options['return_url'];
	$cancel_return = $org_options['cancel_return'];
	$notify_url = $org_options['notify_url'];

	$attendees = $wpdb->get_results("SELECT * FROM ". EVENTS_ATTENDEE_TABLE ." WHERE id ='" . $attendee_id . "'");
	foreach ($attendees as $attendee){
		//$attendee_id = $attendee->id;
		$attendee_last = $attendee->lname;
		$attendee_first = $attendee->fname;
		$attendee_address = $attendee->address;
		$attendee_city = $attendee->city;
		$attendee_state = $attendee->state;
		$attendee_zip = $attendee->zip;
		$attendee_email = $attendee->email;
		//$attendee_organization_name = $attendee->organization_name;
		//$attendee_country = $attendee->country_id;
		$phone = $attendee->phone;
		$date = $attendee->date;
		//$num_people = $attendee->quantity;
		$payment_status = $attendee->payment_status;
		$txn_type = $attendee->txn_type;
		//$event_cost = $attendee->amount_pd;
		$payment_date = $attendee->payment_date;
		$event_id = $attendee->event_id;
		$registration_id=$attendee->registration_id;
	}
	if (function_exists("save_extra_user_profile_fields")){
		if (get_option('events_members_active') == 'true'){
			//PLACE HOLDER
		}
	}

	$num_people = $wpdb->get_results("SELECT COUNT(registration_id) FROM ". EVENTS_ATTENDEE_TABLE ." WHERE registration_id ='" . $registration_id . "'", ARRAY_N);
	$num_people = $num_people[0][0];

	$events = $wpdb->get_results("SELECT * FROM ". EVENTS_DETAIL_TABLE . " WHERE id ='" . $event_id . "'");
	foreach ($events as $event){
		//$event_id = $event->id;
		$event_name = $event->event_name;
		$event_desc = $event->event_desc;
		$event_description = $event->event_desc;
		$event_identifier = $event->event_identifier;
		$send_mail = $event->send_mail;
		$active = $event->is_active;
		$conf_mail = $event->conf_mail;
		//$alt_email = $event->alt_email; //This is used to get the alternate email address that a payment can be made to using PayPal
		if (function_exists('event_espresso_coupon_payment_page')) {
			$use_coupon_code = $event->use_coupon_code;
		}
		if (function_exists('event_espresso_groupon_payment_page')) {
			$use_groupon_code=$event->use_groupon_code;
		}
	}

	$attendee_name = $attendee_first.' '.$attendee_last;
	
	//Figure out if the person has registered using a price selection
	if ($_REQUEST['price_select'] ==true){
		
		$price_options = explode('|',$_REQUEST['price_option'], 2);
		$price_id = $price_options[0];
		$price_type = $price_options[1];
		
		$event_cost = event_espresso_get_final_price($price_id, $event_id);

	}else{
		//$event_cost = $_POST['event_cost'];
		$event_cost = event_espresso_get_final_price($_POST['price_id'], $event_id);
	}
	
	//Test the early discount amount to make sure we are getting the right amount
	//print_r(early_discount_amount($event_id, $event_cost));
	
	$event_price = number_format($event_cost,2, '.', '');
	$event_price_x_attendees = number_format($event_cost * $num_people,2, '.', '');
	$event_cost = number_format($simpleMath->multiply($event_cost, $num_people),2, '.', '');

	if (function_exists('event_espresso_coupon_payment_page') && $_REQUEST['coupon_code'] != ''){
		$event_cost = event_espresso_coupon_payment_page($use_coupon_code, $event_id, $event_cost, $attendee_id, $num_people);
		$event_price = number_format($event_cost / $num_people,2, '.', '');
		$event_price_x_attendees = number_format($event_cost,2, '.', '');
	}else if (function_exists('event_espresso_groupon_payment_page') && $_REQUEST['groupon_code'] != ''){
		$event_cost = event_espresso_groupon_payment_page($use_groupon_code, $event_id, $event_cost, $attendee_id);
	}else{
		if($event_cost == '0.00'){
			$event_cost = '0.00';
			$payment_status = __('Completed','event_espresso');
			$sql=array('amount_pd'=>$event_cost, 'payment_status'=>$payment_status, 'payment_date'=>$today);
			$sql_data = array('%s','%s','%s');
		}else{
			$sql=array('amount_pd'=>$event_cost, 'payment_status'=>$payment_status);
			$sql_data = array('%s','%s');
		}

		$update_id = array('id'=> $attendee_id);
		$wpdb->update(EVENTS_ATTENDEE_TABLE, $sql, $update_id, $sql_data, array( '%d' ) );
	}
	$display_cost = ( $event_cost != "0.00" ) ? $org_options['currency_symbol'] . $event_cost : __('Free','event_espresso') ;
	
	//Pull in the template
	if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR."payment_page.php")){
		require_once(EVENT_ESPRESSO_TEMPLATE_DIR."payment_page.php");//This is the path to the template file if available
	}else{
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."templates/payment_page.php");
	}
}

//This is the alternate PayPal button used for the email
function event_espresso_pay(){
		global $wpdb, $org_options;
		//Make sure id's are empty
		$registration_id=0;
		
		$registration_id=$_GET['registration_id'];
		if ($registration_id ==0){
			_e('Please check your email for payment information.','event_espresso');
		}else{
			$attendees = $wpdb->get_results("SELECT * FROM ". EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . $registration_id . "'");
			foreach ($attendees as $attendee){
				$attendee_id = $attendee->id;
				$lname = $attendee->lname;
				$fname = $attendee->fname;
				$address = $attendee->address;
				$city = $attendee->city;
				$state = $attendee->state;
				$zip = $attendee->zip;
				$attendee_email = $attendee->email;
				$phone = $attendee->phone;
				$date = $attendee->date;
				$payment_status = $attendee->payment_status;
				$txn_type = $attendee->txn_type;
				$event_cost = $attendee->amount_pd;
				$payment_date = $attendee->payment_date;
				$event_id = $attendee->event_id;
				$attendee_name = $fname." ".$lname;
			}

			$Organization =$org_options['organization'];
			$Organization_street1 =$org_options['organization_street1'];
			$Organization_street2=$org_options['organization_street2'];
			$Organization_city =$org_options['organization_city'];
			$Organization_state=$org_options['organization_state'];
			$Organization_zip =$org_options['organization_zip'];
			$contact =$org_options['contact_email'];
			$registrar = $org_options['contact_email'];
			$paypal_cur =$org_options['currency_format'];
			$events_listing_type =$org_options['events_listing_type'];
			$message =$org_options['message'];
			$return_url = $org_options['return_url'];
			$cancel_return = $org_options['cancel_return'];
			$notify_url = $org_options['notify_url'];

			$paypal_settings = get_option('event_espresso_paypal_settings');
				$paypal_id = $paypal_settings['paypal_id'];
				$image_url = $paypal_settings['image_url'];
				$currency_format = $paypal_settings['currency_format'];
				$use_sandbox = $paypal_settings['use_sandbox'];

			//Query Database for event and get variable
			$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
			foreach ($events as $event){
				//$event_id = $event->id;
				$event_name = $event->event_name;
				$event_desc = $event->event_desc;
				$event_description = $event->event_desc;
				$event_identifier = $event->event_identifier;
				$active = $event->is_active;
			}
		//Pull in the template
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR."return_payment.php")){
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR."return_payment.php");//This is the path to the template file if available
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH."templates/return_payment.php");
		}
			
	}
}
