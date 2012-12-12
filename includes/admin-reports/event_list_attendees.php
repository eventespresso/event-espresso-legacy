<?php
function event_list_attendees() {

    global $wpdb, $org_options, $ticketing_installed, $espresso_premium;
	
	define( 'EVT_ADMIN_URL', admin_url( 'admin.php?page=events' ));
	$EVT_ID = isset( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] != '' ? absint( $_REQUEST['event_id'] ) : FALSE;

	if ( $EVT_ID ){
		echo '<h1>'.espresso_event_list_attendee_title( $EVT_ID ).'</h1>'; 
	}	

	$max_rows = isset( $_REQUEST['max_rows'] ) & ! empty( $_REQUEST['max_rows'] ) ? absint( $_REQUEST['max_rows'] ) : 50;
	$start_rec = isset( $_REQUEST['start_rec'] ) && ! empty($_REQUEST['start_rec']) ? absint( $_REQUEST['start_rec'] ) : 0;
	$records_to_show = " LIMIT $max_rows OFFSET $start_rec ";
	
	//Dates
	$curdate = date('Y-m-d');
	$this_year_r = date('Y');
	$this_month_r = date('m');
	$days_this_month = date( 't', time() );

    if ( isset( $_POST['delete_customer'] ) && ! empty( $_POST['delete_customer'] )) {
        if ( is_array( $_POST['checkbox'] )) {
            while ( list( $att_id, $value ) = each( $_POST['checkbox'] )) {
                $SQL = "DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = '%d'";
                $wpdb->query( $wpdb->prepare( $SQL, $att_id ));
				$SQL = "DELETE FROM " . EVENTS_ATTENDEE_META_TABLE . " WHERE attendee_id = '%d'";
				$wpdb->query( $wpdb->prepare( $SQL, $att_id ));
				$SQL = "DELETE FROM " . EVENTS_ANSWER_TABLE . " WHERE attendee_id = '%d'";
				$wpdb->query( $wpdb->prepare( $SQL, $att_id ));				
			}
        }
		?>
		<div id="message" class="updated fade">
			<p>
				<strong><?php _e('Customer(s) have been successfully deleted from the event.', 'event_espresso'); ?></strong>
			</p>
		</div>
		<?php
    }
	
    //	MARKING USERS AS ATTENDED (OR NOT)
    if (( ! empty($_POST['attended_customer']) || ! empty($_POST['unattended_customer'])) && $ticketing_installed == TRUE ) {
        if ( is_array($_POST['checkbox'])) {
            while (list($att_id, $value) = each($_POST['checkbox'])) {
				// on / off value for attended status checkbox
				$checker = $value == "on" && $_POST['attended_customer'] ? 1 : 0;
				
				$SQL = "SELECT checked_in_quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id = %d ";                
                $ticket_scanned = $wpdb->get_var( $wpdb->prepare( $SQL, $att_id ));
				
                if ( $ticket_scanned >= 1 ) {
                    ?>
					<div id="message" class="error fade">
						<p>
							<strong><?php _e('Scanned tickets cannot be redeemed/un-redeemed here.', 'event_espresso'); ?></strong>
						</p>
					</div>
					<?php
				} else {
					
					if ( $wpdb->update( EVENTS_ATTENDEE_TABLE, array( 'checked_in' => $checker ), array( 'id' => $att_id ), array( '%d' ),  array( '%d' ))) {
					?>
					<div id="message" class="updated fade">
					  <p><strong>
						<?php _e('Customer(s) attendance data successfully updated for this event.', 'event_espresso'); ?>
						</strong></p>
					</div>
					<?php
					}
                }
            }
        }
    }
	
    require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/event-management/queries.php');

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php')) {
        require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/admin_reports_filters.php');
    } else {
		?>
		<p>
			<strong><?php _e('Advanced filters are available in the premium versions.', 'event_espresso');?></strong> 
			<a href="http://eventespresso.com/download/" target="_blank">
				<?php _e('Upgrade Now!', 'event_espresso');?>
			</a>
		</p>
		<?php
    }

	
	
	$sql_clause = " WHERE ";
    $sql_a = "(";
	
    if (function_exists('espresso_member_data') && espresso_member_data('role') == 'espresso_group_admin') {
	
        $group = get_user_meta(espresso_member_data('id'), "espresso_group", true);
        $group = implode(",", $group);
        $sql_a .= "SELECT a.*, e.id event_id, e.event_name, checked_in FROM " . EVENTS_ATTENDEE_TABLE . " a ";
        $sql_a .= " LEFT JOIN " . EVENTS_DETAIL_TABLE . " e ON e.id=a.event_id ";

        if ($_REQUEST['category_id'] != '') {
            $sql_a .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
            $sql_a .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
        }
        if ($group != '') {
            $sql_a .= " JOIN " . EVENTS_VENUE_REL_TABLE . " r ON r.event_id = e.id ";
            $sql_a .= " JOIN " . EVENTS_LOCALE_REL_TABLE . " l ON  l.venue_id = r.venue_id ";
        }
        $sql_a .= $_REQUEST['category_id'] != '' ? " AND c.id = '" . $_REQUEST['category_id'] . "' " : '';

        $sql_clause = " WHERE ";

        if ($_REQUEST['payment_status'] != '') {
            $sql_a .= " $sql_clause a.payment_status = '" . $_REQUEST['payment_status'] . "' ";
            $sql_clause = " AND ";
        }
        if ($_POST['month_range'] != '') {
            $pieces = explode('-', $_REQUEST['month_range'], 3);
            $year_r = $pieces[0];
            $month_r = $pieces[1];
            $sql_a .= " $sql_clause a.date BETWEEN '" . event_espresso_no_format_date($year_r . '-' . $month_r . '-01', $format = 'Y-m-d') . "' AND '" . event_espresso_no_format_date($year_r . '-' . $month_r . '-31', $format = 'Y-m-d') . "' ";
            $sql_clause = " AND ";
        }

        if ( $EVT_ID ) {
            $sql_a .= " $sql_clause a.event_id = '" . $EVT_ID . "' ";
            $sql_clause = " AND ";
        }

        if ($_REQUEST['today_a'] == 'true') {
            //$sql_a .= " $sql_clause a.date = '" . event_espresso_no_format_date($curdate,$format = 'Y-m-d') ."' ";
            $sql_a .= " $sql_clause a.date BETWEEN '" . $curdate . ' 00:00:00' . "' AND '" . $curdate . ' 23:59:59' . "' ";
            $sql_clause = " AND ";
        }

        if ($_REQUEST['this_month_a'] == 'true') {
            $sql_a .= " $sql_clause a.date BETWEEN '" . event_espresso_no_format_date($this_year_r . '-' . $this_month_r . '-01', $format = 'Y-m-d') . "' AND '" . event_espresso_no_format_date($this_year_r . '-' . $this_month_r . '-' . $days_this_month, $format = 'Y-m-d') . "' ";
            $sql_clause = " AND ";
        }
        $sql_a .= $group != '' ? $sql_clause . "  l.locale_id IN (" . $group . ") " : '';
		$sql_a .= " AND e.event_status != 'D' ";
        $sql_a .= ") UNION (";
		
    }
	
    $sql_a .= "SELECT a.*, e.id event_id, e.event_name, checked_in FROM " . EVENTS_ATTENDEE_TABLE . " a ";
    $sql_a .= " LEFT JOIN " . EVENTS_DETAIL_TABLE . " e ON e.id=a.event_id ";
	
    if (!empty($_REQUEST['category_id'])) {
        $sql_a .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
        $sql_a .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
    }

    $sql_a .= !empty($_REQUEST['category_id']) ? " AND c.id = '" . $_REQUEST['category_id'] . "' " : '';

    $sql_clause = " WHERE ";

    if (!empty($_REQUEST['payment_status'])) {
        $sql_a .= " $sql_clause a.payment_status = '" . $_REQUEST['payment_status'] . "' ";
        $sql_clause = " AND ";
    }

    if (!empty($_POST['month_range'])) {
        $pieces = explode('-', $_REQUEST['month_range'], 3);
        $year_r = $pieces[0];
        $month_r = $pieces[1];
        $sql_a .= " $sql_clause a.date BETWEEN '" . event_espresso_no_format_date($year_r . '-' . $month_r . '-01', $format = 'Y-m-d') . "' AND '" . event_espresso_no_format_date($year_r . '-' . $month_r . '-31', $format = 'Y-m-d') . "' ";
        $sql_clause = " AND ";
    }

    if ( $EVT_ID ) {
        $sql_a .= " $sql_clause a.event_id = '" . $EVT_ID . "' ";
        $sql_clause = " AND ";
    }
	
    if (!empty($_REQUEST['today_a'])) {
        //$sql_a .= " $sql_clause a.date = '" . event_espresso_no_format_date($curdate,$format = 'Y-m-d') ."' ";
        $sql_a .= " $sql_clause a.date BETWEEN '" . $curdate . ' 00:00:00' . "' AND '" . $curdate . ' 23:59:59' . "' ";
        $sql_clause = " AND ";
    }
	
    if (!empty($_REQUEST['this_month_a'])) {
        $sql_a .= " $sql_clause a.date BETWEEN '" . event_espresso_no_format_date($this_year_r . '-' . $this_month_r . '-01', $format = 'Y-m-d') . "' AND '" . event_espresso_no_format_date($this_year_r . '-' . $this_month_r . '-' . $days_this_month, $format = 'Y-m-d') . "' ";
        $sql_clause = " AND ";
    }
	
    if (function_exists('espresso_member_data') && ( espresso_member_data('role') == 'espresso_event_manager' || espresso_member_data('role') == 'espresso_group_admin')) {
        $sql_a .= $sql_clause . " e.wp_user = '" . espresso_member_data('id') . "' ";
    }
	$sql_a .= " $sql_clause e.event_status != 'D' ";
    $sql_a .= ") ORDER BY date DESC, id ASC ";
    $sql_a .= $records_to_show;
	
    $attendees = $wpdb->get_results($sql_a);
    $total_attendees = $wpdb->num_rows;
	
	$quantity =0;

	$att_table_form_url = add_query_arg( array( 'event_admin_reports' => 'list_attendee_payments', 'event_id' => $EVT_ID ), EVT_ADMIN_URL );
