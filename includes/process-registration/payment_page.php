<?php

//Payment Page/PayPal Buttons - Used to display the payment options and the payment link in the email. Used with the [ESPRESSO_PAYMENTS] tag
//This is the initial PayPal button
function events_payment_page($attendee_id, $price_id = 0, $coupon_code = '', $groupon_code = '') {
	global $wpdb, $org_options;

	$today = date("m-d-Y");
	$num_people = 0;

	$Organization = $org_options['organization'];
	$Organization_street1 = $org_options['organization_street1'];
	$Organization_street2 = $org_options['organization_street2'];
	$Organization_city = $org_options['organization_city'];
	$Organization_state = $org_options['organization_state'];
	$Organization_zip = $org_options['organization_zip'];
	$contact = $org_options['contact_email'];
	$registrar = $org_options['contact_email'];
	$currency_format = getCountryFullData($org_options['organization_country']);

	$message = $org_options['message'];
	$return_url = $org_options['return_url'];
	$cancel_return = $org_options['cancel_return'];
	$notify_url = $org_options['notify_url'];
	$event_page_id = $org_options['event_page_id'];

	$attendees = $wpdb->get_results("SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id ='" . $attendee_id . "'");
	foreach ($attendees as $attendee) {
//$attendee_id = $attendee->id;
		$attendee_last = $attendee->lname;
		$attendee_first = $attendee->fname;
		$attendee_address = $attendee->address;
		$attendee_address2 = $attendee->address2;
		$attendee_city = $attendee->city;
		$attendee_state = $attendee->state;
		$attendee_zip = $attendee->zip;
		$attendee_email = $attendee->email;
//$attendee_organization_name = $attendee->organization_name;
//$attendee_country = $attendee->country_id;
		$phone = $attendee->phone;
		$attendee_phone = $attendee->phone;
		$date = $attendee->date;
		$quantity = $attendee->quantity;
		$payment_status = $attendee->payment_status;
		$txn_type = $attendee->txn_type;
//$event_cost = $attendee->amount_pd;
		$payment_date = $attendee->payment_date;
		$event_id = $attendee->event_id;
		$registration_id = $attendee->registration_id;
	}
//$event_meta = event_espresso_get_event_meta($event_id);
//Get the questions for the attendee
	$questions = $wpdb->get_results("SELECT ea.answer, eq.question
						FROM " . EVENTS_ANSWER_TABLE . " ea
						LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id
						WHERE ea.attendee_id = '" . $attendee_id . "' and eq.admin_only = 'N' ORDER BY eq.sequence asc ");
//echo $wpdb->last_query;
	$display_questions = '';
	foreach ($questions as $question) {
		$display_questions .= '<p>' . $question->question . ':<br /> ' . str_replace(',', '<br />', $question->answer) . '</p>';
	}
	$num_peoplea = $wpdb->get_results("SELECT COUNT(registration_id) FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id ='" . $registration_id . "'", ARRAY_N);
	$num_people = $num_peoplea[0][0];

//If we are using the number of attendees dropdown, and
	if ($quantity > 1) {
		$num_people = $quantity;
	}

	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id ='" . $event_id . "'");
	foreach ($events as $event) {
//$event_id = $event->id;
		$event_name = stripslashes_deep($event->event_name);
		$event_desc = stripslashes_deep($event->event_desc);
		$event_description = stripslashes_deep($event->event_desc);
		$event_identifier = $event->event_identifier;
		$send_mail = $event->send_mail;
		$active = $event->is_active;
		$conf_mail = $event->conf_mail;
//$alt_email = $event->alt_email; //This is used to get the alternate email address that a payment can be made to using PayPal
		if (function_exists('event_espresso_coupon_payment_page')) {
			$use_coupon_code = $event->use_coupon_code;
		}
		if (function_exists('event_espresso_groupon_payment_page')) {
			$use_groupon_code = $event->use_groupon_code;
		}
	}

	$attendee_name = stripslashes_deep($attendee_first . ' ' . $attendee_last);

//Figure out if the person has registered using a price selection
	if (!empty($_REQUEST['price_select']) && $_REQUEST['price_select'] == true) {

		$price_options = explode('|', $_REQUEST['price_option'], 2);
		$price_id = $price_options[0];
		$price_type = $price_options[1];
		$p_id = $price_id;
		$event_cost = event_espresso_get_final_price($price_id, $event_id);
	} elseif ($price_id > 0) {
		$event_cost = event_espresso_get_final_price($price_id, $event_id);
		$p_id = $price_id;
	} else {
//$event_cost = $_POST['event_cost'];
        if ( isset($_POST['price_id'])) {
            $event_cost = event_espresso_get_final_price($_POST['price_id'], $event_id);
            $p_id = $_POST['price_id'];
        } else {
            $event_cost = 0;
        }
	}


//Test the early discount amount to make sure we are getting the right amount
//print_r(early_discount_amount($event_id, $event_cost));

    $event_price = 0.00;
    $event_price_x_attendees = 0.00;
    $event_original_cost = 0.00;
	

	/*
	 * Added for seating chart addon
	 */
	/*
	 * This code block overrides the cost using seating chart add-on price
	 */
	if (defined('ESPRESSO_SEATING_CHART') && class_exists("seating_chart") && seating_chart::check_event_has_seating_chart($event_id) !== false) {
        $sc_cost_row = $wpdb->get_row("select sum(sces.purchase_price) as purchase_price from " . EVENTS_SEATING_CHART_EVENT_SEAT_TABLE . " sces inner join " . EVENTS_ATTENDEE_TABLE . " ea on sces.attendee_id = ea.id where ea.registration_id = '$registration_id'");
        if ($sc_cost_row !== NULL) {
            $event_cost = number_format($sc_cost_row->purchase_price, 2, '.', '');
            $event_original_cost = $event_cost;
            $event_price_x_attendees = $event_cost;
        } 
	} else {
        $event_price = number_format($event_cost, 2, '.', '');
        $event_price_x_attendees = number_format($event_cost * $num_people, 2, '.', '');
        $event_original_cost = number_format($event_cost * $num_people, 2, '.', '');
    }
	/*
	 * End seating chart addon
	 */


	if (function_exists('event_espresso_coupon_payment_page') && (!empty($_REQUEST['coupon_code']) || !empty($coupon_code))) {
		$coupon_data = event_espresso_coupon_payment_page($use_coupon_code, $event_id, $event_original_cost, $attendee_id, $num_people);
		$event_cost = $coupon_data['event_cost'];
		/*
		 * at this point , the $event_cost is correct
		 * The next line divided by the number of people and reassigned it to the same $even_cost var, making the event cost less
		 * I renamed it to another variable
		 */

		//$event_price_x_attendees = number_format($event_cost, 2, '.', '');
		$coupon_code = $_REQUEST['coupon_code'];
		
		if ( $coupon_data['valid'] == true ){
			$event_price_x_attendees = $event_price_x_attendees - $event_cost;
			if ( $coupon_data['percentage'] ) {
				$event_discount_label = $event_original_cost > $event_cost ? ' (' . __('Discount of ', 'event_espresso') . $org_options['currency_symbol'] . number_format($event_original_cost - $event_cost, 2, ".", ",") . ' (' . $coupon_data['discount'] . ')'. __(' applied', 'event_espresso') . ')' : '';
			}else{
				$event_discount_label = $event_original_cost > $event_cost ? ' (' . __('Discount of ', 'event_espresso') . $org_options['currency_symbol'] . number_format($event_original_cost - $event_cost, 2, ".", ",") . __(' applied', 'event_espresso') . ')' : '';
				$event_price_x_attendees = $event_cost;
			}
		}
		
	} else if (function_exists('event_espresso_groupon_payment_page') && ($_REQUEST['groupon_code'] != '' || $coupon_code != '')) {
		$groupon_data = event_espresso_groupon_payment_page($use_groupon_code, $event_id, $event_original_cost, $attendee_id);
		$groupon_code = $_REQUEST['groupon_code'];
		$event_cost = $groupon_data['event_cost'];
		if ( $groupon_data['valid'] == true ){
			$event_price_x_attendees = number_format($event_price_x_attendees - $event_price, 2, ".", ",");
			$event_discount_label = $event_original_cost > $event_cost ? ' (' . __('Discount of ', 'event_espresso') . $org_options['currency_symbol'] . number_format($event_price, 2, ".", ",") . __(' applied', 'event_espresso') . ')' : '';
		}
		$event_cost = $event_price_x_attendees;
		
	} else {
		$event_cost = $event_original_cost;
	}

	if ($num_people != 0)
		$event_individual_cost = number_format($event_cost / $num_people, 2, '.', '');


	if ($event_cost == '0.00') {
		$event_cost = '0.00';
		$payment_status = 'Completed';
		$sql = array('amount_pd' => $event_cost, 'payment_status' => $payment_status, 'payment_date' => $today);
		$sql_data = array('%s', '%s', '%s');
	} else {
		$sql = array('amount_pd' => $event_cost, 'payment_status' => $payment_status);
		$sql_data = array('%s', '%s');
	}

//Add the cost and payment status to the attendee
	$update_id = array('id' => $attendee_id);
	$wpdb->update(EVENTS_ATTENDEE_TABLE, $sql, $update_id, $sql_data, array('%d'));

//If this is a group registration, we need to make sure all attendees have the same payment status
	if (espresso_count_attendees_for_registration($attendee_id) > 1) {
		$wpdb->query("UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status' WHERE registration_id ='" . $registration_id . "'");
	}
	$display_cost = ( $event_cost != "0.00" ) ? $org_options['currency_symbol'] . $event_individual_cost : __('Free', 'event_espresso');

//Pull in the template
	if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "confirmation_display.php")) {
		require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "confirmation_display.php"); //This is the path to the template file if available
	} else {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/confirmation_display.php");
	}
}

