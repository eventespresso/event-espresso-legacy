<?php
//Note: This entire function can be overridden using the "Custom Files" addon
if (!function_exists('multi_register_attendees')) {

	function multi_register_attendees( $single_event_id = NULL, $event_id_sc =0, $meta = array(), $event = FALSE ) {

		global $wpdb, $org_options;
		
		
		
		$events_in_session = $_SESSION['espresso_session']['events_in_session'];
		$event_count = count($events_in_session);
		
		static $event_counter = 1;
		static $attendee_number = 1;

		//The following variables are used to get information about your organization
		$event_page_id = $org_options['event_page_id'];
		$Organization = isset($org_options['organization']) && !empty($org_options['organization']) ? stripslashes_deep($org_options['organization']) : '';
		$Organization_street1 = isset($org_options['organization_street1']) && !empty($org_options['organization_street1']) ? $org_options['organization_street1'] : '';
		$Organization_street2 = isset($org_options['organization_street2']) && !empty($org_options['organization_street2']) ? $org_options['organization_street2'] : '';
		$Organization_city = isset($org_options['organization_city']) && !empty($org_options['organization_city']) ? $org_options['organization_city'] : '';
		$Organization_state = isset($org_options['organization_state']) && !empty($org_options['organization_state']) ? $org_options['organization_state'] : '';
		$Organization_zip = isset($org_options['organization_zip']) && !empty($org_options['organization_zip']) ? $org_options['organization_zip'] : '';
		$contact = isset($org_options['contact_email']) && !empty($org_options['contact_email']) ? $org_options['contact_email'] : '';
		$registrar = isset($org_options['contact_email']) && !empty($org_options['contact_email']) ? $org_options['contact_email']: '';
		$currency_format = isset($org_options['currency_format']) && !empty($org_options['currency_format']) ?  $org_options['currency_format']: '';

		$message = isset($org_options['message']) && !empty($org_options['message']) ? $org_options['message'] : '';
		$paypal_id = isset($org_options['paypal_id']) && !empty($org_options['paypal_id']) ? $org_options['paypal_id'] : '';

		if ( ! $event ) {
		
			// the key we will eventually use in our query to find the event
			$ID = $event_id_sc != '0' ? $event_id_sc : $_REQUEST['event_id'];

			if (!empty($_REQUEST['event_id_time'])) {
				$pieces = explode('|', $_REQUEST['event_id_time'], 3);
				$ID = $pieces[0];
				$start_time = $pieces[1];
				$time_id = $pieces[2];
				$time_selected = true;
			}

			//If a single event needs to be displayed, get its ID
			if ( $single_event_id != NULL ) {
				$sql = "SELECT id FROM " . EVENTS_DETAIL_TABLE;
				$sql .= " WHERE event_identifier = %s";
				$sql .= " LIMIT 0,1";
				$result = $wpdb->get_row( $wpdb->prepare( $sql, $single_event_id ));
				$ID = $result->id;
			}
			
			
			//Build event queries
			$sql = "SELECT * FROM " . EVENTS_DETAIL_TABLE;
			$sql.= " WHERE is_active='Y' ";
			$sql.= " AND event_status != 'D' ";
			$sql.= " AND id =%d LIMIT 0,1";
			
			//Support for diarise
			if ( ! empty( $_REQUEST['post_event_id'] )) {			
				$sql = "SELECT * FROM " . EVENTS_DETAIL_TABLE;
				$sql .= " WHERE post_id = %d ";
				$sql .= " LIMIT 0,1";
				$ID = absint( $_REQUEST['post_event_id'] );
			}
			
			$event = $wpdb->get_row( $wpdb->prepare( $sql, $ID ));
			
		}


		//Build the registration page
		if ( $event ) {
			
			//These are the variables that can be used throughout the regsitration page
			$event_id = $event->id;
			$event_name = stripslashes_deep($event->event_name);
			$event_desc = stripslashes_deep($event->event_desc);
			$display_desc = $event->display_desc;
			$display_reg_form = $event->display_reg_form;
			$event_address = $event->address;
			$event_address2 = $event->address2;
			$event_city = $event->city;
			$event_state = $event->state;
			$event_zip = $event->zip;
			$event_country = $event->country;
			$event_description = stripslashes_deep($event->event_desc);
			$event_identifier = $event->event_identifier;
			$event_cost = ! empty($event->event_cost) ? $event->event_cost : 0;
			//echo '<h4>$event_cost : ' . $event_cost . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';

			$member_only = $event->member_only;
			$reg_limit = $event->reg_limit;
			$allow_multiple = $event->allow_multiple;
			$start_date = $event->start_date;
			$end_date = $event->end_date;
			$allow_overflow = $event->allow_overflow;
			$overflow_event_id = $event->overflow_event_id;

			$virtual_url = stripslashes_deep($event->virtual_url);
			$virtual_phone = stripslashes_deep($event->virtual_phone);

			//Address formatting
			$location = ($event_address != '' ? $event_address : '') . ($event_address2 != '' ? '<br />' . $event_address2 : '') . ($event_city != '' ? '<br />' . $event_city : '') . ($event_state != '' ? ', ' . $event_state : '') . ($event_zip != '' ? '<br />' . $event_zip : '') . ($event_country != '' ? '<br />' . $event_country : '');

			//Google map link creation
			$google_map_link = espresso_google_map_link(array('address' => $event_address, 'city' => $event_city, 'state' => $event_state, 'zip' => $event_zip, 'country' => $event_country, 'text' => 'Map and Directions', 'type' => 'text'));

			$reg_start_date = $event->registration_start;
			$reg_end_date = $event->registration_end;
			$today = date("Y-m-d");

			$reg_limit = $event->reg_limit;
			$additional_limit = $event->additional_limit;


			$question_groups = unserialize($event->question_groups);
			$item_groups = unserialize($event->item_groups);

			$event_meta = maybe_unserialize($event->event_meta);
			//printr( $event_meta, '$event_meta  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
			//This function gets the status of the event.
			//$is_active = event_espresso_get_status($event_id, $event_meta);
			$is_active = event_espresso_get_is_active($event_id, $event_meta);

			//If the coupon code system is intalled then use it
			$use_coupon_code = function_exists('event_espresso_coupon_registration_page') ? $event->use_coupon_code : FALSE;
			//If the groupon code addon is installed, then use it
			$use_groupon_code = function_exists('event_espresso_groupon_payment_page') ? $event->use_groupon_code : FALSE;


			//Set a default value for additional limit
			if ( $additional_limit == '' ) {
				$additional_limit = '5';
			}

			$add_attendee_question_groups = $event_meta['add_attendee_question_groups'];


			if ($org_options['use_captcha'] == 'Y' && isset( $_REQUEST['edit_details'] ) && $_REQUEST['edit_details'] != 'true') {
				?>
				<script type="text/javascript">
					var RecaptchaOptions = {
						theme : '<?php echo $org_options['recaptcha_theme'] == '' ? 'red' : $org_options['recaptcha_theme']; ?>',
						lang : '<?php echo $org_options['recaptcha_language'] == '' ? 'en' : $org_options['recaptcha_language']; ?>'
					};
				</script>
				<?php
			}
			
//*************  This is the start of the registration form. This is where you can start editing your display. *************
			
			//Get the number of attendees
			$num_attendees = get_number_of_attendees_reg_limit($event_id, 'num_attendees'); 
			//Gets a count of the available spaces
			$available_spaces = get_number_of_attendees_reg_limit($event_id, 'available_spaces'); 
			//Gets the number of available spaces
			$number_available_spaces = get_number_of_attendees_reg_limit($event_id, 'number_available_spaces'); 
			//Please visit http://eventespresso.com/forums/?p=247 for available parameters for the get_number_of_attendees_reg_limit() function.
			
			if ($available_spaces == "Unlimited" || $available_spaces >= $number_available_spaces) {
				//(Shows the regsitration form if enough spaces exist)
				
				// SOLD OUT !!!
				if ($num_attendees >= $reg_limit) {
					?>
					<div class="espresso_event_full event-display-boxes" id="espresso_event_full-<?php echo $event_id; ?>">
						<h3 class="event_title"><?php echo stripslashes_deep($event_name) ?></h3>
						<p class="event_full"><strong><?php _e('We are sorry but this event has reached the maximum number of attendees!', 'event_espresso'); ?></strong></p>
						<p class="event_full"><strong><?php _e('Please check back in the event someone cancels.', 'event_espresso'); ?></strong></p>
						<p class="num_attendees"><?php _e('Current Number of Attendees:', 'event_espresso'); ?> <?php echo $num_attendees ?></p>

						<?php
						// is there an overflow event ????
						if (($num_attendees >= $reg_limit) && ($allow_overflow == 'Y' && $overflow_event_id != 0)) {
						?>
							<p id="register_link-<?php echo $overflow_event_id ?>" class="register-link-footer">
								<a id="a_register_link-<?php echo $overflow_event_id ?>" 
									class="a_register_link" 
									href="<?php echo espresso_reg_url($overflow_event_id); ?>" 
									title="<?php echo stripslashes_deep($event_name) ?>"
								>
									<?php _e('Join Waiting List', 'event_espresso'); ?>
								</a>
							</p>
						<?php } ?>
					</div>
					<?php
					// event_espresso_clear_session();
					
				} else {
					 //If enough spaces exists then serve up the registration form
					//As of version 3.0.17 the registration details have been moved to registration_form.php
					include('multi_registration_page_display.php');
					$event_counter++;
					echo '<input type="hidden" name="regevent_action" value="post_multi_attendee" />';
				}//End if ($num_attendees >= $reg_limit) (Shows the regsitration form if enough spaces exist)
			}//End ($available_spaces == "Unlimited" || $available_spaces >= $number_available_spaces)
		}//End Build the registration page
		else {//If there are no results from the query, display this message
			_e('<h3>This event has expired or is no longer available.</h3>', 'event_espresso');
		}
		//Check to see how many database queries were performed
		//echo '<p>Database Queries: ' . get_num_queries() .'</p>';
	}

}

