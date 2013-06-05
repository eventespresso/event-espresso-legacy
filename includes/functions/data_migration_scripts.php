<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	


function espresso_update_data_migrations_option( $func, $errors = array() ) {
	// check for existing  espresso_data_migrations option
	$existing_data_migrations = get_option( 'espresso_data_migrations' );
	// very first version was not an array, so make it an array
	$existing_data_migrations = is_array( $existing_data_migrations ) ? $existing_data_migrations : array( $existing_data_migrations );
	// loop thru array
	foreach ( $existing_data_migrations as $ver => $existing_data_migration ) {
		// check that $existing_data_migration is ALSO an array, or the value that was just "arrayified" above
		if ( is_array( $existing_data_migration )) {
			// these are data migrations from a more recent version of EE
			foreach ( $existing_data_migration as $key => $value ) {
				// ensure that $value is not serialzed (shouldn't be)
				$value = maybe_unserialize( $value );
				// if $value is an array of errors
				if ( ! is_array( $value )) {
					// this is NOT the newest format for tracking data migrations, so convert
					$existing_data_migrations[ $ver ][ $value ] = array();	
				}
			}
		} else {
			//this is a value from the first time we tracked data migrations
			// so we'll convert it to the new format where $value was the EE version, 
			// and array() means no errors (cuz we were not tracking them)
			$existing_data_migrations[ $ver ]['attendee_cost_table_fix_3_1_28'] = array();	
		}
		
	}
	$existing_data_migrations[ EVENT_ESPRESSO_VERSION ][ $func ] = $errors;	
	update_option( 'espresso_data_migrations', $existing_data_migrations );
	if ( ! empty( $errors )) {
		add_action( 'admin_notices', 'espresso_data_migration_update_error_notification' );
	}
	
//	$existing_data_migrations = get_option( 'espresso_data_migrations' );
//	printr( $existing_data_migrations, '$existing_data_migrations  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//	wp_die();
}





/**
* espresso_data_migration_update_error_notification
* 
* displays admin notice if errors occur during data migration
* 
* @since 3.1.28
* @return void
*/
function espresso_data_migration_update_error_notification() {
	$content = '
	<div class="updated">
		<p>
			' . __('An error has potentially occured while attempting to update some of the information in your database. Please contact Event Espresso Customer Service so that they may look further into this matter and ensure that all data is correct', 'event_espresso') . '
		</p>
	</div>
';
	echo $content;
}





