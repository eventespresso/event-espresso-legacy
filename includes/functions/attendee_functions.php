<?php
//Attendee functions

function add_attendee_questions($questions, $registration_id, $attendee_id=0){
	 /*echo 'Debug: <br />';
	print_r( $questions );
	echo '<br>';
	echo $registration_id;
	echo '<br>';
	echo $attendee_id;
	echo '<br>';*/
		$question_groups = unserialize($questions->question_groups);
		global $wpdb, $org_options;
		$wpdb->show_errors();
					//print_r($question_groups);
	
					if (count($question_groups) > 0){
						$questions_in = '';
	
						foreach ($question_groups as $g_id) $questions_in .= $g_id . ',';
	
						$questions_in = substr($questions_in,0,-1);
						$group_name = '';
						$counter = 0;
	
						$questions = $wpdb->get_results("SELECT q.*, qg.group_name
														FROM " . EVENTS_QUESTION_TABLE . " q
														JOIN " .  EVENTS_QST_GROUP_REL_TABLE . " qgr
														on q.id = qgr.question_id
														JOIN " . EVENTS_QST_GROUP_TABLE . " qg
														on qg.id = qgr.group_id
														WHERE qgr.group_id in ( " .   $questions_in
														. ") AND q.system_name IS NULL ORDER BY id ASC");
						$num_rows = $wpdb->num_rows;
	
						if ($num_rows > 0 ){
							global $email_questions;//Make a global variable to hold the answers to the questions to be sent in the admin email.
							$email_questions = '<p>'.__('Form Questions:','event_espresso').'<br />';
							foreach ( $questions as $question ) {
								switch ($question->question_type) {
									case "TEXT" :
									case "TEXTAREA" :
									case "DROPDOWN" :
										$post_val = $_POST [$question->question_type . '_' . $question->id];
										$wpdb->query ( "INSERT into ".EVENTS_ANSWER_TABLE." (registration_id, attendee_id, question_id, answer)
														values ('" . $registration_id . "', '" . $attendee_id . "', '" . $question->id . "', '" . $post_val . "')" );
										$email_questions .=  $question->question.': '.$post_val.'<br />';
									break;
									case "SINGLE" :
										$post_val = $_POST [$question->question_type . '_' . $question->id];
										$wpdb->query ( "INSERT into ".EVENTS_ANSWER_TABLE." (registration_id, attendee_id, question_id, answer)
														values ('" . $registration_id . "', '" . $attendee_id . "', '" . $question->id . "', '" . $post_val . "')" );
										$email_questions .=  $question->question.': '.$post_val.'<br />';
									break;
									case "MULTIPLE" :
										$value_string = '';
										for ($i=0; $i<count($_POST[$question->question_type.'_'.$question->id]); $i++){
											$value_string .= $_POST[$question->question_type.'_'.$question->id][$i].",";
										}
									
										$wpdb->query ( "INSERT INTO ".EVENTS_ANSWER_TABLE." (registration_id, attendee_id, question_id, answer)
														VALUES ('" . $registration_id . "', '" . $attendee_id . "', '" . $question->id . "', '" . $value_string . "')" );
										$email_questions .=  $question->question.': '.$value_string.'<br />';
									break;
								}
							}
							$email_questions .= '</p>';
						}
					}
}