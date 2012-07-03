Nothing Here
<?php 
/*require_once("list_attendee_payments.php");
function event_admin_reports(){
?>
<div class="wrap meta-box-sortables ui-sortable" id="attendee_lists">
<div id="event_reg_theme" class="wrap">
<div id="icon-options-event" class="icon32"></div>  
<?php
	switch($_REQUEST['event_admin_reports']){
		case 'list_attendee_payments':
			echo '<h2>' . __('Attendee Payments','event_espresso') .'</h2>';
			list_attendee_payments();
		break;
		case 'event_list_attendees':
			echo '<h2>' . __('Attendee Reports','event_espresso') .'</h2>';
			event_list_attendees();
		break;
		case 'edit_attendee_record':
		require_once("edit_attendee_record.php");
			echo '<h2>' . __('Edit Attendee Data','event_espresso') .'</h2>';
			edit_attendee_record();
		break;
		case 'enter_attendee_payments':
			require_once("enter_attendee_payments.php");
			//echo '<h2>' . __('Edit Attendee Payment','event_espresso') .'</h2>';
			enter_attendee_payments();
		break;
		case 'add_new_attendee':
			require_once("add_new_attendee.php");
			echo '<h2>' . __('Add New Attendee','event_espresso') .'</h2>';
			add_new_attendee($_REQUEST['event_id']);
		break;
		case 'event_newsletter':
			require_once("event_newsletter.php");
			echo '<h2>' . __('Event Newsletter','event_espresso') .'</h2>';
			event_newsletter($_REQUEST['event_id']);
		break;
		case 'resend_email':
			echo '<div id="message" class="updated fade"><p><strong>Resending email to attendee.</strong></p></div>';
			//event_espresso_email_confirmations($_REQUEST['registration_id'],true,true);
			event_espresso_email_confirmations(array('registration_id' => $_REQUEST['registration_id'], 'send_admin_email' => 'true', 'send_attendee_email' => 'true'));
			list_attendee_payments();		
		break;
		default:
			require_once("process_payments.php");
			echo '<h2>' . __('Attendee Reports','event_espresso') .'</h2>';
			event_process_payments();
		break;
	}
?>
 </div>
</div>
</div>
<?php
}*/
