<?php
//Function for adding new questions
function event_espresso_form_builder_new(){
	$values=array(
		array('id'=>'Y','text'=> __('Yes','event_espresso')),
		array('id'=>'N','text'=> __('No','event_espresso'))
	);
?>
<div class="metabox-holder">
	<div class="postbox">
		<div title="Click to toggle" class="handlediv"><br /></div>
	  	<h3 class="hndle"><?php _e('Add New Questions','event_espresso'); ?></h3>
   		<div class="inside">
		
			<p class="intro">
				<?php _e('By default, all event registrants will be asked for their first name, last name, and email address.','event_espresso'); ?>
			</p>
			
			<form name="newquestion" method="post" action="" id="new-question-form">
				<table class="espresso_form form-table">
					<tbody>
					
						<tr>
							<th>
								<label for="question"><?php _e('Question','event_espresso'); ?><em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em></label>
							</th>
							<td>
								<input class="question-name wide-text"  name="question" id="question" value="" type="text" />
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
								
								echo select_input( 'question_type', $q_values, '', 'id="question_type"');
							?>
							</td>
						</tr>
						
						<tr id="add-question-values">
							<th>
								<label for="question_values"><?php _e('Answer Options','event_espresso'); ?><em title="<?php _e('This field is required', 'event_espresso') ?>"> *</em></label>
							</th>
							<td>
								<input name="question_values" id="question-values" class="wide-text" value="" type="text" /><br />
								<span class="description"><?php _e('A comma separated list of values. Eg. black, blue, red', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<?php do_action('action_hook_espresso_generate_price_mod_form_inputs', $values ); ?>
						
						<tr>
							<th>
								<label class="inline" for="required"><?php _e('Required:','event_espresso'); ?></label>
							</th>
							<td>
								<?php echo select_input('required', $values, 'N'); ?>&nbsp;&nbsp; 
								<span class="description"><?php _e('Mark this question as required.', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<tr>
							<th>
								<label for="required_text"><?php _e('Required Text','event_espresso'); ?></label>
							</th>
							<td>
			 					<input name="required_text" id="required_text" class="wide-text" type="text" />
								<br /><span class="description"><?php _e('Text to display if not completed.', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<tr>
							<th>
								<label class="inline" for="admin_only"><?php _e(' Admin View Only','event_espresso'); ?></label>
							</th>
							<td>
								<?php echo select_input('admin_only', $values, 'N');?>
							</td>
						</tr>
						
						<tr>
							<th>
								<label for="sequence"><?php   _e('Order/Sequence','event_espresso'); ?></label>
							</th>
							<td>
			  				<input name="sequence" id="sequence" class="tiny-text" value="<?php if(isset($sequence)) echo $sequence; ?>" type="text" />
							</td>
						</tr>
						
						<tr>
							<th>
							</th>
							<td>
								<input name="action" value="insert" type="hidden" />
								<?php wp_nonce_field( 'espresso_form_check', 'add_new_question' ); ?><br/>
								<input class="button-primary" name="Submit" value="Add Question" type="submit" />
							</td>
						</tr>
						
					</tbody>
				</table><br/>
			</form>
		</div>
	</div>
</div><br/><br/>
<?php
}
