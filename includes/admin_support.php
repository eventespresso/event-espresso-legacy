<?php 
function event_espresso_support(){
?>
<div id="event_reg_theme" class="wrap">
<div id="icon-options-event" class="icon32"></div><h2>Help and Support</h2>
<?php if ($_REQUEST['action'] == "update_event_dates"){update_event_data();}?>
<?php if ($_REQUEST['action'] == "event_espresso_update_attendee_data"){event_espresso_update_attendee_data();}?>
<div id="event_espresso-col-left">
<ul id="event_espresso-sortables">
<li>
<div class="box-mid-head">
<h2>Contact Support</h2>
</div>
<div class="box-mid-body" id="toggle1">
					<div class="padding">
<p>If you are having any problems that are not discussed here, suggestions, comments or gripes please visit the <a href="http://eventespresso.com/forums/">Event Espresso forums</a> or feel free to send us an <a href="http://eventespresso.com/contact/">email</a>.</p>
		  </div>
</div></li>
<li>
<div class="box-mid-head">
<h2>Settings</h2>
</div>
<div class="box-mid-body" id="toggle3">
					<div class="padding">
<p>To use, create a new page with only  [ESPRESSO_EVENTS]</p>
<p><span class="red_text">*</span>For URL link back to the payment/thank you page use  [ESPRESSO_PAYMENTS] on a new page.</p>
<p><span class="red_text">*</span>For PayPal to notify about payment confirmation use  [ESPRESSO_TXN_PAGE] on a new page.</p>
<p>To display a single event on a page use the [SINGLEEVENT single_event_id=&quot;Unique Event ID&quot;]</p>
<p>To display list of attendees of an active event use [LISTATTENDEES] on a page or post.</p>
<p>To display a list of events in sidebar, use the Event Registration Widget. If your theme doesn't use widgets, you can use  &lt;?php display_all_events(); ?&gt; in theme code.</p>
<p><span class="red_text">*</span>This page should be hidden from from your navigation menu. Exclude pages by using the &lsquo;Exclude Pages&rsquo; plugin from <a href="http://wordpress.org/extend/plugins/exclude-pages/" target="_blank">http://wordpress.org/extend/plugins/exclude-pages/</a> or using the &lsquo;exclude&rsquo; parameter in your &lsquo;wp_list_pages&rsquo; template tag. Please refer to <a href="http://codex.wordpress.org/Template_Tags/wp_list_pages" target="_blank">http://codex.wordpress.org/Template_Tags/wp_list_pages</a> for more inforamation about excluding pages.</p>
<p> Email Confirmations<br />
  For customized confirmation emails, the following tags can be placed in the email form and they will pull data from the database to include in the email.</p>
<p>[fname], [lname], [phone], [event],[description], [cost], [company], [co_add1], [co_add2], [co_city],[co_state], [co_zip],[contact], [payment_url], [start_date], [start_time], [end_date], [end_time], [location], [location_phone], [google_map_link]

</p>
<h3>Sample Mail Send </h3>
<p>***This is an automated response - Do Not Reply***</p>
<p>Thank you [fname] [lname] for registering for [event].  We hope that you will find this event both informative and enjoyable.  Should have any questions, please contact [contact].</p>
<p>If you have not done so already, please submit your payment in the amount of [cost].</p>
<p>Click here to review your payment information [payment_url].</p>
<p>Thank You.</p>
</div>

</div>
</li>
<li>
<div class="box-mid-head">
<h2>Trouble Shooting and Frequently Asked Questions</h2>
</div>
<div class="box-mid-body" id="toggle3">
					<div class="padding">
<p><strong>Registration Page Just Refreshes</strong><br />
Usually its because you need to point the &quot;Main registration page:&quot; (in the Organization Settings page) to whatever page you have the shortcode [ESPRESSO_EVENTS] on.</p>
<p><strong>Problems After Upgrading</strong><br />
  If you have just upgraded  from  the free version of this plugin, your event dates, times, and categories may be out of order, missing, showing an error, or are wrong.  Pressing the &quot;Run Upgrade Script&quot; button below should fix all of these problems.</p>
<form action="<?php echo $_SERVER["REQUEST_URI"]?>" method="post" name="form" id="form">
  <p>
    <input type="hidden" name="action" value="update_event_dates" />
    <input class="button-primary" type="submit" name="update_event_dates_button" value="<?php _e('Run Upgrade Script','event_espresso'); ?>" id="update_event_dates"/>
  </p>

</form> 
 <p>If you have clicked the button above and event dates that should be expired, are showing an error or seem to be out of order. Go into the &quot;<a href="admin.php?page=events">Event Management</a>&quot; page and click the &quot;Edit this Event&quot; button then click the &quot;Update Event&quot; button on each event that is displaying the wrong date(s). This process should fix the problem. If it doesn't then send a support request using the help and support button above.</p>
<?php
if (event_espresso_verify_attendee_data() == true){
?>
<a name="attendee_data" id="attendee_data"></a>
<p class="red_text"><strong>Attendee Information is Outdated</strong></p>
     <p>Due to recent changes in the way attendee information is handled, attendee data may appear to be missing from some events. In order to reassign attendees to events, please run the attendee update script by pressing the button below.</p>
     <form action="<?php echo $_SERVER["REQUEST_URI"]?>" method="post" name="form" id="form">
      <p>
        <input type="hidden" name="action" value="event_espresso_update_attendee_data" />
        <input class="button-primary" type="submit" name="event_espresso_update_attendee_data_button" value="<?php _e('Run Attendee Update Script','event_espresso'); ?>" id="event_espresso_update_attendee_data_button"/>
      </p>
    
    </form>
<?php
}
?>
 </div>
 </div>
 </li>
 </ul>
 </div>
 </div>
<?php
event_espresso_display_right_column ();
}
function event_espresso_update_attendee_data(){
	global $wpdb;
	$wpdb->show_errors();
	
	$sql = "SELECT id, date, fname, email, event_id FROM ". EVENTS_ATTENDEE_TABLE . " WHERE  registration_id IS NULL OR registration_id = '' ";
	$attendees = $wpdb->get_results($sql);
	
	foreach ($attendees as $attendee){
		
		/**********************************
		*******	Update single registrations
		***********************************/
		$registration_id = uniqid('', true);
		
		$update_attendee = "UPDATE ". EVENTS_ATTENDEE_TABLE . " SET registration_id = '" . $registration_id . "' WHERE id = '" . $attendee->id . "'";
		
		if (!$wpdb->query($update_attendee)){
			$error = true;
			return $wpdb->print_error();
		}/*else{
			echo __('Updating Inividual<br />', 'event_espresso'). 'ID: ' . $attendee->id .  ' - ' . $attendee->fname . ' ' . $attendee->email .' Registration ID - '. $registration_id .'<br />';
		}*/
		
		/**********************************
		*******	Update group registrations
		***********************************/
		$groups_sql = "SELECT id, date, fname, email, event_id FROM ". EVENTS_ATTENDEE_TABLE . " WHERE date = '" . $attendee->date . "' AND event_id = '" . $attendee->event_id . "'";
		$groups = $wpdb->get_results($groups_sql);
		$group_registration_id = uniqid('', true);
		if ($wpdb->num_rows >1){
			foreach ($groups as $group_attendee){
				$update_attendee_group = "UPDATE ". EVENTS_ATTENDEE_TABLE . " SET registration_id = '" . $group_registration_id . "' WHERE id = '" . $group_attendee->id . "'";
				if (!$wpdb->query($update_attendee_group)){
					$error = true;
					return $wpdb->print_error();
				}/*else{
					echo __('Adding to Group Registration<br />', 'event_espresso'). 'ID: ' . $group_attendee->id .  ' - ' . $group_attendee->fname . ' ' . $group_attendee->email . 'Registration ID - ' . $group_registration_id . '<br />';
				}*/
			}
		}
		
	}
	
	if ($error != true){?>
		<div id="message" class="updated fade"><p><strong><?php _e('Attendee data has been updated!','event_espresso'); ?></strong></p></div>
<?php 
	}else { ?>
		<div id="message" class="error"><p><strong><?php _e('There was an error in your submission, please try again.','event_espresso'); ?> <?php $wpdb->print_error(); ?>.</strong></p></div>
<?php
	}
}

