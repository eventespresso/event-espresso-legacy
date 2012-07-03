<?php
//Displays the list of attendees and the paymnts they have made
function list_attendee_payments() {
    global $wpdb, $org_options;

    function event_espresso_paid_status_icon( $payment_status ='' ) {
        if ( $payment_status == "None" || $payment_status == "" || $payment_status == "Incomplete" )
        {
            echo '<center><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/exclamation.png" width="16" height="16" alt="' . __( 'None', 'event_espresso' ) . '" /></center>';
        }
        else if ( $payment_status == "Pending" )
        {
            echo '<center><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/error.png" width="16" height="16" alt="' . __( 'Pending', 'event_espresso' ) . '" /></center>';
        }
        else if ( $payment_status == "Completed" )
        {
            echo '<center><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/accept.png" width="16" height="16" alt="' . __( 'Completed', 'event_espresso' ) . '" title="' . __( 'Completed', 'event_espresso' ) . '" /></center>';
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
            $event_name = $event->event_name;
            $event_desc = $event->event_desc;
            $event_description = $event->event_desc;
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
?>
<h3><a id="event-id-<?php echo $event_id ?>" name="event-id-<?php echo $event_id ?>" title="<?php _e( 'View event page', 'event_espresso' ); ?>" href="<?php echo get_option( 'siteurl' ) ?>/?page_id=<?php echo $org_options[ 'event_page_id' ] ?>&amp;regevent_action=register&amp;event_id=<?php echo $event_id ?>&amp;name_of_event=<?php echo $event_name ?>" target="_blank"><?php echo $event_name ?></a> |
  <?php _e( 'Start Date:', 'event_espresso' ); ?>
  <?php echo event_date_display( $start_date ) ?> <?php echo $start_time ?> |
  <?php _e( 'Attendees:', 'event_espresso' ); ?>
  <?php echo get_number_of_attendees_reg_limit( $event_id ); ?> | <?php echo $status[ 'display' ] ?></h3>
<div style="float:right; margin:10px 20px;">
  <ul>
    <li>
      <button style="margin-left:20px" class="button-primary" onclick="window.location='<?php echo get_bloginfo( 'wpurl' ) . "/wp-admin/admin.php?event_espresso&id=" . $event_id . "&export=report&action=payment"; ?>'" >
      <?php _e( 'Export to Excel', 'event_espresso' ); ?>
      </button>
      | <a class="button-primary" href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id ?>">
      <?php _e( 'Edit Event', 'event_espresso' ); ?>
      </a> | <a  class="button-primary"  href="admin.php?page=admin_reports&amp;event_admin_reports=add_new_attendee&amp;event_id=<?php echo $event_id ?>">
      <?php _e( 'Add Attendee', 'event_espresso' ); ?>
      </a></li>
  </ul>
</div>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
  <div id="tablewrapper">
  <div id="tableheader">
    <div class="search">
      <select id="columns" onchange="sorter.search('query')">
      </select>
      <input type="text" id="query" onkeyup="sorter.search('query')" />
    </div>
    <span class="details">
    <div>
      <?php _e( 'Records', 'event_espresso' ); ?>
      <span id="startrecord"></span>-<span id="endrecord"></span>
      <?php _e( 'of', 'event_espresso' ); ?>
      <span id="totalrecords"></span></div>
    <div><a href="javascript:sorter.reset()">
      <?php _e( 'Reset', 'event_espresso' ); ?>
      </a></div>
    </span> </div>
  <table id="table" class="tinytable">
    <thead>
      <tr>
        <th><h3>
            <?php _e( 'Delete', 'event_espresso' ); ?>
          </h3></th>
        <?php /*?><th><h3>
            <?php _e( 'ID', 'event_espresso' ); ?>
          </h3></th><?php */?>
        <th><h3>
            <?php _e( 'Name', 'event_espresso' ); ?>
          </h3></th>
        <th><h3>
            <?php _e( 'Pay Status', 'event_espresso' ); ?>
          </h3></th>
        <th><h3>
            <?php _e( 'Amount', 'event_espresso' ); ?>
          </h3></th>
        <?php /* ?><th><h3>
                              <?php _e('TXN Type','event_espresso'); ?>
                              </h3></th>
                              <th><h3>
                              <?php _e('TXN ID','event_espresso'); ?>
                              </h3></th><?php */ ?>
        <th><h3>
            <?php _e( 'Coupon', 'event_espresso' ); ?>
          </h3></th>
        <?php /* ?><th><h3>
                              <?php _e('Quantity','event_espresso'); ?>
                              </h3></th><?php */ ?>
        <th><h3>
            <?php _e( 'Registration Date', 'event_espresso' ); ?>
          </h3></th>
          <th><h3>
            <?php _e( 'Payment Date', 'event_espresso' ); ?>
          </h3></th>
        <th><h3>
            <?php _e( 'Action', 'event_espresso' ); ?>
          </h3></th>
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

                                    if ( $go || $wpdb->num_rows == $counter )
                                    {
                                        
                ?>
      <tr>
        <td><input name="checkbox[<?php echo $temp_reg_id ?>]" type="checkbox"  title="Delete <?php echo $fname ?><?php echo $lname ?>"></td>
       <?php /*?> <td><?php echo $id ?></td><?php */?>
        <td><a href="admin.php?page=admin_reports&amp;event_admin_reports=edit_attendee_record&amp;event_id=<?php echo $event_id; ?>&amp;registration_id=<?php echo $temp_reg_id; ?>&amp;form_action=edit_attendee&amp;id=<?php echo $id ?>" title="<?php _e( 'Registration #: '.$temp_reg_id, 'event_espresso' ); ?>">
          <ol>
            <?php echo $attendees_group ?>
          </ol>
          </a></td>
        <td><a href="admin.php?page=admin_reports&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Edit Payment', 'event_espresso' ); ?> ID: <?php echo $temp_reg_id ?>">
          <?php event_espresso_paid_status_icon( $payment_status ) ?>
          </a></td>
        <td><?php echo $amount_pd ?></td>
        <?php /* ?><td><?php echo $txn_type?></td>
                                          <td><?php echo $txn_id?></td><?php */ ?>
        <td><?php echo $coupon_code ?></td>
        <?php /* ?><td><?php echo $quantity?></td><?php */ ?>
        <td><?php echo event_date_display($date) ?></td>
        <td><?php echo event_espresso_no_format_date($payment_date) == NULL ? '' : $payment_date; ?></td>
        <td><a href="admin.php?page=admin_reports&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e( 'Edit Payment', 'event_espresso' ); ?> ID: <?php echo $temp_reg_id ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/money.png" width="16" height="16" alt="<?php _e( 'Edit Payment', 'event_espresso' ); ?>" /></a> 
            | <a href="admin.php?page=admin_reports&amp;event_admin_reports=edit_attendee_record&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=edit_attendee" title="<?php _e( 'Edit Attendee', 'event_espresso' ); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/user_edit.png" width="16" height="16" alt="<?php _e( 'Edit Attendee', 'event_espresso' ); ?>" /></a>
            | <a href="admin.php?page=admin_reports&amp;event_admin_reports=resend_email&amp;registration_id=<?php echo $temp_reg_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=resend_email" title="<?php _e( 'Resend Registration Details', 'event_espresso' ); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_link.png" width="16" height="16" alt="<?php _e( 'Resend Registration Details', 'event_espresso' ); ?>" /></a></td>
      </tr>
      <?php
                                        $temp_reg_id = $registration_id;
                                        $attendees_group = "<li>$fname $lname - $email</li>";
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

                            }
                            else
                            {
                ?>
      <tr>
        <td><?php _e( 'No Record Found!', 'event_espresso' ); ?></td>
      <tr>
        <?php

                            }
                    ?>
    </tbody>
  </table>
  <input type="checkbox" name="sAll" onclick="selectAll(this)" />
  <strong>
  <?php _e( 'Check All', 'event_espresso' ); ?>
  </strong>
  <input name="delete_customer" type="submit" class="button-secondary" id="delete_customer" value="<?php _e( 'Delete Customer(s)', 'event_espresso' ); ?>" style="margin-left:100px;" onclick="return confirmDelete();">
</form>
<div id="tablefooter">
  <div id="tablenav">
    <div> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" /> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" /> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" /> <img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" /> </div>
    <div>
      <select id="pagedropdown">
      </select>
    </div>
    <div> <a href="javascript:sorter.showall()">
      <?php _e( 'View All', 'event_espresso' ); ?>
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
      <?php _e( 'Entries Per Page', 'event_espresso' ); ?>
      </span> </div>
    <div class="page">
      <?php _e( 'Page', 'event_espresso' ); ?>
      <span id="currentpage"></span>
      <?php _e( 'of', 'event_espresso' ); ?>
      <span id="totalpages"></span></div>
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
                        size:30,
                        colddid:'columns',
                        currentid:'currentpage',
                        totalid:'totalpages',
                        startingrecid:'startrecord',
                        endingrecid:'endrecord',
                        totalrecid:'totalrecords',
                        hoverid:'selectedrow',
                        pageddid:'pagedropdown',
                        navid:'tablenav',
                        sortcolumn:0,
                        sortdir:1,
                        sum:[3],
                        //avg:[2,7,8,9],
                        columns:[{index:3, format:'<?php echo $org_options[ 'currency_symbol' ] ?>', decimals:2}],
                        init:true
                    });
                </script>
<?php
//End function list_attendee_payments
                        }