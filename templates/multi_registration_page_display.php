<?php
//This is the registration form.
//This is a template file for displaying a registration form for an event on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
global $this_event_id;
$this_event_id = $event_id;
$num_attendees = ' - ' . $_SESSION['espresso_session']['events_in_session'][$event_id]['attendee_quantity'] . __(' attendees', 'event_espresso');
$attendee_quantity = ' x '.sprintf(_n('%d attendee', '%d attendees', $meta['attendee_quantity'], 'event_espresso'), $meta['attendee_quantity']);
$display_description_on_multi_reg_page = isset( $org_options['display_description_on_multi_reg_page'] ) ? $org_options['display_description_on_multi_reg_page'] : 'N';
?>
<div id="event_espresso_registration_form" class="event-display-boxes multi-reg-page ui-widget">

	<h3 class="event_title ui-widget-header ui-corner-top" id="event_title-<?php echo $event_id; ?>">
		<?php echo stripslashes_deep($event_name) ?> <?php echo $is_active['status'] == 'EXPIRED' ? ' - <span class="expired_event">Event Expired</span>' : ''; ?>
	</h3>
	<div class="multi_regis_form_fields event-data-display ui-widget-content ui-corner-bottom" id="multi_regis_form_fields-<?php echo $event_id . '-' . $meta['price_id']; ?>">

		<?php
		//Show the description ?
		if ( $display_desc == "Y" && $display_description_on_multi_reg_page != 'N' ) {
			?>
			<?php //Featured image
			echo apply_filters('filter_hook_espresso_display_featured_image', $event_id, !empty($event_meta['event_thumbnail_url']) ? $event_meta['event_thumbnail_url'] : '');?>
			<div class="event_description">
			<?php
				//Code to show the actual description. The Wordpress function "wpautop" adds formatting to your description.
				echo espresso_format_content($event_desc); 
			?></div>
			<?php
		}//End display description
		//print_r( event_espresso_get_is_active($event_id));

		switch ($is_active['status']) {
			case 'EXPIRED': //only show the event description.
				_e('<h3 class="expired_event">This event has passed.</h3>', 'event_espresso');
				break;

			case 'REGISTRATION_CLOSED': //only show the event description.
				// if todays date is after $reg_end_date
				?>
				<p class="event_full"><strong><?php _e('We are sorry but registration for this event is now closed.', 'event_espresso'); ?></strong></p>
				<p class="event_full"><strong><?php _e('Please <a href="contact" title="contact us">contact us</a> if you would like to know if spaces are still available.', 'event_espresso'); ?></strong></p>
				<?php
				break;

			case 'REGISTRATION_NOT_OPEN': //only show the event description.
				// if todays date is after $reg_end_date
				// if todays date is prior to $reg_start_date
				?>
				<p class="event_full"><strong><?php _e('We are sorry but this event is not yet open for registration.', 'event_espresso'); ?></strong></p>
				<p class="event_full"><strong><?php _e('You will be able to register starting ' . event_espresso_no_format_date($reg_start_date, 'F d, Y'), 'event_espresso'); ?></strong></p>
				<?php
				break;

			default:
				/** This section shows the registration form if it is an active event * */
				if (is_array($add_attendee_question_groups) && count($add_attendee_question_groups) > 0 && $meta['attendee_number'] > 1) {
					$question_groups = $add_attendee_question_groups;
					$meta['additional_attendee_reg_info'] = 9; //this will override the deprecated way of doing the additional attendee questions
					$increase_attende_num = true;
				}
				//echo "additional_attendee_reg_info = ".$meta['additional_attendee_reg_info'];
				//echo "Attendee # ".$meta['attendee_number'];
				$attendee_number = $meta['attendee_number'];
				$is_primary = $event_counter == 1 ? 'primary' : 'additional';

				$price_group_att_counter = 1; //this will keep track of the attendee number inside each event inside each price type
				wp_nonce_field('reg_nonce', 'reg_form_nonce');
				//Outputs registration forms
				?>
				<div class="multi_regis_wrapper_attendee-<?php echo $is_primary; ?>">
					<div class="event-display-boxes">
						<?php
						echo '<h4 class="section-heading"><strong>'.__('Price Type:') . '</strong> ' . stripslashes_deep($meta['price_type']).$attendee_quantity.'</h4>';
						echo '<h3 class="section-heading">' . __('Attendee ', 'event_espresso') . $attendee_number . '</h3>';
		
						//This will be the main attendee
						//$meta['attendee_number'] = 1;
						$meta['attendee_number'] = $price_group_att_counter;
						//echo "Attendee # ".$attendee_number;
						
						//Displays the copy from dropdown
						if ($event_counter > 1) {
							echo event_espresso_copy_dd($event_id, $meta);
						}
						
						//Outputs the form questions.
						echo event_espresso_add_question_groups($question_groups, $events_in_session[$event_id], $event_id, 1, $meta);
						
						//Displays the copy to all button
						if ( $event_counter == 1 && $event_count > 1 || ($meta['attendee_quantity'] > 1 && $event_meta['additional_attendee_reg_info'] > 1) ) {
							?>
							<div class="event-messages ui-state-highlight">
								<p class="instruct" style="position:relative;padding:1em;">
									<span class="copy-all-button-wrapper" style="position:relative;z-index:10;">									
										<?php _e('Copy above information to all forms?', 'event_espresso'); ?> <button type="button" class="copy-all-button" value="<?php echo $event_id . '|' . $meta['price_id']; ?>"><?php _e('Yes', 'event_espresso'); ?></button>										
									</span>
									<span class="copy-all-button-success" style="display:none;position:absolute; top:.2em; left:0;padding:1em; border-radius:3px;z-index:1;background:#DCF3D9;"></span>
								</p>
							</div>
							<?php
						}
						
						?>
					</div>
				</div>
				<?php
				if ($meta['attendee_number'] == 1 || $increase_attende_num) {
					$meta['attendee_number']++;
					$attendee_number++;
				}

				//Outputs the shopping cart items
				if (function_exists('event_espresso_add_cart_item_groups')) {
					echo event_espresso_add_cart_item_groups($item_groups);
				}

				//Multiple Attendees
				if ($allow_multiple == "Y") {

					//This returns the additional attendee form fields.
					//
                        if ($meta['attendee_quantity'] > 1) {
						//echo 'attendee_quantity = '.$meta['attendee_quantity'];
						//If the "Personal Information only" question is selected in the event
						//then only show the registration form for the first attendee
						if ($event_meta['additional_attendee_reg_info'] == 1) {
							echo '<input name="num_people" type="hidden" value="' . $meta['attendee_quantity'] . '" />';
						} else {

							//this is a check for events that have been made before additional attendee questions functionality
							if (is_array($add_attendee_question_groups)) {
								$question_groups = $add_attendee_question_groups;
							}

							//The offset of 2 since this is attendee 2 and on
							//adding 1 since the primary attendee is added
							//in the above function call (c.a. line 104)
							//Used for "Attendee #" display
							for ($i = $attendee_number, $cnt = $meta['attendee_quantity'] + $attendee_number - 1; $i < $cnt; $i++) {
								$price_group_att_counter++;
								//echo 'price_group_att_counter = '.$price_group_att_counter;
								$meta['attendee_number'] = $price_group_att_counter;
								?>
								<hr class="hr_additional_attendee" />
								<div class="multi_regis_wrapper_attendee-additional">
									<div class="event-display-boxes">
										<?php
										echo '<h3 class="section-heading">' . __('Attendee ', 'event_espresso') . $i . '</h3>';
										echo event_espresso_copy_dd($event_id, $meta);
										echo event_espresso_add_question_groups($question_groups, $events_in_session[$event_id], $event_id, 1, $meta);
										?>
									</div>
								</div>
								<?php
							}
						}
					}
				} else {
					
				}//End allow multiple
				break;
		}//End Switch statement to check the status of the event
		?>
	</div>
</div>
