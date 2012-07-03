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
		
		//Display the confirmation page
		if ($_POST['confirm_registration'] == 'true'){
			$registration_id = $_POST['registration_id'];
			echo espresso_confirm_registration($registration_id);
			return;
		}

// Automatically set payment status to Incomplete until a payment transaction is cleared
 
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
	
	//session_destroy();
	//echo $_SESSION['espresso_session_id'];
	
	$check_sql = $wpdb->get_results("SELECT attendee_session, id, registration_id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE attendee_session ='" . $_SESSION['espresso_session_id'] . "' AND event_id ='" . $event_id . "'");
	$num_rows = $wpdb->num_rows;
	
	
	$registration_id = $wpdb->last_result[0]->registration_id == '' ? $registration_id=uniqid('', true) : $wpdb->last_result[0]->registration_id;

	
	$sql=array('registration_id'=>$registration_id,'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$lname, 'fname'=>$fname, 'address'=>$address, 'city'=>$city,
						'state'=>$state, 'zip'=>$zip, 'email'=>$email, 'phone'=>$phone, 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'event_time'=>$start_time,
						'end_time'=>$end_time, 'price_option'=>$price_type, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id
			);
			$sql_data = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						  '%s','%s','%s','%s','%s','%s','%d');

		 /*echo 'Debug: <br />';
	 	  print_r($sql);
		  echo '<br />';
          print 'Number of vars: ' . count ($sql);
          echo '<br />';
          print 'Number of cols: ' . count($sql_data); */
                                
		if ($num_rows > 0 ){
		
			//echo 'Num rows:'.$num_rows;
			
			
			//Delete the old data.
			$wpdb->query(" DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = '" . $registration_id . "'	AND attendee_session ='" . $_SESSION['espresso_session_id'] . "' ");

			
		}	
			//Add new or updated data		
			if (!$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql, $sql_data)){
					$error = true;
				}
							
			$attendee_id = $wpdb->insert_id;
	
			//Add additional attendees to the database
			if (isset($_REQUEST['x_attendee_fname'])) {
				foreach ($_REQUEST['x_attendee_fname'] as $k => $v){
					if (trim($v) != '' && trim($_REQUEST['x_attendee_lname'][$k]) != ''){
						
						
						$sql_a=array('registration_id'=>$registration_id,'attendee_session'=>$_SESSION['espresso_session_id'], 'lname'=>$_REQUEST['x_attendee_lname'][$k], 'fname'=>$v, 'address'=>$address, 'city'=>$city,
								'state'=>$state, 'zip'=>$zip, 'email'=>$_REQUEST['x_attendee_email'][$k], 'phone'=>$_REQUEST['x_attendee_phone'][$k], 'payment'=>$payment, 'amount_pd'=>$amount_pd, 'event_time'=>$start_time,
								'end_time'=>$end_time, 'price_option'=>$price_option, 'organization_name'=>$organization_name, 'country_id'=>$country_id, 'payment_status'=>$payment_status, 'payment_date'=>$payment_date, 'event_id'=>$event_id
						);
						$sql_data_a = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
									  '%s','%s','%s','%s','%s','%s','%d');
									  
						if (!$wpdb->insert( EVENTS_ATTENDEE_TABLE, $sql_a, $sql_data_a)){
							$error = true;
						}
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
				//echo $email_questions;
				
				//This shows the payment page
				if (isset($_POST['admin'])) return $attendee_id;
					//return event_espresso_payment_confirmation($attendee_id);
					return events_payment_page($attendee_id);
				
		}
}