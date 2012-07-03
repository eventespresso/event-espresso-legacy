<?php //Admin Dashboard Widget

$event_espresso_dashboard_stats = get_option('event_espresso_dashboard_stats');
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
        echo '<a style="line-height:140%; float:right" href="admin.php?page=admin_reports">'.__('View Attendees/Payments','event_espresso').'</a>';
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
        echo '<a style="line-height:140%; float:right" href="admin.php?page=admin_reports">'.__('View Attendees/Payments','event_espresso').'</a>';
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
                    <a title="View event" href="admin.php?page=events#event-id-<?php echo $event_id?>"><?php echo stripslashes($event_name)?></a> | <?php _e('Start Date:','event_espresso'); ?> <?php echo event_date_display($start_date)?> <?php echo $start_time?> | <a href="admin.php?page=admin_reports&event_id=<?php echo $event_id?>&event_admin_reports=list_attendee_payments"><?php _e('Attendees:','event_espresso'); ?></a> <?php echo get_number_of_attendees_reg_limit($event_id)?> <?php echo $active_event?>
                    </td>
                </tr>
                <?php
			  }
			   ?>
            </tbody>
        </table></div></div>
		<?php

  }