?>
<form id="attendee-admin-list-page-select-frm" name="attendee_admin_list_page_select_frm" method="post" action="<?php echo $att_table_form_url; ?>">
	<div id="attendee-admin-list-page-select-dv" class="admin-list-page-select-dv">
		<input name="navig" value="<?php _e('Retrieve', 'event_espresso'); ?>" type="submit" class="button-secondary">
		<?php //_e('a max total of', 'event_espresso'); ?>
		<?php $rows = array( 50 => 50, 100 => 100, 250 => 250, 500 => 500, 100000 => 'all' ); ?>
		<select name="max_rows" size="1">
			<?php foreach ( $rows as $key => $value ) { ?>
			<?php $selected = $key == $max_rows ? ' selected="selected"' : ''; ?>
			<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $value ?>&nbsp;&nbsp;</option>
			<?php } ?>
		</select>		
		<?php _e('rows from the db at a time', 'event_espresso'); ?>
		<input name="start_rec" value="<?php echo $start_rec ?>" class="textfield" type="hidden">
		<?php
			if ( $start_rec > 0 && $max_rows < 100000 ) {
				$prev_rows = $start_rec > $max_rows ? ( $start_rec - $max_rows - 1 ) : 0;
				$prev_rows_url = add_query_arg( array( 'event_admin_reports' => 'list_attendee_payments', 'event_id' => $EVT_ID, 'max_rows' => $max_rows, 'start_rec' => $prev_rows ), EVT_ADMIN_URL ); 
		?>
		<a id="attendee-admin-load-prev-rows-btn" href="<?php echo $prev_rows_url; ?>" title="load prev rows" class="button-secondary">
			<?php echo __('Previous', 'event_espresso') . ' ' . $max_rows  . ' ' .  __('rows', 'event_espresso'); ?>
		</a>
		<?php } ?>
		<?php 			
			if ( $total_attendees >= $max_rows && $max_rows < 100000 ) {
				$next_rows = $start_rec + $max_rows + 1;
				$next_rows_url = add_query_arg( array( 'event_admin_reports' => 'list_attendee_payments', 'event_id' => $EVT_ID, 'max_rows' => $max_rows, 'start_rec' => $next_rows ), EVT_ADMIN_URL ); 
		?>
		<a id="attendee-admin-load-next-rows-btn" href="<?php echo $next_rows_url; ?>" title="load next rows" class="button-secondary">
		<?php echo __('Next', 'event_espresso') . ' ' . $max_rows  . ' ' .  __('rows', 'event_espresso'); ?>
		</a> 
		<?php } ?>
	</div>
