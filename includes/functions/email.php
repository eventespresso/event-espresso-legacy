<?php

//Email Confirmations
if (!function_exists('event_espresso_email_confirmations')) {
	function event_espresso_email_confirmations($atts){

		//print_r( $atts );
		//Extract the attendee_id and registration_id
		extract($atts);
		$registration_id = "{$registration_id}";
		$attendee_id = "{$attendee_id}";
		$send_admin_email = "{$send_admin_email}";
		$send_attendee_email = "{$send_attendee_email}";
		$registration_id = $registration_id != '' ? $registration_id : espresso_registration_id($attendee_id);
		global $wpdb, $org_options;
		//Define email headers
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: " . $org_options['organization'] . " <". $org_options['contact_email'] . ">\r\n";
		$headers .= "Reply-To: " . $org_options['organization'] . "  <" . $org_options['contact_email'] . ">\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$message_top = "<html><body>";
		$message_bottom = "</body></html>";

		//Get the questions for the attendee
		$questions = $wpdb->get_results("SELECT ea.answer, eq.question 
                        FROM " . EVENTS_ANSWER_TABLE . " ea 
						LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id
						WHERE ea.registration_id = '".$registration_id."' ORDER BY eq.sequence asc ");
		//echo $wpdb->last_query;
		foreach ($questions as $question){
			$email_questions .= '<p>'. $question->question . ':<br /> ' . str_replace(',', '<br />', $question->answer) . '</p>';
		}
		//print_r ($questions);

		//Get the event information
		$events = $wpdb->get_results("SELECT ed.* FROM ". EVENTS_DETAIL_TABLE . " ed
                        JOIN " . EVENTS_ATTENDEE_TABLE . " ea
                        ON ed.id = ea.event_id
                        WHERE ea.registration_id='".$registration_id."'");

		foreach ($events as $event){
			$event_id=$event->id;
			$event_name=$event->event_name;
			$event_desc=$event->event_desc;
			$display_desc=$event->display_desc;
			$event_identifier=$event->event_identifier;
			$reg_limit = $event->reg_limit;
			$active=$event->is_active;
			$send_mail= $event->send_mail;
			$conf_mail= $event->conf_mail;
			$email_id= $event->email_id;
			$alt_email= $event->alt_email;
			$start_date =  event_date_display($event->start_date);
			$end_date =  $event->end_date;
			$virtual_url = $event->virtual_url;
			$virtual_phone = $event->virtual_phone;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$event_country = $event->country;
			$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
			$location_phone = $event->phone;
			
			$google_map_link = espresso_google_map_link(array( 'address'=>$event_address, 'city'=>$event_city, 'state'=>$event_state, 'zip'=>$event_zip, 'country'=>$event_country) );
		}

		//Build links
		$event_url = get_option('siteurl') . "/?page_id=" . $org_options['event_page_id']. "&regevent_action=register&event_id=". $event_id;
		$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';

		$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;

		if ($registration_id != ''){
			$sql .= " WHERE registration_id = '".$registration_id."' ";
		}elseif ($attendee_id != ''){
			$sql .= " WHERE id = '".$attendee_id."' ";
		}else{
			_e('No ID Supplied', 'event_espresso');
		}

		$sql .= " ORDER BY id ";


		$attendees  = $wpdb->get_results($sql);

		foreach ($attendees as $attendee){
			$attendee_id = $attendee->id;
			$attendee_email = $attendee->email;
			$lname = $attendee->lname;
			$fname = $attendee->fname;
			$address = $attendee->address;
			$address2 = $attendee->address2;
			$city = $attendee->city;
			$state = $attendee->state;
			$zip = $attendee->zip;
			$payment_status = $attendee->payment_status;
			$txn_type = $attendee->txn_type;
			$amount_pd = $attendee->amount_pd;
			$payment_date = $attendee->payment_date;
			$phone = $attendee->phone;
			$event_time = $attendee->event_time;
			$end_time = $attendee->end_time;
			$start_date = $attendee->start_date;
			$end_date = $attendee->end_date;
			$date = $attendee->date;
			$txn_id = $attendee->txn_id;
			$ticket_type = $attendee->price_option;				

			if($start == 0){
				$start = 1;
			}
			
			//Buikd the payment link
			$payment_url = get_option('siteurl') . "/?page_id=" . $org_options['return_url'] . "&amp;registration_id=" . $registration_id;
			$payment_link = '<a href="' . $payment_url . '">' . __('View Your Payment Details') . '</a>';
			
			//Build the ticket link
			$ticket_url = get_option('siteurl') . "/?downlaod_ticket&amp;id=" . $attendee_id . "&amp;registration_id=".$registration_id;
			$ticket_link = '<strong><a href="' . $ticket_url . '">' . __('Download Ticket Now!') . '</a></strong>';
			
			// Email Confirmation to Site Owner
			if ($send_admin_email == 'true'){
				//global $email_questions;//Grab the answers to the questions for the global $email_questions that is created in the add_attendees_to_db.php
				$message = "<p>".$fname." ".$lname." ".__('has signed up on-line for','event_espresso')." ".$event_name."</p>";
				$message .= "<p><strong>".__('Registration ID:','event_espresso')."</strong>  ".$registration_id."</p>";
				$message .= "<p><strong>".__('Email address:','event_espresso')."</strong>  ".$attendee_email."</p>";
				$message .= "<p><strong>".__('Ticket Type:','event_espresso')."</strong>  ".$ticket_type."</p>";
				$message .= "<p><strong>".__('Event Dates:','event_espresso')."</strong>  ".event_espresso_no_format_date($start_date)." - ".event_espresso_no_format_date($end_date)."</p>";
				$message .= "<p><strong>".__('Event Times:','event_espresso')."</strong>  ".$event_time." - ".$end_time."</p>";
				//If the custom ticket is available, load the template file
				if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "/ticketing/template.php")){
					$message .= "<p>".$ticket_link."</p>";
				}
				$message .= $address == '' ? '' : "<p><strong>".__('Address:','event_espresso')."</strong> " .$address."<br />";
				$message .= $city == '' ? '' : '<strong>'.__('City:','event_espresso')."</strong> ".$city."<br />";
				$message .= $state == '' ? '' : '<strong>'.__('State:','event_espresso')."</strong> ".$state."<br />";
				$message .= $zip == '' ? '' : '<strong>'.__('Zip:','event_espresso')."</strong> ".$zip."</p>";
				$message .= $phone == '' ? '' : "<p><strong>".__('Phone:','event_espresso')."</strong> ".$phone."</p>";
				$message .= $email_questions;
				//echo $message;
				wp_mail($alt_email == '' ? $org_options['contact_email']:$alt_email. ',' . $org_options['contact_email'], stripslashes_deep($event_name), stripslashes_deep($message), $headers);
			}

		//Email Confirmation to Attendee
		if ($send_attendee_email == 'true'){

			//Perform replacement
			$SearchValues = array(
								"[event_id]",
								"[event_identifier]",
								"[registration_id]",
								"[fname]",
								"[lname]",
								"[phone]",
								"[event]",
								"[event_name]",
								"[description]",
								"[event_link]",
								"[event_url]",
								"[virtual_url]",
								"[virtual_phone]",
								"[txn_id]",
								"[cost]",
								"[event_price]",
								"[ticket_type]",
								"[ticket_link]",
								"[contact]",
								"[company]",
								"[co_add1]",
								"[co_add2]",
								"[co_city]",
								"[co_state]",
								"[co_zip]",
								"[payment_url]",
								"[start_date]",
								"[start_time]",
								"[end_date]",
								"[end_time]",
								"[location]",
								"[location_phone]",
								"[google_map_link]");

			$ReplaceValues = array(
								$event_id,
								$event_identifier,
								$registration_id,
								$fname,
								$lname,
								$phone,
								$event_name,
								$event_name,
								$event_desc,
								$event_link,
								$event_url,
								$virtual_url,
								$virtual_phone,
								$txn_id,
								$org_options['currency_symbol'] . $amount_pd,
								$org_options['currency_symbol'] . $amount_pd,
								$ticket_type,
								$ticket_link,
								$alt_email == '' ? $org_options['contact_email']:$alt_email,
								$org_options['organization'],
								$org_options['organization_street1'],
								$org_options['organization_street2'],
								$org_options['organization_city'],
								$org_options['organization_state'],
								$org_options['organization_zip'],
								//($start == 0)?$payment_link:'',
								$payment_link,
								$start_date,
								$event_time,
								$end_date,
								$end_time,
								$location,
								$location_phone,
								$google_map_link);
								
			if ($email_id >0){
				$email_data = array();
				$email_data = espresso_email_message($email_id);
				$conf_mail = $email_data['email_text'];
			}
			//This is the custom email set up in the event
			$custom = str_replace($SearchValues, $ReplaceValues, $conf_mail);
			$email_body = $message_top.$custom.$message_bottom;
			
			if($send_mail == 'Y'){
				wp_mail($attendee_email, stripslashes_deep($event_name), stripslashes_deep(html_entity_decode(wpautop($email_body), ENT_QUOTES, "UTF-8")), $headers);
			}

			//This is the default email set up in the org settings
			$default_replaced = str_replace($SearchValues, $ReplaceValues, $org_options['message']);
			
	
			if($send_mail == 'N'){
				wp_mail($attendee_email, stripslashes_deep($event_name), stripslashes_deep(html_entity_decode(wpautop($default_replaced), ENT_QUOTES, "UTF-8")), $headers);
			}
		}

		}
	}
}

