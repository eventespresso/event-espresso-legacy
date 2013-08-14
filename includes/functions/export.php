<?php

// Export data for event, called by excel request below
if (!function_exists('espresso_event_export')) {

	function espresso_event_export($ename) {
		global $wpdb, $org_options;
		$sql = '';
		$htables = array();
		$htables[] = 'Event Id';
		$htables[] = 'Name';
		$htables[] = 'Venue';
		$htables[] = 'Start Date';
		$htables[] = 'Start Time';
		$htables[] = 'DoW';
		$htables[] = 'Reg Begins';

		if (function_exists('espresso_is_admin') && espresso_is_admin() == true && (isset($espresso_premium) && $espresso_premium == true)) {
			$htables[] = 'Submitter';
		}

		$htables[] = 'Status';
		$htables[] = 'Attendees';

		if (isset($_REQUEST['month_range'])) {
			$pieces = explode('-', $_REQUEST['month_range'], 3);
			$year_r = $pieces[0];
			$month_r = $pieces[1];
		}


		$group = '';

		if (function_exists('espresso_member_data') && espresso_member_data('role') == 'espresso_group_admin') {

			$group = get_user_meta(espresso_member_data('id'), "espresso_group", true);
			$group = maybe_unserialize($group);

			$sql = "(SELECT e.id event_id, e.event_name, e.event_identifier, e.reg_limit, e.registration_start, ";
			$sql .= " e.start_date, e.is_active, e.recurrence_id, e.registration_startT, ";
			$sql .= " e.address, e.address2, e.city, e.state, e.zip, e.country, ";
			$sql .= " e.venue_title, e.phone, e.wp_user ";

			if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y') {
				$sql .= ", v.name venue_name, v.address venue_address, v.address2 venue_address2, v.city venue_city, v.state venue_state, v.zip venue_zip, v.country venue_country, v.meta venue_meta ";
			}

			$sql .= " FROM " . EVENTS_DETAIL_TABLE . " e ";

			if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y') {
				$sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = e.id LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = r.venue_id ";
			}

			if ($_REQUEST['category_id'] != '') {
				$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
				$sql .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
			}

			if ($group != '') {
				$sql .= " JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = e.id ";
				$sql .= " JOIN " . EVENTS_LOCALE_REL_TABLE . " l ON  l.venue_id = r.venue_id ";
			}

			$sql .= ($_POST['event_status'] != '' && $_POST['event_status'] != 'IA') ? " WHERE event_status = '" . $_POST['event_status'] . "' " : " WHERE event_status != 'D' ";
			$sql .= $_REQUEST['category_id'] != '' ? " AND c.id = '" . $_REQUEST['category_id'] . "' " : '';
			$sql .= $group != '' ? " AND l.locale_id IN (" . implode(",", $group) . ") " : '';

			if ($_POST['month_range'] != '') {
				$sql .= " AND start_date BETWEEN '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-31')) . "' ";
			}

			if ($_REQUEST['today'] == 'true') {
				$sql .= " AND start_date = '" . $curdate . "' ";
			}

			if ($_REQUEST['this_month'] == 'true') {
				$sql .= " AND start_date BETWEEN '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-' . $days_this_month)) . "' ";
			}

			$sql .= ") UNION ";
		}


		$sql .= "(SELECT e.id event_id, e.event_name, e.event_identifier, e.reg_limit, e.registration_start, ";
		$sql .= " e.start_date,  e.end_date, e.is_active, e.recurrence_id, e.registration_startT, ";
		$sql .= " e.address, e.address2, e.city, e.state, e.zip, e.country, ";
		$sql .= " e.venue_title, e.phone, e.wp_user ";

		if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y') {
			$sql .= ", v.name venue_name, v.address venue_address, v.address2 venue_address2, v.city venue_city, v.state venue_state, v.zip venue_zip, v.country venue_country, v.meta venue_meta ";
		}

		$sql .= " FROM " . EVENTS_DETAIL_TABLE . " e ";

		if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y') {
			$sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = e.id LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = r.venue_id ";
		}

		if (isset($_REQUEST['category_id']) && $_REQUEST['category_id'] != '') {
			$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
			$sql .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
		}

		$sql .= (isset($_POST['event_status']) && $_POST['event_status'] != '' && $_POST['event_status'] != 'IA') ? " WHERE event_status = '" . $_POST['event_status'] . "' " : " WHERE event_status != 'D' ";
		$sql .= isset($_REQUEST['category_id']) && $_REQUEST['category_id'] != '' ? " AND c.id = '" . $_REQUEST['category_id'] . "' " : '';

		if (isset($_POST['month_range']) && $_POST['month_range'] != '') {
			$sql .= " AND start_date BETWEEN '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($year_r . '-' . $month_r . '-31')) . "' ";
		}

		if (isset($_REQUEST['today']) && $_REQUEST['today'] == 'true') {
			$sql .= " AND start_date = '" . $curdate . "' ";
		}

		if (isset($_REQUEST['this_month']) && $_REQUEST['this_month'] == 'true') {
			$sql .= " AND start_date BETWEEN '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-01')) . "' AND '" . date('Y-m-d', strtotime($this_year_r . '-' . $this_month_r . '-' . $days_this_month)) . "' ";
		}

		if (function_exists('espresso_member_data') && ( espresso_member_data('role') == 'espresso_event_manager' || espresso_member_data('role') == 'espresso_group_admin')) {
			$sql .= " AND wp_user = '" . espresso_member_data('id') . "' ";
		}

		$sql .= ") ORDER BY start_date = '0000-00-00' ASC, start_date ASC, event_name ASC";

		ob_start();

		//echo $sql;
		$today = date("Y-m-d-Hi", time());
		$filename = $_REQUEST['all_events'] == "true" ? __('all-events', 'event_espresso') : $event_name;
		$filename = sanitize_title_with_dashes($filename) . "-" . $today;
		switch ($_REQUEST['type']) {
			case "csv" :
				$st = "";
				$et = ",";
				$s = $et . $st;
				header("Content-type: application/x-msdownload");
				header("Content-Disposition: attachment; filename=" . $filename . ".csv");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo implode($s, $htables) . "\r\n";
				break;
			default :
				$st = "";
				$et = "\t";
				$s = $et . $st;
				header("Content-Disposition: attachment; filename=" . $filename . ".xls");
				header("Content-Type: application/vnd.ms-excel");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo implode($s, $htables) . $et . "\r\n";
				break;
		}
		$events = $wpdb->get_results($sql);
		foreach ($events as $event) {
			$event_id = $event->event_id;
			$event_name = stripslashes_deep($event->event_name);
			$event_identifier = stripslashes_deep($event->event_identifier);
			$reg_limit = $event->reg_limit;
			$registration_start = $event->registration_start;
			$start_date = event_date_display($event->start_date, 'Y-m-d');
			$end_date = event_date_display($event->end_date, 'Y-m-d');
			$is_active = $event->is_active;
			$status = array();
			$status = event_espresso_get_is_active($event_id);
			$recurrence_id = $event->recurrence_id;
			$registration_startT = $event->registration_startT;


			//Venue variables
			if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y') {
				//$data = new stdClass;
				//$data->event->venue_meta = unserialize($event->venue_meta);
				//Debug
				//echo "<pre>".print_r($data->event->venue_meta,true)."</pre>";
				$venue_title = $event->venue_name;
				/* $data->event->venue_url = $data->event->venue_meta['website'];
				  $data->event->venue_phone = $data->event->venue_meta['phone'];
				  $data->event->venue_image = '<img src="'.$data->event->venue_meta['image'].'" />';
				  $data->event->address = $data->event->venue_address;
				  $data->event->address2 = $data->event->venue_address2;
				  $data->event->city = $data->event->venue_city;
				  $data->event->state = $data->event->venue_state;
				  $data->event->zip = $data->event->venue_zip;
				  $data->event->country = $data->event->venue_country; */
			} else {
				$venue_title = $event->venue_title;
				/* $event_address = $event->address;
				  $event_address2 = $event->address2;
				  $event_city = $event->city;
				  $event_state = $event->state;
				  $event_zip = $event->zip;
				  $event_country = $event->country;
				  $event_phone = $event->phone; */
			}


			$wp_user = $event->wp_user;
			//$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
			$dow = date("D", strtotime($start_date));
			echo $event_id
			. $s . $event_name
			. $s . $venue_title
			. $s . $start_date
			. $s . event_espresso_get_time($event_id, 'start_time')
			. $s . $dow
			. $s . str_replace(',', ' ', event_date_display($registration_start, get_option('date_format'))); // ticket 570

			if (function_exists('espresso_is_admin') && espresso_is_admin() == true && (isset($espresso_premium) && $espresso_premium == true)) {
				$user_company = espresso_user_meta($wp_user, 'company') != '' ? espresso_user_meta($wp_user, 'company') : '';
				$user_organization = espresso_user_meta($wp_user, 'organization') != '' ? espresso_user_meta($wp_user, 'organization') : '';
				$user_co_org = $user_company != '' ? $user_company : $user_organization;
				echo $s . (espresso_user_meta($wp_user, 'user_firstname') != '' ? espresso_user_meta($wp_user, 'user_firstname') . ' ' . espresso_user_meta($wp_user, 'user_lastname') : espresso_user_meta($wp_user, 'display_name'));
			}

			echo $s . strip_tags($status['display']) . $s . str_replace('/', ' of ', get_number_of_attendees_reg_limit($event_id, 'num_attendees_slash_reg_limit'));

			switch ($_REQUEST['type']) {
				case "csv" : echo "\r\n";
					break;
				default : echo $et . "\r\n";
					break;
			}
		}
	}

}

