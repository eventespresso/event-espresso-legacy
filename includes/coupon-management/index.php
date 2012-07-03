<?php 
function event_espresso_discount_config_mnu(){
	global $wpdb;
?>
<div id="event_reg_theme" class="wrap">
<div id="icon-options-event" class="icon32"></div>
<h2><?php echo _e('Manage Event Discounts', 'event_espresso') ?></h2>
<?php

switch ($_REQUEST['action']){
	case 'add':
		require_once("add_discount.php");
		add_discount_to_db();//Add the discount to the DB
	break;
	case 'new':
		require_once("new_discount.php");
		add_new_event_discount();//Add new discount form
	break;
	case 'edit':
		require_once("edit_discount.php");
		edit_event_discount();//Edit discount form
	break;
	case 'update':
		require_once("update_discount.php");
		update_event_discount();//Update discount in DB
	break;
	case 'delete_discount':
		require_once("delete_discount.php");
		delete_event_discount();//Delete discount in DB
	break;
}
if($_REQUEST['delete_discount']){//This is for the delete checkboxes
	require_once("delete_discount.php");
	delete_event_discount();
}
?>
<div class="box-mid-head">
  <h2 class="fugue f-wrench">
    <?php _e('Current Discount Codes','event_espresso'); ?>
  </h2>
</div>
<div class="box-mid-body" id="toggle2">
  <div class="padding">
    <div id="tablewrapper">
    <div style="float:right; margin:10px 20px;">
  <form name="form" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
    <input type="hidden" name="action" value="new">
    <input class="button-primary" type="submit" name="add_new_discount" value="<?php _e('Add New Discount','event_espresso');?>"/>
  </form>
</div>

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
                <?php _e('Name','event_espresso'); ?>
              </h3>
            </th>
            <th><h3>
                <?php _e('Amount','event_espresso'); ?>
              </h3></th>
            <th><h3>
                <?php _e('Percentage','event_espresso'); ?>
              </h3></th>
           
          </tr>
        </thead>
        <tbody>
          <?php 

		
		$wpdb->get_results("SELECT * FROM ". EVENTS_DISCOUNT_CODES_TABLE);
		if ($wpdb->num_rows > 0) {
		$event_discounts = $wpdb->get_results("SELECT * FROM ". EVENTS_DISCOUNT_CODES_TABLE ." ORDER BY id ASC");
			foreach ($event_discounts as $event_discount){
				$discount_id = $event_discount->id;
				$coupon_code = $event_discount->coupon_code;
				$coupon_code_price = $event_discount->coupon_code_price;
				$coupon_code_description = $event_discount->coupon_code_description;
				$use_percentage = $event_discount->use_percentage;
			?>
          <tr>
            <td><input name="checkbox[<?php echo $discount_id?>]" type="checkbox"  title="Delete <?php echo $coupon_code?>"></td>
            <td><?php echo $discount_id?></td>
            <td><a href="admin.php?page=discounts&amp;action=edit&amp;discount_id=<?php echo $discount_id?>"><?php echo $coupon_code?></a><br />
                <div class="row-actions"><span class="edit"><a href="admin.php?page=discounts&action=edit&discount_id=<?php echo $discount_id?>">Edit</a> | </span><span class="delete"><a onclick="return confirmDelete();" class="delete submitdelete" href="admin.php?page=discounts&action=delete_discount&discount_id=<?php echo $discount_id?>">Delete</a></span></div></td>
            <td><?php echo $coupon_code_price?></td>
            <td><?php echo $use_percentage?></td>
          </tr>
          <?php } 
		}else { ?>
          <tr>
            <td><?php _e('No Record Found!','event_espresso'); ?></td>
          </tr>
          <?php	}?>
        </tbody>
      </table>
      <input type="checkbox" name="sAll" onclick="selectAll(this)" />
      <strong>
      <?php _e('Check All','event_espresso'); ?>
      </strong>
      <input name="delete_discount" type="submit" class="button-secondary" id="delete_discount" value="<?php _e('Delete Discount','event_espresso'); ?>" style="margin-left:100px;" onclick="return confirmDelete();">
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
<?php }


