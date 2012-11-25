<?php
//Function to update questions in the database
function event_espresso_form_builder_update(){

	global $wpdb, $current_user;

	$success = FALSE;
	$errors = FALSE;
	
	$enum_values=array( 'Y' => 'Y', 'N' => 'N' );
	
	$question= isset( $_POST['question'] ) && ! empty( $_POST['question'] ) ? wp_strip_all_tags( $_POST['question'] ) : FALSE;
	$question_id= isset( $_POST['question_id'] ) && ! empty( $_POST['question_id'] ) ? absint( $_POST['question_id'] ) : FALSE;
	
	
	if ( ! $question_id ) {
		$errors = __('An error occured. No ID was received for the question you were attempting to update.', 'event_espresso');
	} elseif ( ! $question ) {
		$errors = __('Question is a required field. You need to enter a value for it in order to proceed.', 'event_espresso');
	} else {
		
		$set_cols_and_values = array( 
				'sequence' 			=> isset( $_POST['sequence'] ) && ! empty( $_POST['sequence'] ) ? absint( $_POST['sequence'] ) : 0,
				'question_type' 	=> isset( $_POST['question_type'] ) && ! empty( $_POST['question_type'] ) ? wp_strip_all_tags( $_POST['question_type'] ) : 'TEXT', 
				'question'			=> $question,
				'response'			=> isset( $_POST['values'] ) && ! empty( $_POST['values'] ) ? wp_strip_all_tags( $_POST['values'] ) : '',
				'required'			=> isset( $_POST['required'] ) && isset( $enum_values[ $_POST['required'] ] ) ?  $enum_values[ $_POST['required'] ]  : 'N',
				'required_text'	=> isset( $_POST['required_text'] ) && ! empty( $_POST['required_text'] ) ? wp_strip_all_tags( $_POST['required_text'] ) : '',
				'price_mod'		=> isset( $_POST['price_mod'] ) && isset( $enum_values[ $_POST['price_mod'] ] ) ?  $enum_values[ $_POST['price_mod'] ]  : 'N',
				'admin_only'		=> isset( $_POST['admin_only'] ) && isset( $enum_values[ $_POST['admin_only'] ] ) ?  $enum_values[ $_POST['admin_only'] ]  : 'N'
		);
		
		$set_format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
		$where_cols_and_values = array( 'id' => $question_id );
		$where_format = array( '%d');
		// run the update
		$upd_success = $wpdb->update( EVENTS_QUESTION_TABLE, $set_cols_and_values, $where_cols_and_values, $set_format, $where_format );
		// unless there was an actual error
		if ( $upd_success !== FALSE ) {
			$value = htmlspecialchars( trim( stripslashes( $_REQUEST['question'] )), ENT_QUOTES, 'UTF-8' );
			$success = __('The question', 'event_espresso') . ' "' . $value . '" ' . __('has been successfully updated.', 'event_espresso'); 
		} else {
			$errors = __('An error occured. The question could not be updated.', 'event_espresso');
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
