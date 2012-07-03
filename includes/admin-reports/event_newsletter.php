<?php
function event_newsletter($event_id=0){
	//print_r($_POST);
	if ($_POST['action']=='send_newsletter'){
		espresso_event_reminder($event_id, $_POST['email_subject'], $_POST['email_text'], $_POST['email_name']);
	}
	//echo $event_id;
	global $wpdb, $org_options;
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);
	
	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "'");
	foreach ($events as $event){

		$event_id = $event->id;
		$event_name = stripslashes_deep($event->event_name);
		$event_description = stripslashes_deep($event->event_desc);
		$start_date =$event->start_date;
		$end_date =$event->end_date;
		$start_time = $event->start_time;
		$end_time = $event->end_time;
		$conf_mail=stripslashes_deep($event->conf_mail);
		
	}
?>
	
    <div class="metabox-holder">
  <div class="postbox">
 
<h3><?php _e('Send an Email to Attendees','event_espresso'); ?></h3>
 <div class="inside">
  <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
  <input type="hidden" name="action" value="send_newsletter"><p><?php _e('Use a <a href="admin.php?page=event_emails">pre-existing email</a>? ', 'event_espresso'); echo espresso_db_dropdown(id, email_name, EVENTS_EMAIL_TABLE, email_name, $email_id, 'desc') . ' <a class="ev_reg-fancylink" href="#email_manager_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?> </p>
  <p><strong>OR</strong></p>
   <ul>
    <li><label for="email_subject"><?php _e('Email Subject Line','event_espresso'); ?></label> <input type="text" name="email_subject" size="25"></li>
   <li><label for="email_text"><?php _e('Email Text','event_espresso'); ?></label><br />
   <textarea class="theEditor" id="email_text" name="email_text"></textarea>
      <br />
<a class="ev_reg-fancylink" href="#custom_email_info"><?php _e('View Custom Email Tags', 'event_espresso'); ?></a>  | <a class="ev_reg-fancylink" href="#custom_email_example"> <?php _e('Email Example','event_espresso'); ?></a>
   </li>
   <li>
    <p>
            <input class="button-primary" type="submit" name="Submit" value="<?php _e('Send Newsletter'); ?>" id="add_new_email" />
            </p>
    </li>
   </ul>
     </form>
     </div>
	</div>
</div>
<div id="email_manager_info" style="display:none">
<?php _e('<h2>Pre-existing Emails</h2>
      <p>This will override the custom email below if selected.</p>','event_espresso'); ?>
</div>
<?php
echo event_espresso_custom_email_info();
}