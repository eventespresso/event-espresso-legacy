<?php 
require_once("add_new_venue.php");
require_once("edit_venue.php");
require_once("update_venue.php");
require_once("add_venue_to_db.php");
function event_espresso_venue_config_mnu(){
	global $wpdb;
	?>
<div class="wrap">
  <div id="icon-options-event" class="icon32"> </div>
      <h2><?php _e('Manage Venues','event_espresso');?>
   <?php  if ($_REQUEST[ 'action' ] !='edit' && $_REQUEST[ 'action' ] !='add_new_venue'){
				echo '<a href="admin.php?page=event_venues&amp;action=add_new_venue" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Venue', 'event_espresso') . '</a>';
			}
			?>
    </h2>

 <div id="poststuff" class="metabox-holder has-right-sidebar">
  <?php event_espresso_display_right_column ();?>
  <div id="post-body">
<div id="post-body-content">   

<?php
	if($_POST['delete_venue'] || $_REQUEST['action']== 'delete_venue'){
		if (is_array($_POST['checkbox'])){
			while(list($key,$value)=each($_POST['checkbox'])):
				$del_id=$key;
				//Delete venue data
				$sql = "DELETE FROM " . EVENTS_VENUE_TABLE . " WHERE id='$del_id'";
				$wpdb->query($sql);
				
				$sql = "DELETE FROM " . EVENTS_VENUE_REL_TABLE . " WHERE venue_id='$del_id'";
				$wpdb->query($sql);
			endwhile;	
		}
		if($_REQUEST['action']== 'delete_venue'){
			//Delete discount data
			$sql = "DELETE FROM ".EVENTS_VENUE_TABLE." WHERE id='" . $_REQUEST['id'] . "'";
			$wpdb->query($sql);
			$sql = "DELETE FROM ".EVENTS_VENUE_REL_TABLE." WHERE venue_id='" . $_REQUEST['id'] . "'";
			$wpdb->query($sql);
		}
		?>
    <div id="message" class="updated fade">
      <p><strong>
        <?php _e('Venues have been successfully deleted from the event.','event_espresso');?>
        </strong></p>
    </div>
<?php }

if ($_REQUEST['action'] == 'update' ){update_event_venue();}
if ($_REQUEST['action'] == 'add' ){add_venue_to_db();}
if ($_REQUEST['action'] == 'add_new_venue'){add_new_event_venue();}
if ($_REQUEST['action'] == 'edit'){edit_event_venue();}
	
?>
      <form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
       
        <table id="table" class="widefat fixed" width="100%"> 
          <thead>
            <tr>
              <th class="manage-column column-cb check-column" id="cb" scope="col" style="width:2.5%;"><input type="checkbox"></th>
              <th class="manage-column column-comments num" id="id" style="padding-top:7px; width:2.5%;" scope="col" title="Click to Sort"><?php _e('ID','event_espresso'); ?></th>
              <th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Name','event_espresso'); ?></th>
              <th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Shortcode','event_espresso'); ?></th>             
            </tr>
          </thead>
          <tbody>
            <?php 

		
	$wpdb->query("SELECT * FROM ". EVENTS_VENUE_TABLE);
	if ($wpdb->num_rows > 0) {
		$results = $wpdb->get_results("SELECT * FROM ". EVENTS_VENUE_TABLE ." ORDER BY id ASC");
		foreach ($results as $result){
			$venue_id= $result->id;
			$name=stripslashes($result->name);
			$venue_desc=stripslashes($result->venue_desc);
			?>
            <tr>
              <td class="check-column" style="padding:7px 0 22px 5px; vertical-align:top;"><input name="checkbox[<?php echo $venue_id?>]" type="checkbox"  title="Delete <?php echo stripslashes($name)?>"></td>
               <td class="column-comments" style="padding-top:3px;"><?php echo $venue_id?></td>
              <td class="post-title page-title column-title"><strong><a href="admin.php?page=event_venues&action=edit&id=<?php echo $venue_id?>"><?php echo $name?></a></strong>
              <div class="row-actions"><span class="edit"><a href="admin.php?page=event_venues&action=edit&id=<?php echo $venue_id?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span><span class="delete"><a onclick="return confirmDelete();" class="delete submitdelete" href="admin.php?page=event_venues&action=delete_venue&id=<?php echo $venue_id?>"><?php _e('Delete', 'event_espresso'); ?></a></span></div>
              </td>
              <td>[ESPRESSO_VENUE id="<?php echo $venue_id?>"]</td>
             
            </tr>
            <?php } 
	}?>
          </tbody>
        </table>
        <div style="clear:both">
        <p>
          <input type="checkbox" name="sAll" onclick="selectAll(this)" />
          <strong>
          <?php _e('Check All','event_espresso'); ?>
          </strong>
          <input name="delete_venue" type="submit" class="button-secondary" id="delete_venue" value="<?php _e('Delete Venue','event_espresso'); ?>" style="margin-left:10px 0 0 10px;" onclick="return confirmDelete();">
          <a  style="margin-left:5px"class="button-primary" href="admin.php?page=event_venues&amp;action=add_new_venue"><?php _e('Add New Venue','event_espresso'); ?></a>
        </p>
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
							{ "bSortable": false },
							 null,
							 null,
							 { "bSortable": false }
							
						]

	} );
	
} );
</script>

<?php 
}