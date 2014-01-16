<?php
if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

function attendee_edit_record() {

	global $wpdb, $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	
	$id = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : FALSE;
	$registration_id = isset( $_REQUEST['registration_id'] ) ? wp_strip_all_tags( $_REQUEST['registration_id'] ) : FALSE;
	$req_primary = isset( $_REQUEST['primary'] ) ? wp_strip_all_tags( absint($_REQUEST['primary']) ) : $id;
	$req_p_id = isset( $_REQUEST['p_id'] ) ? wp_strip_all_tags( absint($_REQUEST['p_id']) ) : FALSE;
	
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
			
			if ( isset( $req_primary ) && isset( $req_p_id )) {
				return events_payment_page( $req_primary/*, $req_p_id*/ );
			}
			
		}
		// end delete attendee
		
		$counter = 0;
		$additional_attendees = NULL;

		$SQL = "SELECT  att.*, evt.event_name, evt.question_groups, evt.event_meta ";
		$SQL .= "FROM " . EVENTS_ATTENDEE_TABLE . " att ";
		$SQL .= "JOIN " . EVENTS_DETAIL_TABLE . " evt ON att.event_id = evt.id ";
		$SQL .= "WHERE att.id = %d AND att.registration_id = %s ";
		$SQL .= "ORDER BY att.id";

		$attendee = $wpdb->get_row( $wpdb->prepare( $SQL, $id, $registration_id ));

		if ( $attendee != FALSE ) {
		
			$display_attendee_form = TRUE;

			$id = $attendee->id;
			$registration_id = $attendee->registration_id;
			$lname = $attendee->lname;
			$fname = $attendee->fname;
			$address = $attendee->address;
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
			$amount_pd = $attendee->amount_pd;
			$quantity = $attendee->quantity;
			$payment_date = $attendee->payment_date;
			$event_id = $attendee->event_id;
			$event_name = stripslashes_deep($attendee->event_name);
			$question_groups = maybe_unserialize($attendee->question_groups);
			$event_meta = maybe_unserialize($attendee->event_meta);

			if ( ! $attendee->is_primary && isset($event_meta['add_attendee_question_groups']) && $event_meta['add_attendee_question_groups'] != NULL ) {
				$question_groups = $event_meta['add_attendee_question_groups'];
			}
			//printr( $question_groups, '$question_groups  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );	
			
			$questions_in = '';
			foreach ($question_groups as $g_id) {
				$questions_in .= $g_id . ',';
			}
			$questions_in = substr($questions_in, 0, -1);
//			echo '<h4>$questions_in : ' . $questions_in . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
			
			$group_name = '';
			$counter = 0;

			//pull the list of questions that are relevant to this event
			$SQL = "SELECT q.*, q.id AS q_id, qg.group_name FROM " . EVENTS_QUESTION_TABLE . " q ";
			$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
			$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
			$SQL .= "WHERE qgr.group_id in ( $questions_in ) ";
			$SQL .= "AND q.admin_only != 'Y' ";
			$SQL .= "ORDER BY qg.group_order, qg.id, q.sequence ASC";
			
			$questions = $wpdb->get_results( $wpdb->prepare( $SQL, NULL ));
//			echo '<h4>last_query : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//			printr( $questions, '$questions  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

			$SQL = "SELECT question_id, answer FROM " . EVENTS_ANSWER_TABLE . " ans WHERE ans.attendee_id = %d";
			$answers = $wpdb->get_results( $wpdb->prepare( $SQL, $id ));

			$answer_a = array();
			foreach ($answers as $answer) {
				array_push($answer_a, $answer->question_id);
			}
			
			// Update the attendee information
			if ( isset( $_REQUEST['attendee_action'] ) && $_REQUEST['attendee_action'] == 'update_attendee' ) {

				$fname = ! empty($_POST['fname']) ? ee_sanitize_value($_POST['fname'] ): '';
				$lname = ! empty($_POST['lname']) ? ee_sanitize_value($_POST['lname']) : '';
				$address = ! empty($_POST['address']) ? ee_sanitize_value($_POST['address']) : '';
				$city = ! empty($_POST['city']) ? ee_sanitize_value($_POST['city']) : '';
				$state = ! empty($_POST['state']) ? ee_sanitize_value($_POST['state']) : '';
				$zip = ! empty($_POST['zip']) ? ee_sanitize_value($_POST['zip']) : '';
				$phone = ! empty($_POST['phone']) ? ee_sanitize_value($_POST['phone']) : '';
				$email = ! empty($_POST['email']) ? ee_sanitize_value($_POST['email']) : '';
					
				$SQL = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET fname=%s, lname=%s, address=%s, city=%s, state=%s, zip=%s, phone=%s, email=%s WHERE id =%d";
				$wpdb->query( $wpdb->prepare( $SQL, $fname, $lname, $address, $city, $state, $zip, $phone, $email, $id ));
				
				if ( $questions ) {				
					foreach ( $questions as $question ) {			
						switch ( $question->question_type ) {
						
							case "TEXT" :
							case "TEXTAREA" :
							case "DROPDOWN" :
							case "SINGLE" :					
								$post_val = ($question->system_name != '') ? ee_sanitize_value($_POST[$question->system_name]) : ee_sanitize_value($_POST[$question->question_type . '_' . $question->q_id]);
								break;
								
							case "MULTIPLE" :					
								$post_val = '';
								if (!empty($_POST[$question->question_type . '_' . $question->id])) {
									for ( $i = 0; $i < count( $_POST[$question->question_type . '_' . $question->id] ); $i++ ) {
										$post_val .= trim( ee_sanitize_value($_POST[$question->question_type . '_' . $question->id][$i]) ) . ',';
									}
								}
								$post_val = substr( $post_val, 0, -1 );
								break;
								
						}
						
						$post_val = html_entity_decode( $post_val, ENT_QUOTES, 'UTF-8' );
						
						if ( in_array( $question->q_id, $answer_a )) {
							$SQL = "UPDATE " . EVENTS_ANSWER_TABLE . " SET answer = %s WHERE attendee_id = %d AND question_id =%d";
							$wpdb->query( $wpdb->prepare( $SQL, $post_val, $id, $question->q_id ));
						} else {
							$SQL = "INSERT INTO " . EVENTS_ANSWER_TABLE . " ( registration_id, answer, attendee_id, question_id ) VALUES ( %s, %s, %d, %d )";
							$wpdb->query( $wpdb->prepare( $SQL, $registration_id, $post_val, $id, $question->q_id ));
						}	
						
						//echo '<h4>LQ : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';	
								
					}
				}

				//If this is not an attendee returing to edit their details, then we need to return a message.
				if ( ! isset($_REQUEST['single'] )) {
					if ( espresso_registration_id( $req_primary ) == $registration_id && espresso_registration_id( $id ) == $registration_id ){
						if($payment_status == 'Completed'){
							_e('Your registration details have been updated.', 'event_espresso');
							return;
						}else{
							return events_payment_page( $req_primary );
							exit();
						}
					}else{
						_e('Sorry, it seems there was an error verifying the attendee id or primary attendee id record.', 'event_espresso');
						return;
					}
					
				}
					
			}
			
		} else {		
			$display_attendee_form = FALSE;			
		}
		

