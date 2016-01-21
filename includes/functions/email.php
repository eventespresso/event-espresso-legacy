<?php

if (!function_exists('espresso_replace_shortcodes')) {
	function espresso_replace_shortcodes($message, $data) {
		global $wpdb, $org_options;
		$payment_data = espresso_get_total_cost(array('attendee_session'=>$data->attendee->attendee_session));
		$event_cost = $payment_data['total_cost'];
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
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
			"[venue_title]",
			"[venue_url]",
			"[venue_image]",
			"[venue_phone]",
			"[venue_address]", //shows the venue address
			"[txn_id]",
			"[cost]",
			"[event_price]",
			"[ticket_qty]",
			"[ticket_type]",
			"[ticket_link]",
			"[certificate_link]",
			"[contact]",
			"[company]",
			"[co_add1]",
			"[co_add2]",
			"[co_city]",
			"[co_state]",
			"[co_zip]",
			"[payment_url]",
			"[invoice_link]",
			"[start_date]",
			"[start_time]",
			"[end_date]",
			"[end_time]",
			"[location]",
			"[location_phone]",
			"[google_map_link]",
			"[attendee_event_list]", //Creates a table of the attendee and event information
			"[custom_questions]",
			"[qr_code]",
			"[seating_tag]",
			"[edit_attendee_link]",
			"[add_to_calendar]"
		);

		$ReplaceValues = array(
			$data->attendee->event_id,
			$data->event->event_identifier,
			$data->attendee->registration_id,
			$data->attendee->fname,
			$data->attendee->lname,
			$data->event->venue_phone,
			$data->event->event_name,
			$data->event->event_name,
			$data->event->event_desc,
			$data->event_link,
			$data->event_url,
			$data->event->virtual_url,
			$data->event->virtual_phone,
			//Venue information
			$data->event->venue_name,
			$data->event->venue_url,
			$data->event->venue_image,
			$data->event->venue_phone,
			$data->location, //For the "[venue_address]" shortcode shows the venue address
			//Payment details
			$data->attendee->txn_id,
			$org_options['currency_symbol'] . $event_cost,
			$org_options['currency_symbol'] . $event_cost,
			$data->attendee->quantity,
			$data->attendee->price_option,
			$data->ticket_link,
			empty($data->certificate_link) ? '' : $data->certificate_link,
			empty($data->event->alt_email) ? $org_options['contact_email'] : $data->event->alt_email,
			//Organization details
			$org_options['organization'],
			$org_options['organization_street1'],
			$org_options['organization_street2'],
			$org_options['organization_city'],
			$org_options['organization_state'],
			$org_options['organization_zip'],
			$data->payment_link,
			$data->invoice_link,
			event_date_display($data->attendee->start_date),
			event_date_display($data->attendee->event_time, get_option('time_format')),
			event_date_display($data->attendee->end_date),
			event_date_display($data->attendee->end_time, get_option('time_format')),
			$data->location,
			$data->event->venue_phone,
			$data->google_map_link,
			$data->table_open . $data->table_heading . $data->event_table . $data->table_close,
			isset($data->email_questions) && !empty($data->email_questions) ? $data->email_questions : '',
			$data->qr_code,
			$data->seatingchart_tag,
			$data->edit_attendee,
			//Add to calendar link
			apply_filters('filter_hook_espresso_display_ical', array(
					'event_id' => $data->attendee->event_id,
					'registration_id' => $data->attendee->registration_id,
					'event_name' => $data->event->event_name,
					'event_desc' => wp_kses($data->event->event_desc,array()),
					'contact_email' => empty($data->event->alt_email) ? $org_options['contact_email'] : $data->event->alt_email,
					'start_time' => empty($event->start_time) ? '' : $event->start_time,
					'start_date' => event_date_display($data->attendee->start_date, get_option('date_format')),
					'end_date' => event_date_display($data->attendee->end_date, get_option('date_format')),
					'start_time' => empty($data->attendee->event_time) ? '' : $data->attendee->event_time,
					'end_time' => empty($data->attendee->end_time) ? '' : $data->attendee->end_time,
					'location' => $data->location,
				), '', '', TRUE
			)
		);
	
		//Get the questions and answers
		$questions = $wpdb->get_results("select qst.question as question, ans.answer as answer from " . EVENTS_ANSWER_TABLE . " ans inner join " . EVENTS_QUESTION_TABLE . " qst on ans.question_id = qst.id where ans.attendee_id = " . $data->attendee->id, ARRAY_A);
		//echo '<p>'.print_r($questions).'</p>';
		if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->question != NULL) {
			foreach ($questions as $q) {
				$k = stripslashes( $q['question'] );
				$v = stripslashes( $q['answer'] );
	
				//Output the question
				array_push($SearchValues, "[" . 'question_' . $k . "]");
				array_push($ReplaceValues, $k);
	
				//Output the answer
				array_push($SearchValues, "[" . 'answer_' . $k . "]");
				array_push($ReplaceValues, rtrim($v, ",") );
			}
		}
		//Get the event meta
		//echo '<p>'.print_r($data->event->event_meta).'</p>';
		if (!empty($data->event->event_meta)) {
			foreach ($data->event->event_meta as $k => $v) {
				if (!empty($k) && !is_array($v)) {
					array_push($SearchValues, "[" . $k . "]");
					array_push($ReplaceValues, stripslashes_deep($v));
				}
			}
		}

		//Filters to allow for custom email shortcodes
		$SearchValues = apply_filters('filter_hook_espresso_post_replace_shortcode_search_values', $SearchValues);
		$ReplaceValues = apply_filters('filter_hook_espresso_post_replace_shortcode_replace_values', $ReplaceValues, $data);

		//Perform the replacement
		return str_replace($SearchValues, $ReplaceValues, $message);
	}
}

