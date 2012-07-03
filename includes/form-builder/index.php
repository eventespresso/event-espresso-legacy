<?php
function event_espresso_questions_config_mnu(){
	global $wpdb;
	//event_espresso_install_system_names();//Install the default system names for the custom questions.
?>
<div id="event_reg_theme" class="wrap">
<div id="icon-options-event" class="icon32"></div>
<h2>
  <?php _e('Manage Additional Questions','event_espresso');?>
</h2>
<?php require(EVENT_ESPRESSO_INCLUDES_DIR . 'form-builder/menu.php'); ?>
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
<div class="box-mid-head">
  <h2 class="fugue f-wrench">
    <?php _e('Current Questions','event_espresso'); ?>
  </h2>
</div>
<div class="box-mid-body" id="toggle2">
  <div class="padding">
    <div id="tablewrapper">
           
           <div style="float:right; margin:10px 20px;"> <a class="button-primary" href="admin.php?page=form_builder&amp;action=new_question"><?php _e('Add New Question','event_espresso'); ?></a></div>

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
                  <?php _e('Question','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('Values','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('Type','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('Required','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('Sequence','event_espresso'); ?>
                </h3></th>
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
			?>
            <tr>
              <td>
              <?php if(is_null($system_name)) : ?>
                  <input name="checkbox[<?php echo $question_id?>]" type="checkbox"  title="Delete <?php echo $question_name?>">
             <?php else: ?>
                  <span>System Field</span>
              <?php endif; ?>
              </td>
              <td><?php echo $question_id?></td>
              <td><a href="admin.php?page=form_builder&amp;action=edit_question&amp;question_id=<?php echo $question_id?>"><?php echo $question_name?></a><br />
                <div class="row-actions"><span class="edit"><a href="admin.php?page=form_builder&amp;action=edit_question&amp;question_id=<?php echo $question_id?>">Edit</a> | </span><?php if(is_null($system_name)):?><span class="delete"><a onclick="return confirmDelete();" class="delete submitdelete"  href="admin.php?page=form_builder&amp;action=delete_question&amp;question_id=<?php echo $question_id?>">Delete</a></span><?php endif; ?></div></td>
              <td><?php echo $values?></td>
              <td><?php echo $question_type?></td>
              <td><?php echo $required?></td>
              <td><?php echo $sequence?></td>
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
        <input type="hidden" name="action" value="delete_question" />
        <input name="delete_question" type="submit" class="button-secondary" id="delete_question" value="<?php _e('Delete Question','event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();"></p>
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
		size:20,
		colddid:'columns',
		currentid:'currentpage',
		totalid:'totalpages',
		startingrecid:'startrecord',
		endingrecid:'endrecord',
		totalrecid:'totalrecords',
		hoverid:'selectedrow',
		pageddid:'pagedropdown',
		navid:'tablenav',
		//sortcolumn:1,
		//sortdir:1,
		//sum:[8],
		//avg:[6,7,8,9],
		//columns:[{index:7, format:'%', decimals:1},{index:8, format:'$', decimals:0}],
		init:true
	});
  </script>
<?php 
}

