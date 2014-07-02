<?php
if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');
//Event Registration Subpage 2 - Add/Delete/Edit Events
require_once('event_functions.php');
require_once("event_list.php");

function event_espresso_manage_events() {
	global $wpdb, $org_options;
	?>
	<div id="configure_organization_form" class="wrap meta-box-sortables ui-sortable">
		<div id="event_reg_theme" class="wrap">
			<div id="icon-options-event" class="icon32"></div>
			<h2>
				<?php
				if ($_REQUEST['page'] == 'events' && (isset($_REQUEST['event_admin_reports']))) {
					switch ($_REQUEST['event_admin_reports']) {
						case 'charts':
							_e('Attendee Reports', 'event_espresso');
							break;
						case 'event_list_attendees':
						case 'resend_email':
						case 'list_attendee_payments':
							_e('Attendee Reports', 'event_espresso');
							if (!empty($_REQUEST['event_id']) && $_REQUEST['event_admin_reports'] != 'add_new_attendee') {
								echo '<a href="admin.php?page=events&amp;event_admin_reports=add_new_attendee&amp;event_id=' . $_REQUEST['event_id'] . '" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Attendee', 'event_espresso') . '</a>';
							}
							break;
						case 'edit_attendee_record':
							_e('Edit Attendee Data', 'event_espresso');
							break;
						case 'enter_attendee_payments':
							_e('Edit Attendee Payment Record', 'event_espresso');
							break;
						case 'add_new_attendee':
							_e('Add New Attendee', 'event_espresso');
							break;
						case 'event_newsletter':
							_e('Email Event Attendees', 'event_espresso');
							break;
					}
				} else {
					_e('Event Overview', 'event_espresso');
					if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'add_new_event')) {
						
					} else {
						echo '<a href="admin.php?page=events&amp;action=add_new_event" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Event', 'event_espresso') . '</a>';
					}
				}
				?>
			</h2>
			<?php
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'copy_event') {
				require_once("copy_event.php");
				$new_id = copy_event();
				$_REQUEST['action'] = 'edit';
				$_REQUEST['event_id'] = $new_id;
				$form_action = add_query_arg(array('action' => 'edit', 'event_id' => $new_id));
			} else {
				$form_action = $_SERVER["REQUEST_URI"];
			}

			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
				event_espresso_delete_event();
			}

			//Delete recurrence series of events
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_recurrence_series') {
				$r = $wpdb->get_results("SELECT id FROM " . EVENTS_DETAIL_TABLE . " ed WHERE recurrence_id = " . $_REQUEST['recurrence_id']);

				if ($wpdb->num_rows > 0) {
					foreach ($r as $row) {
						event_espresso_delete_event($row->id);
					}
				}
			}

			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'csv_import') {
				require_once ('csv_import.php');
				csv_import();
			}

			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') {
				require_once("insert_event.php");
				add_event_to_db();
			}

			//Update the event
			if (isset($_REQUEST['edit_action']) && $_REQUEST['edit_action'] == 'update') {
				require_once("update_event.php");
				update_event();
			}
			//If we need to add or edit a new event then we show the add or edit forms
			if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'add_new_event' || $_REQUEST['action'] == 'edit')) {
				?>
				<form id="espresso_event_editor" name="form" method="post" action="<?php echo $form_action ?>">
					<?php
					if ($_REQUEST['action'] == 'edit') {//show the edit form
						require_once("edit_event.php");
						edit_event($_REQUEST['event_id']);
					} else {//Show the add new event form
						require_once("add_new_event.php");
						add_new_event();
					}
					?>
					<br class="clear" />
				</form>
				<!-- /event_reg_theme -->
				<?php
			} else {
				//If we are not adding or editing an event then show the list of events
				if (isset($_REQUEST['event_admin_reports'])) {
					switch ($_REQUEST['event_admin_reports']) {
						case 'charts':
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/event_list_attendees.php");
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/charts.php");
							espresso_charts();
							break;
						case 'list_attendee_payments':
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/event_list_attendees.php");
							event_list_attendees();
							break;
						case 'event_list_attendees':
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/event_list_attendees.php");
							event_list_attendees();
							break;
						case 'edit_attendee_record':
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/event_list_attendees.php");
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/edit_attendee_record.php");
							edit_attendee_record();
							break;
						case 'enter_attendee_payments':
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/event_list_attendees.php");
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/enter_attendee_payments.php");
							enter_attendee_payments();
							break;
						case 'add_new_attendee':
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/event_list_attendees.php");
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-reports/add_new_attendee.php");
							add_new_attendee($_REQUEST['event_id']);
							break;
						case 'event_newsletter':
							if (file_exists(EVENT_ESPRESSO_INCLUDES_DIR . "admin-files/event_newsletter.php")) {
								require_once(EVENT_ESPRESSO_INCLUDES_DIR . "admin-files/event_newsletter.php");
								event_newsletter($_REQUEST['event_id']);
							} else {
								require_once("event_newsletter.php");
							}

							break;
						case 'resend_email':
							require_once(EVENT_ESPRESSO_INCLUDES_DIR . "/admin-reports/event_list_attendees.php");
							echo '<div id="message" class="updated fade"><p><strong>Resending email to attendee.</strong></p></div>';
							event_espresso_email_confirmations(array('registration_id' => $_REQUEST['registration_id'], 'send_admin_email' => 'false', 'send_attendee_email' => 'true'));
							event_list_attendees();
							break;
						default:
							event_espresso_edit_list();
							break;
					}
				} else {
					event_espresso_edit_list();
				}
			}