function espresso_confirm_registration($registration_id) {
	global $wpdb, $org_options;


//Get the questions for the attendee
	$questions = $wpdb->get_results("SELECT ea.answer, eq.question
						FROM " . EVENTS_ANSWER_TABLE . " ea
						LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id
						WHERE ea.registration_id = '" . $registration_id . "' AND system_name IS NULL ORDER BY eq.sequence asc ");
//echo $wpdb->last_query;
	$display_questions = '';
	foreach ($questions as $question) {
		$display_questions .= '<p class="espresso_questions"><strong>' . $question->question . '</strong>:<br /> ' . str_replace(',', '<br />', $question->answer) . '</p>';
	}

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
		$payment_date = $attendee->payment_date;
		$phone = $attendee->phone;
		$event_time = $attendee->event_time;
		$end_time = $attendee->end_time;
		$date = $attendee->date;
		$pre_approve = $attendee->pre_approve;
		$session_id = $attendee->attendee_session;
	}
####### Added by wp-developers ##############
	$pre_approval_check = is_attendee_approved($event_id, $attendee_id);
###########################################
###### Modified by wp-developers ###############
	if ($pre_approval_check) {

//Pull in the "Thank You" page template
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_page.php")) {
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_page.php"); //This is the path to the template file if available
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/payment_page.php");
		}
		if ($amount_pd != '0.00') {
//Show payment options
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
################ End ##############################
//return __('Your Registration Has Been Confirmed', 'event_espresso');
//unset($_SESSION['espresso_session']['id']);
//session_destroy();
}

//This is the alternate PayPal button used for the email
function event_espresso_pay() {
	global $wpdb, $org_options;
	$active_gateways = get_option('event_espresso_active_gateways', array());
	foreach ($active_gateways as $gateway => $path) {
		event_espresso_require_gateway($gateway . "/init.php");
	}
	$payment_data['attendee_id'] = apply_filters('filter_hook_espresso_transactions_get_attendee_id', '');
	if (espresso_return_reg_id() != false && empty($payment_data['attendee_id'])) {
		$sql = "SELECT id FROM `" . EVENTS_ATTENDEE_TABLE . "` WHERE registration_id='" . espresso_return_reg_id() . "' ORDER BY id LIMIT 1";
		//echo $sql;
		$payment_data['attendee_id'] = $wpdb->get_var($sql);
		$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
	} elseif (!empty($payment_data['attendee_id'])) {
		$payment_data = apply_filters('filter_hook_espresso_prepare_payment_data_for_gateways', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_get_total_cost', $payment_data);
		$payment_data = apply_filters('filter_hook_espresso_prepare_event_link', $payment_data);
		if (espresso_return_reg_id() == false || $payment_data['registration_id'] != espresso_return_reg_id())
			die(__('There was a problem finding your Registration ID', 'event_espresso'));
		if ($payment_data['payment_status'] != 'Completed') {
			$payment_data = apply_filters('filter_hook_espresso_thank_you_get_payment_data', $payment_data);
			espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => 'Payment for: '. $payment_data['lname'] . ', ' . $payment_data['fname'] . '|| registration id: ' . $payment_data['registration_id'] . '|| transaction details: ' . $payment_data['txn_details']));
			$payment_data = apply_filters('filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data);
			do_action('action_hook_espresso_email_after_payment', $payment_data);
		}
	}


	if (!empty($payment_data['attendee_id'])) {
		extract($payment_data);
		if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php")) {
			require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "payment_overview.php"); //This is the path to the template file if available
		} else {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/payment_overview.php");
		}

		if ($payment_data['payment_status'] != "Completed") {
			echo '<a name="payment_options" id="payment_options"></a>';
			if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "return_payment.php")) {
				require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "return_payment.php"); //This is the path to the template file if available
			} else {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/return_payment.php");
			}
		}
	}
	$_REQUEST['page_id'] = $org_options['return_url'];
	ee_init_session();
}

