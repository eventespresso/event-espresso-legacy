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
	
	$curdate = date("Y-m-d");
	$wpdb->query("SELECT id FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D'");
	$total_events =	$wpdb->num_rows;
	
	$wpdb->query("SELECT id FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D' AND start_date = '" . $curdate . "' ");
	$total_events_today =	$wpdb->num_rows;
	
	$pieces = explode('-',$curdate, 3);
	$this_year_r = $pieces[0];
	$this_month_r = $pieces[1];
	//echo $this_year_r;
	$days_this_month = date('t', strtotime($curdate));
	//echo $days_this_month;
	$wpdb->query("SELECT id FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D' AND start_date BETWEEN '".date('Y-m-d', strtotime($this_year_r. '-' .$this_month_r . '-01'))."' AND '".date('Y-m-d', strtotime($this_year_r . '-' .$this_month_r. '-' . $days_this_month))."' ");
	
	$total_events_this_month =	$wpdb->num_rows;
	
?>
<ul class="subsubsub">
    <li><a <?php echo $_REQUEST[ 'all' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&all=true">All Events <span class="count">(<?php echo $total_events ?>)</span></a> |</li>
    <li><a <?php echo $_REQUEST[ 'today' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&today=true">Today <span class="count">(<?php echo $total_events_today ?>)</span></a> |</li>
    <li><a <?php echo $_REQUEST[ 'this_month' ]=='true'? ' class="current" ':''?>  href="admin.php?page=events&this_month=true">This Month <span class="count">(<?php echo $total_events_this_month ?>)</span></a></li>
</ul>

<div class="tablenav" style="margin-bottom:10px;">
    <div class="alignleft actions">
    <form id="form2" name="form2" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
    
    <?php espresso_event_months_dropdown($_POST[ 'month_range' ]); //echo $_POST[ 'month_range' ];  ?>
    <input type="submit" class="button-secondary" value="Filter Month" id="post-query-submit">
    <?php echo espresso_category_dropdown($_REQUEST[ 'category_id' ]); ?><input type="submit" class="button-secondary" value="Filter Category" id="post-query-submit">
    <?php  $status=array(array('id'=>'','text'=> __('Show Active/Inactive','event_espresso')),array('id'=>'A','text'=> __('Active','event_espresso')),array('id'=>'IA','text'=> __('Inactive','event_espresso')), array('id'=>'S','text'=> __('Secondary','event_espresso')), array('id'=>'O','text'=> __('Ongoing','event_espresso')), array('id'=>'D','text'=> __('Deleted','event_espresso'))); echo select_input('event_status', $status, $_REQUEST[ 'event_status' ]);?>
    
    <input type="submit" class="button-secondary" value="Filter Status" id="post-query-submit">
    <a class="button-secondary" href="admin.php?page=events" style=" width:40px; display:inline">Reset Filters</a> <a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=add_new_event"><?php _e('Add New Event','event_espresso'); ?></a>
    </form>
    </div>
</div>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
<table id="table" class="widefat fixed" width="100%"> 
	<thead>
		<tr>
          <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
          <th class="manage-column column-comments num" id="id" style="width:2em;padding-top:7px;" scope="col" title="Click to Sort"><?php _e('ID','event_espresso'); ?></th>
		  <th class="manage-column column-title" id="name" scope="col" title="Click to Sort"><?php _e('Name','event_espresso'); ?></th>
          <th class="manage-column column-author" id="start" scope="col" title="Click to Sort"><?php _e('Start Date & Time','event_espresso'); ?></th>
          <th class="manage-column column-date" id="begins" scope="col" title="Click to Sort"><?php _e('Reg Begins','event_espresso'); ?></th>
		  <th class="manage-column column-date" id="status" scope="col" title="Click to Sort"><?php _e('Status','event_espresso'); ?></th>
		  <th class="manage-column column-date" id="attendees" scope="col" title="Click to Sort"><?php _e('Attendees','event_espresso'); ?></th>
          <th class="manage-column column-author" id="actions" scope="col" title="Click to Sort"><?php _e('Actions','event_espresso'); ?></th>
		</tr>
	</thead>
     
    <tbody>
<?php 
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
			
			if (function_exists('espresso_member_data')&&espresso_member_data('role')=='espresso_eventmanager'){
				$sql .= " AND wp_user = '" . espresso_member_data('id') ."' ";
			}

			$sql .= " GROUP BY e.id  ORDER BY start_date  ASC ";
			//echo espresso_memeber_data('role');
			//echo $sql;
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
              <td class="check-column" style="padding:7px 0 22px 7px; vertical-align:top;"><!--Delete Events-->
			  <?php echo '<input name="checkbox[' . $event_id . ']" type="checkbox"  title="Delete Event '. $event_name .'" />';?></td>
			  <td class="column-comments" style="padding-top:3px; width:2em;"><?php echo $event_id?></td>
              <td class="post-title page-title column-title"><strong><a class="row-title" href="admin.php?page=events&action=edit&event_id=<?php echo $event_id?>"><?php echo $event_name?></a> <?php echo ($recurrence_id >0) ? $recurrence_icon :'' ; ?> </strong>
              	<div class="row-actions"><span><a href="<?php echo  get_option('siteurl') . "/?ee=". $event_id ?>" target="_blank"><?php _e('View', 'event_espresso'); ?></a> | </span><span class='edit'><a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span><span class='delete'><a onclick="return confirmDelete();" href='admin.php?page=events&amp;action=delete&amp;event_id=<?php echo $event_id?>'><?php _e('Delete', 'event_espresso'); ?></a></span> | <span><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>"><?php _e('Attendees', 'event_espresso'); ?></a> | </span><span><a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;id=".$event_id."&amp;export=report&action=payment&amp;type=excel";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><?php _e('Export', 'event_espresso'); ?></a></span></div></td>
              <td class="author column-author"><?php echo event_date_display($start_date,get_option('date_format'))?> <br />
<?php echo event_espresso_get_time($event_id, 'start_time') ?></td>
              <td class="date column-date"><?php echo event_date_display($registration_start,get_option('date_format'));?> <br />
<?php echo $registration_startT ?></td>
              <td class="date column-date"><?php echo $status['display'] ?></td>
			  <td class="author column-author"><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>"><?php echo get_number_of_attendees_reg_limit($event_id);?></a></td>			  
              <td class="date column-date">
                
                <a href="<?php echo get_option('siteurl')?>/?page_id=<?php echo $org_options['event_page_id']?>&regevent_action=register&event_id=<?php echo $event_id?>&name_of_event=<?php echo $event_name?>" title="<?php _e('View Event','event_espresso'); ?>" target="_blank"><div class="view_btn"></div></a>
                
                <a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id?>" title="<?php _e('Edit Event','event_espresso'); ?>"><div class="edit_btn"></div></a>
                
                <a href="admin.php?page=events&amp;event_id=<?php echo $event_id?>&amp;event_admin_reports=list_attendee_payments" title="<?php _e('View Attendees','event_espresso'); ?>"><div class="complete_btn"></div></a>

                <a class="ev_reg-fancylink" href="#unique_id_info_<?php echo $event_id?>" title="<?php _e('Get Short URL/Shortcode','event_espresso'); ?>"><div class="shortcode_btn"></div></a>
                
                <a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;id=".$event_id."&amp;export=report&action=payment&amp;type=excel";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><div class="excel_exp_btn"></div></a>
                
                <a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&id=".$event_id."&export=report&action=payment&type=csv";?>'" title="<?php _e('Export to CSV','event_espresso'); ?>"><div class="csv_exp_btn"></div></a>
                
                <a href="admin.php?page=events&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Email Attendees', 'event_espresso' ); ?>"><div class="newsletter_btn"></div></a>
               
              <div id="unique_id_info_<?php echo $event_id?>" style="display:none">
			  <?php _e('<h2>Short URL/Shortcode</h2><p>This is the short URL to this event:</p><p><span  class="updated fade">' . get_option('siteurl') . "/?ee=". $event_id . '</span></p><p>This will show the registration form for this event jsut about anywhere. Just copy and paste the following shortcode into any page or post.</p><p><span  class="updated fade">[SINGLEEVENT single_event_id="' .  $event_identifier . '"]</span></p> <p class="red_text"> Do not use in place of the main events page that is set in the Organization Settings page.','event_espresso'); ?>
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
          <?php if ( $_POST[ 'event_status' ] =='D' ){?>
          	 <input name="perm_delete_event" type="submit" class="button-secondary" id="perm_delete_event" value="<?php _e( 'Permanently Delete Events(s)', 'event_espresso' ); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDelete();" />
          <?php }else{?>
         	<input name="delete_event" type="submit" class="button-secondary" id="delete_event" value="<?php _e( 'Delete Events(s)', 'event_espresso' ); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDel	ete();" />
            
      <a  style="margin-left:5px"class="button-primary" href="admin.php?page=events&amp;action=csv_import"><?php _e('Import CSV','event_espresso'); ?></a>
       
    <a class="button-primary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;id=".$event_id."&amp;export=report&action=payment&amp;type=excel&all_events=true";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><?php _e( 'Export All Attndee Data', 'event_espresso' ); ?></a>
    <a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=add_new_event"><?php _e('Add New Event','event_espresso'); ?></a>
		  <?php }?>  
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
<?php _e('<h2>Coupon/Promo Code</h2><p>This is used to apply discounts to events.</p><p>A coupon or promo code could can be anything you want. For example: Say you have an event that costs '. $org_options['currency_symbol'].'200. If you supplied a promo like "PROMO50" and entered 50.00 into the "Discount w/Promo Code" field your event will be discounted '.$org_options['currency_symbol'].'50.00, Bringing the cost of the event to '.$org_options['currency_symbol'].'150.</p>','event_espresso'); ?>
</div>
<div id="unique_id_info" style="display:none">
      <?php _e('<h2>Event Identifier</h2><p>This should be a unique identifier for the event. Example: "Event1" (without qoutes.)</p><p>The unique ID can also be used in individual pages using the [SINGLEEVENT single_event_id="Unique Event ID"] shortcode.</p>','event_espresso'); ?>
    </div>
 <div id="custom_email_info" style="display:none">
    <?php _e('<h2>Email Confirmations</h2><p>For customized confirmation emails, the following tags can be placed in the email form and they will pull data from the database to include in the email.</p><p>[fname], [lname], [phone], [event],[description], [cost], [company], [co_add1], [co_add2], [co_city],[co_state], [co_zip],[contact], [payment_url], [start_date], [start_time], [end_date], [end_time], [location], [location_phone], [google_map_link]</p>','event_espresso'); ?>
  </div>
  <?php
  }
