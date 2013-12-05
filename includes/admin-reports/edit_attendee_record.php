<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	


function edit_attendee_record() {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');		
	global $wpdb, $org_options;
	$wpdb->show_errors();
	$notifications['success'] = array(); 
	$notifications['error']	 = array(); 
	
	$failed_nonce_msg = '
<div id="message" class="error">
	<p>
		<strong>' . __( 'An Error Occurred. The request failed to pass a security check.', 'event_espresso' ) . '</strong><br/>
		<span style="font-size:.9em;">' . __( 'Please press the back button on your browser to return to the previous page.', 'event_espresso') . '</span>
	</p>
</div>';

	$attendee_num = 1;
	$is_additional_attendee = FALSE;

	// **************************************************************************
	// **************************** EDIT ATTENDEE  ****************************
	// **************************************************************************
	if ($_REQUEST['form_action'] == 'edit_attendee') {

		$id = isset($_REQUEST['id']) ? absint( $_REQUEST['id'] ) : '';
		$registration_id = isset($_REQUEST['registration_id']) ? ee_sanitize_value( $_REQUEST['registration_id'] ) : '';
		$multi_reg = FALSE;
		
		// check for multi reg, additional attendees, and verify reg id for primary attendee
		$SQL = "SELECT * FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " WHERE registration_id = %s";
		$check = $wpdb->get_row( $wpdb->prepare( $SQL, $registration_id ));
		if ( $check ) {
			$registration_id = $check->primary_registration_id;
			$SQL = "SELECT distinct primary_registration_id, registration_id ";
			$SQL .= "FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " ";
			$SQL .= "WHERE primary_registration_id = %s";
			$registration_ids = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ), ARRAY_A );
			$multi_reg = TRUE;
		} 

		// find the primary attendee id so we know which form to present since the additional attendees will have a different form 
		$SQL = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id =%s AND is_primary = 1 ";
		if ( $r = $wpdb->get_row( $wpdb->prepare( $SQL, $registration_id ))) {
			$primary_attendee = !empty($r->id) ? $r->id : $id;
			$is_additional_attendee = ($primary_attendee != $id) ? TRUE : FALSE;
		} else {
			$primary_attendee = FALSE;
		}


		// **************************************************************************
		// **************************  UPDATE PAYMENT  **************************
		// **************************************************************************
		if (!empty($_REQUEST['attendee_payment']) && $_REQUEST['attendee_payment'] == 'update_price') {

			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit_attendee_' . $registration_id . '_update_price_nonce' )) {
				//wp_die( $failed_nonce_msg );
			}

			$upd_price = (float)number_format( abs( ee_sanitize_value( $_REQUEST['final_price'] )), 2, '.', '' );
			$upd_qty = absint( $_REQUEST['quantity'] );			
			
			$set_cols_and_values = array( 
				'final_price'=>$upd_price, 
				'quantity'=> $upd_qty
			);
			$set_format = array( '%f', '%d' );
			$where_cols_and_values = array( 'id'=> $id );
			$where_format = array( '%d' );
			// run the update
			$upd_success = $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
			// if there was an actual error
			if ( $upd_success === FALSE ) {
				$notifications['error'][] = __('An error occured. Attendee ticket price details could not be updated.', 'event_espresso'); 
			} else {
			
				// now we need to gather all the ticket prices for all attendees for the entire registraion and calculate a new total cost
				$upd_total = 0;
				$SQL = "SELECT payment_status, amount_pd, final_price, quantity, is_primary FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = %s";
				if ( $attendee_tickets = $wpdb->get_results( $wpdb->prepare( $SQL, $registration_id ))) {			
					// loop thru tickets
					foreach ( $attendee_tickets as $attendee_ticket ) {
						// calculate total for each attendee and add to total cost
						$upd_total += $attendee_ticket->final_price * $attendee_ticket->quantity;
						// grab amount paid by primary attendee
						if ( $attendee_ticket->is_primary ) {
							$amount_pd = (float)$attendee_ticket->amount_pd;
							$payment_status = $attendee_ticket->payment_status;
						}
					}
				}
				// format new total_cost
				$upd_total = (float)number_format( $upd_total, 2, '.', '' );
				
				// compare new total_cost with amount_pd
				if ( $upd_total == $amount_pd ) {
					$upd_payment_status = 'Completed';//DO NOT TRANSLATE
				} elseif ( $upd_total > $amount_pd ) {
					$upd_payment_status = 'Pending';//DO NOT TRANSLATE
				} elseif ( $upd_total < $amount_pd ) {
					$upd_payment_status = 'Refund';//DO NOT TRANSLATE
				}
								
				// compare old payment status with new payment status and update if things have changed
				if ( $upd_payment_status != $payment_status ) {
					// update payment status for ALL attendees for the entire registration
					$set_cols_and_values = array( 'payment_status'=>$upd_payment_status );
					$set_format = array( '%s' );
					$where_cols_and_values = array( 'registration_id'=> $registration_id );
					$where_format = array( '%s' );
					// run the update
					$upd_success = $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
					// if there was an actual error
					if ( $upd_success === FALSE ) {
						$notifications['error'][] = __('An error occured while attempting to update the payment status for attendee from this registration.', 'event_espresso'); 
					}					
				}
				
				// now update the primary registrant's total cost field'
				$set_cols_and_values = array( 'total_cost'=>$upd_total );
				$set_format = array( '%f' );
				$where_cols_and_values = array( 'id'=> $id, 'is_primary' => TRUE );
				$where_format = array( '%d', '%d' );
				// run the update
				$upd_success = $wpdb->update( EVENTS_ATTENDEE_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
				// if there was an actual error
				if ( $upd_success === FALSE ) {
					$notifications['error'][] = __('An error occured. The primary attendee ticket total could not be updated.', 'event_espresso'); 
				} 	
				
				// let's base our success on the lack of errors
				$notifications['success'][] = empty( $notifications['error'] ) ? __('All attendee ticket price details have been successfully updated.', 'event_espresso') : __('Some attendee ticket price details were successfully updated, but the following error(s) may have prevented others from being updated:', 'event_espresso'); 	
			
			}			
		}
		
		
		// **************************************************************************
		// **************************  DELETE ATTENDEE  **************************
		// **************************************************************************
		if ( ! empty($_REQUEST['attendee_action']) && $_REQUEST['attendee_action'] == 'delete_attendee' ) {
		
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit_attendee_' . $registration_id . '_delete_attendee_nonce' )) {
				wp_die( $failed_nonce_msg );
			}
			
			$SQL = "DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id =%d";
			$del_results = $wpdb->query( $wpdb->prepare( $SQL, $id ));
			
			if ( $del_results === FALSE ) {
				$notifications['error'][] = __('An error occured. The attendee could not be deleted.', 'event_espresso'); 
			} elseif ( $del_results === 0 ) { 
				$notifications['error'][] = __('The attendee record in the database could not be found and was therefore not deleted.', 'event_espresso'); 
			} else {

				if (defined('ESPRESSO_SEATING_CHART')) {
					$SQL ="DELETE FROM " . EVENTS_SEATING_CHART_EVENT_SEAT_TABLE . " where attendee_id = %d";
					if ( $wpdb->query( $wpdb->prepare( $SQL, $id ))  === FALSE ) {
						$notifications['error'][] = __('An error occured. The attendee seating chart data could not be deleted.', 'event_espresso'); 
					}
				}
				
				// get id's for all attendees from this registration
				$SQL = "SELECT id from " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = %s";
				$attendees = $wpdb->query( $wpdb->prepare( $SQL, $registration_id ));
				if ( $attendees === FALSE ) {
					$notifications['error'][] = __('An error occured while attempting to retrieve additional attendee data from the database.', 'event_espresso'); 
				} else {
					// update quantities for attendees
					$SQL = " UPDATE " . EVENTS_ATTENDEE_TABLE . " SET quantity = IF(quantity IS NULL ,NULL,IF(quantity > 0,IF(quantity-1>0,quantity-1,1),0)) ";
					$SQL .= "WHERE registration_id =%s";
					if ( $wpdb->query( $wpdb->prepare( $SQL, $registration_id )) === FALSE ) {
						$notifications['error'][] = __('An error occured while attempting to update additional attendee ticket quantities.', 'event_espresso'); 
					}
					event_espresso_cleanup_multi_event_registration_id_group_data();
				}

				// let's base our success on the lack of errors
				$notifications['success'][] = empty( $notifications['error'] ) ? __('All attendee details have been successfully deleted.', 'event_espresso') : __('One or more errors may have prevented some attendee details from being successfully deleted.', 'event_espresso'); 	
				
			}

			
		// **************************************************************************
		// **************************  UPDATE ATTENDEE  **************************
		// **************************************************************************
		} else if ( ! empty( $_REQUEST['attendee_action'] ) && $_REQUEST['attendee_action'] == 'update_attendee' ) {
			//printr( $_POST, '$_POST  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
			
			if ( ! wp_verify_nonce( $_REQUEST['update_attendee_nonce'], 'edit_attendee_' . $registration_id . '_update_attendee_nonce' )) {
				//wp_die( $failed_nonce_msg );
			}
			
			//Update the price_option_type
			do_action('action_hook_espresso_save_attendee_meta', $id, 'price_option_type', isset($_POST['price_option_type']) && !empty($_POST['price_option_type']) ? ee_sanitize_value($_POST['price_option_type']) : 'DEFAULT');
			
			//Move attendee
			do_action('action_hook_espresso_attendee_mover_move');
			
			$event_id = isset($_POST['event_id']) ? $_POST['event_id'] : '';
			$txn_type = isset($_POST['txn_type']) ? $_POST['txn_type'] : '';

			$cols_and_values = array( 
					'fname'		=> isset($_POST['fname']) ? ee_sanitize_value($_POST['fname']) : '', 
					'lname'		=> isset($_POST['lname']) ? ee_sanitize_value($_POST['lname']) : '', 
					'address'	=> isset($_POST['address']) ? ee_sanitize_value($_POST['address']) : '', 
					'address2'	=> isset($_POST['address2']) ? ee_sanitize_value($_POST['address2']) : '', 
					'city'		=> isset($_POST['city']) ? ee_sanitize_value($_POST['city']) : '', 
					'state'		=> isset($_POST['state']) ? ee_sanitize_value($_POST['state']) : '', 
					'zip'		=> isset($_POST['zip']) ? ee_sanitize_value($_POST['zip']) : '', 
					'phone'		=> isset($_POST['phone']) ? ee_sanitize_value($_POST['phone']) : '', 
					'email'		=> isset($_POST['email']) ? sanitize_email($_POST['email']) : '' 
			);
			$cols_and_values_format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
			
			// Update the time ?
			if ( isset( $_POST['start_time_id'] )) {
				$SQL = "SELECT ese.start_time, ese.end_time FROM " . EVENTS_START_END_TABLE . " ese WHERE ese.id=%d";				
				if ( $times = $wpdb->get_results( $wpdb->prepare( $SQL, absint( $_POST['start_time_id'] )))) {
					foreach ($times as $time) {
						$start_time = $time->start_time;
						$end_time = $time->end_time;
					}
					$cols_and_values['event_time'] = $start_time;
					$cols_and_values['end_time'] = $end_time;
					array_push( $cols_and_values_format, '%s', '%s' );
				}
			}
		
			//Update price option
			if ( isset( $_POST['price_select'] ) && $_POST['price_select'] == TRUE ) {
				//Figure out if the person has registered using a price selection
				$selected_price_option = isset($_POST['new_price_option']) && !empty($_POST['new_price_option']) ? $_POST['new_price_option'] : $_POST['price_option'] ;
				$price_options = espresso_selected_price_option($selected_price_option);
				$price_type = $price_options['price_type'];
				$price_id = $price_options['price_id'];
				$which_price = $_POST['price_option_type'] == 'MEMBER' ? "member_price" : "event_cost";
				$results = $wpdb->get_row( $wpdb->prepare("SELECT " . $which_price . " as cost, surcharge, surcharge_type FROM " . EVENTS_PRICES_TABLE . " WHERE id='%d' ORDER BY id ASC", $price_id), ARRAY_A );
				$event_cost = number_format($results['cost']+event_espresso_calculate_surcharge($results['cost'], $results['surcharge'], $results['surcharge_type']), 2, '.', '' );
			
			}else{
				//If not using the price selection
				$wpdb->get_results("SELECT price_type, event_cost FROM " . EVENTS_PRICES_TABLE . " WHERE id ='" . absint( $_POST['price_id'] ) . "'");
				$num_rows = $wpdb->num_rows;
				if ($num_rows > 0) {
					//$event_cost = $wpdb->last_result[0]->event_cost;
					$price_type = $wpdb->last_result[0]->price_type;
				}
			}
			
			//Don't update the price if the attendee is moved
			if ( !isset($_POST['move_attendee']) ){
				$cols_and_values['price_option'] = $price_type;
				//$cols_and_values['final_price'] = $event_cost;
				//$cols_and_values['orig_price'] = $event_cost;
				array_push( $cols_and_values_format, '%s' );
			}
			
			//echo "<pre>".print_r($cols_and_values,true)."</pre>";
			
			//Run the update query
			$where_cols_and_values = array( 'id'=> $id );
			$where_format = array( '%d' );
			$upd_success = $wpdb->update( EVENTS_ATTENDEE_TABLE, $cols_and_values, $where_cols_and_values, $cols_and_values_format, $where_format );
			// if there was an actual error
			if ( $upd_success === FALSE ) {
				$notifications['error'][] = __('An error occured. Attendee details could not be updated.', 'event_espresso'); 
			}
			
		
			// Added for seating chart addon
			$booking_id = 0;
			if (defined('ESPRESSO_SEATING_CHART')) {
				if (seating_chart::check_event_has_seating_chart($event_id) !== false) {
					if (isset($_POST['seat_id'])) {
						$booking_id = seating_chart::parse_booking_info($_POST['seat_id']);
						if ($booking_id > 0) {
							seating_chart::confirm_a_seat($booking_id, $id);
						}
					}
				}
			}

			// Insert Additional Questions From Post Here
			$reg_id = $id;
			
			$SQL = "SELECT question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE . " WHERE id = %d";
			$questions = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ));

			$question_groups = unserialize($questions->question_groups);
			$event_meta = unserialize($questions->event_meta);

			if ( $is_additional_attendee && isset($event_meta['add_attendee_question_groups']) && $event_meta['add_attendee_question_groups'] != NULL) {
				$question_groups = $event_meta['add_attendee_question_groups'];
			}

			$questions_in = '';
			foreach ($question_groups as $g_id ) {
				$questions_in .= $g_id . ',';
			}
			$questions_in = substr($questions_in, 0, -1);
			
			$group_name = '';
			$counter = 0;
			
			$FILTER = '';
			if (isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == '2' && isset($_REQUEST['attendee_num']) && $_REQUEST['attendee_num'] > 1) {
				$FILTER .= " AND qg.system_group = 1 ";
			}
			//pull the list of questions that are relevant to this event
			$SQL = "SELECT q.*, q.id AS q_id,  qg.group_name, qg.show_group_description, qg.show_group_name ";
			$SQL .= "FROM " . EVENTS_QUESTION_TABLE . " q ";
			$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
			$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
			$SQL .= "WHERE qgr.group_id in ( $questions_in ) ";
			$SQL .= $FILTER . " ";
			$SQL .= "ORDER BY qg.id, q.id ASC";
			$questions = $wpdb->get_results( $SQL);
			