//Build the email
function espresso_prepare_email_data($attendee_id, $multi_reg, $custom_data='') {
	global $wpdb, $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$data = new stdClass;
	$data->multi_reg = $multi_reg;
    $data->seatingchart_tag = '';
	//print_r($custom_data);
	//Create vars for the custom data
	if (!empty($custom_data)) { 
		extract($custom_data, EXTR_PREFIX_ALL, 'custom_data');
    } 
    
    //echo $custom_data_email_type;
	//Get the event record 
	if (empty($custom_data_email_type)) {
		$custom_data_email_type = '';
    }
    $data->email_type = $custom_data_email_type;
	$sql = "SELECT ed.* ";
	isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ? $sql .= ", v.name venue_name, v.address venue_address, v.address2 venue_address2, v.city venue_city, v.state venue_state, v.zip venue_zip, v.country venue_country, v.meta venue_meta " : '';
	$sql .= " FROM " . EVENTS_DETAIL_TABLE . " ed ";
	isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ? $sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = ed.id LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = r.venue_id " : '';
	$sql .= " JOIN " . EVENTS_ATTENDEE_TABLE . " ea ON ea.event_id=ed.id ";
	$sql .= " WHERE ea.id = '" . $attendee_id . "' ";
	$data->event = $wpdb->get_row($sql, OBJECT);
    
	//Get the attendee record
	$sql = "SELECT ea.* FROM " . EVENTS_ATTENDEE_TABLE . " ea WHERE ea.id = '" . $attendee_id . "' ";
	$data->attendee = $wpdb->get_row($sql, OBJECT);

	//Get the primary/first attendee
	$data->primary_attendee = espresso_is_primary_attendee($data->attendee->id) == true ? true : false;

	$data->event->event_meta = unserialize($data->event->event_meta);

	//Venue variables
	if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y') {
		$data->event->venue_meta = unserialize($data->event->venue_meta);

		//Debug
		//echo "<pre>".print_r($data->event->venue_meta,true)."</pre>";

		$data->event->venue_url = $data->event->venue_meta['website'];
		$data->event->venue_phone = $data->event->venue_meta['phone'];
		$data->event->venue_image = '<img src="'.$data->event->venue_meta['image'].'" />';
		$data->event->venue_name = $data->event->venue_name;
		$data->event->address = $data->event->venue_address;
		$data->event->address2 = $data->event->venue_address2;
		$data->event->city = $data->event->venue_city;
		$data->event->state = $data->event->venue_state;
		$data->event->zip = $data->event->venue_zip;
		$data->event->country = $data->event->venue_country;
	} else {
		$data->event->venue_name = $data->event->venue_title;
		
	}
	//Build the table to hold the event and attendee info
	$data->table_open = '<table width="100%" border="1" cellpadding = "5" cellspacing="5" style="border-collapse:collapse;">';
	$data->table_heading = "<tr><th>" . __('Event Name', 'event_espresso') . "</th><th>" . __('Date', 'event_espresso') . "</th><th>" . __('Time', 'event_espresso') . "</th><th>" . __('Location', 'event_espresso') . "</th></tr>";
	$data->table_close = "</table>";

	//Clear ticket data
	$data->qr_code = '';
	$data->ticket_link = '';
	$data->admin_ticket_link = '';
    
    if (defined("ESPRESSO_SEATING_CHART")) {
        if (class_exists("seating_chart")) {
            if ( seating_chart::check_event_has_seating_chart($data->event->id)) {
                $rs = $wpdb->get_row("select scs.* from ".EVENTS_SEATING_CHART_EVENT_SEAT_TABLE." sces inner join ".EVENTS_SEATING_CHART_SEAT_TABLE." scs on sces.seat_id = scs.id where sces.attendee_id = ".$attendee_id);
                if ( $rs !== NULL ) {
                    $data->seatingchart_tag = $rs->custom_tag." ".$rs->seat." ".$rs->row;
                }
            }
        }
    }
    
	//Old ticketing system
	if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "/ticketing/template.php")) {
		if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "/ticketing/functions.php")) {
			include_once(EVENT_ESPRESSO_UPLOAD_DIR . "/ticketing/functions.php");
			$data->qr_code = espresso_qr_code( array('attendee_id' => $data->attendee->id, 'registration_id' => $data->attendee->registration_id, 'event_code' => $data->event->event_code ));
		}
		$data->ticket_link = espresso_ticket_links($data->attendee->registration_id, $data->attendee->id);
		$data->admin_ticket_link = $data->ticket_link;
	}

	//New ticketing system version 2.0
	if (function_exists('espresso_ticket_launch')) {
		$data->qr_code = espresso_ticket_qr_code( array('attendee_id' => $data->attendee->id, 'registration_id' => $data->attendee->registration_id, 'event_code' => $data->event->event_code ));
		$data->ticket_link = espresso_ticket_links($data->attendee->registration_id, $data->attendee->id, TRUE);
		$data->admin_ticket_link = $data->ticket_link;
	}

	//certificate system
	if (function_exists('espresso_certificate_launch')) {
		$data->certificate_link = espresso_certificate_links($data->attendee->registration_id, $data->attendee->id);
		$data->admin_certificate_link = $data->certificate_link;
	}
        

	//Build the address
	$data->location = ($data->event->address != '' ? $data->event->address : '') . ($data->event->address2 != '' ? '<br />' . $data->event->address2 : '') . ($data->event->city != '' ? '<br />' . $data->event->city : '') . ($data->event->state != '' ? ', ' . $data->event->state : '') . ($data->event->zip != '' ? '<br />' . $data->event->zip : '') . ($data->event->country != '' ? '<br />' . $data->event->country : '');

	//Build Google map link
	$data->google_map_link = espresso_google_map_link(array('address' => $data->event->address, 'city' => $data->event->city, 'state' => $data->event->state, 'zip' => $data->event->zip, 'country' => $data->event->country));

	//Registration URL
	$data->event_url = espresso_reg_url($data->event->id);
	$data->event_link = '<a href="' . $data->event_url . '">' . stripslashes_deep($data->event->event_name) . '</a>';

	//Venue name
	if (!isset($data->event->venue_name))
		$data->event->venue_name = '';

	//Table of events registered for
	$data->event_table .= espresso_generate_attendee_event_list( $data );

	//Output custom questions
	if (function_exists('event_espresso_custom_questions_output')) {
		//Create the question display
		$email_questions_r = event_espresso_custom_questions_output(array('attendee_id' => $data->attendee->id, 'all_questions' => TRUE));
		if ($email_questions_r != '')
			$data->email_questions = '<tr><td colspan = "6">' . $email_questions_r . '</td></tr>';
		$data->event_table .= $data->email_questions;
	}

	//Payment URL
	$payment_url = add_query_arg('r_id', $data->attendee->registration_id, get_permalink($org_options['return_url']));
	$data->payment_link = '<a href="' . $payment_url . '">' . __('View Your Payment Details','event_espresso') . '</a>';

	// download link
	$data->invoice_link = '<a href="' . home_url() . '/?download_invoice=true&amp;attendee_id=' . $data->attendee->id . '&amp;r_id=' . $data->attendee->registration_id . '" target="_blank">' . __('Download PDF Invoice', 'event_espresso') . '</a>';


	//Edit attendee link
	$data->edit_attendee = espresso_edit_attendee($data->attendee->registration_id, $data->attendee->id, $data->attendee->event_id, 'attendee', __('Edit Registration Details','event_espresso'));

	$data->email_subject = !$data->multi_reg ? $data->event->event_name : $org_options['organization'] . __(' registration confirmation', 'event_espresso');
    
	//Build invoice email
	if ($custom_data_email_type == 'invoice') {
		$data->email_subject = $custom_data_invoice_subject;
		$data->event->conf_mail = $custom_data_invoice_message;
		$data->event->send_mail = 'Y';
		$data->event->email_id = empty($_REQUEST['email_name']) ? '' : $_REQUEST['email_name'];
	}
    
	//Build payment email
	if ($custom_data_email_type == 'payment') { 
		$data->email_subject = $custom_data_payment_subject;
		$data->event->conf_mail = $custom_data_payment_message;
		$data->event->send_mail = 'Y'; 
        $data->event->email_id = 0;
	} 
	
	//Build reminder email
	if ($custom_data_email_type == 'reminder') {
		$data->email_subject = $custom_data_email_subject;
		$data->event->conf_mail = $custom_data_email_text;
		$data->event->send_mail = 'Y';
		$data->event->email_id = $custom_data_email_id > 0 ? $custom_data_email_id : '';
	}

	return apply_filters('filter_hook_espresso_post_prepare_email_data', $data);
}


