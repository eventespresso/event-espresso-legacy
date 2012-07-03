<?php
function event_espresso_edit_list(){
	global $wpdb, $org_options;

   if ( $_POST[ 'delete_event' ] )
    {
        if ( is_array( $_POST[ 'checkbox' ] ) )
        {
            while ( list($key, $value) = each( $_POST[ 'checkbox' ] ) ):
                $del_id = $key;
				event_espresso_delete_event($del_id);
            endwhile;
        }
?>
<div id="message" class="updated fade">
          <p><strong>
            <?php _e( 'Event(s) have been permanently deleted.', 'event_espresso' ); ?>
            </strong></p>
        </div>
<?php
	}
	if ( $_POST[ 'perm_delete_event' ] ){
			if ( is_array( $_POST[ 'checkbox' ] ) )
			{
				while ( list($key, $value) = each( $_POST[ 'checkbox' ] ) ):
					$del_id = $key;
					event_espresso_empty_event_trash($del_id);
					//$sql = "DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = '$del_id'";
					//$wpdb->query( $sql );
				endwhile;
			}
	?>
	
			<div id="message" class="updated fade">
			  <p><strong>
				<?php _e( 'Event(s) have been permanently deleted.', 'event_espresso' ); ?>
				</strong></p>
			</div>
	<?php
	}
	
 $recurrence_icon = '';

    if (defined('EVENT_ESPRESSO_RECURRENCE_MODULE_ACTIVE'))

    {

        $recurrence_icon = '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/arrow_rotate_clockwise.png" alt="Recurring Event" title="Recurring Event" class="re_fr" />';

    }
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php')){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH. 'includes/admin-files/admin_reports_filters.php');
	}
	if ($total_events > 500) {
		$max_rows = $_POST['max_rows'] ==""? 500 : $_POST['max_rows'];
		$start_rec = $_POST['start_rec'] ==""? 0 : $_POST['start_rec'];
	?>
        <form method="post" action="admin.php?page=events">
            <p>
            <input name="navig" value="Show:" type="submit">
            <input name="max_rows" size="3" value="<?php echo $max_rows ?>" class="textfield" onfocus="this.select()" type="text">
            <?php _e('row(s) starting from record #', 'event_espresso'); ?>
            <input name="start_rec" size="6" value="<?php echo $start_rec ?>" class="textfield" onfocus="this.select()" type="text"></p>
        </form>
    <?php
		$records_to_show = " LIMIT $max_rows OFFSET $start_rec ";
	}
	if ($total_events > 0) {
		
			if ($_REQUEST['month_range']){
				$pieces = explode('-',$_REQUEST['month_range'], 3);
				$year_r = $pieces[0];
				$month_r = $pieces[1];
			}
			
			$sql = "SELECT e.id event_id, e.event_name, e.event_identifier, e.reg_limit, e.registration_start, ";
			$sql .= " e.start_date, e.is_active, e.recurrence_id, e.registration_startT, ";
			$sql .= " e.address, e.address2, e.city, e.state, e.zip, e.country ";
			$sql .= " FROM ". EVENTS_DETAIL_TABLE ." e ";
			
			if ($_REQUEST[ 'category_id' ] !=''){
				$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
				$sql .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
				
			}
			
			$sql .= ($_POST[ 'event_status' ] !='' && $_POST[ 'event_status' ] !='IA')  ? " WHERE event_status = '" . $_POST[ 'event_status' ] ."' ":" WHERE event_status != 'D' ";
			
			$sql .= $_REQUEST[ 'category_id' ] !='' ? " AND c.id = '" . $_REQUEST[ 'category_id' ] . "' " : '';
				
			if ($_POST[ 'month_range' ] !=''){
				$sql .= " AND start_date BETWEEN '".date('Y-m-d', strtotime($year_r. '-' .$month_r . '-01'))."' AND '".date('Y-m-d', strtotime($year_r . '-' .$month_r. '-31'))."' ";
			}
			
			if ($_REQUEST[ 'today' ]=='true'){
				$sql .= " AND start_date = '" . $curdate ."' ";
			}
			
			if ($_REQUEST[ 'this_month' ]=='true'){
				$sql .= " AND start_date BETWEEN '".date('Y-m-d', strtotime($this_year_r. '-' .$this_month_r . '-01'))."' AND '".date('Y-m-d', strtotime($this_year_r . '-' .$this_month_r. '-' . $days_this_month))."' ";
			}
			
			//If user is an event manager, then show only their events
			if (function_exists('espresso_member_data')&&espresso_member_data('role')=='espresso_event_manager'){
				$sql .= " AND wp_user = '" . espresso_member_data('id') ."' ";
			}

			$sql .= " ORDER BY e.start_date  ASC $records_to_show ";
			//echo $sql;
	?>

<form id="form1" name="form1" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
<table id="table" class="widefat fixed" width="100%"> 
	<thead>
		<tr>
          <th class="manage-column column-cb check-column" id="cb" scope="col" style="width:2.5%;"><input type="checkbox"></th>
          <th class="manage-column column-comments num" id="id" style="padding-top:7px; width:2.5%;" scope="col" title="Click to Sort"><?php _e('ID','event_espresso'); ?></th>
		  <th class="manage-column column-title" id="name" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Name','event_espresso'); ?></th>
          <th class="manage-column column-author" id="start" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Start Date & Time','event_espresso'); ?></th>
          <th class="manage-column column-date" id="begins" scope="col" title="Click to Sort" style="width:20%;"><?php _e('Reg Begins','event_espresso'); ?></th>
		  <th class="manage-column column-date" id="status" scope="col" title="Click to Sort" style="width:10%;"><?php _e('Status','event_espresso'); ?></th>
		  <th class="manage-column column-date" id="attendees" scope="col" title="Click to Sort" style="width:10%;"><?php _e('Attendees','event_espresso'); ?></th>
          <th class="manage-column column-author" id="actions" scope="col" title="Click to Sort" style="width:15%;"><?php _e('Actions','event_espresso'); ?></th>
		</tr>
	</thead>
     
    <tbody>
<?php 
		
			$events = $wpdb->get_results($sql);

				foreach ($events as $event){
					$event_id= $event->event_id;
					$event_name=stripslashes_deep($event->event_name);
					$event_identifier=stripslashes_deep($event->event_identifier);
					$reg_limit = $event->reg_limit;
					$registration_start = $event->registration_start;
					$start_date = $event->start_date;
					$end_date = $event->end_date;
					$is_active= $event->is_active;
					$status = array();
					$status = event_espresso_get_is_active($event_id);
					$recurrence_id = $event->recurrence_id;
					$registration_startT = $event->registration_startT;
					
					$event_address = $event->address;
					$event_address2 = $event->address2;
					$event_city = $event->city;
					$event_state = $event->state;
					$event_zip = $event->zip;
					$event_country = $event->country;
			
			
					$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
	ob_start();
?>
			<tr>
              <td class="check-column" style="padding:7px 0 22px 5px; vertical-align:top;"><!--Delete Events-->
			  <?php echo '<input name="checkbox[' . $event_id . ']" type="checkbox"  title="Delete Event '. $event_name .'" />';?></td>
			  <td class="column-comments" style="padding-top:3px;"><?php echo $event_id?></td>
              <td class="post-title page-title column-title"><strong><a class="row-title" href="admin.php?page=events&action=edit&event_id=<?php echo $event_id?>"><?php echo $event_name?></a> <?php echo ($recurrence_id >0) ? $recurrence_icon :'' ; ?> </strong>
              	<div class="row-actions"><span><a href="<?php echo  home_url() . "/?ee=". $event_id ?>" target="_blank"><?php _e('View', 'event_espresso'); ?></a> | </span><span class='edit'><a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span><span class='delete'><a onclick="return confirmDelete();" href='admin.php?page=events&amp;action=delete&amp;event_id=<?php echo $event_id?>'><?php _e('Delete', 'event_espresso'); ?></a></span> | <span><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>"><?php _e('Attendees', 'event_espresso'); ?></a> | </span><span><a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;event_id=".$event_id."&amp;export=report&action=payment&amp;type=excel";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><?php _e('Export', 'event_espresso'); ?></a></span></div></td>
              <td class="author column-author"><?php echo event_date_display($start_date,get_option('date_format'))?> <br />
<?php echo event_espresso_get_time($event_id, 'start_time') ?></td>
              <td class="date column-date"><?php echo event_date_display($registration_start,get_option('date_format'));?> <br />
<?php echo $registration_startT ?></td>
              <td class="date column-date"><?php echo $status['display'] ?></td>
			  <td class="author column-author"><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>"><?php echo get_number_of_attendees_reg_limit($event_id);?></a></td>			  
              <td class="date column-date">
                
                <a href="<?php echo home_url()?>/?page_id=<?php echo $org_options['event_page_id']?>&regevent_action=register&event_id=<?php echo $event_id?>&name_of_event=<?php echo $event_name?>" title="<?php _e('View Event','event_espresso'); ?>" target="_blank"><div class="view_btn"></div></a>
                
                <a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id?>" title="<?php _e('Edit Event','event_espresso'); ?>"><div class="edit_btn"></div></a>
                
                <a href="admin.php?page=events&amp;event_id=<?php echo $event_id?>&amp;event_admin_reports=list_attendee_payments" title="<?php _e('View Attendees','event_espresso'); ?>"><div class="complete_btn"></div></a>

                <a class="ev_reg-fancylink" href="#unique_id_info_<?php echo $event_id?>" title="<?php _e('Get Short URL/Shortcode','event_espresso'); ?>"><div class="shortcode_btn"></div></a>
                
                <a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;event_id=".$event_id."&amp;export=report&action=payment&amp;type=excel";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><div class="excel_exp_btn"></div></a>
                
                <a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&event_id=".$event_id."&export=report&action=payment&type=csv";?>'" title="<?php _e('Export to CSV','event_espresso'); ?>"><div class="csv_exp_btn"></div></a>
                
                <a href="admin.php?page=events&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Email Attendees', 'event_espresso' ); ?>"><div class="newsletter_btn"></div></a>
               
              <div id="unique_id_info_<?php echo $event_id?>" style="display:none">
			  <?php _e('<h2>Short URL/Shortcode</h2><p>This is the short URL to this event:</p><p><span  class="updated fade">' . home_url() . "/?ee=". $event_id . '</span></p><p>This will show the registration form for this event jsut about anywhere. Just copy and paste the following shortcode into any page or post.</p><p><span  class="updated fade">[SINGLEEVENT single_event_id="' .  $event_identifier . '"]</span></p> <p class="red_text"> Do not use in place of the main events page that is set in the Organization Settings page.','event_espresso'); ?>
            </div></td>
			  </tr>
<?php 
					//echo $_REQUEST['event_status'];
					if ($_REQUEST['event_status'] !=''){
						$content = ob_get_contents();
						ob_end_clean();
						switch ($_REQUEST['event_status']){
						case 'A': 
							switch (event_espresso_get_status($event_id)){
										case 'NOT_ACTIVE':
											//Don't show the event if any of the above are true
										break;
										
										default:
											echo $content;
										break;
							}
						break;
						case 'IA':
							switch (event_espresso_get_status($event_id)){
										case 'NOT_ACTIVE':
											echo $content;
										break;
										
										default:
											//Don' show the event if any of the above are true
										break;
							}
						break;
						default: 
							echo $content;
						break;
						}
					}
				}//End foreach ($events as $event){ 
		}
