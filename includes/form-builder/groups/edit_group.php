<?php

//Function to edit a group of questions
function event_espresso_form_group_edit() {
    
    
    global $wpdb;
    $groups = espresso_get_user_question_groups(null, false, false, $_REQUEST['group_id']);
    
    if ( count($groups) > 0 ) {
        foreach ($groups as $group) {
            $group_id = $group->id;
            $group_order = $group->group_order;
            $group_name = stripslashes($group->group_name);
            $group_identifier = stripslashes($group->group_identifier);
            $group_description = stripslashes($group->group_description);
            $question = stripslashes(empty($group->question) ? '' : $group->question);
            $show_group_name = $group->show_group_name;
            $show_group_description = $group->show_group_description;
            $wp_user = $group->wp_user;
			
			if ($group->system_group > 0){
				$system_group = true;
			} else {
				$system_group = false;
			}
	
        }
    }
    
    if ( function_exists( 'espresso_member_data' ) ) {
        if (function_exists( 'espresso_is_admin' ) ) {  
            // If the user doesn't have admin access get only user's own question groups 
            if ( !espresso_is_admin() ) { 
                if ( espresso_member_data('id') != $wp_user ) {
                    echo '<h2>' . __('Sorry, you do not have permission to edit this question group.', 'event_espresso') . '</h2>';
                    return;
                }
            }
        }
    }
    
    ?>
    <div id="add-edit-new-group" class="metabox-holder">
        <div class="postbox">
					 	<div title="Click to toggle" class="handlediv"><br /></div>
            <h3 class="hndle"><?php _e('Edit Group - ', 'event_espresso'); ?><span><?php echo $group_name ?></span></h3>
             <div class="inside">
			 <?php
					if ($system_group == true){
						echo '<p class="yellow_inform">'.__('Attention: This is a "System Group", some settings may be disabled.','event_espresso').'</p>';
					}
					?>
                <form name="newgroup" method="post" action="<?php echo $_SERVER["REQUEST_URI"] ?>">
                    <table id="table-edit-group" class="ee-tables" border="0">
                        <tr>
                            <td class="a"  valign="top">
                                <fieldset id="general-group-info">
									<legend><?php _e('Group Information', 'event_espresso') ?></legend>
								<ul>

                                    <li>
                                        <label for="group_name"><?php _e('Group Name:', 'event_espresso'); ?></label>
                                        <input name="group_name" id="group_name" size="50" value="<?php echo $group_name ?>" type="text" />
                                    </li>

                                    <li>
                                        <label for="group_order"><?php _e('Group Order:', 'event_espresso'); ?></label>
                                        <input name="group_order" id="group_order" size="6" value="<?php echo $group_order ?>" type="text" />
                                    </li>

                                    <li>
                                        <label for="group_identifier"><?php _e('Group Identifier:', 'event_espresso'); ?></label>
                                        <input disabled="disabled" name="group_identifier" id="group_identifier" size="50" value="<?php echo $group_identifier ?>" type="text" />
                                    </li>

                                    <li>
                                        <label for="group_description"><?php _e('Description:', 'event_espresso'); ?></label>
                                        <textarea name="group_description" cols="40" rows="5"><?php echo $group_description ?></textarea>
                                    </li>

                                    <li>
                                        <label for="show_group_name"><?php _e('Show group name on registration page?', 'event_espresso'); ?></label>
                                        <input type="checkbox" name="show_group_name" id="show_group_name" value="1" <?php if ($show_group_name != 0): ?> checked="checked"<?php endif; ?> />
                                    </li>

                                    <li>
                                        <label for="show_group_description"><?php _e('Show group description on registration page?', 'event_espresso'); ?></label>
                                        <input type="checkbox" name="show_group_description" id="show_group_description" value="1" <?php if ($show_group_description != 0): ?> checked="checked"<?php endif; ?> />
                                    </li>

                                </ul>
																</fieldset>
                            </td>
                            <td class="b"  valign="top">
                              <fieldset id="questions-for-group">
																		<legend><?php _e('Questions', 'event_espresso') ?></legend>															
                                
																	<ul>
																	 <li><p><?php _e('Selected Questions for group<span class="info"> Uncheck box to remove question from group</span>', 'event_espresso') ?></p></li>
                                    <?php
//Questions that are already associated with this group
                                    $questions = espresso_get_user_questions_for_group( $_REQUEST['group_id'], $wp_user );
                                    if ( count($questions['questions_in_group']) > 0 ) {
                                        foreach ($questions['questions_in_group'] as $question) {
                                            $checked = (!is_null($question->rel_id)) ? 'checked="checked"' : '';

                                            $visibility = (preg_match("/fname|lname|email/", $question->system_name) == 1 && $question->system_group == 1 ) ? 'style="visibility:hidden"' : '';

                                            echo '<li><label><input ' . $checked . ' type="checkbox" ' . $visibility . ' name="question_id[' . $question->id . ']" value="' . $question->id . '" id="question_id_' . $question->id . '" />' . stripslashes($question->question) . '</label></li>';
                                        }
                                        
                                    }
                                    ?>
                                    
																			</ul>
																			<ul id="add-more-questions">
																			<li><p><?php _e('Add further questions to group', 'event_espresso') ?></p></li>
                                    <?php
//Questions that are NOT part of this group.

                                    if ( count($questions['remaining_questions']) > 0) {

                                        foreach ($questions['remaining_questions'] as $question) {
                                            $checked = '';
                                            echo '<li><label><input ' . $checked . ' type="checkbox" name="question_id[' . $question->id . ']" value="' . $question->id . '" id="question_id_' . $question->id . '" />' . stripslashes($question->question) . '</label></li>';
                                        }
                                    }
                                    ?>
                                </ul>
																</fieldset>
                            </td>
                        </tr>
                    </table>

                    <p>
                        <input type="hidden" name="edit_action" value="update_group" />
                        <input type="hidden" name="action" value="update_group" />
                        <input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
                        <input class="button-primary" name="Submit" value="Update Group" type="submit" />
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php
}