function espresso_generate_attendee_event_list( $data ) {

	global $wpdb;
	
	$use_venue = isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' ? TRUE : FALSE;
	
 	$SQL = 'SELECT att.event_id, att.price_option, att.start_date, att.end_date, att.event_time, att.end_time, att.email, att.attendee_session, evt.id, evt.event_name ';	
 	$SQL .= $use_venue ? ', v.name venue_name ' : ', evt.venue_title venue_name ';
	$SQL .= 'FROM ' . EVENTS_ATTENDEE_TABLE . ' att ';
	$SQL .= 'LEFT JOIN ' . EVENTS_DETAIL_TABLE . ' evt ON evt.id=att.event_id ';	
	$SQL .= $use_venue ? " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = evt.id LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = r.venue_id " : '';	
	$SQL .= 'WHERE att.email = %s AND att.attendee_session = %s';
	$SQL .= 'ORDER BY att.start_date, att.event_time';

	$events = $wpdb->get_results( $wpdb->prepare( $SQL, $data->attendee->email, $data->attendee->attendee_session ));
	
	$table_row = '';
	foreach ( $events as $event ) {
		$table_row .= "
		<tr>
			<td>" . stripslashes_deep($event->event_name) . " | " . $event->price_option . "</td>
			<td>" . event_date_display($event->start_date) . ' - ' . event_date_display($event->end_date) . "</td>
			<td>" . event_date_display($event->event_time, get_option('time_format')) . " - " . event_date_display($event->end_time, get_option('time_format')) . "</td>
			<td>" . $event->venue_name . "<br />$data->location <br />$data->google_map_link</td>
		</tr>";	
	}

//	echo $wpdb->last_query;
//	echo $wpdb->print_error();
//	echo printr( $attendee, '$attendee' );
//	echo printr( $events, '$events' );
//	echo $table_row;
//	die();

	return $table_row;

}