?>

	<div id="edit-attendee-record-dv" class="event-display-boxes ui-widget">
		<h3 class="event_title ui-widget-header ui-corner-top">
			<?php _e('Edit Registration','event_espresso'); ?>
		</h3>
		<div class="event_espresso_form_wrapper event-data-display ui-widget-content ui-corner-bottom">

<?php if ( $display_attendee_form ) : ?>

			
			<p>
				<strong><?php _e('Event:', 'event_espresso'); ?></strong> <?php echo $event_name; ?>
			</p>
			
			<form method="post" action="<?php echo home_url() ?>/?page_id=<?php echo $org_options['event_page_id']; ?>" class="espresso_form" id="registration_form">
<?php
				if ( count( $question_groups ) > 0 ) {
					
					$questions_in = '';
					foreach ( $question_groups as $g_id ) {
						$questions_in .= $g_id . ',';
					}
					$questions_in = substr( $questions_in, 0, -1 );
//					echo '<h4>$questions_in : ' . $questions_in . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
		
					$FILTER = isset( $event_meta['additional_attendee_reg_info'] ) && $event_meta['additional_attendee_reg_info'] == '2' && isset($_REQUEST['attendee_num']) && $_REQUEST['attendee_num'] > 1 ? ' AND qg.system_group = 1 ' : '';

			
					//pull the list of questions that are relevant to this event
					$SQL = "SELECT q.*, q.id AS q_id, at.*, qg.group_name, qg.show_group_description, qg.show_group_name ";
					$SQL .= "FROM " . EVENTS_QUESTION_TABLE . " q ";
					$SQL .= "LEFT JOIN " . EVENTS_ANSWER_TABLE . " at on q.id = at.question_id ";
					$SQL .= "JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
					$SQL .= "JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
					$SQL .= "WHERE qg.id in ( $questions_in ) ";
					$SQL .= "AND (  at.attendee_id IS NULL OR at.attendee_id = %d ) ";
					$SQL .= "AND q.admin_only != 'Y' ";
					$SQL .= $FILTER;
					$SQL .= "ORDER BY qg.group_order, qg.id, q.sequence ASC";

					if ( $questions = $wpdb->get_results( $wpdb->prepare( $SQL, $id )) ) {

//						printr( $questions, '$questions  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//						echo '<h4>last_query : ' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
			

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

								echo event_form_build_edit( $question, $question->answer, $show_admin_only = FALSE );

								$counter++;
								echo $counter == $total_questions ? '
					</fieldset>
				</div>' : '';
				
							}
						}
					}
					//end questions display
				}
				
//	registration_id=1-5072fa1b52696
//	id=318
//	regevent_action=register
//	form_action=edit_attendee
//	primary=318
//	event_id=1
//	coupon_code=
//	groupon_code=
//	attendee_num=1				
				
?>

				<input type="hidden" name="id" value="<?php echo $id ?>" />
				<input type="hidden" name="r_id" value="<?php echo $registration_id ?>" />
				<input type="hidden" name="event_id" value="<?php echo $event_id ?>" />
				<input type="hidden" name="attendee_action" value="update_attendee" />
				<input type="hidden" name="regevent_action" value="edit_attendee" />
				<input type="hidden" name="primary" value="<?php echo $req_primary ?>" />
				
				<p class="event_form_submit">
					<input class="event-form-submit-btn" type="submit" name="submit" value="<?php _e('Update Record', 'event_espresso'); ?>" />
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
