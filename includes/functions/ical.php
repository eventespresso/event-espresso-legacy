<?php
if (!function_exists('espresso_ical')) {
function espresso_ical() {
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	$name = $_REQUEST['event_summary'] . ".ics";
	$output = "BEGIN:VCALENDAR\n" .
					"VERSION:2.0\n" .
						"PRODID:-//" . $_REQUEST['organization'] . " - Event Espresso Version ".espresso_version()."//NONSGML v1.0//EN\n" .
						"METHOD:PUBLISH\n" .
						//"X-WR-CALNAME:" . $_REQUEST['organization'] . "\n" . //Publishes a new calendar in some systems.
						"X-ORIGINAL-URL:".$_REQUEST['eereg_url']."\n" .
						"X-WR-CALDESC:" . $_REQUEST['organization'] . "\n" .
						"X-WR-TIMEZONE:".get_option('timezone_string')."\n" .
					"BEGIN:VEVENT\n" .
					"DTSTAMP:" . $_REQUEST['currentyear'] . $_REQUEST['currentmonth'] . $_REQUEST['currentday'] . "T" . $_REQUEST['currenttime'] . "\n" .
						"UID:" . $_REQUEST['registration_id'] . "@" . $_REQUEST['eereg_url'] . "\n" .
						"ORGANIZER:MAILTO:" . $_REQUEST['contact_email'] . "\n" .
					"DTSTART:" . $_REQUEST['startyear'] . $_REQUEST['startmonth'] . $_REQUEST['startday'] . "T" . $_REQUEST['starttime'] . "\n" .
					"DTEND:" . $_REQUEST['endyear'] . $_REQUEST['endmonth'] . $_REQUEST['endday'] . "T" . $_REQUEST['endtime'] . "\n" .
					"STATUS:CONFIRMED\n" .
						"URL:" . $_REQUEST['eereg_url'] . "\n" .
					"SUMMARY:" . $_REQUEST['event_summary'] . "\n" .
					//"DESCRIPTION:" . $_REQUEST['event_description'] . "\n" .
						"LOCATION:" . $_REQUEST['location'] . "\n" .
					"END:VEVENT\n" .
					"END:VCALENDAR\n";
		if (ob_get_length()){
		echo('Some data has already been output, can\'t send iCal file');
		}
	header('Content-Type: text/calendar; charset=utf-8');
		if (headers_sent()){
		echo('Some data has already been output, can\'t send iCal file');
		}
	header('Content-Length: ' . strlen($output));
	header('Content-Disposition: inline; filename="' .$name . '"');
	//header('Cache-Control: private, max-age=0, must-revalidate');
	//header('Pragma: public');
	//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	//ini_set('zlib.output_compression', '0');
	echo $output;
	die();
}
	}

/*
  Displays a link to download an .ics (iCal) file.

  Example usage in a template file:
  echo apply_filters('filter_hook_espresso_display_ical', $all_meta);
  (Note: the $all_meta variable (array) is populated in the event_list.php and registration_page.php files.)
  
  Advanced usage using the title and image parameter:
  echo apply_filters('filter_hook_espresso_display_ical', $all_meta, __('iCal Import', 'event_espresso'), '<img alt="'.__('iCal Import', 'event_espresso').'" src="'.EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/calendar_link.png">');

  Parameters:
  meta - the generated meta from an event template file
  title - the text to display in the title tag attribute of the link
  image - adds html to display an image (or text)
  link_only - ignores the image parameter and displays the title instead
*/
if (!function_exists('espresso_ical_prepare_by_meta')) {
	function espresso_ical_prepare_by_meta($meta, $title = '', $image = '', $link_only = FALSE) {
	global $org_options, $wpdb;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		if ( !empty($org_options['display_ical_download']) && $org_options['display_ical_download'] == 'N' || !isset($org_options['display_ical_download']) ){
			return;
	}

		$start_date = strtotime($meta['start_date'] . ' ' . $meta['start_time']);
		$end_date = strtotime($meta['end_date'] . ' ' . $meta['end_time']);
		$title = empty($text) ? __('iCal Import', 'event_espresso') : $title;
		$image = empty($image) ? '<img src="'.EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/calendar_link.png">' : $image;
		if ($link_only == TRUE){
			$image = $title;
		}
		$array = array(
			'iCal' => 'true', 
			'currentyear' => date('Y'),
			'currentmonth' => date('m'),
			'currentday' => date('d'),
			'currenttime' => date('His'),
			'event_id' => $meta['event_id'],
			'registration_id' => !empty($meta['registration_id']) ? $meta['registration_id'] : $_SESSION['espresso_session']['id'],
			'contact_email' => $meta['contact_email'],
			'startyear' => date('Y', $start_date),
			'startmonth' => date('m', $start_date),
			'startday' => date('d', $start_date),
			'starttime' => date('His', $start_date),
			'endyear' => date('Y', $end_date),
			'endmonth' => date('m', $end_date),
			'endday' => date('d', $end_date),
			'endtime' => date('His', $end_date),
			'event_summary' => stripslashes($meta['event_name']),
			//'event_description' => espresso_format_content(stripslashes($meta['event_desc'])),
			'eereg_url' => espresso_reg_url($meta['event_id']),
			'site_url' => site_url(),
			'organization' => $org_options['organization'],
			'location' => str_replace(array('<br>','<br />'), ' ', $meta['location']),
		);
		$url = add_query_arg( $array, site_url() );
		$html = '<a  href="' . wp_kses($url, '') . '" id="espresso_ical_' . $meta['event_id'] . '" class="espresso_ical_link" title="' . $title . '">' . $image . '</a>';
		return $html;
	}
}
add_filter('filter_hook_espresso_display_ical', 'espresso_ical_prepare_by_meta',100,4);