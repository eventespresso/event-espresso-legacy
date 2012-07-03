<?php
//Function for editing existing questions
function event_espresso_form_builder_edit(){
	global $wpdb;
	$question_id = $_REQUEST['question_id'];
	$questions = $wpdb->get_results("SELECT * FROM " . EVENTS_QUESTION_TABLE . " WHERE id = '" . $question_id . "'");
	if ($wpdb->num_rows > 0) {
		foreach ($questions as $question) {
			$question_id = $question->id;
			$question_name = stripslashes($question->question);
			$question_values = stripslashes($question->response);
			$question_type = stripslashes($question->question_type);
			$required = stripslashes($question->required);
			$sequence = $question->sequence;
			$required_text = $question->required_text;
			$admin_only = $question->admin_only;
?>

<div class="metabox-holder">
  <div class="postbox">
    <div class="handlediv" title="Click to toggle"><br>
    </div>
    <h3 class="hndle"><span>
      <?php _e('Edit Question','event_espresso'); ?>
      </span></h3>
      <div class="inside">
    <form name="newquestion" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
      <ul>
        <li>
          <?php _e('Edit the question using the form below.  By default all participants will be asked for their first name, last name, and email address.','event_espresso'); ?>
        </li>
        <li>
          <label for="question">
            <?php _e('Question:','event_espresso'); ?>
          </label>
          <br>
          <input name="question" id="question" size="50" value="<?php echo $question_name; ?>" type="text">
        </li>
        <li>
          <label for="question_type">
            <?php _e('Type:','event_espresso'); ?>
          </label>
          <br>
          <?php $values=array(					
        array('id'=>'TEXT','text'=> __('Text','event_espresso')),
        array('id'=>'TEXTAREA','text'=> __('Text Area','event_espresso')),
		array('id'=>'SINGLE','text'=> __('Single','event_espresso')),
		array('id'=>'DROPDOWN','text'=> __('Drop Down','event_espresso')),
		array('id'=>'MULTIPLE','text'=> __('Multiple','event_espresso')));				
		echo select_input('question_type', $values, $question_type); ?>
        </li>
        <li>
          <label for="values">
            <?php _e('Values:','event_espresso'); ?>
          </label>
          <br><?php _e('A comma seperated list of values. Eg. black, blue, red', 'event_espresso'); ?><br>
          <input name="values" id="values" size="50" value="<?php echo $question_values; ?>" type="text">
        </li>
        <li>
          <label for="required">
            <?php _e('Required:','event_espresso'); ?>
          </label><br />
<?php _e('Mark this question as required.', 'event_espresso'); ?><br>
          <?php $values=array(					
        array('id'=>'Y','text'=> __('Yes','event_espresso')),
		array('id'=>'N','text'=> __('No','event_espresso')));				
		echo select_input('required', $values, $required); ?>
        </li>
        <li>
          <label for="">
            <?php _e('Admin View Only:','event_espresso'); ?>
          </label><br />
<?php _e('Only the administrator can see this field.', 'event_espresso'); ?><br>
          <?php $values=array(
        array('id'=>'Y','text'=> __('Yes','event_espresso')),
		array('id'=>'N','text'=> __('No','event_espresso')));
		echo select_input('admin_only', $values, $admin_only); ?>
        </li>
        <li>
          <label for="required_text">
            <?php _e('Required Text:','event_espresso'); ?>
          </label>
          <br><?php _e('Text displayed if not completed.', 'event_espresso'); ?><br>
          <input name="required_text" id="required_text" size="50" value="<?php echo $required_text; ?>" type="text">
        </li>
        <li>
          <label for="sequence">
            <?php _e('Order/Sequence:','event_espresso'); ?>
          </label>
          <br>
          <input name="sequence" id="sequence" size="50" value="<?php echo $sequence; ?>" type="text">
        </li>
        <input name="edit_action" value="update" type="hidden">
        <input type="hidden" name="action" value="edit_question">
        <input name="question_id" value="<?php echo $question_id; ?>" type="hidden">
        <li><input name="Submit" value="Update Question" type="submit"></li>
      </ul>
    </form>
    </div>
  </div>
</div>
<?php
		}
	}else{
		 _e('Nothing found!','event_espresso');
	}
}