?>
		
          </tbody>
          </table>
          <div style="clear:both">
		  <input type="checkbox" name="sAll" onclick="selectAll(this)" />
  <strong>
  <?php _e( 'Check All', 'event_espresso' ); ?>
  </strong><?php if ( $_POST[ 'event_status' ] =='D' ){?>
          	 <input name="perm_delete_event" type="submit" class="button-secondary" id="perm_delete_event" value="<?php _e( 'Permanently Delete Events(s)', 'event_espresso' ); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />
          <?php }else{?>
         	<input name="delete_event" type="submit" class="button-secondary" id="delete_event" value="<?php _e( 'Delete Events(s)', 'event_espresso' ); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />
            
      <a  style="margin-left:5px"class="button-primary" href="admin.php?page=events&amp;action=csv_import"><?php _e('Import CSV','event_espresso'); ?></a>
       
    <a class="button-primary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;id=".$event_id."&amp;export=report&action=payment&amp;type=excel&all_events=true";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><?php _e( 'Export All Attendee Data', 'event_espresso' ); ?></a>
    <a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=add_new_event"><?php _e('Add New Event','event_espresso'); ?></a>
		  <?php }?>  </div>
          </form>
          
          

<script>
jQuery(document).ready(function($) {						
		
	/* Apply the tooltips */
	/* var mytabletooltip = $('#table tbody tr[title]').tooltip( {
		"delay": 0,
		"track": true,
		"fade": 250
	} ); */
	
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
							 null,
							 null,
							 null,
							 null,
							 { "bSortable": false }
						]

	} );
	
} );
</script>

<div id="coupon_code_info" style="display:none">
<h2><?php _e('Coupon/Promo Code', 'event_espresso'); ?></h2><p><?php _e('This is used to apply discounts to events.', 'event_espresso'); ?></p><p><?php _e('A coupon or promo code could can be anything you want. For example: Say you have an event that costs', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>200. <?php _e('If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted', 'event_espresso'); ?>  <?php echo $org_options['currency_symbol'] ?>50.00, <?php _e('Bringing the cost of the event to', 'event_espresso'); ?> <?php echo $org_options['currency_symbol'] ?>150.</p>
</div>
<div id="unique_id_info" style="display:none">
     <h2><?php _e('Event Identifier', 'event_espresso'); ?></h2><p><?php _e('This should be a unique identifier for the event. Example: "Event1" (without qoutes.)</p><p>The unique ID can also be used in individual pages using the', 'event_espresso'); ?> [SINGLEEVENT single_event_id="<?php _e('Unique Event ID', 'event_espresso'); ?>"] <?php _e('shortcode', 'event_espresso'); ?>.</p>
    </div>
<?php
  echo event_espresso_custom_email_info();
}