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
								<span class="description"><?php _e('A comma seperated list of values. Eg. black, blue, red', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<tr id="add-price-modifier">
							<th>
								<label for="price_mod">
									<?php _e('Modifies Event Price', 'event_espresso');?>
								</label>
							</th>
							<td>
								<?php echo select_input('price_mod', $values, 'N'); ?>&nbsp;&nbsp; 
									<span class="description">
									<?php _e('If this is set to "Yes", then you can add price modifers to the answer options you entered above.', 'event_espresso');?><br />
									<?php _e('Price modifiers will then be added (or subtracted if negative) from the ticket price for that event.', 'event_espresso');?><br />
									<?php _e('Enter price modifiers with a pipe | ( Shift+\ ) separating prices from the answer options.', 'event_espresso');?><br />
									<?php _e('Eg. If the question was "Choice of Dinner Entrée", answer options might be: " steak|39.95, chicken|34.95, vegan|29.95 "', 'event_espresso'); ?>
									<?php _e('If you need to set a limit on how many items are available, then simply add a second pipe followed by the max availalbe number for that item.', 'event_espresso');?><br />
									<?php _e('Eg. If the question was for choice between two workshops at a conference with limited seating: "Creative Writing|34.95|25, Technical Writing|34.95|35"', 'event_espresso'); ?>
								</span>
							</td>
						</tr>
						
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