//End espresso_prepare_email_data()
//Get the email ready to send
function espresso_prepare_email($data) {
	global $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	//Build the subject line
	$email_subject = $data->email_subject; 
	//Merge all the data
    
   
    if ($data->event->email_id > 0 && $data->event->send_mail == 'Y' ) { //Get the email template if it exists 
        $email_data = array();
        $email_data = espresso_email_message($data->event->email_id);
        $conf_mail = $email_data['email_text'];
        $email_subject = $email_data['email_subject'];
    } elseif ($data->event->conf_mail != '' && $data->event->send_mail == 'Y') {//Else get the custom event email 
        $conf_mail = $data->event->conf_mail;
    } else {//Else get the default email from the general settings 
        $conf_mail = $org_options['message'];
    }

	//Get the email subject
	$email_subject = espresso_replace_shortcodes($email_subject, $data);

	//Replace email shortcodes
	$_replaced = espresso_replace_shortcodes($conf_mail, $data);

	//Build the HTML
	$message_top = "<html><body>";
	$message_bottom = "</body></html>";
	$email_body = $message_top . $_replaced . $message_bottom;
	if (!isset($headers))
		$headers = '';
	return array(
			'send_to' => $data->attendee->email,
			'email_subject' => $email_subject,
			'email_body' => $email_body,
			'headers' => $headers
	);
}

