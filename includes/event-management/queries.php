<?php
//Dates
	$curdate = date("Y-m-d");
	
	$pieces = explode('-',$curdate, 3);
	$this_year_r = $pieces[0];
	$this_month_r = $pieces[1];
	//echo $this_year_r;
	$days_this_month = date('t', strtotime($curdate));
	//echo $days_this_month;
	
	/* Events */
	//Get number of total events
	$wpdb->query("SELECT id FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D'");
	$total_events =	$wpdb->num_rows;
	
	//Get total events today
	$wpdb->query("SELECT id FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D' AND start_date = '" . $curdate . "' ");
	$total_events_today =	$wpdb->num_rows;
	
	//Get total events this month
	$wpdb->query("SELECT id FROM ". EVENTS_DETAIL_TABLE ." WHERE event_status != 'D' AND start_date BETWEEN '".date('Y-m-d', strtotime($this_year_r. '-' .$this_month_r . '-01'))."' AND '".date('Y-m-d', strtotime($this_year_r . '-' .$this_month_r. '-' . $days_this_month))."' ");
	
	$total_events_this_month =	$wpdb->num_rows;
	
	/* Attendees */
	//Get number of total attendees
	$wpdb->query("SELECT id FROM ". EVENTS_ATTENDEE_TABLE);
	$total_a =	$wpdb->num_rows;
	
	//Get total attendees today
	$wpdb->query("SELECT id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE date BETWEEN '". $curdate.' 00:00:00'."' AND '". $curdate.' 23:59:59' ."' ");
	$total_a_today = $wpdb->num_rows;
	
	//Get total attendees this month
	$wpdb->query("SELECT id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE date BETWEEN '".event_espresso_no_format_date($this_year_r. '-' .$this_month_r . '-01',$format = 'Y-m-d')."' AND '".event_espresso_no_format_date($this_year_r . '-' .$this_month_r. '-' . $days_this_month,$format = 'Y-m-d')."' ");
	$total_a_this_month =	$wpdb->num_rows;