<?php
//Function for editing existing questions
function event_espresso_form_builder_edit(){

	global $wpdb;
	$values=array(
		array('id'=>'Y','text'=> __('Yes','event_espresso')),
		array('id'=>'N','text'=> __('No','event_espresso'))
	);
									
	$question_id = $_REQUEST['question_id'];
	$questions = espresso_get_user_questions(null, $question_id);
	if ( count($questions) > 0 ) {
		foreach ($questions as $question) {
			$question_id = $question->id;
			$question_name = stripslashes($question->question);
			$question_values = stripslashes($question->response);
			$question_type = stripslashes($question->question_type);
			$required = stripslashes($question->required);
			$sequence = $question->sequence;
			$required_text = $question->required_text;
			$price_mod = $question->price_mod;
			$admin_only = $question->admin_only;
			$system_name = $question->system_name;
			if ($question->system_name !=''){
				$system_question = true;
			} else {
				$system_question = false;
			}
            $wp_user = $question->wp_user;
            
            if ( function_exists( 'espresso_member_data' ) ) {
                if (function_exists( 'espresso_is_admin' ) ) {  
                    // If the user doesn't have admin access get only user's own question groups 
                    if ( !espresso_is_admin() ) { 
                        if ( espresso_member_data('id') != $wp_user ) {
                            echo '<h2>' . __('Sorry, you do not have permission to edit this question.', 'event_espresso') . '</h2>';
                            return;
                        }
                    }
                }
            }
			
?>

<div class="metabox-holder">
	<div class="postbox">
	
		<div class="handlediv" title="Click to toggle"><br></div>
		
		<h3 class="hndle"><?php _e('Edit Question','event_espresso'); ?></h3>
		
		<div class="inside">
			<form id="edit-new-question-form" class="espresso_form" name="newquestion" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
			
				<p class="intro">
					<?php _e('By default all event registrants will be asked for their first name, last name, and email address.','event_espresso'); ?>
				</p>
				
				<?php
					if ($system_question == true){
						echo '<p class="yellow_inform">'.__('Attention: This is a "System Question", some settings may be disabled.','event_espresso').'</p>';
					}
					?>

				<table class="form-table">
					<tbody>
				
						<tr>
							<th>
				  				<label for="question"><?php _e('Question','event_espresso'); ?><em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em></label>
							</th>
							<td>
				  				<input name="question" id="question" class="wide-text" value="<?php echo htmlspecialchars( $question_name, ENT_QUOTES, 'UTF-8' ); ?>" type="text">
							</td>
						</tr>
						
						<tr>
					  		<th id="question-type-select">
			  					<label for="question_type"><?php _e('Type','event_espresso'); ?></label>
							</th>
							<td>
				 				<?php
								$q_values	=	array(
									array('id'=>'TEXT','text'=> __('Text','event_espresso')),
									array('id'=>'TEXTAREA','text'=> __('Text Area','event_espresso')),
									array('id'=>'SINGLE','text'=> __('Radio Button','event_espresso')),
									array('id'=>'DROPDOWN','text'=> __('Drop Down','event_espresso')),
									array('id'=>'MULTIPLE','text'=> __('Checkbox','event_espresso')),
									//array('id'=>'DATE','text'=> __('Date Picker','event_espresso'))
								);
								if ($system_question == true){
									$q_values=array(array('id'=>'TEXT','text'=> __('Text','event_espresso')));
								}

								echo select_input( 'question_type', $q_values,  $question_type, 'id="question_type"');
								?>
							</td>
						</tr>
						
						<tr id="add-question-values">
							<th>
				  			<label for="values"><?php _e('Answer Options','event_espresso'); ?><em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em></label>
							</th>
							<td>
				  			<input name="values" id="question-values" class="wide-text" value="<?php echo htmlspecialchars( $question_values, ENT_QUOTES, 'UTF-8' ); ?>" type="text" />
							<br />
								<span class="description"><?php _e('A comma seperated list of values. Eg. black, blue, red', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<?php do_action('action_hook_espresso_generate_price_mod_form_inputs', $values, $question ); ?>
						
						<tr>
							<th>
								<label for="required"><?php _e('Required','event_espresso'); ?></label>
							</th>
							<td>
					  			<?php
									if ($system_question == true && ($system_name =='fname'||$system_name =='lname'||$system_name =='email')){
											$values=array(array('id'=>'Y','text'=> __('Yes','event_espresso')));
									}
									echo select_input('required', $values, $required); 
								?>&nbsp;&nbsp; 
								<span class="description"><?php _e('Mark this question as required.', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<tr>
							<th>
								<label for="required_text">
									<?php _e('Required Text','event_espresso'); ?>
								</label>
							</th>
							<td>
								<input name="required_text" id="required_text" class="wide-text" value="<?php echo htmlspecialchars( $required_text, ENT_QUOTES, 'UTF-8' ); ?>" type="text" /><br />
								<span class="description"><?php _e('Text displayed if not completed.', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<tr>
							<th>
				  			<label for="admin_only">
									<?php _e('Admin View Only','event_espresso'); ?>
				  			</label>
							</th>
							<td>						
							<?php
								if ($system_question == true && ($system_name =='fname'||$system_name =='lname'||$system_name =='email')){
									$values=array(array('id'=>'N','text'=> __('No','event_espresso')));
								}
								echo select_input('admin_only', $values, $admin_only);
								?>&nbsp;&nbsp; 
								<span class="description"><?php _e('Only the administrator can see this field.', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<tr>
							<th>
				  			<label for="sequence">
									<?php _e('Order/Sequence','event_espresso'); ?>
								</label>
							</th>
							<td>
				  			<input name="sequence" id="sequence" class="tiny-text" value="<?php echo $sequence; ?>" type="text" />
							</td>
						</tr>
				
						<tr>
							<th>
							</th>
							<td>
								<input name="edit_action" value="update" type="hidden">
								<input type="hidden" name="action" value="edit_question">
								<input name="question_id" value="<?php echo $question_id; ?>" type="hidden">
								<?php wp_nonce_field( 'espresso_form_check', 'edit_question' ) ?><br/>
								<input class="button-primary" name="Submit" value="Update Question" type="submit">
							</td>
						</tr>
						
					</tbody>
				</table><br/>
		
			</form>
		</div>
		
	 </div>
</div>
<br/><br/>
<?php
		}		
	} else {
		 _e('Nothing found!','event_espresso');
	}
}
