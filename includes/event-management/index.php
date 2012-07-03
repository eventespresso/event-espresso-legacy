<?php
//Event Registration Subpage 2 - Add/Delete/Edit Events
require_once('event_functions.php');
require_once("event_list.php");
function event_espresso_manage_events(){
?>
<div id="configure_organization_form" class="wrap meta-box-sortables ui-sortable">
  <div id="event_reg_theme" class="wrap">
    <div id="icon-options-event" class="icon32"></div>
    <h2>
      <?php _e('Event Management','event_espresso'); ?>
    </h2>
<?php
	global $wpdb, $org_options;
        switch ( $_REQUEST['action'] ){
			case ( 'copy_event' ):
				require_once("copy_event.php");
				copy_event();
			break;
			case ( 'delete' ):
				//This function is called from the "/functions/admin.php" file.
				event_espresso_delete_event();
			break;
			case ( 'csv_import' ):
				require_once ('csv_import.php');
				csv_import();
			break;
			case ( 'add' ):
				require_once("insert_event.php");
				add_event_to_db();
			break;
			
			
			//This case statement is being rmoved because the add to calendar function is now in the event editor
			/*case ( 'add_to_calendar' ):
				require_once("add_to_calendar.php");
				add_to_calendar();
			break;*/
		}

		//Update the event
		if ( $_REQUEST['edit_action'] == 'update' ){require_once("update_event.php");update_event();}		
		//If we need to add or edit a new event then we show the add or edit forms
		if ( $_REQUEST['action'] == 'add_new_event' || $_REQUEST['action'] == 'edit' ){
?>
  <form name="form" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
    <div id="poststuff" class="metabox-holder has-right-sidebar">
      <?php
			if ( $_REQUEST['action'] == 'edit'){//show the edit form
				require_once("edit_event.php");
				edit_event($_REQUEST['event_id']);
			}else{//Show the add new event form
				require_once("add_new_event.php");
				add_new_event();
			}
		?>
      <br class="clear" />
    </div>
    <!-- /poststuff -->
  </form>
<!-- /event_reg_theme -->
<?php
		}else{
			//If we are not adding or editing an event then show the list of events
	switch($_REQUEST['event_admin_reports']){
		case 'list_attendee_payments':
		require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/list_attendee_payments.php");
			echo '<h2>' . __('Attendee Payments','event_espresso') .'</h2>';
			list_attendee_payments();
		break;
		case 'event_list_attendees':
		require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/list_attendee_payments.php");
			echo '<h2>' . __('Attendee Reports','event_espresso') .'</h2>';
			event_list_attendees();
		break;
		case 'edit_attendee_record':
		require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/list_attendee_payments.php");
		require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/edit_attendee_record.php");
			echo '<h2>' . __('Edit Attendee Data','event_espresso') .'</h2>';
			edit_attendee_record();
		break;
		case 'enter_attendee_payments':
			require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/list_attendee_payments.php");
			require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/enter_attendee_payments.php");
			//echo '<h2>' . __('Edit Attendee Payment','event_espresso') .'</h2>';
			enter_attendee_payments();
		break;
		case 'add_new_attendee':
			require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/list_attendee_payments.php");
			require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/add_new_attendee.php");
			echo '<h2>' . __('Add New Attendee','event_espresso') .'</h2>';
			add_new_attendee($_REQUEST['event_id']);
		break;
		case 'event_newsletter':
			require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-files/event_newsletter.php");
			echo '<h2>' . __('Event Newsletter','event_espresso') .'</h2>';
			event_newsletter($_REQUEST['event_id']);
		break;
		case 'resend_email':
		require_once(EVENT_ESPRESSO_INCLUDES_DIR."/admin-reports/list_attendee_payments.php");
			echo '<div id="message" class="updated fade"><p><strong>Resending email to attendee.</strong></p></div>';
			//event_espresso_email_confirmations($_REQUEST['registration_id'],true,true);
			event_espresso_email_confirmations(array('registration_id' => $_REQUEST['registration_id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
			list_attendee_payments();		
		break;
		default:
			event_espresso_edit_list();
		break;
	}
			
		}
//Do not remove anything below this line. These are the color box popups.
?></div>
</div>
<div id="email_manager_info" style="display:none">
<?php _e('<h2>Pre-existing Emails</h2>
      <p>These emails will override the custom email if a pre-existing email is selected. You must select "Yes" in the "Send custom confirmation emails for this event?" above.</p>','event_espresso'); ?>
</div>
<div id="coupon_code_info" style="display:none">
  <?php _e('<h2>Coupon/Promo Code</h2>
      <p>This is used to apply discounts to events.</p>
      <p>A coupon or promo code could can be anything you want. For example: Say you have an event that costs '. $org_options['currency_symbol'].'200. If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted '.$org_options['currency_symbol'].'50.00, Bringing the cost of the event to '.$org_options['currency_symbol'].'150.</p>','event_espresso'); ?>
</div>
<div id="unique_id_info" style="display:none">
  <?php _e('<h2>Event Identifier</h2>
      <p>This should be a unique identifier for the event. Example: "Event1" (without qoutes.)</p>
      <p>The unique ID can also be used in individual pages using the [SINGLEEVENT single_event_id="Unique Event ID"] shortcode.</p>','event_espresso'); ?>
</div>
<div id="secondary_info" style="display:none">
<?php _e('<h2>Secondary Events</h2>
      <p>These types of events can be used as a overflow or waiting list events.</p>
      <p>If an event is set up as an "Secondary Event," it can be set to not appear in your event listings template. You will need to customize your event_listing.php file to make this work. For more information, please <a href="http://eventespresso.com/forums/?p=512" target="_blank">visit the forums</a>. ','event_espresso'); ?>
</div>
<div id="external_URL_info" style="display:none">
<?php _e('<h2>Off-site Registration Page</h2>
      <p>If an off-site registration page is entered, it will override your registration page and send attendees to the URL that is entered.</p>','event_espresso'); ?>
</div>
<div id="alt_email_info" style="display:none">
<?php _e('<h2>Alternate Email Address</h2>
      <p>If an alternate email address is entered. Admin email notifications wil be sent to this address instead.</p>','event_espresso'); ?>
</div>
<div id="status_types_info" style="display:none;">
<?php _e('<h2>Event Status Types</h2>
      <ul>
	  	<li><strong>Primary</strong><br />This type if event should always appear in the event lsiting. It is a live event (not deleted, ongoing or secondary.)</li>
		<li><strong>Secondary</strong><br />This type of event can be hidden and used as a waiting list for a primary event. Template customizations are required. For more information, please <a href="http://eventespresso.com/forums/?p=512" target="_blank">visit the forums</a></li>
		<li><strong>Ongoing</strong><br />This type of an event can be set to appear in your event listings and display a registration page. Tempalte customizations are required. For more information, please <a href="http://eventespresso.com/forums/?p=518" target="_blank">visit the forums</a></li>
		<li><strong>Deleted</strong><br />This is event type will not appear in the event listings and will not dispaly a registrations page. Deleted events can still be accessed in the <a href="admin.php?page=events">Attendee Reports</a> page.</li>
	</ul>','event_espresso'); ?>
</div>
<?php
	echo event_espresso_custom_email_info();
}