//Do not remove anything below this line. These are the color box popups.
			?></div>
	</div>
	<div id="email_manager_info" style="display:none">
		<h2><?php _e('Pre-existing Emails', 'event_espresso'); ?></h2>
		<p><?php _e('These emails will override the custom email if a pre-existing email is selected. You must select "Yes" in the "Send custom confirmation emails for this event?" above.', 'event_espresso'); ?></p>
	</div>
	<div id="coupon_code_info" style="display:none">
		<h2><?php _e('Coupon/Promo Code', 'event_espresso'); ?></h2><p><?php _e('This is used to apply discounts to events.', 'event_espresso'); ?></p><p><?php _e('A coupon or promo code can be anything you want. For example: Say you have an event that costs', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>200. <?php _e('If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted', 'event_espresso'); ?>  <?php echo $org_options['currency_symbol'] ?>50.00, <?php _e('bringing the cost of the event to', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>150. </p>
			<p><?php	_e("Note: Promo codes which are set to 'apply to all events', can also be used for this event, provided it allows promo codes.", "event_espresso");?></p>
	</div>
	<div id="unique_id_info" style="display:none">
		<h2><?php _e('Event Identifier', 'event_espresso'); ?></h2><p><?php _e('This should be a unique identifier for the event. Example: "Event1" (without quotes.)</p><p>The unique ID can also be used in individual pages using the', 'event_espresso'); ?> [SINGLEEVENT single_event_id="<?php _e('Unique Event ID', 'event_espresso'); ?>"] <?php _e('shortcode', 'event_espresso'); ?>.</p>
	</div>
	<div id="secondary_info" style="display:none">
		<h2><?php _e('Waitlist Events', 'event_espresso'); ?></h2>
		<p><?php _e('These types of events can be used as a overflow or waiting list events.', 'event_espresso'); ?></p>
		<p><?php _e('If an event is set up as an "Waitlist Event," it can be set to not appear in your event listings template. You may need to customize your event_listing.php file to make this work. For more information, please', 'event_espresso'); ?> <a href="http://eventespresso.com/wiki/create-a-waiting-list-for-your-event/" target="_blank"><?php _e('view the documentation', 'event_espresso'); ?></a>.</p>
	</div>
	<div id="external_URL_info" style="display:none">
		<h2><?php _e('Alternative Registration Page', 'event_espresso'); ?></h2>
		<p><?php _e('This option will override your existing registration page and send attendees to the URL that is entered.', 'event_espresso'); ?></p>
	</div>
	<div id="alt_email_info" style="display:none">
		<h2><?php _e('CC Email Address', 'event_espresso'); ?></h2>
		<p><?php _e('If an email address is entered, then admin email notifications for the event will also be sent to this address.', 'event_espresso'); ?></p>
	</div>
	<div id="status_types_info" style="display:none;">
		<h2><?php _e('Event Status Types', 'event_espresso'); ?></h2>
		<ul>
			<li><strong><?php _e('Public', 'event_espresso'); ?></strong><br /><?php _e('This type if event will appear in the event listings. It is a live event (not deleted, ongoing or secondary.)', 'event_espresso'); ?></li>
			<li><strong><?php _e('Waitlist', 'event_espresso'); ?></strong><br /><?php _e('This type of event can be hidden and used as a waiting list for a primary event. Template customizations may be required. For more information, please', 'event_espresso'); ?> <a href="http://eventespresso.com/wiki/create-a-waiting-list-for-your-event/" target="_blank"><?php _e('view the documentation', 'event_espresso'); ?></a></li>
			<li><strong><?php _e('Ongoing', 'event_espresso'); ?></strong><br /><?php _e('This type of an event can be set to appear in your event listings and display a registration page. Template customizations are required.', 'event_espresso'); ?></li>
			<li><strong><?php _e('Deleted', 'event_espresso'); ?></strong><br /><?php _e('This event type will not appear in the event listings and will not display a registrations page. Deleted events can still be accessed in the', 'event_espresso'); ?> <a href="admin.php?page=events"><?php _e('Event Overview', 'event_espresso'); ?></a> <?php _e('page by filtering the status to deleted.', 'event_espresso'); ?>.</li>
		</ul>
	</div>
	<?php
	echo event_espresso_custom_email_info();
}
