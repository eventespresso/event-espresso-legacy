<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');		


function events_payment_page( $attendee_id = FALSE, $notifications = array() ) {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');	
	
	if ( ! $attendee_id ) {
		wp_die( __('An error occured. No Attendee was received.', 'event_espresso') );
	}
	
	global $wpdb, $org_options;

	$num_people = 0;

//	$Organization = $org_options['organization'];
//	$Organization_street1 = $org_options['organization_street1'];
//	$Organization_street2 = $org_options['organization_street2'];
//	$Organization_city = $org_options['organization_city'];
//	$Organization_state = $org_options['organization_state'];
//	$Organization_zip = $org_options['organization_zip'];
//	$contact = $org_options['contact_email'];
//	$registrar = $org_options['contact_email'];
//	$currency_format = getCountryFullData($org_options['organization_country']);

	$message = $org_options['message'];
	$return_url = $org_options['return_url'];
	$cancel_return = $org_options['cancel_return'];
	$notify_url = $org_options['notify_url'];
	$event_page_id = $org_options['event_page_id'];
	
	// GET ATTENDEE
	$SQL = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id =%d";
	$attendee = $wpdb->get_row( $wpdb->prepare( $SQL, $attendee_id ));
	
	//printr( $attendee, '$attendee  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	$attendee_last = $attendee->lname;
	$attendee_first = $attendee->fname;
	$attendee_name = stripslashes_deep($attendee_first . ' ' . $attendee_last);
	$attendee_address = $attendee->address;
	$attendee_address2 = $attendee->address2;
	$attendee_city = $attendee->city;
	$attendee_state = $attendee->state;
	$attendee_zip = $attendee->zip;
	$attendee_email = $attendee->email;
	$phone = $attendee->phone;
	$attendee_phone = $attendee->phone;
	$date = $attendee->date;
	$quantity = (int)$attendee->quantity;
	$payment_status = $attendee->payment_status;
	$txn_type = $attendee->txn_type;
	$payment_date = $attendee->payment_date;
	$event_id = $attendee->event_id;
	$registration_id = $attendee->registration_id;
	
	$orig_price = (float)$attendee->orig_price;
	$final_price = (float)$attendee->final_price;


	//Get the questions for the attendee
	$SQL = "SELECT ea.answer, eq.question ";
	$SQL .= "	FROM " . EVENTS_ANSWER_TABLE . " ea ";
	$SQL .= "LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id ";
	$SQL .= "	WHERE ea.attendee_id = %d and eq.admin_only != 'Y' ";
	$SQL .= "	ORDER BY eq.sequence asc ";
	
	$questions = $wpdb->get_results($wpdb->prepare( $SQL, $attendee_id ));
//	echo '<h4>LQ : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	printr( $questions, '$questions  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	
	$display_questions = '';
	foreach ($questions as $question) {
	
		$question->question = trim( stripslashes( str_replace( '&#039;', "'", $question->question )));
		$question->question = htmlspecialchars( $question->question, ENT_QUOTES, 'UTF-8' );

		$question->answer = trim( stripslashes( str_replace( '&#039;', "'", $question->answer )));
		$question->answer = htmlspecialchars( $question->answer, ENT_QUOTES, 'UTF-8' );

		$display_questions .= '<p>' . $question->question . ':<br /> ' . str_replace(',', '<br />', $question->answer) . '</p>';
	}

	// update total cost for primary attendee
	$total_cost = ((float)$final_price * (int)$quantity) - $attendee->amount_pd;
	$total_attendees = (int)$quantity;
	$attendee_prices[] = array( 'option' => $attendee->price_option, 'qty' => (int)$quantity, 'price' => (float)( $final_price - $attendee->amount_pd ));
	
	// get # of attendees
	$SQL = "SELECT price_option, quantity, final_price, amount_pd  FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id =%s";
	$prices = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ));
	//printr( $prices, '$prices  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	if ( $prices !== FALSE ) {
		$total_cost = 0;
		$total_attendees = 0;
		$attendee_prices = array();
		// ensure prices is an array
		$prices = is_array( $prices ) ? $prices : array( $prices );
		foreach ( $prices as $price ) {
			// update total cost for all attendees
			$total_cost += (float)($price->final_price * (int)$price->quantity) - (float)$price->amount_pd;
			$total_attendees += $price->quantity;
			$attendee_prices[] = array( 'option' => $price->price_option, 'qty' => (int)$price->quantity, 'price' => (float)( $price->final_price - $price->amount_pd ));
		}
	}


	$SQL = "SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id = %d";
	$event = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ));
	
	$event_name = isset( $event->event_name ) ? stripslashes_deep($event->event_name) : '';
	$event_description = $event_desc = isset( $event->event_desc ) ? stripslashes_deep($event->event_desc) : '';
	$event_identifier = isset( $event->event_identifier ) ? $event->event_identifier : '';
	$send_mail = isset( $event->send_mail ) ? $event->send_mail : '';
	$active = isset( $event->is_active ) ? $event->is_active : TRUE;
	$conf_mail = isset( $event->conf_mail ) ? $event->conf_mail : '';

    //$event_price_x_attendees = number_format( $final_price * $num_people, 2, '.', '' );
    $event_original_cost = $orig_price;
	

	// Added for seating chart addon
	// This code block overrides the cost using seating chart add-on price
	if ( defined('ESPRESSO_SEATING_CHART') && class_exists("seating_chart") && seating_chart::check_event_has_seating_chart($event_id) !== false) {
		
		$SQL = "SELECT sum(sces.purchase_price) as purchase_price ";
		$SQL .= "FROM " . EVENTS_SEATING_CHART_EVENT_SEAT_TABLE . " sces ";
		$SQL .= "INNER JOIN " . EVENTS_ATTENDEE_TABLE . " ea ON sces.attendee_id = ea.id ";
		$SQL .= "WHERE ea.registration_id = %s";  
		
        if ( $seat = $wpdb->get_row( $wpdb->prepare( $SQL, $registration_id ))) {
            $total_cost = number_format( $seat->purchase_price, 2, '.', '' );
            //$event_price_x_attendees = (float)$final_price;
        } 
	 
	} 

	if ( $total_cost == 0 ) {
		$payment_status = 'Completed';//DO NOT TRANSLATE
		$today = date(get_option('date_format'));
		$data = array('amount_pd' => 0.00, 'payment_status' => $payment_status, 'payment_date' => $today);
		$format = array('%f', '%s', '%s');
		$update_id = array('id' => $attendee_id);
		$wpdb->update(EVENTS_ATTENDEE_TABLE, $data, $update_id, $format, array('%d'));
		//If this is a group registration, we need to make sure all attendees have the same payment status
		if (espresso_count_attendees_for_registration($attendee_id) > 1) {
			$wpdb->query("UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status' WHERE registration_id ='" . $registration_id . "'");
		}
	}	

	if ( function_exists( 'espresso_update_attendee_coupon_info' ) && $attendee_id && ! empty( $attendee->coupon_code )) {
		espresso_update_attendee_coupon_info( $attendee_id, $attendee->coupon_code  );
	} 	
					
	if ( function_exists( 'espresso_update_groupon' ) && $attendee_id && ! empty( $attendee->coupon_code )) {
		espresso_update_groupon( $attendee_id, $attendee->coupon_code  );
	} 	
	
	