//End espresso_prepare_email()
//Build the admin email
function espresso_prepare_admin_email($data) {
	global $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	//Edit attendee link
	$admin_attendee_link = espresso_edit_attendee($data->attendee->registration_id, $data->attendee->id, $data->attendee->event_id, 'admin', $data->attendee->fname . ' ' . $data->attendee->lname);

	//Group registration check
	$primary_attendee = $data->attendee->quantity > 0 && !$data->multi_reg && $data->primary_attendee == true ? "<p><strong>" . __('Primary Attendee', 'event_espresso') . "</strong></p>" : '';

	//Build the email title
	$admin_message = "<h3>" . __('Registration Summary:', 'event_espresso') . "</h3>";

	//Email body
	$attendee_quantity_count = $data->attendee->quantity;
	$admin_email_body = "<tr>
		<td>$primary_attendee $admin_attendee_link</td>
		<td>" . $data->attendee->email . "</td>
		<td>" . stripslashes_deep($data->event->event_name) . " | " . $data->attendee->price_option . "</td>
		<td>" . event_date_display($data->attendee->start_date) . ' - ' . event_date_display($data->attendee->end_date) . "</td>
		<td>" . event_date_display($data->attendee->event_time, get_option('time_format')) . " - " . event_date_display($data->attendee->end_time, get_option('time_format')) . "</td> " .
					($attendee_quantity_count > 0 ? '<td>' . $attendee_quantity_count . ' ' . sprintf( _n('attendee', 'attendees', $attendee_quantity_count, 'event_espresso') ) . '</td>' : '') . "</tr>";

	//Additional information/questions
	$admin_additional_info = "<h3>" . __('Additional Information:', 'event_espresso') . "</h3>";

	//Registration ID
	if (!empty($data->attendee->registration_id)) {
		$admin_additional_info .= '<strong>' . __('Registration ID: ', 'event_espresso') . '</strong><br />';
		$admin_additional_info .= $data->attendee->registration_id;
	}

	if (!empty($data->email_questions)) {
		$admin_additional_info .= $data->email_questions;
	}

	//Ticket links
	if (!empty($data->admin_ticket_link)) {
		$admin_additional_info .= '<strong>' . __('Ticket(s):', 'event_espresso') . '</strong><br />';
		$admin_additional_info .= $data->admin_ticket_link;
	}

	//Certificate links
	if (!empty($data->admin_certificate_link)) {
		$admin_additional_info .= '<strong>' . __('Certificate(s):', 'event_espresso') . '</strong><br />';
		$admin_additional_info .= $data->admin_certificate_link;
	}

	//invoice links
	if (!empty($data->invoice_link)) {
		$admin_additional_info .= '<p><strong>' . __('Invoice:', 'event_espresso') . '</strong><br />';
		$admin_additional_info .= $data->invoice_link;
		$admin_additional_info .= '</p>';
	}

	//Build the headers
	$headers = '';
	return array(
			'send_to' => $data->event->alt_email == '' ? $org_options['contact_email'] : $data->event->alt_email . ',' . $org_options['contact_email'],
			'email_subject' => !$data->multi_reg ? $data->event->event_name . ' ' . __('registration confirmation', 'event_espresso') : __('Event Registration Notification', 'event_espresso'),
			'email_body' => $admin_message . $data->table_open . $admin_email_body . $data->table_close . $admin_additional_info,
			'headers' => $headers
	);
}

//End espresso_prepare_admin_email()
function email_by_attendee_id($attendee_id, $send_attendee_email = TRUE, $send_admin_email = TRUE, $multi_reg = FALSE, $custom_data='') {
    
	$data = espresso_prepare_email_data($attendee_id, $multi_reg, $custom_data);
   
	
	if ($send_attendee_email == 'true') {
		$email_params = espresso_prepare_email($data);  
		event_espresso_send_email($email_params);
	}
	if ($send_admin_email == 'true') {
		$email_params = espresso_prepare_admin_email($data);
		event_espresso_send_email($email_params);
	}
}

//End email_by_attendee_id()
function email_by_session_id($session_id, $send_attendee_email = TRUE, $send_admin_email = TRUE, $multi_reg = FALSE) {
	global $wpdb;
	$sql = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE attendee_session = %s";
	$attendees = $wpdb->get_col( $wpdb->prepare( $sql, $session_id ));
	$admin_email_params = array('email_body'=>'');
	foreach ($attendees as $attendee_id) {
		$data = espresso_prepare_email_data($attendee_id, $multi_reg);
		if ($send_attendee_email == 'true') {
			$attendee_email_params = espresso_prepare_email($data);
			event_espresso_send_email($attendee_email_params);
		}
		if ($send_admin_email == 'true') {
			$email_params = espresso_prepare_admin_email($data);
			$admin_email_params['send_to'] = $email_params['send_to'];
			$admin_email_params['email_subject'] = $email_params['email_subject'];
			$admin_email_params['email_body'] .= '<----------------------------------------------><br />';
			$admin_email_params['email_body'] .= $email_params['email_subject'] . '<br />';
			$admin_email_params['email_body'] .= '<----------------------------------------------><br />';
			$admin_email_params['email_body'] .= $email_params['email_body'] . '<br />';
			$admin_email_params['headers'] = $email_params['headers'];
		}
	}
	if ($send_admin_email == 'true') {
		event_espresso_send_email($admin_email_params);
	}
}//End email_by_session_id()

