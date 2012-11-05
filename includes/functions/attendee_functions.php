<?php

//Attendee functions

function add_attendee_questions($questions, $registration_id, $attendee_id = 0, $extra = array()) {

	if (array_key_exists('session_vars', $extra)) {
		$response_source = $extra['session_vars'];
	} else {
		$response_source = $_POST;
	}
		

	array_walk_recursive( $response_source, 'sanitize_text_field' );
	
	//printr( $questions, '$questions  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	$question_groups = maybe_unserialize( $questions ); 
	global $wpdb, $org_options;
	$wpdb->show_errors();


	if (count($question_groups) > 0) {
		$questions_in = '';


		foreach ($question_groups as $g_id) {
			$questions_in .= $g_id . ',';
		}			

		$questions_in = substr($questions_in, 0, -1);
		$group_name = '';
		$counter = 0;

		$SQL = "SELECT q.*, qg.group_name FROM " . EVENTS_QUESTION_TABLE . " q ";
		$SQL .= "	JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
		$SQL .= "	JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
		$SQL .= "	WHERE qgr.group_id in (%s) ORDER BY q.id ASC";

		$questions = $wpdb->get_results( $wpdb->prepare( $SQL, $questions_in ));

		if ( $questions !== FALSE ) {
			$question_displayed = array();
			global $email_questions; //Make a global variable to hold the answers to the questions to be sent in the admin email.
			$email_questions = '<p>' . __('Form Questions:', 'event_espresso') . '<br />';
			foreach ($questions as $question) {
				$question_type = !empty($response_source[$question->question_type . '_' . $question->id]) ? $response_source[$question->question_type . '_' . $question->id] :'';
				if (!in_array($question->id, $question_displayed)) {
					$question_displayed[] = $question->id;
					switch ($question->question_type) {
						case "TEXT" :
						case "TEXTAREA" :
						case "DROPDOWN" :
						case "SINGLE" :

							if ($question->admin_only != 'Y') {
								$post_val = ($question->system_name != '') ? $response_source[$question->system_name] : $question_type;
							} else {
								$post_val = '';
							}
							
							break;
						case "MULTIPLE" :
						
							$post_val = '';
							if (!empty($response_source[$question->question_type . '_' . $question->id]) && $question->admin_only != 'Y') {
								for ($i = 0; $i < count($response_source[$question->question_type . '_' . $question->id]); $i++) {
									$post_val .= trim($response_source[$question->question_type . '_' . $question->id][$i]) . ",";
								}
							}
							
							break;
					}

					$columns_and_values = array(
						'registration_id' => $registration_id, 
						'attendee_id' => $attendee_id, 
						'question_id' => $question->id,
						'answer' => html_entity_decode( trim( $post_val ), ENT_QUOTES )
					);
					$data_formats = array( '%s', '%d',  '%d', '%s' );
				
					$wpdb->prepare( $wpdb->insert( EVENTS_ANSWER_TABLE, $columns_and_values, $data_formats ));

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
