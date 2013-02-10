<?php

function event_espresso_questions_config_mnu() {
	global $wpdb;

	//Update the questions when re-ordering
	if (!empty($_REQUEST['update_sequence'])) {
		$rows = explode(",", $_POST['row_ids']);
		for ($i = 0; $i < count($rows); $i++) {
			$wpdb->query("UPDATE " . EVENTS_QUESTION_TABLE . " SET sequence=" . $i . " WHERE id='" . $rows[$i] . "'");
		}
		die();
	}

	// get counts
	$total_questions = count(espresso_get_user_questions(null,null,false));
	$total_self_questions = count( espresso_get_user_questions(get_current_user_id(), null, true, true) );
	?>
	<div class="wrap">
		<div id="icon-options-event" class="icon32"> </div>
		<h2><?php echo _e('Manage Questions', 'event_espresso') ?>
			<?php
			if (!isset($_REQUEST['action']) || ($_REQUEST['action'] != 'edit_question' && $_REQUEST['action'] != 'new_question')) {
				echo '<a href="admin.php?page=form_builder&action=new_question" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Question', 'event_espresso') . '</a>';
			}
			?>
		</h2>
		<?php
		ob_start();
		if (function_exists('espresso_is_admin') && espresso_is_admin() == true) {
			?>
			<div style="margin-bottom: 10px;">
				<ul class="subsubsub" style="margin-bottom: 0;clear:both;">
					<li><strong><?php _e('Questions', 'event_espresso'); ?>: </strong> </li>
					<li><a <?php echo ( (!isset($_REQUEST['self']) && !isset($_REQUEST['all']) ) || ( isset($_REQUEST['self']) && $_REQUEST['self'] == 'true') ) ? ' class="current" ' : '' ?> href="admin.php?page=form_builder&self=true"><?php _e('My Questions', 'event_espresso'); ?> <span class="count">(<?php echo $total_self_questions; ?>)</span> </a> | </li>
					<li><a <?php echo (isset($_REQUEST['all']) && $_REQUEST['all'] == 'true') ? ' class="current" ' : '' ?> href="admin.php?page=form_builder&all=true"><?php _e('All Questions', 'event_espresso'); ?> <span class="count">(<?php echo $total_questions; ?>)</span> </a></li>
				</ul>
				<div class="clear"></div>
			</div>
		<?php } ?>

		<div class="meta-box-sortables ui-sortables">
			<?php
			//Update the question
			if (isset($_REQUEST['edit_action']) && $_REQUEST['edit_action'] == 'update') {
				require_once("questions/update_question.php");
				event_espresso_form_builder_update();
			}
			
			$button_style = 'button-primary';
			//Figure out which view to display
			if (isset($_REQUEST['action'])) {
				switch ($_REQUEST['action']) {
					case 'insert':
						if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/form-builder/questions/insert_question.php')) {
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/form-builder/questions/insert_question.php');
							event_espresso_form_builder_insert();
						} else {
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/pricing_table.php');
						}
						break;
					case 'new_question':
						if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/form-builder/questions/new_question.php')) {
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/form-builder/questions/new_question.php');
							event_espresso_form_builder_new();
						} else {
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/pricing_table.php');
						}
						$button_style = 'button-secondary';
						break;
					case 'edit_question':
						require_once("questions/edit_question.php");
						event_espresso_form_builder_edit();
						$button_style = 'button-secondary';
						break;
					case 'delete_question':
						if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/form-builder/questions/delete_question.php')) {
							require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/form-builder/questions/delete_question.php');
							event_espresso_form_builder_delete();
						} else {
							?>
							<div id="message" class="updated fade">
								<p><strong>
										<?php _e('This function is not available in the free version of Event Espresso.', 'event_espresso'); ?>
									</strong></p>
							</div>
							<?php
						}
						break;
				}
			}
			?>
		</div>

		<form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"] ?>">
			<table id="table" class="widefat manage-questions">

				<thead>
					<tr>
						<th class="manage-column" id="cb" scope="col" ><input type="checkbox"></th>

						<th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:25%;">
							<?php _e('Question', 'event_espresso'); ?>
						</th>
						<th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:15%;">
							<?php _e('Values', 'event_espresso'); ?>
						</th>
						<?php if (function_exists('espresso_is_admin') && espresso_is_admin() == true) { ?>
							<th class="manage-column column-creator" id="creator" scope="col" title="Click to Sort" style="width:15%;">
								<?php _e('Creator', 'event_espresso'); ?>
							</th>
						<?php } ?>

						<th class="manage-column column-title" id="values" scope="col" title="Click to Sort"  style="width:10%;">
							<?php _e('Type', 'event_espresso'); ?>
						</th>
						<th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:10%;">
							<?php _e('Required', 'event_espresso'); ?>
						</th>
						<th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:10%;">
							<?php _e('Admin Only', 'event_espresso'); ?>
						</th>

					</tr>
				</thead>
				<tbody>
					<?php
					$questions = espresso_get_user_questions( get_current_user_id() );
				
					if ( count( $questions ) > 0) {
						foreach ($questions as $question) {
							$question_id = $question->id;
							$question_name = stripslashes($question->question);
							$values = stripslashes($question->response);
							$question_type = stripslashes($question->question_type);
							$required = stripslashes($question->required);
							$system_name = $question->system_name;
							$sequence = $question->sequence;
							$admin_only = $question->admin_only;
							$wp_user = $question->wp_user == 0 ? 1 : $question->wp_user;
							?>
							<tr style="cursor: move" id="<?php echo $question_id ?>">
								<td class="checkboxcol"><input name="row_id" type="hidden" value="<?php echo $question_id ?>" />
									<?php if ($system_name == '') : ?>
										<input  style="margin:7px 0 22px 8px; vertical-align:top;" name="checkbox[<?php echo $question_id ?>]" type="checkbox"  title="Delete <?php echo wp_strip_all_tags( $question_name ); ?>">
									<?php else: ?>
										<span><?php echo '<img style="margin:7px 0 22px 8px; vertical-align:top;" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/lock.png" alt="System Questions" title="System Questions" />'; ?></span>
									<?php endif; ?>
								</td>

								<td class="post-title page-title column-title"><strong><a href="admin.php?page=form_builder&amp;action=edit_question&amp;question_id=<?php echo $question_id ?>"><?php echo wp_strip_all_tags( $question_name ); ?></a></strong>
									<div class="row-actions">
										<span class="edit"><a href="admin.php?page=form_builder&amp;action=edit_question&amp;question_id=<?php echo $question_id ?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span>
										<?php if ($system_name == ''): ?><span class="delete"><a onclick="return confirmDelete();" class="submitdelete"  href="admin.php?page=form_builder&amp;action=delete_question&amp;question_id=<?php echo $question_id ?>"><?php _e('Delete', 'event_espresso'); ?></a></span><?php endif; ?>
									</div>
								</td>
								<td class="author column-author"><?php echo $values ?></td>
								<?php if (function_exists('espresso_is_admin') && espresso_is_admin() == true) { ?>
									<td><?php echo espresso_user_meta($wp_user, 'user_firstname') != '' ? espresso_user_meta($wp_user, 'user_firstname') . ' ' . espresso_user_meta($wp_user, 'user_lastname') . ' (<a href="user-edit.php?user_id=' . $wp_user . '">' . espresso_user_meta($wp_user, 'user_nicename') . '</a>)' : espresso_user_meta($wp_user, 'display_name') . ' (<a href="user-edit.php?user_id=' . $wp_user . '">' . espresso_user_meta($wp_user, 'user_nicename') . '</a>)'; ?></td>
								<?php } ?>
								<td class="author column-author"><?php echo $question_type ?></td>
								<td class="author column-author"><?php echo $required ?></td>
								<td class="author column-author"><?php echo $admin_only ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>

			<div>
				<p><input type="checkbox" name="sAll" onclick="selectAll(this)" />
					<strong>
						<?php _e('Check All', 'event_espresso'); ?>
					</strong>
					<input type="hidden" name="action" value="delete_question" />
					<input name="delete_question" type="submit" class="button-secondary" id="delete_question" value="<?php _e('Delete Question', 'event_espresso'); ?>" style="margin-left:10px 0 0 10px;" onclick="return confirmDelete();">
					<a  style="margin-left:5px"class="button-secondary" href="admin.php?page=form_groups"><?php _e('Question Groups', 'event_espresso'); ?></a>
					<a style="margin-left:5px"class="button-secondary thickbox" href="#TB_inline?height=400&width=500&inlineId=question_info">Help</a>
					<a  style="margin-left:5px"class="<?php echo $button_style; ?>" href="admin.php?page=form_builder&amp;action=new_question"><?php _e('Add New Question', 'event_espresso'); ?></a>
				</p>
			</div>
		</form>
		<?php
		$main_post_content = ob_get_clean();
		espresso_choose_layout($main_post_content, event_espresso_display_right_column());
		?>							

	</div>
	<div id="question_info" class="pop-help" style="display:none">
		<div class="TB-ee-frame">
			<h2><?php _e('Manage Questions Overview', 'event_espresso'); ?></h2>
			<p><?php _e('The <code>Questions</code> page shows your list of available questions to add to your registration forms for events', 'event_espresso'); ?></p>
			<p><?php _e('Use the add new question button at the top of the page to create a new question to add to the list ', 'event_espresso'); ?><a href="admin.php?page=form_builder&amp;action=new_question"><?php _e('Add New Question', 'event_espresso'); ?></a></p>
			<p><?php _e('Once you have a built a list of questions you may further organize your questions into <code>Groups.</code> These', 'event_espresso') ?> <a href="admin.php?page=form_groups"><?php _e('Question Groups ', 'event_espresso'); ?></a><?php _e('allow you to easily and conveniently add a group to a registration that will have a pre populated set of questions, this is especially handy when creating many registration forms, saving time, by being able to re-use specific groups of questions repetedly.', 'event_espresso') ?></p>
		</div>
	</div>



	<script type="text/javascript">
		jQuery(document).ready(function($) {

			/* show the table data */
			var mytable = $('#table').dataTable( {
				"bStateSave": true,
				"sPaginationType": "full_numbers",

				"oLanguage": {	"sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong>",
					"sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" },
				"aoColumns": [
					{ "bSortable": true },
					null,
					null,
					null,
					null,
	<?php echo function_exists('espresso_is_admin') && espresso_is_admin() == true ? 'null,' : ''; ?>
					null

				]

			} );

			var startPosition;
			var endPosition;
			$("#table tbody").sortable({
				cursor: "move",
				start:function(event, ui){
					startPosition = ui.item.prevAll().length + 1;
				},
				update: function(event, ui) {
					endPosition = ui.item.prevAll().length + 1;
					//alert('Start Position: ' + startPosition + ' End Position: ' + endPosition);
					var row_ids="";
					$('#table tbody input[name="row_id"]').each(function(i){
						row_ids= row_ids + ',' + $(this).val();
					});
					$.post(EEGlobals.ajaxurl, { action: "update_sequence", row_ids: row_ids, update_sequence: "true"} );
				}
			});
			postboxes.add_postbox_toggles('form_builder');
		} );
						
		// Remove li parent for input 'values' from page if 'text' box or 'textarea' are selected
		var selectValue = jQuery('#question_type option:selected').val();
		//alert(selectValue + ' - this is initial value');
		// hide values field on initial page view
		if( selectValue == 'SINGLE' || selectValue == 'DROPDOWN' || selectValue == 'MULTIPLE' ) {
			jQuery('#question-values').attr("name","values") 
		} else {
			jQuery('#add-question-values').hide();
			jQuery('#add-price-modifier').hide();
			jQuery('#set-price-mod-qty').hide();
			jQuery('#set-price-mod-available').hide();
			// we don't want the values field trying to validate if not displayed, remove its name
			jQuery('#question-values').attr("name","question_values") 
		}
			
					
		jQuery('#question_type').on('change', function() {
			var selectValue = jQuery('#question_type option:selected').val();
				  
			if ( selectValue == 'SINGLE' || selectValue == 'DROPDOWN' || selectValue == 'MULTIPLE' ) {
				jQuery('#add-price-modifier').fadeIn('fast');
				jQuery('#set-price-mod-qty').fadeIn('fast');
				jQuery('#set-price-mod-available').fadeIn('fast');
				jQuery('#add-question-values').fadeIn('fast');
				jQuery('#question-values').attr("name","values");
			} else {
				jQuery('#add-question-values').fadeOut('fast');
				jQuery('#add-price-modifier').fadeOut('fast');
				jQuery('#set-price-mod-qty').fadeOut('fast');
				jQuery('#set-price-mod-available').fadeOut('fast');
				jQuery('#question-values').attr("name","question_values");
			}
		});


		// Add new question or question group form validation
		jQuery(function(){
			jQuery('#new-question-form').validate({
				rules: {
					question: "required",
					values: "required"
				},
				messages: {
					question: "Please add a title for your question",
					values: "Please add a list of answer options for your question"
				}
			});
						
		});				
	</script>

	<?php
}