if ( ! function_exists('event_espresso_email_confirmations')) {

	function event_espresso_email_confirmations($atts) {
       
		extract($atts);
		//print_r($atts);

		$multi_reg = empty( $multi_reg ) ? FALSE :  $multi_reg;
		$send_admin_email = empty( $send_admin_email ) ? FALSE :  $send_admin_email;
		$send_attendee_email = empty( $send_attendee_email ) ? FALSE :  $send_attendee_email;
		$custom_data = empty( $custom_data ) ? '' :  $custom_data;
		
		if ( ! empty( $attendee_id ) && ! $multi_reg ) { 
		
			email_by_attendee_id($attendee_id, $send_attendee_email, $send_admin_email, $multi_reg, $custom_data);
			
		} elseif ( ! empty( $registration_id ) && ! $multi_reg ) { 
		
			global $wpdb;
            $sql = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = %s";
			$attendees = $wpdb->get_col( $wpdb->prepare( $sql, $registration_id ));
			foreach ($attendees as $attendee_id) {
				email_by_attendee_id($attendee_id, $send_attendee_email, $send_admin_email, $multi_reg, $custom_data);
			}
			
		} elseif ( ! empty( $session_id )) { 
		
			email_by_session_id($session_id, $send_attendee_email, $send_admin_email, $multi_reg);
			
		}
	}

}//End event_espresso_email_confirmations()


//Email sender
if (!function_exists('event_espresso_send_email')) {

	function event_espresso_send_email($params) {
		global $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		extract($params);
		//Define email headers
		$headers = "";
		if ($org_options['email_fancy_headers']=='Y') {
			$headers .= "From: " . $org_options['organization'] . " <" . $org_options['contact_email'] . ">\r\n";
			$headers .= "Reply-To: " . $org_options['organization'] . "  <" . $org_options['contact_email'] . ">\r\n";
		} else {
			$headers .= "From: " . $org_options['contact_email'] . "\r\n";
			$headers .= "Reply-To: " . $org_options['contact_email'] . "\r\n";
		}
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		//Debug
//		 echo '<br/><br/><br/><br/>';
//		  echo '<p>$headers = '.$headers.'</p>';
//		  echo '<p>$send_to = '.$send_to.'</p>';
//		  echo '<p>$email_subject = '.$email_subject.'</p>';
//		  echo '<p>$email_body = '.$email_body.'</p>';
//		  echo '<p>'.$email_body.'</p>';
//		 echo '<br/><br/><br/><br/>';

		return wp_mail($send_to, stripslashes_deep(html_entity_decode($email_subject, ENT_QUOTES, "UTF-8")), stripslashes_deep(html_entity_decode(wpautop($email_body), ENT_QUOTES, "UTF-8")), $headers);
	}

}//End event_espresso_send_email()


//Send Invoice
if (!function_exists('event_espresso_send_invoice')) {

	function event_espresso_send_invoice( $registration_id, $invoice_subject, $invoice_message, $array_of_reg_ids = FALSE ) {
		global $wpdb, $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		$SQL ='SELECT a.id FROM '. EVENTS_ATTENDEE_TABLE . ' a	WHERE a.registration_id = %s';
		$result = $wpdb->get_row( $wpdb->prepare( $SQL, $registration_id ));

		$registration_id = isset($result->registration_id) && !empty($result->registration_id) ? $result->registration_id : '';
		$attendee_id = $result->id;

		event_espresso_email_confirmations(array('attendee_id' => $attendee_id, 'send_admin_email' => 'false', 'send_attendee_email' => 'true', 'custom_data' => array('email_type' => 'invoice', 'invoice_subject' => $invoice_subject, 'invoice_message' => $invoice_message)));

		return;
	}

}//End event_espresso_send_invoice()


//Payment Confirmations
if (!function_exists('event_espresso_send_payment_notification')) {

	function event_espresso_send_payment_notification($atts) {

		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		global $wpdb, $org_options;
		//Extract the attendee_id and registration_id
		extract($atts);
		
		$registration_id = is_array( $registration_id ) ? $registration_id[0] : $registration_id;
		
		if ( empty( $registration_id ) && isset( $attendee_id )) {
			$registration_id = espresso_registration_id($attendee_id);
		}
			
		if ( empty( $registration_id )) {
			return __('No Registration ID was supplied', 'event_espresso');
		}			

		//Get the attendee  id or registration_id and create the sql statement
		$SQL = "SELECT a.* FROM " . EVENTS_ATTENDEE_TABLE . " a ";
		$SQL .= " WHERE a.registration_id = %s ";
		$attendees = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ));

		if ($org_options['default_mail'] == 'Y') { 
			foreach ($attendees as $attendee) {
				event_espresso_email_confirmations(array('attendee_id' => $attendee->id, 'send_admin_email' => 'false', 'send_attendee_email' => 'true', 'custom_data' => array('email_type' => 'payment', 'payment_subject' => $org_options['payment_subject'], 'payment_message' => $org_options['payment_message'])));
			}
		}

		return;
	}

}

