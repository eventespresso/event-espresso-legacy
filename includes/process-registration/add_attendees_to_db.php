<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	

	
if ( ! function_exists( 'event_espresso_add_attendees_to_db' )) {
	//This entire function can be overridden using the "Custom Files" addon
	function event_espresso_add_attendees_to_db( $event_id = NULL, $session_vars = NULL, $skip_check = FALSE ) {
	
		//echo '<h3>'. __CLASS__ . '->' . __FUNCTION__ . ' <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		

		global $wpdb, $org_options, $espresso_premium;
		
		// check if user has already hit this page before ( ie: going back n forth thru reg process )
		if ( is_null( $session_vars )) {
			$SQL = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE attendee_session=%s";
			$prev_sessions = $wpdb->get_col( $wpdb->prepare( $SQL, $_SESSION['espresso_session']['id'] ));
			
			if ( ! empty( $prev_sessions )) {
				$_SESSION['espresso_session']['id'] = array();
				ee_init_session();
			}
		}
		
		$data_source = $_POST;
		$att_data_source = $_POST;
		$multi_reg = FALSE;
		$notifications = array( 'coupons' => '', 'groupons' => '' );

		
		if ( ! is_null($event_id) && ! is_null($session_vars)) {
			//event details, ie qty, price, start..
			$data_source = $session_vars['data']; 
			//event attendee info ie name, questions....
			$att_data_source = $session_vars['event_attendees']; 
			$multi_reg = TRUE;
		} else {
			$event_id = absint( $data_source['event_id'] );
		}
		
		$data_source['reg_form_nonce'] = isset( $_POST['reg_form_nonce'] ) && !empty( $_POST['reg_form_nonce'] ) ? $_POST['reg_form_nonce'] : NULL;
	
		wp_verify_nonce( $data_source['reg_form_nonce'] );
		
		$skip_check = $skip_check || isset( $data_source['admin'] ) ? TRUE : FALSE;
			
		if ( espresso_verify_recaptcha( $skip_check )) {

			array_walk_recursive($data_source, 'wp_strip_all_tags');
			array_walk_recursive($att_data_source, 'wp_strip_all_tags');

			array_walk_recursive($data_source, 'espresso_apply_htmlentities');
			array_walk_recursive($att_data_source, 'espresso_apply_htmlentities');

			// Will be used for multi events to keep track of evant id change in the loop, for recording event total cost for each group
			static $temp_event_id = ''; 
			//using this var to keep track of the first attendee
			static $attendee_number = 1; 
			static $loop_number = 1;
			static $total_cost = 0;
			static $primary_att_id = NULL;
		
//		echo '<h1>$attendee_number : ' . $attendee_number . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h1>';
//		printr( $data_source, '$data_source  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );			
//		printr( $att_data_source, '$att_data_source  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
	

			if ($temp_event_id == '' || $temp_event_id != $event_id) {
				$temp_event_id = $event_id;
				$event_change = 1;
			} else {
				$event_change = 0;
			}
			
			$event_cost = isset($data_source['cost']) && $data_source['cost'] != '' ? $data_source['cost'] : 0.00;
			$final_price = $event_cost;

			$fname = isset($att_data_source['fname']) ? html_entity_decode( trim( $att_data_source['fname'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$lname = isset($att_data_source['lname']) ? html_entity_decode( trim( $att_data_source['lname'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$address = isset($att_data_source['address']) ? html_entity_decode( trim( $att_data_source['address'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$address2 = isset($att_data_source['address2']) ? html_entity_decode( trim( $att_data_source['address2'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$city = isset($att_data_source['city']) ? html_entity_decode( trim( $att_data_source['city'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$state = isset($att_data_source['state']) ? html_entity_decode( trim( $att_data_source['state'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$zip = isset($att_data_source['zip']) ? html_entity_decode( trim( $att_data_source['zip'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$phone = isset($att_data_source['phone']) ? html_entity_decode( trim( $att_data_source['phone'] ), ENT_QUOTES, 'UTF-8' ) : '';
			$email = isset($att_data_source['email']) ? html_entity_decode( trim( $att_data_source['email'] ), ENT_QUOTES, 'UTF-8' ) : '';


			$SQL = "SELECT question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE . " WHERE id = %d";
			$questions = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ));
			$event_meta = maybe_unserialize( $questions->event_meta );
			$questions = maybe_unserialize( $questions->question_groups );

			// Adding attenddee specific cost to events_attendee table
			if (isset($data_source['admin'])) {
			
				$attendee_quantity = 1;
				$final_price = $data_source['event_cost'];
				$orig_price = $data_source['event_cost'];
				$price_type =  __('Admin', 'event_espresso');		
				//echo '<h4>$orig_price : ' . $orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				//echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				
			} elseif (isset($data_source['seat_id'])) {
			
				// Added for seating chart add-on
				// If a seat was selected then price of that seating will be used instead of event price
				$final_price = seating_chart::get_purchase_price($data_source['seat_id']);
				$orig_price = $data_source['cost'];
				//echo '<h4>$orig_price : ' . $orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				//echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					
			} elseif ( isset( $att_data_source['price_id'] ) && ! empty( $att_data_source['price_id'] )) {
			
				if ( $att_data_source['price_id'] == 'free' ) {
					$orig_price = 0.00;
					$final_price = 0.00;
					$price_type =  __('Free Event', 'event_espresso');		
				} else {
					$orig_price = event_espresso_get_orig_price_and_surcharge( $att_data_source['price_id'] );
					$final_price = isset( $att_data_source['price_id'] ) ? event_espresso_get_final_price( $att_data_source['price_id'], $event_id, $orig_price ) : 0.00;
					$price_type = isset( $att_data_source['price_id'] ) ? espresso_ticket_information( array( 'type' => 'ticket', 'price_option' => $att_data_source['price_id'] )) : '';
					$surcharge = event_espresso_calculate_surcharge( $orig_price->event_cost , $orig_price->surcharge, $orig_price->surcharge_type );
					$orig_price = (float)number_format( $orig_price->event_cost + $surcharge, 2, '.', '' ); 			
//					echo '<h4>$orig_price : ' . $orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//					echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				}
				
			} elseif ( isset( $data_source['price_select'] ) && $data_source['price_select'] == TRUE ) {
				
				//Figure out if the person has registered using a price selection
				$price_options = explode( '|', $data_source['price_option'], 2 );
				$price_id = $price_options[0];
				$price_type = $price_options[1];
				$orig_price = event_espresso_get_orig_price_and_surcharge( $price_id );
				$final_price = event_espresso_get_final_price( $price_id, $event_id, $orig_price );
				$surcharge = event_espresso_calculate_surcharge( $orig_price->event_cost , $orig_price->surcharge, $orig_price->surcharge_type );
				$orig_price = (float)number_format( $orig_price->event_cost + $surcharge, 2, '.', '' ); 
				//echo '<h4>$orig_price : ' . $orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				//echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				
			} else {
			
				if ( $data_source['price_id'] == 'free' ) {
					$orig_price = 0.00;
					$final_price = 0.00;
					$price_type =  __('Free Event', 'event_espresso');		
				} else {
					$orig_price = event_espresso_get_orig_price_and_surcharge( $data_source['price_id'] );
					$final_price = isset( $data_source['price_id'] ) ? event_espresso_get_final_price( $data_source['price_id'], $event_id, $orig_price ) : 0.00;
					$price_type = isset($data_source['price_id']) ? espresso_ticket_information(array('type' => 'ticket', 'price_option' => $data_source['price_id'])) : '';
					$surcharge = event_espresso_calculate_surcharge( $orig_price->event_cost , $orig_price->surcharge, $orig_price->surcharge_type );
					$orig_price = (float)number_format( $orig_price->event_cost + $surcharge, 2, '.', '' ); 
					//echo '<h4>$orig_price : ' . $orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					//echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';					
				}
			
			}

			$final_price = apply_filters( 'filter_hook_espresso_attendee_cost', $final_price );
			$attendee_quantity = isset( $data_source['num_people'] ) ? $data_source['num_people'] : 1;
			$coupon_code = '';

//echo '<h4>$orig_price : ' . $orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

			if ($multi_reg) {			
				$event_cost = $_SESSION['espresso_session']['grand_total'];
				//$coupon_code = $attendee_number == 1 ? $_SESSION['espresso_session']['event_espresso_coupon_code'] : '';				
			} 
			
			do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'line '. __LINE__ .' : attendee_cost=' . $final_price);

			$event_cost = apply_filters( 'filter_hook_espresso_cart_grand_total', $event_cost ); 
//			$amount_pd = $attendee_number == 1 ? $event_cost : 0.00;
			$amount_pd = 0.00;


			//Check to see if the registration id already exists
			$incomplete_filter = ! $multi_reg ? " AND payment_status ='Incomplete'" : '';
			$SQL = "SELECT attendee_session, id, registration_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE attendee_session =%s AND event_id = %d";
			$SQL .= $incomplete_filter;
			
			$check_sql = $wpdb->get_results($wpdb->prepare( $SQL, $_SESSION['espresso_session']['id'], $event_id ));
			$nmbr_of_regs = $wpdb->num_rows;

			//Check if the registration id has been created previously.
			$registration_id = empty($wpdb->last_result[0]->registration_id) ? apply_filters('filter_hook_espresso_registration_id', $event_id) : $wpdb->last_result[0]->registration_id;

			$txn_type = "";

			if (isset($data_source['admin'])) {	
					
				$payment_status = "Completed";
				$payment = "Admin";
				$txn_type = __('Added by Admin', 'event_espresso');
				//$payment_date = date("m-d-Y");
				$payment_date = date(get_option('date_format'));
				$amount_pd = $data_source['event_cost'] != '' ? $data_source['event_cost'] : 0.00;
				$registration_id = uniqid('', true);
				$_SESSION['espresso_session']['id'] = uniqid('', true);

				
			} else {

				//print_r( $event_meta);
				$default_payment_status = $event_meta['default_payment_status'] != '' ? $event_meta['default_payment_status'] : $org_options['default_payment_status'];
				$payment_status = ( $multi_reg && $data_source['cost'] == 0.00 ) ? "Completed" : $default_payment_status;
				$payment = '';
				
			}

			$times_sql = "SELECT ese.start_time, ese.end_time, e.start_date, e.end_date ";
			$times_sql .= "FROM " . EVENTS_START_END_TABLE . " ese ";
			$times_sql .= "LEFT JOIN " . EVENTS_DETAIL_TABLE . " e ON ese.event_id = e.id WHERE ";
			$times_sql .= "e.id=%d";
			if (!empty($data_source['start_time_id'])) {
				$times_sql .= " AND ese.id=" . $data_source['start_time_id'];
			}

			$times = $wpdb->get_results($wpdb->prepare( $times_sql, $event_id ));
			foreach ($times as $time) {
				$start_time = $time->start_time;
				$end_time = $time->end_time;
				$start_date = $time->start_date;
				$end_date = $time->end_date;
			}


			//If we are using the number of attendees dropdown, add that number to the DB
			//echo $data_source['espresso_addtl_limit_dd'];
			if (isset($data_source['espresso_addtl_limit_dd'])) {
				$num_people = $data_source ['num_people'];
			} elseif (isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == 1) {
				$num_people = $data_source ['num_people'];
			} else {
				$num_people = 1;
			}

			
			// check for coupon 
			if ( function_exists( 'event_espresso_process_coupon' )) {
				if ( $coupon_results = event_espresso_process_coupon( $event_id, $final_price, $multi_reg )) {
					//printr( $coupon_results, '$coupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
					if ( $coupon_results['valid'] ) {
						$final_price = number_format( $coupon_results['event_cost'], 2, '.', '' );
						$coupon_code = $coupon_results['code'];
					}
					if ( ! $multi_reg && ! empty( $coupon_results['msg'] )) {
						$notifications['coupons'] = $coupon_results['msg'];
					}
				}					
			} 

			// check for groupon 
			if ( function_exists( 'event_espresso_process_groupon' )) {
				if ( $groupon_results = event_espresso_process_groupon( $event_id, $final_price, $multi_reg )) {
					//printr( $groupon_results, '$groupon_results  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
					if ( $groupon_results['valid'] ) {
						$final_price = number_format( $groupon_results['event_cost'], 2, '.', '' );
						$coupon_code = $groupon_results['code'];
					}
					if ( ! $multi_reg && ! empty( $groupon_results['msg'] )) {
						$notifications['groupons'] = $groupon_results['msg'];
					}
				}					
			} 
			
			// check for groupon 
//			$use_groupon =  isset( $data_source['use_groupon'][ $event_id ] ) && function_exists( 'espresso_apply_goupon_to_attendee' ) ? $data_source['use_groupon'][ $event_id ] == 'Y' : 'N';
//			if ( $use_groupon && isset( $data_source['groupon'] )) {	
//				if ( $new_att_price_data = espresso_apply_goupon_to_attendee( $event_id, $final_price, $data_source['groupon'] )) {
//					extract( $new_att_price_data );
//					$data_source['groupon'] = $groupon;
//				}			
//			}

			$start_time = empty($start_time) ? '' : $start_time;
			$end_time = empty($end_time) ? '' : $end_time;
			$start_date = empty($start_date) ? '' : $start_date;
			$end_date = empty($end_date) ? '' : $end_date;
			$organization_name = empty($organization_name) ? '' : $organization_name;
			$country_id = empty($country_id) ? '' : $country_id;
			$payment_date = empty($payment_date) ? '' : $payment_date;
			$coupon_code = empty($coupon_code) ? '' : $coupon_code;

			$amount_pd = number_format( (float)$amount_pd, 2, '.', '' );
			$orig_price = number_format( (float)$orig_price, 2, '.', '' );
			$final_price = number_format( (float)$final_price, 2, '.', '' );
			$total_cost = $total_cost + $final_price;
			
//echo '<h4>$amount_pd : ' . $amount_pd . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//echo '<h4>$orig_price : ' . $orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//echo '<h4>$final_price : ' . $final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//echo '<h4>$total_cost : ' . $total_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4><br /><br /><br />';
//echo '<h4>$payment_status : ' . $payment_status . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4><br /><br /><br />';

			$columns_and_values = array(
					'registration_id' => $registration_id,
					'is_primary' => $attendee_number == 1 ? TRUE : FALSE,
					'attendee_session' => $_SESSION['espresso_session']['id'],
					'lname' => $lname,
					'fname' => $fname,
					'address' => $address,
					'address2' => $address2,
					'city' => $city,
					'state' => $state,
					'zip' => $zip,
					'email' => $email,
					'phone' => $phone,
					'payment' => $payment,
					'txn_type' => $txn_type,
					'coupon_code' => $coupon_code,
					'event_time' => $start_time,
					'end_time' => $end_time,
					'start_date' => $start_date,
					'end_date' => $end_date,
					'price_option' => $price_type,
					'organization_name' => $organization_name,
					'country_id' => $country_id,
					'payment_status' => $payment_status,
					'payment_date' => $payment_date,
					'event_id' => $event_id,
					'quantity' => (int)$num_people,
					'amount_pd' => $amount_pd,
					'orig_price' => $orig_price,
					'final_price' => $final_price
			);
			
//			printr( $columns_and_values, '$columns_and_values  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

			$data_formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f' );
			
			// delete previous entries from this session in case user is jumping back n forth between pages during the reg process
			if ( $nmbr_of_regs > 0 && $loop_number == 1 ) {
				if ( ! isset( $data_source['admin'] )) {
				
					//Added for seating chart addon
					$tmp_session = wp_strip_all_tags( $_SESSION['espresso_session']['id'] );

					$SQL = "SELECT id, registration_id FROM " . EVENTS_ATTENDEE_TABLE . ' ';
					$SQL .= "WHERE attendee_session = %s ";
					$SQL .= $incomplete_filter;
					
					if ( $rem_attendee_ids = $wpdb->get_results($wpdb->prepare( $SQL, $tmp_session ))) {
						foreach ( $rem_attendee_ids as $v ) {
							if ( defined('ESPRESSO_SEATING_CHART')) {						
								$SQL = "DELETE FROM " . EVENTS_SEATING_CHART_EVENT_SEAT_TABLE . ' ';
								$SQL .= "WHERE attendee_id = %d";
								$wpdb->query($wpdb->prepare( $SQL, $v->id ));
							}
						}						
					}

					$SQL = "DELETE t1, t2 FROM " . EVENTS_ATTENDEE_TABLE . "  t1 ";
					$SQL .= "JOIN  " . EVENTS_ANSWER_TABLE . " t2 on t1.id = t2.attendee_id ";
					$SQL .= "WHERE t1.attendee_session = %s ";
					$SQL .= $incomplete_filter;
					$wpdb->query($wpdb->prepare( $SQL, $tmp_session ));
					
				}

				//Added by Imon
				// First delete attempt might fail if there is no data in answer table. So, second attempt without joining answer table is taken bellow -
				$SQL = " DELETE FROM " . EVENTS_ATTENDEE_TABLE . ' ';
				$SQL .= "WHERE attendee_session = %s ";
				$SQL .= $incomplete_filter;
				$wpdb->query($wpdb->prepare( $SQL, $tmp_session ));

				// Clean up any attendee information from attendee_cost table where attendee is not available in attendee table
				event_espresso_cleanup_multi_event_registration_id_group_data();

			}
			$loop_number++;

			// save the attendee details - FINALLY !!!
			if ( ! $wpdb->insert( EVENTS_ATTENDEE_TABLE, $columns_and_values, $data_formats )) {
				$error = true;
			}

			$attendee_id = $wpdb->insert_id;
			
			// save attendee id for the primary attendee
			$primary_att_id = $attendee_number == 1 ? $attendee_id : FALSE;


			// Added for seating chart addon
			$booking_id = 0;
			if (defined('ESPRESSO_SEATING_CHART')) {
				if (seating_chart::check_event_has_seating_chart($event_id) !== false) {
					if (isset($_POST['seat_id'])) {
						$booking_id = seating_chart::parse_booking_info($_POST['seat_id']);
						if ($booking_id > 0) {
							seating_chart::confirm_a_seat($booking_id, $attendee_id);
						}
					}
				}
			}
			
			//Add a record for the primary attendee
			if ( $attendee_number == 1 ) {
				
				$columns_and_values = array('attendee_id' => $primary_att_id, 'meta_key' => 'primary_attendee', 'meta_value' => 1);
				$data_formats = array('%s', '%s', '%s');
			
				if ( !$wpdb->insert(EVENTS_ATTENDEE_META_TABLE, $columns_and_values, $data_formats) ) {
					$error = true;
				}

			}


			if (defined('EVENTS_MAILCHIMP_ATTENDEE_REL_TABLE') && $espresso_premium == true) {
				MailChimpController::list_subscribe($event_id, $attendee_id, $fname, $lname, $email);
			}

			//Defining the $base_questions variable in case there are no additional attendee questions
			$base_questions = $questions;

			//Since main attendee and additional attendees may have different questions,
			//$attendee_number check for 2 because is it statically set at 1 first and is incremented for the primary attendee above, hence 2
			$questions = ( $attendee_number > 1 && isset($event_meta['add_attendee_question_groups'])) ? $event_meta['add_attendee_question_groups'] : $questions;

			add_attendee_questions( $questions, $registration_id, $attendee_id, array( 'session_vars' => $att_data_source ));

			//Add additional attendees to the database
			if ($event_meta['additional_attendee_reg_info'] > 1) {
			
				$questions = $event_meta['add_attendee_question_groups'];

				if (empty($questions)) {
					$questions = $base_questions;
				}


				if ( isset( $att_data_source['x_attendee_fname'] )) {
					foreach ( $att_data_source['x_attendee_fname'] as $k => $v ) {
					
						if ( trim($v) != '' && trim( $att_data_source['x_attendee_lname'][$k] ) != '' ) {

							// Added for seating chart addon
							$seat_check = true;
							$x_booking_id = 0;
							if ( defined('ESPRESSO_SEATING_CHART')) {
								if (seating_chart::check_event_has_seating_chart($event_id) !== false) {
									if (!isset($att_data_source['x_seat_id'][$k]) || trim($att_data_source['x_seat_id'][$k]) == '') {
										$seat_check = false;
									} else {
										$x_booking_id = seating_chart::parse_booking_info($att_data_source['x_seat_id'][$k]);
										if ($x_booking_id > 0) {
											$seat_check = true;
										} else {
											$seat_check = false; //Keeps the system from adding an additional attndee if no seat is selected
										}
									}
								}
							}
							
							if ($seat_check) {

								$data = array(
										'registration_id' => $registration_id,
										'attendee_session' => $_SESSION['espresso_session']['id'],
										'lname' => $att_data_source['x_attendee_lname'][$k],
										'fname' => $v,
										'email' => $att_data_source['x_attendee_email'][$k],
										'address' => empty($att_data_source['x_attendee_address'][$k]) ? '' : $att_data_source['x_attendee_address'][$k],
										'address2' => empty($att_data_source['x_attendee_address2'][$k]) ? '' : $att_data_source['x_attendee_address2'][$k],
										'city' => empty($att_data_source['x_attendee_city'][$k]) ? '' : $att_data_source['x_attendee_city'][$k],
										'state' => empty($att_data_source['x_attendee_state'][$k]) ? '' : $att_data_source['x_attendee_state'][$k],
										'zip' => empty($att_data_source['x_attendee_zip'][$k]) ? '' : $att_data_source['x_attendee_zip'][$k],
										'phone' => empty($att_data_source['x_attendee_phone'][$k]) ? '' : $att_data_source['x_attendee_phone'][$k],
										'payment' => $payment,
										'event_time' => $start_time,
										'end_time' => $end_time,
										'start_date' => $start_date,
										'end_date' => $end_date,
										'price_option' => $price_type,
										'organization_name' => $organization_name,
										'country_id' => $country_id,
										'payment_status' => $payment_status,
										'payment_date' => $payment_date,
										'event_id' => $event_id,
										'quantity' => (int)$num_people,
										'amount_pd' => (float)0.00,
										'orig_price' => $orig_price,
										'final_price' => $final_price										
								);
								
								$format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f' );
								$wpdb->insert( EVENTS_ATTENDEE_TABLE, $data, $format );
								
								//Added by Imon
								$ext_attendee_id = $wpdb->insert_id;
								$mailchimp_attendee_id = $ext_attendee_id;

								if (defined('EVENTS_MAILCHIMP_ATTENDEE_REL_TABLE') && $espresso_premium == true) {
									MailChimpController::list_subscribe($event_id, $mailchimp_attendee_id, $v, $att_data_source['x_attendee_lname'][$k], $att_data_source['x_attendee_email'][$k]);
								}
								
								//Added by Imon
								$ext_att_data_source = array(
										'registration_id' => $registration_id,
										'attendee_session' => $_SESSION['espresso_session']['id'],
										'lname' => $att_data_source['x_attendee_lname'][$k],
										'fname' => $v,
										'email' => $att_data_source['x_attendee_email'][$k],
										'address' => empty($att_data_source['x_attendee_address'][$k]) ? '' : $att_data_source['x_attendee_address'][$k],
										'address2' => empty($att_data_source['x_attendee_address2'][$k]) ? '' : $att_data_source['x_attendee_address2'][$k],
										'city' => empty($att_data_source['x_attendee_city'][$k]) ? '' : $att_data_source['x_attendee_city'][$k],
										'state' => empty($att_data_source['x_attendee_state'][$k]) ? '' : $att_data_source['x_attendee_state'][$k],
										'zip' => empty($att_data_source['x_attendee_zip'][$k]) ? '' : $att_data_source['x_attendee_zip'][$k],
										'phone' => empty($att_data_source['x_attendee_phone'][$k]) ? '' : $att_data_source['x_attendee_phone'][$k],
										'payment' => $payment,
										'amount_pd' => (float)0.00,
										'event_time' => $start_time,
										'end_time' => $end_time,
										'start_date' => $start_date,
										'end_date' => $end_date,
										'price_option' => $price_type,
										'organization_name' => $organization_name,
										'country_id' => $country_id,
										'payment_status' => $payment_status,
										'payment_date' => $payment_date,
										'event_id' => $event_id,
										'quantity' => (int)$num_people
								);
								
								if ( ! is_array($questions) && !empty($questions)) {
									$questions = unserialize($questions);
								}

								$questions_in = '';
								foreach ($questions as $g_id) {
									$questions_in .= $g_id . ',';
								}
								$questions_in = substr($questions_in, 0, -1);

								$SQL = "SELECT q.*, qg.group_name FROM " . EVENTS_QUESTION_TABLE . " q ";
								$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
								$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
								$SQL .= "WHERE qgr.group_id in ( $questions_in ) ";
								$SQL .= "ORDER BY q.id ASC";
								
								$questions_list = $wpdb->get_results($wpdb->prepare( $SQL, NULL ));
								foreach ($questions_list as $question_list) {
									if ($question_list->system_name != '') {
										$ext_att_data_source[$question_list->system_name] = $att_data_source['x_attendee_' . $question_list->system_name][$k];
									} else {
										$ext_att_data_source[$question_list->question_type . '_' . $question_list->id] = $att_data_source['x_attendee_' . $question_list->question_type . '_' . $question_list->id][$k];
									}
								}

								echo add_attendee_questions($questions, $registration_id, $ext_attendee_id, array('session_vars' => $ext_att_data_source));

								
							}
							
							// Added for seating chart addon
							if (defined('ESPRESSO_SEATING_CHART')) {
								if (seating_chart::check_event_has_seating_chart($event_id) !== false && $x_booking_id > 0) {
									seating_chart::confirm_a_seat($x_booking_id, $ext_attendee_id);
								}
							}
						}
					}
				}
			}


			//Add member data if needed
			if (defined('EVENTS_MEMBER_REL_TABLE')) {
				require_once(EVENT_ESPRESSO_MEMBERS_DIR . "member_functions.php"); //Load Members functions
				require(EVENT_ESPRESSO_MEMBERS_DIR . "user_vars.php"); //Load Members functions
				if ($userid != 0) {
					event_espresso_add_user_to_event( $event_id, $userid, $attendee_id );
				}
			}

			$attendee_number++;

			if (isset($data_source['admin'])) {
				return $attendee_id;
			}
			

			//This shows the payment page
			if ( ! $multi_reg) {
				return events_payment_page( $attendee_id, $notifications ); 
			}
			
			return array( 'registration_id' => $registration_id, 'notifications' => $notifications );
						
		}		
	}
}



function espresso_update_primary_attendee_total_cost( $attendee_id, $total_cost, $source ) {
	
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, array( '$total_cost' => $total_cost ));		
	global $wpdb;
	
	$set_cols_and_values = array( 'total_cost'=>number_format( (float)$total_cost, 2, '.', '' ));
	$set_format = array( '%f' );
	$where_cols_and_values = array( 'id'=> $attendee_id );
	$where_format = array( '%d' );		
	
	if ( $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format  ) === FALSE ) {
		wp_die( __('An error occured. The primary attende\'s data could not be updated.' . "\n( " . basename( $source ) . ' )', 'event_espresso'));
	}				
				
}



//    @                   @     @@@@@      @@@@
//    @@           @ @     @                     @          @
//    @    @    @    @     @@@             @@@@
//    @        @        @     @                      @        @
//    @                    @     @@@@@     @           @



if ( ! function_exists('event_espresso_add_attendees_to_db_multi')) {
	//This function is called from the shopping cart
	function event_espresso_add_attendees_to_db_multi() {
	
		//echo '<h3>'. __CLASS__ . '->' . __FUNCTION__ . ' <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
		
		global $wpdb, $org_options;
		
		if ( espresso_verify_recaptcha() ) {

			$primary_registration_id = NULL;
			$multi_reg = true;

			$events_in_session = $_SESSION['espresso_session']['events_in_session'];
			if (event_espresso_invoke_cart_error($events_in_session)) {
				return false;
			}				

			$count_of_events = count($events_in_session);
			$current_session_id = $_SESSION['espresso_session']['id'];
			$biz_name = $count_of_events . ' ' . $org_options['organization'] . __(' events', 'event_espresso');
			$event_cost = $_SESSION['espresso_session']['grand_total'];
			$event_cost = apply_filters('filter_hook_espresso_cart_grand_total', $event_cost);

			// If there are events in the session, add them one by one to the attendee table
			if ($count_of_events > 0) {
			
				//first event key will be used to find the first attendee
				$first_event_id = key($events_in_session);

				reset($events_in_session);
				foreach ($events_in_session as $event_id => $event) {

					$event_meta = event_espresso_get_event_meta($event_id);
					$session_vars['data'] = $event;

					if ( is_array( $event['event_attendees'] )) {
					
						$counter = 1;
						//foreach price type in event attendees
						foreach ( $event['event_attendees'] as $price_id => $event_attendees ) { 
						
							$session_vars['data'] = $event;

							foreach ( $event_attendees as $attendee) {

								$attendee['price_id'] = $price_id;
								//this has all the attendee information, name, questions....
								$session_vars['event_attendees'] = $attendee; 
								$session_vars['data']['price_type'] = $event['price_id'][$price_id]['price_type'];
								if ( isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == 1 ) {

									$num_people = (int)$event['price_id'][$price_id]['attendee_quantity'];
									$session_vars['data']['num_people'] = empty($num_people) || $num_people == 0 ? 1 : $num_people;

								}

								// ADD ATTENDEE TO DB
								$return_data = event_espresso_add_attendees_to_db( $event_id, $session_vars, TRUE );
								
								$tmp_registration_id = $return_data['registration_id'];
								$notifications = $return_data['notifications'];

								if ($primary_registration_id === NULL) {
									$primary_registration_id = $tmp_registration_id;
								}

								$SQL = "SELECT * FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . "  ";
								$SQL .= "WHERE primary_registration_id = %s AND registration_id = %s";
								$check = $wpdb->get_row( $wpdb->prepare( $SQL, $primary_registration_id, $tmp_registration_id ));
								
								if ( $check === NULL) {
									$tmp_data = array( 'primary_registration_id' => $primary_registration_id, 'registration_id' => $tmp_registration_id );
									$wpdb->insert( EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE, $tmp_data, array( '%s', '%s' ));
								}
							$counter++;

							}
						}
					}
				}
				

				$SQL = "SELECT a.*, ed.id AS event_id, ed.event_name, dc.coupon_code_price, dc.use_percentage ";
				$SQL .= "FROM " . EVENTS_ATTENDEE_TABLE . " a JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
				$SQL .= "LEFT JOIN " . EVENTS_DISCOUNT_CODES_TABLE . " dc ON a.coupon_code=dc.coupon_code ";
				$SQL .= "WHERE attendee_session=%s ORDER BY a.id ASC";
				
				$attendees = $wpdb->get_results( $wpdb->prepare( $SQL, $current_session_id ));
				//printr( $attendees, '$attendees  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				
				$quantity = 0;
				$final_total = 0;
				$sub_total = 0;
				$discounted_total = 0;
				$discount_amount = 0;
				//$coupon_amount = ! empty($attendees[0]->coupon_code_price) ? $attendees[0]->coupon_code_price : 0;
				$is_coupon_pct = ! empty( $attendees[0]->use_percentage ) && $attendees[0]->use_percentage == 'Y' ? TRUE : FALSE;
				
				//printr( $attendees, '$attendees  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				foreach ($attendees as $attendee) {
				
					if ( $attendee->is_primary ) {
						$primary_attendee_id = $attendee_id = $attendee->id;
						$coupon_code = $attendee->coupon_code;
						$event_id = $attendee->event_id;
						$fname = $attendee->fname;
						$lname = $attendee->lname;
						$address = $attendee->address;
						$city = $attendee->city;
						$state = $attendee->state;
						$zip = $attendee->zip;
						$attendee_email = $attendee->email;
						$registration_id = $attendee->registration_id;
					}
					$final_total += $attendee->final_price;
					$sub_total += (int)$attendee->quantity * $attendee->orig_price;
					$discounted_total += (int)$attendee->quantity * $attendee->final_price;
					$quantity += (int)$attendee->quantity;

					//echo '<h2>$attendee->id : ' . $attendee->id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h2>';
					//echo '<h4>$attendee->orig_price : ' . $attendee->orig_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					//echo '<h4>$attendee->final_price : ' . $attendee->final_price . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					//echo '<h4>$attendee->quantity : ' . (int)$attendee->quantity . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					//echo '<h4>$sub_total : ' . $sub_total . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
					//echo '<h4>$discounted_total : ' . $discounted_total . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

				}
				$discount_amount = $sub_total - $discounted_total;
				$total_cost = $discounted_total;
				
//echo '<h4>$discount_amount : ' . $discount_amount . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//echo '<h4>$total_cost : ' . $total_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//echo '<h4>$final_total : ' . $final_total . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4><br/>';
								
				$total_cost = $total_cost < 0 ? 0.00 : (float)$total_cost;
				
				if ( function_exists( 'espresso_update_attendee_coupon_info' ) && $primary_attendee_id && ! empty( $attendee->coupon_code )) {
					espresso_update_attendee_coupon_info( $primary_attendee_id, $attendee->coupon_code  );
				} 	
					
				if ( function_exists( 'espresso_update_groupon' ) && $primary_attendee_id && ! empty( $coupon_code )) {
					espresso_update_groupon( $primary_attendee_id, $coupon_code  );
				} 

				espresso_update_primary_attendee_total_cost( $primary_attendee_id, $total_cost, __FILE__ );
				//echo '<h4>$total_cost : ' . $total_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';


				if ( ! empty( $notifications['coupons'] ) || ! empty( $notifications['groupons'] )) {
					echo '<div id="event_espresso_notifications" class="clearfix event-data-display no-hide">';
					echo $notifications['coupons'];
					// add space between $coupon_notifications and  $groupon_notifications ( if any $groupon_notifications exist )
					echo ! empty( $notifications['coupons'] ) && ! empty( $notifications['groupons'] ) ? '<br/>' : '';
					echo $notifications['groupons'];
					echo '</div>';	
				}						
				
				//Post the gateway page with the payment options
				if ( $total_cost > 0 ) {
?>

<div class="espresso_payment_overview event-display-boxes ui-widget" >
  <h3 class="section-heading ui-widget-header ui-corner-top">
		<?php _e('Payment Overview', 'event_espresso'); ?>
  </h3>
	<div class="event-data-display ui-widget-content ui-corner-bottom" >

		<div class="event-messages ui-state-highlight"> <span class="ui-icon ui-icon-alert"></span>
			<p class="instruct">
				<?php _e('Your registration is not complete until payment is received.', 'event_espresso'); ?>
			</p>
		</div>
		<p><?php echo $org_options['email_before_payment'] == 'Y' ? __('A confirmation email has been sent with additional details of your registration.', 'event_espresso') : ''; ?></p>
		<table>
			<?php foreach ($attendees as $attendee) { ?>
			<tr>
				<td width="70%">
					<?php echo '<strong>'.stripslashes_deep($attendee->event_name ) . '</strong>'?>&nbsp;-&nbsp;<?php echo stripslashes_deep( $attendee->price_option ) ?> <?php echo $attendee->final_price < $attendee->orig_price ? '<br />&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size:.8em;">' . $org_options['currency_symbol'] . number_format($attendee->orig_price - $attendee->final_price, 2) . __(' discount per registration','event_espresso') . '</span>' : ''; ?><br/>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php echo __('Attendee:','event_espresso') . ' ' . stripslashes_deep($attendee->fname . ' ' . $attendee->lname) ?>
				</td>
				<td width="10%"><?php echo $org_options['currency_symbol'] . number_format($attendee->final_price, 2); ?></td>
				<td width="10%"><?php echo 'x ' . (int)$attendee->quantity ?></td>
				<td width="10%" style="text-align:right;"><?php echo $org_options['currency_symbol'] . number_format( $attendee->final_price * (int)$attendee->quantity, 2) ?></td>
			</tr>
			<?php } ?>
			
			<tr>
				<td colspan="3"><?php _e('Sub-Total:','event_espresso'); ?></td>
				<td colspan="" style="text-align:right"><?php echo $org_options['currency_symbol'] . number_format($sub_total, 2); ?></td>
			</tr>
			<?php
					if (!empty($discount_amount)) {
							?>
			<tr>
				<td colspan="3"><?php _e('Total Discounts:','event_espresso'); ?></td>
				<td colspan="" style="text-align:right"><?php echo '-' . $org_options['currency_symbol'] . number_format( $discount_amount, 2 ); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="3"><strong class="event_espresso_name">
					<?php _e('Total Amount due: ', 'event_espresso'); ?>
					</strong></td>
				<td colspan="" style="text-align:right"><?php echo $org_options['currency_symbol'] ?><?php echo number_format($total_cost,2); ?></td>
			</tr>
		</table>
		<p class="event_espresso_refresh_total">
			<a href="?page_id=<?php echo $org_options['event_page_id']; ?>&regevent_action=show_shopping_cart">
			<?php _e('Edit Cart', 'event_espresso'); ?>
			</a>
			<?php _e(' or ', 'event_espresso'); ?>
			<a href="?page_id=<?php echo $org_options['event_page_id']; ?>&registration_id=<?php echo $registration_id; ?>&id=<?php echo $attendee_id; ?>&regevent_action=edit_attendee&primary=<?php echo $primary_attendee_id; ?>&event_id=<?php echo $event_id; ?>&attendee_num=1">
			<?php _e('Edit Registrant Information', 'event_espresso'); ?>
			</a> 
		</p>
	</div>
</div>
<br/><br/>
<?php
					//Show payment options
					if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php")) {
						require_once(EVENT_ESPRESSO_GATEWAY_DIR . "gateway_display.php");
					} else {
						require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/gateway_display.php");
					}
					//Check to see if the site owner wants to send an confirmation eamil before payment is recieved.
					if ($org_options['email_before_payment'] == 'Y') {
						event_espresso_email_confirmations(array('session_id' => $_SESSION['espresso_session']['id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true', 'multi_reg' => true));
					}
					
				} elseif ( $total_cost == 0.00 ) {
					?>
<p>
	<?php _e('Thank you! Your registration is confirmed for', 'event_espresso'); ?>
	<strong><?php echo stripslashes_deep( $biz_name ) ?></strong></p>
<p>
	<?php _e('A confirmation email has been sent with additional details of your registration.', 'event_espresso'); ?>
</p>
<?php
					event_espresso_email_confirmations(array('session_id' => $_SESSION['espresso_session']['id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true', 'multi_reg' => true));

					event_espresso_clear_session();
				}
			}
			
		}		
		
	}
}







function espresso_verify_recaptcha( $skip_check = FALSE ) {

	//echo '<h3>'. __CLASS__ . '->' . __FUNCTION__ . ' <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h3>';
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
	
	global $org_options;

//	echo '<h4>use_captcha : ' . $org_options['use_captcha'] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>is_user_logged_in : ' . is_user_logged_in() . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>$skip_check : ' . $skip_check . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//
//	echo '<h4>recaptcha_privatekey : ' . $org_options['recaptcha_privatekey'] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>REMOTE_ADDR : ' . $_SERVER["REMOTE_ADDR"] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>recaptcha_challenge_field : ' . $_POST["recaptcha_challenge_field"] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	echo '<h4>recaptcha_response_field : ' . $_POST["recaptcha_response_field"] . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

	if ( $skip_check || is_user_logged_in() || $org_options['use_captcha'] != 'Y' ) {
		return TRUE;
	}	else {
		
		// make sure RC lib is loaded
		if ( ! function_exists('recaptcha_check_answer')) {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/recaptchalib.php');
		}
		
		$recaptcha_privatekey = isset( $org_options["recaptcha_privatekey"] ) ? $org_options["recaptcha_privatekey"] : FALSE;
		$remote_addr = isset( $_SERVER["REMOTE_ADDR"] ) ? $_SERVER["REMOTE_ADDR"] : FALSE;
		$recaptcha_challenge_field = isset( $_POST["recaptcha_challenge_field"] ) ? $_POST["recaptcha_challenge_field"] : FALSE;
		$recaptcha_response_field = isset( $_POST["recaptcha_response_field"] ) ? $_POST["recaptcha_response_field"] : FALSE;
		
		// check private key
		if ( ! $recaptcha_privatekey ) {
			echo '<div class="attention-icon"><p class="event_espresso_attention"><strong>' . __('Sorry, it appears that the ReCaptcha anti-spam settings are not correct. Please contact the site admin or click your browser\'s back button and try again.', 'event_espresso') . '</strong></p></div>';
			return FALSE;			
		}
		
		// check $remote_addr
		if ( ! $remote_addr ) {
			echo '<div class="attention-icon"><p class="event_espresso_attention"><strong>' . __('Sorry, an error occured and the anti-spam settings could not be verified. Please contact the site admin or click your browser\'s back button and try again.', 'event_espresso') . '</strong></p></div>';
			return FALSE;			
		}
		
		// check $recaptcha_challenge_field
		if ( ! $recaptcha_challenge_field ) {
			echo '<div class="attention-icon"><p class="event_espresso_attention"><strong>' . __('Sorry, an error occured and the anti-spam fields could not be verified. Please contact the site admin or click your browser\'s back button and try again.', 'event_espresso') . '</strong></p></div>';
			return FALSE;			
		}
		
		$resp = recaptcha_check_answer( $recaptcha_privatekey, $remote_addr, $recaptcha_challenge_field, $recaptcha_response_field );
//		printr( $resp, '$resp  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

		if ( $resp->is_valid ) {
			return TRUE;
		} else {
			echo '<div class="attention-icon"><p class="event_espresso_attention"><strong>' . __('Sorry, you did not enter the correct anti-spam phrase. Please click your browser\'s back button and try again.', 'event_espresso') . '</strong></p></div>';
			return FALSE;
		}
		
	}
				
}


// function for applying htmlentities via array_walk_recursive()
if (!function_exists('espresso_apply_htmlentities')) {
	function espresso_apply_htmlentities(&$value, $key) {
		$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
	}
}