//Payment Confirmations
if (!function_exists('event_espresso_send_payment_notification')) {
	function event_espresso_send_payment_notification($atts){
		global $wpdb, $org_options;

		//Extract the attendee_id and registration_id
		extract( $atts );
		$registration_id = "{$registration_id}";
		$attendee_id = "{$attendee_id}";
		$registration_id = $registration_id != '' ? $registration_id : espresso_registration_id($attendee_id);

		//Define email headers
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: " . $org_options['organization'] . " <". $org_options['contact_email'] . ">\r\n";
		$headers .= "Reply-To: " . $org_options['organization'] . "  <" . $org_options['contact_email'] . ">\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$message_top = "<html><body>";
		$message_bottom = "</html></body>";
		
		//Get the attendee  id or registration_id and create the sql statement
		$sql = "SELECT a.*, e.event_name, e.event_desc, e.start_date, e.send_mail FROM ". EVENTS_ATTENDEE_TABLE ." a ";
		$sql .= " LEFT JOIN ". EVENTS_DETAIL_TABLE ." e ON e.id = a.event_id ";
		if ($registration_id != ''){
			$sql .= " WHERE a.registration_id = '" . $registration_id . "' ";
		}elseif ($attendee_id != ''){
			$sql .= " WHERE a.id = '" . $attendee_id . "' ";
		}else{
			_e('No ID Supplied', 'event_espresso');
		}
		$sql .= "  ORDER BY id LIMIT 1 ";
		
		$attendees = $wpdb->get_results($sql);

		$start = 0;
		foreach ($attendees as $attendee){
			$attendee_id = $attendee->id;
			$attendee_email = $attendee->email;
			$event_id = $attendee->event_id;
			$lname = $attendee->lname;
			$fname = $attendee->fname;
			$address = $attendee->address;
			$address2 = $attendee->address2;
			$city = $attendee->city;
			$state = $attendee->state;
			$zip = $attendee->zip;
			$phone = $attendee->phone;
			$event_time = $attendee->event_time;
			$end_time = $attendee->end_time;
			$date = $attendee->date;
			
			$ticket_type = $attendee->price_option;
			
			
			if($start == 0){
				/*
				* Since the payment amount and info is stored with the primary attendee, we want to grab only
				* the first record info
				*/
				$payment_status = $attendee->payment_status;
				$txn_type = $attendee->txn_type;
				$amount_pd = $attendee->amount_pd;
				$payment_date = $attendee->payment_date;
				$txn_id = $attendee->txn_id;
				$start = 1;
			}
		}
		
		//Get the event information
		$events = $wpdb->get_results("SELECT ed.* FROM ". EVENTS_DETAIL_TABLE . " ed
                        JOIN " . EVENTS_ATTENDEE_TABLE . " ea
                        ON ed.id = ea.event_id
                        WHERE ea.registration_id='".$registration_id."'");

		foreach ($events as $event){
			$event_id=$event->id;
			$event_name=$event->event_name;
			$event_desc=$event->event_desc;
			$display_desc=$event->display_desc;
			$event_identifier=$event->event_identifier;
			$reg_limit = $event->reg_limit;
			$active=$event->is_active;
			$send_mail= $event->send_mail;
			$conf_mail= $event->conf_mail;
			$email_id= $event->email_id;
			$alt_email= $event->alt_email;
			$start_date =  event_date_display($event->start_date);
			$end_date =  $event->end_date;
			$virtual_url = $event->virtual_url;
			$virtual_phone = $event->virtual_phone;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$event_country = $event->country;
			$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
			$location_phone = $event->phone;
			
			$google_map_link = espresso_google_map_link(array( 'address'=>$event_address, 'city'=>$event_city, 'state'=>$event_state, 'zip'=>$event_zip, 'country'=>$event_country) );
		}

		//Build links
		$event_url = get_option('siteurl') . "/?page_id=" . $org_options['event_page_id']. "&regevent_action=register&event_id=". $event_id;
		$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';
		
		//Build the payment link
			$payment_url = get_option('siteurl') . "/?page_id=" . $org_options['return_url'] . "&amp;registration_id=" . $registration_id;
			$payment_link = '<a href="' . $payment_url . '">' . __('View Your Payment Details') . '</a>';
		
		//Build the ticket link
			$ticket_url = get_option('siteurl') . "/?downlaod_ticket&amp;id=" . $attendee_id . "&amp;registration_id=".$registration_id;
			$ticket_link = '<strong><a href="' . $ticket_url . '">' . __('Download Ticket Now!') . '</a></strong>';
			
		//Perform replacement
			$SearchValues = array(
								"[event_id]",
								"[event_identifier]",
								"[registration_id]",
								"[fname]",
								"[lname]",
								"[phone]",
								"[event]",
								"[event_name]",
								"[description]",
								"[event_link]",
								"[event_url]",
								"[virtual_url]",
								"[virtual_phone]",
								"[txn_id]",
								"[cost]",
								"[event_price]",
								"[ticket_type]",
								"[ticket_link]",
								"[contact]",
								"[company]",
								"[co_add1]",
								"[co_add2]",
								"[co_city]",
								"[co_state]",
								"[co_zip]",
								"[payment_url]",
								"[start_date]",
								"[start_time]",
								"[end_date]",
								"[end_time]",
								"[location]",
								"[location_phone]",
								"[google_map_link]");

			$ReplaceValues = array(
								$event_id,
								$event_identifier,
								$registration_id,
								$fname,
								$lname,
								$phone,
								$event_name,
								$event_name,
								$event_desc,
								$event_link,
								$event_url,
								$virtual_url,
								$virtual_phone,
								$txn_id,
								$org_options['currency_symbol'] . $amount_pd,
								$org_options['currency_symbol'] . $amount_pd,
								$ticket_type,
								$ticket_link,
								$alt_email == '' ? $org_options['contact_email']:$alt_email,
								$org_options['organization'],
								$org_options['organization_street1'],
								$org_options['organization_street2'],
								$org_options['organization_city'],
								$org_options['organization_state'],
								$org_options['organization_zip'],
								//($start == 0)?$payment_link:'',
								$payment_link,
								$start_date,
								$event_time,
								$end_date,
								$end_time,
								$location,
								$location_phone,
								$google_map_link);
								
		$email_body = $message_top.$org_options['payment_message'].$message_bottom;

		$subject = str_replace($SearchValues,$ReplaceValues,$org_options['payment_subject']);
		$email_body    = str_replace($SearchValues,$ReplaceValues,$email_body);
		if ($org_options['default_mail'] == 'Y'){
			wp_mail($attendee_email, stripslashes_deep($subject), stripslashes_deep(html_entity_decode(wpautop($email_body), ENT_QUOTES, "UTF-8")), $headers);
		}
	}
}

//Cancelation Notices
if (!function_exists('event_espresso_send_cancellation_notice')) {
	function event_espresso_send_cancellation_notice($event_id){
		global $wpdb, $org_options;
		//Define email headers
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: " . $org_options['organization'] . " <". $org_options['contact_email'] . ">\r\n";
		$headers .= "Reply-To: " . $org_options['organization'] . "  <" . $org_options['contact_email'] . ">\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$message_top = "<html><body>";
		$message_bottom = "</html></body>";

		$events = $wpdb->get_results("SELECT * FROM ". EVENTS_DETAIL_TABLE ." WHERE id='".$event_id."'");
		foreach ($events as $event){
			$event_name=$event->event_name;
			$event_desc=$event->event_desc;
			$send_mail= $event->send_mail;
			$conf_mail= $event->conf_mail;
			$email_id= $event->email_id;
			$alt_email= $event->alt_email;
			$start_date =  $event->start_date;
			$end_date =  $event->end_date;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
			$location_phone = $event->phone;

			$attendees = $wpdb->get_results("SELECT * FROM ". EVENTS_ATTENDEE_TABLE ." WHERE event_id ='" . $event_id . "'");
			foreach ($attendees as $attendee){
				$lname = $attendee->lname;
				$fname = $attendee->fname;
				$address = $attendee->address;
				$city = $attendee->city;
				$state = $attendee->state;
				$zip = $attendee->zip;
				$attendee_email = $attendee->email;
				$phone = $attendee->phone;
				$date = $attendee->date;
				$event_id = $attendee->event_id;
				$event_time = $attendee->event_time;
				$end_time = $attendee->end_time;

				//Replace the tags
				//$tags = array("[fname]", "[lname]", "[event_name]" );
				//$vals = array($fname, $lname, $event_name);
				//$email_body = $message_top.$email_body.$message_bottom;
				//$subject = str_replace($tags,$vals,$email_subject);


				$subject = __('Event Cancellation Notice','event_espresso');
				$email_body  = '<p>'.$event_name. __(' has been cancelled.','event_espresso') . '</p>';
				$email_body .= '<p>'. __('For more information, please email '. $alt_email == '' ? $org_options['contact_email']:$alt_email,'event_espresso') .'</p>';
				$body  = str_replace($tags,$vals,$email_body);
				wp_mail($attendee_email, stripslashes($subject), stripslashes_deep(html_entity_decode(wpautop($email_body), ENT_QUOTES, "UTF-8")), $headers);
			}
		}
	}
}

//Send Invoice
if (!function_exists('event_espresso_send_invoice')) {
	function event_espresso_send_invoice($registration_id, $invoice_subject, $invoice_message ){
 		global $wpdb, $org_options;
		//Define email headers
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: " . $org_options['organization'] . " <". $org_options['contact_email'] . ">\r\n";
		$headers .= "Reply-To: " . $org_options['organization'] . "  <" . $org_options['contact_email'] . ">\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$message_top = "<html><body>";
		$message_bottom = "</html></body>";
		$start = 0;
		//$results = $wpdb->get_results("SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id ='" . $registration_id . "'");
		$results = $wpdb->get_results("SELECT a.*, e.event_name, e.event_desc FROM ". EVENTS_ATTENDEE_TABLE ." a
										LEFT JOIN ". EVENTS_DETAIL_TABLE ." e ON e.id = a.event_id
										WHERE a.registration_id = '" . $registration_id . "' ORDER BY a.id LIMIT 1");

			foreach ($results as $result){
				$registration_id = $result->registration_id;
				$lname = $result->lname;
				$fname = $result->fname;
				$address = $result->address;
				$city = $result->city;
				$state = $result->state;
				$zip = $result->zip;
				
				$ticket_type = $attendee->price_option;

				$phone = $result->phone;
				$date = $result->date;
                           if($start == 0){
                            /*
                             * Since the payment amount and info is stored with the primary attendee, we want to grab only
                             * the first record info
                             */
                            $email = $result->email;
                            $payment_status = $result->payment_status;
                            $txn_type = $result->txn_type;
                            $amount_pd = $result->amount_pd;
                            $payment_date = $result->payment_date;
                            $txn_id = $result->txn_id;
                            $quantity = $result->quantity;
                            $coupon_code = $result->coupon_code;
                            $start = 1;

                           }



				$event_id = $result->event_id;



				$event_name = $result->event_name;
				$event_desc = $result->event_desc;
			}
			//Build links
			$event_url = get_option('siteurl') . "/?page_id=" . $org_options['event_page_id']. "&regevent_action=register&event_id=". $event_id;
			$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';
			
			//Build the payment link
			$payment_url = get_option('siteurl') . "/?page_id=" . $org_options['return_url'] . "&amp;registration_id=" . $registration_id;
			$payment_link = '<a href="' . $payment_url . '">' . __('View Your Payment Details') . '</a>';
		
		$gaddress = ($address != '' ? $address :'') . ($city != '' ? ',' . $city :'') . ($state != '' ? ',' . $state :'') . ($zip != '' ? ',' . $zip :'') . ($country != '' ? ',' . $country :''); 
		$google_map = htmlentities2('http://maps.google.com/maps?q='.$gaddress);
		$google_map_link = '<a href="'.$google_map.'">'.$google_map.'</a>';
		
			//Perform replacement
			$SearchValues = array(
								"[event_id]",
								"[event_identifier]",
								"[registration_id]",
								"[fname]",
								"[lname]",
								"[phone]",
								"[event]",
								"[event_name]",
								"[description]",
								"[event_link]",
								"[event_url]",
								"[virtual_url]",
								"[virtual_phone]",
								"[txn_id]",
								"[cost]",
								"[event_price]",
								"[ticket_type]",
								"[ticket_link]",
								"[contact]",
								"[company]",
								"[co_add1]",
								"[co_add2]",
								"[co_city]",
								"[co_state]",
								"[co_zip]",
								"[payment_url]",
								"[start_date]",
								"[start_time]",
								"[end_date]",
								"[end_time]",
								"[location]",
								"[location_phone]",
								"[google_map_link]");

			$ReplaceValues = array(
								$event_id,
								$event_identifier,
								$registration_id,
								$fname,
								$lname,
								$phone,
								$event_name,
								$event_name,
								$event_desc,
								$event_link,
								$event_url,
								$virtual_url,
								$virtual_phone,
								$txn_id,
								$org_options['currency_symbol'] . $amount_pd,
								$org_options['currency_symbol'] . $amount_pd,
								$ticket_type,
								$ticket_link,
								$alt_email == '' ? $org_options['contact_email']:$alt_email,
								$org_options['organization'],
								$org_options['organization_street1'],
								$org_options['organization_street2'],
								$org_options['organization_city'],
								$org_options['organization_state'],
								$org_options['organization_zip'],
								//($start == 0)?$payment_link:'',
								$payment_link,
								$start_date,
								$event_time,
								$end_date,
								$end_time,
								$location,
								$location_phone,
								$google_map_link);

			$invoice_subject = str_replace($SearchValues, $ReplaceValues, $invoice_subject);
			$message = str_replace($SearchValues, $ReplaceValues, $invoice_message);

			$email_body = $message_top.$message.$message_bottom;

			wp_mail($email, stripslashes_deep($invoice_subject), stripslashes_deep(html_entity_decode(wpautop($email_body), ENT_QUOTES, "UTF-8")), $headers );
	}
}

//Reminder Notices
if (!function_exists('espresso_event_reminder')) {
	function espresso_event_reminder($event_id, $email_subject='', $email_text='', $email_id=0){
		global $wpdb, $org_options;
		//Define email headers
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: " . $org_options['organization'] . " <". $org_options['contact_email'] . ">\r\n";
		$headers .= "Reply-To: " . $org_options['organization'] . "  <" . $org_options['contact_email'] . ">\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$message_top = "<html><body>";
		$message_bottom = "</html></body>";
$count= 0;
		$events = $wpdb->get_results("SELECT * FROM ". EVENTS_DETAIL_TABLE ." WHERE id='".$event_id."'");
		foreach ($events as $event){
			$event_id=$event->id;
			$event_name=$event->event_name;
			$event_desc=$event->event_desc;
			$alt_email= $event->alt_email;
			//$display_desc=$event->display_desc;
			//$event_identifier=$event->event_identifier;
			//$reg_limit = $event->reg_limit;
			//$active=$event->is_active;
			//$send_mail= $event->send_mail;
			$raw_email_message = $email_text != ''? $email_text : $event->conf_mail;
			$raw_email_subject = $email_subject != '' ? $email_subject : $event_name;
			//$email_id= $event->email_id;
			$start_date =  event_date_display($event->start_date);
			$end_date =  $event->end_date;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$event_country = $event->country;
			$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
			$location_phone = $event->phone;
			
			$google_map_link = espresso_google_map_link(array( 'address'=>$event_address, 'city'=>$event_city, 'state'=>$event_state, 'zip'=>$event_zip, 'country'=>$event_country) );
			
			//Build links
			$event_url = get_option('siteurl') . "/?page_id=" . $org_options['event_page_id']. "&regevent_action=register&event_id=". $event_id;
			$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';
                        }
                        
			$attendees = $wpdb->get_results("SELECT * FROM ". EVENTS_ATTENDEE_TABLE ." WHERE event_id ='" . $event_id . "'");
			foreach ($attendees as $attendee){
				$attendee_id = $attendee->id;
				$registration_id = $attendee->registration_id;
				$attendee_email = $attendee->email;
				$lname = $attendee->lname;
				$fname = $attendee->fname;
				$address = $attendee->address;
				$city = $attendee->city;
				$state = $attendee->state;
				$zip = $attendee->zip;
				$payment_status = $attendee->payment_status;
				$txn_type = $attendee->txn_type;
				$amount_pd = $attendee->amount_pd;
				$payment_date = $attendee->payment_date;
				$phone = $attendee->phone;
				$event_time = $attendee->event_time;
				$end_time = $attendee->end_time;
				$date = $attendee->date;
				$ticket_type = $attendee->price_option;
				
				//Build the payment link
				$payment_url = get_option('siteurl') . "/?page_id=" . $org_options['return_url'] . "&amp;registration_id=" . $registration_id;
				$payment_link = '<a href="' . $payment_url . '">' . __('View Your Payment Details') . '</a>';
				
				//Build the ticket link
				$ticket_url = get_option('siteurl') . "/?downlaod_ticket&amp;id=" . $attendee_id . "&amp;registration_id=".$registration_id;
				$ticket_link = '<strong><a href="' . $ticket_url . '">' . __('Download Ticket Now!') . '</a></strong>';

				//Perform replacement
				$SearchValues = array(
								"[event_id]",
								"[event_identifier]",
								"[registration_id]",
								"[fname]",
								"[lname]",
								"[phone]",
								"[event]",
								"[event_name]",
								"[description]",
								"[event_link]",
								"[event_url]",
								"[virtual_url]",
								"[virtual_phone]",
								"[txn_id]",
								"[cost]",
								"[event_price]",
								"[ticket_type]",
								"[ticket_link]",
								"[contact]",
								"[company]",
								"[co_add1]",
								"[co_add2]",
								"[co_city]",
								"[co_state]",
								"[co_zip]",
								"[payment_url]",
								"[start_date]",
								"[start_time]",
								"[end_date]",
								"[end_time]",
								"[location]",
								"[location_phone]",
								"[google_map_link]");

				$ReplaceValues = array(
								$event_id,
								$event_identifier,
								$registration_id,
								$fname,
								$lname,
								$phone,
								$event_name,
								$event_name,
								$event_desc,
								$event_link,
								$event_url,
								$virtual_url,
								$virtual_phone,
								$txn_id,
								$org_options['currency_symbol'] . $amount_pd,
								$org_options['currency_symbol'] . $amount_pd,
								$ticket_type,
								$ticket_link,
								$alt_email == '' ? $org_options['contact_email']:$alt_email,
								$org_options['organization'],
								$org_options['organization_street1'],
								$org_options['organization_street2'],
								$org_options['organization_city'],
								$org_options['organization_state'],
								$org_options['organization_zip'],
								//($start == 0)?$payment_link:'',
								$payment_link,
								$start_date,
								$event_time,
								$end_date,
								$end_time,
								$location,
								$location_phone,
								$google_map_link);
								
				if ($email_id >0){
					$email_data = array();
					$email_data = espresso_email_message($email_id);
					$raw_email_message = $email_data['email_text'];
					$raw_email_subject = $email_data['email_subject'];
				}
				
				
				$email_subject = str_replace($SearchValues, $ReplaceValues, $raw_email_subject);
				
				$email_message = str_replace($SearchValues, $ReplaceValues, $raw_email_message);
				
				$email_body = $message_top.$email_message.$message_bottom;
		
				if (wp_mail($attendee_email, stripslashes_deep($email_subject), stripslashes_deep(html_entity_decode(wpautop($email_body), ENT_QUOTES, "UTF-8")), $headers)){
					//sleep(1);
					$count ++;
				}
			}
?>
			<div id="message" class="updated fade">
          <p><strong>
            <?php _e( 'Email Sent to ' . $count . ' people sucessfully.', 'event_espresso' ); ?>
            </strong></p>
        </div>
<?php
		
	}
}