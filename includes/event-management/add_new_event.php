<?php
if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

function add_new_event() {
	global $wpdb, $org_options, $espresso_premium;
	if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
		espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => ''));
	}
	//This line keeps the notices from displaying twice
	if (did_action('espresso_admin_notices') == false)
		do_action('espresso_admin_notices');
	?>


	<!--New event display-->

	<div id="side-info-column" class="inner-sidebar event-espresso_page_events">

	  <div id="side-sortables" class="meta-box-sortables ui-sortable">
			<div id="submitdiv" class="postbox">
				<div class="handlediv" title="Click to toggle"><br />
				</div>
				<h3 class="hndle"> <span>
	<?php _e('New Event', 'event_espresso'); ?>
					</span> </h3>
				<div class="inside">
					<div class="submitbox" id="submitpost"><!-- /minor-publishing -->
						<div id="major-publishing-actions" class="clearfix">
							<div id="delete-action"> <a class="submitdelete deletion" href="admin.php?page=events" onclick="return confirm('<?php _e('Are you sure you want to cancel ' . $event_name . '?', 'event_espresso'); ?>')">
	<?php _e('Cancel', 'event_espresso'); ?>
								</a> </div>

							<div id="publishing-action">
	<?php wp_nonce_field('espresso_form_check', 'ee_add_new_event'); ?>
								<input class="button-primary" type="submit" name="Submit" value="<?php _e('Submit New Event', 'event_espresso'); ?>" id="add_new_event" />

							</div>

							<!-- /publishing-action -->
						</div>
						<?php
						if (function_exists('espresso_is_admin') && espresso_is_admin() == true && $espresso_premium == true) {
							if (function_exists('espresso_manager_list')) {
								echo '<div style="padding:10px 0 10px 10px" class="clearfix">' . espresso_manager_list($wp_user) . '</div>';
							}
						}
						?>
						<!-- /major-publishing-actions -->
					</div>
					<!-- /submitpost -->
				</div>
				<!-- /inside -->
			</div>
			<!-- /submitdiv -->

			<?php
			$values = array(array('id' => 'Y', 'text' => __('Yes', 'event_espresso')), array('id' => 'N', 'text' => __('No', 'event_espresso')));

			$advanced_options = '';
			if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/event-management/advanced_settings.php')) {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "includes/admin-files/event-management/advanced_settings.php");
			} else {
				//Display Lite version options
				$status = array(array('id' => 'A', 'text' => __('Active', 'event_espresso')), array('id' => 'D', 'text' => __('Deleted', 'event_espresso')));
				$advanced_options = '<p><strong>' . __('Advanced Options:', 'event_espresso') . '</strong></p>'
								. '<p>' . __('Is this an active event? ', 'event_espresso') . __(select_input('is_active', $values, $is_active)) . '</p>'
								. '<p>' . __('Display  description? ', 'event_espresso') . select_input('display_desc', $values, $display_desc) . '</p>'
								. '<p>' . __('Display  registration form? ', 'event_espresso') . select_input('display_reg_form', $values, $display_reg_form) . '</p>';
			}//Display Lite version options - End

			$reg_limit = isset($reg_limit) ? $reg_limit : '';
			$additional_limit = isset($additional_limit) ? $additional_limit : '';
			postbox('event-status', 'Event Options', '<p class="inputundersmall"><label for="reg-limit">' . __('Attendee Limit: ', 'event_espresso') . '</label> <input id="reg-limit" name="reg_limit" size="10" type="text" value="' . $reg_limit . '"><br />' .
							'<span>(' . __('leave blank for unlimited', 'event_espresso') . ')</span></p>' .
							'<p class="clearfix" style="clear: both;"><label for="group-reg">' . __('Allow group registrations? ', 'event_espresso') . '</label> ' . select_input('allow_multiple', $values, 'N', 'id="group-reg"') .
							'<p class="inputundersmall"><label for="max-registrants">' . __('Max Group Registrants: ', 'event_espresso') . '</label> <input id="max-registrants" type="text" name="additional_limit" value="' . $additional_limit . '" size="4">' .
							$advanced_options
			);

			//Featured image section
			if (function_exists('espresso_featured_image_event_admin') && $espresso_premium == true) {
				espresso_featured_image_event_admin();
			}

			/*
			 * Added for seating chart addon
			 */
			if (defined('ESPRESSO_SEATING_CHART')) {
				?>
				<div style="display: block;" id="seating_chart-options" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"><span>
		<?php _e('Seating chart', 'event_espresso'); ?>
						</span></h3>
					<div class="inside">
						<p>
							<select name="seating_chart_id" id="seating_chart_id" style="float:none; width:200px;" class="chzn-select">
								<option value="0" ><?php _e('None', 'event_espresso'); ?></option>
								<?php
								$seating_charts = $wpdb->get_results("select * from " . EVENTS_SEATING_CHART_TABLE . " order by name");
								foreach ($seating_charts as $seating_chart) {
									?>
									<option value="<?php echo $seating_chart->id; ?>"><?php echo $seating_chart->name; ?></option>
			<?php
		}
		?>
							</select>
						</p>
					</div>
				</div>
				<?php
			}
			/*
			 * End
			 */

			###### Modification by wp-developers to introduce attendee pre-approval requirement ##########
			if (isset($org_options['use_attendee_pre_approval']) && $org_options['use_attendee_pre_approval'] == 'Y' && $espresso_premium == true) {
				?>
				<div id="attendee-pre-approval-options" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"> <span>
							<?php _e('Attendee pre-approval required?', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<p class="pre-approve">
							<?php
							$pre_approval_values = array(array('id' => '1', 'text' => __('Yes', 'event_espresso')), array('id' => '0', 'text' => __('No', 'event_espresso')));
							echo select_input("require_pre_approval", $pre_approval_values, "0");
							?>
						</p>
					</div>
				</div>
				<?php
			}
			########## END #################################


			if (function_exists('espresso_ticket_dd') && $espresso_premium == true) {
				?>
				<div  id="ticket-options" class="postbox">
					<div class="handlediv" title="Click to toggle"><br>
					</div>
					<h3 class="hndle"> <span>
		<?php _e('Custom Tickets', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<p><?php echo espresso_ticket_dd(); ?></p>
					</div>
				</div>
				<!-- /ticket-options -->
				<?php
			}

			if (function_exists('espresso_certificate_dd') && $espresso_premium == true) {
				?>
				<div  id="certificate-options" class="postbox">
					<div class="handlediv" title="Click to toggle"><br>
					</div>
					<h3 class="hndle"> <span>
		<?php _e('Custom Certificates', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<p><?php echo espresso_certificate_dd(); ?></p>
					</div>
				</div>
				<!-- /certificate-options -->
				<?php
			}


			if (get_option('events_members_active') == 'true' && $espresso_premium == true) {
				?>
				<div id="member-options" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"> <span>
		<?php _e('Member Options', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<p><?php echo event_espresso_member_only(); ?></p>
					</div>
				</div>
				<!-- /event-category -->
			<?php
			}

			if (get_option('event_mailchimp_active') == 'true' && $espresso_premium == true) {
				MailChimpView::event_list_selection();
			}
			?>
						<?php if (function_exists('espresso_fb_createevent') == 'true' && $espresso_premium == true) { ?>
				<div id="event-meta" class="postbox">
					<div class="handlediv" title="Click to toggle"><br>
					</div>
					<h3 class="hndle"> <span>
		<?php _e('Post to Facebook', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<input type="checkbox" name="espresso_fb" id="espresso_fb" />
		<?php _e('Post to Facebook', 'event_espresso'); ?>
					</div>
				</div>
						<?php } ?>
			<div  id="event-categories" class="postbox">
				<div class="handlediv" title="Click to toggle"><br />
				</div>
				<h3 class="hndle"> <span>
			<?php _e('Event Category', 'event_espresso'); ?>
					</span> </h3>
				<div class="inside"> <?php echo event_espresso_get_categories(); ?> </div>
			</div>
			<!-- /event-category -->

			<?php
			if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/event-management/promotions_box.php')) {
				require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "includes/admin-files/event-management/promotions_box.php");
			}

			if (get_option('events_groupons_active') == 'true' && $espresso_premium == true) {
				?>
				<div id="groupon-options" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"> <span>
				<?php _e('Groupon Options', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<p><?php echo event_espresso_add_new_event_groupon($use_groupon_code); ?></p>
					</div>
				</div>
				<!-- /groupon-options -->
			<?php }
			echo espresso_event_question_groups(empty($question_groups) ? array() : $question_groups); ?>
			<!-- /event-questions -->

						<?php
						if (function_exists('espresso_personnel_cb') && isset($org_options['use_personnel_manager']) && $org_options['use_personnel_manager'] == 'Y' && $espresso_premium == true) {
							?>
				<div id="event-category" class="postbox">
					<div class="handlediv" title="Click to toggle"><br>
					</div>
					<h3 class="hndle"> <span>
				<?php _e('Event Staff / Speakers', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside"> <?php echo espresso_personnel_cb($event_id); ?> </div>
				</div>
	<?php } ?>

	  </div>
	  <!-- /side-sortables -->
	</div>
	<!-- /side-info-column -->

	<!-- Left Column-->
	<div id="post-body">
	  <div id="post-body-content">
			<div id="titlediv"> <strong>
						<?php _e('Event Title', 'event_espresso'); ?>
				</strong>
				<div id="titlewrap">
					<label class="screen-reader-text" for="title">
	<?php _e('Event Title', 'event_espresso'); ?>
					</label>
					<input type="text" name="event" size="30" tabindex="1" value="<?php echo isset($event_name) ? $event_name : ''; ?>" id="title" autocomplete="off" />
				</div>

				<!-- /titlewrap -->
				<div class="inside">
					<div id="edit-slug-box"> <strong>
						<?php _e('Unique Event Identifier:', 'event_espresso'); ?>
						</strong>
						<input type="text" size="30" tabindex="2" name="event_identifier" id="event_identifier" value ="<?php echo isset($event_identifier) ? $event_identifier : ''; ?>" />
						<?php echo '<a href="#" class="button" onclick="prompt(&#39;Event Shortcode:&#39;, \'[SINGLEEVENT single_event_id=&#34;\' + jQuery(\'#event_identifier\').val() + \'&#34;]\'); return false;">' . __('Get Shortcode') . '</a>' ?>
						<?php
						$org_options['event_page_id'] = isset($org_options['event_page_id']) ? $org_options['event_page_id'] : '';
						$event_id = isset($event_id) ? $event_id : '';
						echo '<a href="#" class="button" onclick="prompt(&#39;Event URL:&#39;, \'' . espresso_reg_url($event_id) . '\'); return false;">' . __('Get URL') . '</a>'
						?>
					</div>
				</div>
				<!-- /edit-slug-box -->
			</div>
			<!-- /titlediv -->
			<div id="descriptiondivrich" class="postarea"> <strong>
				<?php _e('Event Description', 'event_espresso'); ?>
				</strong>
				<?php
				/*
				  This is the editor used by WordPress. It is very very hard to find documentation for this thing, so I pasted everything I could find below.
				  param: string $content Textarea content.
				  param: string $id Optional, default is 'content'. HTML ID attribute value.
				  param: string $prev_id Optional, default is 'title'. HTML ID name for switching back and forth between visual editors.
				  param: bool $media_buttons Optional, default is true. Whether to display media buttons.
				  param: int $tab_index Optional, default is 2. Tabindex for textarea element.
				 */
				//the_editor($content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2)
				the_editor('', $id = 'event_desc', $prev_id = 'title', $media_buttons = true, $tab_index = 3);
				?>
				<table id="post-status-info" cellspacing="0">
					<tbody>
						<tr>
							<td id="wp-word-count"></td>
							<td class="autosave-info"><span id="autosave">&nbsp;</span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- /postdivrich -->
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				<div  id="event-date-time" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"> <span>
	<?php _e('Event Date/Times', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<table width="100%" border="0" cellpadding="5">
							<tr valign="top">
								<td class="a"><fieldset id="add-reg-dates">
										<legend>
	<?php _e('Registration Dates', 'event_espresso'); ?> <?php apply_filters('espresso_help', 'reg_date_info'); ?>
										</legend>
										<p>
											<label for="registration_start"><?php echo __('Registration Start', 'event_espresso') ?> </label>
											<input type="text" size="10" id="registration_start" class="datepicker" name="registration_start" value="" />
										</p>
										<p>
											<label for="registration_end"> <?php echo __('Registration End', 'event_espresso') ?></label>
											<input type="text" size="10" id="registration_end" class="datepicker" name="registration_end" value="" />
										</p>
									</fieldset>
									<fieldset id="add-event-dates">
										<legend>
												<?php _e('Event Dates', 'event_espresso'); ?> <?php apply_filters('espresso_help', 'event_date_info'); ?>
										</legend>
										<p>
											<label for="start_date">
	<?php _e('Event Start Date', 'event_espresso') ?>
											</label>
											<input type="text" size="10" id="start_date" class="datepicker" name="start_date" value="" />
										</p>
										<p>
											<label for="end_date">
									<?php _e('Event End Date', 'event_espresso') ?>
											</label>
											<input type="text" size="10" id="end_date" class="datepicker" name="end_date" value="" />
										</p>
									</fieldset>
											<?php if ((!isset($org_options['use_event_timezones']) || $org_options['use_event_timezones'] != 'Y') && $espresso_premium == true) { ?>
										<p><span class="run-in">
										<?php _e('Current Time', 'event_espresso'); ?>
											</span> <span class="current-date"> <?php echo date(get_option('date_format')) . ' ' . date(get_option('time_format')); ?></span> <?php apply_filters('espresso_help', 'current_time_info'); ?>
											<a class="change-date-time" href="options-general.php" target="_blank">
		<?php _e('Change timezone and date format settings?', 'event_espresso'); ?>
											</a></p>
												<?php } ?>
												<?php if (isset($org_options['use_event_timezones']) && $org_options['use_event_timezones'] == 'Y' && $espresso_premium == true) { ?>
										<fieldset id="event-timezone">
											<p>
												<label>
										<?php _e('Event Timezone', 'event_espresso') ?>
												</label>
										<?php echo eventespresso_ddtimezone($event_id) ?></p>
										</fieldset>
									<?php
									}

									if ($espresso_premium == true) {
										echo get_option('event_espresso_re_active') == 1 ? '' : '<p class="recurring-available"><a class="inform" href="http://eventespresso.com/?p=3319" target="_blank" title="Visit eventespresso.com for full details">' . __('Recurring Event Manager Now Available!', 'event_espresso') . '</a></p>';
									}
									?>

								</td>
										<?php // ADDED TIME REGISTRATION LIMITS  ?>
								<td class="b"><fieldset id="add-register-times">
										<legend>
											<?php _e('Registration Times', 'event_espresso'); ?> <?php apply_filters('espresso_help', 'reg_date_info'); ?>
										</legend>
										<?php echo event_espresso_timereg_editor(); ?>
									</fieldset>
									<fieldset id="add-event-times">
										<legend>
	<?php _e('Event Times', 'event_espresso'); ?> <?php apply_filters('espresso_help', 'event_times_info'); ?>
										</legend>
	<?php echo event_espresso_time_editor(); ?>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<?php
				/**
				 * Load the recurring events form if the add-on has been installed and activated.
				 */
				if (get_option('event_espresso_re_active') == 1 && $espresso_premium == true) {
					require_once(EVENT_ESPRESSO_RECURRENCE_FULL_PATH . "functions/re_view_functions.php");
					event_espresso_re_form($events);
				}
				?>
				<div id="event-pricing" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"> <span>
								<?php _e('Event Pricing', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<table width="100%" border="0" cellpadding="5">
							<tr valign="top">
								<td id="standard-pricing" class="a"><?php event_espresso_multi_price_update($event_id); //Standard pricing ?></td>
								<?php
								//If the members addon is installed, define member only event settings
								if (get_option('events_members_active') == 'true' && $espresso_premium == true) {
									?>
									<td id="member-pricing" class="b"><?php echo event_espresso_member_only_pricing(); //Show the the member only pricing options.  ?></td>
		<?php
	}
	?>
							</tr>
						</table>
					</div>
				</div>
				<h2>
							<?php _e('Advanced Options', 'event_espresso'); ?>
				</h2>
				<div id="event-location" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"> <span>
								<?php _e('Additional Event/Venue Information', 'event_espresso'); ?>
						</span> </h3>
					<div class="inside">
						<table width="100%" border="0" cellpadding="5">

							<tr>

										<?php
										if (function_exists('espresso_venue_dd') && isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' && $espresso_premium == true) {
											$ven_type = 'class="use-ven-manager"';
											?>
									<td <?php echo $ven_type ?>><fieldset id="venue-manager">
											<legend><?php echo __('Venue Information', 'event_espresso') ?></legend>
											<?php if (!espresso_venue_dd()) : ?>
												<p class="info"><b>
										<?php _e('You have not created any venues yet.', 'event_espresso'); ?>
													</b></p>
												<p><a href="admin.php?page=event_venues"><?php echo __('Add venues to the Venue Manager', 'event_espresso') ?></a></p>
									<?php else: ?>
			<?php echo espresso_venue_dd($venue_id) ?>
												<?php endif; ?>
										</fieldset></td>
	<?php
	} else {
		$ven_type = 'class="manual-venue"';
		?>
									<td <?php echo $ven_type ?>><fieldset id="phys-location">
											<legend>
		<?php _e('Physical Location', 'event_espresso'); ?>
											</legend>
											<p>
												<label for="phys-addr">
		<?php _e('Address:', 'event_espresso'); ?>
												</label>
												<input id="phys-addr" size="20" tabindex="101"  type="text"  value="" name="address" />
											</p>
											<p>
												<label for="phys-addr-2">
		<?php _e('Address 2:', 'event_espresso'); ?>
												</label>
												<input id="phys-addr-2" size="20" tabindex="102"  type="text"  value="" name="address2" />
											</p>
											<p>
												<label for="phys-city">
		<?php _e('City:', 'event_espresso'); ?>
												</label>
												<input id="phys-city" size="20" tabindex="103"  type="text"  value="" name="city" />
											</p>
											<p>
												<label for="phys-state">
		<?php _e('State:', 'event_espresso'); ?>
												</label>
												<input id="phys-state" size="20" tabindex="104"  type="text"  value="" name="state" />
											</p>
											<p>
												<label for="zip-postal">
		<?php _e('Zip/Postal Code:', 'event_espresso'); ?>
												</label>
												<input size="20" id="zip-postal" tabindex="105"  type="text"  value="" name="zip" />
											</p>
											<p>
												<label for="phys-country">
		<?php _e('Country:', 'event_espresso'); ?>
												</label>
												<input id="phys-country" size="20" tabindex="106"  type="text"  value="" name="country" />
											</p>
										</fieldset></td>
									<td <?php echo $ven_type; ?>>

										<fieldset id="venue-info">

											<legend>
		<?php _e('Venue Information', 'event_espresso'); ?>
											</legend>
											<p>
												<label for="ven-title">
		<?php _e('Title:', 'event_espresso'); ?>
												</label>
												<input id="ven-title" size="20" tabindex="106"  type="text"  value="<?php echo isset($venue_title) ? $venue_title : '' ?>" name="venue_title" />
											</p>
											<p>
												<label for="ven-website">
		<?php _e('Website:', 'event_espresso'); ?>
												</label>
												<input id="ven-website" size="20" tabindex="107"  type="text"  value="<?php echo isset($venue_url) ? $venue_url : '' ?>" name="venue_url" />
											</p>
											<p>
												<label for="ven-phone">
		<?php _e('Phone:', 'event_espresso'); ?>
												</label>
												<input id="ven-phone"  size="20" tabindex="108"  type="text"  value="<?php echo isset($venue_phone) ? $venue_phone : '' ?>" name="venue_phone" />
											</p>
											<p>
												<label for="ven-image">
		<?php _e('Image:', 'event_espresso'); ?>
												</label>
												<input id="ven-image" size="20" tabindex="110"  type="text"  value="<?php echo isset($venue_image) ? $venue_image : '' ?>" name="venue_image" />
											</p>
									</td>

												<?php } ?>
								<td <?php echo $ven_type ?>>
									<fieldset id="virt-location">
										<legend>
	<?php _e('Virtual Location', 'event_espresso'); ?>
										</legend>
										<p>
											<label for="virt-phone">
	<?php _e('Phone:', 'event_espresso'); ?>
											</label>
											<input id="virt-phone" size="20"  type="text" tabindex="107" value="" name="phone" />
										</p>
										<p>
											<label for="url-event">
	<?php _e('URL of Event:', 'event_espresso'); ?>
											</label>
											<textarea id="url-event" cols="30" rows="4" tabindex="108"  name="virtual_url"></textarea>
										</p>
										<p>
											<label for="call-in-num">
	<?php _e('Call in Number:', 'event_espresso'); ?>
											</label>
											<input id="call-in-num" size="20" tabindex="109"  type="text"  value="" name="virtual_phone" />
									</fieldset>
								</td>
							</tr>

						</table>
						<p>
							<label for="enable-for-gmap"> <?php _e('Enable event address in Google Maps? ', 'event_espresso') ?></label>
	<?php echo select_input('enable_for_gmap', $values, 'N', 'id="enable-for-gmap"') ?>
						</p>
					</div>
				</div>

				<!-- /event-location-->
						<?php if ($espresso_premium == true) { ?>
					<div  id="event-meta" class="postbox">
						<div class="handlediv" title="Click to toggle"><br>
						</div>
						<h3 class="hndle"> <span>
		<?php _e('Event Meta', 'event_espresso'); ?>
							</span> </h3>
						<div class="inside">
		<?php event_espresso_meta_edit(empty($event_meta) ? '' : $event_meta); ?>
						</div>
					</div>
	<?php } ?>
				<!-- /event-meta-->
				<div id="confirmation-email" class="postbox">
					<div class="handlediv" title="Click to toggle"><br />
					</div>
					<h3 class="hndle"> <span>
									<?php _e('Email Confirmation:', 'event_espresso') ?>
						</span> </h3>
					<div class="inside">
						<div id="emaildescriptiondivrich" class="postarea">
							<div class="email-conf-opts">
								<p class="inputunder"><label><?php echo __('Send custom confirmation emails for this event?', 'event_espresso') ?> <?php apply_filters('espresso_help', 'custom_email_info') ?> </label> <?php echo select_input('send_mail', $values); ?> </p>

								<p class="inputunder">
									<?php $email_id = isset($email_id) ? $email_id : ''; ?>
									<label>
	<?php _e('Use a ', 'event_espresso'); ?>
										<a href="admin.php?page=event_emails" target="_blank">
	<?php _e('pre-existing email? ', 'event_espresso'); ?>
										</a>
	<?php apply_filters('espresso_help', 'email_manager_info') ?>
									</label>
									<?php echo espresso_db_dropdown('id', 'email_name', EVENTS_EMAIL_TABLE, 'email_name', $email_id, 'desc') ?>
								</p>

								<p>
									<em>OR</em>
								</p>
								<p class="section-heading">
										<?php _e('Create a custom email:', 'event_espresso') ?> <?php apply_filters('espresso_help', 'event_custom_emails'); ?>
								</p>
							</div>
							<div class="visual-toggle">
								<p><a class="toggleVisual">
	<?php _e('Visual', 'event_espresso'); ?>
									</a> <a class="toggleHTML">
	<?php _e('HTML', 'event_espresso'); ?>
									</a></p>
							</div>
							<div class="postbox">
								<textarea name="conf_mail" class="theEditor" id="conf_mail"></textarea>
								<table id="email-confirmation-form" cellspacing="0">
									<tbody>
										<tr>
											<td class="aer-word-count"></td>
											<td class="autosave-info"><span><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_info">
	<?php _e('View Custom Email Tags', 'event_espresso'); ?>
													</a> | <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=custom_email_example">
	<?php _e('Email Example', 'event_espresso'); ?>
													</a></span></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<!-- /confirmation-email-->
	<?php
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/event-management/new_event_post.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "includes/admin-files/event-management/new_event_post.php");
	}
	?>
			</div>
			<!-- /normal-sortables-->
	  </div>
	  <!-- /post-body-content -->
	<?php include_once('create_events_help.php'); ?>
	</div>
	<!-- /post-body -->
	<input type="hidden" name="action" value="add" />
	<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
	<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
	<script type="text/javascript" charset="utf-8">
		//<![CDATA[
		jQuery(document).ready(function() {

			postboxes.add_postbox_toggles('events');

			jQuery(".datepicker" ).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: "yy-mm-dd",
				showButtonPanel: true
			});
			jQuery("#start_date").change(function(){
				jQuery("#end_date").val(jQuery(this).val());
			});

			var header_clicked = false;
			jQuery('#upload_image_button').click(function() {
				formfield = jQuery('#upload_image').attr('name');
				tb_show('', 'media-upload.php?type=image&amp;TB_iframe=1');
				header_clicked = true;
				return false;
			});
	<?php if (function_exists('espresso_featured_image_event_admin') && $espresso_premium == true) { ?>
					window.original_send_to_editor = window.send_to_editor;

					window.send_to_editor = function(html) {
						if(header_clicked) {
							imgurl = jQuery('img',html).attr('src');
							jQuery('#' + formfield).val(imgurl);
							jQuery('#featured-image').append("<p id='image-display'><img class='show-selected-img' src='"+imgurl+"' alt='' /></p>");
							header_clicked = false;
							tb_remove();
							jQuery("#featured-image").append("<a id='remove-image' href='#' title='Remove this image' onclick='return false;'>Remove Image</a>");
							jQuery('#remove-image').click(function(){
								//alert('delete this image');
								jQuery('#' + formfield).val('');
								jQuery("#image-display").empty();
								jQuery('#remove-image').remove();
							});
						} else {
							window.original_send_to_editor(html);
						}
					}
		<?php
	}
	?>

			});

			//]]>
	</script>
	<?php
	espresso_tiny_mce();
}