<?php
	global $espresso_premium; if ($espresso_premium != true) return;
?>
<ul class="subsubsub" style="margin-bottom: 0;clear:both;">
	<li><strong><?php _e('Events', 'event_espresso'); ?>: </strong> </li>
    <li><a <?php echo $_REQUEST[ 'all' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&all=true"><?php _e('All Events', 'event_espresso'); ?> <span class="count">(<?php echo $total_events ?>)</span></a> |</li>
    <li><a <?php echo $_REQUEST[ 'today' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&today=true"><?php _e('Today', 'event_espresso'); ?> <span class="count">(<?php echo $total_events_today ?>)</span></a> |</li>
    <li><a <?php echo $_REQUEST[ 'this_month' ]=='true'? ' class="current" ':''?>  href="admin.php?page=events&this_month=true"><?php _e('This Month', 'event_espresso'); ?> <span class="count">(<?php echo $total_events_this_month ?>)</span></a></li>
</ul>
<ul class="subsubsub" style="clear:both;margin-bottom: 10;">
	<li><strong><?php _e('Attendees', 'event_espresso'); ?>: </strong> </li>
    <li><a <?php echo $_REQUEST[ 'all_a' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&event_admin_reports=event_list_attendees&all_a=true"><?php _e('All Attendees', 'event_espresso'); ?> <span class="count">(<?php echo $total_a ?>)</span></a> | </li>
    <li><a <?php echo $_REQUEST[ 'today_a' ]=='true'? ' class="current" ':''?> href="admin.php?page=events&event_admin_reports=event_list_attendees&today_a=true"><?php _e('Today', 'event_espresso'); ?> <span class="count">(<?php echo $total_a_today ?>)</span></a> |</li>
    <li><a <?php echo $_REQUEST[ 'this_month_a' ]=='true'? ' class="current" ':''?>  href="admin.php?page=events&event_admin_reports=event_list_attendees&this_month_a=true"><?php _e('This Month', 'event_espresso'); ?> <span class="count">(<?php echo $total_a_this_month ?>)</span></a> </li>
    
</ul>
<?php 
if ($_REQUEST['page']=='events'){?>
    <div class="tablenav" style="margin-bottom:10px;">
        <div class="alignleft actions">
        <form id="form2" name="form2" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
<?php 
	switch($_REQUEST['event_admin_reports']){
		case'event_list_attendees':
		case'edit_attendee_record':
		case'resend_email':
		case'enter_attendee_payments':
		case'list_attendee_payments':
		case'add_new_attendee';
?>
        <?php espresso_attendees_by_month_dropdown($_POST[ 'month_range' ]); //echo $_POST[ 'month_range' ];  ?>
        <input type="submit" class="button-secondary" value="Filter Month" id="post-query-month">
        <?php echo espresso_category_dropdown($_REQUEST[ 'category_id' ]); ?> <input type="submit" class="button-secondary" value="Filter Category" id="post-query-category">
        <?php
            //Payment status drop down
            $status=array(array('id'=>'','text'=> __('Show All Completed/Incomplete','event_espresso')),array('id'=>'Completed','text'=> __('Completed','event_espresso')),array('id'=>'Pending','text'=> __('Pending','event_espresso')),array('id'=>'Incomplete','text'=> __('Incomplete','event_espresso')), array('id'=>'Payment Declined','text'=> __('Payment Declined','event_espresso'))); 
        
            echo select_input('payment_status', $status, $_REQUEST[ 'payment_status' ]);
        ?>
        <input type="submit" class="button-secondary" value="Filter Status" id="post-query-payment">
        <a class="button-secondary" href="admin.php?page=events&event_admin_reports=event_list_attendees" style=" width:40px; display:inline"><?php _e('Reset Filters', 'event_espresso'); ?></a>
<?php 
		break;
		
		default:
?>
<?php espresso_event_months_dropdown($_POST[ 'month_range' ]); //echo $_POST[ 'month_range' ];  ?>
    <input type="submit" class="button-secondary" value="Filter Month" id="post-query-submit">
    <?php echo espresso_category_dropdown($_REQUEST[ 'category_id' ]); ?><input type="submit" class="button-secondary" value="Filter Category" id="post-query-submit">
    <?php  $status=array(array('id'=>'','text'=> __('Show Active/Inactive','event_espresso')),array('id'=>'A','text'=> __('Active','event_espresso')),array('id'=>'IA','text'=> __('Inactive','event_espresso')), array('id'=>'S','text'=> __('Secondary','event_espresso')), array('id'=>'O','text'=> __('Ongoing','event_espresso')), array('id'=>'D','text'=> __('Deleted','event_espresso'))); echo select_input('event_status', $status, $_REQUEST[ 'event_status' ]);?>
    
    <input type="submit" class="button-secondary" value="Filter Status" id="post-query-submit">
    <a class="button-secondary" href="admin.php?page=events" style=" width:40px; display:inline"><?php _e('Reset Filters', 'event_espresso'); ?></a>
<?php		
		break;

	}
?>
        </form>
        </div>
    </div>
<?php }?>