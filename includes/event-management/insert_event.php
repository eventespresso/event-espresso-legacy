<?php
if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	

// Adds an Event or Function to the Event Database
function add_event_to_db($recurrence_arr = array()) {
		
	global $wpdb, $org_options, $current_user, $espresso_premium;
	
	//Security check using nonce
	if ( empty($_POST['nonce_verify_insert_event']) || !wp_verify_nonce($_POST['nonce_verify_insert_event'],'espresso_verify_insert_event_nonce') ){
		
		if (!isset($recurrence_arr['bypass_nonce'])){
			print '<h3 class="error">'.__('Sorry, there was a security error and your event was not saved.', 'event_espresso').'</h3>';
			return;
		}
	}
	
	//Set FEM to false
	$use_fem = FALSE;
	$is_espresso_event_manager = false;
		
	//If using FEM
	if ( isset($_REQUEST['ee_fem_action']) && $_REQUEST['ee_fem_action'] == 'ee_fem_add'){
		$use_fem = TRUE;
		if ( function_exists('espresso_member_data') && espresso_member_data('role') == 'espresso_event_manager' ){
			global $espresso_manager;
			$event_manager_approval = isset($espresso_manager['event_manager_approval']) && $espresso_manager['event_manager_approval'] == 'Y' ? true : false;
			$is_espresso_event_manager = TRUE;
		}
	}

	//Don't show sql errors if using the FEM
	if ( $use_fem === FALSE )
		$wpdb->show_errors();

	static $recurrence_id = null;

	if (defined('EVENT_ESPRESSO_RECURRENCE_TABLE')) {
		require_once(EVENT_ESPRESSO_RECURRENCE_FULL_PATH . "functions/re_functions.php");
		$recurrence_id = array_key_exists('recurrence_id', $recurrence_arr) ? $recurrence_arr['recurrence_id'] : Null;
		if ($_POST['recurrence'] == 'Y' && count($recurrence_arr) < 2) {

			if (is_null($recurrence_id))
				$recurrence_id = add_recurrence_master_record();

			$re_params = array(
				'start_date'					=> ($_POST['recurrence_type'] == 'a') ? sanitize_text_field($_POST['recurrence_start_date']) : sanitize_text_field($_POST['recurrence_manual_dates']),
				'event_end_date'				=> ($_POST['recurrence_type'] == 'a') ? sanitize_text_field($_POST['recurrence_event_end_date']) : sanitize_text_field($_POST['recurrence_manual_end_dates']),
				'end_date'						=> ($_POST['recurrence_type'] == 'a') ? sanitize_text_field($_POST['recurrence_end_date']) : sanitize_text_field($_POST['end_date']),
				'registration_start'			=> sanitize_text_field($_POST['recurrence_regis_start_date']),
				'registration_end'				=> sanitize_text_field($_POST['recurrence_regis_end_date']),
				'frequency'						=> sanitize_text_field($_POST['recurrence_frequency']),
				'interval'						=> sanitize_text_field($_POST['recurrence_interval']),
				'type'							=> sanitize_text_field($_POST['recurrence_type']),
				'weekdays'						=> isset($_POST['recurrence_weekday']) ? $_POST['recurrence_weekday'] : '',
				'repeat_by'						=> $_POST['recurrence_repeat_by'],
				'recurrence_regis_date_increment' => $_POST['recurrence_regis_date_increment'],
				'recurrence_manual_dates'		=> $_POST['recurrence_manual_dates'],
				'recurrence_manual_end_dates'	=> $_POST['recurrence_manual_end_dates'],
				'recurrence_id'					=> $recurrence_id,
				'adding_to_db'					=> 'Y'
			);

			$recurrence_dates = ($_POST['recurrence_type'] == 'm') ? find_recurrence_manual_dates($re_params) : find_recurrence_dates($re_params);
		}
	}

//echo_f('re array', $recurrence_dates);


	if (defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE') && $_POST['recurrence'] == 'Y' && count($recurrence_arr) == 0) {
//skip the first insert because we do not have the start dates
	} else {
		$event_meta = array(); //will be used to hold event meta data
		
		//Filters the event description based on user level
		$user_access = apply_filters( 'filter_hook_espresso_event_unfiltered_description', current_user_can('administrator') );
		$_REQUEST['event_desc'] = is_admin() || $user_access ? $_REQUEST['event_desc'] : apply_filters( 'filter_hook_espresso_event_wp_kses_post_description', wp_kses_post( $_REQUEST['event_desc'] ) );
		
		$event_code			= uniqid($current_user->ID . '-');
		$event_name			= !empty($_REQUEST['event']) ? sanitize_text_field($_REQUEST['event']) : $event_code;
		if (!isset($_REQUEST['event_identifier']) || $_REQUEST['event_identifier'] == ''){
			$event_identifier = sanitize_title_with_dashes($event_name . '-' . $event_code);
		}else{
			$event_identifier = sanitize_title_with_dashes($_REQUEST['event_identifier']) . $event_code;
		}
		$event_desc			= !empty($_REQUEST['event_desc']) ? $_REQUEST['event_desc'] : '';
		$display_desc		= !empty($_REQUEST['display_desc']) ? sanitize_text_field($_REQUEST['display_desc']) : 'Y';
		$display_reg_form	= !empty($_REQUEST['display_reg_form']) ? sanitize_text_field($_REQUEST['display_reg_form']) : 'Y';
		$externalURL		= isset($_REQUEST['externalURL']) ? sanitize_text_field($_REQUEST['externalURL']) : '';
		$post_type			= !empty($_REQUEST['espresso_post_type']) ? sanitize_text_field($_REQUEST['espresso_post_type']) : '';
		$reg_limit			= !empty($_REQUEST['reg_limit']) ? sanitize_text_field($_REQUEST['reg_limit']) : '999999';
		$_REQUEST['reg_limit'] = $reg_limit;
		$allow_multiple		= !empty($_REQUEST['allow_multiple']) ? sanitize_text_field($_REQUEST['allow_multiple']) : 'N';
		$additional_limit	= !empty($_REQUEST['additional_limit']) && $_REQUEST['additional_limit'] > 0 ? sanitize_text_field($_REQUEST['additional_limit']) : '5';
		$_REQUEST['additional_limit'] = $additional_limit;
		$member_only		= !empty($_REQUEST['member_only']) ? sanitize_text_field($_REQUEST['member_only']) : 'N';
		$is_active			= !empty($_REQUEST['is_active']) ? sanitize_text_field($_REQUEST['is_active']) : 'Y';
		$event_status		= !empty($_REQUEST['event_status']) ? sanitize_text_field($_REQUEST['event_status']) : 'A';
		$use_coupon_code	= !empty($_REQUEST['use_coupon_code']) ? sanitize_text_field($_REQUEST['use_coupon_code']) : 'N';
		$ticket_id			= empty($_REQUEST['ticket_id']) ? 0 : sanitize_text_field($_REQUEST['ticket_id']);
		
		//Get the first instance of the start and end times
		$start_time			= !empty($_REQUEST['start_time'][0]) ? sanitize_text_field($_REQUEST['start_time'][0]) : '8:00 AM';
		$_REQUEST['event_start_time'] = $start_time;
		$end_time			= !empty($_REQUEST['end_time'][0]) ? sanitize_text_field($_REQUEST['end_time'][0]) : '5:00 PM';
		$_REQUEST['event_end_time'] = $end_time;
		
		// Add Timezone
		$timezone_string	= isset($_REQUEST['timezone_string']) ? sanitize_text_field($_REQUEST['timezone_string']) : '';
		
		//Early discounts
		$early_disc			= !empty($_REQUEST['early_disc']) ? sanitize_text_field($_REQUEST['early_disc']) : '';
		$early_disc_date	= !empty($_REQUEST['early_disc_date']) ? sanitize_text_field($_REQUEST['early_disc_date']) : '';
		$early_disc_percentage = !empty($_REQUEST['early_disc_percentage']) ? sanitize_text_field($_REQUEST['early_disc_percentage']) : '';
		
		//Alternate email address field
		$alt_email			= !empty($_REQUEST['alt_email']) ? sanitize_text_field($_REQUEST['alt_email']) : '';
		
		//Send a custom emal
		$send_mail			= !empty($_REQUEST['send_mail']) ? sanitize_text_field($_REQUEST['send_mail']) : 'N';
	   	
		//Custom email content
		$conf_mail			= !empty($_REQUEST['conf_mail']) ? esc_html($_REQUEST['conf_mail']) : ''; 
		
		//Use a premade custom email
		$email_id			= isset($_REQUEST['email_name']) ? (int)$_REQUEST['email_name'] : '0';
				
		//Venue Information
		$venue_title		= isset($_REQUEST['venue_title']) ? sanitize_text_field($_REQUEST['venue_title']) : '';
		$venue_url			= isset($_REQUEST['venue_url']) ? sanitize_text_field($_REQUEST['venue_url']) : '';
		$venue_phone		= isset($_REQUEST['venue_phone']) ? sanitize_text_field($_REQUEST['venue_phone']) : '';
		$venue_image		= isset($_REQUEST['venue_image']) ? sanitize_text_field($_REQUEST['venue_image']) : '';

		//Virtual location
		$virtual_url		= !empty($_REQUEST['virtual_url']) ? sanitize_text_field($_REQUEST['virtual_url']) : '';
		$virtual_phone		= !empty($_REQUEST['virtual_phone']) ? sanitize_text_field($_REQUEST['virtual_phone']) : '';
		
		//Address/venue information
		$address 			= isset($_REQUEST['address']) ? sanitize_text_field($_REQUEST['address']) : '';
		$address2 			= isset($_REQUEST['address2']) ? sanitize_text_field($_REQUEST['address2']) : '';
		$city 				= isset($_REQUEST['city']) ? sanitize_text_field($_REQUEST['city']) : '';
		$state 				= isset($_REQUEST['state']) ? sanitize_text_field($_REQUEST['state']) : '';
		$zip 				= isset($_REQUEST['zip']) ? sanitize_text_field($_REQUEST['zip']) : '';
		$country 			= isset($_REQUEST['country']) ? sanitize_text_field($_REQUEST['country']) : '';
		$phone				= isset($_REQUEST['phone']) ? sanitize_text_field($_REQUEST['phone']) : '';
		
		$event_location		= '';
		if ( !empty($address) )
			$event_location	.= $address . ' ';
		
		if ( !empty($address2) )
			$event_location .= '<br />' . $address2;
		
		if ( !empty($city) )
			$event_location .= '<br />' . $city;
			
		if ( !empty($state) )
			$event_location .= ', ' . $state;
		
		if ( !empty($zip) )
			$event_location .= ', ' . $state;
			
		if ( !empty($country) )
			$event_location .= '<br />' . $country;
					
		if ( $is_espresso_event_manager == true && $use_fem == true && $event_manager_approval == true ) {
			$event_status = 'P';
		}
		
		//Get the registration start and end times
		$_REQUEST['registration_startT']	= !empty($_REQUEST['registration_startT']) ? sanitize_text_field($_REQUEST['registration_startT']) : '12:01 AM';
		$_REQUEST['registration_endT']		= !empty($_REQUEST['registration_endT']) ? sanitize_text_field($_REQUEST['registration_endT']) : '11:59 PM';
		
		// Add registration times
		$registration_startT				= event_date_display($_REQUEST['registration_startT'], 'H:i');
		$registration_endT					= event_date_display($_REQUEST['registration_endT'], 'H:i');
		$_REQUEST['registration_start']		= !empty($_REQUEST['registration_start']) ? sanitize_text_field($_REQUEST['registration_start']) : date('Y-m-d');
		$_REQUEST['registration_end']		= !empty($_REQUEST['registration_end']) ? sanitize_text_field($_REQUEST['registration_end']) : date('Y-m-d',time() + (60 * 60 * 24 * 29));
		$registration_start					= array_key_exists('registration_start', $recurrence_arr) ? $recurrence_arr['registration_start'] : $_REQUEST['registration_start'];
		$registration_end					= array_key_exists('registration_end', $recurrence_arr) ? $recurrence_arr['registration_end'] : $_REQUEST['registration_end'];

		//Check which start/end date to use.  Will be determined by recurring events addon, if installed.
		if (array_key_exists('recurrence_start_date', $recurrence_arr)) {
			//Recurring event
			$start_date = $recurrence_arr['recurrence_start_date'];
		} elseif ( !empty($_REQUEST['start_date']) && !empty($_REQUEST['recurrence_start_date']) ) {
			//If they leave the Event Start Date empty, the First Event Date in the recurrence module is selected
			$start_date = sanitize_text_field($_REQUEST['recurrence_start_date']);
		} elseif ( isset($_POST['recurrence']) && $_POST['recurrence'] == 'Y' && !empty($_REQUEST['start_date']) ) {
			$start_date = sanitize_text_field($_REQUEST['recurrence_manual_dates'][0]);
		} else {
			$start_date = !empty($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : date('Y-m-d',time() + (60 * 60 * 24 * 30));
		}
		
		$_REQUEST['start_date'] = $start_date;

		if (array_key_exists('recurrence_event_end_date', $recurrence_arr)) {
			
			//If this is a Recurring event
			$end_date = $recurrence_arr['recurrence_event_end_date'];
			
		} elseif ( !empty($_REQUEST['end_date']) && !empty($_REQUEST['recurrence_event_end_date']) ) {
			
			//If they leave the Event Start Date empty, the First Event Date in the recurrence module is selected
			$end_date = $_REQUEST['recurrence_event_end_date'];
		
		} elseif (isset($_POST['recurrence']) && $_POST['recurrence'] == 'Y' && !empty($_REQUEST['end_date']) ) {
			$end_date = $_REQUEST['recurrence_manual_end_dates'][count($_REQUEST['recurrence_manual_end_dates']) - 1];
		} else {
			$end_date = !empty($_REQUEST['end_date']) ? $_REQUEST['end_date'] : date('Y-m-d',time() + (60 * 60 * 24 * 30));
		}
		$_REQUEST['end_date'] = $end_date;
		
		if (array_key_exists('visible_on', $recurrence_arr)) {
			
			//If this is a Recurring event
			$visible_on = $recurrence_arr['visible_on'];
		
		} elseif (isset($_REQUEST['visible_on']) && $_REQUEST['visible_on'] != '') {
			$visible_on = $_REQUEST['visible_on'];
		} elseif (isset($_REQUEST['visible_on']) && $_REQUEST['visible_on'] == '' && count($recurrence_dates) > 0) {
			$visible_on = $recurrence_dates[$start_date]['visible_on'];
		} else {
			$visible_on = date("Y-m-d");
		}
		
		//Questions/question groups
		$question_groups = empty($_REQUEST['question_groups']) ? serialize(array(1)) : serialize($_REQUEST['question_groups']);
		$add_attendee_question_groups = empty($_REQUEST['add_attendee_question_groups']) ? '' : $_REQUEST['add_attendee_question_groups'];
		
		//Process event meta data
		$event_meta['venue_id'] = isset($_REQUEST['venue_id']) ? $_REQUEST['venue_id'][0] : 0;
		$event_meta['additional_attendee_reg_info'] = !empty($_REQUEST['additional_attendee_reg_info']) ? sanitize_text_field($_REQUEST['additional_attendee_reg_info']) : '2';
		$event_meta['add_attendee_question_groups'] = $add_attendee_question_groups;
		$event_meta['date_submitted'] = date("Y-m-d H:i:s");
		$event_meta['default_payment_status'] = !empty($_REQUEST['default_payment_status']) ? sanitize_text_field($_REQUEST['default_payment_status']) : '';

		//Process thumbnail uploads
		$event_thumbnail_url = '';
		if (isset($_REQUEST['upload_image']) && !empty($_REQUEST['upload_image']) ){
			$event_meta['event_thumbnail_url'] = sanitize_text_field($_REQUEST['upload_image']);
			$event_thumbnail_url = sanitize_text_field($event_meta['event_thumbnail_url']);
		}
			
		
		if ( isset($_REQUEST['emeta']) && !empty($_REQUEST['emeta']) ) {
			foreach ($_REQUEST['emeta'] as $k => $v) {
				$event_meta[$v] = strlen(trim($_REQUEST['emetad'][$k])) > 0 ? sanitize_text_field($_REQUEST['emetad'][$k]) : '';
			}
		}
		$event_meta = serialize($event_meta);		
		//echo strlen(trim($_REQUEST['emetad'][$k]));
	  	//print_r($_REQUEST['emeta'] );

		############ Added by wp-developers ######################
		$require_pre_approval = 0;
		if (isset($_REQUEST['require_pre_approval'])) {
			$require_pre_approval = sanitize_text_field($_REQUEST['require_pre_approval']);
		}
		################# END #################
		
		//When adding colums to the following arrays, be sure both arrays have equal values.
		$sql = array(
			'event_code'				=> $event_code, 
			'event_name'				=> $event_name, 
			'event_desc'				=> $event_desc, 
			'display_desc'				=> $display_desc, 
			'display_reg_form'			=> $display_reg_form, 
			'event_identifier'			=> $event_identifier,
			'address'					=> $address, 
			'address2'					=> $address2, 
			'city'						=> $city, 
			'state'						=> $state, 
			'zip'						=> $zip, 
			'country'					=> $country, 
			'phone'						=> $phone, 
			'virtual_url'				=> $virtual_url,
			'virtual_phone'				=> $virtual_phone, 
			'venue_title'				=> $venue_title, 
			'venue_url'					=> $venue_url, 
			'venue_phone'				=> $venue_phone, 
			'venue_image'				=> $venue_image,
			'registration_start'		=> $registration_start, 
			'registration_end'			=> $registration_end, 
			'start_date'				=> $start_date, 
			'end_date'					=> $end_date,
			'allow_multiple'			=> $allow_multiple, 
			'send_mail'					=> $send_mail, 
			'is_active'					=> $is_active, 
			'event_status'				=> $event_status,
			'conf_mail'					=> $conf_mail, 
			'use_coupon_code'			=> $use_coupon_code, 
			'member_only'				=> $member_only, 
			'externalURL'				=> $externalURL,
			'early_disc'				=> $early_disc, 
			'early_disc_date'			=> $early_disc_date, 
			'early_disc_percentage'		=> $early_disc_percentage, 
			'alt_email'					=> $alt_email,
			'question_groups'			=> $question_groups, 
			'registration_startT'		=> $registration_startT, 
			'registration_endT'			=> $registration_endT, 
			'reg_limit'					=> (int)$reg_limit, 
			'additional_limit'			=> (int)$additional_limit, 
			'recurrence_id'				=> $recurrence_id, 
			'email_id'					=> (int)$email_id, 
			'wp_user'					=> (int)$current_user->ID, 
			'event_meta'				=> $event_meta, 
			'require_pre_approval'		=> $require_pre_approval, 
			'timezone_string'			=> $timezone_string, 
			'submitted'					=> date('Y-m-d H:i:s', time()), 
			'ticket_id'					=> (int)$ticket_id
		);

		$sql_data = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d');

		/* echo 'Debug: <br />';
		  print_r($sql);
		  echo '<br />';
		  print 'Number of vars: ' . count ($sql);
		  echo '<br />';
		  print 'Number of cols: ' . count($sql_data); */


		//Add groupon reference if installed
		if (function_exists('event_espresso_add_event_to_db_groupon')) {
			$sql = event_espresso_add_event_to_db_groupon($sql, sanitize_text_field($_REQUEST['use_groupon_code']));
			
			//print count ($sql);
			$sql_data = array_merge((array) $sql_data, (array) '%s');
			
			//print count($sql_data);
			if ( ! $wpdb->insert(EVENTS_DETAIL_TABLE, $sql, $sql_data )) {
				$error = true;
			}
			
		} else {
			if ( ! $wpdb->insert(EVENTS_DETAIL_TABLE, $sql, $sql_data )) {
				$error = true;
			}
		}

		$last_event_id = $wpdb->insert_id;

		############# MailChimp Integration ##############
		if (defined('EVENTS_MAILCHIMP_ATTENDEE_REL_TABLE') && $espresso_premium == true) {
			MailChimpController::add_event_list_rel($last_event_id);
		}
		if (function_exists('espresso_fb_createevent') == 'true' && $espresso_premium == true) {
			espresso_fb_createevent($last_event_id);
		}
		
		//Added for seating chart addon
		if ( isset($_REQUEST['seating_chart_id']) ){
			$cls_seating_chart = new seating_chart();
			$cls_seating_chart->associate_event_seating_chart($_REQUEST['seating_chart_id'],$last_event_id);
		}

		//Add event to a category
		$string_cat = '';
		if (isset($_REQUEST['event_category']) && $_REQUEST['event_category'] != '') {
			foreach ($_REQUEST['event_category'] as $k => $v) {
				if ($v != '') {
					$sql_cat = "INSERT INTO " . EVENTS_CATEGORY_REL_TABLE . " (event_id, cat_id) VALUES ('" . $last_event_id . "', '" . (int)$v . "')";
					if ( !$wpdb->query($wpdb->prepare($sql_cat, NULL)) ) {
						$error = true;
					}
					//We get the category id's of the event and put them in events_detail_table.category_id as a well-formatted string (id,n id)
					$string_cat.=$v.",";
				}
			}

			if($string_cat != "" && $string_cat != ","){
				$cleaned_string_cat = substr($string_cat, 0, -1);
				$tmp=explode(",",$cleaned_string_cat);
				sort($tmp);
				$cleaned_string_cat=implode(",", $tmp);
				trim($cleaned_string_cat);
	
				$sql_insert_event_detail_category_id="UPDATE ".EVENTS_DETAIL_TABLE." SET category_id = '".$cleaned_string_cat."' WHERE id='" . $last_event_id . "'";
				$wpdb->query($wpdb->prepare($sql_insert_event_detail_category_id, NULL));
			}

		}
		
		//Process staff assignments
		if (isset($_REQUEST['event_person']) && !empty($_REQUEST['event_person'])) {
			foreach ($_REQUEST['event_person'] as $k => $v) {
				if ($v != '') {
					$sql_ppl = "INSERT INTO " . EVENTS_PERSONNEL_REL_TABLE . " (event_id, person_id) VALUES ('" . $last_event_id . "', '" . $v . "')";
					//echo "$sql_ppl <br>";
					$wpdb->query($wpdb->prepare($sql_ppl, NULL));
				}
			}
		}
		
		//Process dynamic adding of venues
		//If we are adding an venue from within the event editor.
		//Then we need to add it to the venue database and add the id to the event.
		if ( isset($_REQUEST['add_new_venue_dynamic']) && $_REQUEST['add_new_venue_dynamic'] == 'true' && $_REQUEST['venue_id'][0] == '0') {
			require_once(EVENT_ESPRESSO_INCLUDES_DIR.'admin-files/venue-management/add_venue_to_db.php');
			$_REQUEST['venue_id'][0] = add_venue_to_db();
		}
		
		//Process venues
		if (isset($_REQUEST['venue_id']) && !empty($_REQUEST['venue_id'])) {
			foreach ($_REQUEST['venue_id'] as $k => $v) {
				if ($v != '' && $v != 0) {
					$sql_venues = "INSERT INTO " . EVENTS_VENUE_REL_TABLE . " (event_id, venue_id) VALUES ('" . $last_event_id . "', '" . $v . "')";
					$wpdb->query($wpdb->prepare($sql_venues, NULL));
				}
			}
		}
		
		//Process the discounts
		if (isset($_REQUEST['event_discount']) && !empty($_REQUEST['event_discount']) && $_REQUEST['use_coupon_code']=='Y') {
			//if they have specified to use specific coupon codes, THEN we add entries ot teh discount rel table
			//otherwise we shouldn't
			foreach ($_REQUEST['event_discount'] as $k => $v) {
				if ($v != '') {
					$sql_cat = "INSERT INTO " . EVENTS_DISCOUNT_REL_TABLE . " (event_id, discount_id) VALUES ('" . $last_event_id . "', '" . $v . "')";
					if ( !$wpdb->query($wpdb->prepare($sql_cat, NULL)) ) {
						$error = true;
					}
				}
			}
		}
		
		//Process event times
		if (isset($_REQUEST['start_time']) && !empty($_REQUEST['start_time'])) {
			foreach ($_REQUEST['start_time'] as $k => $v) {
				$time_qty = ( isset( $_REQUEST[ 'time_qty' ] ) && strlen( trim( $_REQUEST['time_qty'][$k] ) ) > 0 )? "'" . $_REQUEST['time_qty'][$k] . "'" : '0' ;
				$v = !empty($v) ? $v : $start_time;
				$_REQUEST['end_time'][$k] = !empty($_REQUEST['end_time'][$k]) ? $_REQUEST['end_time'][$k] : $end_time;
				$sql3 = "INSERT INTO " . EVENTS_START_END_TABLE . " (event_id, start_time, end_time, reg_limit) VALUES ('" . $last_event_id . "', '" . event_date_display($v, 'H:i') . "', '" . event_date_display($_REQUEST['end_time'][$k], 'H:i') . "', " . $time_qty . ")";
				if ( !$wpdb->query( $wpdb->prepare($sql3, NULL) ) ) {
					$error = true;
				}
			}
		}
		
		//Process event costs
		if ( isset($_REQUEST['event_cost']) && !empty($_REQUEST['event_cost']) ) {
			foreach ($_REQUEST['event_cost'] as $k => $v) {
				if ($v != '') {
					$v = (float)preg_replace('/[^0-9\.]/ui','',$v);//Removes non-integer characters
					$price_type = !empty($_REQUEST['price_type'][$k]) ? sanitize_text_field(stripslashes_deep($_REQUEST['price_type'][$k])) : __('General Admission', 'event_espresso');
					$member_price_type = !empty($_REQUEST['member_price_type'][$k]) ? sanitize_text_field(stripslashes_deep($_REQUEST['member_price_type'][$k])) : __('Members Admission', 'event_espresso');
					$member_price = !empty($_REQUEST['member_price'][$k]) ? $_REQUEST['member_price'][$k] : $v;
					//$sql_price = "INSERT INTO " . EVENTS_PRICES_TABLE . " (event_id, event_cost, surcharge, surcharge_type, price_type, member_price, member_price_type) VALUES ('" . $last_event_id . "', '" . $v . "', '" . $_REQUEST['surcharge'][$k] . "', '" . $_REQUEST['surcharge_type'][$k] . "', '" . $price_type . "', '" . $member_price . "', '" . $member_price_type . "')";
					//echo "$sql3 <br>";
					
					$sql_price = array('event_id' => $last_event_id, 'event_cost' => $v, 'surcharge' => $_REQUEST['surcharge'][$k], 'surcharge_type' => $_REQUEST['surcharge_type'][$k], 'price_type' => $price_type, 'member_price' => $member_price, 'member_price_type' => $member_price_type );
					$sql_price_data = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');
					
					if ( !$wpdb->insert(EVENTS_PRICES_TABLE, $sql_price, $sql_price_data) ) {
						$error = true;
					}
				}
			}
		} elseif (isset($_REQUEST['event_cost']) && $_REQUEST['event_cost'][0] == 0) {
			$sql_price = "INSERT INTO " . EVENTS_PRICES_TABLE . " (event_id, event_cost, surcharge, price_type, member_price, member_price_type) VALUES ('" . $last_event_id . "', '0.00', '0.00', '" . __('Free', 'event_espresso') . "', '0.00', '" . __('Free', 'event_espresso') . "')";
			if ( !$wpdb->query($wpdb->prepare($sql_price, NULL)) ) {
				$error = true;
			}
		}

		//Process blog or custom post
		if ( isset($_REQUEST['create_post']) && $_REQUEST['create_post'] == 'Y' ) {
			$post_type = !empty($_REQUEST['espresso_post_type']) ? $_REQUEST['espresso_post_type'] : 'post';
			if ($post_type == 'post') {
				if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "event_post.php") || file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/event_post.php")) {
					
					// Load message from template into message post variable
					ob_start();
					if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR . "event_post.php")) {
						require_once(EVENT_ESPRESSO_TEMPLATE_DIR . "event_post.php");
					} else {
						require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "templates/event_post.php");
					}
					$post_content = ob_get_contents();
					ob_end_clean();
				} else {
					_e('There was error finding a post template. Please verify your post templates are available.', 'event_espresso');
				}
			} elseif ($post_type == 'espresso_event') {
				ob_start();
				echo $event_desc;
				$post_content = ob_get_contents();
				ob_end_clean();
				// if there's a cart link shortcode in the post, replace the shortcode with one that includes the event_id
				if (preg_match("/ESPRESSO_CART_LINK/", $post_content)) { $post_content = preg_replace('/ESPRESSO_CART_LINK/', 'ESPRESSO_CART_LINK event_id=' . $event_id, $post_content); }
			}
			$my_post = array();

			$my_post['post_title'] = sanitize_text_field($_REQUEST['event']);
			$my_post['post_content'] = $post_content;
			$my_post['post_status'] = 'publish';
			$my_post['post_author'] = !empty($_REQUEST['user']) ? $_REQUEST['user'] : '';
			$my_post['post_category'] = !empty($_REQUEST['post_category']) ? $_REQUEST['post_category'] : '';
			$my_post['tags_input'] = !empty($_REQUEST['post_tags']) ? $_REQUEST['post_tags'] : '';
			$my_post['post_type'] = !empty($post_type) ? $post_type : 'post';
			//print_r($my_post);
			// Insert the post into the database
			$post_id = wp_insert_post($my_post);
			// Store the POST ID so it can be displayed on the edit page
			$sql = array('post_id' => $post_id, 'post_type' => $post_type);

			add_post_meta($post_id, 'event_id', $last_event_id);
			add_post_meta($post_id, 'event_meta', $event_meta);
			add_post_meta($post_id, 'event_identifier', $event_identifier);
			add_post_meta($post_id, 'event_start_date', $start_date);
			add_post_meta($post_id, 'event_end_date', $end_date);
			add_post_meta($post_id, 'event_location', $event_location);
			add_post_meta($post_id, 'event_thumbnail_url', $event_thumbnail_url);
			add_post_meta($post_id, 'virtual_url', $virtual_url);
			add_post_meta($post_id, 'virtual_phone', $virtual_phone);
			add_post_meta($post_id, 'event_address', $address);
			add_post_meta($post_id, 'event_address2', $address2);
			add_post_meta($post_id, 'event_city', $city);
			add_post_meta($post_id, 'event_state', $state);
			add_post_meta($post_id, 'event_country', $country);
			add_post_meta($post_id, 'event_phone', $phone);
			add_post_meta($post_id, 'venue_title', $venue_title);
			add_post_meta($post_id, 'venue_url', $venue_url);
			add_post_meta($post_id, 'venue_phone', $venue_phone);
			add_post_meta($post_id, 'venue_image', $venue_image);
			add_post_meta($post_id, 'event_externalURL', $externalURL);
			add_post_meta($post_id, 'event_reg_limit', $reg_limit);
			add_post_meta($post_id, 'event_start_time', time_to_24hr($start_time));
			add_post_meta($post_id, 'event_end_time', time_to_24hr($end_time));
			add_post_meta($post_id, 'event_registration_start', $registration_start);
			add_post_meta($post_id, 'event_registration_end', $registration_end);
			add_post_meta($post_id, 'event_registration_startT', $registration_startT);
			add_post_meta($post_id, 'event_registration_endT', $registration_endT);
			//add_post_meta( $post_id, 'timezone_string', $_REQUEST['timezone_string'] );

			$sql_data = array('%d', '%s');
			$update_id = array('id' => $last_event_id);
			$wpdb->update(EVENTS_DETAIL_TABLE, $sql, $update_id, $sql_data, array('%d'));
		}

		if (empty($error)) {
			?>
			<div id="message" class="updated fade"><p><strong><?php _e('The event', 'event_espresso'); ?>
			<a href="<?php echo espresso_reg_url($last_event_id); ?>" target="_blank">
				<?php echo htmlentities( stripslashes( sanitize_text_field( $_REQUEST['event'] )), ENT_QUOTES, 'UTF-8' ); ?>				
			</a>

			<?php _e('has been added for ', 'event_espresso'); ?><?php echo date("m/d/Y", strtotime($start_date)); ?> <a href="<?php echo admin_url(); ?>admin.php?page=events&action=edit&event_id=<?php echo $last_event_id; ?>"><?php _e('Edit this event?', 'event_espresso'); ?></a></strong></p></div>
		<?php } else { ?>
			<div id="message" class="error"><p><strong><?php _e('There was an error in your submission. The event was not saved! Please try again.', 'event_espresso'); ?> <?php print $wpdb->print_error(); ?></strong></p></div>
			<?php
		}
	}

	//With the recursion of this function, additional recurring events will be added
	if (isset($recurrence_dates) && count($recurrence_dates) > 0) {
		foreach ($recurrence_dates as $k => $v) {
			add_event_to_db(
					array(
						'recurrence_id' => $recurrence_id,
						'recurrence_start_date' => $v['start_date'],
						'recurrence_event_end_date' => $v['event_end_date'],
						'registration_start' => $v['registration_start'],
						'registration_end' => $v['registration_end'],
			));
		}
	}
	
	$_REQUEST['event_id'] = !empty($last_event_id) ? $last_event_id : '';
	do_action('action_hook_espresso_insert_event_success',$_REQUEST);
	
	//If not using the FEM addon, then we return the event id
	if ( !$use_fem === TRUE )
		return @$last_event_id;
	
}//End add_event_funct_to_db()
