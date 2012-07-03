<?php
if (!function_exists('event_espresso_add_attendees_to_db')) {
	//This entire function can be overridden using the "Custom Files" addon
	function event_espresso_add_attendees_to_db(){
		global $wpdb, $org_options;
		//print_r($_POST);
               // exit;
		$Organization = $org_options['organization'];
		$Organization_street1 = $org_options['organization_street1'];
		$Organization_street2 = $org_options['organization_street2'];
		$Organization_city = $org_options['organization_city'];
		$Organization_state = $org_options['organization_state'];
		$Organization_zip = $org_options['organization_zip'];
		$contact = $org_options['contact_email'];
		$contact_email = $org_options['contact_email'];
		$paypal_id = $org_options['paypal_id'];
		$paypal_cur = $org_options['currency_format'];
		$return_url = $org_options['return_url'];
		$cancel_return = $org_options['cancel_return'];
		$notify_url = $org_options['notify_url'];
		
		$default_mail= $org_options['default_mail'];
		$conf_message = $org_options['message'];
		$email_before_payment = $org_options['email_before_payment'];

		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$address = $_POST['address'];
		$address2 = $_POST['address2'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		$phone = $_POST['phone'];
		$email = $_POST['email'];
		//$num_people = $_POST ['num_people'];
		$event_id=$_POST['event_id'];
		$questions = $wpdb->get_row ( "SELECT question_groups FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'" );
		
		//$payment = $_POST['payment'];
		
		//Figure out if the person has registered using a price selection
		if ($_REQUEST['price_select'] ==true){
		
			$price_options = explode('|',$_REQUEST['price_option'], 2);
			$price_id = $price_options[0];
			$price_type = $price_options[1];
			$event_cost = event_espresso_get_final_price($price_id, $event_id);
		}else{
			$event_cost = event_espresso_get_final_price($_POST['price_id'], $event_id);
		}
		
		//Display the confirmation page
		if ($_POST['confirm_registration'] == 'true'){
			$registration_id = $_POST['registration_id'];
			echo espresso_confirm_registration($registration_id);
			return;
		}
		
	//Check to see if the registration id already exists
	$check_sql = $wpdb->get_results("SELECT attendee_session, id, registration_id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE attendee_session ='" . $_SESSION['espresso_session_id'] . "' AND event_id ='" . $event_id . "'");
	$num_rows = $wpdb->num_rows;
	
	$registration_id = $wpdb->last_result[0]->registration_id == '' ? $registration_id=uniqid('', true) : $wpdb->last_result[0]->registration_id;
	
	if (isset($_POST['admin'])) {
		 $payment_status = "Completed";
		 $payment = "Admin";
		 $payment_date = date("m-d-Y");
		 $amount_pd = $_POST["event_cost"];
		 $registration_id=uniqid('', true);
	} else{
		if ($org_options['use_captcha'] == 'Y'){//Recaptcha portion
			//require_once('includes/recaptchalib.php');
			if (!function_exists('recaptcha_check_answer')) {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/recaptchalib.php');
			}
			$resp = recaptcha_check_answer ($org_options['recaptcha_privatekey'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
			if (!$resp->is_valid) {
				echo '<h2 style="color:#FF0000;">'.__('Sorry, you did not enter the correct anti-spam phrase. Please click your browser\'s back button and try again.','event_espresso').'</h2>';
				return;
			}
		}
		// Automatically set payment status to Incomplete until a payment transaction is cleared
		$payment_status = "Incomplete";

		//$times = $wpdb->get_results("SELECT * FROM ". EVENTS_START_END_TABLE ." WHERE id='" . $_POST['start_time_id'] . "'");
		
		$times_sql =  "SELECT ese.start_time, ese.end_time, e.start_date, e.end_date ";
		$times_sql .= "FROM " .EVENTS_START_END_TABLE. " ese ";
		$times_sql .= "LEFT JOIN " .EVENTS_DETAIL_TABLE. " e ON ese.id WHERE ese.id='" . $_POST['start_time_id'] . "' AND e.id='" . $event_id . "' ";

		$times = $wpdb->get_results($times_sql);
		foreach ($times as $time){
			$start_time = $time->start_time;
			$end_time = $time->end_time;
			$start_date = $time->start_date;
			$end_date = $time->end_date;
		}
	}
	
	//If we are using the number of attendees dropdown, add that number to the DB
	//echo $_REQUEST['espresso_addtl_limit_dd'];
	if (isset($_REQUEST['espresso_addtl_limit_dd']))
		$num_people = $_POST ['num_people'];
	
		$sql=array('registration_id'=>$registration_id,'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$lname, 'fname'=>$fname, 'address'=>$address, 'address2'=>$address2, 'city'=>$city, 'state'=>$state, 'zip'=>$zip, 'email'=>$email, 'phone'=>$phone, 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'event_time'=>$start_time, 'end_time'=>$end_time, 'start_date'=>$start_date, 'end_date'=>$end_date, 'price_option'=>$price_type, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id, 'quantity'=>$num_people);
		$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						  '%s','%s','%s','%s','%s','%s','%d','%d');

		//Debugging output
		/* echo 'Debug: <br />';
	 	  print_r($sql);
		  echo '<br />';
          print 'Number of vars: ' . count ($sql);
          echo '<br />';
          print 'Number of cols: ' . count($sql_data); */
                                
		if ($num_rows > 0 ){
		
			//echo 'Num rows:'.$num_rows;
			
			
			//Delete the old data.
			$wpdb->query(" DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = '" . $registration_id . "'	AND attendee_session ='" . $_SESSION['espresso_session_id'] . "' ");
			$wpdb->query(" DELETE FROM " . EVENTS_ANSWER_TABLE . " WHERE registration_id = '" . $registration_id . "' ");
			
		}	
			//Add new or updated data		
			if (!$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql, $sql_data)){
				$error = true;
			}
							
			$attendee_id = $wpdb->insert_id;

			add_attendee_questions($questions, $registration_id, $attendee_id);
	
			//Add additional attendees to the database
			if (isset($_REQUEST['x_attendee_fname'])) {
				foreach ($_REQUEST['x_attendee_fname'] as $k => $v){
					if (trim($v) != '' && trim($_REQUEST['x_attendee_lname'][$k]) != ''){
				
						$sql_a=array('registration_id'=>$registration_id, 'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$_REQUEST['x_attendee_lname'][$k], 'fname'=>$v, 'email'=>$_REQUEST['x_attendee_email'][$k], 'address'=>$address, 'address2'=>$address2, 'city'=>$city, 'state'=>$state, 'zip'=>$zip, 'phone'=>$phone, 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'event_time'=>$start_time, 'end_time'=>$end_time, 'start_date'=>$start_date, 'end_date'=>$end_date, 'price_option'=>$price_type, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id, 'quantity'=>$num_people);
						$sql_data_a = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						  '%s','%s','%s','%s','%s','%s','%d','%d');
									  
						$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql_a, $sql_data_a);
							//print_r( $questions );
							
							//Debugging output
							/* echo 'Debug: <br />';
							  print_r($sql);
							  echo '<br />';
							  print 'Number of vars: ' . count ($sql);
							  echo '<br />';
							  print 'Number of cols: ' . count($sql_data); */
		  
							echo add_attendee_questions($questions, $registration_id, $wpdb->insert_id);
								//echo 'Failed';
							//echo $wpdb->insert_id;
						
					}
					
				}
			}
	
	
			//Add user data if needed
			if (get_option('events_members_active') == 'true'){
				require_once(EVENT_ESPRESSO_MEMBERS_DIR . "member_functions.php"); //Load Members functions
				require_once(EVENT_ESPRESSO_MEMBERS_DIR . "user_vars.php"); //Load Members functions
				if ($userid != 0){
					event_espresso_add_user_to_event($event_id, $userid, $attendee_id);
				}
			}
				//This shows the payment page
				if (isset($_POST['admin'])) return $attendee_id;
					//return event_espresso_payment_confirmation($attendee_id);
					return events_payment_page($attendee_id);
				
		}
}

if (!function_exists('event_espresso_add_attendees_to_db_multi')) {
	//This entire function can be overridden using the "Custom Files" addon
	function event_espresso_add_attendees_to_db_multi(){
		global $wpdb, $org_options, $events_in_session;
               //echo "<pre>", print_r($events_in_session), "</pre>";

                if (count($events_in_session) > 0) {
                
                     foreach($events_in_session as $k=>$v){
                         
                         event_espresso_add_attendees_to_db_multi_process($k, $v);
                         
                     }

                }


        }
}



if (!function_exists('event_espresso_add_attendees_to_db_multi_process')) {
	/*
         * Will be used by the multi event registration.
         * - will be called from event_espresso_add_attendees_to_db_multi for each one of the events
         * - Will use session vars intead of the POST vars
         */
	function event_espresso_add_attendees_to_db_multi_process($event_id, $session_vars){
		global $wpdb, $org_options, $events_in_session;
		//print_r($_POST);
                //echo "<pre>", print_r($session_vars), "</pre>";

                
		$Organization = $org_options['organization'];
		$Organization_street1 = $org_options['organization_street1'];
		$Organization_street2 = $org_options['organization_street2'];
		$Organization_city = $org_options['organization_city'];
		$Organization_state = $org_options['organization_state'];
		$Organization_zip = $org_options['organization_zip'];
		$contact = $org_options['contact_email'];
		$contact_email = $org_options['contact_email'];
		$paypal_id = $org_options['paypal_id'];
		$paypal_cur = $org_options['currency_format'];
		$return_url = $org_options['return_url'];
		$cancel_return = $org_options['cancel_return'];
		$notify_url = $org_options['notify_url'];

		$default_mail= $org_options['default_mail'];
		$conf_message = $org_options['message'];
		$email_before_payment = $org_options['email_before_payment'];

		$fname = $session_vars['fname'];
		$lname = $session_vars['lname'];
		$address = $session_vars['address'];
		$address2 = $session_vars['address2'];
		$city = $session_vars['city'];
		$state = $session_vars['state'];
		$zip = $session_vars['zip'];
		$phone = $session_vars['phone'];
		$email = $session_vars['email'];
		//$num_people = $_POST ['num_people'];
		$event_id=$event_id;
		$questions = $wpdb->get_row ( "SELECT question_groups FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'" );

		//$payment = $_POST['payment'];

		//Figure out if the person has registered using a price selection
		/*if ($_REQUEST['price_select'] ==true){

			$price_options = explode('|',$_REQUEST['price_option'], 2);
			$price_id = $price_options[0];
			$price_type = $price_options[1];
			$event_cost = event_espresso_get_final_price($price_id, $event_id);
		}else{
			$event_cost = event_espresso_get_final_price($_POST['price_id'], $event_id);
		}*/

                $event_cost = $_SESSION['event_espresso_grand_total'];
		//Display the confirmation page
		if ($_POST['confirm_registration'] == 'true'){
			$registration_id = $_POST['registration_id'];
			//echo espresso_confirm_registration($registration_id);
			//return;
		}

	//Check to see if the registration id already exists
	$check_sql = $wpdb->get_results("SELECT attendee_session, id, registration_id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE attendee_session ='" . $_SESSION['espresso_session_id'] . "' AND event_id ='" . $event_id . "'");
	$num_rows = $wpdb->num_rows;
	//session_destroy();
	//echo $_SESSION['espresso_session_id'];
	$registration_id = $wpdb->last_result[0]->registration_id == '' ? $registration_id=uniqid('', true) : $wpdb->last_result[0]->registration_id;

	if (isset($_POST['admin'])) {
		 $payment_status = "Completed";
		 $payment = "Admin";
		 $payment_date = date("m-d-Y");
		 $amount_pd = $_POST["event_cost"];
		 $registration_id=uniqid('', true);
	} else{
		if ($org_options['use_captcha'] == 'Y'){//Recaptcha portion
			//require_once('includes/recaptchalib.php');
			if (!function_exists('recaptcha_check_answer')) {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/recaptchalib.php');
			}
			$resp = recaptcha_check_answer ($org_options['recaptcha_privatekey'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
			if (!$resp->is_valid) {
				echo '<h2 style="color:#FF0000;">'.__('Sorry, you did not enter the correct anti-spam phrase. Please click your browser\'s back button and try again.','event_espresso').'</h2>';
				return;
			}
		}
      
		// Automatically set payment status to Incomplete until a payment transaction is cleared
		$payment_status = "Incomplete";

		//$times = $wpdb->get_results("SELECT * FROM ". EVENTS_START_END_TABLE ." WHERE id='" . $_POST['start_time_id'] . "'");

		$times_sql =  "SELECT ese.start_time, ese.end_time, e.start_date, e.end_date ";
		$times_sql .= "FROM wp_events_start_end ese ";
		$times_sql .= "LEFT JOIN wp_events_detail e ON ese.id WHERE ese.id='" . $session_vars['start_time_id'] . "' AND e.id='" . $event_id . "' ";

		$times = $wpdb->get_results($times_sql);
		foreach ($times as $time){
			$start_time = $time->start_time;
			$end_time = $time->end_time;
			$start_date = $time->start_date;
			$end_date = $time->end_date;
		}
	}


	$sql=array('registration_id'=>$registration_id,'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$lname, 'fname'=>$fname, 'address'=>$address, 'address2'=>$address2, 'city'=>$city,
						'state'=>$state, 'zip'=>$zip, 'email'=>$email, 'phone'=>$phone, 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'event_time'=>$start_time,
						'end_time'=>$end_time, 'start_date'=>$start_date, 'end_date'=>$end_date, 'price_option'=>$price_type, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id
			);
			$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						  '%s','%s','%s','%s','%s','%s','%d');


		if ($num_rows > 0 ){

			//echo 'Num rows:'.$num_rows;


			//Delete the old data.
			$wpdb->query(" DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = '" . $registration_id . "'	AND attendee_session ='" . $_SESSION['espresso_session_id'] . "' ");
			$wpdb->query(" DELETE FROM " . EVENTS_ANSWER_TABLE . " WHERE registration_id = '" . $registration_id . "' ");

		}
			//Add new or updated data
			if (!$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql, $sql_data)){
				$error = true;
			}

			$attendee_id = $wpdb->insert_id;

			add_attendee_questions($questions, $registration_id, $attendee_id, array('session_vars'=>$session_vars));

			//Add additional attendees to the database
			if (isset($session_vars['x_attendee_fname'])) {
				foreach ($session_vars['x_attendee_fname'] as $k => $v){
					if (trim($v) != '' && trim($session_vars['x_attendee_lname'][$k]) != ''){

						$sql_a=array('registration_id'=>$registration_id,'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$session_vars['x_attendee_lname'][$k], 'fname'=>$v, 'address'=>$address, 'city'=>$city,
								'state'=>$state, 'zip'=>$zip, 'email'=>$session_vars['x_attendee_email'][$k], 'phone'=>$phone, 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'event_time'=>$start_time,
								'end_time'=>$end_time, 'start_date'=>$start_date, 'end_date'=>$end_date, 'price_option'=>$price_option, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id
						);
						$sql_data_a = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
									  '%s','%s','%s','%s','%s','%s','%s','%s','%d');

						$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql_a, $sql_data_a);
							//print_r( $questions );

							//echo add_attendee_questions($questions, $registration_id, $wpdb->insert_id);
								//echo 'Failed';
							//echo $wpdb->insert_id;

					}

				}
			}


			//Add user data if needed
			if (get_option('events_members_active') == 'true'){
				require_once(EVENT_ESPRESSO_MEMBERS_DIR . "member_functions.php"); //Load Members functions
				require_once(EVENT_ESPRESSO_MEMBERS_DIR . "user_vars.php"); //Load Members functions
				if ($userid != 0){
					event_espresso_add_user_to_event($event_id, $userid, $attendee_id);
				}
			}
				//This shows the payment page
				if (isset($_POST['admin'])) return $attendee_id;
					//return event_espresso_payment_confirmation($attendee_id);
					//return events_payment_page($attendee_id);

                                        	if($amount_pd != '0.00'){
		//Show payment options
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH. "gateways/gateway_display.php");
		}
		//Check to see if the site owner wants to send an confirmation eamil before payment is recieved.
		if ($org_options['email_before_payment'] == 'Y'){
			event_espresso_email_confirmations(array('registration_id' => $registration_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
		}
	}else{
		event_espresso_email_confirmations(array('registration_id' => $registration_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
	}

		}
}
