<?php

//Function to check if an array is empty
function isEmptyArray($array) {
	$my_not_empty = create_function('$v', 'return strlen($v) > 0;');
	return (count(array_filter($array, $my_not_empty)) == 0) ? 1 : 0;
}

function espresso_edit_attendee($registration_id, $attendee_id, $event_id = 0, $type = '', $text = '') {
	global $org_options;
	$html = '';
	if ($text == '')
		$text = __('Edit Attendee', 'event_espresso');
	switch ($type) {
		case'admin':
			$html .= '<a href="' . get_admin_url() . 'admin.php?page=events&event_admin_reports=edit_attendee_record&event_id=' . $event_id . '&form_action=edit_attendee&id=' . $attendee_id . '&registration_id=' . $registration_id . '">' . $text . '</a>';
			break;
		case'attendee':
		default:
			$array = array('r_id' => $registration_id, 'id' => $attendee_id, 'event_id' => $event_id, 'edit_attendee' => 'true', 'single' => 'true');
			$url = add_query_arg($array, get_permalink($org_options['event_page_id']));
			$html .= '<a  href="' . $url . '" target="_blank" id="espresso_edit_attendee_' . $attendee_id . '" class="espresso_edit_attendee" title="' . __('Edit Attendee Details', 'event_espresso') . '">' . $text . '</a>';
			//$html .= '<a  href="' . home_url() . '?page_id=' . $org_options['event_page_id'] . '&registration_id=' . $registration_id . '&amp;id=' . $attendee_id . '&amp;regevent_action=register&form_action=edit_attendee&single=true" target="_blank" id="espresso_edit_attendee_' . $attendee_id . '" class="espresso_edit_attendee" title="' . __('Edit Attendee Details', 'event_espresso') . '">' . $text . '</a>';
			break;
	}
	return $html;
}

function espresso_reg_url($event_id = 0) {
	global $org_options;
	if ($event_id > 0) {
		//return espresso_getTinyUrl(home_url().'/?page_id='.$org_options['event_page_id'].'&regevent_action=register&event_id='.$event_id);
		$new_url = add_query_arg('ee', $event_id, get_permalink($org_options['event_page_id']));
		return $new_url;
	}/* else {
	  echo 'No event id supplied'; */
	return;
	//}
}

function espresso_getTinyUrl($url) {
	return file_get_contents("http://tinyurl.com/api-create.php?url=" . $url);
}

//Text formatting function.
//This should fix all of the formatting issues of text output from the database.
function espresso_format_content($content = '') {
	$allowed_tags = '
<!-->
<a>
<abbr>
<acronym>
<address>
<applet>
<area>
<article>
<aside>
<audio>
<b>
<base>
<basefont>
<bdi>
<bdo>
<big>
<blockquote>
<body>
<br>
<button>
<canvas>
<caption>
<center>
<cite>
<code>
<col>
<colgroup>
<command>
<datalist>
<dd>
<del>
<details>
<dfn>
<dir>
<div>
<dl>
<dt>
<em>
<embed>
<fieldset>
<figcaption>
<figure>
<font>
<footer>
<form>
<frame>
<frameset>
<head>
<header>
<hgroup>
<h1>
<h2>
<h3>
<h4>
<h5>
<h6>
<hr>
<html>
<i>
<iframe>
<img>
<input>
<ins>
<kbd>
<keygen>
<label>
<legend>
<li>
<link>
<map>
<mark>
<menu>
<meta>
<meter>
<nav>
<noframes>
<object>
<ol>
<optgroup>
<option>
<output>
<p>
<param>
<pre>
<progress>
<q>
<rp>
<rt>
<ruby>
<s>
<samp>
<section>
<select>
<small>
<source>
<span>
<strike>
<strong>
<style>
<sub>
<summary>
<sup>
<table>
<tbody>
<td>
<textarea>
<tfoot>
<th>
<thead>
<time>
<title>
<tr>
<track>
<tt>
<u>
<ul>
<var>
<video>
<wbr>';
	return strip_tags(wpautop(stripslashes_deep(html_entity_decode(do_shortcode($content), ENT_QUOTES, "UTF-8"))),$allowed_tags);
}

//This function pulls HTML entities back into HTML format first then strips it.
//Use it if you want to strip the HTML from the event_desc column in the daatabase.
//I have to store HTML as special chars in the database, because the html was breaking the sql queries.
//I tried doing add_slashes, then strip_slashes, but it kept adding to many slashes and not removing the extras. It was a nightmare so i decided to jsut make all HTML into special chars.
function event_espresso_strip_html_from_entity($html_entity) {
	$stripped_html_entity = strip_tags(html_entity_decode($html_entity));
	return $stripped_html_entity;
}

/* 	This function checks a registration id to see if their session is registered more than once, if so, it returns the session id	 */

function event_espresso_more_than_one($registration_id) {
	global $wpdb;
	$sql = "SELECT a.attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " a JOIN " . EVENTS_ATTENDEE_TABLE . " b ON b.attendee_session = a.attendee_session WHERE b.registration_id='" . $registration_id . "' GROUP BY a.id";
	$res = $wpdb->get_results($sql);
	if ($wpdb->num_rows > 1) {
		$attendee_session = $wpdb->get_var($sql . " ORDER BY a.id LIMIT 1 ");
		return $attendee_session;
	}
	return null;
}

//For testing email functions
function event_espresso_test_email($optional_message = 'None') {
	global $org_options;

	$to = $org_options['contact_email'];
	$subject = 'Event Espresso Test Message from' . $org_options['organization'];
	$message = 'Event Espresso email is working properly. Optional message: ' . $optional_message;
	$headers = 'From: ' . $org_options['contact_email'] . "\r\n" .
					'Reply-To: ' . $org_options['contact_email'] . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
	wp_mail($to, $subject, $message, $headers);
}

//This function is not currently used
function event_espresso_session_start() {
	/* if(!isset($_SESSION['event_espresso_sessionid'])){
	  $sessionid = (mt_rand(100,999).time());
	  $_SESSION['event_espresso_sessionid'] = $sessionid;
	  } */
	//print_r( $_SESSION['event_espresso_sessionid']); //See if the session already exists
}

//This function just returns the session id.
function event_espresso_session_id() {
	if (!isset($_SESSION['espresso_session']['id'])) {
		$sessionid = (mt_rand(100, 999) . time());
		$_SESSION['espresso_session']['id'] = $sessionid;
	}
	return $_SESSION['espresso_session']['id'];
}

//This function just returns the session id.
function espresso_reg_sessionid($registration_id) {
	/* if(empty($_SESSION['espresso_reg_sessionid'])){
	  $sessionid =  $registration_id;
	  //$sessionid = (mt_rand(100,999).time());
	  $_SESSION['espresso_reg_sessionid'] = $sessionid;
	  }
	  return $_SESSION['espresso_reg_sessionid']; */
}

//Function to display additional attendee fields.

