<?php
if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

function attendee_edit_record() {

	global $wpdb, $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	
	$id = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : FALSE;
	$registration_id = isset( $_REQUEST['registration_id'] ) ? wp_strip_all_tags( $_REQUEST['registration_id'] ) : FALSE;
	
	if ( isset( $_REQUEST['r_id'] ) && ! empty( $_REQUEST['r_id'] )) {
		$registration_id = wp_strip_all_tags( $_REQUEST['r_id'] );
	}		
	
	if ( $id && $registration_id ) {
			
		if ( ! empty($_REQUEST['delete_attendee'] ) && $_REQUEST['delete_attendee'] == 'true' ) {
		
			$SQL = " DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = %d";
			$wpdb->query($wpdb->prepare( $SQL, $id ));
			
			$SQL = "SELECT id from " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = %s";
			$wpdb->query($wpdb->prepare( $SQL, $registration_id ));
			
			if ($wpdb->num_rows == 0) {
			
				$SQL = " UPDATE " . EVENTS_ATTENDEE_TABLE . " ";
				$SQL .= "SET quantity = IF(quantity IS NULL ,NULL,IF(quantity > 0,IF(quantity-1>0,quantity-1,1),0)) ";
				$SQL .= "WHERE registration_id = %s";
				
				$wpdb->query( $wpdb->prepare( $SQL, $registration_id ));

				event_espresso_cleanup_multi_event_registration_id_group_data();
				
			}
			
			if ( isset( $_REQUEST['primary'] ) && isset( $_REQUEST['p_id'] )) {
				return events_payment_page( $_REQUEST['primary'], $_REQUEST['p_id'] );
			}
			
		}
		// end delete attendee
		
		$counter = 0;
		$additional_attendees = NULL;

		$SQL = "SELECT  t1.*, t2.event_name, t2.question_groups, t2.event_meta FROM " . EVENTS_ATTENDEE_TABLE . " t1 ";
		$SQL .= "JOIN " . EVENTS_DETAIL_TABLE . " t2 ON t1.event_id = t2.id ";
		$SQL .= "WHERE t1.id = %d AND t1.registration_id = %s ";
		$SQL .= "ORDER BY t1.id";

		$results = $wpdb->get_results( $wpdb->prepare( $SQL, $id, $registration_id ));
		
		if ( ! empty( $results )) {
		
			$display_attendee_form = TRUE;

			foreach ($results as $result) {
							
				$id = $result->id;
				$registration_id = $result->registration_id;
				$lname = $result->lname;
				$fname = $result->fname;
				$address = $result->address;
				$city = $result->city;
				$state = $result->state;
				$zip = $result->zip;
				$email = $result->email;
				$payment = $result->payment;
				$phone = $result->phone;
				$date = $result->date;
				$payment_status = $result->payment_status;
				$txn_type = $result->txn_type;
				$txn_id = $result->txn_id;
				$amount_pd = $result->amount_pd;
				$quantity = $result->quantity;
				$payment_date = $result->payment_date;
				$event_id = $result->event_id;
				$event_name = stripslashes_deep($result->event_name);
				$question_groups = unserialize($result->question_groups);
				$event_meta = unserialize($result->event_meta);
				$counter = 1;
				
			}

			$response_source = $_POST;
			$SQL = "SELECT question_groups, event_meta FROM " . EVENTS_DETAIL_TABLE . " WHERE id = %d";
			
			if ( $questions = $wpdb->get_row( $wpdb->prepare( $SQL, $event_id ))) {
				$question_groups = maybe_unserialize($questions->question_groups);
				$event_meta = maybe_unserialize($questions->event_meta);
			} else {
				$question_groups = array();
				$event_meta = array();
			}

			if (isset($event_meta['add_attendee_question_groups']) && $event_meta['add_attendee_question_groups'] != NULL) {
				$question_groups = $event_meta['add_attendee_question_groups'];
			}
			
			if ( !is_array($question_groups) && !empty($question_groups)) {
				$question_groups = unserialize($question_groups);
			}

			$questions_in = '';
			foreach ($question_groups as $g_id) {
				$questions_in .= $g_id . ',';
			}
			$questions_in = substr($questions_in, 0, -1);
			
			$group_name = '';
			$counter = 0;

			//pull the list of questions that are relevant to this event
			$SQL = "SELECT q.*, q.id q_id, qg.group_name FROM " . EVENTS_QUESTION_TABLE . " q ";
			$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
			$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
			$SQL .= "WHERE qgr.group_id in ( '$questions_in' ) ";
			$SQL .= "AND q.admin_only = 'N' ";
			$SQL .= "ORDER BY qg.id, q.sequence ASC";
			
			$questions = $wpdb->get_results( $wpdb->prepare( $SQL ));

			$SQL = "SELECT question_id, answer FROM " . EVENTS_ANSWER_TABLE . " ans WHERE ans.attendee_id = %d";
			$answers = $wpdb->get_results( $wpdb->prepare( $SQL, $id ));


			$answer_a = array();
			foreach ($answers as $answer) {
				array_push($answer_a, $answer->question_id);
			}
			
			// Update the attendee information
			if ( ! empty( $_REQUEST['attendee_action'] ) && $_REQUEST['attendee_action'] == 'update_attendee' ) {

				$fname = ! empty($_POST['fname']) ? $_POST['fname'] : '';
				$lname = ! empty($_POST['lname']) ? $_POST['lname'] : '';
				$address = ! empty($_POST['address']) ? $_POST['address'] : '';
				$city = ! empty($_POST['city']) ? $_POST['city'] : '';
				$state = ! empty($_POST['state']) ? $_POST['state'] : '';
				$zip = ! empty($_POST['zip']) ? $_POST['zip'] : '';
				$phone = ! empty($_POST['phone']) ? $_POST['phone'] : '';
				$email = ! empty($_POST['email']) ? $_POST['email'] : '';
					
				$SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET fname=%s, lname=%s, address=%s, city=%s, state=%s, zip=%s, phone=%s, email=%s WHERE id =%d";
				$wpdb->query( $wpdb->prepare( $SQL, $fname, $lname, $address, $city, $state, $zip, $phone, $email, $id ));

				if ( $questions ) {
					foreach ( $questions as $question ) {			
						switch ( $question->question_type ) {
						
							case "TEXT" :
							case "TEXTAREA" :
							case "DROPDOWN" :
							case "SINGLE" :					
								$post_val = ($question->system_name != '') ? $response_source[$question->system_name] : $response_source[$question->question_type . '_' . $question->q_id];
								break;
								
							case "MULTIPLE" :					
								$post_val = '';
								if (!empty($response_source[$question->question_type . '_' . $question->id])) {
									for ( $i = 0; $i < count( $response_source[$question->question_type . '_' . $question->id] ); $i++ ) {
										$post_val .= trim( $response_source[$question->question_type . '_' . $question->id][$i] ) . ',';
									}
								}
								$post_val = substr( $post_val, 0, -1 );
								break;
								
						}
						
						if ( in_array( $question->q_id, $answer_a )) {
							$SQL = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer = %s WHERE attendee_id = %d AND question_id =%d";
							$wpdb->query( $wpdb->prepare( $SQL, $post_val, $id, $question->q_id ));
						} else {
							$sql = "INSERT INTO " . EVENTS_ANSWER_TABLE . " ( registration_id, answer, attendee_id, question_id ) VALUES ( %s, %s, %d, %d )";
							$wpdb->query( $wpdb->prepare( $SQL, $registration_id, $post_val, $id, $question->q_id ));
						}		
								
					}
				}

				//If this is not an attendee returing to edit thier details, then we need to return to the payment page
				if ( ! isset($_REQUEST['single'] )) {
					return events_payment_page( $_REQUEST['primary'], $_REQUEST['p_id'] );
				}
					
			}
			
		} else {		
			$display_attendee_form = FALSE;			
		}
		
		
?>

	<div id="event_espresso_registration_form" class="event-display-boxes ui-widget">
		<h3 class="event_title ui-widget-header ui-corner-top">
			<?php _e('Edit Registration','event_espresso'); ?>
		</h3>
		<div class="event_espresso_form_wrapper event-data-display ui-widget-content ui-corner-bottom">

<?php if ( $display_attendee_form ) : ?>

			
			<p>
				<strong><?php _e('Event:', 'event_espresso'); ?></strong> <?php echo $event_name; ?>
			</p>
			
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" class="espresso_form" id="registration_form">
<?php
				if ( count( $question_groups ) > 0 ) {
					
					$questions_in = '';
					foreach ( $question_groups as $g_id ) {
						$questions_in .= $g_id . ',';
					}
					$questions_in = substr( $questions_in, 0, -1 );
		
					$FILTER = isset( $event_meta['additional_attendee_reg_info'] ) && $event_meta['additional_attendee_reg_info'] == '2' && isset($_REQUEST['attendee_num']) && $_REQUEST['attendee_num'] > 1 ? ' AND qg.system_group = 1 ' : '';

					//pull the list of questions that are relevant to this event
					$SQL = "SELECT q.*, q.id q_id, at.*, qg.group_name, qg.show_group_description, qg.show_group_name ";
					$SQL .= "FROM " . EVENTS_QUESTION_TABLE . " q ";
					$SQL .= "LEFT JOIN " . EVENTS_ANSWER_TABLE . " at on q.id = at.question_id ";
					$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
					$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
					$SQL .= "WHERE qgr.group_id in ( '$questions_in' ) ";
					$SQL .= "AND ( at.attendee_id IS NULL OR at.attendee_id = %d ) ";
					$SQL .= "AND q.admin_only != 'Y' ";
					$SQL .= $FILTER;
					$SQL .= "ORDER BY qg.id, q.id ASC";

					if ( $questions = $wpdb->get_results( $wpdb->prepare( $SQL, $id )) ) {

						$existing_questions = '';
						foreach ( $questions as $question ) {
							$existing_questions .= $question->question_id . ',';
						}
						$existing_questions = substr( $existing_questions, 0, -1 );

						$SQL = "SELECT q.* FROM " . EVENTS_QUESTION_TABLE . " q ";
						$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr ON q.id = qgr.question_id ";
						$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg ON qg.id = qgr.group_id ";
						$SQL .= "WHERE qgr.group_id IN ( '$questions_in' ) ";
						$SQL .= "AND q.id NOT IN ( %s ) ";
						$SQL .= "GROUP BY q.question ";
						$SQL .= "ORDER BY qg.id, q.id ASC";


						if ( $questions_2 = $wpdb->get_results( $wpdb->prepare( $SQL, $existing_questions ))) {
							//Merge the existing questions with any missing questions
							array_merge( $questions, $questions_2 );
						}

						//Output the questions
						$question_displayed = array();
						$group_name = '';
						$counter = 0;
						$total_questions = count( $questions );
						
						foreach ( $questions as $question ) {
						
							if ( ! in_array( $question->id, $question_displayed )) {
							
								$question_displayed[] = $question->id;
								
								// if question group has changed, close prev group tags
								echo ( $group_name != '' && $group_name != $question->group_name ) ? '
					</fieldset>
				</div>' : '';
								
								// new group ?
								if ( $group_name != $question->group_name ) {
								
									$question->group_identifier = ! empty( $question->group_identifier ) ? ' id="' . $question->group_identifier . '"' : '';								
									$question->group_description = ! empty( $question->group_description ) ? $question->group_description : '';
										
									echo '
				<div class="event_questions"' . $question->group_identifier . '>
					<fieldset>';
									echo $question->show_group_name != 0 ? '
						<h3 class="section-title">' . $question->group_name . '</h3>' : '';
									echo $question->show_group_description != 0 && $question->group_description != '' ? '
						<p>
							' . $question->group_description . '
						</p>' : '';
									$group_name = $question->group_name;
								}

	                        echo '
						<p>
							';
	                        echo event_form_build_edit( $question, $question->answer, $show_admin_only = false );
	                        echo '
						</p>';

								$counter++;
								echo $counter == $total_questions ? '
					</fieldset>
				</div>' : '';
				
							}
						}
					}
					//end questions display
				}
?>

				<input type="hidden" name="id" value="<?php echo $id ?>" />
				<input type="hidden" name="r_id" value="<?php echo $registration_id ?>" />
				<input type="hidden" name="event_id" value="<?php echo $event_id ?>" />
				<input type="hidden" name="form_action" value="edit_attendee" />
				<input type="hidden" name="attendee_action" value="update_attendee" />
				<input type="hidden" name="regevent_action" value="register" />
				<input type="hidden" name="primary" value="<?php echo $_REQUEST['primary'] ?>" />
				<br/>
				
				<p class="espresso_confirm_registration">
					<input class="btn_event_form_submit" type="submit" name="submit" value="<?php _e('Update Record', 'event_espresso'); ?>" />
					<br/><br/>
				</p>
				
			</form>
			
<?php else : ?>
	
			<div class="event_espresso_error">
				<h3><?php _e('An error occured.', 'event_espresso'); ?></h3>
				<p>
					<?php _e('The requested attendee data could not be found.<br/>Please refresh the page and try again or contact the site admin if problem\'s persist.', 'event_espresso'); ?>
				</p>
			</div>
	
<?php endif; ?>

		</div><!-- / .event-display-boxes -->
	</div><!-- / .event_espresso_form_wrapper .event-data-display -->

<?php

	} else {
		_e('No attendee record was found.', 'event_espresso');
	}
}
