<?php
function edit_event_email(){
	global $wpdb;
	wp_tiny_mce( false , // true makes the editor "teeny"
		array(
			"editor_selector" => "theEditor"//This is the class name of your text field
		)
	);
	$id=$_REQUEST['id'];
	$results = $wpdb->get_results("SELECT * FROM ". EVENTS_EMAIL_TABLE ." WHERE id =".$id);
	foreach ($results as $result){
		$email_id= $result->id;
		$email_name=stripslashes($result->email_name);
		$email_subject=stripslashes($result->email_subject);
		$email_text=stripslashes($result->email_text);
	}
	?>
<!--Add event display-->
<div class="metabox-holder">
  <div class="postbox">
<h3><?php _e('Edit Email:','event_espresso'); ?> <?php echo stripslashes($email_name) ?></h3>
<div class="inside">
  <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
  <input type="hidden" name="email_id" value="<?php echo $email_id; ?>">
  <input type="hidden" name="action" value="update">
   <ul>
    <li><label><strong><?php _e('Email Name:','event_espresso'); ?></strong></label> <input name="email_name" size="25" value="<?php echo stripslashes($email_name);?>"></li> <li><label><strong><?php _e('Email Subject Line:','event_espresso'); ?></strong></label> <input name="email_subject" size="25" value="<?php echo stripslashes($email_subject);?>"></li>
   <li><strong><?php _e('Email Text:','event_espresso'); ?></strong><br />
   <textarea class="theEditor" id="email_text_new" name="email_text"><?php echo $email_text; ?></textarea><br />
<a class="ev_reg-fancylink" href="#custom_email_info"><?php _e('View Custom Email Tags', 'event_espresso'); ?></a>  | <a class="ev_reg-fancylink" href="#custom_email_example"> <?php _e('Email Example','event_espresso'); ?></a>
   </li>
   <li>
    <p>
            <input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Email'); ?>" id="update_email" />
            </p>
    </li>
   </ul>
     </form>
     </div>
	</div>
</div>
<?php 

}