if (!function_exists('event_espresso_additional_attendees')) {

	function event_espresso_additional_attendees( $event_id = 0, $additional_limit = 2, $available_spaces = 999, $label = '', $show_label = true, $event_meta = '', $qstn_class = '' ) {
		global $espresso_premium;
		$event_id = $event_id == 0 ? $_REQUEST['event_id'] : $event_id;

		if ($event_meta == 'admin') {
			$admin = true;
			$event_meta = '';
		}
		if ($event_meta == '' && ($event_id != '' || $event_id != 0)) {
			$event_meta = event_espresso_get_event_meta($event_id);
		}

		//If the additional attednee questions are empty, then default to the first question group
		if (empty($event_meta['add_attendee_question_groups']))
			$event_meta['add_attendee_question_groups'] = array(1 => 1);


		$i = 0;
		if ( (isset($event_meta['additional_attendee_reg_info']) && $event_meta['additional_attendee_reg_info'] == 1) || $espresso_premium == FALSE ) {
		
			$label = $label == '' ? __('Number of Tickets', 'event_espresso') : $label;
			$html = '<p class="espresso_additional_limit highlight-bg">';
			$html .= $show_label == true ? '<label for="num_people">' . $label . '</label>' : '';
			$html .= '<select name="num_people" id="num_people-' . $event_id . '" style="width:70px;">';
			while (($i < $additional_limit) && ($i < $available_spaces)) {
				$i++;
				$html .= '<option value="' . $i . '">' . $i . '</option>';
			}
			$html .= '</select>';
			//$html .= '<br />';
			$html .= '<input type="hidden" name="espresso_addtl_limit_dd" value="true">';
			$html .= '</p>';
			$buffer = '';
			
		} else {
		
//			while (($i < $additional_limit) && ($i < $available_spaces)) {
//				$i++;
//			}
			$i = min( $additional_limit, $available_spaces ) - 1;
			
			$html = '<div id="additional_header" class="event_form_field additional_header espresso_add_subtract_attendees">';
			// fixed for translation string, previous string untranslatable - http://events.codebasehq.com/projects/event-espresso/tickets/11
			$html .= '<a id="add-additional-attendee-0" rel="0" class="add-additional-attendee-lnk additional-attendee-lnk">' . __('Add More Attendees? (click to toggle, limit ', 'event_espresso');
			$html .= $i . ')</a>';
			$html .= '</div>';
			
			
			//ob_start();
			$attendee_form = '<div id="additional_attendee_XXXXXX" class="espresso_add_attendee">';
			$attendee_form .= '<h4 class="additional-attendee-nmbr-h4">' . __('Additional Attendee #', 'event_espresso') . 'XXXXXX</h4>';
			/*
			 * Added for seating chart addon
			 */
			if (defined('ESPRESSO_SEATING_CHART')) {
				if (seating_chart::check_event_has_seating_chart($_REQUEST['event_id']) !== false) {
					$attendee_form .= '<p>';
					$attendee_form .= '<label>' . __('Select a Seat:', 'event_espresso') . '</label>';
					$attendee_form .= '<input type="text" name="x_seat_id[XXXXXX]" value="" class="ee_s_select_seat" event_id="' . $_REQUEST['event_id'] . '" readonly="readonly" />';
					$attendee_form .= '<br/>[' . __('If you do not select a seat this attendee will not be added', 'event_espresso') . ']';
					$attendee_form .= '</p>';
				}
			}
			if ($event_meta['additional_attendee_reg_info'] == 2) {
				$attendee_form .= '<p>';
				$attendee_form .= '<label for="x_attendee_fname">' . __('First Name:', 'event_espresso') . '</label>';
				$attendee_form .= '<input type="text" name="x_attendee_fname[XXXXXX]" class="input"/>';
				$attendee_form .= '</p>';
				$attendee_form .= '<p>';
				$attendee_form .= '<label for="x_attendee_lname">' . __('Last Name:', 'event_espresso') . '</label>';
				$attendee_form .= '<input type="text" name="x_attendee_lname[XXXXXX]" class="input"/>';
				$attendee_form .= '</p>';
				$attendee_form .= '<p>';
				$attendee_form .= '<label for="x_attendee_email">' . __('Email:', 'event_espresso') . '</label>';
				$attendee_form .= '<input type="text" name="x_attendee_email[XXXXXX]" class="input"/>';
				$attendee_form .= '</p>';
			} else {
				$meta = array("x_attendee" => true);
				if(!empty($admin)) {
					$meta['admin_only'] = true;
				}
				$attendee_form .= event_espresso_add_question_groups( $event_meta['add_attendee_question_groups'], '', null, 0, $meta, $qstn_class );
			}
			$attendee_form .= '<div class="espresso_add_subtract_attendees">';

			$attendee_form .= '
			<a id="remove-additional-attendee-XXXXXX" rel="XXXXXX" class="remove-additional-attendee-lnk additional-attendee-lnk" title="' . __('Remove the above Attendee', 'event_espresso') . '">
				<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif" alt="' . __('Remove Attendee', 'event_espresso') . '" />
				' . __('Remove the above Attendee:', 'event_espresso') . '
			</a><br/>';
			
			$attendee_form .= '
			<a id="add-additional-attendee-XXXXXX" rel="XXXXXX" class="add-additional-attendee-lnk additional-attendee-lnk" title="' . __('Add an Additonal Attendee', 'event_espresso') . '">
				<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/add.png" alt="' . __('Add an Additonal Attendee', 'event_espresso') . '" />
				' . __('Add an Additonal Attendee:', 'event_espresso') . '
			</a>';


			$attendee_form .= '</div></div>';

			wp_register_script( 'espresso_add_reg_attendees', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/espresso_add_reg_attendees.js', array('jquery'), '0.1', TRUE );
			wp_enqueue_script( 'espresso_add_reg_attendees' );

			$espresso_add_reg_attendees = array( 'additional_limit' => $additional_limit, 'attendee_form' => stripslashes( $attendee_form ));
			wp_localize_script( 'espresso_add_reg_attendees', 'espresso_add_reg_attendees', $espresso_add_reg_attendees );		
		}
		return $html;
	}

}

/* Helps keep the <p> tags from wrapping around our js scripts

Add this to your themes function.php file:

if ( !is_admin() ){
	remove_filter('the_content', 'wpautop');
	remove_filter('the_content', 'wptexturize');
	add_filter('the_content', 'espresso_raw_formatter', 99);
}

 */
function espresso_raw_formatter($content) {
	$new_content = '';
	$pattern_full = '{(\[raw\].*?\[/raw\])}is';
	$pattern_contents = '{\[raw\](.*?)\[/raw\]}is';
	$pieces = preg_split($pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE);

	foreach ($pieces as $piece) {
		if (preg_match($pattern_contents, $piece, $matches)) {
			$new_content .= $matches[1];
		} else {
			$new_content .= wptexturize(wpautop($piece));
		}
	}

	return $new_content;
}

function event_espresso_get_event_meta($event_id) {
	global $wpdb;
	$event_meta = array();
	$sql = "SELECT event_meta  FROM " . EVENTS_DETAIL_TABLE . " e WHERE e.id = '" . $event_id . "' LIMIT 0,1";
	if ($wpdb->get_results($sql)) {
		$events = $wpdb->get_results($sql);
		foreach ($events as $event) {
			$event_meta = $event->event_meta;
			$event_meta = unserialize($event_meta);
		}
	}
	return $event_meta;
}

//This function returns the condition of an event
if (!function_exists('event_espresso_get_is_active')) {

	function event_espresso_get_is_active($event_id, $event_meta = '') {
		//printr( $event_meta, '$event_meta  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		global $wpdb, $org_options;
		//If the timezome is set in the wordpress database, then lets use it as the default timezone.
		if (get_option('timezone_string') != '') {
			date_default_timezone_set(get_option('timezone_string'));
		}

		if (!empty($event_meta)) {

			$is_active = $event_meta['is_active'];
			$event_status = $event_meta['event_status'];

			$start_time = $event_meta['start_time'];
			$start_date = $event_meta['start_date'];

			$registration_start = $event_meta['registration_start'];
			$registration_startT = $event_meta['registration_startT'];

			$registration_end = $event_meta['registration_end'];
			$registration_endT = $event_meta['registration_endT'];

			$registration_start = $registration_start . " " . $registration_startT;
			$registration_end = $registration_end . " " . $registration_endT;
		} else {
			$sql = "SELECT e.id, e.start_date start_date, e.is_active is_active, e.event_status event_status, e.registration_start, e.registration_startT, e.registration_end, e.registration_endT, ese.start_time start_time ";
			$sql .= "FROM " . EVENTS_DETAIL_TABLE . " e ";
			$sql .= "LEFT JOIN " . EVENTS_START_END_TABLE . " ese ON ese.event_id = e.id ";
			$sql .= "WHERE e.id = '" . $event_id . "' LIMIT 0,1";
			$events = $wpdb->get_results($sql);
			$start_date = $wpdb->last_result[0]->start_date;
			$is_active = $wpdb->last_result[0]->is_active;
			$event_status = $wpdb->last_result[0]->event_status;
			$start_time = $wpdb->last_result[0]->start_time;

			$registration_start = $wpdb->last_result[0]->registration_start . " " . $wpdb->last_result[0]->registration_startT;
			$registration_end = $wpdb->last_result[0]->registration_end . " " . $wpdb->last_result[0]->registration_endT;
		}

		//$timezone_string =  $wpdb->last_result[0]->timezone_string;
		//$t = time();
		//$today = date_at_timezone("Y-m-d H:i", $timezone_string, $t);
		//Build the timestamps
		$timestamp = strtotime($start_date . ' ' . $start_time); //Creates a timestamp from the event start date and start time
		$registration_start_timestamp = strtotime($registration_start); //Creates a timestamp from the event registration start date
		$registration_end_timestamp = strtotime($registration_end); //Creates a timestamp from the event registration start date
		//echo $timestamp;
		//echo date('Y-m-d h:i:s A', time());
		//echo time('', $timestamp);
		//echo date(time());
		//echo ' event date = '.date( $timestamp);
		//IF the event is ongoing, then display ongoing
		if ($is_active == "Y" && $event_status == "O") {
			$event_status = array('status' => 'ONGOING', 'display' => '<span style="color: #090; font-weight:bold;">' . __('ONGOING', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_ongoing">' . __('Ongoing', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//IF the event is a secondary event, show as waitlist
		elseif ($is_active == "Y" && $event_status == "S") {
			$event_status = array('status' => 'SECONDARY', 'display' => '<span style="color: #090; font-weight:bold;">' . __('WAITLIST', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_secondary">' . __('Waitlist', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//IF the event is a waitlist/secondary event, show as waitlist
		elseif ($is_active == "Y" && $event_status == "R") {
			$event_status = array('status' => 'DRAFT', 'display' => '<span style="color: #ff8400; font-weight:bold;">' . __('DRAFT', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_draft">' . __('Draft', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//IF the event is a pending event, show as pending
		elseif ($is_active == "Y" && $event_status == "P") {
			$event_status = array('status' => 'PENDING', 'display' => '<span style="color: #ff8400; font-weight:bold;">' . __('PENDING', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_pending">' . __('Pending', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//IF the event is a denied event, show as denied
		elseif ($is_active == "Y" && $event_status == "X") {
			$event_status = array('status' => 'DENIED', 'display' => '<span style="color: #F00; font-weight:bold;">' . __('DENIED', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_denied">' . __('Denied', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		/*		 * * Check registration dates ** */

		//If the registration end date is greater than the current date
		elseif ($is_active == "Y" && date($registration_end_timestamp) <= date(time()) && $event_status != "D") {
			$event_status = array('status' => 'REGISTRATION_CLOSED', 'display' => '<span style="color: #F00; font-weight:bold;">' . __('CLOSED', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_closed">' . __('Closed', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//If the registration start date is less than the current date
		elseif ($is_active == "Y" && date($registration_start_timestamp) >= date(time()) && $event_status != "D") {
			$event_status = array('status' => 'REGISTRATION_NOT_OPEN', 'display' => '<span style="color: #090; font-weight:bold;">' . __('NOT_OPEN', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_not_open">' . __('Not Open', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//If the registration start date is less than the current date
		elseif ($is_active == "Y" && date($registration_start_timestamp) <= date(time()) && $event_status != "D") {
			$event_status = array('status' => 'REGISTRATION_OPEN', 'display' => '<span style="color: #090; font-weight:bold;">' . __('OPEN', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_open">' . __('Open', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		/*		 * * End Check registration dates ** */

		//If the start date and time has passed, show as expired.
		elseif ($is_active == "Y" && date($timestamp) <= date(time()) && $event_status != "D") {
			$event_status = array('status' => 'EXPIRED', 'display' => '<span style="color: #F00; font-weight:bold;">' . __('EXPIRED', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_expired">' . __('Expired', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//If the start date and time has not passed, show as active.
		elseif ($is_active == "Y" && date($timestamp) >= date(time()) && $event_status != "D") {
			$event_status = array('status' => 'ACTIVE', 'display' => '<span style="color: #090; font-weight:bold;">' . __('ACTIVE', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_active">' . __('Active', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//IF the event is not active, show as Not Active
		elseif ($is_active == "N" && $event_status != "D") {
			$event_status = array('status' => 'NOT_ACTIVE', 'display' => '<span style="color: #F00; font-weight:bold;">' . __('NOT_ACTIVE', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_not_active">' . __('Not Active', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}

		//IF the event was deleted, show as deleted
		elseif ($event_status == "D") {
			$event_status = array('status' => 'DELETED', 'display' => '<span style="color: #000; font-weight:bold;">' . __('DELETED', 'event_espresso') . '</span>', 'display_custom' => '<span class="espresso_deleted">' . __('Deleted', 'event_espresso') . '</span>');
			//print_r( $event_status);
			return $event_status;
		}
	}

}

//This function returns the overall status of an event
if (!function_exists('event_espresso_get_status')) {

	function event_espresso_get_status($event_id, $event_meta = '') {
		//printr( $event_meta, '$event_meta  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		$event_status = event_espresso_get_is_active($event_id, $event_meta);
		switch ($event_status['status']) {
			case 'EXPIRED':
			case 'NOT_ACTIVE':
			case 'DELETED':
			case 'REGISTRATION_CLOSED':
			case 'DENIED':
				//case 'REGISTRATION_NOT_OPEN':
				return 'NOT_ACTIVE';
				break;

			case 'PENDING':
			case 'DRAFT':
				return 'PENDING';
				break;

			case 'ACTIVE':
			case 'ONGOING':
			case 'SECONDARY':
			case 'REGISTRATION_OPEN':
				return 'ACTIVE';
				break;

			default:
				break;
		}
	}

}

if (!function_exists('espresso_status_detail')) {

	function espresso_status_detail($event_id) {

	}

}
/* Formats the event address */
if (!function_exists('event_espresso_format_address')) {

	function event_espresso_format_address($event_address) {
		$event_address = str_replace(array("\r\n", "\n", "\r"), "<br>", $event_address);
		return $event_address;
	}

}

//Function for merging arrrays
function event_espresso_array_merge($array1, $array2) {
	$result = array_merge($array1, $array2);
	return $result;
}

// Append associative array elements
function event_espresso_array_push_associative(&$arr) {
	$args = func_get_args();
	foreach ($args as $arg) {
		if (is_array($arg)) {
			foreach ($arg as $key => $value) {
				$arr[$key] = $value;
			}
		} else {
			$arr[$arg] = "";
		}
	}
}

/*
 * Display the amount of attendees and/or registration limit
 * Available parameters for the get_number_of_attendees_reg_limit() function
 *  @ $event_id - required
 *  @ $type -
 * 		available_spaces = returns the number of available spaces
 * 		num_attendees = returns the number of attendees
 * 		all_attendees = returns the number of all paid attendees
 * 		reg_limit = returns the total number of spaces
 * 		num_incomplete = returns the number of incomplete (non paid) registrations
 * 		num_completed = returns the number of completed (paid) registrations
 * 		num_completed_slash_incomplete = returns the number of completed and incomplete registrations separated by a slash (eg. 3/1)
 * 		num_attendees_slash_reg_limit = returns the number of attendees and the registration limit separated by a slash (eg. 4/30)
 * 	@ $full_text - the text to display when the event is full
 */
if (!function_exists('get_number_of_attendees_reg_limit')) {

	function get_number_of_attendees_reg_limit($event_id, $type = 'NULL', $full_text = 'EVENT FULL') {
		global $wpdb;

		switch ($type) {

			case 'available_spaces' :
			case 'num_attendees' :
			case 'number_available_spaces' :
			case 'num_completed_slash_incomplete' :
			case 'num_attendees_slash_reg_limit' :
			case 'avail_spaces_slash_reg_limit' :
				$num_attendees = 0;
				$a_sql = "SELECT SUM(quantity) quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND (payment_status='Completed' OR payment_status='Pending') ";
				$wpdb->get_results($a_sql, ARRAY_A);
				if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->quantity != NULL) {
					$num_attendees = $wpdb->last_result[0]->quantity;
				}
			//break;

			case 'reg_limit' :
			case 'available_spaces' :
			case 'number_available_spaces' :
			case 'avail_spaces_slash_reg_limit' :
			case 'num_attendees_slash_reg_limit' :
				$number_available_spaces = 0;
				$sql_reg_limit = "SELECT reg_limit FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'";
				$reg_limit = $wpdb->get_var($sql_reg_limit);
				if (empty($num_attendees))
					$num_attendees = 0;
				if ($reg_limit > $num_attendees) {
					$number_available_spaces = $reg_limit - $num_attendees;
				}
			//break;

			case 'num_incomplete' :
			case 'num_completed_slash_incomplete' :
				$num_incomplete = 0;
				$a_sql = "SELECT SUM(quantity) quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND payment_status='Incomplete'";
				$wpdb->get_results($a_sql);
				if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->quantity != NULL) {
					$num_incomplete = $wpdb->last_result[0]->quantity;
				}
			//break;
		}

		switch ($type) {
			case 'number_available_spaces' :
				return $number_available_spaces;
				break;
			case 'available_spaces' :
				if ($reg_limit >= 99999) {
					$number_available_spaces = "Unlimited";
				}
				return $number_available_spaces;
				break;
			case 'num_attendees' :
				return $num_attendees;
				break;
			case 'all_attendees' :
				$a_sql = "SELECT SUM(quantity) quantity  FROM " . EVENTS_ATTENDEE_TABLE . " WHERE quantity >= 1 ";
				$attendees = $wpdb->get_results($a_sql);
				if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->quantity != NULL) {
					$num_attendees = $wpdb->last_result[0]->quantity;
				}
				return $num_attendees;
				break;
			case 'reg_limit' :
				return $reg_limit;
				break;
			case 'num_incomplete' :
				return $num_incomplete;
				break;
			case 'num_completed' :
				$num_completed = 0;
				$a_sql = "SELECT SUM(quantity) quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND (payment_status='Completed' OR payment_status='Pending')  ";
				$wpdb->get_results($a_sql);
				if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->quantity != NULL) {
					$num_completed = $wpdb->last_result[0]->quantity;
				}
				return $num_completed;
				break;
			case 'num_pending' :
				$num_pending = 0;
				$a_sql = "SELECT SUM(quantity) quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND  payment_status='Pending'";
				$wpdb->get_results($a_sql);
				if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->quantity != NULL) {
					$num_pending = $wpdb->last_result[0]->quantity;
				}
				return $num_pending;
				break;
			case 'num_declined' :
				$num_declined = 0;
				$a_sql = "SELECT SUM(quantity) quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND  payment_status='Payment Declined'";
				$wpdb->get_results($a_sql);
				if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->quantity != NULL) {
					$num_declined = $wpdb->last_result[0]->quantity;
				}
				return $num_declined;
				break;
			case 'num_completed_slash_incomplete' :
				return '<font color="green">' . $num_attendees . '</font>/<font color="red">' . $num_incomplete . '</font>';
				break;

			case 'avail_spaces_slash_reg_limit' :
				return $number_available_spaces . '/' . $reg_limit;
				break;
			case 'num_attendees_slash_reg_limit' :
			default:
				return $num_attendees . '/' . $reg_limit;
				break;
		}
	}

}

function event_espresso_update_alert($url = '') {
	return wp_remote_retrieve_body(wp_remote_get($url));
}

function espresso_registration_footer() {
	global $espresso_premium, $org_options;
	$url = (!isset($org_options['affiliate_id']) || $org_options['affiliate_id'] == '' || $org_options['affiliate_id'] == 0) ? 'http://eventespresso.com/' : 'https://www.e-junkie.com/ecom/gb.php?cl=113214&c=ib&aff=' . $org_options['affiliate_id'];
	if ($espresso_premium != true || (isset($org_options['show_reg_footer']) && $org_options['show_reg_footer'] == 'Y')) {
		return '<p style="font-size: 12px;"><a href="' . $url . '" title="Event Registration Powered by Event Espresso" target="_blank">Event Registration and Ticketing</a> Powered by <a href="' . $url . '" title="Event Espresso - Event Registration and Management System for WordPress" target="_blank">Event Espresso</a></p>';
	}
}

//Gets the current page url. Used for redirecting back to a page
function event_espresso_cur_pageURL() {
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

//This function simply returns a custom capability, nothing else. Can be used to change admin capability of the Event Manager menu without the admin losing rights to certain menus. Should be used with the custom files addon. Credit goes to Justin Tadlock (http://justintadlock.com/archives/2009/09/18/custom-capabilities-in-plugins-and-themes)
if (!function_exists('event_espresso_management_capability')) {

	function event_espresso_management_capability($default, $custom) {
		return $custom;
	}

	add_filter('event_espresso_management_capability', 'event_espresso_management_capability', 10, 3);
}

//Build the form questions. This function can be overridden using the custom files addon
if (!function_exists('event_espresso_add_question_groups')) {

	function event_espresso_add_question_groups($question_groups, $answer = '', $event_id = null, $multi_reg = 0, $meta = array(), $class = 'my_class') {
		global $wpdb;
		
		//If memebers addon is installed, check to see if we want to disable the form fields for members
		$disabled = '';
		if ( function_exists('espresso_members_installed') && espresso_members_installed() == true ) {
			$member_options = get_option('events_member_settings');
			if ( is_user_logged_in() && $member_options['autofilled_editable'] == 'N' )
			$disabled = 'disabled="disabled"';
		}
				
		$event_id = empty($_REQUEST['event_id']) ? $event_id : $_REQUEST['event_id'];
		if (count($question_groups) > 0) {
			$questions_in = '';

			$FILTER = '';
			if (isset($_REQUEST['regevent_action']))
				$FILTER = " AND q.admin_only != 'Y' ";

			//echo 'additional_attendee_reg_info = '.$meta['additional_attendee_reg_info'].'<br />';
			//Only personal inforamation for the additional attendees in each group
			if (isset($meta['additional_attendee_reg_info']) && $meta['additional_attendee_reg_info'] == '2' && isset($meta['attendee_number']) && $meta['attendee_number'] > 1)
				$FILTER .= " AND qg.system_group = 1 ";
            
			if (!is_array($question_groups) && !empty($question_groups)) {
				$question_groups = unserialize($question_groups);
			}

			//Debug
			//echo "<pre>".print_r($question_groups,true)."</pre>";

			foreach ($question_groups as $g_id) {
				$questions_in .= $g_id . ',';
			}

			$questions_in = substr($questions_in, 0, -1);
			$group_name = '';
			$counter = 0;

			$sql = "SELECT q.*, qg.group_name, qg.group_description, qg.show_group_name, qg.show_group_description, qg.group_identifier
					FROM " . EVENTS_QUESTION_TABLE . " q
					JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr ON q.id = qgr.question_id
					JOIN " . EVENTS_QST_GROUP_TABLE . " qg ON qg.id = qgr.group_id
					WHERE qgr.group_id in ( $questions_in ) $FILTER
					ORDER BY qg.group_order ASC, qg.id, q.sequence, q.id ASC";
			//echo $sql;

			$questions = $wpdb->get_results($sql);

			$num_rows = $wpdb->num_rows;
			$html = '';

			if ($num_rows > 0) {
				$questions_displayed = array();
				foreach ($questions as $question) {
					$counter++;
					if (!in_array($question->id, $questions_displayed)) {
						$questions_displayed[] = $question->id;

						//if new group, close fieldset
						$html .= ($group_name != '' && $group_name != $question->group_name) ? '</fieldset>' : '';

						if ($group_name != $question->group_name) {
							$html .= '<fieldset class="event_questions" id="' . $question->group_identifier . '">';
							$html .= $question->show_group_name != 0 ? "<h4 class=\"reg-quest-title section-title\">".stripslashes_deep($question->group_name)."</h4>" : '';
							$html .= $question->show_group_description != 0 && $question->group_description == true ? '<p class="quest-group-descript">' . stripslashes_deep($question->group_description) . '</p>' : '';
							$group_name = stripslashes_deep($question->group_name);
						}

						$html .= event_form_build($question, $answer, $event_id, $multi_reg, $meta, $class, $disabled);
					}
					$html .= $counter == $num_rows ? '</fieldset>' : '';
				}
			}//end questions display
		} else {
			$html = '';
		}
		return $html;
	}

}

//Simple function to return the meta an event, venue, staff etc.
function ee_show_meta($meta, $name) {
	if ($meta == '')
		return;
	foreach ($meta as $key => $value) {
		switch ($key) {
			case $name:
				return $value;
				break;
		}
	}
}

//This function returns an array of category data based on an event id
if (!function_exists('espresso_event_category_data')) {

	function espresso_event_category_data($event_id, $all_cats = FALSE) {
		global $wpdb;
		$sql = "SELECT c.category_identifier, c.category_name, c.category_desc, c.display_desc, c.category_meta FROM " . EVENTS_DETAIL_TABLE . " e ";
		$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
		$sql .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
		$sql .= " WHERE e.id = '" . $event_id . "' ";

		$wpdb->get_results($sql);
		$num_rows = $wpdb->num_rows;

		if ($num_rows > 0 && $all_cats = FALSE) {
			$category_data = array('category_identifier' => $wpdb->last_result[0]->category_identifier, 'category_name' => $wpdb->last_result[0]->category_name, 'category_desc' => $wpdb->last_result[0]->category_desc, 'display_desc' => $wpdb->last_result[0]->display_desc, 'category_meta' => $wpdb->last_result[0]->category_meta);
			return $category_data;
		} elseif ($num_rows > 0) {
			$category_data = array( 'category_identifier' => '', 'category_name' => '', 'category_desc' => '', 'display_desc' => '', 'category_meta' => '' );
			foreach ($wpdb->last_result as $result) {
				$category_data['category_identifier'] .= $result->category_identifier . ' ';
				$category_data['category_name'] .= $result->category_name . ' ';
				$category_data['category_desc'] .= $result->category_desc . ' ';
				$category_data['display_desc'] .= $result->display_desc . ' ';
				$category_data['category_meta'] .= $result->category_meta . ' ';
			}
			return $category_data;
		} else {
			//echo 'No Categories';
			return;
		}
	}

}

if (!function_exists('espresso_registration_id')) {

	function espresso_registration_id($attendee_id) {
		global $wpdb;
		$sql = $wpdb->get_results("SELECT registration_id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id ='" . $wpdb->escape($attendee_id) . "'");
		$num_rows = $wpdb->num_rows;

		if ($num_rows > 0) {
			return $wpdb->last_result[0]->registration_id;
		} else {
			return 0;
		}
	}

}

if (!function_exists('espresso_attendee_id')) {

	function espresso_attendee_id($registration_id) {
		global $wpdb;
		$sql = $wpdb->get_results("SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id ='" . $wpdb->escape($registration_id) . "'");
		$num_rows = $wpdb->num_rows;

		if ($num_rows > 0) {
			return $wpdb->last_result[0]->id;
		} else {
			return 0;
		}
	}

}

if (!function_exists('espresso_ticket_information')) {

	function espresso_ticket_information($atts) {
		global $wpdb;
		extract($atts);
		if (!empty($registration_id))
			$registration_id = "{$registration_id}";
		$price_option = "{$price_option}";

		$type = "{$type}";

		switch ($type) {
			case 'ticket':
				$sql = $wpdb->get_results("SELECT * FROM " . EVENTS_PRICES_TABLE . " WHERE id ='" . $price_option . "'");
				$num_rows = $wpdb->num_rows;
				if ($num_rows > 0) {
					return $wpdb->last_result[0]->price_type;
				}
				break;
		}
	}

}

//Creates a Google Map Link
if (!function_exists('espresso_google_map_link')) {

	function espresso_google_map_link($atts) {
		extract($atts);

		$address = "{$address}";
		$city = "{$city}";
		$state = "{$state}";
		$zip = "{$zip}";
		$country = "{$country}";
		$text = isset($text) ? "{$text}" : "";
		$type = isset($type) ? "{$type}" : "";
		$map_w = isset($map_w) ? "{$map_w}" : 400;
		$map_h = isset($map_h) ? "{$map_h}" : 400;
		$map_image_class = isset($map_image_class) ? "{$map_image_class}" : '';

		$gaddress = ($address != '' ? $address : '') . ($city != '' ? ',' . $city : '') . ($state != '' ? ',' . $state : '') . ($zip != '' ? ',' . $zip : '') . ($country != '' ? ',' . $country : '');

		$google_map = htmlentities2('http://maps.google.com/maps?q=' . urlencode($gaddress));

		switch ($type) {
			case 'text':
			default:
				$text = $text == '' ? __('Map and Directions', 'event_espresso') : $text;
				break;

			case 'url':
				$text = $google_map;
				break;

			case 'map':
				$google_map_link = '<a href="' . $google_map . '" target="_blank">' . '<img id="venue_map_' . $id . '" ' . $map_image_class . ' src="' . htmlentities2('http://maps.googleapis.com/maps/api/staticmap?center=' . urlencode($gaddress) . '&amp;zoom=14&amp;size=' . $map_w . 'x' . $map_h . '&amp;markers=color:green|label:|' . urlencode($gaddress) . '&amp;sensor=false') . '" /></a>';
				return $google_map_link;
		}

		$google_map_link = '<a href="' . $google_map . '" target="_blank">' . $text . '</a>';
		return $google_map_link;
	}

}

//Returns a string of keys and values
if (!function_exists("unkeyvaluepair")) {

	function unkeyvaluepair($string) {
		$array = array();
		$pairs = explode("&", $string);
		foreach ($pairs as $pair) {
			list($key, $value) = explode("=", $pair, 2);
			$array[$key] = urldecode($value);
		}
		return $array;
	}

}

function espresso_add_query_vars($aVars) {
	$aVars[] = "searchdate"; // represents the name of the date as shown in the URL
	return $aVars;
}

// hook add_query_vars function into query_vars
//add_filter('query_vars', 'espresso_add_query_vars');

function espresso_serialize($data) {


	if (!is_serialized($data)) {
		return maybe_serialize($data);
	}

	return $data;
}

function espresso_unserialize($data, $return_format = '') {


	if (is_serialized($data)) {
		return maybe_unserialize($data);
	}

	return $data;
}

//Checks to see if the array is multidimensional
function is_multi($array) {
	return (count($array) != count($array, 1));
}

//escape the commas in csv file export
function escape_csv_val($val) {
	return "\"" . eregi_replace("\"", "\"\"", $val) . "\"";
}

//return field(s) from a table
function get_event_field($field, $table, $where) {
	global $wpdb;

	$r = $wpdb->get_row('SELECT ' . $field . ' FROM ' . $table . $where, ARRAY_A);

	return $r[$field];
}

/*
  Shows the personnel that are assigned to an event

  Example usage in a template file
  espresso_show_personnel($event_id , array('wrapper_start'=>'<ul style="event_staff">','wrapper_end'=>'</ul>','before'=>'<li>','after'=>'</li>', 'limit'=>1,'show_info'=>true) );

  Parameters:
  event_id - id of event
  wrapper_start - adds html to the beginning of the output block
  wrapper_end - adds html the end of the output block
  before - adds html to the beginning of each persons details
  after - adds html to the end of each persons details
  staff_id - show a single person by id (useful for showing people not assigned to an event)
  limit - how many people to show
  show_info - shows the persons role and organization (if available) */

if (!function_exists('espresso_show_personnel')) {

	function espresso_show_personnel($event_id = 0, $atts) {
		global $espresso_premium;
		if ($espresso_premium != true)
			return;
		global $wpdb;
		extract($atts, EXTR_PREFIX_ALL, "v");
		if ($event_id == 0 && ($v_staff_id == 0 || $v_staff_id == ''))
			return;
		$v_limit = $v_limit > 0 ? " LIMIT 0," . $v_limit . " " : '';
		$sql = "SELECT s.id, s.name, s.role, s.meta ";
		$sql .= " FROM " . EVENTS_PERSONNEL_TABLE . ' s ';
		if ($v_staff_id > 0) {
			$sql .= " WHERE s.id ='" . $v_staff_id . "' ";
		} else {
			$sql .= " JOIN " . EVENTS_PERSONNEL_REL_TABLE . " r ON r.person_id = s.id ";
			$sql .= " WHERE r.event_id ='" . $event_id . "' ";
		}
		$sql .= $v_limit;
		//echo $sql;
		$event_personnel = $wpdb->get_results($sql);
		$num_rows = $wpdb->num_rows;
		if ($num_rows > 0) {
			$html = '';
			foreach ($event_personnel as $person) {
				$person_id = $person->id;
				$person_name = $person->name;
				$person_role = $person->role;

				$meta = unserialize($person->meta);
				$person_organization = $meta['organization'] != '' ? $meta['organization'] : '';
				//$person_title = $meta['title']!=''? $meta['title']:'';
				$add_dash = ($person_role != '' && $person_organization != '') ? ' - ' : '';
				if ($v_show_info == true)
					$person_info = ($person_role != '' || $person_organization != '') ? ' [' . $person_role . $add_dash . $person_organization . ']' : '';

				$html .= $v_before . $person_name . $person_info . $v_after;
			}
		}
		return $v_wrapper_start . $html . $v_wrapper_end;
	}

}

//Function to include a template file. Checks user templates folder first, then default template.
if (!function_exists('event_espresso_require_template')) {

	/**
	 * event_espresso_require_template()
	 *
	 * @param mixed $template_file_name // Name of template file.
	 * @param bool $must_exist          // Error if neither file exist.
	 * @param bool $as_require_once     // True for require_once(), False for require()
	 * @return void    // No return value. File already included.
	 *
	 * Usage: event_espresso_require_template('shopping_cart.php')
	 */
	function event_espresso_require_template($template_file_name, $must_exist = true, $as_require_once = true) {
		event_espresso_require_file($template_file_name, EVENT_ESPRESSO_TEMPLATE_DIR, EVENT_ESPRESSO_PLUGINFULLPATH . 'templates/', $must_exist, $as_require_once);
	}

}

//Function to include a gateway file. Checks user gateway folder first, then default template.
if (!function_exists('event_espresso_require_gateway')) {

	/**
	 * event_espresso_require_gateway()
	 *
	 * @param mixed $template_file_name // Name of template file.
	 * @param bool $must_exist          // Error if neither file exist.
	 * @param bool $as_require_once     // True for require_once(), False for require()
	 * @return void    // No return value. File already included.
	 *
	 * Usage: event_espresso_require_gateway('PaymentGateway.php')
	 */
	function event_espresso_require_gateway($template_file_name, $must_exist = true, $as_require_once = true) {
		event_espresso_require_file($template_file_name, EVENT_ESPRESSO_GATEWAY_DIR . '/', EVENT_ESPRESSO_PLUGINFULLPATH . 'gateways/', $must_exist, $as_require_once);
	}

}

//Function to include a template file. Checks user templates folder first, then default template.
if (!function_exists('event_espresso_require_file')) {

	/**
	 * event_espresso_require_file()
	 *
	 * @param mixed $template_file_name // Name of template file.
	 * @param mixed $path_first         // First choice for file location.
	 * @param mixed $path_first         // Fallback location for file.
	 * @param bool $must_exist          // Error if neither file exist.
	 * @param bool $as_require_once     // True for require_once(), False for require()
	 * @return void    // No return value. File already included.
	 *
	 * Usage: event_espresso_require_file('shopping_cart.php',EVENT_ESPRESSO_TEMPLATE_DIR,EVENT_ESPRESSO_PLUGINFULLPATH.'templates/')
	 */
	function event_espresso_require_file($template_file_name, $path_first, $path_else, $must_exist = true, $as_require_once = true) {
		if (file_exists($path_first . $template_file_name)) {
			// Use the template file in the user's upload folder
			$full_path = $path_first . $template_file_name;
		} else {
			// Use the system file path
			$full_path = $path_else . $template_file_name;
		}
		if (file_exists($full_path) || $must_exist) {
			$path = substr($full_path,0,strrpos($full_path, '/'));
			($as_require_once == true) ? require_once($full_path) : require($full_path);
		}
	}

}

//Added by Imon
//Function to clean up left out data from multi event registration id group table
if (!function_exists('event_espresso_cleanup_multi_event_registration_id_group_data')) {

	/**
	 * event_espresso_cleanup_multi_event_registration_id_group_data()
	 *
	 * Usage: event_espresso_cleanup_multi_event_registration_id_group_data()
	 */
	function event_espresso_cleanup_multi_event_registration_id_group_data() {
		global $wpdb;
		$wpdb->query(" delete emerig from " . EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE . " emerig left join " . EVENTS_ATTENDEE_TABLE . "  ea on emerig.registration_id = ea.registration_id where ea.registration_id is null ");
	}

}



function espresso_check_scripts() {
	if (function_exists('wp_script_is')) {
		if (!wp_script_is('jquery')) {
			echo '<div class="event_espresso_error"><p><em>' . __('Jquery is not loaded!', 'event_espresso') . '</em><br />' . __('Event Espresso is unable to load Jquery do to a conflict with your theme or another plugin.', 'event_espresso') . '</p></div>';
		}
	}
	if (!function_exists('wp_head')) {
		echo '<div class="event_espresso_error"><p><em>' . __('Missing wp_head() Function', 'event_espresso') . '</em><br />' . __('The WordPress function wp_head() seems to be missing in your theme. Please contact the theme developer to make sure this is fixed before using Event Espresso.', 'event_espresso') . '</p></div>';
	}
	if (!function_exists('wp_footer')) {
		echo '<div class="event_espresso_error"><p><em>' . __('Missing wp_footer() Function', 'event_espresso') . '</em><br />' . __('The WordPress function wp_footer() seems to be missing in your theme. Please contact the theme developer to make sure this is fixed before using Event Espresso.', 'event_espresso') . '</p></div>';
	}
}

//These functions were moved here from admin.php on 08-30-2011 by Seth
function espresso_edit_this($event_id) {
	global $espresso_premium;
	if ($espresso_premium != true)
		return;
	global $current_user;
	wp_get_current_user();
	$curauth = wp_get_current_user();
	$user_id = $curauth->ID;
	$user = new WP_User($user_id);
	foreach ($user->roles as $role) {
		//echo $role;
		//Build the edit event link
		$edit_link = '<a class="post-edit-link" href="' . site_url() . '/wp-admin/admin.php?page=events&action=edit&event_id=' . $event_id . '">' . __('Edit Event') . '</a>';
		switch ($role) {
			case 'administrator':
			case 'espresso_event_admin':
			case 'espresso_event_manager':
			case 'espresso_group_admin':
				//If user is an event manager, then show the edit link for their events
				if (function_exists('espresso_member_data') && espresso_member_data('role') == 'espresso_eventmanager' && espresso_member_data('id') != espresso_is_my_event($event_id))
					return;
				return $edit_link;
				break;
		}
	}
}

//Retrives the attendee count based on an attendee ids
function espresso_count_attendees_for_registration($attendee_id) {
	global $wpdb;
	$cnt = $wpdb->get_var("SELECT COUNT(1) as cnt FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . espresso_registration_id($attendee_id) . "' ORDER BY id ");
	if ($cnt == 1) {
		$cnt = $wpdb->get_var("SELECT quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . espresso_registration_id($attendee_id) . "' ORDER BY id ");
		if ($cnt == 0) {
			return 1;
		} elseif ($cnt > 0) {
			return $cnt;
		}
	}
	return $cnt;
}

function espresso_quantity_for_registration($attendee_id) {
	global $wpdb;
	$cnt = $wpdb->get_var("SELECT quantity FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id='" . espresso_registration_id($attendee_id) . "' ORDER BY id ");
	return $cnt;
}

function espresso_is_primary_attendee($attendee_id) {
	global $wpdb;
	$sql = "SELECT am.meta_value FROM " . EVENTS_ATTENDEE_META_TABLE . " am ";
	$sql .= " WHERE am.attendee_id = '" . $attendee_id . "' AND am.meta_key='primary_attendee' AND am.meta_value='1' ";
	//echo $sql;
	$wpdb->get_results($sql);
	if ($wpdb->num_rows > 0) {
		return true;
	}
}

function espresso_get_primary_attendee_id($registration_id) {
	global $wpdb;
	$sql = "SELECT am.attendee_id FROM " . EVENTS_ATTENDEE_META_TABLE . " am ";
	$sql .= " JOIN " . EVENTS_ATTENDEE_TABLE . " ea ON ea.id = am.attendee_id ";
	$sql .= " WHERE ea.registration_id = '" . $registration_id . "' AND am.meta_key='primary_attendee' AND am.meta_value='1' ";
	//echo $sql;
	$wpdb->get_results($sql);
	if ($wpdb->num_rows > 0) {
		return $wpdb->last_result[0]->attendee_id;
	}
}

function espresso_ticket_links($registration_id, $attendee_id, $email = FALSE) {
	global $wpdb;
	$sql = "SELECT * FROM " . EVENTS_ATTENDEE_TABLE;
	if (espresso_is_primary_attendee($attendee_id) != true) {
		$sql .= " WHERE id = '" . $attendee_id . "' ";
	} else {
		$sql .= " WHERE registration_id = '" . $registration_id . "' ";
	}
	//echo $sql;
	$attendees = $wpdb->get_results($sql);
	$ticket_link = '';
	if ($wpdb->num_rows > 0) {
		
		$break = '<br />';
		$group = $wpdb->num_rows > 1 ? sprintf(__('Tickets Purchased (%s):', 'event_espresso'), $wpdb->num_rows).$break : __('Download/Print Ticket:', 'event_espresso').$break;
		
		foreach ($attendees as $attendee) {
			$ticket_url = get_option('siteurl') . "/?download_ticket=true&amp;id=" . $attendee->id . "&amp;r_id=" . $attendee->registration_id;
			if (function_exists('espresso_ticket_launch')) {
				$ticket_url = espresso_ticket_url($attendee->id, $attendee->registration_id);
			}
			$ticket_link .= '<a href="' . $ticket_url . '" target="_blank">' . $attendee->fname . ' ' . $attendee->lname . '</a>' . $break;
		}
		
		if ($email == TRUE){
			$text = '<p>' . $group . $ticket_link .'</p>';
		}else{
			$text = $ticket_link;
		}
		
		return $text;
	}
}


/**
 * Function espresso_get_attendee_coupon_discount
 * Get discount amount for a given attendee id and cost
 *
 * @global wpdb $wpdb
 * @param int $attendee_id
 * @param double $cost
 */
function espresso_get_attendee_coupon_discount($attendee_id, $cost) {
	global $wpdb;
	$coupon_code = "";

	$row = $wpdb->get_row($wpdb->prepare("select * from " . EVENTS_ATTENDEE_TABLE . " where id = %d", $attendee_id), ARRAY_A);
	if (!is_null($row['coupon_code']) && !empty($row['coupon_code'])) {
		$coupon_code = $row['coupon_code'];
		$event_id = $row['event_id'];
		//$results = $wpdb->get_results("SELECT * FROM ". EVENTS_DISCOUNT_CODES_TABLE ." WHERE coupon_code = '".$_REQUEST['coupon_code']."'");
		$discounts = $wpdb->get_results("SELECT d.* FROM " . EVENTS_DISCOUNT_CODES_TABLE . " d JOIN " . EVENTS_DISCOUNT_REL_TABLE . " r ON r.discount_id  = d.id WHERE d.coupon_code = '" . $coupon_code . "'  AND r.event_id = '" . $event_id . "' ");
		if ($wpdb->num_rows > 0) {
			$valid_discount = true;
			foreach ($discounts as $discount) {
				$discount_id = $discount->id;
				$coupon_code = $discount->coupon_code;
				$coupon_code_price = $discount->coupon_code_price;
				$coupon_code_description = $discount->coupon_code_description;
				$use_percentage = $discount->use_percentage;
			}
			$discount_type_price = $use_percentage == 'Y' ? $coupon_code_price . '%' : $org_options['currency_symbol'] . $coupon_code_price;

			if ($use_percentage == 'Y') {
				$pdisc = $coupon_code_price / 100;
				$cost = $cost - ($cost * $pdisc);
			} else {
				$cost = $cost - $coupon_code_price;
			}
		}
	}
	return $cost;
}

//Returns the registration id from a url string
function espresso_return_reg_id(){
	if( isset($_REQUEST['registration_id']) && !empty($_REQUEST['registration_id']) ){
		return $_REQUEST['registration_id'];
	}elseif ( isset($_REQUEST['r_id']) && !empty($_REQUEST['r_id']) ){
		return $_REQUEST['r_id'];
	}else{
		return false;
	}
}

//Build the registration id
function espresso_build_registration_id($event_id){
	return uniqid($event_id . '-');
}
		
//Registration id filter
add_filter('filter_hook_espresso_registration_id', 'espresso_build_registration_id', 10, 1);

/*
  Displays a featured image in the event listings and registration pages.

  Example usage in a template file:
  echo apply_filters('filter_hook_espresso_display_featured_image', $event_id, $event_meta['event_thumbnail_url']);
  (Note: the $event_meta variable (array) is populated in the event_list.php and registration_page.php files.)
  
  Advanced usage using the class, title and align parameters:
  echo apply_filters('filter_hook_espresso_display_featured_image', $event_id, $event_meta['event_thumbnail_url'], 'a-custom-class', 'Title of the image');

  Parameters:
  event_id - used in the id attribute of the image
  class - a custom css class. //Default: ee-featured-image
  image_url - the url of the image, most likely the $event_meta['event_thumbnail_url'] variable from the event_list.php and registration_page.php files
  title - the text to display in the title tag attribute of the image. //Default: Featured Image
*/
if (!function_exists('espresso_display_featured_image')) {
	function espresso_display_featured_image($event_id, $image_url, $class = '', $title = '') {
		global $org_options;
		if ( !empty($org_options['display_featured_image']) && $org_options['display_featured_image'] == 'N' || !isset($org_options['display_featured_image']) ){
			return;
		}
		$class = empty($class) ? 'ee-featured-image' : $class;
		$title = empty($title) ? __('Featured Image', 'event_espresso') : $title;
		$align = empty($align) ? 'right' : $align;
		$output = '<div class="' . $class . '" id="espresso_featured_image-'.$event_id.'"><img title="' . $title . '" src="'.$image_url.'" /></div>';
		return $output; 
	}
}
add_filter('filter_hook_espresso_display_featured_image', 'espresso_display_featured_image',100,5);
