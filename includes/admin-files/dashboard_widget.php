<?php //Admin Dashboard Widget

// WP Event Dashboard Widget Table Function
function event_espresso_edit_list_widget(){
    global $wpdb, $org_options;
    
	if ($org_options['espresso_dashboard_widget'] != 'Y')
		return;
	
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
    ?>
    

    <form id="form1" name="form1" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ] ?>">
    <table id="table" class="widefat fixed" width="100%"> 
    <thead>
        <tr>
          <th class="manage-column column-title" id="title" scope="col" title="Click to Sort"><?php _e('Name','event_espresso'); ?></th>
          <th class="manage-column column-author" id="author" scope="col" title="Click to Sort"><?php _e('Date','event_espresso'); ?></th>
          <th class="manage-column column-date" id="date" scope="col" title="Click to Sort"><?php _e('Status','event_espresso'); ?></th>
          <th class="manage-column column-date" id="date" scope="col" title="Click to Sort"><?php _e('Attendees','event_espresso'); ?></th>
          <th class="manage-column column-author" id="author" scope="col" title="Click to Sort"><?php _e('Actions','event_espresso'); ?></th>
        </tr>
    </thead>
     
    <tbody>
    <?php 
		$wpdb->query("SELECT id FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D'");
		$total_events =    $wpdb->num_rows;
        if ($total_events > 0) {
        
           /* if ($_REQUEST['month_range']){
                $pieces = explode('-',$_REQUEST['month_range'], 3);
                $year_r = $pieces[0];
                $month_r = $pieces[1];
            }*/
			
			$curdate = date("Y-m-d");
			/*$pieces = explode('-',$curdate, 3);
			$this_year_r = $pieces[0];
			$this_month_r = $pieces[1];
			//echo $this_year_r;
			$days_this_month = date('t', strtotime($curdate));*/
            
            $sql = "SELECT e.id event_id, e.event_name, e.event_identifier, e.reg_limit, e.registration_start, ";
            $sql .= " e.start_date, e.is_active, e.recurrence_id, e.registration_startT FROM ". EVENTS_DETAIL_TABLE ." e ";
            
           if ($total_events_today >0){
                $sql .= " WHERE start_date = '" . $curdate ."' ";
            }else{
				$sql .= " WHERE ADDDATE('".date ( 'Y-m-d' )."', INTERVAL 30 DAY) >= start_date AND start_date >= '".date('Y-m-d', strtotime($curdate))."' ";
			}
				//$sql .= " WHERE event_status != 'D' AND start_date BETWEEN '".date('Y-m-d', strtotime($this_year_r. '-' .$this_month_r . '-01'))."' AND '".date('Y-m-d', strtotime($this_year_r . '-' .$this_month_r. '-' . $days_this_month))."' ";
			
            
            $sql .= " GROUP BY e.id  ORDER BY start_date  ASC ";
            
            //echo $sql;
            $results = $wpdb->get_results($sql);
    
                foreach ($results as $result){
                    $event_id= $result->event_id;
                    $event_name=stripslashes_deep($result->event_name);
                    $event_identifier=stripslashes_deep($result->event_identifier);
                    $reg_limit = $result->reg_limit;
                    $registration_start = $result->registration_start;
                    $start_date = $result->start_date;
                    $end_date = $result->end_date;
                    $is_active= $result->is_active;
                    $status = array();
                    $status = event_espresso_get_is_active($event_id);
                    $recurrence_id = $result->recurrence_id;
                    $registration_startT = $result->registration_startT;
    ?>
            <tr>
              <td class="post-title page-title column-title"><strong><a class="row-title" href="admin.php?page=events&action=edit&event_id=<?php echo $event_id?>"><?php echo $event_name?></a> <?php echo ($recurrence_id >0) ? $recurrence_icon :'' ; ?> </strong>
                <div class="row-actions"><span><a href="#">View</a> | </span><span class='edit'><a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id?>"><?php _e('Edit', 'event_espresso'); ?></a> | </span><span class='delete'><a onclick="return confirmDelete();" href='admin.php?page=events&amp;action=delete&amp;event_id=<?php echo $event_id?>'><?php _e('Delete', 'event_espresso'); ?></a></span> | <span><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>"><?php _e('Attendees', 'event_espresso'); ?></a> | </span><span><a href="#"><?php _e('Export', 'event_espresso'); ?></a></span></div></td>
               <td class="author column-author"><?php echo event_date_display($start_date,get_option('date_format'))?> <br />
<?php echo event_espresso_get_time($event_id, 'start_time') ?></td>
              <td class="date column-date"><?php echo $status['display'] ?></td>
              <td align="center" class="author column-attendees"><a href="admin.php?page=events&amp;event_admin_reports=list_attendee_payments&amp;event_id=<?php echo $event_id?>"><?php echo get_number_of_attendees_reg_limit($event_id, 'num_attendees');?></a></td>              
              <td class="date column-date">
                
                <a href="<?php echo get_option('siteurl')?>/?page_id=<?php echo $org_options['event_page_id']?>&regevent_action=register&event_id=<?php echo $event_id?>&name_of_event=<?php echo $event_name?>" title="<?php _e('View Event','event_espresso'); ?>" target="_blank"><div class="view_btn"></div></a>
                
                <a href="admin.php?page=events&amp;action=edit&amp;event_id=<?php echo $event_id?>" title="<?php _e('Edit Event','event_espresso'); ?>"><div class="edit_btn"></div></a>
                
                <a href="admin.php?page=events&amp;event_id=<?php echo $event_id?>&amp;event_admin_reports=list_attendee_payments" title="<?php _e('View Attendees','event_espresso'); ?>"><div class="complete_btn"></div></a>
    
                <a class="ev_reg-fancylink" href="#unique_id_info_<?php echo $event_id?>" title="<?php _e('View Shortcode','event_espresso'); ?>"><div class="shortcode_btn"></div></a>
                
                <a href="#" onclick="window.location='<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?event_espresso&amp;id=".$event_id."&amp;export=report&action=payment&amp;type=excel";?>'" title="<?php _e('Export to Excel','event_espresso'); ?>"><div class="excel_exp_btn"></div></a>
                
                
               
              <div id="unique_id_info_<?php echo $event_id?>" style="display:none">
    <?php _e('<h2>Shortcode</h2>
      <p>This will show the registration form for this event jsut about anywhere. Just copy and paste the following shortcode into any page or post.</p>
       <p><span  class="updated fade">[SINGLEEVENT single_event_id="' .  $event_identifier . '"]</span></p> <p class="red_text"> Do not use in place of the main events page that is set in the Organization Settings page.','event_espresso'); ?>
    </div></td>
      </tr>
    <?php } 
        }else { ?>
    <tr>
    <td><?php _e('No Record Found!','event_espresso'); ?></td>
    </tr>
    <?php    }?>
        
      </tbody>
</table>
<script>
    
    jQuery(document).ready(function($) {                        
    
    var mytable = $('#table').dataTable( {
            "bStateSave": true,
            /*"sDom": '<"search_filter"f>',*/
            "oLanguage": { "sSearch": "<strong>Live Search Filter:</strong>" }
    
    } );
    
    } );
    </script>
    <div style="clear:both"></div>
</div>
    
    
    
    <?php
}


