<?php
/**
 * 	espresso_get_events_page_list_table_results
 *
 * @access public
 * @param boolean $count whether this query will return field data or COUNT events or SUM attendee quantity
 * @param boolean $attendees whether this query will return data from the events table or the attendee table
 * @param string $filters whether a particular filter is active or not; mostly used in conjunction with the $count parameter ie: COUNT events this_month
 * @param mixed boolean | array $group_admin_locales array og R&P member group IDs or FALSE if not used
 * @return string
 */
function espresso_generate_events_page_list_table_sql( $count = FALSE, $attendees= FALSE, $filters = '', $group_admin_locales = FALSE ) {
	global $org_options, $espresso_premium, $ticketing_installed, $wpdb;
	
	if ( ! $group_admin_locales ) {
		$member_id = FALSE;
	}

	$max_rows = isset($_REQUEST['max_rows']) & !empty($_REQUEST['max_rows']) ? absint($_REQUEST['max_rows']) : 50;
	$max_rows = min( $max_rows, 100000 );
	$start_rec = isset($_REQUEST['start_rec']) && !empty($_REQUEST['start_rec']) ? absint($_REQUEST['start_rec']) : 0;
	$records_to_show = ' LIMIT ' . $max_rows .' OFFSET ' . $start_rec;

	//Dates
	$curdate = date('Y-m-d');
	$this_year_r = date('Y');
	$this_month_r = date('m');
	$days_this_month = date('t', strtotime($curdate));
	
	// event date filters
	$month_range = isset($_REQUEST['month_range']) && !empty($_REQUEST['month_range']) ? sanitize_text_field($_REQUEST['month_range']) : FALSE;
	$this_month_filter = isset($_REQUEST['this_month']) && $_REQUEST['this_month'] == 'true' ? TRUE : FALSE;
	$today_filter = isset($_REQUEST['today']) && $_REQUEST['today'] == 'true' ? TRUE : FALSE;
	// attendee date filters
	$this_month_filter = isset($_REQUEST['this_month_a']) && $_REQUEST['this_month_a'] == 'true' ? TRUE : $this_month_filter;
	$today_filter = isset($_REQUEST['today_a']) && $_REQUEST['today_a'] == 'true' ? TRUE : $today_filter;
	
	// toggle filters based on value passed from "count functions" (date filters)
	switch ( $filters ) {
		case 'this_month' :
				$month_range = FALSE;
				$this_month_filter = TRUE;
				$today_filter = FALSE;
			break;
		case 'today' :
				$month_range = FALSE;
				$this_month_filter = FALSE;
				$today_filter = TRUE;
			break;
		case 'none' :
				$month_range = FALSE;
				$this_month_filter = FALSE;
				$today_filter = FALSE;
			break;
	}

	$event_id = isset( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] != '' ? absint( $_REQUEST['event_id'] ) : FALSE;
	$category_id = isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']) ? absint($_REQUEST['category_id']) : FALSE;
	$event_status = isset($_REQUEST['event_status']) && !empty($_REQUEST['event_status']) ? sanitize_text_field($_REQUEST['event_status']) : FALSE;
	$payment_status = isset($_REQUEST['payment_status']) ? wp_strip_all_tags( $_REQUEST['payment_status'] ) : FALSE;

	if ( $month_range ) {
		$pieces = explode('-', $month_range, 3);
		$year_r = $pieces[0];
		$month_r = $pieces[1];
		$days_this_month = date('t', strtotime($month_range));
	}

	//Check if the venue manager is turned on
	$use_venue_manager = isset( $org_options['use_venue_manager'] ) && $org_options['use_venue_manager'] == 'Y' ? TRUE : FALSE;

	$SQL = '';
	$close_union = FALSE;
	
	//Roles & Permissions
	//This checks to see if the user is a regional manager and creates a union to join the events that are in the users region based on the venue/locale combination
	if ( ! $group_admin_locales && function_exists('espresso_member_data') && current_user_can('espresso_group_admin') && !current_user_can('administrator') ) {
		$member_id = espresso_member_data('id');
		$group_admin_locales = get_user_meta( $member_id, 'espresso_group', TRUE );
		$group_admin_locales = is_array( $group_admin_locales ) ? implode( ',', $group_admin_locales ) : FALSE;
		if ( $group_admin_locales ) {
			$SQL .= '( ';
			$SQL .= espresso_generate_events_page_list_table_sql( $count, $attendees, $filters, $group_admin_locales );
			$SQL .= ' ) UNION ( ';
			$close_union = TRUE;
		}
	}
	
	//If this is an event manager	
	$event_manager = function_exists('espresso_member_data') && ( current_user_can('espresso_event_manager') && !current_user_can('administrator') ) ? true : false;
	$event_admin = function_exists('espresso_member_data') && ( current_user_can('espresso_event_admin') ) ? true : false;	
	
	$SQL .= 'SELECT ';
	
	if ( $count && $attendees ) {
		// count attendees
		$SQL .= 'SUM(a.quantity) quantity';
	} else if ( ! $count && $attendees ) {
		// get attendees
		$SQL .= 'a.*, e.id event_id, e.event_name';
		$SQL .= $ticketing_installed ? ', ac.date_scanned, ac.checked_in ac_checked_in' : '';
	} else if ( $count && ! $attendees ) {
		// count events
		$SQL .= 'COUNT(e.id) events'; //, t.start_time
	} else if ( ! $count && ! $attendees ) {
		// get events
		$SQL .= 'e.id event_id, e.event_name, e.event_identifier, e.registration_start, e.registration_startT, e.registration_end, e.registration_endT, e.start_date, e.end_date, e.is_active, e.recurrence_id, e.wp_user, e.event_status, e.reg_limit'; //, t.start_time
	} 	
	//get the venue 
	if ( ! $count && ! $attendees && $use_venue_manager ) {
		$SQL .= ', v.name AS venue_title, v.address AS venue_address, v.address2 AS venue_address2, v.city AS venue_city, v.state AS venue_state, v.zip AS venue_zip, v.country AS venue_country';
	} else if ( ! $count && ! $attendees ) {
		$SQL .= ', e.venue_title, e.phone, e.address, e.address2, e.city, e.state, e.zip, e.country';
	}
	// get the locale for R&P
	$SQL .= ! $count && ! $attendees && $group_admin_locales && $use_venue_manager ? ', lc.name AS locale_name' : '';
	// this might be needed
	$SQL .= $attendees ? ' FROM '. EVENTS_ATTENDEE_TABLE . ' a ' : ' FROM '. EVENTS_DETAIL_TABLE . ' e ';	
	// join event table for attendee queries
	$SQL .= $attendees ? 'JOIN '. EVENTS_DETAIL_TABLE . ' e ON e.id=a.event_id ' : '';
	$SQL .= $attendees && $ticketing_installed ? 'LEFT JOIN ' . $wpdb->prefix . "events_attendee_checkin" . ' ac ON ac.attendee_id = a.id ' : '';
	// join  categories
	if ( $category_id ) {
		$SQL .= 'JOIN ' . EVENTS_CATEGORY_REL_TABLE . ' cr ON cr.event_id = e.id ';
		$SQL .= 'JOIN ' . EVENTS_CATEGORY_TABLE . ' c ON  c.id = cr.cat_id ';
	}
	// join venues
	if (  ! $count && ! $attendees && $use_venue_manager ) {
		$SQL .= 'LEFT JOIN ' . EVENTS_VENUE_REL_TABLE . ' vr ON vr.event_id = e.id ';
		$SQL .= 'LEFT JOIN ' . EVENTS_VENUE_TABLE . ' v ON v.id = vr.venue_id ';
	}
	// join locales for R&P
	if (   ! $count && ! $attendees && $use_venue_manager && $group_admin_locales  ) {
		$SQL .= 'JOIN ' . EVENTS_LOCALE_REL_TABLE . ' l ON  l.venue_id = vr.venue_id ';
		$SQL .= 'JOIN ' . EVENTS_LOCALE_TABLE . ' lc ON lc.id = l.locale_id ';
	}
	//Event status filter
	if (  ! $event_id  ) {
		if ( $event_status ) {
			switch ( $event_status ) {			
				case 'X' : // Denied
				case 'D' : // Deleted
						$SQL .= 'WHERE e.event_status = "' . $event_status . '"';
					break;
				case 'IA' : // Inactive
						$SQL .= 'WHERE ( e.is_active = "N" AND e.event_status != "D" ) OR ( e.end_date < "' . $curdate . '" AND e.event_status != "O" )';
						// and if we are NOT filtering the date in any other way, then only retrieve currently running events
						//$SQL .=  ! $month_range && ! $today_filter ? ' OR e.end_date < "' . $curdate . '" )' : ' )';
					break;
				case 'A' : // Active
						$SQL .= 'WHERE e.is_active = "Y" AND  ( e.event_status = "' . $event_status . '" OR e.event_status = "O" )';
						// and if we are NOT filtering the date in any other way, then only retrieve currently running events
						$SQL .=  ! $month_range && ! $today_filter ? ' AND ( e.end_date >= "' . $curdate . '" OR e.event_status = "O" )' : '';
					break;							
				case 'P' : // Pending
				case 'R' : // Draft
				case 'S' : // Waitlist
						$SQL .= 'WHERE e.is_active = "Y" AND  e.event_status = "' . $event_status . '"';
						// and if we are NOT filtering the date in any other way, then only retrieve currently running events
						$SQL .=  ! $month_range && ! $today_filter ? ' AND ( e.end_date >= "' . $curdate . '" OR e.event_status = "O" )' : '';
					break;							
				case 'O' : // Ongoing
						$SQL .= 'WHERE e.is_active = "Y" AND  e.event_status = "' . $event_status . '"';
					break;							
				case 'L' : // ALL
				default :
						$SQL .= 'WHERE e.event_status != "D"';
					break;							
			}		
		} else {
			// show ACTIVE events
			$SQL .= 'WHERE e.is_active = "Y" AND ( e.event_status = "A" OR e.event_status = "O" )';
			// and if we are NOT filtering the date in any other way, then only retrieve currently running events
			if ( $espresso_premium == TRUE ){
				$SQL .=  ! $month_range && ! $today_filter ? ' AND ( e.end_date >= "' . $curdate . '" OR e.event_status = "O" )' : '';
			}
		}
		// specific event?
		$SQL .= ! $count && $event_id ? ' AND e.id = ' . $event_id : '';
		
	} else {
		// we want a specific event and don't care about status filters
		$SQL .= 'WHERE e.id = ' . $event_id;
	}
	//Category filter
	$SQL .= $category_id ? ' AND c.id = "' . $category_id . '" ' : '';
	// for R&P : Find events in the locale
	$SQL .=! $count && ! $attendees && $use_venue_manager && $group_admin_locales ? ' AND l.locale_id IN (' . $group_admin_locales . ') ' : '';
	// Attendee Payment Status
	$SQL .= ! $count && $attendees && $payment_status ? ' AND a.payment_status = "' . $payment_status . '"' : '';
	// Filter to allow the user to excluded attendees based on payment status within the default attendee report
	if (!$count && $attendees && !$payment_status) { $SQL .=  apply_filters('espresso_attendee_report_payment_status_where', ''); }
	//Month filter
	$SQL .= $month_range && $attendees && ! $event_id ? ' AND a.date BETWEEN "' . $year_r . '-' . $month_r . '-01" AND "' . $year_r . '-' . $month_r . '-' . $days_this_month . '"' : '';
	$SQL .= $month_range && ! $attendees && ! $event_id ? ' AND e.start_date BETWEEN "' . $year_r . '-' . $month_r . '-01" AND "' . $year_r . '-' . $month_r . '-' . $days_this_month . '"' : '';
	// Today events filter 
	$SQL .= $today_filter && $attendees && ! $event_id ? " AND date BETWEEN '". $curdate . " 00:00:00' AND '". $curdate." 23:59:59' " : '';
	$SQL .= $today_filter && ! $attendees && ! $event_id ? ' AND e.start_date = "' . $curdate . '"' : '';
	//This months events filter
	$SQL .= $this_month_filter && $attendees && ! $event_id ? " AND date BETWEEN '" . $this_year_r . "-" . $this_month_r . "-01' AND '" . $this_year_r . "-" . $this_month_r . "-" . $days_this_month . "' " : '';
	$SQL .= $this_month_filter && ! $attendees && ! $event_id ? ' AND e.start_date BETWEEN "' . $this_year_r . '-' . $this_month_r . '-01" AND "' . $this_year_r . '-' . $this_month_r . '-' . $days_this_month . '"' : '';
	// for R&P : If user is an event manager, then show only their events
	$SQL .= $member_id && ! $event_manager && ! $event_admin ? ' AND e.wp_user = "' . $member_id . '"' : '';
	$SQL .= $event_manager && ! $member_id && ! $event_admin ? " AND e.wp_user = '" . espresso_member_data('id') ."'" : '';
	// group data queries by event
	$SQL .= ! $count && ! $attendees ? ' GROUP BY e.id' : '';		
	// for R&P : close the UNION
	$SQL .= $close_union ? ' )' : '';
	// order by
	$SQL .= ! $count && $attendees && ! $group_admin_locales ? ' ORDER BY date DESC, id ASC' : '';
	$SQL .= ! $count && ! $attendees && ! $group_admin_locales ? ' ORDER BY e.start_date ASC, e.event_name ASC' : '';
	// limit and offset
	$SQL .= ! $count && ! $group_admin_locales ? $records_to_show : '';
	// send 'er back
	return $SQL;

}



