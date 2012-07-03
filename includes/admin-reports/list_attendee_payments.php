<?php
//Displays the list of attendees and the paymnts they have made
function list_attendee_payments() {
	global $wpdb, $org_options;
	
	$event_id = $_REQUEST[ 'event_id' ];
	
	//If user is an event manager, then show only their attendess
	if (function_exists('espresso_member_data')&&espresso_member_data('role')=='espresso_event_manager'&&espresso_member_data('id')!= espresso_is_my_event($event_id))
		return;

    function event_espresso_paid_status_icon( $payment_status ='' ) {
        if ( $payment_status == "None" || $payment_status == "" || $payment_status == "Incomplete" )
        {
            echo '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/exclamation.png" width="16" height="16" alt="' . __( 'None', 'event_espresso' ) . '" />';
        }
        else if ( $payment_status == "Pending" )
        {
            echo '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/error.png" width="16" height="16" alt="' . __( 'Pending', 'event_espresso' ) . '" />';
        }
        else if ( $payment_status == "Completed" )
        {
            echo '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/accept.png" width="16" height="16" alt="' . __( 'Completed', 'event_espresso' ) . '" title="' . __( 'Completed', 'event_espresso' ) . '" />';
        }
    }

    if ( $_POST[ 'delete_customer' ] )
    {
        if ( is_array( $_POST[ 'checkbox' ] ) )
        {
            while ( list($key, $value) = each( $_POST[ 'checkbox' ] ) ):
                $del_id = $key;

                $sql = "DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = '$del_id'";
                $wpdb->query( $sql );
            endwhile;
        }
?>
<div id="configure_organization_form" class="wrap meta-box-sortables ui-sortable">
<div id="event_reg_theme" class="wrap">
<div id="message" class="updated fade">
  <p><strong>
    <?php _e( 'Customer(s) have been successfully deleted from the event.', 'event_espresso' ); ?>
    </strong></p>
</div>
<?php

        }

        $event_id = $_REQUEST[ 'event_id' ];

        $events = $wpdb->get_results( "SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'" );
        foreach ( $events as $event ) {
            $event_id = $event->id;
            $event_name = stripslashes_deep($event->event_name);
            $event_desc = stripslashes_deep($event->event_desc);
            $event_description = stripslashes_deep($event->event_desc);
            $event_identifier = $event->event_identifier;
            $start_date = $event->start_date;
            $end_date = $event->end_date;
            $cost = $event->event_cost;
            $is_active = $event->is_active;
            $event_status = $event->event_status;
            $status = array( );
            $status = event_espresso_get_is_active( $event_id );

            $reg_limit = $event->reg_limit;
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
<h3><a id="event-id-<?php echo $event_id ?>" name="event-id-<?php echo $event_id ?>" title="<?php _e( 'View event page', 'event_espresso' ); ?>" href="<?php echo get_option( 'siteurl' ) ?>/?page_id=<?php echo $org_options[ 'event_page_id' ] ?>&amp;regevent_action=register&amp;event_id=<?php echo $event_id ?>&amp;name_of_event=<?php echo $event_name ?>" target="_blank"><?php echo $event_name ?></a> |
  <?php _e( 'Start Date:', 'event_espresso' ); ?>
  <?php echo event_date_display( $start_date ) ?> <?php echo $start_time ?> |
  <?php _e( 'Attendees:', 'event_espresso' ); ?>
  <?php echo get_number_of_attendees_reg_limit( $event_id ); ?> | <?php echo $status[ 'display' ] ?></h3>

<ul class="subsubsub">
    <li><a <?php echo $_REQUEST[ 'all' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&all=true"><?php _e('All Events', 'event_espresso'); ?> <span class="count">(<?php echo $total_events ?>)</span></a> |</li>
    <li><a <?php echo $_REQUEST[ 'today' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&today=true"><?php _e('Today', 'event_espresso'); ?> <span class="count">(<?php echo $total_events_today ?>)</span></a> |</li>
    <li><a <?php echo $_REQUEST[ 'this_month' ]=='true'? ' class="current" ':''?>  href="admin.php?page=events&this_month=true"><?php _e('This Month', 'event_espresso'); ?> <span class="count">(<?php echo $total_events_this_month ?>)</span></a></li>
     <li><a href="admin.php?page=events&event_admin_reports=event_list_attendees"><?php _e('All Attendees', 'event_espresso'); ?> <span class="count">(<?php echo get_number_of_attendees_reg_limit(0, 'all_attendees') ?>)</span></a></li>
</ul>
<?php /*?><div class="tablenav" style="margin-bottom:10px;">
    <div class="alignleft actions">
    <form id="form2" name="form2" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
    
    <?php espresso_event_months_dropdown($_POST[ 'month_range' ]); //echo $_POST[ 'month_range' ];  ?>
    <input type="submit" class="button-secondary" value="Filter Month" id="post-query-submit">
    <?php echo espresso_category_dropdown($_REQUEST[ 'category_id' ]); ?><input type="submit" class="button-secondary" value="Filter Category" id="post-query-submit">
    <?php  $status=array(array('id'=>'','text'=> __('Show Active/Inactive','event_espresso')),array('id'=>'A','text'=> __('Active','event_espresso')),array('id'=>'IA','text'=> __('Inactive','event_espresso')), array('id'=>'S','text'=> __('Secondary','event_espresso')), array('id'=>'O','text'=> __('Ongoing','event_espresso')), array('id'=>'D','text'=> __('Deleted','event_espresso'))); echo select_input('event_status', $status, $_REQUEST[ 'event_status' ]);?>
    
    <input type="submit" class="button-secondary" value="Filter Status" id="post-query-submit">
    <a class="button-secondary" href="admin.php?page=events" style=" width:40px; display:inline">Reset Filters</a>
    </form>
    </div>
</div><?php */?>
<div style="clear:both"></div>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">  
  <table id="table" class="widefat fixed" width="100%"> 
    <thead>
      <tr>
        <th class="manage-column column-cb check-column" id="cb" scope="col">
           <input type="checkbox">
          </th>
        <?php /*?><th>
            <?php _e( 'ID', 'event_espresso' ); ?>
          </th><?php */?>
        <th class="manage-column column-title" id="name" scope="col" title="Click to Sort">
            <?php _e( 'Name', 'event_espresso' ); ?>
          </th>
        <th id="start" scope="col" title="Click to Sort">
            <?php _e( 'Pay Status', 'event_espresso' ); ?>
          </th>
        <th class="manage-column column-date" id="begins" scope="col" title="Click to Sort">
            <?php _e( 'Amount', 'event_espresso' ); ?>
          </th>
       
        <th class="manage-column column-date" id="coupon" scope="col" title="Click to Sort">
            <?php _e( 'Coupon', 'event_espresso' ); ?>
          </th>
        <?php /* ?><th>
                              <?php _e('Quantity','event_espresso'); ?>
                              </th><?php */ ?>
        <th class="manage-column column-date" id="registration_date" scope="col" title="Click to Sort" style="width:125px;">
            <?php _e( 'Registration Date', 'event_espresso' ); ?>
          </th>
          <th class="manage-column column-date" id="payment_date" scope="col" title="Click to Sort">
            <?php _e( 'Payment Date', 'event_espresso' ); ?>
          </th>
        <th class="manage-column column-date" id="action" scope="col" title="Click to Sort" style="width:80px;">
            <?php _e( 'Action', 'event_espresso' ); ?>
          </th>
      </tr>
    </thead>
    <tbody>
      <?php

                            $temp_reg_id = ''; //will temporarily hold the registration id for checking with the next row
                            $attendees_group = ''; //will hold the names of the group members
                            $counter = 0; //used for keeping track of the last row.  If counter = num_rows, print
                            $go = false; //triggers the output when true.  Set when the next reg id != temp_reg_id
                            
                            $attendees = $wpdb->get_results( "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' ORDER BY id " );

                            if ( $wpdb->num_rows > 0 )
                            {
                                for ($i = 0; $i <= $wpdb->num_rows; $i++){
                                $attendee = $attendees[$i];

                                    $registration_id = $attendee->registration_id;
                                    $id = $attendee->id;
                                    $lname = $attendee->lname;
                                    $fname = $attendee->fname;
                                    $address = $attendee->address;
                                    $city = $attendee->city;
                                    $state = $attendee->state;
                                    $zip = $attendee->zip;
                                    $email = $attendee->email;
                                    $phone = $attendee->phone;
									$quantity = $attendee->quantity >1?'('.$attendee->quantity.')':'';
                                    
                                    
                                    $txn_type = $attendee->txn_type;
                                    $txn_id = $attendee->txn_id;
                                   
                                    if ( $temp_reg_id == ''){
                                        $temp_reg_id = $registration_id;
                                        $amount_pd = $attendee->amount_pd;
                                        $payment_status = $attendee->payment_status;
                                        $payment_date = $attendee->payment_date;
                                        $date = $attendee->date;
                                        $event_id = $attendee->event_id;
                                        $coupon_code = $attendee->coupon_code;
                                    }
                                    if ( $temp_reg_id == $registration_id )
                                    {
                                        $attendees_group .= "<li>$fname $lname - $email</li>";
                                        //$payment_status = $attendee->payment_status; //Seems to be showing the wrong status, moved into the upper loop
                                    } else $go = true;

                                    if ( $go || $wpdb->num_rows == $counter ){
                                        
                ?>
      <tr>
        <td class="check-column" style="padding:7px 0 22px 7px; vertical-align:top;"><input name="checkbox[<?php echo $temp_reg_id ?>]" type="checkbox"  title="Delete <?php echo $fname ?><?php echo $lname ?>"></td>
        <td class="post-title column-title"><a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;event_id=<?php echo $event_id; ?>&amp;registration_id=<?php echo $temp_reg_id; ?>&amp;form_action=edit_attendee&amp;id=<?php echo $id ?>" title="<?php _e( 'Registration #: '.$temp_reg_id, 'event_espresso' ); ?>">
          <ul>
            <?php echo $attendees_group ?>
          </ul>
          </a></td>
        <td class="manage-column column-date"><a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Edit Payment', 'event_espresso' ); ?> ID: <?php echo $temp_reg_id ?>">
          <?php event_espresso_paid_status_icon( $payment_status ) ?>
          </a></td>
        <td class="date column-date"><?php echo $amount_pd ?></td>
        <?php /* ?><td><?php echo $txn_type?></td>
                                          <td><?php echo $txn_id?></td><?php */ ?>
        <td class="date column-date"><?php echo $coupon_code ?></td>
        <?php /* ?><td><?php echo $quantity?></td><?php */ ?>
        <td class="date column-date" style="width:125px;"><?php echo event_date_display($date) ?></td>
        <td class="date column-date"><?php echo event_espresso_no_format_date($payment_date) == NULL ? '' : event_espresso_no_format_date($payment_date); ?></td>
        <td class="date column-date" style="width:80px;"><a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Edit Payment', 'event_espresso' ); ?> ID: <?php echo $temp_reg_id ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/money.png" width="16" height="16" alt="<?php _e( 'Edit Payment', 'event_espresso' ); ?>" /></a> 
             <a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=edit_attendee" title="<?php _e( 'Edit Attendee', 'event_espresso' ); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/user_edit.png" width="16" height="16" alt="<?php _e( 'Edit Attendee', 'event_espresso' ); ?>" /></a>
             <a href="admin.php?page=events&amp;event_admin_reports=resend_email&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=resend_email" title="<?php _e( 'Resend Registration Details', 'event_espresso' ); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_link.png" width="16" height="16" alt="<?php _e( 'Resend Registration Details', 'event_espresso' ); ?>" /></a></td>
      </tr>
      <?php
                                        $temp_reg_id = $registration_id;
                                        $attendees_group = "<li>$fname $lname - $email $quantity</li>";
                                        $go = false;
                                        $amount_pd = $attendee->amount_pd;
                                        $payment_status = $attendee->payment_status;
                                        $payment_date = $attendee->payment_date;
                                        $date = $attendee->date;
                                        $event_id = $attendee->event_id;
                                         $coupon_code = $attendee->coupon_code;
                                    }
                                     $counter++;
                                }
							}?>
    </tbody>
  </table>
  <div style="clear:both"><input type="checkbox" name="sAll" onclick="selectAll(this)" />
  <strong>
  <?php _e( 'Check All', 'event_espresso' ); ?>
  </strong>
  <input name="delete_customer" type="submit" class="button-secondary" id="delete_customer" value="<?php _e( 'Delete Customer(s)', 'event_espresso' ); ?>" style="margin-left:10px;" onclick="return confirmDelete();">
 
 <a  class="button-primary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;id=".$_REQUEST[ 'event_id' ]."&amp;export=report&action=payment&amp;type=excel";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><?php _e( 'Export to Excel', 'event_espresso' ); ?></a>
      
      <a style="margin-left:5px"  class="button-primary"  href="admin.php?page=events&amp;event_admin_reports=add_new_attendee&amp;event_id=<?php echo $_REQUEST[ 'event_id' ] ?>">
      <?php _e( 'Add Attendee', 'event_espresso' ); ?>
      </a>
      
       <a style="margin-left:5px"  class="button-primary"  href="admin.php?page=events&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $_REQUEST[ 'event_id' ] ?>">
      <?php _e( 'Send Newsletter', 'event_espresso' ); ?>
      </a>
    <a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=add_new_event"><?php _e('Add New Event','event_espresso'); ?></a>
    
     <a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo  $_REQUEST[ 'event_id' ] ?>">
      <?php _e( 'Edit Event', 'event_espresso' ); ?>
      </a> </div>
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
</div></div>
<?php
//End function list_attendee_payments
}
