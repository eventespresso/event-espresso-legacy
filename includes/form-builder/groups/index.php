<?php
function event_espresso_question_groups_config_mnu(){
	global $wpdb;
	//event_espresso_install_system_names();//Install the default system names for the custom questions.
?>
<div id="event_reg_theme" class="wrap">
<div id="icon-options-event" class="icon32"></div>
<h2>
  <?php _e('Manage Additional Question Groups','event_espresso');?>
</h2>
<?php require(EVENT_ESPRESSO_INCLUDES_DIR . 'form-builder/menu.php'); ?>
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
<div class="box-mid-head">
  <h2 class="fugue f-wrench">
    <?php _e('Current Groups','event_espresso'); ?>
  </h2>
</div>
<div class="box-mid-body" id="toggle2">
  <div class="padding">
    <div id="tablewrapper">
           <div style="float:right; margin:10px 20px;"><a class="button-primary" href="admin.php?page=form_groups&amp;action=new_group"><?php _e('Add New Group','event_espresso'); ?></a></div>
            
           <?php /*?><div style="float:right; margin:10px 20px;"> <a class="button-primary" href="admin.php?page=form_builder&amp;action=new_question"><?php _e('Add New Question','event_espresso'); ?></a></div><?php */?>

      <form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
        <div id="tableheader">
          <div class="search">
            <select id="columns" onchange="sorter.search('query')">
            </select>
            <input type="text" id="query" onkeyup="sorter.search('query')" />
          </div>
          <span class="details">
          <div>
            <?php _e('Records','event_espresso'); ?>
            <span id="startrecord"></span>-<span id="endrecord"></span>
            <?php _e('of','event_espresso'); ?>
            <span id="totalrecords"></span></div>
          <div><a href="javascript:sorter.reset()">
            <?php _e('Reset','event_espresso'); ?>
            </a></div>
          </span> </div>
        <table id="table" class="tinytable">
          <thead>
            <tr>
              <th><h3>
                  <?php _e('Delete','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('ID','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('Group Name','event_espresso'); ?>

                </h3></th>
              <th><h3>
                  <?php _e('Indentifier','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('Description','event_espresso'); ?>
                </h3></th>
                <th><h3>
                  <?php _e('Order','event_espresso'); ?>
                </h3></th>
               
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
                  <input name="checkbox[<?php echo $group_id?>]" type="checkbox"  title="Delete <?php echo $question_name?>">
             <?php else: ?>
                  <span>System Group</span>
              <?php endif; ?>
              <td><?php echo $group_id?></td>
              <td><a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=<?php echo $group_id?>"><?php echo $group_name?></a><br />
                <div class="row-actions"><span class="edit"><a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=<?php echo $group_id?>">Edit</a> | </span><?php if($system_group == 0) :?><span class="delete"><a onclick="return confirmDelete();" class="delete submitdelete"  href="admin.php?page=form_groups&amp;action=delete_group&amp;group_id=<?php echo $group_id?>">Delete</a></span><?php endif; ?></div></td>
              <td><?php echo $group_identifier?></td>
              <td><?php echo $group_description?></td>
              <td><?php echo $group_order?></td>
            </tr>
            <?php } 
	}else{ ?>
            <tr>
              <td><?php _e('No Record Found!','event_espresso'); ?></td>
            </tr>
            <?php	}?>
          </tbody>
        </table>
        <p><input type="checkbox" name="sAll" onclick="selectAll(this)" />
        <strong>
        <?php _e('Check All','event_espresso'); ?>
        </strong>
        <input name="delete_group" type="submit" class="button-secondary" id="delete_group" value="<?php _e('Delete Question Group','event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();"></p>
      </form>
      <div id="tablefooter">
        <div id="tablenav">
          <div> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" /> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" /> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" /> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" /> </div>
          <div>
            <select id="pagedropdown">
            </select>
          </div>
          <div> <a href="javascript:sorter.showall()">
            <?php _e('View All','event_espresso'); ?>
            </a> </div>
        </div>
        <div id="tablelocation">
          <div>
            <select onchange="sorter.size(this.value)">
              <option value="5">5</option>
              <option value="10" selected="selected">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>
            <?php _e('Entries Per Page','event_espresso'); ?>
            </span> </div>
          <div class="page">
            <?php _e('Page','event_espresso'); ?>
            <span id="currentpage"></span>
            <?php _e('of','event_espresso'); ?>
            <span id="totalpages"></span></div>
        </div>
      </div>
    </div>
  </div>
</div>
<p><?php require(EVENT_ESPRESSO_INCLUDES_DIR . 'form-builder/menu.php'); ?></p>
<script type="text/javascript">
	var sorter = new TINY.table.sorter('sorter','table',{
		headclass:'head',
		ascclass:'asc',
		descclass:'desc',
		evenclass:'evenrow',
		oddclass:'oddrow',
		evenselclass:'evenselected',
		oddselclass:'oddselected',
		paginate:true,
		size:10,
		colddid:'columns',
		currentid:'currentpage',
		totalid:'totalpages',
		startingrecid:'startrecord',
		endingrecid:'endrecord',
		totalrecid:'totalrecords',
		hoverid:'selectedrow',
		pageddid:'pagedropdown',
		navid:'tablenav',
		sortcolumn:1,
		sortdir:1,
		//sum:[8],
		//avg:[6,7,8,9],
		//columns:[{index:7, format:'%', decimals:1},{index:8, format:'$', decimals:0}],
		init:true
	});
  </script>
<?php 
}