<?php
//Function to update questions in the database
function event_espresso_form_builder_update(){
	global $wpdb;
	$question_text = str_replace("'", "&#039", $_POST['question']);
	$question_id = $_POST['question_id'];
	$question_type = $_POST['question_type'];
	$sequence = $_POST['sequence'];
	$values = empty($_POST['values']) ? '' : str_replace("'", "&#039;", $_POST['values']);
	$required = $_POST['required'];
	$required_text = $_POST['required_text'];
	$admin_only = $_POST['admin_only'];
	$is_global = isset($_POST['is_global']) && $_POST['is_global'] != '' ? 1 : 0;

	$wpdb->query("UPDATE " . EVENTS_QUESTION_TABLE . " SET question_type = '" . $question_type . "', question = '" . $question_text . "', response = '" . $values . "', required = '" . $required . "',admin_only = '" . $admin_only . "', required_text = '" . $required_text . "', sequence = '" . $sequence . "', is_global = '" . $is_global . "' WHERE id = '" . $question_id . "'");
}