<?php
if (!defined('EVENT_ESPRESSO_VERSION')) {
	exit('No direct script access allowed');
}

function event_list_attendees() {

	global $wpdb, $org_options, $ticketing_installed, $espresso_premium;
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/event-management/queries.php');
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "includes/functions/attendee_functions.php");

	if (!defined('EVT_ADMIN_URL')) {
		define('EVT_ADMIN_URL', admin_url('admin.php?page=events'));
	}

	$EVT_ID = isset($_REQUEST['event_id']) && $_REQUEST['event_id'] != '' ? absint($_REQUEST['event_id']) : FALSE;

	if ($EVT_ID) {
		echo '<h1>' . espresso_event_list_attendee_title($EVT_ID) . '</h1>';
	}

	//Delete the attendee(s)
	if (isset($_POST['delete_customer']) && !empty($_POST['delete_customer'])) {
		if (is_array($_POST['checkbox'])) {
			while (list( $att_id, $value ) = each($_POST['checkbox'])) {

				//hook for before delete
				do_action('action_hook_espresso_before_delete_attendee_event_list', $att_id, $EVT_ID);

				$SQL = "DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = '%d'";
				$wpdb->query($wpdb->prepare($SQL, $att_id));
				$SQL = "DELETE FROM " . EVENTS_ATTENDEE_META_TABLE . " WHERE attendee_id = '%d'";
				$wpdb->query($wpdb->prepare($SQL, $att_id));
				$SQL = "DELETE FROM " . EVENTS_ANSWER_TABLE . " WHERE attendee_id = '%d'";
				$wpdb->query($wpdb->prepare($SQL, $att_id));

				//hook for after delete
				do_action('action_hook_espresso_after_delete_attendee_event_list', $att_id, $EVT_ID);
			}
		}
		?>
		<div id="message" class="updated fade">
			<p>
				<strong><?php _e('Customer(s) have been successfully deleted from the event.', 'event_espresso'); ?></strong>
			</p>
		</div>
		<?php
	}

	//	MARKING USERS AS ATTENDED (OR NOT)
	if ((!empty($_POST['attended_customer']) || !empty($_POST['unattended_customer'])) && $ticketing_installed == TRUE) {
		if (is_array($_POST['checkbox'])) {
			while (list($att_id, $value) = each($_POST['checkbox'])) {
				// on / off value for attended status checkbox
				$check_in_or_out = $value == "on" && array_key_exists('attended_customer', $_POST) ? 1 : 0;

				$SQL = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = %d ";
				$attendee = $wpdb->get_row($wpdb->prepare($SQL, $att_id));
				$ticket_quantity_scanned = $attendee->checked_in_quantity;
				$tickets_for_attendee = $attendee->quantity;
				$updated_ticket_quantity = $check_in_or_out ? $tickets_for_attendee : 0;
				$type = $check_in_or_out == true ? 'checkin' : 'checkout';
				//$type
				if (($ticket_quantity_scanned >= 1 && true == $check_in_or_out)
								||
								($ticket_quantity_scanned <= 0 && false == $check_in_or_out)) {
					?>
					<div id="message" class="error fade">
						<p>
							<strong><?php _e('Scanned tickets cannot be redeemed/un-redeemed here.', 'event_espresso'); ?></strong>
						</p>
					</div>
					<?php
				} else {
					if ($wpdb->update(EVENTS_ATTENDEE_TABLE, array('checked_in' => $check_in_or_out, 'checked_in_quantity' => $updated_ticket_quantity), array('id' => $att_id), array('%d', '%d'), array('%d'))) {
						?>
						<div id="message" class="updated fade">
							<p><strong>
						<?php _e('Customer(s) attendance data successfully updated for this event.', 'event_espresso'); ?>
								</strong></p>
						</div>
						<?php
						//Add the date checked-out into the events_attendee_checkin table
						$columns_and_values = array(
								'attendee_id' => $att_id,
								'registration_id' => $attendee->registration_id,
								'event_id' => $attendee->event_id,
								'checked_in' => $updated_ticket_quantity, //Checked-out
								'date_scanned' => date('Y-m-d H:i:s'),
								'method' => 'website',
								'type' => $type,
						);
						$data_formats = array('%d', '%s', '%d', '%d', '%s', '%s', '%s',);
						$scan_date = $wpdb->insert($wpdb->prefix . "events_attendee_checkin", $columns_and_values, $data_formats);
						if (!$scan_date) {
							//throw new EspressoAPI_OperationFailed(__("Updating of date checked in failed:","event_espresso").$scan_date);
						}
					}
				}
			}
		}
	}

	// get SQL for query
	$SQL = espresso_generate_events_page_list_table_sql(FALSE, TRUE);
	$attendees = $wpdb->get_results($SQL, OBJECT_K);
	$total_attendees = $wpdb->num_rows;
