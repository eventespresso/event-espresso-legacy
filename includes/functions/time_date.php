<?php
//Time and date functions

/*-------------------------------------------------------------
 Name:      espresso_countdown

 Purpose:   Calculates countdown times
 Receive:   $time_start, $time_end, $message
 Return:	$output_countdown
-------------------------------------------------------------*/
function espresso_countdown($time_start, $time_end, $expired_message) {
	//If the timezome is set in the wordpress database, then lets use it as the default timezone.
		if (get_option('timezone_string') != ''){
			date_default_timezone_set(get_option('timezone_string'));
		}
		
  	$present = current_time('timestamp');
 	$difference = $time_start - $present;
	$daystart = floor($present / 86400) * 86400;
	$dayend = $daystart + 86400;

  	if ($difference < 0) $difference = 0;

 	$days_left = floor($difference/60/60/24);
  	$hours_left = floor(($difference - $days_left*60*60*24)/60/60);
  	$minutes_left = floor(($difference - $days_left*60*60*24 - $hours_left*60*60)/60);

	if($minutes_left < "10") $minutes_left = "0".$minutes_left;
	if($hours_left < "10") $hours_left = "0".$hours_left;

	$output_countdown = '';
  	if ( $days_left == 0 and $hours_left == 0 and $minutes_left == 0 and $present > $time_end) {
		$output_countdown .= $message;
	} else if ( $days_left == 0 ) {
		$output_countdown .= __('in','event_espresso').' '. $hours_left .':'. $minutes_left .' '.__('hours','event_espresso').'.';
	} else if ($time_end >= $daystart and $time_start <= $dayend) {
		$output_countdown .= __('today','event_espresso').'. '.__('in','event_espresso').' '. $minutes_left .' '.__('minutes','event_espresso').'.';
	} else {
	  	$output_countdown .= __('in','event_espresso').' ';
		if($days_left == 1) {
			$output_countdown .= $days_left .' '.__('day','event_espresso').' ';
		} else {
			$output_countdown .= $days_left .' '.__('days','event_espresso').' ';
		}
		$output_countdown .= __('and','event_espresso').' '. $hours_left .':'. $minutes_left .' '.__('hours','event_espresso').'.';
	}
	return $output_countdown;
}

/*-------------------------------------------------------------
 Name:      espresso_countup

 Purpose:   Calculates the time since the event
 Receive:   $time_start, $time_end, $message
 Return:	$output_archive
-------------------------------------------------------------*/
function espresso_countup($time_start, $time_end, $expired_message) {
	//If the timezome is set in the wordpress database, then lets use it as the default timezone.
		if (get_option('timezone_string') != ''){
			date_default_timezone_set(get_option('timezone_string'));
		}
  	$present = current_time('timestamp');
  	$difference = $present - $time_start;
	$daystart = floor($present / 86400) * 86400;
	$dayend = $daystart + 86400;

  	if ($difference < 0) $difference = 0;

 	$days_ago = floor($difference/60/60/24);
  	$hours_ago = floor(($difference - $days_ago*60*60*24)/60/60);
  	$minutes_ago = floor(($difference - $days_ago*60*60*24 - $hours_ago*60*60)/60);

	if($minutes_ago < "10") $minutes_ago = "0".$minutes_ago;
	if($hours_ago < "10") $hours_ago = "0".$hours_ago;

	$output_archive = '';
  	if ( $days_ago == 0 and $hours_ago == 0 and $minutes_ago == 0 and $present > $time_end ) {
		$output_archive .= $message;
	} else if ( $days_ago == 0 ) {
		$output_archive .= $hours_ago .':'. $minutes_ago .' '.__('hours','event_espresso').' '.__('ago','event_espresso').'.';
	} else {
		if($days_ago == 1) {
			$output_archive .= $days_ago .' '.__('day','event_espresso').' ';
		} else {
			$output_archive .= $days_ago .' '.__('days','event_espresso').' ';
		}
		$output_archive .=__('and','event_espresso').' '. $hours_ago .':'. $minutes_ago .' '.__('hours','event_espresso').' '.__('ago','event_espresso').'.';
	}
	return $output_archive;
}