/**
 * 	Get total number of events
 *
 * @access public
 * @return int
 */
function espresso_total_events(){
	global $wpdb;
	$SQL = espresso_generate_events_page_list_table_sql( TRUE, FALSE, 'none' );
	$results = $wpdb->get_var( $SQL );
	return $results;
}



/**
 * 	Get total number of events this month
 *
 * @access public
 * @return int
 */

function espresso_total_events_this_month(){	
	global $wpdb;
	$SQL = espresso_generate_events_page_list_table_sql( TRUE, FALSE, 'this_month' );
	$results = $wpdb->get_var( $SQL );
	return $results;
}



/**
 * 	Get total number of events for today
 *
 * @access public
 * @return int
 */
function espresso_total_events_today(){
	global $wpdb;
	$SQL = espresso_generate_events_page_list_table_sql( TRUE, FALSE, 'today' );
	$results = $wpdb->get_var( $SQL );
	return $results;
}



/**
 * 	Get total number of attendees for all events
 *
 * @access public
 * @return int
 */
function espresso_total_all_attendees(){
		//Get number of total attendees
	global $wpdb;
	$SQL = espresso_generate_events_page_list_table_sql( TRUE, TRUE, 'none' );
	$results = $wpdb->get_var( $SQL );
	return $results;
}



/**
 * 	Get total number of attendees for this month
 *
 * @access public
 * @return int
 */
function espresso_total_attendees_this_month(){
		
	global $wpdb;
	$SQL = espresso_generate_events_page_list_table_sql( TRUE, TRUE, 'this_month' );
	$results = $wpdb->get_var( $SQL );
	return $results;
}



/**
 * 	Get total number of attendees for today
 *
 * @access public
 * @return int
 */
function espresso_total_attendees_today(){
	global $wpdb;
	$SQL = espresso_generate_events_page_list_table_sql( TRUE, TRUE, 'today' );
	$results = $wpdb->get_var( $SQL );
	return $results;
}