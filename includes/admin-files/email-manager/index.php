<?php 
require_once("add_new_email.php");
require_once("edit_email.php");
require_once("update_email.php");
require_once("add_email_to_db.php");
function event_espresso_email_config_mnu(){
	global $wpdb;
	?>

<div id="event_reg_theme" class="wrap">
<div id="icon-options-event" class="icon32"></div>
<h2>
  <?php _e('Manage Event Emails','event_espresso');?>
</h2>
<?php
	if($_POST['delete_email']){
		if (is_array($_POST['checkbox'])){
			while(list($key,$value)=each($_POST['checkbox'])):
				$del_id=$key;
				//Delete email data
				$sql = "DELETE FROM " . EVENTS_EMAIL_TABLE . " WHERE id='$del_id'";
				$wpdb->query($sql);
			endwhile;	
		}
		?>
<div id="message" class="updated fade">
  <p><strong>
    <?php _e('Emails have been successfully deleted.','event_espresso');?>
    </strong></p>
</div>
<?php }?>
 <?php
	
if ($_REQUEST['action'] == 'update' ){update_event_email();}
if ($_REQUEST['action'] == 'add' ){add_email_to_db();}
if ($_REQUEST['action'] == 'add_new_email'){add_new_event_email();}
if ($_REQUEST['action'] == 'edit'){edit_event_email();}
	
?>
<div class="box-mid-head">
  <h2 class="fugue f-wrench">
    <?php _e('Current Emails','event_espresso'); ?>
  </h2>
</div>

<div class="box-mid-body" id="toggle2">
  <div class="padding">
    <div id="tablewrapper">
      <div style="float:right; margin:10px 20px;">
        <form name="form" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
          <input type="hidden" name="action" value="add_new_email">
          <input class="button-primary" type="submit" name="add_new_email" value="<?php _e('Add New Email','event_espresso');?>"/>
        </form>
      </div>
     <p><?php _e('Create customized emails for use in multiple events.', 'event_espresso'); ?></p>
      <form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
        <div id="tablewrapper">
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
                  <?php _e('Name','event_espresso'); ?>
                </h3>
              </th>
             
              <th><h3>
                  <?php _e('Action','event_espresso'); ?>
                </h3></th>
            </tr>
          </thead>
          <tbody>
            <?php 

		
	$wpdb->query("SELECT * FROM ". EVENTS_EMAIL_TABLE);
	if ($wpdb->num_rows > 0) {
		$results = $wpdb->get_results("SELECT * FROM ". EVENTS_EMAIL_TABLE ." ORDER BY id ASC");
		foreach ($results as $result){
			$email_id= $result->id;
			$email_name=stripslashes($result->email_name);
			$email_text=stripslashes($result->email_text);
			?>
            <tr>
              <td><input name="checkbox[<?php echo $email_id?>]" type="checkbox"  title="Delete <?php echo stripslashes($email_name)?>"></td>
              <td><?php echo $email_id?></td>
              <td><?php echo $email_name?></td>
              <td style="background-color:#FFF"><a href="admin.php?page=event_emails&action=edit&id=<?php echo $email_id?>">
                <?php _e('Edit Email','event_espresso'); ?>
                </a></td>
            </tr>
            <?php } 
	}else{ ?>
            <tr>
              <td><?php _e('No Record Found!','event_espresso'); ?></td>
            </tr>
              <?php	}?>
          </tbody>
        </table>
        <p>

          <input type="checkbox" name="sAll" onclick="selectAll(this)" />
          <strong>
          <?php _e('Check All','event_espresso'); ?>
          </strong>
          <input name="delete_email" type="submit" class="button-secondary" id="delete_email" value="<?php _e('Delete Email','event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();">
        </p>
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
</div>

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
echo event_espresso_custom_email_info();
}