/*-------------------------------------------------------------
 Name:      espresso_duration

 Purpose:   Calculates the duration of the event
 Receive:   $event_start, $event_end, $allday
 Return:	$output_duration
-------------------------------------------------------------*/
function espresso_duration($event_start, $event_end, $allday) {
	//If the timezome is set in the wordpress database, then lets use it as the default timezone.
		if (get_option('timezone_string') != ''){
			date_default_timezone_set(get_option('timezone_string'));
		}

  	$difference = $event_end - $event_start;
  	if ($difference < 0) $difference = 0;

 	$days_duration = floor($difference/60/60/24);
  	$hours_duration = floor(($difference - $days_duration*60*60*24)/60/60);
  	$minutes_duration = floor(($difference - $days_duration*60*60*24 - $hours_duration*60*60)/60);

	if($minutes_duration < "10") $minutes_duration = "0".$minutes_duration;
	if($hours_duration < "10") $hours_duration = "0".$hours_duration;

	$output_duration = '';
  	if ($allday == 'Y') {
		$output_duration .= __('allday','event_espresso');
	} else if (($days_duration == 0 and $hours_duration == 0 and $minutes_duration == 0) or ($event_start == $event_end)) {
		$output_duration .= __('duration','event_espresso');
	} else if ($days_duration == 0) {
		$output_duration .= $hours_duration .':'. $minutes_duration .' '.__('hours','event_espresso').'.';
	} else if ($days_duration == 0 and $hours_duration == 0) {
		$output_duration .= $minutes_duration .' '.__('minutes','event_espresso').'.';
	} else {
		if($days_duration == 1) {
			$output_duration .= $days_duration .' '.__('day','event_espresso').' ';
		} else {
			$output_duration .= $days_duration .' '.__('days','event_espresso').' ';
		}
		$output_duration .= __('and','event_espresso').' '. $hours_duration .':'. $minutes_duration .' '.__('hours','event_espresso').'.';
	}

	return $output_duration;
}


//Creates a dropdown if multiple times are associated with an event
if (!function_exists('event_espresso_time_dropdown')) {
	function event_espresso_time_dropdown($event_id = 'NULL'){
		global $wpdb;
		$event_times = $wpdb->get_results("SELECT * FROM " . EVENTS_START_END_TABLE . " WHERE event_id='".$event_id."'");
		if ($wpdb->num_rows == 1) {
			echo '<span class="span_event_time_label">' . __('Start Time:</span> ','event_espresso') . '</span>';
			foreach ($event_times as $time){
				echo '<span class="span_event_time_value">' . $time->start_time . '</span>';
				echo '<br /><span class="span_event_time_label">' . __('End Time: ','event_espresso') . '</span>';
				echo '<span class="span_event_time_value">' . $time->end_time . '</span>';
				echo '<input type="hidden" name="start_time_id" id="start_time_id' . $time->end_time . '" value="' .$time->id .'">';
			}
		}else if ($wpdb->num_rows > 1) {
			echo '<label for="start_time_id">' . __('Choose an Event Time:','event_espresso') . '</label>';
			echo '<select name="start_time_id" id="start_time_id-' . $time->id . '">';
			foreach ($event_times as $time){
				echo '<option value="' . $time->id . '">' . $time->start_time . ' - ' . $time->end_time . '</option>';
			}
			echo '</select>';
		}//End if ($wpdb->num_rows == 1)
	}
}

//Get a single start or end time
// @params $event_id (required)
// @params $time (optional, start_time (default) | end_time) 

if (!function_exists('event_espresso_get_time')) {
	function event_espresso_get_time($event_id, $format = 'start_time'){
		global $wpdb;
		$event_times = $wpdb->get_results("SELECT ". $format . " FROM " . EVENTS_START_END_TABLE . " WHERE event_id='".$event_id."' LIMIT 0,1 ");
		if ($wpdb->num_rows > 0) {
			foreach ($event_times as $time){
				switch ($format) {
					case 'start_time' :
						return $time->start_time;
					break;
	
					case 'end_time' :
						return $time->end_time;
					break;
				}
			}
		}
	}
}


/*
* Time display function
* Shows an event time based on time_id
* @params string $time_id
* @params string $format - format for the time display
* 	start - show the event start time only
* 	end - show the event end time only
* 	default - show the event end and start time
*/
if (!function_exists('event_espresso_display_selected_time')) {
	function event_espresso_display_selected_time($time_id = 0, $format = 'NULL'){
		global $wpdb;
		$event_times = $wpdb->get_results("SELECT * FROM " . EVENTS_START_END_TABLE . " WHERE id='".$time_id."'");
		foreach ($event_times as $time){
			switch ($format) {
				case 'start' :
					echo $time->start_time;
				break;

				case 'end' :
					echo $time->end_time;
				break;

				default :
					_e('Start Time: ','event_espresso');
					echo $time->start_time;
					_e('<br />End Time: ','event_espresso');
					echo $time->end_time;
				break;
			}
		echo '<input type="hidden" name="start_time_id" id="start_time_id-' . $time->id .'" value="' . $time->id . '"><input type="hidden" name="event_time" id="event_time-' . $time->start_time . '" value="' . $time->start_time . '">';
		}
	}
}

