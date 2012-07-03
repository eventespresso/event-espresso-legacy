<?php
function event_list_attendees(){
	global $wpdb, $org_options;
		
		if ( $_POST[ 'delete_customer' ] ){
        if ( is_array( $_POST[ 'checkbox' ] ) )
        {
            while ( list($key, $value) = each( $_POST[ 'checkbox' ] ) ):
                $del_id = $key;

                $sql = "DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id = '$del_id'";
                $wpdb->query( $sql );
            endwhile;
        }
		?>
		
		<div id="message" class="updated fade">
		  <p><strong>
			<?php _e( 'Customer(s) have been successfully deleted from the event.', 'event_espresso' ); ?>
			</strong></p>
		</div>
		<?php

	}
	
	
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php')){
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH. 'includes/admin-files/admin_reports_filters.php');
	}
	?>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
<table id="table" class="widefat fixed" width="100%"> 
	<thead>
		<tr>
          <th class="manage-column column-cb check-column" id="cb" scope="col" style="width: 5%;"><input type="checkbox"></th>
		  <th class="manage-column column-title" id="name" scope="col" title="Click to Sort"style="width: 15%;"><?php _e('Attendee Name','event_espresso'); ?></th>
          <th class="manage-column column-date" id="registration" scope="col" title="Click to Sort" style="width: 10%;"><?php _e('Registered','event_espresso'); ?></th>
          <th class="manage-column column-title" id="event" scope="col" title="Click to Sort" style="width: 10%;"><?php _e('Event','event_espresso'); ?></th>
          <th class="manage-column column-title" id="event" scope="col" title="Click to Sort" style="width: 10%;"><?php _e('Option','event_espresso'); ?></th>
          <th class="manage-column column-title" id="event" scope="col" title="Click to Sort" style="width: 10%;"><?php _e('Time','event_espresso'); ?></th>
		  <th class="manage-column column-date" id="amount" scope="col" title="Click to Sort" style="width: 10%;"><?php _e('Payment','event_espresso'); ?></th>
          <th class="manage-column column-date" id="payment_type" scope="col" title="Click to Sort" style="width: 10%;"><?php _e('Type','event_espresso'); ?></th>
          <th class="manage-column column-date" id="coupon" scope="col" title="Click to Sort" style="width: 10%;"><?php _e('Coupon','event_espresso'); ?></th>
          
          <th class="manage-column column-date" id="txn_id" scope="col" title="Click to Sort" style="width: 15%;"><?php _e('Transaction ID','event_espresso'); ?></th>
          <th class="manage-column column-date" id="action" scope="col" title="Click to Sort"style="width: 10%;"><?php _e( 'Action', 'event_espresso' ); ?></th>
		</tr>
	</thead>
     
        <tbody>
      <?php
                            $temp_reg_id = ''; //will temporarily hold the registration id for checking with the next row
                            $attendees_group = ''; //will hold the names of the group members
                            $counter = 0; //used for keeping track of the last row.  If counter = num_rows, print
                            $go = false; //triggers the output when true.  Set when the next reg id != temp_reg_id
                            $sql_a = "SELECT a.*, e.id event_id, e.event_name FROM " . EVENTS_ATTENDEE_TABLE. " a ";
							$sql_a .= " LEFT JOIN ". EVENTS_DETAIL_TABLE ." e ON e.id=a.event_id ";
							
							if ($_REQUEST[ 'category_id' ] !=''){
								$sql_a .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
								$sql_a .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
							}
							
							$sql_a .= $_REQUEST[ 'category_id' ] !='' ? " AND c.id = '" . $_REQUEST[ 'category_id' ] . "' " : '';
							
							$sql_cluase = " WHERE ";
							
							if ($_REQUEST[ 'payment_status' ] !=''){
								$sql_a .= " $sql_cluase a.payment_status = '" . $_REQUEST[ 'payment_status' ] . "' ";
								$sql_cluase = " AND ";
							}
														
							if ($_POST[ 'month_range' ] !=''){
								$pieces = explode('-',$_REQUEST['month_range'], 3);
								$year_r = $pieces[0];
								$month_r = $pieces[1];
								$sql_a .= " $sql_cluase a.date BETWEEN '".event_espresso_no_format_date($year_r. '-' .$month_r . '-01',$format = 'Y-m-d')."' AND '".event_espresso_no_format_date($year_r . '-' .$month_r. '-31',$format = 'Y-m-d')."' ";
								$sql_cluase = " AND ";
							}
							
							if ($_REQUEST[ 'event_id' ] !=''){
								$sql_a .= " $sql_cluase a.event_id = '" . $_REQUEST[ 'event_id' ] . "' ";
								$sql_cluase = " AND ";
							}
							
							if ($_REQUEST[ 'today_a' ]=='true'){
								//$sql_a .= " $sql_cluase a.date = '" . event_espresso_no_format_date($curdate,$format = 'Y-m-d') ."' ";
								$sql_a .= " $sql_cluase a.date BETWEEN '". $curdate.' 00:00:00'."' AND '". $curdate.' 23:59:59' ."' ";
								$sql_cluase = " AND ";
							}
							
							if ($_REQUEST[ 'this_month_a' ]=='true'){
								$sql_a .= " $sql_cluase a.date BETWEEN '".event_espresso_no_format_date($this_year_r. '-' .$this_month_r . '-01',$format = 'Y-m-d')."' AND '".event_espresso_no_format_date($this_year_r . '-' .$this_month_r. '-' . $days_this_month,$format = 'Y-m-d')."' ";
								$sql_cluase = " AND ";
							}
			
							$sql_a .= " ORDER BY a.date DESC ";
							
							//echo $sql_a;
							
                            $attendees = $wpdb->get_results( $sql_a );

                            if ( $wpdb->num_rows > 0 )
                            {
                                for ($i = 0; $i <= $wpdb->num_rows; $i++){
                                $attendee = $attendees[$i];

                                    $registration_id = $attendee->registration_id;
                                    $lname = $attendee->lname;
                                    $fname = $attendee->fname;
                                    $address = $attendee->address;
                                    $city = $attendee->city;
                                    $state = $attendee->state;
                                    $zip = $attendee->zip;
                                    $email = '<span style="visibility:hidden"'.$attendee->email.'</span>';
                                    $phone = $attendee->phone;
									$quantity = $attendee->quantity >1?'<br />('.__('Total Attendees', 'event_espresso') .': '.$attendee->quantity.')':'';
                                   
                                    if ( $temp_reg_id == ''){
										$id = $attendee->id;
                                        $temp_reg_id = $registration_id;
										
                                        $amount_pd = $attendee->amount_pd;
                                        $payment_status = $attendee->payment_status;
                                        $payment_date = $attendee->payment_date;
                                        $date = $attendee->date;
                                        $event_id = $attendee->event_id;
                                        $coupon_code = $attendee->coupon_code;
										$txn_id = $attendee->txn_id;
										$txn_type = $attendee->txn_type;
										$price_option = $attendee->price_option;
										$event_time = $attendee->event_time;
										$event_name = $attendee->event_name;
                                    }
                                    if ( $temp_reg_id == $registration_id )
                                    {
                                        $attendees_group .= "<li>$fname $lname $email <span style=\"visibility:hidden\"".$registration_id."</span></li>";
                                        //$payment_status = $attendee->payment_status; //Seems to be showing the wrong status, moved into the upper loop
                                    } else $go = true;

                                    if ( $go || $wpdb->num_rows == $counter ){
                                        
                ?>
      <tr>
      
        <td class="check-column" style="padding:7px 0 22px 7px; vertical-align:top;"><input name="checkbox[<?php echo $temp_reg_id ?>]" type="checkbox"  title="Delete <?php echo $fname ?><?php echo $lname ?>"></td>
        
        <td class="row-title"  nowrap="nowrap"><a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;event_id=<?php echo $event_id; ?>&amp;registration_id=<?php echo $temp_reg_id; ?>&amp;form_action=edit_attendee&amp;id=<?php echo $id ?>" title="<?php _e( 'Registration #: '.$temp_reg_id, 'event_espresso' ); ?>">
          <ul>
            <?php echo $attendees_group ?>
          </ul>
          </a></td>
            <td class="date column-date"><?php echo event_date_display($date) ?></td>
            <td nowrap="nowrap"><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>" title="<?php _e('View attendees for this event', 'event_espresso'); ?>"><?php echo stripslashes_deep($event_name)?></a></td>
            <td nowrap="nowrap"><?php echo $price_option ?></td>
            <td nowrap="nowrap"><?php echo $event_time ?></td>
            <td class="date column-date"><a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Edit Payment', 'event_espresso' ); ?> ID: <?php echo $temp_reg_id ?>">
              <?php event_espresso_paid_status_icon( $payment_status ) ?>
              </a> <a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Edit Payment', 'event_espresso' ); ?> ID: <?php echo $temp_reg_id ?>"><?php echo $org_options[ 'currency_symbol' ] ?><?php echo $amount_pd ?></a></td>
            <td class="date column-date"><?php echo espresso_payment_type($txn_type); ?></td>
            <td class="date column-date"><?php echo $coupon_code ?></td>
            <td class="date column-date"><?php echo $txn_id ?></td>
            <td class="date column-date" ><a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Edit Payment', 'event_espresso' ); ?> ID: <?php echo $temp_reg_id ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/money.png" width="16" height="16" alt="<?php _e( 'Edit Payment', 'event_espresso' ); ?>" /></a> <a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=edit_attendee" title="<?php _e( 'Edit Attendee', 'event_espresso' ); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/user_edit.png" width="16" height="16" alt="<?php _e( 'Edit Attendee', 'event_espresso' ); ?>" /></a> <a href="admin.php?page=events&amp;event_admin_reports=resend_email&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=resend_email" title="<?php _e( 'Resend Registration Details', 'event_espresso' ); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_link.png" width="16" height="16" alt="<?php _e( 'Resend Registration Details', 'event_espresso' ); ?>" /></a></td>
      </tr>
      <?php
                                        $id = $attendee->id;
										$temp_reg_id = $registration_id;
										$email = '<span style="visibility:hidden">'.$attendee->email.'</span>';
                                        $attendees_group = "<li>$fname $lname $email $quantity</li>";
                                        $go = false;
										/*$lname = $attendee->lname;
										$fname = $attendee->fname;
										$address = $attendee->address;
										$city = $attendee->city;
										$state = $attendee->state;
										$zip = $attendee->zip;
										$email = $attendee->email;
										$phone = $attendee->phone;
										$quantity = $attendee->quantity >1?'('.$attendee->quantity.')':'';*/
                                        $amount_pd = $attendee->amount_pd;
                                        $payment_status = $attendee->payment_status;
                                        $payment_date = $attendee->payment_date;
                                        $date = $attendee->date;
                                        $event_id = $attendee->event_id;
                                        $coupon_code = $attendee->coupon_code;
										$txn_type = $attendee->txn_type;
										$txn_id = $attendee->txn_id;
										$event_name = $attendee->event_name;
                                    }
                                     $counter++;
                                }
							}?>
    </tbody>
          </table>
        <div style="clear:both">
         	<input name="delete_customer" type="submit" class="button-secondary" id="delete_customer" value="<?php _e( 'Delete Attendee(s)', 'event_espresso' ); ?>" style="margin:10px 0 0 10px;" onclick="return confirmDel	ete();" />
            
      <a  style="margin-left:5px"class="button-primary" href="admin.php?page=events&amp;action=csv_import"><?php _e('Import CSV','event_espresso'); ?></a>
    
    <a  class="button-primary" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;event_id=".$_REQUEST[ 'event_id' ]."&amp;export=report&action=payment&amp;type=excel";echo $_REQUEST[ 'event_id' ]==''? '&amp;all_events=true':'';?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><?php _e( 'Export to Excel', 'event_espresso' ); ?></a>
    
    <?php echo $_REQUEST[ 'event_id' ]!=''?'<a style="margin-left:5px"  class="button-primary"  href="admin.php?page=events&amp;event_admin_reports=add_new_attendee&amp;event_id='.$_REQUEST[ 'event_id' ].'">'.__( 'Add Attendee', 'event_espresso' ).'</a>':''; ?>
    
    <?php echo $_REQUEST[ 'event_id' ]!=''?'<a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=edit&amp;event_id=' .$_REQUEST[ 'event_id' ]. '">' .__( 'Edit Event', 'event_espresso' ).'</a>':'';?>
		</div>
          </form>
<script>
jQuery(document).ready(function($) {                        
    
    /* show the table data */
    var mytable = $('#table').dataTable( {
            "bStateSave": true,
            "sPaginationType": "full_numbers",

            "oLanguage": {    "sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong> (eg, email, txn id, event, etc.)",
                             "sZeroRecords": "<?php _e('No Records Found!','event_espresso'); ?>" },
            "aoColumns": [
                            { "bSortable": false },
                             null,
                             null,
                             null,
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

<?php
}