//	echo '<h4>' . $wpdb->last_query . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
//	printr( $attendees, '$attendees  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );


	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php');
		espresso_display_admin_reports_filters($total_attendees);
	} else {
		?>
		<p>
			<strong><?php _e('Advanced filters are available in the premium versions.', 'event_espresso'); ?></strong> 
			<a href="http://eventespresso.com/download/" target="_blank">
		<?php _e('Upgrade Now!', 'event_espresso'); ?>
			</a>
		</p>
		<?php
	}

	$updated_ticket_quantity = 0;
	$att_table_form_url = add_query_arg(array('event_admin_reports' => 'list_attendee_payments', 'event_id' => $EVT_ID), EVT_ADMIN_URL);
	?>

	<form id="form1" name="form1" method="post" action="<?php echo $att_table_form_url; ?>">
		<table id="table" class="widefat fixed" width="100%">
			<thead>
				<tr>
					<th class="manage-column column-cb check-column" id="cb" scope="col" style="width:3%;min-width:35px !important;">
						<input type="checkbox">
					</th>
					<th class="manage-column column-att-id" id="att-id" scope="col" title="Click to Sort"style="width:3%;max-width:35px !important;"> 
						<span><?php _e('ID', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-name" id="name" scope="col" title="Click to Sort"style="width: 10%;"> 
						<span><?php _e('Attendee Name', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-registrationid" id="registrationid" scope="col" title="Click to Sort" style="width: 10%;">
						<span><?php _e('Reg ID', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-date" id="registration" scope="col" title="Click to Sort" style="width: 10%;"> 
						<span><?php _e('Registered', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-title" id="event-title" scope="col" title="Click to Sort" style="width: 10%;"> 
						<span><?php _e('Event Title', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-title" id="event-time" scope="col" title="Click to Sort" style="width: 8%;"> 
						<span><?php _e('Event Time', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-title" id="attended" scope="col" title="Click to Sort" style="width: 8%;">
						<span><?php echo $ticketing_installed == true ? __('Attended', 'event_espresso') : __('Quantity', 'event_espresso') ?></span> <span class="sorting-indicator"></span> 
					</th>
					<?php if ($ticketing_installed == true) { ?>
						<th class="manage-column column-title" id="date_attended" scope="col" title="Click to Sort" style="width: 8%;">
							<span><?php _e('Scan Date', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
						</th>
						<?php
					}
					?>
					<th class="manage-column column-title" id="ticket-option" scope="col" title="Click to Sort" style="width: 13%;">
						<span><?php _e('Option', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th align="center" class="manage-column column-date" id="amount" style="width: 5%;" title="Click to Sort" scope="col">
						<span><?php _e('Status', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-date" id="payment_type" scope="col" title="Click to Sort" style="width: 8%;">
						<span><?php _e('Type', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-date" id="coupon" scope="col" title="Click to Sort" style="width: 10%;"> 
						<span><?php _e('Coupon', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-date" id="txn_id" scope="col" title="Click to Sort" style="width: 10%;"> 
						<span><?php _e('Transaction ID', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
					</th>
					<th class="manage-column column-date" id="action" scope="col" title="" >
						<?php _e('Actions', 'event_espresso'); ?>
					</th>
				</tr>
			</thead>
			<tbody>	
				<?php
				if ($total_attendees > 0) {
					foreach ($attendees as $attendee) {
						$id = $attendee->id;
						$registration_id = $attendee->registration_id;
						$lname = htmlspecialchars(stripslashes($attendee->lname), ENT_QUOTES, 'UTF-8');
						$fname = htmlspecialchars(stripslashes($attendee->fname), ENT_QUOTES, 'UTF-8');
						$address = htmlspecialchars(stripslashes($attendee->address), ENT_QUOTES, 'UTF-8');
						$city = htmlspecialchars(stripslashes($attendee->city), ENT_QUOTES, 'UTF-8');
						$state = htmlspecialchars(stripslashes($attendee->state), ENT_QUOTES, 'UTF-8');
						$zip = $attendee->zip;
						$email = '<span style="visibility:hidden">' . $attendee->email . '</span>';
						$phone = $attendee->phone;
						$ticket_quantity_scanned = $attendee->checked_in_quantity;
						//$updated_ticket_quantity = $attendee->quantity > 1 ? '<div class="row-actions">(' . __('Qty', 'event_espresso') . ': ' . $attendee->quantity . ')</div>' : '';
						if ($ticketing_installed == TRUE) {
							$qty_scanned = $ticket_quantity_scanned . ' / ' . $attendee->quantity;
						} else {
							$qty_scanned = $attendee->quantity;
						}
						$attended = $attendee->checked_in;
						$amount_pd = $attendee->amount_pd;
						$payment_status = $attendee->payment_status;
						$payment_date = $attendee->payment_date;
						$date = $attendee->date;
						$event_id = $attendee->event_id;
						$coupon_code = $attendee->coupon_code;
						$txn_id = $attendee->txn_id;
						$txn_type = $attendee->txn_type;
						$price_option = $attendee->price_option;
						$event_time = $attendee->event_time;
						$event_name = $attendee->event_name;
						$event_date = $attendee->start_date;

						$date_scanned = $ticketing_installed == true && !empty($attendee->date_scanned) ? $attendee->date_scanned : '';
						?>
						<tr>

							<td class="check-column" style="padding:7px 0 22px 7px; vertical-align:top;">
								<input name="checkbox[<?php echo $id ?>]" type="checkbox"  title="Delete <?php echo $fname ?><?php echo $lname ?>">
							</td>

							<td nowrap="nowrap">
			<?php echo $attendee->id; ?>
							</td>

							<td class="row-title" nowrap="nowrap" title="<?php echo 'ID#:' . $id . ' [ REG#: ' . $registration_id . ' ] Email: ' . $attendee->email; ?>">
								<a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;event_id=<?php echo $event_id; ?>&amp;registration_id=<?php echo $registration_id; ?>&amp;form_action=edit_attendee&amp;id=<?php echo $id ?>">
			<?php echo $fname ?> <?php echo $lname ?> <?php echo $email ?>
								</a>
							</td>

							<td nowrap="nowrap" title="<?php echo $registration_id ?>">
			<?php echo $registration_id ?>
							</td>

							<td class="date column-date">
			<?php echo event_date_display($date, get_option('date_format') . ' g:i a') ?>
							</td>

							<td nowrap="nowrap">
								<a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('View attendees for this event', 'event_espresso'); ?>">
			<?php echo stripslashes_deep($event_name) ?>
								</a>
							</td>

							<td nowrap="nowrap">
			<?php echo event_date_display($event_time, get_option('time_format')) ?>
							</td>

							<td nowrap="nowrap" title="<?php echo $qty_scanned; ?>">
								<p style="padding-left:15px">
			<?php echo $qty_scanned; ?>
								</p>
							</td>
			<?php if ($ticketing_installed == true) { ?>
								<td class="date column-date">
								<?php echo event_date_display($date_scanned, get_option('date_format') . ' g:i a') ?>
								</td>
								<?php } ?>

							<td nowrap="nowrap" title="<?php echo $price_option ?>">
			<?php echo $price_option ?>
							</td>

							<td class="date column">
								<a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $registration_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Payment', 'event_espresso'); ?> ID: <?php echo $registration_id ?>">
									<p style="padding-left:17px"><?php event_espresso_paid_status_icon($payment_status) ?></p>
								</a> 
							</td>

							<td class="">
			<?php echo stripslashes_deep(espresso_payment_type($txn_type)); ?>
							</td>

							<td class="">
			<?php echo $coupon_code ?>
							</td>

							<td class="">
			<?php echo $txn_id ?>
							</td>

							<td class="" >

								<a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $registration_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Payment', 'event_espresso'); ?> ID: <?php echo $registration_id ?>">
									<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/money.png" width="16" height="16" alt="<?php _e('Edit Payment', 'event_espresso'); ?>" />
								</a>

								<a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;registration_id=<?php echo $registration_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=edit_attendee" title="<?php _e('Edit Attendee', 'event_espresso'); ?>">
									<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/user_edit.png" width="16" height="16" alt="<?php _e('Edit Attendee', 'event_espresso'); ?>" />
								</a>

								<a href="admin.php?page=events&amp;event_admin_reports=resend_email&amp;registration_id=<?php echo $registration_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=resend_email" title="<?php _e('Resend Registration Details', 'event_espresso'); ?>">
									<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_link.png" width="16" height="16" alt="<?php _e('Resend Registration Details', 'event_espresso'); ?>" />
								</a>

			<?php if ($espresso_premium == true) { ?>
									<a href="<?php echo home_url(); ?>/?download_invoice=true&amp;admin=true&amp;registration_id=<?php echo $registration_id ?>" target="_blank"  title="<?php _e('Download Invoice', 'event_espresso'); ?>">
										<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/page_white_acrobat.png" width="16" height="16" alt="<?php _e('Download Invoice', 'event_espresso'); ?>" />
									</a>
			<?php } ?>

								<?php if ($ticketing_installed == true && function_exists('espresso_ticket_url')) { ?>
									<a href="<?php echo espresso_ticket_url($id, $registration_id); ?>" target="_blank"  title="<?php _e('View/Download Ticket', 'event_espresso'); ?>">
										<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>images/icons/ticket-arrow-icon.png" width="16" height="16" alt="<?php _e('Download Ticket', 'event_espresso'); ?>" />
									</a>
			<?php }

			if ($org_options["use_attendee_pre_approval"] == "Y") {
				?>
									<br/>
									<a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $registration_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Payment', 'event_espresso'); ?> ID: <?php echo $registration_id ?>">
										<?php if (is_attendee_approved($event_id, $id)) { ?>
											<strong><?php _e('Approved', 'event_espresso'); ?></strong><br/>
										<?php } else { ?>
											<span style="color:#FF0000"><strong><?php _e('Awaiting approval', 'event_espresso'); ?></strong></span>
										<?php } ?>
									</a>
								<?php } ?>

							</td>

						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>

		<div style="clear:both; margin-bottom:30px;">

			<input name="delete_customer" type="submit" class="button-secondary" id="delete_customer" value="<?php _e('Delete Attendee(s)', 'event_espresso'); ?>" style="margin:10px 0 0 0;" onclick="return confirmDelete();" />

			<?php if ($ticketing_installed == true) { ?>
				<input name="attended_customer" type="submit" class="button-secondary" id="attended_customer" value="<?php _e('Mark as Attended', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" />
				<input name="unattended_customer" type="submit" class="button-secondary" id="attended_customer" value="<?php _e('Unmark as Attended', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" />
			<?php } ?>

			<a style="margin-left:5px" class="button-secondary" href="admin.php?page=events&amp;action=csv_import">
				<?php _e('Import Events', 'event_espresso'); ?>
			</a> 

			<?php if (function_exists('espresso_attendee_import') && $espresso_premium == true) { ?>
				<a style="margin-left:5px" class="button-secondary" href="admin.php?page=espresso_attendee_import">
					<?php _e('Import Attendees', 'event_espresso'); ?>
				</a>
			<?php } ?>

			<a class="button-secondary" style="margin-left:5px" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;export=report&action=payment&amp;type=excel&amp;";
			echo $EVT_ID ? "event_id=" . $EVT_ID : "all_events=true"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>">
				<?php _e('Export to Excel', 'event_espresso'); ?>
			</a>

			<?php if ($EVT_ID) { ?>
				<a style="margin-left:5px"  class="button-secondary"  href="admin.php?page=events&amp;event_admin_reports=add_new_attendee&amp;event_id=<?php echo $EVT_ID; ?>">
					<?php _e('Add Attendee', 'event_espresso') ?>
				</a>
			<?php } ?> 

			<?php if ($EVT_ID) { ?>
				<a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $EVT_ID ?>">
					<?php _e('Edit Event', 'event_espresso') ?>
				</a>
			<?php } ?> 

			<?php do_action('action_hook_espresso_attendee_list_admin_buttons', $EVT_ID); ?>

		</div>

	</form>

	<h4 style="clear:both"><?php _e('Legend', 'event_espresso'); ?></h4>

	<dl style="float:left; margin-left:10px; width:200px">
		<dt>
		<?php event_espresso_paid_status_icon('Completed') ?> - <?php _e('Completed', 'event_espresso'); ?>
		</dt>
		<dt>
		<?php event_espresso_paid_status_icon('Incomplete') ?> - <?php _e('Incomplete', 'event_espresso'); ?>
		</dt>
		<dt>
		<?php event_espresso_paid_status_icon('Pending') ?> - <?php _e('Pending', 'event_espresso'); ?>
		</dt>
		<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/money.png" width="16" height="16" alt="<?php _e('Payment Details', 'event_espresso'); ?>" /> - <?php _e('Payment Details', 'event_espresso'); ?>
		</dt>
	</dl>

	<dl style="float:left; margin-left:10px; width:200px">
		<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_link.png" width="16" height="16" alt="<?php _e('Resend Details', 'event_espresso'); ?>" /> - <?php _e('Resend Email', 'event_espresso'); ?>
		</dt>
		<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/page_white_acrobat.png" width="16" height="16" alt="<?php _e('Download Invoice', 'event_espresso'); ?>" /> - <?php _e('Download Invoice', 'event_espresso'); ?>
		</dt>
		<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/user_edit.png" width="16" height="16" alt="<?php _e(' Attendee Details', 'event_espresso'); ?>" /> - <?php _e('Attendee Details', 'event_espresso'); ?>
		</dt>
	</dl>
	<?php
	$hide = $EVT_ID ? '1,5' : '1,3';
	$hide .= $ticketing_installed ? ',8,12,13' : ',11,12';
	?>
	<script>
		jQuery(document).ready(function($) {
			/* show the table data */
			var mytable = $('#table').dataTable( {
				"sDom": 'Clfrtip',
				"bAutoWidth": false,
				"bStateSave": true,
				"sPaginationType": "full_numbers",
				"oLanguage": {    "sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong> (eg, email, txn id, event, etc.)",
					"sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" },
				"aoColumns": [
					{ "bSortable": false },
					null,
					null,
					null,
					null,
					null,
					null,
					null,//Qty/Attended
	<?php echo $ticketing_installed ? "null," : ''; ?>//Date Attended
									null,
									null,
									null,
									null,
									null,
									{ "bSortable": false }
								],
								"aoColumnDefs": [
									{ "bVisible": false, "aTargets": [<?php echo $hide; ?>] }
								],
								"oColVis": {
									"aiExclude": [0,2],
									"buttonText": "Filter: Show / Hide Columns",
									"bRestore": true
								},
							});
						});
	</script>
	<?php
}
