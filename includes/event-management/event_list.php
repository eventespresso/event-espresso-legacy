<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }	

function event_espresso_edit_list() {
	
	global $wpdb, $org_options;
    require_once( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/event-management/queries.php' );
	if ( ! defined( 'EVT_ADMIN_URL' )) {
		define( 'EVT_ADMIN_URL', admin_url( 'admin.php?page=events' ));		
	}

	 // DELETE EVENT
	if (isset($_POST['delete_event'])) {
		if (is_array($_POST['checkbox'])) {
			while (list($key, $value) = each($_POST['checkbox'])):
				$del_id = $key;
				event_espresso_delete_event($del_id);
			endwhile;
		}
		?>
		<div id="message" class="updated fade">
			<p><strong>
					<?php _e('Event(s) have been permanently deleted.', 'event_espresso'); ?>
				</strong></p>
		</div>
		<?php
	}
	// REALLY REALLY DELETE EVENT THIS TIME !!!
	if (isset($_POST['perm_delete_event'])) {
		if (is_array($_POST['checkbox'])) {
			while (list($key, $value) = each($_POST['checkbox'])):
				$del_id = $key;
				event_espresso_empty_event_trash($del_id);
			endwhile;
		}
		?>
		<div id="message" class="updated fade">
			<p><strong>
					<?php _e('Event(s) have been permanently deleted.', 'event_espresso'); ?>
				</strong></p>
		</div>
		<?php
	}
	
	// dejavu ?
	$recurrence_icon = '';
	if (defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE')) {
		$recurrence_icon = '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/arrow_rotate_clockwise.png" alt="Recurring Event" title="Recurring Event" class="re_fr" />';
	}

	// get SQL for query
	$SQL = espresso_generate_events_page_list_table_sql();
	$events = $wpdb->get_results( $SQL, OBJECT_K );
	$total_events = $wpdb->num_rows;
	//echo '<h4>' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
	//printr( $events, '$events  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );


	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php');
		espresso_display_admin_reports_filters( $total_events );
	} else {
		?>
		<p>
			<strong><?php _e('Advanced filters are available in the premium versions.', 'event_espresso');?></strong> 
			<a href="http://eventespresso.com/pricing/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Advanced+filters+are+available+in+the+premium+versions<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=event_overview_tab" target="_blank">
				<?php _e('Upgrade Now!', 'event_espresso');?>
			</a>
		</p>
		<?php
	}

	
 ?>
	<form id="form1" name="form1" method="post" action="admin.php?page=events<?php //echo $_SERVER["REQUEST_URI"]  ?>">
		<table id="table" class="widefat event-list" width="100%">
			<thead>
				<tr>
					<th class="manage-column column-cb check-column" id="cb" scope="col" style="width:28px;"><input type="checkbox"></th>

					<th class="manage-column column-comments num" id="id" style="padding-top:7px; width:3%;" scope="col" title="Click to Sort">
						<span><?php _e('ID', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:26%;">
						<span><?php _e('Name', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:12%;">
						<span><?php _e('Venue', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-date" id="dow" scope="col" title="Click to Sort" style="width:4%;";>
						<span><?php _e('DoW', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:12%;">
						<span><?php _e('Start Date', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:7%;">
						<span><?php _e('Start Time', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-date" id="begins" scope="col" title="Click to Sort" style="width:16%;">
						<span><?php _e('Reg Begins', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>

					<th class="manage-column column-date" id="status" scope="col" title="Click to Sort" style="width:8%;">
						<span><?php _e('Status', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>
					<?php if (function_exists('espresso_is_admin') && espresso_is_admin() == true && $espresso_premium == true) { ?>
						<th class="manage-column column-date" id="status" scope="col" title="Click to Sort" style="width:10%;">
							<span><?php _e('Creator', 'event_espresso'); ?></span>
							<span class="sorting-indicator"></span>
						</th>
	<?php } ?>
					<th class="manage-column column-date" id="attendees" scope="col" title="Click to Sort" style="width:7%;">
						<span><?php _e('Attendees', 'event_espresso'); ?></span>
						<span class="sorting-indicator"></span>
					</th>
					<th class="manage-column column-author" id="actions" scope="col" style="width:25%;">
	<?php _e('Actions', 'event_espresso'); ?>
					</th>

				</tr>
			</thead>

			<tbody>
				<?php
				if ( $total_events > 0 ) {
					
					// instead of doing queries for each event while looping through them, we're going to grab a list of event IDs and consolidate our queries outside the loop
					$event_ids = implode( ',', array_keys( $events ));
					// first let's grab attendee counts in one BIG query instead of individual queries for each event
					$SQL = "SELECT event_id, SUM(quantity) AS quantity FROM " . EVENTS_ATTENDEE_TABLE . " ";
					$SQL .= "WHERE (payment_status='Completed' OR payment_status='Pending' OR payment_status='Refund') ";
					$SQL .= "GROUP BY event_id HAVING event_id IN ( $event_ids )";
					$attendees = $wpdb->get_results( $SQL, OBJECT_K );		
//					echo '<h4>' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';			
//					printr( $attendees, '$attendees  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

					// now let's grab start times for each event
					$SQL = "SELECT event_id, start_time FROM " . EVENTS_START_END_TABLE . " ";
					$SQL .= "WHERE event_id IN ( $event_ids ) ";
					$SQL .= 'ORDER BY start_time ASC';  
					$start_times = $wpdb->get_results( $SQL, OBJECT_K );
//					echo '<h4>' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//					printr( $start_times, '$start_times  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
					
					foreach ($events as $event) {
					//printr( $event, '$event  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
						$event_id = $event->event_id;
						$event_name = stripslashes_deep($event->event_name);
						$event_identifier = stripslashes_deep($event->event_identifier);
						$reg_limit = isset($event->reg_limit) ? $event->reg_limit : '';
						$start_date = isset($event->start_date) ? $event->start_date : '';
						$start_time = isset( $start_times[ $event_id ] ) ? $start_times[ $event_id ]->start_time : '';
						$end_date = isset($event->end_date) ? $event->end_date : '';
						$is_active = isset($event->is_active) ? $event->is_active : '';
						$status = array();
						$recurrence_id = isset($event->recurrence_id) ? $event->recurrence_id : '';
						$registration_start = isset($event->registration_start) ? $event->registration_start : '';
						$registration_startT = isset($event->registration_startT) ? $event->registration_startT : '';

						$event_address = isset($event->address) ? $event->address : '';
						$event_address2 = isset($event->address2) ? $event->address2 : '';
						$event_city = isset($event->city) ? $event->city : '';
						$event_state = isset($event->state) ? $event->state : '';
						$event_zip = isset($event->zip) ? $event->zip : '';
						$event_country = isset($event->country) ? $event->country : '';
						//added new
						$venue_title = isset($event->venue_title) ? stripslashes_deep($event->venue_title) : '';
						$venue_locale = isset($event->locale_name) ? $event->locale_name : '';
						$wp_user = isset($event->wp_user) ? $event->wp_user : '';
						
						$event_meta = array();
						$event_meta['is_active'] = $is_active;
						$event_meta['event_status'] = $event->event_status;

						$event_meta['start_time'] = $start_time;
						$event_meta['start_date'] = $start_date;

						$event_meta['registration_start'] = $registration_start;
						$event_meta['registration_startT'] = $registration_startT;

						$registration_end = $event_meta['registration_end'] = $event->registration_end;
						$registration_endT = $event_meta['registration_endT'] = $event->registration_endT;
									
						$status = event_espresso_get_is_active( $event_id, $event_meta );
						
						//Get number of attendees
						$num_attendees = isset( $attendees[ $event_id ]) ? $attendees[ $event_id ]->quantity : 0;

						$location = (!empty($event_address) ? $event_address : '') . (!empty($event_address2) ? '<br />' . $event_address2 : '') . (!empty($event_city) ? '<br />' . $event_city : '') . (!empty($event_state) ? ', ' . $event_state : '') . (!empty($event_zip) ? '<br />' . $event_zip : '') . (!empty($event_country) ? '<br />' . $event_country : '');
						$dow = date("D", strtotime($start_date));
						ob_start();
						?>
						<tr>
							<td class="check-column" style="padding:7px 0 22px 7px; vertical-align:top;"><!--Delete Events-->
			<?php echo '<input name="checkbox[' . $event_id . ']" type="checkbox"  title="Delete Event ' . $event_name . '" />'; ?></td>

							<td class="column-comments" style="padding-top:3px;"><?php echo $event_id ?></td>

							<td class="post-title page-title">
								<strong><a class="row-title" href="admin.php?page=events&action=edit&event_id=<?php echo $event_id ?>"><?php echo $event_name ?></a> <?php echo ($recurrence_id > 0) ? $recurrence_icon : ''; ?> </strong>
								<div class="row-actions"><span><a href="<?php echo espresso_reg_url($event_id); ?>" target="_blank"><?php _e('View', 'event_espresso'); ?></a> | </span><span class='edit'><a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id ?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span><span class='delete'><a onclick="return confirmDelete();" href='admin.php?page=events&amp;action=delete&amp;event_id=<?php echo $event_id ?>'><?php _e('Delete', 'event_espresso'); ?></a></span> | <span><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id ?>"><?php _e('Attendees', 'event_espresso'); ?></a> | </span><span><a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;event_id=" . $event_id . "&amp;export=report&action=payment&amp;type=excel"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>"><?php _e('Export', 'event_espresso'); ?></a></span></div>
							</td>

							<td class="author">
								<?php 
									echo $venue_title != '' ? $venue_title : '';
									echo $venue_locale != '' ? '<br />[' . $venue_locale . ']' : '';
								?>						
							</td>

							<td class="date"><?php echo $dow ?></td>

							<td class="author"><?php echo event_date_display( $start_date, get_option('date_format')) ?></td>

							<td class="author"><?php echo date( get_option('time_format'), strtotime( $start_time )); ?></td>

							<td class="date"><?php echo event_date_display( $registration_start, get_option('date_format')); ?> @ <?php echo date( get_option('time_format'), strtotime( $registration_startT )); ?></td>

							<td class="date"><?php echo $status['display'] ?></td>

							<?php
							if (function_exists('espresso_is_admin') && espresso_is_admin() == true && $espresso_premium == true) {
								$user_company = espresso_user_meta($wp_user, 'company') != '' ? espresso_user_meta($wp_user, 'company') : '';
								$user_organization = espresso_user_meta($wp_user, 'organization') != '' ? espresso_user_meta($wp_user, 'organization') : '';
								$user_co_org = $user_company != '' ? $user_company : $user_organization;
								?>
								<td class="date"><?php echo espresso_user_meta($wp_user, 'user_firstname') != '' ? espresso_user_meta($wp_user, 'user_firstname') . ' ' . espresso_user_meta($wp_user, 'user_lastname') . ' (<a href="user-edit.php?user_id='.$wp_user.'">' . espresso_user_meta($wp_user, 'user_nicename'). '</a>)' : espresso_user_meta($wp_user, 'display_name')  . ' (<a href="user-edit.php?user_id='.$wp_user.'">' . espresso_user_meta($wp_user, 'user_nicename'). '</a>)'; ?>
				<?php echo $user_co_org != '' ? '<br />[' . espresso_user_meta($wp_user, 'company') . ']' : ''; ?>
								</td>
			<?php } ?>

							<td class="author">
								<?php $attendees_url = add_query_arg( array( 'event_admin_reports' => 'list_attendee_payments', 'event_id' => $event_id , 'event_status' => $event->event_status ), EVT_ADMIN_URL ); ?>
								<a href="<?php echo $attendees_url ?>"><?php echo $num_attendees . '/' . $reg_limit; ?></a>
							</td>
							
							<td class="date">
								<div style="width:180px;">
								
									<a href="<?php echo espresso_reg_url($event_id); ?>" title="<?php _e('View Event', 'event_espresso'); ?>" target="_blank">
										<div class="view_btn"></div>
									</a>

									<a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Event', 'event_espresso'); ?>">
										<div class="edit_btn"></div>
									</a>

									<a href="admin.php?page=events&amp;event_id=<?php echo $event_id ?>&amp;event_admin_reports=list_attendee_payments" title="<?php _e('View Attendees', 'event_espresso'); ?>">
										<div class="complete_btn"></div>
									</a>
									
									<a href="admin.php?page=events&event_admin_reports=charts&event_id=<?php echo $event_id ?>" title="<?php _e('View Report', 'event_espresso'); ?>">
										<div class="reports_btn"></div>
									</a>


									<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=unique_id_info_<?php echo $event_id ?>" title="<?php _e('Get Short URL/Shortcode', 'event_espresso'); ?>">
										<div class="shortcode_btn"></div>
									</a>

									<a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;event_id=" . $event_id . "&amp;export=report&action=payment&amp;type=excel"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>">
										<div class="excel_exp_btn"></div>
									</a>

									<a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&event_id=" . $event_id . "&export=report&action=payment&type=csv"; ?>'" title="<?php _e('Export to CSV', 'event_espresso'); ?>">
										<div class="csv_exp_btn"></div>
									</a>

									<a href="admin.php?page=events&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Email Attendees', 'event_espresso'); ?>">
										<div class="newsletter_btn"></div>
									</a>
									
								</div>

								<div id="unique_id_info_<?php echo $event_id ?>" style="display:none">
						<?php _e('<h2>Short URL/Shortcode</h2><p>This is the short URL to this event:</p><p><span  class="updated fade">' . espresso_reg_url($event_id) . '</span></p><p>This will show the registration form for this event just about anywhere. Copy and paste the following shortcode into any page or post.</p><p><span  class="updated fade">[SINGLEEVENT single_event_id="' . $event_identifier . '"]</span></p> <p class="red_text"> Do not use in place of the main events page that is set in the Organization Settings page.', 'event_espresso'); ?>
								</div>
								
							</td>
						</tr>
						<?php

							$content = ob_get_contents();
							ob_end_clean();
							echo $content;
							
					}
					//End foreach ($events as $event){						
				} 
				?>

			</tbody>
		</table>
		<div style="clear:both; margin-bottom:30px;">
			<input type="checkbox" name="sAll" onclick="selectAll(this)" />
			<strong>
	<?php _e('Check All', 'event_espresso'); ?>
			</strong><?php if (isset($_REQUEST['event_status']) && $_REQUEST['event_status'] == 'D') { ?>
				<input name="perm_delete_event" type="submit" class="button-secondary" id="perm_delete_event" value="<?php _e('Permanently Delete Events(s)', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />
			<?php } else { ?>
				<input name="delete_event" type="submit" class="button-secondary" id="delete_event" value="<?php _e('Delete Events(s)', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />

				<a  style="margin-left:5px"class="button-secondary" href="admin.php?page=events&amp;action=csv_import"><?php _e('Import Events', 'event_espresso'); ?></a>
				<?php if (function_exists('espresso_attendee_import') && $espresso_premium == true) {?><a style="margin-left:5px" class="button-secondary" href="admin.php?page=espresso_attendee_import"><?php _e('Import Attendees', 'event_espresso'); ?></a><?php } ?>

				<a style="margin-left:5px" class="button-secondary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;export=report&action=payment&amp;type=excel&all_events=true"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>"><?php _e('Export All Attendee Data', 'event_espresso'); ?></a>
				<a class="button-secondary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;export=report&action=event&amp;type=excel&all_events=true"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>"><?php _e('Export All Event Data', 'event_espresso'); ?></a>
				<a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=add_new_event"><?php _e('Add New Event', 'event_espresso'); ?></a>
		<?php } ?>  </div>
	</form>
	<h4 style="clear:both"><?php _e('Legend', 'event_espresso'); ?></h4>
	<dl style="float:left; margin-left:10px; width:200px">
	<?php
	echo defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE') ?
			'<dt><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/arrow_rotate_clockwise.png" alt="Recurring Event" title="Recurring Event"  /> - ' . __('Recurring Event', 'event_espresso') . '</dt>' : '';
	?>
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/magnifier.png" width="16" height="16" alt="<?php _e('View Event', 'event_espresso'); ?>" /> - <?php _e('View Event', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/calendar_edit.png" width="16" height="16" alt="<?php _e('Edit Event', 'event_espresso'); ?>" /> - <?php _e('Edit Event', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/group.png" width="16" height="16" alt="<?php _e('Event Attendees', 'event_espresso'); ?>" /> - <?php _e('Event Attendees', 'event_espresso'); ?></dt>
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/chart_bar.png" width="16" height="16" alt="<?php _e('Send Event Email', 'event_espresso'); ?>" /> - <?php _e('View Report', 'event_espresso'); ?></dt>


	</dl>

	<dl style="float:left; margin-left:10px;">
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/tag.png" width="16" height="16" alt="<?php _e('Short Code', 'event_espresso'); ?>" /> - <?php _e('Short Code', 'event_espresso'); ?></dt>
		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/excel_icon.png" width="16" height="16" alt="<?php _e('Excel Spreadsheet', 'event_espresso'); ?>" /> - <?php _e('Excel Export', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/csv_icon_sm.gif" width="16" height="16" alt="<?php _e('CSV Spreadsheet', 'event_espresso'); ?>" /> - <?php _e('CSV Export', 'event_espresso'); ?></dt>

		<dt><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_go.png" width="16" height="16" alt="<?php _e('View Report', 'event_espresso'); ?>" /> - <?php _e('Event Newsletter', 'event_espresso'); ?></dt>
	</dl>

	<script>
		jQuery(document).ready(function($) {
			// show the table data 
			var mytable = $('#table').dataTable( {
				"sDom": 'Clfrtip',
				"aoColumns": [
					{ "bSortable": false },
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
	<?php echo function_exists('espresso_is_admin') && espresso_is_admin() == true ? 'null,' : ''; ?>
					null,
					{ "bSortable": false }
				],
				"aoColumnDefs": [
					{ "bVisible": false, "aTargets": [ <?php echo $org_options['use_venue_manager'] == 'Y' ? '' : '3,' ?> 4 ] }
				],
				"oColVis": {
					"aiExclude": [ 0, 1, 2 ],
					"buttonText": "Filter: Show / Hide Columns",
					"bRestore": true
				},
				"bAutoWidth": false,
				"bStateSave": true,
				"sPaginationType": "full_numbers",
				"oLanguage": {	"sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong>",
					"sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" }

			});
		});
	</script>

	<div id="coupon_code_info" style="display:none">
		<h2><?php _e('Coupon/Promo Code', 'event_espresso'); ?></h2><p><?php _e('This is used to apply discounts to events.', 'event_espresso'); ?></p><p><?php _e('A coupon or promo code could can be anything you want. For example: Say you have an event that costs', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>200. <?php _e('If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted', 'event_espresso'); ?>  <?php echo $org_options['currency_symbol'] ?>50.00, <?php _e('Bringing the cost of the event to', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>150. </p>
			<p><?php	_e("Note: Promo Codes which are marked to 'apply to all events', although not explicitly enumerated, can also be used for this event, provided it allows promo codes.", "event_espresso");?></p>
	</div>
	<div id="unique_id_info" style="display:none">
		<h2><?php _e('Event Identifier', 'event_espresso'); ?></h2><p><?php _e('This should be a unique identifier for the event. Example: "Event1" (without quotes.)</p><p>The unique ID can also be used in individual pages using the', 'event_espresso'); ?> [SINGLEEVENT single_event_id="<?php _e('Unique Event ID', 'event_espresso'); ?>"] <?php _e('shortcode', 'event_espresso'); ?>.</p>
	</div>
	<?php
	echo event_espresso_custom_email_info();
}
