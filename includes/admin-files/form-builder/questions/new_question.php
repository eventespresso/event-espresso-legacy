<?php
//Function for adding new questions
function event_espresso_form_builder_new(){
?>
<div class="metabox-holder">
  <div class="postbox">
	<h3><?php _e('Add New Questions','event_espresso'); ?></h3>
    <div class="inside">
    <form name="newquestion" method="post" action="">
    <ul>
	<li><?php _e('Add questions using the form below.  By default all participants will be asked for their first name, last name, and email address.','event_espresso'); ?></li>
    <li><label for="question"><?php _e('Question:','event_espresso'); ?></label><br>
<input name="question" id="question" size="50" value="" type="text"></li>
    <li><label for="question_type"><?php _e('Type:','event_espresso'); ?></label><br>
			<select name="question_type" id="question_type">
			  <option value="TEXT">Text</option>
			  <option value="TEXTAREA">Text Area</option>
			  <option value="SINGLE">Single</option>
			  <option value="MULTIPLE">Multiple</option>
			  <option value="DROPDOWN">Drop Down</option>
			</select>
            </li>
            <li><label for="values"><?php _e('Values:','event_espresso'); ?></label><br />
<?php _e('A comma seperated list of values. Eg. black, blue, red', 'event_espresso'); ?><br>
            <input name="values" id="values" size="50" value="" type="text"></li>
            <li><label for="required"><?php _e('Required:','event_espresso'); ?></label>
            <input name="required" id="required" type="checkbox"></li>
          <li>
            <li><label for="admin_only"><?php _e('Admin view only:','event_espresso'); ?></label>
            <input name="admin_only" id="admin_only" type="checkbox"></li>
          <li>
          <li>
          <label for="required_text">
            <?php _e('Required Text:','event_espresso'); ?>
          </label>
          <br><?php _e('Text displayed if not completed.', 'event_espresso'); ?><br>
          <input name="required_text" id="required_text" size="50" type="text">
        </li>
          <label for="sequence">
            <?php _e('Order/Sequence:','event_espresso'); ?>
          </label>
          <br>
          <input name="sequence" id="sequence" size="50" value="<?php echo $sequence; ?>" type="text">
        </li>
            <input name="action" value="insert" type="hidden">
            <li><input name="Submit" value="Add Question" type="submit"></li>
    </ul>
  	</form>
   </div>
    </div>
    </div>
<?php
}