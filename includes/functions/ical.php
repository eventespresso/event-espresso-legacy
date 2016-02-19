<?php
if (!function_exists('espresso_ical')) {
function espresso_ical() {

	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	// clean the data
	$valid_data = espresso_validate_iCal_data();

	$output = "BEGIN:VCALENDAR\n" .
		"VERSION:2.0\n" .
		"PRODID:-//" . $valid_data['organization'] . " - Event Espresso Version ".espresso_version()."//NONSGML v1.0//EN\n" .
		"METHOD:PUBLISH\n" .
		//"X-WR-CALNAME:" . $valid_data['organization'] . "\n" . //Publishes a new calendar in some systems.
		"X-ORIGINAL-URL:".$valid_data['ee_reg_url']."\n" .
		"X-WR-CALDESC:" . $valid_data['organization'] . "\n" .
		"X-WR-TIMEZONE:".get_option('timezone_string')."\n" .
		"BEGIN:VEVENT\n" .
		"DTSTAMP:" . $valid_data['current_year'] . $valid_data['current_month'] . $valid_data['current_day'] . "T" . $valid_data['current_time'] . "\n" .
		"UID:" . $valid_data['ee_reg_id'] . "@" . $valid_data['ee_reg_url'] . "\n" .
		"ORGANIZER:MAILTO:" . $valid_data['contact_email'] . "\n" .
		"DTSTART:" . $valid_data['start_year'] . $valid_data['start_month'] . $valid_data['start_day'] . "T" . $valid_data['start_time'] . "\n" .
		"DTEND:" . $valid_data['end_year'] . $valid_data['end_month'] . $valid_data['end_day'] . "T" . $valid_data['end_time'] . "\n" .
		"STATUS:CONFIRMED\n" .
		"URL:" . $valid_data['ee_reg_url'] . "\n" .
		"SUMMARY:" . $valid_data['event_summary'] . "\n" .
		//"DESCRIPTION:" . $valid_data['event_description'] . "\n" .
		"LOCATION:" . $valid_data['location'] . "\n" .
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
		header('Content-Disposition: inline; filename="' . $valid_data['event_summary'] . '.ics"');
		//header('Cache-Control: private, max-age=0, must-revalidate');
		//header('Pragma: public');
		//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		//ini_set('zlib.output_compression', '0');
		echo $output;
		die();
	}
}


/**
 * validate and sanitize iCal $_REQUEST data
 */
function espresso_validate_iCal_data() {
	return array(
		'event_summary' => isset( $_REQUEST['event_summary'] ) ? sanitize_text_field( $_REQUEST['event_summary'] ) : '',
		'organization' => isset( $_REQUEST['organization'] ) ? sanitize_text_field( $_REQUEST['organization'] ) : '',
		'ee_reg_url' => isset( $_REQUEST['ee_reg_url'] ) ? esc_url_raw( $_REQUEST['ee_reg_url'] ) : '',
		'ee_reg_id' => isset( $_REQUEST['ee_reg_id'] ) ? sanitize_text_field( $_REQUEST['ee_reg_id'] ) : '',
		'current_year' => isset( $_REQUEST['current_year'] ) ? sanitize_text_field( $_REQUEST['current_year'] ) : date('Y'),
		'current_month' => isset( $_REQUEST['current_month'] ) ? sanitize_text_field( $_REQUEST['current_month'] ) : date('m'),
		'current_day' => isset( $_REQUEST['current_day'] ) ? sanitize_text_field( $_REQUEST['current_day'] ) : date('d'),
		'current_time' => isset( $_REQUEST['current_time'] ) ? sanitize_text_field( $_REQUEST['current_time'] ) : date('His'),
		'contact_email' => isset( $_REQUEST['contact_email'] ) ? sanitize_email( $_REQUEST['contact_email'] ) : '',
		'start_year' => isset( $_REQUEST['start_year'] ) ? sanitize_text_field( $_REQUEST['start_year'] ) : '',
		'start_month' => isset( $_REQUEST['start_month'] ) ? sanitize_text_field( $_REQUEST['start_month'] ) : '',
		'start_day' => isset( $_REQUEST['start_day'] ) ? sanitize_text_field( $_REQUEST['start_day'] ) : '',
		'start_time' => isset( $_REQUEST['start_time'] ) ? sanitize_text_field( $_REQUEST['start_time'] ) : '',
		'end_year' => isset( $_REQUEST['end_year'] ) ? sanitize_text_field( $_REQUEST['end_year'] ) : '',
		'end_month' => isset( $_REQUEST['end_month'] ) ? sanitize_text_field( $_REQUEST['end_month'] ) : '',
		'end_day' => isset( $_REQUEST['end_day'] ) ? sanitize_text_field( $_REQUEST['end_day'] ) : '',
		'end_time' => isset( $_REQUEST['end_time'] ) ? sanitize_text_field( $_REQUEST['end_time'] ) : '',
		'event_description' => isset( $_REQUEST['event_description'] ) ? sanitize_text_field( $_REQUEST['event_description'] ) : '',
		'location' => isset( $_REQUEST['location'] ) ? sanitize_text_field( $_REQUEST['location'] ) : '',
	);
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
	global $org_options;
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		if (  ! isset( $org_options['display_ical_download'] ) || empty( $org_options['display_ical_download'] ) || $org_options['display_ical_download'] == 'N' ){
			return '';
	}
		$start_date = !empty($meta['start_date_unformatted']) ? $meta['start_date_unformatted'] : $meta['start_date'];
		$start_date = strtotime( $start_date . ' ' . $meta['start_time']);

		$end_date = !empty($meta['end_date_unformatted']) ? $meta['start_date_unformatted'] : $meta['start_date'];
		$end_date = strtotime( $end_date . ' ' . $meta['end_time']);

		$title = empty($text) ? __('iCal Import', 'event_espresso') : $title;
		$image = empty($image) ? '<img src="'.EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/calendar_link.png">' : $image;
		if ($link_only == TRUE){
			$image = $title;
		}
		$array = array(
			'iCal' => 'true',
			'current_year' => date('Y'),
			'current_month' => date('m'),
			'current_day' => date('d'),
			'current_time' => date('His'),
			'event_id' => $meta['event_id'],
			'ee_reg_id' => !empty($meta['ee_reg_id']) ? $meta['ee_reg_id'] : $_SESSION['espresso_session']['id'],
			'contact_email' => $meta['contact_email'],
			'start_year' => date('Y', $start_date),
			'start_month' => date('m', $start_date),
			'start_day' => date('d', $start_date),
			'start_time' => date('His', $start_date),
			'end_year' => date('Y', $end_date),
			'end_month' => date('m', $end_date),
			'end_day' => date('d', $end_date),
			'end_time' => date('His', $end_date),
			'event_summary' => stripslashes($meta['event_name']),
			//'event_description' => espresso_format_content(stripslashes($meta['event_desc'])),
			'ee_reg_url' => espresso_reg_url($meta['event_id']),
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