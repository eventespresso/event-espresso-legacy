<?php

//Attendee functions

function add_attendee_questions($questions, $registration_id, $attendee_id = 0, $extra = array()) {

	global $wpdb, $org_options;
	//$wpdb->show_errors();

	if (array_key_exists('session_vars', $extra)) {
		$response_source = $extra['session_vars'];
	} else {
		$response_source = $_POST;
	}
	
	array_walk_recursive( $response_source, 'sanitize_text_field' );

	$question_groups = maybe_unserialize( $questions ); 
	//printr( $questions, '$question_groups  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	if (count($question_groups) > 0) {
		$questions_in = '';

		foreach ($question_groups as $g_id) {
			$questions_in .= $g_id . ',';
		}
		$questions_in = substr($questions_in, 0, -1);
		
		$SQL = "SELECT q.*, q.id AS qstn_id, qg.id, qg.group_name ";
		$SQL .= "FROM " . EVENTS_QST_GROUP_TABLE . " qg ";
		$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr ON qg.id = qgr.group_id ";
		$SQL .= "	JOIN " . EVENTS_QUESTION_TABLE . " q ON q.id = qgr.question_id ";
		$SQL .= 'WHERE qg.id IN ('.$questions_in.') ORDER BY qg.id, q.id ASC';

		$questions = $wpdb->get_results( $wpdb->prepare( $SQL, NULL ));
//		echo '<h4>LQ : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//		printr( $questions, '$questions  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

		if ( $questions !== FALSE ) {
			// we'll store question IDs in here so we know which ones ahve already been displayed
			$question_displayed = array();
			//Make a global variable to hold the answers to the questions to be sent in the admin email.
			global $email_questions; 
			$email_questions = '<p>' . __('Form Questions:', 'event_espresso') . '<br />';
			// cycle thru questions
			foreach ($questions as $question) {
				//printr( $question, '$question  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				// depending on the quesion, it's POST key may be different
				$question_type = !empty($response_source[$question->question_type . '_' . $question->qstn_id]) ? $response_source[$question->question_type . '_' . $question->qstn_id] :'';			
				//echo '<h4>$question_type : ' . $question_type . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
				// so if we haven't already displayed this question
				if ( ! in_array( $question->qstn_id, $question_displayed )) {
					// store question ID
					$question_displayed[] = $question->qstn_id;
					// what kinda question ?
					switch ($question->question_type) {
					
						case "TEXT" :
						case "TEXTAREA" :
						case "DROPDOWN" :
						case "SINGLE" :
							
							if ($question->admin_only != 'Y') {
								$post_val = ( $question->system_name != '' ) ? $response_source[$question->system_name] : $question_type;
								$post_val = apply_filters( 'filter_hook_espresso_form_question_response', trim( $post_val ), $question, $attendee_id );
						} else {
								$post_val = '';
							}
							
							break;
						case "MULTIPLE" :
						
							$post_val = '';
							if ( ! empty( $response_source[$question->question_type . '_' . $question->qstn_id] ) && $question->admin_only != 'Y' ) {
								for ( $i = 0; $i < count( $response_source[$question->question_type . '_' . $question->qstn_id] ); $i++ ) {
									$val = trim( $response_source[$question->question_type . '_' . $question->qstn_id][$i] );
									$val =  apply_filters( 'filter_hook_espresso_form_question_response', $val, $question, $attendee_id );
									$post_val .= $val . ",";
								}
							}
							
							break;
					}

					$columns_and_values = array(
						'registration_id' => $registration_id, 
						'attendee_id' => $attendee_id, 
						'question_id' => $question->qstn_id,
						'answer' => ee_sanitize_value($post_val)
					);
					$data_formats = array( '%s', '%d',  '%d', '%s' );
				
					$wpdb->insert( EVENTS_ANSWER_TABLE, $columns_and_values, $data_formats );
					//echo '<h4>LQ : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

					$email_questions .= $question->question . ': ' . $post_val . '<br />';
					
				}
			}
			$email_questions .= '</p>';
		}
	}
}

function is_attendee_approved($event_id, $attendee_id) {
	global $wpdb, $org_options;
	$result = true;
	if (isset($org_options["use_attendee_pre_approval"]) && $org_options["use_attendee_pre_approval"] == "Y") {
		$result = false;
		$require_pre_approval = 0;
		$tmp_events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id = " . $event_id);
		foreach ($tmp_events as $tmp_event) {
			$require_pre_approval = $tmp_event->require_pre_approval;
		}
		if ($require_pre_approval == 1) {
			$tmp_attendees = $wpdb->get_results("SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = " . $attendee_id);
			foreach ($tmp_attendees as $tmp_attendee) {
				$pre_approve = $tmp_attendee->pre_approve;
			}
			if ($pre_approve == 0) {
				$result = true;
			}
		} else {
			$result = true;
		}
	}
	return $result;
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