/**
* espresso_copy_data_from_attendee_cost_table
* 
* moves data from the wp_events_attendee_cost table back into the attendee table
* calculates and/or adds data for is_primary,  final_price,  orig_price, total_cost, and amount_pd
* 
* @since 3.1.28
* @return void
*/
function espresso_copy_data_from_attendee_cost_table() {

	global $wpdb;
	$errors = array();
	
	$data_migrated_version = get_option( 'espresso_data_migrated' );
	if ( ! $data_migrated_version ) {

		// check for events_attendee_cost table
		$SQL = 'SHOW TABLES LIKE %s';
		if ( $wpdb->get_var( $wpdb->prepare( $SQL, $wpdb->prefix . 'events_attendee_cost' )) == $wpdb->prefix . 'events_attendee_cost' ) {
			// copy attendee costs to orig_price
			$SQL = "SELECT * FROM " . $wpdb->prefix . "events_attendee_cost";			
			if ( $results = $wpdb->get_results($SQL)) {
				foreach ( $results as $result ) {
					if ( $wpdb->update( 
							$wpdb->prefix . "events_attendee", 
							array( 'orig_price' => $result->cost,  'final_price' => $result->cost ), 
							array( 'id' => $result->attendee_id ),
							array( '%f', '%f' ),
							array( '%d' )
					) === FALSE ) {
						$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
					} 
				}						
			} else {
				$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
			}
		} else {
			$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
		}

		// get  reg IDs for all multi registration attendees that are NOT the primary attendee
		$SQL = "SELECT registration_id FROM " . $wpdb->prefix . "events_multi_event_registration_id_group WHERE registration_id != primary_registration_id";
		if ( ! $non_primary_registrants = $wpdb->get_results($SQL) ) {
			$non_primary_registrants = array();
		}

		// now grab ALL attendees
		$SQL = "SELECT registration_id, is_primary, final_price, quantity, total_cost, amount_pd, payment_status FROM " . $wpdb->prefix . "events_attendee";
		$attendees = $wpdb->get_results($SQL);
		if ( $attendees !== FALSE && ! empty( $attendees )) {
			// loop thru attendees
			foreach ( $attendees as $attendee ) {
				// check for non-primary attendees
				if ( in_array( $attendee->registration_id, $non_primary_registrants )) {
					// set "is_primary" to false
					if ( $wpdb->update(
							$wpdb->prefix . "events_attendee",
							array( 'is_primary' => 0,  'amount_pd' => 0.00 ),
							array( 'registration_id' => $attendee->registration_id ),
							array( '%d', '%f' ),
							array( '%s' )
					) === FALSE ) {
						$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
					}
				} else {

					//calculate new total
					$total_cost = $attendee->final_price * $attendee->quantity;
					// but keep the old one if it exists
					$total_cost = $attendee->total_cost != '0.00' ? $attendee->total_cost : $total_cost;
					//calculate new amount paid
					$amount_pd = $attendee->payment_status == 'Completed' ? $total_cost : 0.00;
					// but keep the old one if it exists
					$amount_pd = $attendee->amount_pd != '0.00' ? $attendee->amount_pd : $amount_pd;
					// update
					if ( $wpdb->update(
							$wpdb->prefix . "events_attendee",
							array( 'is_primary' => 1,  'total_cost' => $total_cost,  'amount_pd' => $amount_pd ),
							array( 'registration_id' => $attendee->registration_id ),
							array( '%d', '%f', '%f' ),
							array( '%s' )
					) === FALSE ) {
						$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
					}
				}
			}
		} else {
			$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
		}


		$SQL = "SELECT DISTINCT primary_registration_id FROM " . $wpdb->prefix . "events_multi_event_registration_id_group";
		$primary_registrants = $wpdb->get_results($SQL);
		if ( $primary_registrants !== FALSE && ! empty( $primary_registrants )) {	
			// now calculate a new event total for each primary_registrant
			foreach ( $primary_registrants as $primary_registrant ) {		
				//echo '<h4>primary_registration_id : ' . $primary_registrant->primary_registration_id . '</h4>';			
				// total cost for all attendees for a registration
				$reg_total = 0;
				//assume txn's are complete
				$txn_complete = TRUE;
				// first get all reg IDs associated with the primary reg
				$SQL = "SELECT * FROM " . $wpdb->prefix . "events_multi_event_registration_id_group ";
				$SQL .= "WHERE primary_registration_id = %s";
				if ( $registrations = $wpdb->get_results( $wpdb->prepare( $SQL, $primary_registrant->primary_registration_id ))) {
					// find payment info for those registrations in the attendee table
					foreach ( $registrations as $registration ) {				
						//echo '<h4>registration_id : ' . $registration->registration_id . '</h4>';			
						$SQL = "SELECT registration_id, is_primary, final_price, quantity, payment_status ";
						$SQL .= "FROM " . $wpdb->prefix . "events_attendee ";
						$SQL .= "WHERE registration_id = %s";							
						if ( $attendees = $wpdb->get_results( $wpdb->prepare( $SQL, $registration->registration_id ))) {
							// cycle thru attendees
							foreach ( $attendees as $attendee ) {
								//printr( $attendee, '$attendee  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
								// calculate attendee total
								$att_total = $attendee->final_price * $attendee->quantity;
								// add to total for the entire registration
								$reg_total += $att_total;
								
								// while we are here, update total cost and zero out amount paid for non-primary attendees
								if ( $primary_registrant->primary_registration_id != $attendee->registration_id || ! $attendee->is_primary ) {
									if ( $wpdb->update( 
											$wpdb->prefix . "events_attendee", 
											array( 'total_cost' => $att_total,  'amount_pd' => 0.00 ), 
											array( 'registration_id' => $attendee->registration_id ),
											array( '%f', '%f' ),
											array( '%s' )
									) === FALSE ) {
										$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
									}
													
								} else {
									$txn_complete = $attendee->payment_status == 'Completed' ? TRUE : FALSE;
								}				
							}	
						} else {
							// was it an actual error ?
							if ( $attendees === FALSE ) {
								$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
							}							
						}						
					}
				} else {
					$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
				}
				
				$amount_pd = $txn_complete ? $reg_total : 0.00;
				//echo '<h4>txn completed</h4>';	
				if ( $wpdb->update( 
						$wpdb->prefix . "events_attendee", 
						array( 'total_cost' => $reg_total, 'amount_pd' => $amount_pd ), 
						array( 'registration_id' => $primary_registrant->primary_registration_id ),
						array( '%f', '%f' ),
						array( '%s' )
				) === FALSE ) {
					$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
				}
										
			}	// end foreach ( $primary_registrants as $primary_registrant )
		}	// if ( $primary_registrants !== FALSE && ! empty( $primary_registrants ))
		
		espresso_update_data_migrations_option( __FUNCTION__, $errors );
		
	}
}





/**
* espresso_ensure_is_primary_is_set
* 
* ensure that for each registration or registration group
* that the is_primary field in the attendee table has been set properly
* 
* @since 3.1.29
* @return void
*/
function espresso_ensure_is_primary_is_set() {

	global $wpdb;
	$errors = array();
	
	$prev_session = FALSE;
	$SQL = "SELECT id, payment_status, attendee_session FROM " . $wpdb->prefix . "events_attendee ORDER BY id";
	if ( $attendees = $wpdb->get_results( $SQL )) {
		foreach ( $attendees as $attendee ) {		
			// compare attendee sessions and payment status	
			if ( $attendee->attendee_session != $prev_session || $attendee->payment_status == 'Incomplete' ) {
				// IS the primary attendent
				if ( $wpdb->update( 
						$wpdb->prefix . "events_attendee", 
						array( 'is_primary' => 1 ), 
						array( 'id' => $attendee->id ),
						array( '%d' ),
						array( '%d' )
				) === FALSE ) {
					$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
				}
							
			} else {
				// NOT the primary
				if ( $wpdb->update( 
						$wpdb->prefix . "events_attendee", 
						array( 'is_primary' => 0 ), 
						array( 'id' => $attendee->id ),
						array( '%d' ),
						array( '%d' )
				) === FALSE ) {
					$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
				}				
			}
			// set prev_session to current
			$prev_session = $attendee->attendee_session;
		}
	} else {
		$errors[] = __FUNCTION__ . ' : line #' . __LINE__ . ' : ' . $wpdb->last_error;
	}
	
	espresso_update_data_migrations_option( __FUNCTION__, $errors );

}