//Checks if a date is later than another date
if (!function_exists('event_espresso_firstdate_later')) {
	function event_espresso_firstdate_later($first_date, $second_date)
	{
	  $start = strtotime($first_date);
	  $end = strtotime($second_date);
	  if ($start - $end > 0)
		return true;
	  else
	   return false;
	};
};


/*
* Date function without formatting
* Formats a date
* @params string $date
* @params string $format - format for the date
*/
if (!function_exists('event_espresso_no_format_date')) {
	function event_espresso_no_format_date($date, $format = 'M d, Y'){
		if (empty($date)){
			echo NULL;
		}else{
			$event_date_display = date_i18n($format, strtotime($date)); //Fixed for international use
		}
		return $event_date_display;
	}
}

/*
* Date formatting function
* Formats a date
* @params string $date
* @params string $format - format for the date
*/
if (!function_exists('event_date_display')) {
	function event_date_display($date, $format = 'M d, Y'){
		if (empty($date)){
			echo '<span style="color:red;">'.__('NO DATE SUPPLIED','event_espresso').'</span>';
		}else{
			$event_date_display = date_i18n($format, strtotime($date)); //Fixed for international use
		}
		return $event_date_display;
	}
}


//This function just returns an event start date
//@param the event id
if (!function_exists('event_espresso_event_start_date')) {
	function event_espresso_event_start_date($event_id){
		global $wpdb;
		$sql = "SELECT e.start_date FROM ". EVENTS_DETAIL_TABLE . " e
		WHERE e.id = '" . $event_id . "'";
		$events = $wpdb->get_results($sql);
		$start_date = $wpdb->last_result[0]->start_date;
		return $start_date;
	}
}

function event_espresso_datetime2mysqldatetime($datetime){// "25.12.2010 12:10:00" -> "2010-12-25 12:10:00"
	return date('Y-m-d H:i:s', strtotime($datetime)); 	// "25.12.2010" -> "2010-12-25 00:00:00"
}

function event_espresso_mysqldatetime2datetime($mysql_datetime){// "2010-12-25 12:10:00" -> "25.12.2010 12:10:00"
	$d = split(' ', $mysql_datetime);				// "2010-12-25" -> "25.12.2010"
	if($d && count($d)>1){
		list($year, $month, $day) = split("-", $d[0]);
		list($hour, $minute, $second) = split(":", $d[1]);
		$d = date('d.m.Y H:i:s', mktime($hour, $minute, $second, $month, $day, $year));}
	else if($d && count($d)==1){
		list($year, $month, $day) = split("-", $d[0]);
		$d = date('d.m.Y', mktime(0, 0, 0, $month, $day, $year));
	} else {
		$d = NULL;

	}
	return $d;
}

//Returns the times and dates of individual events
// @params $event_id (required)
// @params $format ( start_date_time (default) | end_date_time | start_time | end_time | start_date | end_date | start_timestamp | end_timestamp ) 
if (!function_exists('espresso_event_time')) {
	function espresso_event_time($event_id, $type, $format = 'M d, Y'){
		global $wpdb;
		
		$sql = "SELECT e.id, e.start_date start_date, e.end_date end_date, ese.start_time start_time, ese.end_time end_time ";
		$sql .= "FROM ". EVENTS_DETAIL_TABLE . " e ";
		$sql .= "LEFT JOIN " . EVENTS_START_END_TABLE . " ese ON ese.event_id = e.id ";
		$sql .= "WHERE e.id = '" . $event_id . "' LIMIT 0,1";

		$wpdb->get_results($sql);
		//event_date_display($date, $format = 'M d, Y');
		switch ($type){
			case 'start_time':
				return $wpdb->last_result[0]->start_time;
			break;
			case 'end_time':
				return $wpdb->last_result[0]->end_time;
			break;
			case 'start_date':
				return event_date_display($wpdb->last_result[0]->start_date,$format);
			break;
			case 'end_date':
				return event_date_display($wpdb->last_result[0]->end_date,$format);
			break;
			case 'start_timestamp':
				return strtotime($wpdb->last_result[0]->start_date . ' '. $wpdb->last_result[0]->start_time );
			break;
			case 'end_timestamp':
				return strtotime($wpdb->last_result[0]->end_date . ' '. $wpdb->last_result[0]->end_time );
			break;
			case 'end_date_time':
				return event_date_display($wpdb->last_result[0]->end_date,$format) . ' '. $wpdb->last_result[0]->end_time;
			break;
			case 'start_date_time':
			default:
				return event_date_display($wpdb->last_result[0]->start_date,$format) . ' '. $wpdb->last_result[0]->start_time;
			break;
			
		}
	}
}