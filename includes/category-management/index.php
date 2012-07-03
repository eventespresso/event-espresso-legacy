<?php 
require_once("add_new_category.php");
require_once("edit_event_category.php");
require_once("update_event_category.php");
require_once("add_cat_to_db.php");
function event_espresso_categories_config_mnu(){
	global $wpdb;
	?>

<div id="event_reg_theme" class="wrap">
<div id="icon-options-event" class="icon32"></div>
<h2>
  <?php _e('Manage Event Categories','event_espresso');?>
</h2>
<?php
	if($_POST['delete_category']){
		if (is_array($_POST['checkbox'])){
			while(list($key,$value)=each($_POST['checkbox'])):
				$del_id=$key;
				//Delete category data
				$sql = "DELETE FROM " . EVENTS_CATEGORY_TABLE . " WHERE id='$del_id'";
				$wpdb->query($sql);
				
				$sql = "DELETE FROM " . EVENTS_CATEGORY_REL_TABLE . " WHERE cat_id='$del_id'";
				$wpdb->query($sql);
			endwhile;	
		}
		?>
<div id="message" class="updated fade">
  <p><strong>
    <?php _e('Categories have been successfully deleted from the event.','event_espresso');?>
    </strong></p>
</div>
<?php }?>
<div class="box-mid-head">
  <h2 class="fugue f-wrench">
    <?php _e('Current Categories','event_espresso'); ?>
  </h2>
</div>
<div class="box-mid-body" id="toggle2">
  <div class="padding">
    <div id="tablewrapper">
      <div style="float:right; margin:10px 20px;">
        <form name="form" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
          <input type="hidden" name="action" value="add_new_category">
          <input class="button-primary" type="submit" name="add_new_category" value="<?php _e('Add New Category','event_espresso');?>"/>
        </form>
      </div>
      <?php
	
if ($_REQUEST['action'] == 'update' ){update_event_category();}
if ($_REQUEST['action'] == 'add' ){add_cat_to_db();}
if ($_REQUEST['action'] == 'add_new_category'){add_new_event_category();}
if ($_REQUEST['action'] == 'edit'){edit_event_category();}
	
?>
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
                  <?php _e('Shortcode','event_espresso'); ?>
                </h3></th>
              <th><h3>
                  <?php _e('Action','event_espresso'); ?>
                </h3></th>
            </tr>
          </thead>
          <tbody>
            <?php 

		
	$wpdb->query("SELECT * FROM ". EVENTS_CATEGORY_TABLE);
	if ($wpdb->num_rows > 0) {
		$results = $wpdb->get_results("SELECT * FROM ". EVENTS_CATEGORY_TABLE ." ORDER BY id ASC");
		foreach ($results as $result){
			$category_id= $result->id;
			$category_name=stripslashes($result->category_name);
			$category_identifier=stripslashes($result->category_identifier);
			$category_desc=stripslashes($result->category_desc);
			$display_category_desc=stripslashes($result->display_desc);
			?>
            <tr>
              <td><input name="checkbox[<?php echo $category_id?>]" type="checkbox"  title="Delete <?php echo stripslashes($category_name)?>"></td>
              <td><?php echo $category_id?></td>
              <td><?php echo $category_name?></td>
              <td>[EVENT_ESPRESSO_CATEGORY event_category_id="<?php echo $category_identifier?>"]</td>
              <td style="background-color:#FFF"><a href="admin.php?page=event_categories&action=edit&id=<?php echo $category_id?>">
                <?php _e('Edit Category','event_espresso'); ?>
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
          <input name="delete_category" type="submit" class="button-secondary" id="delete_category" value="<?php _e('Delete Category','event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();">
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
<div id="unique_id_info" style="display:none">
  <?php _e('<h2>Unique Category Identifier</h2>
      <p>This should be a unique identifier for the category. Example: "category1" (without qoutes.)</p>
      <p>The unique ID can also be used in individual pages using the  	[EVENT_ESPRESSO_CATEGORY event_category_id="category_identifier"] shortcode.</p>','event_espresso'); ?>
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
}