//Reminder Notices
if (!function_exists('espresso_event_reminder')) {

	function espresso_event_reminder($event_id, $email_subject='', $email_text='', $email_id=0, $filter="all") {
		global $wpdb, $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		$count = 0;
                switch ($filter) {
                    case "completed":
                        $sql_filter = " AND payment_status='Completed'";
                        break;
                    case "incomplete":
                        $sql_filter = " AND payment_status='Incomplete'";
                        break;
                    case "pending":
                        $sql_filter = " AND payment_status='Pending'";
                        break;
                    default:
                        $sql_filter = "";
                }
		$SQL = 'SELECT * FROM ' . EVENTS_ATTENDEE_TABLE . ' WHERE event_id =%d' . $sql_filter . ' GROUP BY lname, fname';
                
		$attendees = $wpdb->get_results( $wpdb->prepare( $SQL, $event_id ));
		
		if ($wpdb->num_rows > 0) {
			foreach ($attendees as $attendee) {
				$attendee_id = $attendee->id;
				event_espresso_email_confirmations(array('attendee_id' => $attendee_id, 'send_admin_email' => 'false', 'send_attendee_email' => 'true', 'custom_data' => array('email_type' => 'reminder', 'email_subject' => $email_subject, 'email_text' => $email_text, 'email_id' => $email_id)));
				$count++;
			}
			?>
			<div id="message" class="updated fade">
				<p><strong>
						<?php echo sprintf(_n('Email Sent to 1 person successfully.', 'Email Sent to %d people successfully.', $count, 'event_espresso'), $count); ?>
					</strong></p>
			</div>
			<?php
			return;
		} else {
			?>
			<div id="message" class="error fade">
				<p><strong>
						<?php _e('No attendee records available.', 'event_espresso'); ?>
					</strong></p>
			</div>
			<?php
		}
	}

}

//Cancelation Notices
if (!function_exists('event_espresso_send_cancellation_notice')) {

	function event_espresso_send_cancellation_notice($event_id) {
		global $wpdb, $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		//Define email headers
		$headers = "";
		if ($org_options['email_fancy_headers']=='Y') {
			$headers .= "From: " . $org_options['organization'] . " <" . $org_options['contact_email'] . ">\r\n";
			$headers .= "Reply-To: " . $org_options['organization'] . "  <" . $org_options['contact_email'] . ">\r\n";
		} else {
			$headers .= "From: " . $org_options['contact_email'] . "\r\n";
			$headers .= "Reply-To: " . $org_options['contact_email'] . "\r\n";
		}
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$message_top = "<html><body>";
		$message_bottom = "</html></body>";

		$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
		foreach ($events as $event) {
			$event_name = $event->event_name;
			$event_desc = $event->event_desc;
			$send_mail = $event->send_mail;
			$conf_mail = $event->conf_mail;
			$email_id = $event->email_id;
			$alt_email = $event->alt_email;
			$start_date = $event->start_date;
			$end_date = $event->end_date;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$location = (!empty($event_address) ? $event_address : '') . (!empty($event_address2) ? '<br />' . $event_address2 : '') . (!empty($event_city) ? '<br />' . $event_city : '') . (!empty($event_state) ? ', ' . $event_state : '') . (!empty($event_zip) ? '<br />' . $event_zip : '') . (!empty($event_country) ? '<br />' . $event_country : '');
			$location_phone = $event->phone;

			$attendees = $wpdb->get_results("SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id ='" . $event_id . "'");
			foreach ($attendees as $attendee) {
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


				$subject = __('Event Cancellation Notice', 'event_espresso');
				$email_body = '<p>' . sprintf( __( '%d has been cancelled.', 'event_espresso' ), $event_name ) . '</p>';
				$email_body .= '<p>' . sprintf( __('For more information, please email %d', 'event_espresso'), $alt_email == '' ? $org_options['contact_email'] : $alt_email ) . '</p>';
				$body = str_replace($tags, $vals, $email_body);
				wp_mail($attendee_email, stripslashes_deep(html_entity_decode($subject, ENT_QUOTES, "UTF-8")), stripslashes_deep(html_entity_decode(wpautop($email_body), ENT_QUOTES, "UTF-8")), $headers);
			}
		}
	}

}

