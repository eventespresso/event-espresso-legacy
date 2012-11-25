<?php
/*This page adds/edits the individual questions*/
//Function to add a question to the database
function event_espresso_form_builder_insert(){

	global $wpdb, $current_user;

	$success = FALSE;
	$errors = FALSE;

	$enum_values=array( 'Y' => 'Y', 'N' => 'N' );
//	$event_id = empty($_REQUEST['event_id']) ? 0 : $_REQUEST['event_id'];
//	$event_name = empty($_REQUEST['event_name']) ? '' : $_REQUEST['event_name'];
	
	$question= isset( $_POST['question'] ) && ! empty( $_POST['question'] ) ? wp_strip_all_tags( $_POST['question'] ) : FALSE;
	
	if ( ! $question ) {
		$errors = __('Question is a required field. You need to enter a value for it in order to proceed.', 'event_espresso');
	} else {
		
		$set_cols_and_values = array( 
			'sequence'			=> isset( $_POST['sequence'] ) && ! empty( $_POST['sequence'] ) ? absint( $_POST['sequence'] ) : 0,
			'question_type'	=>isset( $_POST['question_type'] ) && ! empty( $_POST['question_type'] ) ? wp_strip_all_tags( $_POST['question_type'] ) : 'TEXT', 
			'question'			=> $question, 
			'response'			=> isset( $_POST['values'] ) && ! empty( $_POST['values'] ) ? wp_strip_all_tags( $_POST['values'] ) : '', 
			'required'			=> isset( $_POST['required'] ) && isset( $enum_values[ $_POST['required'] ] ) ?  $enum_values[ $_POST['required'] ]  : 'N',
			'required_text'	=> isset( $_POST['required_text'] ) && ! empty( $_POST['required_text'] ) ? wp_strip_all_tags( $_POST['required_text'] ) : '',
			'price_mod'		=> isset( $_POST['price_mod'] ) && isset( $enum_values[ $_POST['price_mod'] ] ) ?  $enum_values[ $_POST['price_mod'] ]  : 'N',
			'admin_only'		=> isset( $_POST['admin_only'] ) && isset( $enum_values[ $_POST['admin_only'] ] ) ?  $enum_values[ $_POST['admin_only'] ]  : 'N',
			'wp_user'				=> function_exists( 'espresso_member_data' ) ? $current_user->ID : 1
		);
		
		$data_format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' );
		$insert_success = $wpdb->insert( EVENTS_QUESTION_TABLE, $set_cols_and_values, $data_format );
		// unless there was an actual error
		if ( $insert_success !== FALSE ) {
			$value = htmlspecialchars( trim( stripslashes( $_REQUEST['question'] )), ENT_QUOTES, 'UTF-8' );
			$success = __('The question', 'event_espresso') . ' "' . $value . '" ' . __('has been successfully added.', 'event_espresso'); 
		} else {
			$errors = __('An error occured. The question could not be saved to the database.', 'event_espresso');
		}
		
	}
	

	
	if ( $success ) { 
?>

		<div id="message" class="updated fade">
			<p>
				<strong><?php echo $success; ?></strong>
			</p>
		</div>
		
<?php 
	} elseif ( $errors ) { 
?>

		<div id="message" class="error">
			<p>
				<strong><?php echo $errors; ?></strong>
			</p>
		</div>

<?php

	}
}