//	echo '<h4>$attendee_id : ' . $attendee_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>$total_cost : ' . $total_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	espresso_update_primary_attendee_total_cost( $attendee_id, $total_cost, __FILE__ );


	if ( ! empty( $notifications['coupons'] ) || ! empty( $notifications['groupons'] )) {
		echo '<div id="event_espresso_notifications" class="clearfix event-data-display no-hide">';
		echo $notifications['coupons'];
		// add space between $coupon_notifications and  $groupon_notifications ( if any $groupon_notifications exist )
		echo ! empty( $notifications['coupons'] ) && ! empty( $notifications['groupons'] ) ? '<br/>' : '';
		echo $notifications['groupons'];
		echo '</div>';	
	}
	

	
	
	if ( isset($org_options['skip_confirmation_page']) && $org_options['skip_confirmation_page'] == 'Y' ) {	

		$redirect_url = home_url().'/?page_id='.$org_options['event_page_id'] . '&regevent_action=confirm_registration';				
		$_POST['regevent_action'] = 'confirm_registration';
		$_POST['confirm'] = 'Confirm Registration';
		$_POST['confirm_registration'] = TRUE;
		$_POST['attendee_id'] = $attendee_id;
		$_POST['event_id'] = $event_id;
		$_POST['registration_id'] = $registration_id;

		espresso_confirm_registration();

	} else {

		$display_cost = $total_cost > 0 ? $org_options['currency_symbol'] . number_format( $total_cost, 2, '.', '' ) : __('Free', 'event_espresso');

	 	// Pull in the template
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "confirmation_display.php")) {
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "confirmation_display.php"); //This is the path to the template file if available
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/confirmation_display.php");
		}
			
	}
	
}







