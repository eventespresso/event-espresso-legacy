<?php
//Function to add a new group of questions
function event_espresso_form_group_new(){
	global $wpdb;
?>
<div class="metabox-holder">
  <div class="postbox">
	<h3><?php _e('Add New Group','event_espresso'); ?></h3>
    <div class="inside">
    <form name="newgroup" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
    <table width="90%" border="0">
  <tr>
    <td width="50%" align="left" valign="top">
 <ul>
    <li><?php _e('Add new groups using the form below.','event_espresso'); ?></li>
    <li><label for="group_name"><?php _e('Group Name:','event_espresso'); ?></label><br>
      <input name="group_name" id="group_name" size="50" value="" type="text"></li>
       <li><label for="group_order"><?php _e('Group Order:','event_espresso'); ?></label><br>
      <input name="group_order" id="group_order" size="6" value="" type="text"></li>
    
    <li><label for="group_identifier"><?php _e('Group Identifier:','event_espresso'); ?></label><br>
      <input name="group_identifier" id="group_identifier" size="50" value="" type="text"></li>
    <li><label for="group_description"><?php _e('Description:','event_espresso'); ?></label><br>
      <textarea name="group_description" cols="40" rows="5"></textarea></li>
    <li><label for="show_group_name"><?php _e('Show group name on registration page?','event_espresso'); ?></label><br>
		<input type="checkbox" name="show_group_name" id="show_group_name" value="1" checked="checked" /></li>
        <li><label for="show_group_description"><?php _e('Show group description on registration page?','event_espresso'); ?></label><br>
		<input type="checkbox" name="show_group_description" id="show_group_description" value="1" checked="checked" /></li>
    
    <input name="Submit" value="Add Group" type="submit">
  </ul>
  
  </td>
  <td>

 <ul>
  <li><p><strong><?php _e('Add Questions:','event_espresso'); ?></strong></p></li>
    <?php
	$questions = $wpdb->get_results("SELECT * FROM " . EVENTS_QUESTION_TABLE . " ORDER BY sequence, id ASC");
	if ($wpdb->num_rows > 0) {
		foreach ($questions as $question) {
			echo '<li><label><input type="checkbox" name="question_id[]" value="' . $question->id . '" id="question_id_' . $question->id . '">' . stripslashes($question->question) . '</label></li>';
		}
	}
?>
  </ul>
</td>
</tr>
</table>

    <input name="action" value="insert_group" type="hidden">
  	</form>
    </div>
  </div>
    </div>
<?php		
}