</form>
	
<form id="form1" name="form1" method="post" action="<?php echo $att_table_form_url; ?>">
	<table id="table" class="widefat fixed" width="100%">
		<thead>
			<tr>
				<th class="manage-column column-cb check-column" id="cb" scope="col" style="width:3%;min-width:35px !important;">
					<input type="checkbox">
				</th>
				<th class="manage-column column-att-id" id="att-id" scope="col" title="Click to Sort"style="width:3%;max-width:35px !important;"> 
					<span><?php _e('ID', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-name" id="name" scope="col" title="Click to Sort"style="width: 10%;"> 
					<span><?php _e('Attendee Name', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-registrationid" id="registrationid" scope="col" title="Click to Sort" style="width: 10%;">
				 	<span><?php _e('Reg ID', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-date" id="registration" scope="col" title="Click to Sort" style="width: 10%;"> 
					<span><?php _e('Registered', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-title" id="event-title" scope="col" title="Click to Sort" style="width: 10%;"> 
					<span><?php _e('Event Title', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-title" id="event-time" scope="col" title="Click to Sort" style="width: 8%;"> 
					<span><?php _e('Event Time', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<?php if ($ticketing_installed == true) { ?>
				<th class="manage-column column-title" id="attended" scope="col" title="Click to Sort" style="width: 8%;">
				 	<span><?php _e('Attended', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<?php } ?>
				<th class="manage-column column-title" id="ticket-option" scope="col" title="Click to Sort" style="width: 13%;">
				 	<span><?php _e('Option', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th align="center" class="manage-column column-date" id="amount" style="width: 5%;" title="Click to Sort" scope="col">
				 	<span><?php _e('Payment', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-date" id="payment_type" scope="col" title="Click to Sort" style="width: 8%;">
				 	<span><?php _e('Type', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-date" id="coupon" scope="col" title="Click to Sort" style="width: 10%;"> 
					<span><?php _e('Coupon', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-date" id="txn_id" scope="col" title="Click to Sort" style="width: 10%;"> 
					<span><?php _e('Transaction ID', 'event_espresso'); ?></span> <span class="sorting-indicator"></span> 
				</th>
				<th class="manage-column column-date" id="action" scope="col" title="" >
					<?php _e('Actions', 'event_espresso'); ?>
				</th>
			</tr>
		</thead>
		<tbody>	
<?php
	
    if ($total_attendees > 0) {
		foreach ($attendees as $attendee) {

			$id = $attendee->id;
			$registration_id = $attendee->registration_id;
			$lname = htmlspecialchars( stripslashes( $attendee->lname ), ENT_QUOTES, 'UTF-8' );
			$fname = htmlspecialchars( stripslashes( $attendee->fname ), ENT_QUOTES, 'UTF-8' );
			$address = htmlspecialchars( stripslashes( $attendee->address ), ENT_QUOTES, 'UTF-8' );
			$city = htmlspecialchars( stripslashes( $attendee->city ), ENT_QUOTES, 'UTF-8' );
			$state = htmlspecialchars( stripslashes( $attendee->state ), ENT_QUOTES, 'UTF-8' );
			$zip = $attendee->zip;
			$email = '<span style="visibility:hidden">' . $attendee->email . '</span>';
			$phone = $attendee->phone;
			$quantity = $attendee->quantity > 1 ? '<br />(' . __('Total Attendees', 'event_espresso') . ': ' . $attendee->quantity . ')' : '';

			$attended = $attendee->checked_in;
			$ticket_scanned = $attendee->checked_in_quantity;
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
			$event_date = $attendee->start_date;				
				
?>
			<tr>
			
				<td class="check-column" style="padding:7px 0 22px 7px; vertical-align:top;">
					<input name="checkbox[<?php echo $id ?>]" type="checkbox"  title="Delete <?php echo $fname ?><?php echo $lname ?>">
				</td>
				
	            <td nowrap="nowrap">
					<?php echo $attendee->id; ?>
				</td>
				
	            <td class="row-title"  nowrap="nowrap">
					<a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;event_id=<?php echo $event_id; ?>&amp;registration_id=<?php echo $registration_id; ?>&amp;form_action=edit_attendee&amp;id=<?php echo $id ?>" title="<?php echo 'ID#:'.$id.' [ REG#: ' . $registration_id.' ] Email: '.$attendee->email; ?>">
						<?php echo $fname ?> <?php echo $lname ?> <?php echo $email ?>
	              </a>
				 </td>
				
	            <td nowrap="nowrap">
					<?php echo $registration_id ?>
				</td>
				
	            <td class="date column-date">
					<?php echo event_date_display($date, get_option('date_format') . ' g:i a') ?>
				</td>
				
	            <td nowrap="nowrap">
					<a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('View attendees for this event', 'event_espresso'); ?>">
						<?php echo stripslashes_deep($event_name) ?>
					</a>
				</td>
				
	            <td nowrap="nowrap">
					<?php echo event_date_display($event_time, get_option('time_format')) ?>
				</td>
				
	            <?php if ($ticketing_installed == true) { ?>
	            <td nowrap="nowrap">
					<p style="padding-left:15px">
						<?php echo ($attended == 1 || $ticket_scanned >= 1) ? event_espresso_paid_status_icon('Checkedin') : event_espresso_paid_status_icon('NotCheckedin'); ?>
					</p>
				</td>
	            <?php } ?>
				
	            <td nowrap="nowrap">
					<?php echo $price_option ?>
				</td>
				
	            <td class="date column">
					<a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $registration_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Payment', 'event_espresso'); ?> ID: <?php echo $registration_id ?>">
	              		<p style="padding-left:17px"><?php event_espresso_paid_status_icon($payment_status) ?></p>
	              	</a> 
				</td>
				
				<td class="">
					<?php echo espresso_payment_type($txn_type); ?>
				</td>
				
	            <td class="">
					<?php echo $coupon_code ?>
				</td>
				
	            <td class="">
					<?php echo $txn_id ?>
				</td>
				
	            <td class="" >
	            
	            	<a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $registration_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Payment', 'event_espresso'); ?> ID: <?php echo $registration_id ?>">
						<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/money.png" width="16" height="16" alt="<?php _e('Edit Payment', 'event_espresso'); ?>" />
					</a>
	            
	            	<a href="admin.php?page=events&amp;event_admin_reports=edit_attendee_record&amp;registration_id=<?php echo $registration_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=edit_attendee" title="<?php _e('Edit Attendee', 'event_espresso'); ?>">
						<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/user_edit.png" width="16" height="16" alt="<?php _e('Edit Attendee', 'event_espresso'); ?>" />
					</a>
	            
	            	<a href="admin.php?page=events&amp;event_admin_reports=resend_email&amp;registration_id=<?php echo $registration_id ?>&amp;event_id=<?php echo $event_id ?>&amp;form_action=resend_email" title="<?php _e('Resend Registration Details', 'event_espresso'); ?>">
						<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_link.png" width="16" height="16" alt="<?php _e('Resend Registration Details', 'event_espresso'); ?>" />
					</a>
					
	            	<?php if ($espresso_premium == true){ ?>
	            	<a href="<?php echo home_url(); ?>/?download_invoice=true&amp;admin=true&amp;registration_id=<?php echo $registration_id ?>" target="_blank"  title="<?php _e('Download Invoice', 'event_espresso'); ?>">
						<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/page_white_acrobat.png" width="16" height="16" alt="<?php _e('Download Invoice', 'event_espresso'); ?>" />
					</a>
					<?php } ?>
				
					<?php if ( $ticketing_installed == true && function_exists('espresso_ticket_url')) { ?>
					<a href="<?php echo espresso_ticket_url($id, $registration_id); ?>" target="_blank"  title="<?php _e('View/Download Ticket', 'event_espresso');?>">
						<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL; ?>images/icons/ticket-arrow-icon.png" width="16" height="16" alt="<?php _e('Download Ticket', 'event_espresso');?>" />
					</a>
					<?php } 
			
					if ($org_options["use_attendee_pre_approval"] == "Y") { ?>
		  				<br/>
		  				<a href="admin.php?page=events&amp;attendee_pay=paynow&amp;form_action=payment&amp;registration_id=<?php echo $registration_id ?>&amp;event_admin_reports=enter_attendee_payments&amp;event_id=<?php echo $event_id ?>" title="<?php _e('Edit Payment', 'event_espresso'); ?> ID: <?php echo $registration_id ?>">
		  				<?php if (is_attendee_approved($event_id, $id)) {  ?>
		  					<strong><?php _e('Approved', 'event_espresso'); ?></strong><br/>
		  				<?php } else { ?>
		  					<span style="color:#FF0000"><strong><?php _e('Awaiting approval', 'event_espresso'); ?></strong></span>
		  				<?php } ?>
		  				</a>
	  				<?php } ?>
					
				</td>
				
			</tr>
<?php
		}		
	}
?>
		</tbody>
	</table>
	
  	<div style="clear:both; margin-bottom:30px;">
	
    	<input name="delete_customer" type="submit" class="button-secondary" id="delete_customer" value="<?php _e('Delete Attendee(s)', 'event_espresso'); ?>" style="margin:10px 0 0 0;" onclick="return confirmDelete();" />
		
	    <?php if ($ticketing_installed == true) { ?>
	    <input name="attended_customer" type="submit" class="button-secondary" id="attended_customer" value="<?php _e('Mark as Attended', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" />
	    <input name="unattended_customer" type="submit" class="button-secondary" id="attended_customer" value="<?php _e('Unmark as Attended', 'event_espresso'); ?>" style="margin:10px 0 0 10px;" />
	    <?php } ?>
		
	    <a style="margin-left:5px" class="button-secondary" href="admin.php?page=events&amp;action=csv_import">
	    <?php _e('Import Events', 'event_espresso'); ?>
	    </a> 
		
		<?php if (function_exists('espresso_attendee_import') && $espresso_premium == true) {?>
		<a style="margin-left:5px" class="button-secondary" href="admin.php?page=espresso_attendee_import">
			<?php _e('Import Attendees', 'event_espresso'); ?>
		</a>
		<?php } ?>
		
		<a class="button-secondary" style="margin-left:5px" href="#" onclick="window.location='<?php echo get_bloginfo('wpurl') . "/wp-admin/admin.php?event_espresso&amp;export=report&action=payment&amp;type=excel&amp;"; echo $EVT_ID  ? "event_id=" . $EVT_ID : "all_events=true"; ?>'" title="<?php _e('Export to Excel', 'event_espresso'); ?>">
	    	<?php _e('Export to Excel', 'event_espresso'); ?>
	    </a> 
		
		<?php if( $EVT_ID ) { ?>
		<a style="margin-left:5px"  class="button-secondary"  href="admin.php?page=events&amp;event_admin_reports=add_new_attendee&amp;event_id=<?php echo $EVT_ID;?>">
			<?php _e('Add Attendee', 'event_espresso')?>
		</a>
		<?php } ?> 
		
		<?php if ( $EVT_ID ) { ?>
		<a style="margin-left:5px" class="button-primary" href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $EVT_ID?>">
			<?php _e('Edit Event', 'event_espresso')?>
		</a>
		<?php } ?> 
		
	</div>
	
</form>

<h4 style="clear:both"><?php _e('Legend', 'event_espresso'); ?></h4>

<dl style="float:left; margin-left:10px; width:200px">
	<dt>
		<?php event_espresso_paid_status_icon('Completed') ?> - <?php _e('Completed', 'event_espresso'); ?>
	</dt>
	<dt>
		<?php event_espresso_paid_status_icon('Incomplete') ?> - <?php _e('Incomplete', 'event_espresso'); ?>
	</dt>
    <dt>
		<?php event_espresso_paid_status_icon('Pending') ?> - <?php _e('Pending', 'event_espresso'); ?>
	</dt>
	<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/money.png" width="16" height="16" alt="<?php _e('Payment Details', 'event_espresso'); ?>" /> - <?php _e('Payment Details', 'event_espresso'); ?>
	</dt>
</dl>

 <dl style="float:left; margin-left:10px; width:200px">
	<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_link.png" width="16" height="16" alt="<?php _e('Resend Details', 'event_espresso'); ?>" /> - <?php _e('Resend Email', 'event_espresso'); ?>
	</dt>
	<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/page_white_acrobat.png" width="16" height="16" alt="<?php _e('Download Invoice', 'event_espresso'); ?>" /> - <?php _e('Download Invoice', 'event_espresso'); ?>
	</dt>
	<dt>
		<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/user_edit.png" width="16" height="16" alt="<?php _e(' Attendee Details', 'event_espresso'); ?>" /> - <?php _e('Attendee Details', 'event_espresso'); ?>
	</dt>
</dl>
<?php 


$hide = $EVT_ID ? '1,5' : '1,3';
$hide .= $ticketing_installed ? ',11,12' : ',10,11'; 

?>
<script>
	jQuery(document).ready(function($) {
		/* show the table data */
		var mytable = $('#table').dataTable( {
			"sDom": 'Clfrtip',
			"bAutoWidth": false,
			"bStateSave": true,
			"sPaginationType": "full_numbers",
			"oLanguage": {    "sSearch": "<strong><?php _e('Live Search Filter', 'event_espresso'); ?>:</strong> (eg, email, txn id, event, etc.)",
			"sZeroRecords": "<?php _e('No Records Found!', 'event_espresso'); ?>" },
			"aoColumns": [
				{ "bSortable": false },
				null,
				<?php echo $ticketing_installed == true ? 'null,' : '' ?>
				null,
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
			],
			"aoColumnDefs": [
				{ "bVisible": false, "aTargets": [<?php echo $hide; ?>] }
			],
			"oColVis": {
				"aiExclude": [0,2],
				"buttonText": "Filter: Show / Hide Columns",
				"bRestore": true
			},
		});
	});
</script>
<?php
}
