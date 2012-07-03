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
		$events_listing_type = $org_options['events_listing_type'];
		$default_mail= $org_options['default_mail'];
		$conf_message = $org_options['message'];
		$email_before_payment = $org_options['email_before_payment'];

		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$address = $_POST['address'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		$phone = $_POST['phone'];
		$email = $_POST['email'];
		//$num_people = $_POST ['num_people'];
		$event_id=$_POST['event_id'];

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


		// Automatically set payment status to Incomplete until a payment transaction is cleared
  $registration_id=uniqid('', true);
	if (isset($_POST['admin'])) {
		 $payment_status = "Completed";
		 $payment = "Admin";
		 $payment_date = date("m-d-Y");
		 $event_cost = $_POST["event_cost"];
	} else{
		$payment_status = "Incomplete";

		$times = $wpdb->get_results("SELECT * FROM ". EVENTS_START_END_TABLE ." WHERE id='" . $_POST['start_time_id'] . "'");
		foreach ($times as $time){
			$start_time = $time->start_time;
			$end_time = $time->end_time;
		}
	}
		//Ronalds addition
		$sql = "INSERT INTO " . EVENTS_ATTENDEE_TABLE . "(registration_id, lname, fname, address, city, state, zip, email, phone, payment, amount_pd, event_id,  event_time, end_time, price_option, organization_name, country_id, payment_status, payment_date ) VALUES ('$registration_id', '$lname', '$fname', '$address', '$city', '$state', '$zip', '$email', '$phone', '$payment', '$event_cost', '$event_id', '$start_time', '$end_time', '$price_type', '$organization_name', '$country', '$payment_status', '$payment_date')";
		//End Ronalds addition

		$wpdb->query($wpdb->prepare($sql));
		$attendee_id = $wpdb->insert_id;

//Ronalds addition
//Add additional attendees to the database
 	if (isset($_REQUEST['x_attendee_fname'])) {
		foreach ($_REQUEST['x_attendee_fname'] as $k => $v){
			if (trim($v) != '' && trim($_REQUEST['x_attendee_lname'][$k]) != ''){
				$sql = "INSERT INTO " . EVENTS_ATTENDEE_TABLE . "(registration_id, lname, fname, address, city, state, zip, email, phone, payment, amount_pd, event_id, event_time, end_time, price_option, organization_name, country_id, payment_status, payment_date ) VALUES ('$registration_id', '".$_REQUEST['x_attendee_lname'][$k]."', '$v', '$address', '$city', '$state', '$zip', '".$_REQUEST['x_attendee_email'][$k]."', '".$_REQUEST['x_attendee_phone'][$k]."', '$payment', '$event_cost',  '$event_id', '$start_time', '$end_time', '$price_type', '$organization_name', '$country', '$payment_status', '$payment_date')";
				$wpdb->query($wpdb->prepare($sql));
				
			}
		}
	}
//End Ronalds addition

		//Add user data if needed
		if (get_option('events_members_active') == 'true'){
			require_once(EVENT_ESPRESSO_MEMBERS_DIR . "member_functions.php"); //Load Members functions
			require_once(EVENT_ESPRESSO_MEMBERS_DIR . "user_vars.php"); //Load Members functions
			if ($userid != 0){
				event_espresso_add_user_to_event($event_id, $userid, $attendee_id);
			}
		}


		$questions = $wpdb->get_row ( "SELECT question_groups FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'" );
                
                $question_groups = unserialize($questions->question_groups);
                //print_r($question_groups);

                if (count($question_groups) > 0){
					$questions_in = '';

					foreach ($question_groups as $g_id) $questions_in .= $g_id . ',';

					$questions_in = substr($questions_in,0,-1);
					$group_name = '';
					$counter = 0;

					$questions = $wpdb->get_results("SELECT q.*, qg.group_name
													FROM " . EVENTS_QUESTION_TABLE . " q
													JOIN " .  EVENTS_QST_GROUP_REL_TABLE . " qgr
													on q.id = qgr.question_id
													JOIN " . EVENTS_QST_GROUP_TABLE . " qg
													on qg.id = qgr.group_id
													WHERE qgr.group_id in ( " .   $questions_in
													. ") AND q.system_name IS NULL ORDER BY id ASC");
					$num_rows = $wpdb->num_rows;

					if ($num_rows > 0 ){
						global $email_questions;//Make a global variable to hold the answers to the questions to be sent in the admin email.
						$email_questions = '<p>'.__('Form Questions:','event_espresso').'<br />';
						foreach ( $questions as $question ) {
							switch ($question->question_type) {
								case "TEXT" :
								case "TEXTAREA" :
								case "DROPDOWN" :
									$post_val = $_POST [$question->question_type . '_' . $question->id];
									$wpdb->query ( "INSERT into ".EVENTS_ANSWER_TABLE." (registration_id, question_id, answer)
													values ('" . $registration_id . "', '" . $question->id . "', '" . $post_val . "')" );
									$email_questions .=  $question->question.': '.$post_val.'<br />';
								break;
								case "SINGLE" :
									$post_val = $_POST [$question->question_type . '_' . $question->id];
									$wpdb->query ( "INSERT into ".EVENTS_ANSWER_TABLE." (registration_id, question_id, answer)
													values ('" . $registration_id . "', '" . $question->id . "', '" . $post_val . "')" );
									$email_questions .=  $question->question.': '.$post_val.'<br />';
								break;
								case "MULTIPLE" :
									$value_string = '';
									for ($i=0; $i<count($_POST[$question->question_type.'_'.$question->id]); $i++){
										$value_string .= $_POST[$question->question_type.'_'.$question->id][$i].",";
									}
								
									$wpdb->query ( "INSERT INTO ".EVENTS_ANSWER_TABLE." (registration_id, question_id, answer)
													VALUES ('" . $registration_id . "', '" . $question->id . "', '" . $value_string . "')" );
									$email_questions .=  $question->question.': '.$value_string.'<br />';
								break;
							}
						}
						$email_questions .= '</p>';
					}
				}
				//Ronalds Addition
				if (isset($_POST['admin'])) return $attendee_id;
					return event_espresso_payment_confirmation($attendee_id);//This function shows the payment page
				}
				//End Ronalds Addition
}

if (!function_exists('event_espresso_payment_confirmation')) {
	function event_espresso_payment_confirmation($attendee_id){
		global $wpdb, $org_options;

		$attendees = $wpdb->get_results("SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = '".$attendee_id."'");
		foreach ($attendees as $attendee){
			$attendee_id = $attendee->id;
			$registration_id = $attendee->registration_id;
			$lname = $attendee->lname;
			$fname = $attendee->fname;
			$address = $attendee->address;
			$city = $attendee->city;
			$state = $attendee->state;
			$zip = $attendee->zip;
			$email = $attendee->email;
			$phone = $attendee->phone;
			$date = $attendee->date;
			$payment_status = $attendee->payment_status;
			$txn_type = $attendee->txn_type;
			$amount_pd = $attendee->amount_pd;
			$payment_date = $attendee->payment_date;
			$event_id = $attendee->event_id;
			$event_time = $attendee->event_time;
		}
		//Send screen confirmation & forward to paypal if selected.
		if ($event_cost== '0.00'){
			echo '<p>' . __('This is a free event. Details have been sent to your email.','event_espresso').'</p>';
			echo '<p>' . __('Your Registration data has been added to our records.','event_espresso') . '</p>';
			//Send the email confirmation
			//@params $attendee_id, $send_admin_email, $send_attendee_email
			//event_espresso_email_confirmations($attendee_id, 'true', 'true' );
			event_espresso_email_confirmations(array('registration_id' => $registration_id, 'attendee_id' => $attendee_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
		}else{
			//Check to see if the site owner wants to send an eamil before payment is recieved.
			if ($org_options['email_before_payment'] == 'Y'){
				//event_espresso_email_confirmations($registration_id, 'true', 'true' );
				event_espresso_email_confirmations(array('registration_id' => $registration_id, 'attendee_id' => $attendee_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
			}

			//Display the payment page
			return events_payment_page($attendee_id);
		}
	}
}