//			printr( $questions, '$questions  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
			
			$SQL = "SELECT id, question_id, answer FROM " . EVENTS_ANSWER_TABLE . " at WHERE at.attendee_id = %d";		
			$answers = $wpdb->get_results( $wpdb->prepare( $SQL, $id ), OBJECT_K );
			foreach ( $answers as $answer ) {
				$answer_a[$answer->id] = $answer->question_id;
			}
			
			if ($questions) {
				foreach ($questions as $question) {
					
					//printr( $question, '$question  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				
					switch ($question->question_type) {
					
						case "TEXT" :
						case "TEXTAREA" :
						case "SINGLE" :
						case "DROPDOWN" :
							if ( $question->system_name != '' ) {
								$post_val = isset( $_POST[ $question->system_name ] ) ? $_POST[ $question->system_name ] : '';
							} else {
								$post_val = isset( $_POST[ $question->question_type . '_' . $question->q_id ] ) ? $_POST[ $question->question_type . '_' . $question->q_id ] : '';
							}
							
							$post_val = apply_filters( 'filter_hook_espresso_admin_question_response', $post_val, $question );
							$post_val = ee_sanitize_value( stripslashes( $post_val ));
							break;
							
						case "MULTIPLE" :
						
							$post_val = '';
							for ( $i = 0; $i < count( $_POST[ $question->question_type . '_' . $question->q_id ] ); $i++ ) {
								$pval = apply_filters( 'filter_hook_espresso_admin_question_response', trim( $_POST[ $question->question_type . '_' . $question->q_id ][$i] ), $question );
								$post_val .= $pval . ",";
							}
							$post_val = ee_sanitize_value( substr( stripslashes( $post_val ), 0, -1 ));
							
							break;
					}
					
//					echo '<h4>$post_val : ' . $post_val . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//					echo '<h4>$question->id : ' . $question->q_id . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//					printr( $answer_a, '$answer_a  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
					
					if ( in_array( $question->q_id, $answer_a )) {
						// existing answer
						$set_cols_and_values = array( 'answer'=> html_entity_decode( trim( $post_val ), ENT_QUOTES, 'UTF-8' ));
						//echo "<pre>".print_r($set_cols_and_values,true)."</pre>";
						$set_format = array( '%s' );
						$where_cols_and_values = array( 'attendee_id'=>$id, 'question_id' => $question->q_id );
						$where_format = array( '%d', '%d' );
						// run the update
						$upd_success = $wpdb->update( EVENTS_ANSWER_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
//						echo '<h4>last_query : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

					} else {
						// new answer
						$set_cols_and_values = array( 
							'registration_id'=>$registration_id,
							'attendee_id'=>$id,
							'question_id'=> $question->q_id,
							'answer'=>html_entity_decode( trim( $post_val ), ENT_QUOTES, 'UTF-8' )
						);
						$set_format = array( '%s', '%d', '%d', '%s'  );
						// run the insert
						$upd_success = $wpdb->insert( EVENTS_ANSWER_TABLE, $set_cols_and_values, $set_format );
//						echo '<h4>INSERlast_query : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

					}				
				}
			}
			

			// let's base our success on the lack of errors
			$notifications['success'][] = empty( $notifications['error'] ) ? __('All attendee details have been successfully updated.', 'event_espresso') : __('One or more errors may have prevented some attendee details from being successfully updated.', 'event_espresso'); 	
				
		}



		// **************************************************************************
		// *************************  RETRIEVE ATTENDEE  *************************
		// **************************************************************************

		$counter = 0;
		$additional_attendees = NULL;
		
		$SQL = "SELECT att.*, evt.event_name, evt.question_groups, evt.event_meta, evt.additional_limit FROM " . EVENTS_ATTENDEE_TABLE . " att ";
		$SQL .= "JOIN " . EVENTS_DETAIL_TABLE . " evt ON att.event_id = evt.id ";
		// are we looking for an additional attendee ?
		if ( isset( $_REQUEST['attendee_num'] ) && $_REQUEST['attendee_num'] > 1 && isset( $_REQUEST['id'] )) {
			$SQL .= "WHERE  att.id = " . ee_sanitize_value( $_REQUEST['id'] );
		} else {
			// check for multi reg & additional attendees by first finding primary attendee
			$SQL2 = "SELECT primary_registration_id FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " WHERE registration_id = %s";
			if ( $primary_registration_id = $wpdb->get_var( $wpdb->prepare( $SQL2, ee_sanitize_value( $_REQUEST['registration_id'] )))) {
				// now find all registrations
				$SQL3 = "SELECT registration_id FROM " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " WHERE primary_registration_id = %s";
				$reg_ids = $wpdb->get_col( $wpdb->prepare( $SQL3, $primary_registration_id ));
				$reg_ids = "'" . implode("','", $reg_ids) . "'";
			} else {
				$reg_ids = "'" . ee_sanitize_value( $_REQUEST['registration_id'] ) . "'";
			}		
			$SQL .= " WHERE registration_id IN ( $reg_ids ) ORDER BY att.id";
		}
		$attendees = $wpdb->get_results( $wpdb->prepare( $SQL, NULL ));
		
		foreach ($attendees as $attendee) {
			if ( $counter == 0 ) {
				$id = $attendee->id;
				$registration_id = $attendee->registration_id;
				$lname = $attendee->lname;
				$fname = $attendee->fname;
				$address = $attendee->address;
				$address2 = $attendee->address2;
				$city = $attendee->city;
				$state = $attendee->state;
				$zip = $attendee->zip;
				$email = $attendee->email;
				$payment = $attendee->payment;
				$phone = $attendee->phone;
				$date = $attendee->date;
				$payment_status = $attendee->payment_status;
				$txn_type = $attendee->txn_type;
				$txn_id = $attendee->txn_id;
				$quantity = $attendee->quantity;
				$payment_date = $attendee->payment_date;
				$event_id = $attendee->event_id;
				$event_name = $attendee->event_name;
				$question_groups = unserialize($attendee->question_groups);
				$event_meta = unserialize($attendee->event_meta);
				$coupon_code = $attendee->coupon_code;
				$is_additional_attendee = ($primary_attendee != $id) ? true : false;
				$attendee_limit = $attendee->additional_limit;
				$amount_pd = $attendee->amount_pd;
				$total_cost = $attendee->total_cost;
				$orig_price = $attendee->orig_price;
				$final_price = $attendee->final_price;
				$price_option = $attendee->price_option;

				$start_date = $attendee->start_date;
				$event_time = $attendee->event_time;
				
				//Create an array for the default/member price type
				$price_type_select = '';
				if (function_exists('espresso_members_version')) { 
					$p_values =	array(
						array('id'=>'DEFAULT','text'=> __('Default Pricing','event_espresso')),
						array('id'=>'MEMBER','text'=> __('Member Pricing','event_espresso'))
					);
					$price_type_select = '<li>'.select_input( 'price_option_type', $p_values, apply_filters('action_hook_espresso_get_attendee_meta_value', $id, 'price_option_type'), 'id="price_option_type"').'</li>';
				}
									
				// Added for seating chart addon
				$booking_info = "";
				if ( defined('ESPRESSO_SEATING_CHART') ){
					$seating_chart_id = seating_chart::check_event_has_seating_chart($event_id);
					if ( $seating_chart_id !== false ){
						$seat = $wpdb->get_row("select scs.* , sces.id as booking_id from ".EVENTS_SEATING_CHART_SEAT_TABLE." scs inner join ".EVENTS_SEATING_CHART_EVENT_SEAT_TABLE." sces on scs.id = sces.seat_id where sces.attendee_id = '".$id."' ");
						if ( $seat !== NULL ){
							$booking_info = $seat->custom_tag." #booking id: ".$seat->booking_id;
						}
					}
				}


				$event_date = event_date_display($start_date . ' ' . $event_time, get_option('date_format') . ' g:i a');

				if ( $is_additional_attendee && isset($event_meta['add_attendee_question_groups']) && $event_meta['add_attendee_question_groups'] != NULL ) {
					$question_groups = $event_meta['add_attendee_question_groups'];
				}
				
				$counter++;

			} else {
				$additional_attendees[$attendee->id] = array('full_name' => $attendee->fname . ' ' . $attendee->lname, 'email' => $attendee->email, 'phone' => $attendee->phone);
			}
		}

		// display success messages
		if ( ! empty( $notifications['success'] )) { 
			$success_msg = implode( $notifications['success'], '<br />' );
		?>
				
			<div id="message" class="updated fade">
				<p>
					<strong><?php echo $success_msg; ?></strong>
				</p>
			</div>

		<?php
		 } 
		// display error messages
		if ( ! empty( $notifications['error'] )) {
			$error_msg = implode( $notifications['error'], '<br />' );
		?>
				
			<div id="message" class="error">
				<p>
					<strong><?php echo $error_msg; ?></strong>
				</p>
			</div>

		<?php 
		}
		?>
		
