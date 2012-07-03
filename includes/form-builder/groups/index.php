<?php
function event_espresso_question_groups_config_mnu(){
	global $wpdb;
	//event_espresso_install_system_names();//Install the default system names for the custom questions.
?>
<div class="wrap">
  <div id="icon-options-event" class="icon32"> </div>
 <h2><?php echo _e('Manage Question Groups', 'event_espresso') ?>
   <?php  if ($_REQUEST[ 'action' ] !='edit_group' && $_REQUEST[ 'action' ] !='new_group'){
				echo '<a href="admin.php?page=form_groups&amp;action=new_group" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Group', 'event_espresso') . '</a>';
			}
			?>
    </h2>
 <div id="poststuff" class="metabox-holder has-right-sidebar">
  <?php event_espresso_display_right_column ();?>
  <div id="post-body">
<div id="post-body-content">   


<?php
	switch ($_REQUEST['action']){
		case 'new_group':
			require_once("new_group.php");
			event_espresso_form_group_new();
		break;
		case 'edit_group':
			require_once("edit_group.php");
			event_espresso_form_group_edit();
		break;
		case 'insert_group':
			require_once("insert_group.php");
			event_espresso_insert_group();
		break;
		case 'update_group':
			require_once("update_group.php");
			event_espresso_form_group_update($_REQUEST['group_id']);
		break;
		case 'delete_group':
			require_once("delete_group.php");
			event_espresso_form_group_delete();
		break;
	}
	if($_REQUEST['delete_group']){//This is for the delete checkboxes
		require_once("delete_group.php");
		event_espresso_form_group_delete();
	}
?>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
<table id="table" class="widefat fixed" width="100%"> 
          <thead>
            <tr>
             <th class="manage-column" id="cb" scope="col" ><input type="checkbox"></th>
              <th class="manage-column column-comments num" id="id" style="padding-top:7px; width:2.5%;" scope="col" title="Click to Sort">
                  <?php _e('ID','event_espresso'); ?>
                </th>
              <th class="manage-column column-title" id="values" scope="col" title="Click to Sort" style="width:20%;">
                  <?php _e('Group Name','event_espresso'); ?>

               </th>
              <th class="manage-column column-title" id="type" scope="col" title="Click to Sort" style="width:20%;">
                  <?php _e('Indentifier','event_espresso'); ?>
                </th>
              <th class="manage-column column-title" id="required" scope="col" title="Click to Sort" style="width:20%;">
                  <?php _e('Description','event_espresso'); ?>
                </th>
               <th class="manage-column column-title" id="required" scope="col" title="Click to Sort" style="width:20%;">
                  <?php _e('Order','event_espresso'); ?>
                </th>
               
            </tr>
          </thead>
          <tbody>
<?php 
	
        $groups = $wpdb->get_results("SELECT * FROM  " . EVENTS_QST_GROUP_TABLE . " qg ORDER BY group_order");

        
	if ($wpdb->num_rows > 0) {
		foreach ($groups as $group) {
			$group_id = $group->id;
			$group_name = stripslashes($group->group_name);
			$group_identifier = stripslashes($group->group_identifier);
			$group_description = stripslashes($group->group_description);
			$question = stripslashes($group->question);
			$group_order = $group->group_order;
			$system_group = $group->system_group;
			?>
            <tr><td>
                 <?php if($system_group == 0) : ?>
                  <input style="margin:7px 0 22px 8px; vertical-align:top;" name="checkbox[<?php echo $group_id?>]" type="checkbox"  title="Delete <?php echo $question_name?>">
             <?php else: ?>
                  <span><?php _e('System Group', 'event_espresso'); ?></span>
              <?php endif; ?>
              <td class="column-comments" style="padding-top:3px;"><?php echo $group_id?></td>
               <td class="post-title page-title column-title"><strong><a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=<?php echo $group_id?>"><?php echo $group_name?></a></strong>
                <div class="row-actions"><span class="edit"><a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=<?php echo $group_id?>">Edit</a> | </span><?php if($system_group == 0) :?><span class="delete"><a onclick="return confirmDelete();" class="delete submitdelete"  href="admin.php?page=form_groups&amp;action=delete_group&amp;group_id=<?php echo $group_id?>">Delete</a></span><?php endif; ?></div></td>
              <td class="author column-author"><?php echo $group_identifier?></td>
              <td class="author column-author"><?php echo $group_description?></td>
              <td class="author column-author"><?php echo $group_order?></td>
            </tr>
            <?php } 
	}else{ ?>
            <tr>
              <td><?php _e('No Record Found!','event_espresso'); ?></td>
            </tr>
            <?php	}?>
          </tbody>
        </table>
        <div style="clear:both">
        <p><input type="checkbox" name="sAll" onclick="selectAll(this)" />
        <strong>
        <?php _e('Check All','event_espresso'); ?>
        </strong>
        <input name="delete_group" type="submit" class="button-secondary" id="delete_group" value="<?php _e('Delete Question Group','event_espresso'); ?>"  style="margin:10 0 0 10px;" onclick="return confirmDelete();">
        <a  style="margin-left:5px"class="button-primary" href="admin.php?page=form_groups&amp;action=new_group"><?php _e('Add New Group','event_espresso'); ?></a>
         <a  style="margin-left:5px"class="button-primary" href="admin.php?page=form_builder"><?php _e('Questions','event_espresso'); ?></a></p>
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
							 null
							
						]

	} );
	
} );
</script>

<?php 
}