<?php
//Function to edit a group of questions
function event_espresso_form_group_edit(){
	global $wpdb;
	$groups = $wpdb->get_results("SELECT qg.id, qg.group_name, qg.group_order, qg.group_identifier, qg.group_description, qg.show_group_name, qg.show_group_description
                                    FROM  " . EVENTS_QST_GROUP_TABLE . " qg
                                    WHERE qg.id = '" . $_REQUEST['group_id'] . "' ORDER BY id ASC");
	if ($wpdb->num_rows > 0) {
		foreach ($groups as $group) {
			$group_id = $group->id;
			$group_order = $group->group_order;
			$group_name = stripslashes($group->group_name);
			$group_identifier = stripslashes($group->group_identifier);
			$group_description = stripslashes($group->group_description);
			$question = stripslashes($group->question);
			$show_group_name = $group->show_group_name;
			$show_group_description = $group->show_group_description;
		}
	}


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
	<li><strong><?php _e('Group Information','event_espresso'); ?></strong></li>
    <li><label for="group_name"><?php _e('Group Name:','event_espresso'); ?></label><br>
		<input name="group_name" id="group_name" size="50" value="<?php echo $group_name ?>" type="text"></li>
         <li><label for="group_order"><?php _e('Group Order:','event_espresso'); ?></label><br>
		<input name="group_order" id="group_order" size="6" value="<?php echo $group_order ?>" type="text"></li>
	<li><label for="group_identifier"><?php _e('Group Identifier:','event_espresso'); ?></label><br>
		<input name="group_identifier" id="group_identifier" size="50" value="<?php echo $group_identifier ?>" type="text"></li>
    <li><label for="group_description"><?php _e('Description:','event_espresso'); ?></label><br>
		<textarea name="group_description" cols="40" rows="5"><?php echo $group_description ?></textarea></li>
        <li><label for="show_group_name"><?php _e('Show group name on registration page?','event_espresso'); ?></label><br>
		<input type="checkbox" name="show_group_name" id="show_group_name" value="1" <?php if($show_group_name != 0):?> checked="checked"<?php endif;?> /></li>
        <li><label for="show_group_description"><?php _e('Show group description on registration page?','event_espresso'); ?></label><br>
		<input type="checkbox" name="show_group_description" id="show_group_description" value="1" <?php if($show_group_description != 0):?> checked="checked"<?php endif;?> /></li>
        <li><input name="Submit" value="Update Group" type="submit"></li>
    </ul>
   </td>
    <td width="50%" align="left" valign="top"> <ul>
	<li><strong><?php _e('Questions','event_espresso'); ?></strong></li>
	<?php
//Questions that are already associated with this group

	$questions = $wpdb->get_results("SELECT q.id, q.question, qgr.id as rel_id, q.system_name, qg.system_group
                    FROM " . EVENTS_QUESTION_TABLE . " q
                    JOIN " .  EVENTS_QST_GROUP_REL_TABLE . " qgr
                        on q.id = qgr.question_id
                    JOIN " . EVENTS_QST_GROUP_TABLE . " qg
                        on qg.id = qgr.group_id
                        WHERE qg.id = " .   $_REQUEST['group_id']
                . " ORDER BY q.sequence, id ASC");
                $questions_in_group = '';
                if ($wpdb->num_rows > 0) {
                
		foreach ($questions as $question) {
                    $questions_in_group .= $question->id . ',';
                    $checked = (!is_null($question->rel_id))?'checked="checked"':'';

                    $visibility = (preg_match("/fname|lname|email/",$question->system_name) == 1 && $question->system_group == 1 )?'style="visibility:hidden"':'';
                    
			echo '<li></label><input ' . $checked . ' type="checkbox" ' . $visibility . ' name="question_id[' . $question->id . ']" value="' . $question->id . '" id="question_id_' . $question->id . '"> <label> ' . stripslashes($question->question) .'</label></li>';
		}
              $questions_in_group = substr($questions_in_group,0,-1);
	}
        ?>
    <hr style="width:50%;" align="left" />
<?php

//Questions that are NOT part of this group.
// @todo Make this happen with one query above

    $WHERE = $questions_in_group != ''?" WHERE q.id not in($questions_in_group) ":'';
    $questions = $wpdb->get_results("SELECT q.id, q.question 
                    FROM " . EVENTS_QUESTION_TABLE . " q
                    $WHERE
                    ORDER BY id ASC");

                if ($wpdb->num_rows > 0) {

		foreach ($questions as $question) {
                    $checked = '';
			echo '<li><label><input ' . $checked . ' type="checkbox" name="question_id[' . $question->id . ']" value="' . $question->id . '" id="question_id_' . $question->id . '">' . stripslashes($question->question) . '</label></li>';
		}
	}

?>
</ul></td>
  </tr>
</table>
    
	
    <input type="hidden" name="edit_action" value="update_group">
<input type="hidden" name="action" value="update_group">
<input type="hidden" name="group_id" value="<?php echo $group_id?>">
	
  	</form>
    </div>
  </div>
    </div>
<?php		
}