<?php
function espresso_pending_registration_approval($registration_id) {
	global $wpdb, $org_options;
	//Get the event information
	$SQL = "SELECT ed.* FROM ". EVENTS_DETAIL_TABLE . " ed ";
	$SQL .= "JOIN " . EVENTS_ATTENDEE_TABLE . " ea ON ed.id = ea.event_id ";
	$SQL .= "WHERE ea.registration_id=%s";
	$events = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ));

	foreach ($events as $event){
		$event_id=$event->id;
		$event_name=stripslashes_deep($event->event_name);
		$event_desc=stripslashes_deep($event->event_desc);
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
		$require_pre_approval = $event->require_pre_approval;
		
		$google_map_link = espresso_google_map_link(array( 'address'=>$event_address, 'city'=>$event_city, 'state'=>$event_state, 'zip'=>$event_zip, 'country'=>$event_country) );
	}

	//Build links
	$event_url = espresso_reg_url($event_id);
	$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';


	$SQL = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;

	if ($registration_id != ''){
		$SQL .= " WHERE registration_id = '".$registration_id."' ";
	}elseif ($attendee_id != ''){
		$SQL .= " WHERE id = '".$attendee_id."' ";
	}else{
		wp_die( _e('No ID Supplied', 'event_espresso'));
	}

	$SQL .= " ORDER BY id ";
	$SQL .= " LIMIT 0,1 ";//Get the first attendees details


	$attendee  = $wpdb->get_row($wpdb->prepare( $SQL, NULL ));
	//global $attendee_id;
	
	if ( $attendee !== FALSE ) {
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
		$event_cost = $attendee->amount_pd;
		$payment_date = $attendee->payment_date;
		$phone = $attendee->phone;
		$event_time = $attendee->event_time;
		$end_time = $attendee->end_time;
		$date = $attendee->date;
		$pre_approve = $attendee->pre_approve;
			
		event_espresso_send_attendee_registration_approval_pending($registration_id);
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH."templates/pending_approval.php");		
	} else {
		wp_die( _e('An error occured. The primary attendee could not be located.', 'event_espresso'));		
	}

}