// Init WP Event Dashboard Widget
add_action('wp_dashboard_setup', 'event_espresso_dashboard_widget');

function event_espresso_dashboard_widget() {
global $wp_meta_boxes;

wp_add_dashboard_widget('todays_events_widget', __('Upcoming Events', 'event_espresso'), 'custom_dashboard_events');
}

function custom_dashboard_events() {
    echo '<p>A quick overview of upcoming events.  For a complete list of events visit the <a href="admin.php?page=events">Events Overview</a> page.</p>';
    event_espresso_edit_list_widget();
}



/*$event_espresso_dashboard_stats = get_option('event_espresso_dashboard_stats');
$event_espresso_dashboard_stats = 'abox';
switch(strtolower($event_espresso_dashboard_stats)) {
        case 'abox':
            // activity box
            add_action('activity_box_end', 'event_espresso_admin_latest_activity');            
        break;        
        case 'widget':
            // separate widget  
			add_action('wp_dashboard_setup', 'event_espresso_register_dashboard_widget');
			add_filter('wp_dashboard_widgets', 'event_espresso_add_dashboard_widget');
        break;        
    }

### Function: Register Dashboard Widget

function event_espresso_register_dashboard_widget() {
    //global $event_espresso_full_plugin_name;    
    wp_register_sidebar_widget('dashboard_event_espresso', __('Event Registration with PayPal IPN', 'Event Registration with PayPal IPN'), 'dashboard_event_espresso',    
        array(
        'width' => 'half', // OR 'fourth', 'third', 'half', 'full' (Default: 'half')
        'height' => 'single', // OR 'single', 'double' (Default: 'single')
        )
    );
}

### Function: Add Dashboard Widget
function event_espresso_add_dashboard_widget($widgets) {
    global $wp_registered_widgets;
    if (!isset($wp_registered_widgets['dashboard_event_espresso'])) {
        return $widgets;
    }
	$w1 = array_slice($widgets,0,1);
	$w2 = array_slice($widgets,1);
	return array_merge($w1,array('dashboard_event_espresso'),$w2);
    
    //$widgets[] = array('dashboard_event_espresso');
    
    //return $widgets;
}


function event_espresso_get_stats($opt){
    global $wpdb;
    switch($opt){
        case 'total_attendees':
            $sql = "SELECT count(id) FROM ".EVENTS_ATTENDEE_TABLE;
        break;               
        case 'total_events':
            $sql = "SELECT count(id) FROM ".EVENTS_DETAIL_TABLE ." WHERE is_active = 'Y' AND event_status != 'D' AND start_date >= '".date ( 'Y-m-d' )."'";        
        break;   
		case 'total_sales':
            $sql = "SELECT sum(amount_pd) FROM ".EVENTS_ATTENDEE_TABLE." WHERE payment_status = 'Completed' AND amount_pd != '0.00'";        
        break;
		case 'total_paid':
            $sql = "SELECT count(id) FROM ".EVENTS_ATTENDEE_TABLE." WHERE payment_status = 'Completed' AND amount_pd != '0.00'";        
        break;
		case 'total_not_paid':
            $sql = "SELECT count(id) FROM ".EVENTS_ATTENDEE_TABLE." WHERE payment_status != 'Completed'";        
        break;
    }
    $res = $wpdb->get_var($wpdb->prepare($sql));
    
    return $res;
}

### Function: Print Dashboard Widget
function dashboard_event_espresso($sidebar_args) {
    global $wpdb, $org_options;    
    if (is_array($sidebar_args)){
        extract($sidebar_args, EXTR_SKIP);
    }
    echo $before_widget;
    echo $before_title;
    echo $widget_name;
    echo $after_title;
    
        //global $event_espresso_plugindir;
        echo '<div>';
        echo '<div class="youhave" style="clear:both; overflow:hidden;">';        
        echo '<p class="event-regis_sub" style="margin: 0; float:left;">'.__('Events stats','event_espresso').'</p>';
        echo '<a style="line-height:140%; float:right" href="admin.php?page=events">'.__('View Attendees/Payments','event_espresso').'</a>';
        echo '</div>';        
        
        //$admin_current_event = event_espresso_get_current_event('current_event');
        $admin_total_events = event_espresso_get_stats('total_events');
        $admin_total_attendees = event_espresso_get_stats('total_attendees');        
		$admin_total_event_sales = event_espresso_get_stats('total_sales');
		$admin_total_total_paid = event_espresso_get_stats('total_paid');
		$admin_total_total_not_paid = event_espresso_get_stats('total_not_paid');
        
?>
        <div style="overflow-y: auto;">     
        <table style="width:auto;" class="event-regis-dboard-summary">
            <thead>
                <tr>
                    <th><?php _e('Total Active Events','event_espresso'); ?></th>
                    <th><?php _e('All Time Attendees','event_espresso'); ?></th>
                    <th><?php _e('All Time Paid','event_espresso'); ?></th>
                    <th><?php _e('All Time Not Paid','event_espresso'); ?></th>
					<th><?php _e('All Time Payments','event_espresso'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $admin_total_events?></td>
                    <td><?php echo $admin_total_attendees?></td>
                    <td style="color: orange;"><?php echo $admin_total_total_paid?></td>
                    <td style="color: red;"><?php echo $admin_total_total_not_paid?></td>
                    <td style="color: green;"><?php echo $org_options['currency_symbol'].$admin_total_event_sales?></td>
                </tr>
                
            </tbody>
</table></div></div>
		<?php

    echo $after_widget;
}

//Add the Event Registration stats to the admin dashboard under the latest activity box
function event_espresso_admin_latest_activity() {
	global $wpdb, $org_options;
        echo '<div style="margin-top:10px;">';
        echo '<div class="youhave" style="clear:both; overflow:hidden;">';        
        echo '<p class="event-regis_sub" style="margin: 0; float:left; font-weight: bold;">'.__('Event Registration with PayPal IPN','event_espresso').'</p>';
        echo '<a style="line-height:140%; float:right" href="admin.php?page=events">'.__('View Attendees/Payments','event_espresso').'</a>';
        echo '</div>';        
        
         //$admin_current_event = event_espresso_get_current_event('current_event');
        $admin_total_events = event_espresso_get_stats('total_events');
        $admin_total_attendees = event_espresso_get_stats('total_attendees');
		$admin_total_event_sales = event_espresso_get_stats('total_sales');
		$admin_total_total_paid = event_espresso_get_stats('total_paid');
		$admin_total_total_not_paid = event_espresso_get_stats('total_not_paid');        
?>
        <div style="overflow-y: auto;">     
        <table style="width:auto;" class="event-regis-dboard-summary">
            <thead>
                <tr>
                     <th><?php _e('Total Active Events','event_espresso'); ?></th>
                    <th><?php _e('All Time Attendees','event_espresso'); ?></th>
                    <th><?php _e('All Time Paid','event_espresso'); ?></th>
                    <th><?php _e('All Time Not Paid','event_espresso'); ?></th>
					<th><?php _e('All Time Payments','event_espresso'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                     <td><?php echo $admin_total_events?></td>
                    <td><?php echo $admin_total_attendees?></td>
                    <td style="color: orange;"><?php echo $admin_total_total_paid?></td>
                    <td style="color: red;"><?php echo $admin_total_total_not_paid?></td>
                    <td style="color: green;"><?php echo $org_options['currency_symbol'].$admin_total_event_sales?></td>
                </tr>
             </tbody>
             </table>   
           <table style="width:99%;" class="event-regis-dboard-summary">
            <thead>
                <tr  style="text-align:left">
                     <th><?php _e('Next 3 Upcoming Events','event_espresso'); ?></th>
                     </tr>
                     </thead>
                     <tbody>
               
<?php 
		$results = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE is_active = 'Y' AND start_date >= '" . date ( 'Y-m-d' ) . "' AND event_status != 'D' ORDER BY date(start_date) LIMIT 3");
		foreach ($results as $result){
			$event_id=$result->id;
			$event_name=$result->event_name;   
			$reg_limit = $result->reg_limit;
			$start_date =$result->start_date;
?>
                
                <tr>
                	<td style="text-align:left; padding:2px">
                    <a title="View event" href="admin.php?page=events#event-id-<?php echo $event_id?>"><?php echo stripslashes($event_name)?></a> | <?php _e('Start Date:','event_espresso'); ?> <?php echo event_date_display($start_date)?> <?php echo $start_time?> | <a href="admin.php?page=events&event_id=<?php echo $event_id?>&event_admin_reports=list_attendee_payments"><?php _e('Attendees:','event_espresso'); ?></a> <?php echo get_number_of_attendees_reg_limit($event_id)?> <?php echo $active_event?>
                    </td>
                </tr>
                <?php
			  }
			   ?>
            </tbody>
        </table></div></div>
		<?php

  }*/