<div>		
	<p>
		<a href="admin.php?page=events&event_id=<?php echo $event_id; ?>&event_admin_reports=list_attendee_payments">
			<strong>&laquo;&nbsp;<?php _e('Back to Attendees List', 'event_espresso'); ?></strong>
		</a>
	</p>
</div>

<div class="metabox-holder">
	<div class="postbox">
		<h3>
			<?php _e('Registration Id <a href="admin.php?page=events&event_admin_reports=edit_attendee_record&event_id=' . $event_id . '&registration_id=' . $registration_id . '&form_action=edit_attendee">#' . $registration_id . '</a> | ID #' . $id . ' | Name: ' . $fname . ' ' . $lname . ' | Registered For:', 'event_espresso'); ?>
			<a href="admin.php?page=events&event_admin_reports=list_attendee_payments&event_id=<?php echo $event_id ?>"><?php echo stripslashes_deep($event_name) ?></a> - <?php echo $event_date; ?>
		</h3>
		<div class="inside">
			<table width="100%">
				<tr>
					<td width="50%" valign="top">
						<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="espresso_form" id="attendee_details">
							<h4 class="qrtr-margin">
								<?php _e('Registration Information', 'event_espresso'); ?>
								<?php echo $is_additional_attendee == false ? '[ <span class="green_text">' . __('Primary Attendee Record', 'event_espresso') . '</span> ]' : '[ <a href="admin.php?page=events&event_admin_reports=edit_attendee_record&event_id=' . $event_id . '&registration_id=' . $registration_id . '&form_action=edit_attendee">View/Edit Primary Attendee</a> ]'; ?>
							</h4>
							<fieldset>
								<ul>
									
									<?php 
									
									echo $price_type_select;
									$price_type_select_notice = '<div style="width:80%;" class="red_text">'.__('Please Note: Changing the price type will not affect "Attendee Ticket Fees" on the right side of this page. Price changes will need to be updated manually.', 'event_espresso').'</div>';
									
									?>
									
									<li id="standard_price_selection">
										<?php do_action( 'action_hook_espresso_attendee_admin_price_dropdown', $event_id, array('show_label'=>TRUE, 'label'=>'Price Option', 'current_value'=>$price_option) );?>
										<?php echo $price_type_select_notice; ?>
									</li>
									<li id="members_price_selection">
										<?php do_action( 'action_hook_espresso_attendee_admin_price_dropdown_member', $event_id, array('show_label'=>TRUE, 'label'=>'Member Price Option', 'current_value'=>$price_option) );?>
										<?php echo $price_type_select_notice; ?>
									</li>
									
									<li>
										<?php
										$time_id = 0;
										$SQL = "SELECT id FROM " . EVENTS_START_END_TABLE . " WHERE event_id=%d AND start_time =%s";										
										if ( $event_time = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id, $event_time ))) {
											$time_id = $event_time->id;
										}
										echo event_espresso_time_dropdown( $event_id, $label = 1, $multi_reg = 0, $time_id );
										?>
									</li>
									<li>
										<?php
											//Added for seating chart addon.  Creates a field to select a seat from a popup.
											do_action('ee_seating_chart_css');
											do_action('ee_seating_chart_js');
											do_action('ee_seating_chart_flush_expired_seats');
											do_action( 'espresso_seating_chart_select', $event_id, $booking_info);
										?>
									</li>
									<li>
										<?php
												if (count($question_groups) > 0) {
													$questions_in = '';

													foreach ($question_groups as $g_id) {
														$questions_in .= $g_id . ',';
													}

													$questions_in = substr($questions_in, 0, -1);
													$group_name = '';
													$counter = 0;
													$FILTER = '';
													if (isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == '2' && isset($_REQUEST['attendee_num']) && $_REQUEST['attendee_num'] > 1) {
														$FILTER .= " AND qg.system_group = 1 ";
													}

													//pull the list of questions that are relevant to this event
													$SQL = "SELECT q.*,  qg.group_name, qg.show_group_description, qg.show_group_name ";
													$SQL .= "FROM " . EVENTS_QUESTION_TABLE . " q ";
													//$SQL .= "LEFT JOIN " . EVENTS_ANSWER_TABLE . " at on q.id = at.question_id ";
													$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
													$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
													$SQL .= "WHERE qgr.group_id in ( $questions_in ) ";
													//$SQL .= "AND ( at.attendee_id IS NULL OR at.attendee_id = %d ) ";
													$SQL .= $FILTER . " ";
													$SQL .= "ORDER BY qg.id, q.id ASC";
													//echo "sql:".$wpdb->prepare( $SQL, $id );
													$questions = $wpdb->get_results( $SQL);
													
													$question_ids = array();
													foreach($questions as $question){
														$question_ids[]=$question->id;
													}
													$answer_objs_to_those_questions = $wpdb->get_results($wpdb->prepare("
														SELECT question_id,answer FROM ".EVENTS_ANSWER_TABLE." WHERE
															question_id IN (".implode(",",$question_ids).") AND attendee_id = %d",$id));
													$answers_to_question_ids = array();
													foreach($answer_objs_to_those_questions as $answer){
														$answers_to_question_ids[$answer->question_id] = $answer->answer;
													}
													
													$num_rows = $wpdb->num_rows;

													if ($num_rows > 0) {

													//Output the questions
														$question_displayed = array();
														foreach ($questions as $question) {
															
															$counter++;
															if (!in_array($question->id, $question_displayed)) {
																$question_displayed[] = $question->id;
																//echo '<p>';
																echo event_form_build_edit($question, isset($answers_to_question_ids[$question->id])?$answers_to_question_ids[$question->id]:null, $show_admin_only = true);
																//echo "</p>";


																#echo $counter == $num_rows ? '</fieldset>' : '';
															}
														}
													}//end questions display
												}
												?>
									</li>
									
									<?php do_action('action_hook_espresso_attendee_mover_events_list', $event_id)?>
									<?php echo espresso_hidden_price_id($event_id); ?>
									<input type="hidden" name="new_price_option" id="new_price_option-<?php echo $event_id ?>" value="<?php echo $event_id . '|'.$price_option ?>" />
									<input type="hidden" name="id" value="<?php echo $id ?>" />
									<input type="hidden" name="registration_id" value="<?php echo $registration_id ?>" />
									<input type="hidden" name="event_id" value="<?php echo $event_id ?>" />
									<input type="hidden" name="display_action" value="view_list" />
									<input type="hidden" name="form_action" value="edit_attendee" />
									<input type="hidden" name="attendee_action" value="update_attendee" />
									<?php wp_nonce_field( 'edit_attendee_' . $registration_id . '_update_attendee_nonce','update_attendee_nonce' ); ?>
									<li>
										<input type="submit" name="Submit" class="button-primary action"  value="<?php _e('Update Record', 'event_espresso'); ?>" />
									</li>
								</ul>
							</fieldset>
						</form></td>
					<td  width="50%" valign="top">
						<?php if (count($additional_attendees) > 0) { ?> 
						<h4>
							<?php _e('Additional Attendees', 'event_espresso'); ?>
						</h4>
						<ol>
							<?php
								foreach ($additional_attendees as $att => $row) {
									$attendee_num++;
							?>
							<li>
								<?php 
								// create edit link
								$edit_att_url_params = array( 
									'event_admin_reports' => 'edit_attendee_record',
									'form_action' => 'edit_attendee',
									'registration_id' => $registration_id,
									'id' => $att,
									'attendee_num' => $attendee_num,
									'event_id' => $event_id
								);
								// add url params
								$edit_attendee_link = add_query_arg( $edit_att_url_params, 'admin.php?page=events' );
								?>								
								<a href="<?php echo $edit_attendee_link; ?>" title="<?php _e('Edit Attendee', 'event_espresso'); ?>">
									<strong><?php echo $row['full_name']; ?></strong> (<?php echo $row['email']; ?>)
								</a>
								&nbsp;&nbsp;|&nbsp;&nbsp;
								<?php 
								// create delete link
								$delete_att_url_params = array( 
									'event_admin_reports' => 'edit_attendee_record',
									'form_action' => 'edit_attendee',
									'attendee_action' => 'delete_attendee',
									'registration_id' => $registration_id,
									'id' => $att,
									'event_id' => $event_id
								);
								// add url params
								$delete_attendee_link = add_query_arg( $delete_att_url_params, 'admin.php?page=events' );
								// add nonce 
								$delete_attendee_link = wp_nonce_url( $delete_attendee_link, 'edit_attendee_' . $registration_id . '_delete_attendee_nonce' );
								?>								
								<a href="<?php echo $delete_attendee_link ?>" title="<?php _e('Delete Attendee', 'event_espresso'); ?>" onclick="return confirmDelete();">
									<?php _e('Delete', 'event_espresso'); ?>
								</a>
							</li>
							<?php } ?>
						</ol>
						<?php } ?>
						
						
						<br/>
						<h4 class="qrtr-margin"><?php _e('Ticket Prices', 'event_espresso'); ?></h4>
						
						<form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>&status=saved" class="espresso_form">
							<fieldset>
								<ul>
									<li>
											<strong class="att-tckt-prc-lbl"><?php _e('Payment Status:', 'event_espresso'); ?></strong> 
											<?php echo $payment_status; ?> <?php echo event_espresso_paid_status_icon($payment_status); ?>&nbsp;&nbsp;[&nbsp;<a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $registration_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Payment', 'event_espresso'); ?>">
											<?php _e('View/Edit Payment', 'event_espresso'); ?>
											</a>&nbsp;]
									</li>
									<li>
											<strong class="att-tckt-prc-lbl"><?php _e('Transaction ID:', 'event_espresso'); ?></strong> 
											<?php echo !empty($txn_id) ? $txn_id : 'N/A'; ?>
									</li>
									<li>
											<strong class="att-tckt-prc-lbl"><?php _e('Date Paid:', 'event_espresso'); ?></strong> 
											<?php echo !empty($payment_date) ? event_date_display($payment_date) : 'N/A' ?>
									</li>
									<li>
											<strong class="att-tckt-prc-lbl">
											<?php _e('Total Amount Owing:', 'event_espresso'); ?>
											</strong>
											<?php echo $org_options['currency_symbol'] ?><?php echo $total_cost; ?>
									</li>
									<li>
											<strong class="att-tckt-prc-lbl"><?php _e('Total Amount Paid to Date:', 'event_espresso'); ?></strong> 
											<?php echo $org_options['currency_symbol'] . $amount_pd; ?><?php //echo espresso_attendee_price(array('attendee_id' => $id, 'reg_total' => true)); ?>
									</li>
									<li>
										<h6 class="qrtr-margin"><strong><?php _e('Attendee Ticket Fees:', 'event_espresso'); ?></strong></h6>
										<div  <?php if (isset($_REQUEST['show_payment']) && $_REQUEST['show_payment'] == 'true') echo ' class="yellow_inform"'; ?>>
											<table  border="0">
												<tr>
													<td  align="left" valign="top">
														<label><?php _e('Amount:', 'event_espresso'); ?> ( <?php echo $org_options['currency_symbol'] ?> )</label>
													</td>
													<td  align="center" valign="top">
														<label><?php _e('# Tickets:', 'event_espresso'); ?></label>
													</td>
													<td  align="right" valign="top">
														<label class="algn-rght"><?php _e('Total:', 'event_espresso'); ?></label>
													</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<input name="final_price" class="small-text algn-rght" type="text" value ="<?php echo $final_price; ?>" />
													</td>
													<td align="center" valign="top">
														<?php
															// number of tickets currently purchased
															$quantity = ! empty( $quantity ) ? $quantity : 1; 
															 // availalbe spaces left for event
															$available_spaces = get_number_of_attendees_reg_limit( $event_id, 'number_available_spaces');
															if ( $available_spaces != 'Unlimited' ) {
																// first add our purchased tickets ($quantity) back into available spaces 
																// ( becuase a sold out show incluldes these tickets here, so admin should be allowed to play with these numbers - think about it )
																$available_spaces += $quantity;
																$attendee_limit = ($attendee_limit <= $available_spaces) ? $attendee_limit : $available_spaces;
															}
															// final check to make sure that attendee limit has to at LEAST be the number of tickets this attendee has already purchased
															// otherwise the ticket quantity selector may display less than what this attendee has already purchased													
															$attendee_limit = $attendee_limit < $quantity ? $quantity : $attendee_limit;
														?>
														<select name="quantity" class="price_id">
														<?php
															for ($i = 0; $i <= $attendee_limit; $i++) {
																$selected = ( $i == $quantity ) ? ' selected="selected" ' : '';
														?>
															<option <?php echo $selected; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
														<?php } ?>
														</select>
														<!--<input name="quantity" type="text" value ="<?php echo !empty($quantity) ? $quantity : 1; ?>"  />-->
													</td>
													<td  align="right" valign="top">
														<?php $ticket_total = (float)( $final_price * $quantity ) > 0 ? number_format( $final_price * $quantity, 2, '.', '' ) : 0.00 ;?>
														<input class="small-text algn-rght" type="text" name="total_owing" disabled="true" value ="<?php echo $ticket_total; ?>" />
													</td>
												</tr>
											</table>
										</div>
									</li>
									<li>
											<strong class="att-tckt-prc-lbl"><?php _e('Original Ticket Price:', 'event_espresso'); ?></strong> 
											<?php echo $org_options['currency_symbol'] . '&nbsp;' . $orig_price; ?>&nbsp;&nbsp;/&nbsp;&nbsp;<?php _e('ticket', 'event_espresso'); ?>								
									</li>
									<li>
										<br/>
										<input type="submit" name="submit_ticket_prices" class="button-primary action"  value="Update Price" />
									</li>
								</ul>
							</fieldset>
							<input type="hidden" name="id" value="<?php echo $id ?>" />
							<input type="hidden" name="registration_id" value="<?php echo $registration_id ?>" />
							<input type="hidden" name="form_action" value="edit_attendee" />
							<input type="hidden" name="event_id" value="<?php echo $event_id ?>" />
							<input type="hidden" name="attendee_payment" value="update_price" />
							<?php wp_nonce_field( 'edit_attendee_' . $registration_id . '_update_price_nonce' ); ?>
						</form>
						
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					// Remove li parent for input 'values' from page if 'text' box or 'textarea' are selected
					<?php if (function_exists('espresso_members_version')) { ?>
						var selectValue = jQuery('select#price_option_type option:selected').val();
					<?php }else{ ?>
						var selectValue = 'DEFAULT';
					<?php }?>
					//alert(selectValue + ' - this is initial value');
					
					if(selectValue == 'DEFAULT'){
						jQuery('#members_price_selection').hide();
						var standard_SelectValue = jQuery('select#price_option-<?php echo $event_id ?> option:selected').val();
						jQuery('#new_price_option-<?php echo $event_id ?>').val(standard_SelectValue);
						jQuery('select#price_option-<?php echo $event_id ?>').bind('change', function() {
							var new_standard_SelectValue = jQuery('select#price_option-<?php echo $event_id ?> option:selected').val();
							jQuery('#new_price_option-<?php echo $event_id ?>').val(new_standard_SelectValue);
						});	
					}else{
						jQuery('#standard_price_selection').hide();
						var member_SelectValue = jQuery('select#members_price_option-<?php echo $event_id ?> option:selected').val();
						jQuery('#new_price_option-<?php echo $event_id ?>').val(member_SelectValue);
						jQuery('select#members_price_option-<?php echo $event_id ?>').bind('change', function() {
						var new_member_SelectValue = jQuery('select#members_price_option-<?php echo $event_id ?> option:selected').val();
							jQuery('#new_price_option-<?php echo $event_id ?>').val(new_member_SelectValue);
						});
					}
					
					jQuery('select#price_option_type').bind('change', function() {
						var selectValue = jQuery('select#price_option_type option:selected').val();
							  
						if (selectValue == 'MEMBER') {
							//alert(selectValue);
							jQuery('#members_price_selection').fadeIn('fast');
							jQuery('#standard_price_selection').fadeOut('fast');
							//move to hidden field
							var member_SelectValue = jQuery('select#members_price_option-<?php echo $event_id ?> option:selected').val();
							jQuery('#new_price_option-<?php echo $event_id ?>').val(member_SelectValue);
							
							jQuery('select#members_price_option-<?php echo $event_id ?>').bind('change', function() {
							var new_member_SelectValue = jQuery('select#members_price_option-<?php echo $event_id ?> option:selected').val();
								jQuery('#new_price_option-<?php echo $event_id ?>').val(new_member_SelectValue);
							});
					
						} else {
							//alert(selectValue);
							jQuery('#standard_price_selection').fadeIn('fast');
							jQuery('#members_price_selection').fadeOut('fast');
							//move to hidden field
							var standard_SelectValue = jQuery('select#price_option-<?php echo $event_id ?> option:selected').val();
							jQuery('#new_price_option-<?php echo $event_id ?>').val(standard_SelectValue);
							
							jQuery('select#price_option-<?php echo $event_id ?>').bind('change', function() {
								var new_standard_SelectValue = jQuery('select#price_option-<?php echo $event_id ?> option:selected').val();
								jQuery('#new_price_option-<?php echo $event_id ?>').val(new_standard_SelectValue);
							});	
						}
					});
					
				});
			</script>
<?php
	}
}






function espresso_parse_admin_question_response_for_price( $value = '', $price_mod = 'N' ) {
	if ( $price_mod == 'Y' ) {
		global $org_options;
		$values = explode( '|', $value );
		$price = number_format( (float)$values[1], 2, '.', ',' );
		$plus_or_minus = $price > 0 ? '+' : '-';
		$price_mod = $price > 0 ? $price : $price * (-1);
		$value = $values[0] . '&nbsp;[' . $plus_or_minus . $org_options['currency_symbol'] . $price_mod . ']';	
	}
	return $value;
}