function update_event_data(){	
	global $wpdb;
	$wpdb->show_errors();
	$event_dates = $wpdb->get_results("SELECT * FROM ". EVENTS_DETAIL_TABLE . " WHERE start_date = '' OR start_date LIKE '%--%' OR end_date = '' OR end_date LIKE '%--%'");
	foreach ($event_dates as $event_date){	
		$event_id = $event_date->id;
		$start_month=$event_date->start_month;
		$start_day=$event_date->start_day;
		$start_year=$event_date->start_year;
		$end_month=$event_date->end_month;
		$end_day=$event_date->end_day;
		$end_year=$event_date->end_year;
		
		if ($start_month == "Jan" || $start_month == "January"){$month_no = '01';}
		if ($start_month == "Feb" || $start_month == "February"){$month_no = '02';}
		if ($start_month == "Mar" || $start_month == "March"){$month_no = '03';}
		if ($start_month == "Apr" || $start_month == "April"){$month_no = '04';}
		if ($start_month == "May" || $start_month == "May"){$month_no = '05';}
		if ($start_month == "Jun" || $start_month == "June"){$month_no = '06';}
		if ($start_month == "Jul" || $start_month == "July"){$month_no = '07';}
		if ($start_month == "Aug" || $start_month == "August"){$month_no = '08';}
		if ($start_month == "Sep" || $start_month == "September"){$month_no = '09';}
		if ($start_month == "Oct" || $start_month == "October"){$month_no = '10';}
		if ($start_month == "Nov" || $start_month == "November"){$month_no = '11';}
		if ($start_month == "Dec" || $start_month == "December"){$month_no = '12';}
		$start_date = $start_year."-".$month_no."-".$start_day;
		
		if ($end_month == "Jan" || $end_month == "January"){$end_month_no = '01';}
		if ($end_month == "Feb" || $end_month == "February"){$end_month_no = '02';}
		if ($end_month == "Mar" || $end_month == "March"){$end_month_no = '03';}
		if ($end_month == "Apr" || $end_month == "April"){$end_month_no = '04';}
		if ($end_month == "May" || $end_month == "May"){$end_month_no = '05';}
		if ($end_month == "Jun" || $end_month == "June"){$end_month_no = '06';}
		if ($end_month == "Jul" || $end_month == "July"){$end_month_no = '07';}
		if ($end_month == "Aug" || $end_month == "August"){$end_month_no = '08';}
		if ($end_month == "Sep" || $end_month == "September"){$end_month_no = '09';}
		if ($end_month == "Oct" || $end_month == "October"){$end_month_no = '10';}
		if ($end_month == "Nov" || $end_month == "November"){$end_month_no = '11';}
		if ($end_month == "Dec" || $end_month == "December"){$end_month_no = '12';}
		$end_date = $end_year."-".$end_month_no."-".$end_day;
			
		$sql_dates = "UPDATE ". EVENTS_DETAIL_TABLE . " SET start_date='" . $start_date . "', end_date='" . $end_date . "' WHERE id = '" . $event_id . "'";
		if (!$wpdb->query($sql_dates)){
			$error = true;
		}
	}
	//Change fields that have 'yes' and 'no' values to 'Y' and 'N' values		
	$events_Y = $wpdb->get_results("SELECT id FROM ". EVENTS_DETAIL_TABLE . " WHERE is_active = 'yes' OR is_active = 'Yes'");
	foreach ($events_Y as $event_Y){
		$event_id = $event_Y->id;
		$is_active = "Y";
		$update_events_Y = "UPDATE ". EVENTS_DETAIL_TABLE . " SET is_active = '" . $is_active . "' WHERE id = '" . $event_id . "'";
		if (!$wpdb->query($update_events_Y)){
			$error = true;
		}
	}
	
	$events_N = $wpdb->get_results("SELECT id FROM ". EVENTS_DETAIL_TABLE . " WHERE is_active = 'no' OR is_active = 'No'");
	foreach ($events_N as $event_N){
		$event_id = $event_N->id;
		$is_active = "N";
		$update_events_N = "UPDATE ". EVENTS_DETAIL_TABLE . " SET is_active = '" . $is_active . "' WHERE id = '" . $event_id . "'";
			if (!$wpdb->query($update_events_N)){
				$error = true;
			}
	}
	//End change fields that have 'yes' and 'no' values to 'Y' and 'N' values
	
	//This section copies the current prices, discounts, and event times from events and places them in their respective tables.
	$wpdb->get_results("SELECT id FROM " . EVENTS_DISCOUNT_CODES_TABLE);
	$disc_codes_num_rows = $wpdb->num_rows;

	$wpdb->get_results("SELECT id FROM " . EVENTS_START_END_TABLE);
	$times_num_rows = $wpdb->num_rows;
				
	$wpdb->get_results("SELECT id FROM " . EVENTS_PRICES_TABLE);
	$prices_num_rows = $wpdb->num_rows;
	
	$events = $wpdb->get_results("SELECT * FROM " . EVENTS_DETAIL_TABLE);
	foreach ($events as $event){
		$event_id = $event->id;
		$end_time = $event->end_time;
		$start_time = $event->start_time;
		$coupon_code = $event->coupon_code;
		$coupon_code_price = $event->coupon_code_price;
		$use_percentage = $event->use_percentage;
		$event_cost = $event->event_cost;
		if ($times_num_rows == 0){
			$sql_times="INSERT INTO " . EVENTS_START_END_TABLE . " (event_id, start_time, end_time) VALUES ('" . $event_id . "', '" . $start_time . "', '" . $end_time . "')";
			//echo "$sql_times <br>";
			if (!$wpdb->query($sql_times)){
				$error = true;
			}
		}
			
		if ($disc_codes_num_rows == 0){
			if ($coupon_code != ''){
				$sql_disc = "INSERT INTO " . EVENTS_DISCOUNT_CODES_TABLE . " (coupon_code, coupon_code_price, use_percentage) VALUES ('" . $coupon_code . "', '" . $coupon_code_price . "', '" . $use_percentage . "')";
				echo "$sql_disc <br>";
				if (!$wpdb->query($sql_disc)){
					$error = true;
				}
				
				//Copy the discount codes to the relationship tables
				$discount_id = $wpdb->insert_id;
				$sql_disc_rel = "INSERT INTO " . EVENTS_DISCOUNT_REL_TABLE . " (event_id, discount_id) VALUES ('" . $event_id . "', '" . $discount_id . "')";
				echo "$sql_disc_rel <br>";
				if (!$wpdb->query($sql_disc_rel)){
					$error = true;
				}
			}
		}
					
		if ($prices_num_rows == 0){
			if ($event_cost != ''){
				$sql_price="INSERT INTO " . EVENTS_PRICES_TABLE . " (event_id, event_cost) VALUES ('" . $event_id . "', '" . $event_cost . "')";
				//echo "$sql_price <br>";
				if (!$wpdb->query($sql_price)){
					$error = true;
				}
			}
		}
	}  
	  
	if ($error != true){?>
		<div id="message" class="updated fade"><p><strong><?php _e('Event data has been updated!','event_espresso'); ?></strong></p></div>
<?php 
	}else { ?>
		<div id="message" class="error"><p><strong><?php _e('There was an error in your submission, please try again.','event_espresso'); ?> <?php $wpdb->print_error(); ?>.</strong></p></div>
<?php
	}
}