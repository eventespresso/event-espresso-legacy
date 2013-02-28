<?php 
function event_espresso_venue_config_mnu(){
	global $wpdb,$current_user,$espresso_premium;
	$_REQUEST[ 'action' ] = isset($_REQUEST[ 'action' ]) ? $_REQUEST[ 'action' ]:NULL;
?>

<div class="wrap">
	<div id="icon-options-event" class="icon32"> </div>
	<h2>
		<?php _e('Manage Venues','event_espresso');?>
		<?php  if ($_REQUEST[ 'action' ] !='edit' && $_REQUEST[ 'action' ] !='add_new_venue'){
				echo '<a href="admin.php?page=event_venues&amp;action=add_new_venue" class="button add-new-h2" style="margin-left: 20px;">' . __('Add New Venue', 'event_espresso') . '</a>';
			}
?>
	</h2>
	<?php ob_start();
	if( isset( $_POST['delete_venue'] ) || ( isset( $_REQUEST['action'] )  && 'delete_venue' == $_REQUEST['action'] ) ){
        $venue_deleted = 0;
		if ( isset( $_POST[ 'checkbox' ] ) && is_array( $_POST['checkbox'] ) ) {
			while(list($key,$value)=each($_POST['checkbox'])):
				$del_id=$key;
                $flag = true;
                if ( function_exists( 'espresso_user_has_venue_permission') ) {
                    $flag = espresso_user_has_venue_permission( $del_id );
                }
                if ( $flag ) {
                    //Delete venue data
                    $sql = "DELETE FROM " . EVENTS_VENUE_TABLE . " WHERE id='$del_id'";
                    $wpdb->query($sql);
                    if ( $wpdb->rows_affected > 0 ) {
                        $venue_deleted++;
                    }
                    $sql = "DELETE FROM " . EVENTS_VENUE_REL_TABLE . " WHERE venue_id='$del_id'";
                    $wpdb->query($sql);
                    $sql = "DELETE FROM " . EVENTS_LOCALE_REL_TABLE . " WHERE venue_id='$del_id'";
                    $wpdb->query($sql);
                }
			endwhile;	
		}
		if( isset( $_REQUEST[ 'id' ] ) && 'delete_venue' == $_REQUEST['action'] ){
            $flag = true;
            $del_id = $_REQUEST['id'];
            if ( function_exists( 'espresso_user_has_venue_permission') ) {
                $flag = espresso_user_has_venue_permission( $del_id );
            }
            if ( $flag ) {
                //Delete discount data
                $sql = "DELETE FROM ".EVENTS_VENUE_TABLE." WHERE id='" . $del_id . "'";
                $wpdb->query($sql);
                if ( $wpdb->rows_affected > 0 ) {
                    $venue_deleted++;
                }
                $sql = "DELETE FROM ".EVENTS_VENUE_REL_TABLE." WHERE venue_id='" . $del_id . "'";
                $wpdb->query($sql);
                $sql = "DELETE FROM " . EVENTS_LOCALE_REL_TABLE . " WHERE venue_id='" . $del_id . "'";
                $wpdb->query($sql);	
            }
		}
        if ( $venue_deleted > 0 ) {
		?>
            <div id="message" class="updated fade">
                <p><strong>
                    <?php _e('Venues have been successfully deleted from the event.','event_espresso');?>
                    </strong></p>
            </div>
<?php 
        }
	}

	$button_style = 'button-primary';
		if (isset($_REQUEST['action'])) {
			switch ($_REQUEST['action']) {
				case 'update':
					require_once("update_venue.php");
					update_event_venue();
					break;
				case 'add':
					require_once("add_venue_to_db.php");
					add_venue_to_db();
					break;
				case 'add_new_venue':
					require_once("add_new_venue.php");
					add_new_event_venue();
					$button_style = 'button-secondary';
					break;
				case 'edit':
					require_once("edit_venue.php");
					edit_event_venue();
					$button_style = 'button-secondary';
					break;
			}
		}
	
?>
				<form id="form1" name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]?>">
					<table id="table" class="widefat manage-discounts">
						<thead>
							<tr>
								<th class="manage-column column-cb check-column" id="cb" scope="col" style="width:2.5%;"><input type="checkbox"></th>
								<th class="manage-column column-comments num" id="id" style="padding-top:7px; width:2.5%;" scope="col" title="Click to Sort"><?php _e('ID','event_espresso'); ?></th>
								<th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Name','event_espresso'); ?></th>
								<?php if (function_exists('espresso_is_admin')&&espresso_is_admin()==true && $espresso_premium == true){ ?>
								<th class="manage-column column-creator" id="creator" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Locale','event_espresso'); ?></th>
								<?php } ?>
								<?php if (function_exists('espresso_is_admin')&&espresso_is_admin()==true && $espresso_premium == true){ ?>
								<th class="manage-column column-creator" id="creator" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Creator','event_espresso'); ?></th>
								<?php } ?>
								<th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Shortcode','event_espresso'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
    global $espresso_manager;
    // If user is an event manager, then show only their venues
    $sql = "( SELECT v.* FROM ". EVENTS_VENUE_TABLE . " v ";
	if(  function_exists('espresso_member_data') && ( espresso_member_data('role')=='espresso_group_admin' ) ){
		if(	$espresso_manager['event_manager_venue'] == "Y" ){
			//	show only venues inside their assigned locales.
            $group = get_user_meta(espresso_member_data('id'), "espresso_group", true);
			$sql .= " LEFT JOIN " . EVENTS_LOCALE_REL_TABLE . " l ON  l.venue_id = v.id "; 
			$sql .= " WHERE l.locale_id IN (" . implode(",", $group) . ")";
			$sql .= ") UNION ( ";
			$sql .= "SELECT v.* FROM ". EVENTS_VENUE_TABLE . " v ";
		}
	}
	if(  function_exists('espresso_member_data') && ( espresso_member_data('role')=='espresso_event_manager' || espresso_member_data('role')=='espresso_group_admin' ) ){
        $sql .= "  WHERE v.wp_user = ".$current_user->ID;
    }
    $sql .= ")";
	#echo $sql;
    #exit("TEST");
    $wpdb->query($sql);
	if ($wpdb->num_rows > 0) {
		$results = $wpdb->get_results($sql." ORDER BY id ASC");
		foreach ($results as $result){
			$venue_id = $result->id;
			$name = isset($result->name) ? stripslashes_deep($result->name):'';
			$venue_desc = isset($result->venue_desc) ? stripslashes_deep($result->venue_desc):'';
            $wp_user = isset($result->wp_user) ? $result->wp_user:'';
			?>
							<tr>
								<td class="check-column" style="padding:7px 0 22px 5px; vertical-align:top;"><input name="checkbox[<?php echo $venue_id?>]" type="checkbox"  title="Delete <?php echo stripslashes($name)?>"></td>
								<td class="column-comments" style="padding-top:3px;"><?php echo $venue_id?></td>
								<td class="post-title page-title column-title"><strong><a href="admin.php?page=event_venues&action=edit&id=<?php echo $venue_id?>"><?php echo $name?></a></strong>
									<div class="row-actions"> <span class="edit"><a href="admin.php?page=event_venues&action=edit&id=<?php echo $venue_id?>">
										<?php _e('Edit', 'event_espresso'); ?>
										</a> | </span> <span class="delete"><a onclick="return confirmDelete();" class="submitdelete" href="admin.php?page=event_venues&action=delete_venue&id=<?php echo $venue_id?>">
										<?php _e('Delete', 'event_espresso'); ?>
										</a></span> </div></td>
								<?php if (function_exists('espresso_is_admin')&&espresso_is_admin()==true && $espresso_premium == true){ ?>
								<td><?php
					$last_locale_id = $wpdb->get_var("SELECT locale_id FROM ".EVENTS_LOCALE_REL_TABLE." WHERE venue_id='".$venue_id."'");
					$locales = $wpdb->get_results("SELECT * FROM " . EVENTS_LOCALE_TABLE . " WHERE id = '".$last_locale_id."'");
					if ( count($locales) > 0) {
						foreach ($locales as $locale){
							$locale_id= $locale->id;
							$name=stripslashes($locale->name);
							echo $name;
						}
					}
				?></td>
								<?php	}	?>
								<?php if (function_exists('espresso_is_admin')&&espresso_is_admin()==true && $espresso_premium == true){ ?>
								<td><?php echo espresso_user_meta($wp_user, 'user_firstname') !=''?espresso_user_meta($wp_user, 'user_firstname') . ' ' . espresso_user_meta($wp_user, 'user_lastname'):espresso_user_meta($wp_user, 'display_name'); ?></td>
								<?php } ?>
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
							<a  style="margin-left:5px"class="<?php echo $button_style; ?>" href="admin.php?page=event_venues&amp;action=add_new_venue">
							<?php _e('Add New Venue','event_espresso'); ?>
							</a> </p>
					</div>
				</form>
            <?php
            $main_post_content = ob_get_clean();
            espresso_choose_layout($main_post_content, event_espresso_display_right_column());
            ?>
</div>
<?php #### help dialogue box #### ?>
<div id="venue_locale" style="display:none">
	<div class="TB-ee-frame">
		<h2>
			<?php _e('Venue Locale/Region', 'event_espresso'); ?>
		</h2>
		<p>
			<?php _e('This can be used to group venues together by locales/regions.', 'event_espresso'); ?>
		</p>
		<p>
			<?php _e('Once you have created a locale in the <a href="admin.php?page=event_locales"> Manage Locales/Regions</a> page it will be available to select on the \'Add a Venue\' page', 'event_espresso')?>
		</p>
	</div>
</div>
<?php #### end help #### ?>
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
							 <?php echo function_exists('espresso_is_admin')&&espresso_is_admin()==true ? 'null,' : ''; ?>
							 null,
							 <?php echo function_exists('espresso_is_admin')&&espresso_is_admin()==true ? 'null,' : ''; ?>
							 { "bSortable": false }
							
						]

	} );
	
} );		
// Add new venue form validation
	jQuery(function(){
		jQuery('#venues-form').validate({
		rules: {
		  name: "required"
		 },
		 messages: {
		  name: "please add a name for your venue"
			}
		
		});
		});
</script>
<?php 
}