function espresso_confirm_registration() {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
	global $wpdb, $org_options;

	if ( ! empty($_POST['confirm_registration'])) {
		$registration_id = sanitize_text_field( $_POST['registration_id'] );
	} else {
		wp_die(__('An error has occured. The registration ID could not be found.', 'event_espresso'));
	}
	
	do_action('action_hook_espresso_confirmation_page_before',$registration_id);

	//Get the questions for the attendee
	$SQL = "SELECT ea.answer, eq.question FROM " . EVENTS_ANSWER_TABLE . " ea ";
	$SQL .= "LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id ";
	$SQL .= "WHERE ea.registration_id = %s ";
	$SQL .= "AND system_name IS NULL ORDER BY eq.sequence asc ";
	$questions = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ));
	//echo $wpdb->last_query;
	
	$display_questions = '';
	foreach ($questions as $question) {
	
		$question->question = trim( stripslashes( str_replace( '&#039;', "'", $question->question )));
		$question->question = htmlspecialchars( $question->question, ENT_QUOTES, 'UTF-8' );

		$question->answer = trim( stripslashes( str_replace( '&#039;', "'", $question->answer )));
		$question->answer = htmlspecialchars( $question->answer, ENT_QUOTES, 'UTF-8' );

		$display_questions .= '<p class="espresso_questions"><strong>' . $question->question . '</strong>:<br /> ' . str_replace(',', '<br />', $question->answer) . '</p>';
	}

	//Get the event information
	$SQL = "SELECT ed.*  FROM " . EVENTS_DETAIL_TABLE . " ed ";
	$SQL .= "JOIN " . EVENTS_ATTENDEE_TABLE . " ea ON ed.id = ea.event_id ";
	$SQL .= "WHERE ea.registration_id=%s";
	$events = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ));

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


	$SQL = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;

	if ($registration_id != '') {
		$SQL .= " WHERE registration_id = '" . $registration_id . "' ";
	} elseif ($attendee_id != '') {
		$SQL .= " WHERE id = '" . $attendee_id . "' ";
	} else {
		_e('No ID Supplied', 'event_espresso');
	}

	$SQL .= " AND is_primary = 1 ";
	$SQL .= " ORDER BY id ";
	$SQL .= " LIMIT 0,1 "; //Get the first attendees details


	if ( ! $attendee = $wpdb->get_row( $wpdb->prepare( $SQL, NULL ))) {
		wp_die(__('An error occured. The primary attendee could not be found.', 'event_espresso'));
	}

	$attendee_id = $attendee->id;
	$attendee_email = isset( $attendee->email ) ? $attendee->email : '';
	$lname = isset( $attendee->lname ) ? htmlspecialchars( stripslashes( $attendee->lname ), ENT_QUOTES, 'UTF-8' ) : '';
	$fname = isset( $attendee->fname ) ? htmlspecialchars( stripslashes( $attendee->fname ), ENT_QUOTES, 'UTF-8' ) : '';
	$address = isset( $attendee->address ) ? htmlspecialchars( stripslashes( $attendee->address ), ENT_QUOTES, 'UTF-8' ) : '';
	$address2 = isset( $attendee->address2 ) ? htmlspecialchars( stripslashes( $attendee->address2 ), ENT_QUOTES, 'UTF-8' ) : '';
	$city = isset( $attendee->city ) ? htmlspecialchars( stripslashes( $attendee->city ), ENT_QUOTES, 'UTF-8' ) : '';
	$state = isset( $attendee->state ) ? htmlspecialchars( stripslashes( $attendee->state ), ENT_QUOTES, 'UTF-8' ) : '';
	$country = isset( $attendee->country ) ? htmlspecialchars( stripslashes( $attendee->country ), ENT_QUOTES, 'UTF-8' ) : '';
	$zip =  isset( $attendee->zip ) ? $attendee->zip : '';
	$payment_status = $attendee->payment_status;
	$txn_type = $attendee->txn_type;
	$amount_pd = (float)$attendee->amount_pd;
	$total_cost = (float)$attendee->total_cost;
	$payment_date = $attendee->payment_date;
	$phone = $attendee->phone;
	$event_time = $attendee->event_time;
	$end_time = $attendee->end_time;
	$date = $attendee->date;
	$pre_approve = $attendee->pre_approve;
	$session_id = $attendee->attendee_session;
	if ( $attendee->is_primary ) {
		$event_cost = $total_cost;
	}

	$attendee_pre_approved = is_attendee_approved($event_id, $attendee_id);

	if ( $attendee_pre_approved ) {

		//Pull in the "Thank You" page template
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_page.php")) {
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_page.php"); //This is the path to the template file if available
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/payment_page.php");
		}
		
		//Show payment options
		if ( $total_cost  > 0 ) {
		
			if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")) {
				require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
			} else {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/gateway_display.php");
			}
			
			//Check to see if the site owner wants to send an confirmation eamil before payment is recieved.
			if ($org_options['email_before_payment'] == 'Y') {
				event_espresso_email_confirmations(array('session_id' => $session_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
			}
			
		} else {
			event_espresso_email_confirmations(array('session_id' => $session_id, 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
		}
		
	} else {
	
		if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/process-registration/pending_approval_page.php')) {
			require_once('pending_approval_page.php');
			echo espresso_pending_registration_approval($registration_id);
			return;
		}
		
	}
	
}