if (!function_exists('espresso_export_stuff')) {

	function espresso_export_stuff() {

		$today = date("Y-m-d-Hi", time());
		$export_all_events = isset($_REQUEST['all_events']) && $_REQUEST['all_events'] == "true" ? TRUE : FALSE;

		//Export data to Excel file
		if (isset($_REQUEST['export'])) {
			switch ($_REQUEST['export']) {

				case "report":
					global $wpdb;

					$event_id = isset($_REQUEST['event_id']) ? $_REQUEST['event_id'] : FALSE;

					// export for one event only ?
					if ($event_id) {

						$SQL = "SELECT event_name, event_desc, event_identifier, question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE;
						$SQL .= " WHERE id = %d";

						if ($results = $wpdb->get_row($wpdb->prepare($SQL, $event_id), ARRAY_N)) {

							list( $event_name, $event_description, $event_identifier, $question_groups, $event_meta) = $results;

							$question_groups = maybe_unserialize($question_groups);
							$event_meta = maybe_unserialize($event_meta);

							if (!empty($event_meta['add_attendee_question_groups'])) {
								$question_groups = array_unique(array_merge((array) $question_groups, (array) $event_meta['add_attendee_question_groups']));
							}
						}
					} else {

						// export for ALL EVENTS

						$question_groups = array();
						$event_meta = array();
						$SQL = "SELECT event_name, event_desc, event_identifier, question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE;
						if ($results = $wpdb->get_results($SQL, ARRAY_N)) {

							foreach ($results as $result) {

								list( $event_name, $event_description, $event_identifier, $q_groups, $e_meta) = $result;
								$question_groups = array_unique(array_merge($question_groups, (array) maybe_unserialize($q_groups)));
								$e_meta = (array) maybe_unserialize($e_meta);
								$event_meta = array_unique(array_merge($event_meta, (array) $e_meta['add_attendee_question_groups']));
							}
						}
					}

					$basic_header = array(__('Group', 'event_espresso'), __('ID', 'event_espresso'), __('Reg ID', 'event_espresso'), __('Payment Method', 'event_espresso'), __('Reg Date', 'event_espresso'), __('Pay Status', 'event_espresso'), __('Type of Payment', 'event_espresso'), __('Transaction ID', 'event_espresso'), __('Price', 'event_espresso'), __('Coupon Code', 'event_espresso'), __('# Attendees', 'event_espresso'), __('Amount Paid', 'event_espresso'), __('Date Paid', 'event_espresso'), __('Event Name', 'event_espresso'), __('Price Option', 'event_espresso'), __('Event Date', 'event_espresso'), __('Event Time', 'event_espresso'), __('Website Check-in', 'event_espresso'), __('Tickets Scanned', 'event_espresso'), __('Check-in Date', 'event_espresso'),__('Seat Tag', 'event_espresso'), __('First Name', 'event_espresso'), __('Last Name', 'event_espresso'), __('Email', 'event_espresso'));

					$question_groups = maybe_unserialize($question_groups);
					$event_meta = maybe_unserialize($event_meta);

					if (isset($event_meta['add_attendee_question_groups'])) {

//					if ( is_serialized(  $event_meta['add_attendee_question_groups'] ) ){
//						$add_attendee_question_groups = unserialize($event_meta['add_attendee_question_groups']);
//					} else {
//						$add_attendee_question_groups = $event_meta['add_attendee_question_groups'];
//					}					

						if (!empty($add_attendee_question_groups)) {
							$question_groups = array_unique(array_merge((array) $question_groups, (array) $event_meta['add_attendee_question_groups']));
						}
					}


					switch ($_REQUEST['action']) {

						case "event":
							espresso_event_export($event_name);
							break;

						case "payment":

							$question_list = array(); //will be used to associate questions with correct answers
							$question_filter = array(); //will be used to keep track of newly added and deleted questions

							if (count($question_groups) > 0) {
								$question_sequence = array();

								$questions_in = '';
								foreach ($question_groups as $g_id) {
									$questions_in .= $g_id . ',';
								}
								$questions_in = substr($questions_in, 0, -1);

								$group_name = '';
								$counter = 0;

								$quest_sql = "SELECT q.id, q.question FROM " . EVENTS_QUESTION_TABLE . " q ";
								$quest_sql .= " JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
								$quest_sql .= " JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
								$quest_sql .= " WHERE qgr.group_id in ( $questions_in ) ";
								if (function_exists('espresso_member_data') && ( espresso_member_data('role') == 'espresso_event_manager')) {
									$quest_sql .= " AND qg.wp_user = '" . espresso_member_data('id') . "' ";
								}
								//Fix from Jesse in the forums (http://eventespresso.com/forums/2010/10/form-questions-appearing-in-wrong-columns-in-excel-export/)
								//$quest_sql .= " AND q.system_name is null ORDER BY qg.id, q.id ASC ";
								//$quest_sql .= " AND q.system_name is null ";
								$quest_sql .= " ORDER BY q.sequence, q.id ASC ";

								$questions = $wpdb->get_results($quest_sql);
								$ignore = array('1'=>1, '2'=>2, '3'=>3);

								$num_rows = $wpdb->num_rows;
								if ($num_rows > 0) {
									foreach ($questions as $question) {
										if (!isset($ignore[$question->id])) {
											$question_list[$question->id] = $question->question;
											$question_filter[$question->id] = $question->id;
											$question_text = escape_csv_val( stripslashes( $question->question ));
											if ( ! in_array( $question_text, $basic_header )) {
												array_push( $basic_header, $question_text );
											}																	
										}
									}
								}
							}

							if (count($question_filter) > 0) {
								$question_filter = implode(",", $question_filter);
							}
							//$question_filter = str_replace( array( '1,','2,','3,' ), '', $question_filter );

							$sql = '';

							$espresso_member = function_exists('espresso_member_data') && espresso_member_data('role') == 'espresso_group_admin' ? TRUE : FALSE;

							if ($espresso_member) {

								$group = get_user_meta(espresso_member_data('id'), "espresso_group", true);
								$group = maybe_unserialize($group);
								$group = implode(",", $group);
								$sql .= "(SELECT ed.event_name, ed.start_date, a.id AS att_id, a.registration_id, a.payment, a.date, a.payment_status, a.txn_type, a.txn_id";
								$sql .= ", a.amount_pd, a.quantity, a.coupon_code, a.checked_in, a.checked_in_quantity";
								if ( function_exists('espresso_ticketing_install') )
									$sql.=", ac.date_scanned";
								$sql .= ", a.payment_date, a.event_time, a.price_option, a.final_price a_final_price, a.quantity a_quantity, a.fname, a.lname, a.email";
								$sql .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
								$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=a.event_id ";

								if ( function_exists('espresso_ticketing_install') )
									$sql .= " LEFT JOIN " . $wpdb->prefix . "events_attendee_checkin ac ON a.id=ac.attendee_id ";
								if ($group != '') {
									$sql .= " JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = ed.id ";
									$sql .= " JOIN " . EVENTS_LOCALE_REL_TABLE . " l ON  l.venue_id = r.venue_id ";
								}
								$sql .= $event_id ? " WHERE ed.id = '" . $event_id . "' " : '';
								$sql .= $group != '' ? " AND  l.locale_id IN (" . $group . ") " : '';
								$sql .= ") UNION (";
							}
							$sql .= "SELECT ed.event_name, ed.start_date, a.id AS att_id, a.registration_id, a.payment, a.date, a.payment_status, a.txn_type, a.txn_id";
							$sql .= ", a.quantity, a.coupon_code, a.checked_in, a.checked_in_quantity, a.final_price a_final_price, a.amount_pd, a.quantity a_quantity";

							if ( function_exists('espresso_ticketing_install') )
									$sql.=", ac.date_scanned";

							$sql .= ", a.payment_date, a.event_time, a.price_option, a.fname, a.lname, a.email";
							$sql .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
							$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=a.event_id ";
							if ( function_exists('espresso_ticketing_install') )
								$sql .= " LEFT JOIN " . $wpdb->prefix . "events_attendee_checkin ac ON a.id=ac.attendee_id ";
							//$sql .= " JOIN " . EVENTS_ATTENDEE_COST_TABLE . " ac ON a.id=ac.attendee_id ";
							$sql .= $event_id ? " WHERE ed.id = '" . $event_id . "' " : '';

							$sql .= apply_filters('filter_hook_espresso_export_payments_query_where', '');

							if (function_exists('espresso_member_data') && ( espresso_member_data('role') == 'espresso_event_manager' || espresso_member_data('role') == 'espresso_group_admin')) {
								$sql .= " AND ed.wp_user = '" . espresso_member_data('id') . "' ";
							}

							$sql .= $espresso_member ? ") ORDER BY att_id " : " ORDER BY a.id ";

							$participants = $wpdb->get_results($sql);

							$filename = ( isset($_REQUEST['all_events']) && $_REQUEST['all_events'] == "true" ) ? __('all-events', 'event_espresso') : $event_name;

							$filename = sanitize_title_with_dashes($filename) . "-" . $today;
							switch ($_REQUEST['type']) {
								case "csv" :
									$st = "";
									$et = ",";
									$s = $et . $st;
									header("Content-type: application/x-msdownload");
									header("Content-Disposition: attachment; filename=" . $filename . ".csv");
									//header("Content-Disposition: attachment; filename='" .$filename .".csv'");
									header("Pragma: no-cache");
									header("Expires: 0");
									//echo header
									echo implode($s, $basic_header) . "\r\n";
									break;

								default :
									$st = "";
									$et = "\t";
									$s = $et . $st;
									header("Content-Disposition: attachment; filename=" . $filename . ".xls");
									//header("Content-Disposition: attachment; filename='" .$filename .".xls'");
									header("Content-Type: application/vnd.ms-excel");
									header("Pragma: no-cache");
									header("Expires: 0");
									//echo header
									echo implode($s, $basic_header) . $et . "\r\n";
									break;
							}


							if ($participants) {
								$temp_reg_id = ''; //will temporarily hold the registration id for checking with the next row
								$attendees_group = ''; //will hold the names of the group members
								$group_counter = 1;
								$amount_pd = 0;

								foreach ($participants as $participant) {

									if ($temp_reg_id == '') {
										$temp_reg_id = $participant->registration_id;
										$amount_pd = $participant->amount_pd;
									}


									if ($temp_reg_id == $participant->registration_id) {
										//Do nothing
									} else {
										$group_counter++;
										$temp_reg_id = $participant->registration_id;
									}
									$attendees_group = "Group $group_counter";

									//Build the seating assignment
									$seatingchart_tag = '';
									if (defined("ESPRESSO_SEATING_CHART")) {
										if (class_exists("seating_chart")) {
											if (seating_chart::check_event_has_seating_chart($event_id)) {
												$rs = $wpdb->get_row("select scs.* from " . EVENTS_SEATING_CHART_EVENT_SEAT_TABLE . " sces inner join " . EVENTS_SEATING_CHART_SEAT_TABLE . " scs on sces.seat_id = scs.id where sces.attendee_id = " . $participant->att_id);
												if ($rs !== NULL) {
													$participant->seatingchart_tag = $rs->custom_tag . " " . $rs->seat . " " . $rs->row;
												}
											}
										}
									} else {
										$participant->seatingchart_tag = '';
									}
									
									if(!empty($participant->date_scanned)) {
										$scanned_date_object = DateTime::createFromFormat('Y-m-d H:i:s', $participant->date_scanned);
										$scanned_date = $scanned_date_object->format(get_option('date_format') . ' ' . get_option('time_format'));
									} else {
										$scanned_date = "";
									}

									echo $attendees_group
									. $s . escape_csv_val($participant->att_id)
									. $s . escape_csv_val($participant->registration_id)
									. $s . escape_csv_val(stripslashes($participant->payment))
									. $s . escape_csv_val(stripslashes(event_date_display($participant->date, get_option('date_format'))))
									. $s . escape_csv_val(stripslashes($participant->payment_status))
									. $s . escape_csv_val(stripslashes($participant->txn_type))
									. $s . escape_csv_val(stripslashes($participant->txn_id))
									. $s . escape_csv_val($participant->a_final_price * $participant->a_quantity)
									. $s . escape_csv_val($participant->coupon_code)
									. $s . escape_csv_val($participant->quantity)
									. $s . escape_csv_val($participant->amount_pd)
									. $s . escape_csv_val(event_date_display($participant->payment_date, get_option('date_format')))
									. $s . escape_csv_val($participant->event_name)
									. $s . escape_csv_val($participant->price_option)
									. $s . escape_csv_val(event_date_display($participant->start_date, get_option('date_format')))
									. $s . escape_csv_val(event_date_display($participant->event_time, get_option('time_format')))
									. $s . escape_csv_val($participant->checked_in ? "Yes" : "No")
									. $s . escape_csv_val($participant->checked_in_quantity)
									. $s . escape_csv_val($scanned_date)
									. $s . escape_csv_val($participant->seatingchart_tag)
									. $s . escape_csv_val($participant->fname)
									. $s . escape_csv_val($participant->lname)
									. $s . escape_csv_val($participant->email)
									;


									$SQL = "SELECT question_id, answer FROM " . EVENTS_ANSWER_TABLE . " ";
									$SQL .= "WHERE question_id IN ($question_filter) AND attendee_id = %d";

									$answers = $wpdb->get_results($wpdb->prepare($SQL, $participant->att_id), OBJECT_K);

									foreach ($question_list as $k => $v) {

										// in case the event organizer removes a question from a question group,
										//  the orphaned answers will remian in the answers table.  This check will make sure they don't get exported.

										$search = array("\r", "\n", "\t");
										if (isset($answers[$k])) {
											$clean_answer = str_replace($search, " ", $answers[$k]->answer);
											$clean_answer = stripslashes(str_replace("&#039;", "'", trim($clean_answer)));
											$clean_answer = escape_csv_val($clean_answer);
											echo $s . $clean_answer;
										} else {
											echo $s;
										}
									}

									switch ($_REQUEST['type']) {
										case "csv" :
											echo "\r\n";
											break;
										default :
											echo $et . "\r\n";
											break;
									}
								}
							} else {
								echo __('No participant data has been collected.', 'event_espresso');
							}
							exit;
							break;

						default:
							echo '<p>' . __('This Is Not A Valid Selection!', 'event_espresso') . '</p>';
							break;
					}

				default:
					break;
			}
		}
	}

}