// Attendee registration approval pending
if (!function_exists('event_espresso_send_attendee_registration_approval_pending')) {

	function event_espresso_send_attendee_registration_approval_pending($registration_id) {
		global $org_options, $wpdb;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		//Get the event information
		$events = $wpdb->get_results("SELECT ed.* FROM " . EVENTS_DETAIL_TABLE . " ed
						JOIN " . EVENTS_ATTENDEE_TABLE . " ea
						ON ed.id = ea.event_id
						WHERE ea.registration_id='" . $registration_id . "'");

		foreach ($events as $event) {
			$event_id = $event->id;
			$event_name = stripslashes_deep($event->event_name);
			$event_desc = stripslashes_deep($event->event_desc);
			$display_desc = $event->display_desc;
			$event_identifier = $event->event_identifier;
			$reg_limit = $event->reg_limit;
			$active = $event->is_active;
			$send_mail = $event->send_mail;
			$conf_mail = $event->conf_mail;
			$email_id = $event->email_id;
			$alt_email = $event->alt_email;
			$start_date = event_date_display($event->start_date);
			$end_date = $event->end_date;
			$virtual_url = $event->virtual_url;
			$virtual_phone = $event->virtual_phone;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$event_country = $event->country;
			$location = ($event_address != '' ? $event_address : '') . ($event_address2 != '' ? '<br />' . $event_address2 : '') . ($event_city != '' ? '<br />' . $event_city : '') . ($event_state != '' ? ', ' . $event_state : '') . ($event_zip != '' ? '<br />' . $event_zip : '') . ($event_country != '' ? '<br />' . $event_country : '');
			$location_phone = $event->phone;
			$require_pre_approval = $event->require_pre_approval;

			$google_map_link = espresso_google_map_link(array('address' => $event_address, 'city' => $event_city, 'state' => $event_state, 'zip' => $event_zip, 'country' => $event_country));
		}

		//Build links
		$event_url = espresso_reg_url($event_id);
		$event_link = '<a href="' . $event_url . '">' . $event_name . '</a>';

		$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;

		if ($registration_id != '') {
			$sql .= " WHERE registration_id = '" . $registration_id . "' ";
		} elseif ($attendee_id != '') {
			$sql .= " WHERE id = '" . $attendee_id . "' ";
		} else {
			_e('No ID Supplied', 'event_espresso');
		}

		$sql .= " ORDER BY id ";
		$sql .= " LIMIT 0,1 "; //Get the first attendees details


		$attendees = $wpdb->get_results($sql);
		//global $attendee_id;

		foreach ($attendees as $attendee) {
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
			$payment_date = event_date_display($attendee->payment_date);
			$phone = $attendee->phone;
			$event_time = event_date_display($attendee->event_time, get_option('time_format'));
			$end_time = event_date_display($attendee->end_time, get_option('time_format'));
			$date = event_date_display($attendee->date);
			$pre_approve = $attendee->pre_approve;
		}
		$admin_email = $alt_email == '' ? $org_options['contact_email'] : $alt_email . ',' . $org_options['contact_email'];
		if (!empty($admin_email)) {
			$subject = __('New attendee registration approval pending','event_espresso');
			$body = sprintf( __('Event title: %s', 'event_espresso'), $event_name );
			$body .= '<br/>';
			$body .= sprintf( __('Attendee name: %1$s %2$s', 'event_espresso'), $fname, $lname );
			$body .= '<br/>';
			$body .= __('Thank You.', 'event_espresso');
			$email_params = array(
					'send_to' => $admin_email,
					'email_subject' => __($subject, 'event_espresso'),
					'email_body' => $body
			);
			event_espresso_send_email($email_params);
		}

		if (!empty($attendee_email)) {
			$subject = __('Event registration pending','event_espresso');
			$body = sprintf( __('Event title: %s', 'event_espresso'), $event_name );
			$body .= '<br/>';
			$body .= sprintf( __('Attendee name: %1$s %2$s', 'event_espresso'), $fname, $lname );
			$body .= '<br/>';
			$body .= __('Your registration is pending for approval from event admin. You will receive an email with payment info when admin approves your registration.', 'event_espresso');
			$body .= '<br/><br/>';
			$body .= __('Thank You.', 'event_espresso');
			$email_params = array(
					'send_to' => $attendee_email,
					'email_subject' => __($subject, 'event_espresso'),
					'email_body' => $body
			);
			event_espresso_send_email($email_params);
		}
	}

}



/**
 * 		@ print_r an array
 * 		@ access public
 * 		@ return void
 */
function printr($var, $var_name = 'ARRAY', $height = 'auto') {

	echo '<pre style="display:block; width:100%; height:' . $height . '; overflow:scroll; border:2px solid light-blue;">';
	echo '<h3>' . $var_name . '</h3>';
	echo print_r($var);
	echo '</pre>';
}