//This is the alternate PayPal button used for the email
function event_espresso_pay() {

	ob_start();
	
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
	global $wpdb, $org_options, $espresso_content;

	$payment_data= array( 'attendee_id' => '' );

	$payment_data['attendee_id'] = apply_filters( 'filter_hook_espresso_transactions_get_attendee_id', $payment_data['attendee_id'] );
	$REG_ID = espresso_return_reg_id();
	
	if ( $REG_ID != false && empty($payment_data['attendee_id'] )) {
		//we're assuming there is NO payment data in this request, so we'll just 
		//prepare the $payment_data for display only. No processing of payment etc.
		$SQL = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . $REG_ID . "' ORDER BY id LIMIT 1";
		$payment_data['attendee_id'] = $wpdb->get_var( $wpdb->prepare( $SQL, NULL ));
				
		$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
		
	} elseif ( ! empty( $payment_data['attendee_id'] )) {
	
		$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
		
		if ( $REG_ID == false || $payment_data['registration_id'] != $REG_ID ) {
			wp_die(__('There was a problem finding your Registration ID', 'event_espresso'));
		}
			
		if ( $payment_data['payment_status'] != 'Completed' && $payment_data['payment_status'] != 'Refund' ) {
		
			
			$payment_data = apply_filters('filter_hook_espresso_thank_you_get_payment_data', $payment_data);
			
			$payment_details = array(
							'file' => __FILE__, 
							'function' => __FUNCTION__, 
							'status' => 'Payment for: '. $payment_data['lname'] . ', ' . $payment_data['fname'] . '|| attendee_session id: ' . $payment_data['attendee_session'] . '|| registration id: ' . $payment_data['registration_id'] . '|| transaction details: ' . $payment_data['txn_details']
					);
			espresso_log::singleton()->log( $payment_details );
			
			$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);
			add_action('action_hook_espresso_email_after_payment','espresso_email_after_payment');
			do_action('action_hook_espresso_email_after_payment', $payment_data);
			
		}
	}


	if (!empty($payment_data['attendee_id'])) {
	
		extract($payment_data);
		//printr( $payment_data, '$payment_data  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php")) {
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php"); //This is the path to the template file if available
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/payment_overview.php");
		}

		if ( $payment_status != "Completed" ) {
			echo '<a name="payment_options" id="payment_options"></a>';
			if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "return_payment.php")) {
				require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "return_payment.php"); //This is the path to the template file if available
			} else {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/return_payment.php");
			}
		}
	}
	$_REQUEST['page_id'] = $org_options['return_url'];
	unset( $_SESSION['espresso_session']['id'] );
	ee_init_session();
	
	$espresso_content = ob_get_contents();
	ob_end_clean();
	add_shortcode('ESPRESSO_PAYMENTS', 'espresso_return_espresso_content');
	return $espresso_content;
	
}

