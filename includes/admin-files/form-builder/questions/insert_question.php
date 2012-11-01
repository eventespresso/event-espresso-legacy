<?php
/*This page adds/edits the individual questions*/
//Function to add a question to the database
function event_espresso_form_builder_insert(){
	global $wpdb,$current_user;
	//$wpdb->show_errors();
	$event_id = empty($_REQUEST['event_id']) ? 0 : $_REQUEST['event_id'];
	$event_name = empty($_REQUEST['event_name']) ? '' : $_REQUEST['event_name'];
	$question = str_replace("'", "&#039;", $_POST['question']);
	$question_type = $_POST['question_type'];
	$question_values = empty($_POST['values']) ? NULL : str_replace("'", "&#039;", $_POST['values']);
	$required = !empty($_POST['required']) ? $_POST['required']:'N';
	$admin_only = !empty($_POST['admin_only']) ? $_POST['admin_only']:'N';
   	$sequence = $_POST['sequence'] ?  $_POST['sequence']:'0';
	if (!function_exists('espresso_member_data'))
			$current_user->ID = 1;

		if ($wpdb->query("INSERT INTO " . EVENTS_QUESTION_TABLE . " (question_type, question, response, required, admin_only, sequence,wp_user)"
				. " VALUES ('" . $question_type . "', '" . $question . "', '" . $question_values . "', '" . $required . "', '" . $admin_only . "', " . $sequence . ",'".$current_user->ID."')")){?>
		<div id="message" class="updated fade"><p><strong><?php _e('The question', 'event_espresso'); ?> <?php echo htmlentities2($_REQUEST['question']);?> <?php _e('has been added.', 'event_espresso'); ?></strong></p></div>
	<?php }else { ?>
		<div id="message" class="error"><p><strong><?php _e('The question', 'event_espresso'); ?> <?php echo htmlentities2($_REQUEST['question']);?> <?php _e('was not saved.', 'event_espresso'); ?> <?php //$wpdb->print_error(); ?>.</strong></p></div>

<?php
		}
}
