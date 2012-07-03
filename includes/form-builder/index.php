<?php
function event_espresso_questions_config_mnu(){
	global $wpdb;
	//event_espresso_install_system_names();//Install the default system names for the custom questions.
?>
<div class="wrap">
  <div id="icon-options-event" class="icon32"> </div>
 <h2><?php echo _e('Manage Questions', 'event_espresso') ?>
   <?php  if ($_REQUEST[ 'action' ] !='edit_question' && $_REQUEST[ 'action' ] !='new_question'){
				echo '<a href="admin.php?page=form_builder&action=new_question" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Question', 'event_espresso') . '</a>';
			}
			?>
    </h2>
 <div id="poststuff" class="metabox-holder has-right-sidebar">
  <?php event_espresso_display_right_column ();?>
  <div id="post-body">
<div id="post-body-content">   


<?php
	//Update the question
	if ( $_REQUEST['edit_action'] == 'update' ){require_once("questions/update_question.php");event_espresso_form_builder_update();}
	
	//Figure out which view to display
	switch ($_REQUEST['action']){
		case 'insert':
			require_once("questions/insert_question.php");
			event_espresso_form_builder_insert();
		break;
		case 'new_question':
			require_once("questions/new_question.php");
			event_espresso_form_builder_new();
		break;
		case 'edit_question':
			require_once("questions/edit_question.php");
			event_espresso_form_builder_edit();
		break;
		case 'delete_question':
			require_once("questions/delete_question.php");
			event_espresso_form_builder_delete();
		break;
	}
?>

       <form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
<table id="table" class="widefat fixed" width="100%"> 

          <thead>
            <tr>
              <th class="manage-column" id="cb" scope="col" ><input type="checkbox"></th>
              <th class="manage-column column-comments num" id="id" style="padding-top:7px; width:5%;" scope="col" title="Click to Sort">
                  <?php _e('ID','event_espresso'); ?>
                </th>
               <th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:20%;">
                  <?php _e('Question','event_espresso'); ?>
                </th>
               <th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:20%;">
                  <?php _e('Values','event_espresso'); ?>
               </th>
              <th class="manage-column column-title" id="values" scope="col" title="Click to Sort"  style="width:10%;">
                  <?php _e('Type','event_espresso'); ?>
                </th>
               <th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:10%;">
                  <?php _e('Required','event_espresso'); ?>
                </th>
               <th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:10%;">
                  <?php _e('Admin View Only','event_espresso'); ?>
                </th>
               <th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:10%;">
                  <?php _e('Sequence','event_espresso'); ?>
                </th>
            </tr>
          </thead>
          <tbody>
<?php 
	$questions = $wpdb->get_results("SELECT * FROM " . EVENTS_QUESTION_TABLE . " ORDER BY sequence");
	if ($wpdb->num_rows > 0) {
		foreach ($questions as $question) {
			$question_id = $question->id;
			$question_name = stripslashes($question->question);
			$values = stripslashes($question->response);
			$question_type = stripslashes($question->question_type);
			$required = stripslashes($question->required);
			$system_name = $question->system_name;
			$sequence = $question->sequence;
			$admin_only = $question->admin_only;
			?>
            <tr>
              <td>
              <?php if(is_null($system_name)) : ?>
                  <input  style="margin:7px 0 22px 8px; vertical-align:top;" name="checkbox[<?php echo $question_id?>]" type="checkbox"  title="Delete <?php echo $question_name?>">
             <?php else: ?>
                  <span><?php _e('System Field', 'event_espresso'); ?></span>
              <?php endif; ?>
              </td>
              <td class="column-comments" style="padding-top:3px;"><?php echo $question_id?></td>
              <td class="post-title page-title column-title"><strong><a href="admin.php?page=form_builder&amp;action=edit_question&amp;question_id=<?php echo $question_id?>"><?php echo $question_name?></a></strong>
                <div class="row-actions"><span class="edit"><a href="admin.php?page=form_builder&amp;action=edit_question&amp;question_id=<?php echo $question_id?>">Edit</a> | </span><?php if(is_null($system_name)):?><span class="delete"><a onclick="return confirmDelete();" class="delete submitdelete"  href="admin.php?page=form_builder&amp;action=delete_question&amp;question_id=<?php echo $question_id?>">Delete</a></span><?php endif; ?></div></td>
              <td class="author column-author"><?php echo $values?></td>
              <td class="author column-author"><?php echo $question_type?></td>
              <td class="author column-author"><?php echo $required?></td>
              <td class="author column-author"><?php echo $admin_only?></td>
              <td class="author column-author"><?php echo $sequence?></td>
              
            </tr>
            <?php } 
	}
	?>
          </tbody>
        </table>
        <div style="clear:both">
        <p><input type="checkbox" name="sAll" onclick="selectAll(this)" />
        <strong>
        <?php _e('Check All','event_espresso'); ?>
        </strong>
        <input type="hidden" name="action" value="delete_question" />
        <input name="delete_question" type="submit" class="button-secondary" id="delete_question" value="<?php _e('Delete Question','event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();"></p>
        </div>
      </form>
     </div>
    </div>
  </div>
</div>
<script>
jQuery(document).ready(function($) {						
	
	/* show the table data */
	var mytable = $('#table').dataTable( {
			"bStateSave": true,
			"sPaginationType": "full_numbers",

			"oLanguage": {	"sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong>",
						 	"sZeroRecords": "<?php _e('No Records Found!','event_espresso'); ?>" },
			"aoColumns": [
							{ "bSortable": true },
							 null,
							 null,
							 null,
							 null,
							 null,
							 null,
							 null
							
						]

	} );
	
} );
</script>

<?php 
}

