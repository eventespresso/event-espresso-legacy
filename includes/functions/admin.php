<?php

//All of the functions that deal with admin area should go here.
//These all of the scripts we need
function event_espresso_config_page_styles() {
	wp_enqueue_style('dashboard');
	wp_enqueue_style('thickbox');
	wp_enqueue_style('global');
	wp_enqueue_style('wp-admin');
	wp_enqueue_style('event_espresso', EVENT_ESPRESSO_PLUGINFULLURL . 'css/admin-styles.css'); //Events core style
	if (isset($_REQUEST['page'])) {
		switch ($_REQUEST['page']) {
			case ( 'events' ):
			case ( 'espresso_reports' ):
				wp_enqueue_style('jquery-ui-style', EVENT_ESPRESSO_PLUGINFULLURL . 'css/jquery-ui-1.9.2.custom.min.css');
				break;
			case ( 'payment_gateways'):
				wp_enqueue_media();
				break;
		}
		if (isset($_REQUEST['event_admin_reports'])) {
			switch ($_REQUEST['event_admin_reports']) {
				case 'charts':
					wp_enqueue_style('jquery-jqplot-css', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jqplot/jquery.jqplot.min.css');
					break;
			}
		}
	}
}

function event_espresso_config_page_scripts() {
	add_thickbox();
	wp_enqueue_script('postbox');
	wp_enqueue_script('dashboard');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery');
	wp_enqueue_script('tiny_mce');
	wp_enqueue_script('editor');
	wp_enqueue_script('editor-functions');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('post');
	wp_enqueue_script('dataTables', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jquery.dataTables.min.js', array('jquery')); //Events core table script
	wp_enqueue_script('dataTablesColVis', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jquery.ColVis.min.js', array('jquery')); //Events core table column hide/show script

	if ($_REQUEST['page'] == 'espresso_calendar' || $_REQUEST['page'] == 'event_categories') {
		wp_enqueue_script('farbtastic');
		wp_enqueue_style('farbtastic');
	}

	if ($_REQUEST['page'] == 'events' && isset($_REQUEST['action']) && ($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'add_new_event')) {
		//Load jquery UI stuff
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs');

		//Load datepicker script
		wp_enqueue_script('jquery-ui-datepicker');
	}

	if (isset($_REQUEST['event_admin_reports']) && $_REQUEST['event_admin_reports'] == 'add_new_attendee' || $_REQUEST['page'] == 'form_groups' || $_REQUEST['page'] == 'form_builder' || $_REQUEST['page'] == 'event_staff' || $_REQUEST['page'] == 'event_categories' || $_REQUEST['page'] == 'event_venues' || $_REQUEST['page'] == 'discounts' || $_REQUEST['page'] == 'groupons') {
		//Load form validation script
		wp_register_script('jquery.validate.js', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/jquery.validate.min.js"), false, '1.8.1');
		wp_enqueue_script('jquery.validate.js');
	}

	wp_register_script('event_espresso_js', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/event_espresso.js"), false);
	wp_enqueue_script('event_espresso_js');

	if (isset($_REQUEST['event_admin_reports'])) {
		switch ($_REQUEST['event_admin_reports']) {
			case 'charts':
				wp_enqueue_script('jquery-jqplot-js', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jqplot/jquery.jqplot.min.js', array('jquery'));
				wp_enqueue_script('jqplot-barRenderer-js', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jqplot/plugins/jqplot.barRenderer.min.js', array('jquery'));
				wp_enqueue_script('jqplot-pieRenderer-js', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jqplot/plugins/jqplot.pieRenderer.min.js', array('jquery'));
				wp_enqueue_script('jqplot-categoryAxisRenderer-js', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jqplot/plugins/jqplot.categoryAxisRenderer.min.js', array('jquery'));
				wp_enqueue_script('jqplot-highlighter-js', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jqplot/plugins/jqplot.highlighter.min.js', array('jquery'));
				wp_enqueue_script('jqplot-pointLabels-js', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/jqplot/plugins/jqplot.pointLabels.min.js', array('jquery'));
				break;
		}
	}
	remove_all_filters('mce_external_plugins');
}

//Text formatting function for the_editor.
//This should fix all of the formatting issues of text output from the database.
function espresso_admin_format_content($content = '') {
	return wpautop(stripslashes_deep(html_entity_decode($content, ENT_QUOTES, "UTF-8")));
}

//This loads the the tinymce script into the header
function espresso_tiny_mce() {
	global $wp_version;

	$wp_min_version = '3.2';
	//If the version of WordPress is lower than 3.2, then we load the fallback script.
	if (!version_compare($wp_version, $wp_min_version, '>=')) {
		//If this is an older version of WordPress, then we need to load this.
		if (function_exists('wp_tiny_mce_preload_dialogs')) {
			add_action('admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30);
		}
	}
	$show = true;
	//If this is a newer version of wordress and we are the events page, we don't want to load the editor function
	if (version_compare($wp_version, $wp_min_version, '>=')) {
		//If this is the event editor page, we don't want to load the tiny mce editor because it breaks the page
		if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'events')) {
			$show = false;
		}
		//If this is the edit attendee payments page then we need to load the tiny mce editor.
		//We need to do it this way because the 'event_admin_reports' is in the same URL string as 'event' above.
		if (isset($_REQUEST['event_admin_reports']) && ($_REQUEST['event_admin_reports'] == 'enter_attendee_payments')) {
			$show = true;
		}
	}
	//Load the tiny mce editor
	if ($show == true)
		wp_tiny_mce(false, array("editor_selector" => "theEditor")); // true gives you a stripped down version of the editor
}

//function to delete event
//From now on I am making events disapear instead of deleting completely.
//If an event is active and has active attendees, it will send the attendees an email notification of the cancelled event.
//@param optional pass an event id to delete
if (!function_exists('event_espresso_delete_event')) {

	function event_espresso_delete_event($event_id = 'NULL') {
		global $wpdb;
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
			$event_id = $_REQUEST['event_id'];
		}
		if ($event_id != 'NULL') {
			$sql = array('event_status' => 'D');
			$update_id = array('id' => $event_id);
			$sql_data = array('%s');
			/* if ($wpdb->update(EVENTS_DETAIL_TABLE, $sql, $update_id, $sql_data, array( '%d' ) ) && event_espresso_get_status($event_id) == 'ACTIVE'){
			  event_espresso_send_cancellation_notice($event_id);
			  } */

			//Add an option in general settings for the following?
			/* if (event_espresso_get_status($event_id) == 'ACTIVE') {
			  event_espresso_send_cancellation_notice($event_id);
			  } */

			if ($wpdb->update(EVENTS_DETAIL_TABLE, $sql, $update_id, $sql_data, array('%d'))/* && event_espresso_get_status($event_id) == 'ACTIVE' */) {
				$event_post = $wpdb->get_row("SELECT post_id FROM " . EVENTS_DETAIL_TABLE . " WHERE id =" . $event_id, ARRAY_A);
				wp_delete_post($event_post['post_id']);
				//echo $event_post['post_id'];
				do_action('action_hook_espresso_delete_event_success',$event_id);
				
			}
			
		} else {
			echo '<h1>' . __('No ID  Supplied', 'event_espresso') . '</h1>';
		}
	}

}

//function to empty trash
//This will delete everything that is related to the events that have been deleted
function event_espresso_empty_event_trash($event_id) {
	global $wpdb;
	//if ( $_REQUEST['action'] == 'delete' ){
	//$event_id=$_REQUEST['id'];
	//Remove the event
	$sql = "DELETE FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'";
	$wpdb->query($sql);

	//Remove the event times
	$sql = "DELETE FROM " . EVENTS_START_END_TABLE . " WHERE event_id='" . $event_id . "'";
	$wpdb->query($sql);

	//Remove the event prices
	$sql = "DELETE FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='" . $event_id . "'";
	$wpdb->query($sql);

	//Remove the event discount
	$sql = "DELETE FROM " . EVENTS_DISCOUNT_REL_TABLE . " WHERE event_id='" . $event_id . "'";
	$wpdb->query($sql);

	$sql = "DELETE FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "'";
	$wpdb->query($sql);
	
	do_action('action_hook_espresso_empty_event_trash_success',$event_id);

	/* delete_price_from_event($event_id);
	  delete_category_from_event($event_id);
	  delete_discount_from_event($event_id);
	  delete_attendees_from_event($event_id); */
	//}
}

/**
 * Create a postbox widget
 */
function espresso_postbox($id, $title, $content) {
	?>

	<div id="<?php echo $id; ?>" class="postbox">
		<div class="handlediv" title="Click to toggle"><br />
		</div>
		<h3 class="hndle"><span><?php echo $title; ?></span></h3>
		<div class="inside"> <?php echo $content; ?> </div>
	</div>
	<?php
}

/* Aurelio */

function ee_tep_not_null($value) {
	if (is_array($value)) {
		if (sizeof($value) > 0) {
			return true;
		} else {
			return false;
		}
	} else {
		if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
			return true;
		} else {
			return false;
		}
	}
}

function ee_tep_round($number, $precision) {
	if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.') + 1)) > $precision)) {
		$number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

		if (substr($number, -1) >= 5) {
			if ($precision > 1) {
				$number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision - 1) . '1');
			} elseif ($precision == 1) {
				$number = substr($number, 0, -1) + 0.1;
			} else {
				$number = substr($number, 0, -1) + 1;
			}
		} else {
			$number = substr($number, 0, -1);
		}
	}

	return $number;
}

function ee_tep_output_string($string, $translate = false, $protected = false) {
	if ($protected == true) {
		return htmlspecialchars($string);
	} else {
		if ($translate == false) {
			return ee_tep_parse_input_field_data($string, array('"' => '&quot;'));
		} else {
			return ee_tep_parse_input_field_data($string, $translate);
		}
	}
}

function ee_tep_parse_input_field_data($data, $parse) {
	return strtr(trim($data), $parse);
}

/* Turns an array into a select field */

function select_input($name, $values, $default = '', $parameters = '') {
	$field = '<select name="' . ee_tep_output_string($name) . '"';
	if (ee_tep_not_null($parameters))
		$field .= ' ' . $parameters;
	$field .= '>';

	if (empty($default) && isset($GLOBALS[$name]))
		$default = stripslashes($GLOBALS[$name]);

	for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
		$field .= '<option value="' . $values[$i]['id'] . '"';
		if ($default == $values[$i]['id']) {
			$field .= 'selected = "selected"';
		}

		$field .= '>' . $values[$i]['text'] . '</option>';
	}
	$field .= '</select>';

	return $field;
}

/* * ** These functions deals with moving templates and files *** */

//Creates folders in the uploads directory to facilitate addons and templates
function event_espresso_create_upload_directories() {
	// Create the required folders
	$folders = array(
			EVENT_ESPRESSO_UPLOAD_DIR,
			EVENT_ESPRESSO_TEMPLATE_DIR,
			EVENT_ESPRESSO_GATEWAY_DIR,
			EVENT_ESPRESSO_UPLOAD_DIR . '/logs/',
			EVENT_ESPRESSO_UPLOAD_DIR . '/languages/',
	);

	$folder_permissions = apply_filters( 'espresso_folder_permissions', 0755 );

	foreach ($folders as $folder) {
		wp_mkdir_p($folder);
		@ chmod($folder, $folder_permissions);
	}
	
	if (!file_exists(EVENT_ESPRESSO_UPLOAD_DIR . 'logs/.htaccess')) {
		if (file_put_contents(EVENT_ESPRESSO_UPLOAD_DIR . 'logs/.htaccess', 'deny from all')){
			do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'created .htaccess file that blocks direct access to logs folder');
		}
	}
	
	if (!file_exists(EVENT_ESPRESSO_UPLOAD_DIR . 'languages/index.php')) {
		if (file_put_contents(EVENT_ESPRESSO_UPLOAD_DIR . 'languages/index.php', '')){
			do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'created uploads/languages/index.php');
		}
	}
}

/**
 * event_espresso_count_files, does exactly what the name says
 */
function event_espresso_count_files($path, $exclude = ".|..|.svn", $recursive = false) {
	$result = array();
	$path = rtrim($path, "/") . "/";
	if (is_dir($path)) {
		$folder_handle = opendir($path);
		$exclude_array = explode("|", $exclude);
		while (false !== ($filename = readdir($folder_handle))) {
			if (!in_array(strtolower($filename), $exclude_array)) {
				if (is_dir($path . $filename . "/")) {
					if ($recursive)
						$result[] = file_array($path, $exclude, true);
				} else {
					$result[] = $filename;
				}
			}
		}
	}
	//return $result;
	return count($result);
}

//Functions for copying and moving templates
function event_espresso_trigger_copy_templates() {
	global $wpdb;
	check_admin_referer('copy_templates');
	event_espresso_smartCopy(EVENT_ESPRESSO_PLUGINFULLPATH . 'templates/', EVENT_ESPRESSO_TEMPLATE_DIR);

	$_SESSION['event_espresso_themes_copied'] = true;
	$sendback = wp_get_referer();
	$sendback = add_query_arg('tab', $_SESSION['event_espresso_settings_curr_page'], remove_query_arg('tab', $sendback));
	wp_redirect($sendback);
	exit();
}

//Functions for copying and moving gateways
function event_espresso_trigger_copy_gateways() {
	global $wpdb;
	check_admin_referer('copy_gateways');
	event_espresso_smartCopy(EVENT_ESPRESSO_PLUGINFULLPATH . 'gateways/', EVENT_ESPRESSO_GATEWAY_DIR);

	$_SESSION['event_espresso_gateways_copied'] = true;
	$sendback = wp_get_referer();
	$sendback = add_query_arg('tab', $_SESSION['event_espresso_settings_curr_page'], remove_query_arg('tab', $sendback));
	wp_redirect($sendback);
	exit();
}

//Functions for copying and moving files and themes
function event_espresso_smartCopy($source, $dest, $folder_permissions = 0755, $file_permissions = 0644) {
# source=file & dest=dir => copy file from source-dir to dest-dir
# source=file & dest=file / not there yet => copy file from source-dir to dest and overwrite a file there, if present
# source=dir & dest=dir => copy all content from source to dir
# source=dir & dest not there yet => copy all content from source to a, yet to be created, dest-dir
	$result = false;
	$folder_permissions = apply_filters( 'espresso_folder_permissions', $folder_permissions );
	$file_permissions = apply_filters( 'espresso_file_permissions', $file_permissions );

	if (is_file($source)) { # $source is file
		if (is_dir($dest)) { # $dest is folder
			if ($dest[strlen($dest) - 1] != '/') # add '/' if necessary
				$__dest = $dest . "/";
			$__dest .= basename($source);
		}
		else { # $dest is (new) filename
			$__dest = $dest;
		}
		$result = copy($source, $__dest);
		chmod($__dest, $file_permissions);
	} elseif (is_dir($source)) { # $source is dir
		if (!is_dir($dest)) { # dest-dir not there yet, create it
			@mkdir($dest, $folder_permissions);
			chmod($dest, $folder_permissions);
		}
		if ($source[strlen($source) - 1] != '/') # add '/' if necessary
			$source = $source . "/";
		if ($dest[strlen($dest) - 1] != '/') # add '/' if necessary
			$dest = $dest . "/";

		# find all elements in $source
		$result = true; # in case this dir is empty it would otherwise return false
		$dirHandle = opendir($source);
		while ($file = readdir($dirHandle)) { # note that $file can also be a folder
			if ($file != "." && $file != "..") { # filter starting elements and pass the rest to this function again
#                echo "$source$file ||| $dest$file<br />\n";
				$result = event_espresso_smartCopy($source . $file, $dest . $file, $folder_permissions, $file_permissions);
			}
		}
		closedir($dirHandle);
	} else {
		$result = false;
	}
	return $result;
}

function espresso_getFileList($dir) {
	#array to hold return value
	$retval = array();
	#add trailing slash if missing
	if (substr($dir, -1) != "/")
		$dir .= "/";
	# open pointer to directory and read list of files
	$d = @dir($dir) or die("getFileList: Failed opening directory $dir for reading");
	while (false !== ($entry = $d->read())) {
		# skip hidden files
		if ($entry[0] == "." || $entry[0] == "_")
			continue;
		if (is_dir("$dir$entry")) {
			$retval[] = array("name" => "$dir$entry/", "type" => filetype("$dir$entry"), "size" => 0, "lastmod" => filemtime("$dir$entry"));
		} elseif (is_readable("$dir$entry")) {
			$retval[] = array("name" => "$dir$entry", "type" => mime_content_type("$dir$entry"), "size" => filesize("$dir$entry"), "lastmod" => filemtime("$dir$entry"));
		}
	} $d->close();
	return $retval;
}

/*
  // espresso_getFileList() Usage
  $dirlist = espresso_getFileList(EVENT_ESPRESSO_TEMPLATE_DIR);
  echo "<table>\n";
  echo "<tr><th>Name</th><th>Type</th><th>Size</th><th>Last Mod.</th></tr>\n";
  foreach($dirlist as $file) { echo "<tr>\n";
  echo "<td>{$file['name']}</td>\n";
  echo "<td>{$file['type']}</td>\n";
  echo "<td>{$file['size']}</td>\n";
  echo "<td>" . date("r", $file['lastmod']) . "</td>\n";
  echo "</tr>\n"; }
  echo "</table>\n\n"; */

/* * ** These functions deal with country data *** */

function getCountriesArray($lang = "en") {
	//first code, country_id
	//seconde code, country name
	//third code, ISO country id with two chars
	//fourth code, ISO country id with three chars
	//last code is for political zones, 2 is for european union, 1 for the rest of the world (by the moment)
	switch ($lang) {
		default: return array(
			// updated country list since 3.1.30
					array(0, __('No country selected', 'event_espresso'), '', '', 0),
					array(64, 'United States', 'US', 'USA', 1),
					array(15, 'Australia', 'AU', 'AUS', 1),
					array(39, 'Canada', 'CA', 'CAN', 1),
					array(171, 'United Kingdom', 'GB', 'GBR', 1),
					array(70, 'France', 'FR', 'FRA', 2),
					array(111, 'Italy', 'IT', 'ITA', 2),
					array(63, 'Spain', 'ES', 'ESP', 2),
					array(1, 'Afghanistan', 'AF', 'AFG', 1),
					array(2, 'Albania', 'AL', 'ALB', 1),
					array(3, 'Germany', 'DE', 'DEU', 2),
					array(198, 'Switzerland', 'CH', 'CHE', 1),
					array(87, 'Netherlands', 'NL', 'NLD', 2),
					array(197, 'Sweden', 'SE', 'SWE', 1),
					array(230, 'Akrotiri and Dhekelia', 'CY', 'CYP', 2),
					array(4, 'Andorra', 'AD', 'AND', 2),
					array(5, 'Angola', 'AO', 'AGO', 1),
					array(6, 'Anguilla', 'AI', 'AIA', 1),
					array(7, 'Antarctica', 'AQ', 'ATA', 1),
					array(8, 'Antigua and Barbuda', 'AG', 'ATG', 1),
					array(10, 'Saudi Arabia', 'SA', 'SAU', 1),
					array(11, 'Algeria', 'DZ', 'DZA', 1),
					array(12, 'Argentina', 'AR', 'ARG', 1),
					array(13, 'Armenia', 'AM', 'ARM', 1),
					array(14, 'Aruba', 'AW', 'ABW', 1),
					array(16, 'Austria', 'AT', 'AUT', 2),
					array(17, 'Azerbaijan', 'AZ', 'AZE', 1),
					array(18, 'Bahamas', 'BS', 'BHS', 1),
					array(19, 'Bahrain', 'BH', 'BHR', 1),
					array(20, 'Bangladesh', 'BD', 'BGD', 1),
					array(21, 'Barbados', 'BB', 'BRB', 1),
					array(22, 'Belgium ', 'BE', 'BEL', 2),
					array(23, 'Belize', 'BZ', 'BLZ', 1),
					array(24, 'Benin', 'BJ', 'BEN', 1),
					array(25, 'Bermudas', 'BM', 'BMU', 1),
					array(26, 'Belarus', 'BY', 'BLR', 1),
					array(27, 'Bolivia', 'BO', 'BOL', 1),
					array(28, 'Bosnia and Herzegovina', 'BA', 'BIH', 1),
					array(29, 'Botswana', 'BW', 'BWA', 1),
					array(96, 'Bouvet Island', 'BV', 'BVT', 1),
					array(30, 'Brazil', 'BR', 'BRA', 1),
					array(31, 'Brunei', 'BN', 'BRN', 1),
					array(32, 'Bulgaria', 'BG', 'BGR', 1),
					array(33, 'Burkina Faso', 'BF', 'BFA', 1),
					array(34, 'Burundi', 'BI', 'BDI', 1),
					array(35, 'Bhutan', 'BT', 'BTN', 1),
					array(36, 'Cape Verde', 'CV', 'CPV', 1),
					array(37, 'Cambodia', 'KH', 'KHM', 1),
					array(38, 'Cameroon', 'CM', 'CMR', 1),
					array(98, 'Cayman Islands', 'KY', 'CYM', 1),
					array(172, 'Central African Republic', 'CF', 'CAF', 1),
					array(40, 'Chad', 'TD', 'TCD', 1),
					array(41, 'Chile', 'CL', 'CHL', 1),
					array(42, 'China', 'CN', 'CHN', 1),
					array(105, 'Christmas Island', 'CX', 'CXR', 1),
					array(43, 'Cyprus', 'CY', 'CYP', 2),
					array(99, 'Cocos Island', 'CC', 'CCK', 1),
					array(100, 'Cook Islands', 'CK', 'COK', 1),
					array(44, 'Colombia', 'CO', 'COL', 1),
					array(45, 'Comoros', 'KM', 'COM', 1),
					array(46, 'Congo', 'CG', 'COG', 1),
					array(47, 'North Korea', 'KP', 'PRK', 1),
					array(50, 'Costa Rica', 'CR', 'CRI', 1),
					array(51, 'Croatia', 'HR', 'HRV', 1),
					array(52, 'Cuba', 'CU', 'CUB', 1),
					array(173, 'Czech Republic', 'CZ', 'CZE', 1),
					array(53, 'Denmark', 'DK', 'DNK', 1),
					array(54, 'Djibouti', 'DJ', 'DJI', 1),
					array(55, 'Dominica', 'DM', 'DMA', 1),
					array(174, 'Dominican Republic', 'DO', 'DOM', 1),
					array(56, 'Ecuador', 'EC', 'ECU', 1),
					array(57, 'Egypt', 'EG', 'EGY', 1),
					array(58, 'El Salvador', 'SV', 'SLV', 1),
					array(60, 'Eritrea', 'ER', 'ERI', 1),
					array(61, 'Slovakia', 'SK', 'SVK', 2),
					array(62, 'Slovenia', 'SI', 'SVN', 2),
					array(65, 'Estonia', 'EE', 'EST', 2),
					array(66, 'Ethiopia', 'ET', 'ETH', 1),
					array(102, 'Faroe islands', 'FO', 'FRO', 1),
					array(103, 'Falkland Islands', 'FK', 'FLK', 1),
					array(67, 'Fiji', 'FJ', 'FJI', 1),
					array(69, 'Finland', 'FI', 'FIN', 2),
					array(71, 'Gabon', 'GA', 'GAB', 1),
					array(72, 'Gambia', 'GM', 'GMB', 1),
					array(73, 'Georgia', 'GE', 'GEO', 1),
					array(74, 'Ghana', 'GH', 'GHA', 1),
					array(75, 'Gibraltar', 'GI', 'GIB', 1),
					array(76, 'Greece', 'GR', 'GRC', 2),
					array(77, 'Grenada', 'GD', 'GRD', 1),
					array(78, 'Greenland', 'GL', 'GRL', 1),
					array(79, 'Guadeloupe', 'GP', 'GLP', 1),
					array(80, 'Guam', 'GU', 'GUM', 1),
					array(81, 'Guatemala', 'GT', 'GTM', 1),
					array(82, 'Guinea', 'GN', 'GIN', 1),
					array(83, 'Equatorial Guinea', 'GQ', 'GNQ', 1),
					array(84, 'Guinea-Bissau', 'GW', 'GNB', 1),
					array(85, 'Guyana', 'GY', 'GUY', 1),
					array(86, 'Haiti', 'HT', 'HTI', 1),
					array(88, 'Honduras', 'HN', 'HND', 1),
					array(89, 'Hong Kong', 'HK', 'HKG', 1),
					array(90, 'Hungary', 'HU', 'HUN', 1),
					array(91, 'India', 'IN', 'IND', 1),
					array(205, 'British Indian Ocean Territory', 'IO', 'IOT', 1),
					array(92, 'Indonesia', 'ID', 'IDN', 1),
					array(93, 'Iraq', 'IQ', 'IRQ', 1),
					array(94, 'Iran', 'IR', 'IRN', 1),
					array(95, 'Ireland', 'IE', 'IRL', 2),
					array(97, 'Iceland', 'IS', 'ISL', 1),
					array(110, 'Israel', 'IL', 'ISR', 1),
					array(49, 'Ivory Coast ', 'CI', 'CIV', 1),
					array(112, 'Jamaica', 'JM', 'JAM', 1),
					array(113, 'Japan', 'JP', 'JPN', 1),
					array(114, 'Jordan', 'JO', 'JOR', 1),
					array(115, 'Kazakhstan', 'KZ', 'KAZ', 1),
					array(116, 'Kenya', 'KE', 'KEN', 1),
					array(117, 'Kyrgyzstan', 'KG', 'KGZ', 1),
					array(118, 'Kiribati', 'KI', 'KIR', 1),
					array(48, 'South Korea', 'KR', 'KOR', 1),
					array(228, 'Kosovo', 'XK', 'XKV', 2), // there is no official ISO code for Kosovo yet (http://geonames.wordpress.com/2010/03/08/xk-country-code-for-kosovo/) so using a temporary country code and a modified 3 character code for ISO code -- this should be updated if/when Kosovo gets its own ISO code
					array(119, 'Kuwait', 'KW', 'KWT', 1),
					array(120, 'Laos', 'LA', 'LAO', 1),
					array(121, 'Latvia', 'LV', 'LVA', 2),
					array(122, 'Lesotho', 'LS', 'LSO', 1),
					array(123, 'Lebanon', 'LB', 'LBN', 1),
					array(124, 'Liberia', 'LR', 'LBR', 1),
					array(125, 'Libya', 'LY', 'LBY', 1),
					array(126, 'Liechtenstein', 'LI', 'LIE', 1),
					array(127, 'Lithuania', 'LT', 'LTU', 2),
					array(128, 'Luxemburg', 'LU', 'LUX', 2),
					array(129, 'Macao', 'MO', 'MAC', 1),
					array(130, 'Macedonia', 'MK', 'MKD', 1),
					array(131, 'Madagascar', 'MG', 'MDG', 1),
					array(132, 'Malaysia', 'MY', 'MYS', 1),
					array(133, 'Malawi', 'MW', 'MWI', 1),
					array(134, 'Maldivas', 'MV', 'MDV', 1),
					array(135, 'Mali', 'ML', 'MLI', 1),
					array(136, 'Malta', 'MT', 'MLT', 2),
					array(101, 'Northern Marianas', 'MP', 'MNP', 1),
					array(137, 'Morocco', 'MA', 'MAR', 1),
					array(104, 'Marshall islands', 'MH', 'MHL', 1),
					array(138, 'Martinique', 'MQ', 'MTQ', 1),
					array(139, 'Mauritius', 'MU', 'MUS', 1),
					array(140, 'Mauritania', 'MR', 'MRT', 1),
					array(141, 'Mayote', 'YT', 'MYT', 2),
					array(142, 'Mexico', 'MX', 'MEX', 1),
					array(143, 'Micronesia', 'FM', 'FSM', 1),
					array(144, 'Moldova', 'MD', 'MDA', 1),
					array(145, 'Monaco', 'MC', 'MCO', 2),
					array(146, 'Mongolia', 'MN', 'MNG', 1),
					array(147, 'Montserrat', 'MS', 'MSR', 1),
					array(227, 'Montenegro', 'ME', 'MNE', 2),
					array(148, 'Mozambique', 'MZ', 'MOZ', 1),
					array(149, 'Myanmar', 'MM', 'MMR', 1),
					array(150, 'Namibia', 'NA', 'NAM', 1),
					array(151, 'Nauru', 'NR', 'NRU', 1),
					array(152, 'Nepal', 'NP', 'NPL', 1),
					array(9, 'Netherlands Antilles', 'AN', 'ANT', 1),
					array(153, 'Nicaragua', 'NI', 'NIC', 1),
					array(154, 'Niger', 'NE', 'NER', 1),
					array(155, 'Nigeria', 'NG', 'NGA', 1),
					array(156, 'Niue', 'NU', 'NIU', 1),
					array(157, 'Norway', 'NO', 'NOR', 1),
					array(158, 'New Caledonia', 'NC', 'NCL', 1),
					array(159, 'New Zealand', 'NZ', 'NZL', 1),
					array(160, 'Oman', 'OM', 'OMN', 1),
					array(161, 'Pakistan', 'PK', 'PAK', 1),
					array(162, 'Palau', 'PW', 'PLW', 1),
					array(163, 'Panama', 'PA', 'PAN', 1),
					array(164, 'Papua New Guinea', 'PG', 'PNG', 1),
					array(165, 'Paraguay', 'PY', 'PRY', 1),
					array(166, 'Peru', 'PE', 'PER', 1),
					array(68, 'Philippines', 'PH', 'PHL', 1),
					array(167, 'Poland', 'PL', 'POL', 1),
					array(168, 'Portugal', 'PT', 'PRT', 2),
					array(169, 'Puerto Rico', 'PR', 'PRI', 1),
					array(170, 'Qatar', 'QA', 'QAT', 1),
					array(176, 'Rwanda', 'RW', 'RWA', 1),
					array(177, 'Romania', 'RO', 'ROM', 2),
					array(178, 'Russia', 'RU', 'RUS', 1),
					array(229, 'Saint Pierre and Miquelon', 'PM', 'SPM', 2),
					array(180, 'Samoa', 'WS', 'WSM', 1),
					array(181, 'American Samoa', 'AS', 'ASM', 1),
					array(183, 'San Marino', 'SM', 'SMR', 2),
					array(184, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 1),
					array(185, 'Saint Helena', 'SH', 'SHN', 1),
					array(186, 'Saint Lucia', 'LC', 'LCA', 1),
					array(188, 'Senegal', 'SN', 'SEN', 1),
					array(189, 'Seychelles', 'SC', 'SYC', 1),
					array(190, 'Sierra Leona', 'SL', 'SLE', 1),
					array(191, 'Singapore', 'SG', 'SGP', 1),
					array(192, 'Syria', 'SY', 'SYR', 1),
					array(193, 'Somalia', 'SO', 'SOM', 1),
					array(194, 'Sri Lanka', 'LK', 'LKA', 1),
					array(195, 'South Africa', 'ZA', 'ZAF', 1),
					array(196, 'Sudan', 'SD', 'SDN', 1),
					array(199, 'Suriname', 'SR', 'SUR', 1),
					array(200, 'Swaziland', 'SZ', 'SWZ', 1),
					array(201, 'Thailand', 'TH', 'THA', 1),
					array(202, 'Taiwan', 'TW', 'TWN', 1),
					array(203, 'Tanzania', 'TZ', 'TZA', 1),
					array(204, 'Tajikistan', 'TJ', 'TJK', 1),
					array(206, 'Timor Oriental', 'TP', 'TMP', 1),
					array(207, 'Togo', 'TG', 'TGO', 1),
					array(208, 'Tokelau', 'TK', 'TKL', 1),
					array(209, 'Tonga', 'TO', 'TON', 1),
					array(210, 'Trinidad and Tobago', 'TT', 'TTO', 1),
					array(211, 'Tunisia', 'TN', 'TUN', 1),
					array(212, 'Turkmenistan', 'TM', 'TKM', 1),
					array(213, 'Turkey', 'TR', 'TUR', 1),
					array(214, 'Tuvalu', 'TV', 'TUV', 1),
					array(215, 'Ukraine', 'UA', 'UKR', 1),
					array(216, 'Uganda', 'UG', 'UGA', 1),
					array(59, 'United Arab Emirates', 'AE', 'ARE', 1),
					array(217, 'Uruguay', 'UY', 'URY', 1),
					array(218, 'Uzbekistan', 'UZ', 'UZB', 1),
					array(219, 'Vanuatu', 'VU', 'VUT', 1),
					array(220, 'Vatican City', 'VA', 'VAT', 2),
					array(221, 'Venezuela', 'VE', 'VEN', 1),
					array(222, 'Vietnam', 'VN', 'VNM', 1),
					array(108, 'Virgin Islands', 'VI', 'VIR', 1),
					array(223, 'Yemen', 'YE', 'YEM', 1),
					array(225, 'Zambia', 'ZM', 'ZMB', 1),
					array(226, 'Zimbabwe', 'ZW', 'ZWE', 1));
	}
}

function getCountryZoneId($country_id) {
	//1 for the rest of the world
	//2 is for european union
	$countries = getCountriesArray();
	for ($t = 0; $t < sizeof($countries); $t++)
		if ($country_id == $countries[$t][0])
			return $countries[$t][4];
	return 0;
}

// this function doesn't appear to work -- default country/zone is NOT USA, for some reason it's defaulting to EU ~c
function getCountryBelongsZone($country_id, $zone_id = 1) { // USA by default
	//2 is for european union
	$countries = getCountriesArray();
	for ($t = 0; $t < sizeof($countries); $t++)
		if ($country_id == $countries[$t][0])
			if ($zone_id == $countries[$t][4])
				return true;
	return false;
}

function getCountryName($id, $lang = "en") {
	$countries = getCountriesArray($lang);
	for ($t = 0; $t < sizeof($countries); $t++)
		if ($id == $countries[$t][0])
			return $countries[$t][1];
	return __('No country selected', 'event_espresso');
}

function getCountryFullData($id, $lang = "en") {
	$countries = getCountriesArray($lang);
	for ($t = 0; $t < sizeof($countries); $t++)
		if ($id == $countries[$t][0])
			return array('id' => $countries[$t][0],
					'title' => $countries[$t][1],
					'iso_code_2' => $countries[$t][2],
					'iso_code_3' => $countries[$t][3]);

	return array('id' => '0',
			'title' => __('No country selected', 'event_espresso'),
			'iso_code_2' => '',
			'iso_code_3' => '');
}

function printCountriesSelector($name, $selected) {
	$selected = intval($selected);
	$countries = getCountriesArray("es");

	echo "<select name='" . $name . "'>";
	for ($t = 0; $t < sizeof($countries); $t++) {
		echo "<option ";
		if ($selected == $countries[$t][0])
			echo " selected='selected' ";
		echo "value='" . $countries[$t][0] . "'>" . $countries[$t][1] . "</option>";
	}
	echo "</select>";
}

/* * ** Misc functions *** */

/**
 * Check if URL is valid
 *
 * @param string $url
 * @return boolean
 */
function event_espresso_is_url($url) {
	return preg_match('~^https?://~', $url);
}

function event_espresso_admin_news($url) {
	return wp_remote_retrieve_body(wp_remote_get($url));
}

//Not sure if this works or not. Need to test at some point.
//This function should build the event editor description field.
function events_editor($content, $id = 'content', $prev_id = 'title') {
	$media_buttons = false;
	$richedit = user_can_richedit();
	?>
	<div id="quicktags">
		<?php wp_print_scripts('quicktags'); ?>
		<script type="text/javascript">edToolbar()</script>
	</div>
	<?php //if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) $output = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($output);    ?>
	<?php
	$the_editor = apply_filters('the_editor', "<div id='editorcontainer'><textarea rows='6' cols='90' name='$id' tabindex='4' id='$id'>%s</textarea></div>\n");
	$the_editor_content = apply_filters('the_editor_content', $content);
	printf($the_editor, $content);
	?>
	<script type="text/javascript">
		// <![CDATA[
		edCanvas = document.getElementById('<?php echo $id; ?>');
	<?php if (user_can_richedit() && $prev_id) { ?>
			var dotabkey = true;
			// If tinyMCE is defined.
			if ( typeof tinyMCE != 'undefined' ) {
				// This code is meant to allow tabbing from Title to Post (TinyMCE).
				jQuery('#<?php echo $prev_id; ?>')[jQuery.browser.opera ? 'keypress' : 'keydown'](function (e) {
					if (e.which == 9 && !e.shiftKey && !e.controlKey && !e.altKey) {
						if ( (jQuery("#post_ID").val() < 1) && (jQuery("#title").val().length > 0) ) { autosave(); }
						if ( tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden() && dotabkey ) {
							e.preventDefault();
							dotabkey = false;
							tinyMCE.activeEditor.focus();
							return false;
						}
					}
				});
			}
	<?php } ?>
		// ]]>
	</script>
	<?php
}

//Create a dashboard widget for Event Espresso News
function espresso_news_dashboard_widget_function() {
	wp_widget_rss_output('http://eventespresso.com/feed/', array('items' => 5, 'show_author' => 1, 'show_date' => 1, 'show_summary' => 0));
}

function espresso_news_dashboard_widgets() {
	wp_add_dashboard_widget('espresso_news_dashboard_widget', 'Event Espresso News', 'espresso_news_dashboard_widget_function');
}
add_action('wp_dashboard_setup', 'espresso_news_dashboard_widgets');


add_action( 'wp_ajax_espresso-ajax-content', 'event_espresso_ajax_metabox_content', 10 );


function event_espresso_ajax_metabox_content() {
	$contentid = isset( $_GET['contentid'] ) ? $_GET['contentid'] : '';
	$url = isset( $_GET['contenturl'] ) ? $_GET['contenturl'] : '';
	
	event_espresso_cached_rss_display( $contentid, $url );
	wp_die();
}



function event_espresso_cached_rss_display( $rss_id, $url ) {
	$loading = '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="hide-if-js">' . __( 'This widget requires JavaScript.' ) . '</p>';
	$doing_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	$pre = '<div class="espresso-rss-display">' . "\n\t";
	$pre .= '<span id="' . $rss_id . '_url" class="hidden">' . $url . '</span>';
	$post = '</div>' . "\n";

	$cache_key = 'esp_rss_' . md5( $rss_id );
	if ( FALSE != ( $output = get_transient( $cache_key ) ) ) {
		echo $pre . $output . $post;
		return TRUE;
	}

	if ( ! $doing_ajax ) {
		echo $pre . $loading . $post;
		return FALSE;
	}

	ob_start();
	wp_widget_rss_output($url, array('show_date' => 0, 'items' => 5) );
	set_transient( $cache_key, ob_get_flush(), 12 * HOUR_IN_SECONDS );
	return TRUE;

}

function event_espresso_display_right_column() {
	global $espresso_premium;
	ob_start();
	?>
			<div id="espresso_news_box_blog" class="postbox">
				<div title="Click to toggle" class="handlediv"><br />
				</div>
				<h3 class="hndle">
					<?php _e('New @ Event Espresso', 'event_espresso'); ?>
				</h3>
				<div class="inside">
					<div class="padding">
						<div class="infolinks">
							<?php
							echo '<h4 style="margin:0">' . __('From the Blog', 'event_espresso') . '</h4>';

							$url = urlencode('http://eventespresso.com/feed/');
							event_espresso_cached_rss_display( 'espresso_news_box_blog', $url );
							?>
						</div>
					</div>
				</div>
			</div>
			<div id="submitdiv2" class="postbox " >
				<div title="Click to toggle" class="handlediv"><br />
				</div>
				<h3 class="hndle">
					<?php _e('Helpful Plugin Links', 'event_espresso'); ?>
				</h3>
				<div class="inside">
					<div class="padding">
						<ul class="infolinks">
							<li>
								<?php echo '<a href="http://eventespresso.com/support/documentation/#getting-started?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Installation+ee_version_'.EVENT_ESPRESSO_VERSION .'&utm_campaign=plugin_sidebar" target="_blank">'.__('Getting Started', 'event_espresso') . '</a>'; ?>
								</li>
							<li><a href="http://eventespresso.com/wiki/put-custom-templates/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Template+Customization<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank">
									<?php _e('Template Customization', 'event_espresso'); ?>
								</a></li>
							<li><a href="http://eventespresso.com/support/forums/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Support+Forums<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank">
									<?php _e('Support Forums', 'event_espresso'); ?>
								</a></li>
							
							<li><a href="http://eventespresso.com/wiki/change-log/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Changelog<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank">
									<?php _e('Changelog', 'event_espresso'); ?>
								</a></li>
							<li><a href="http://eventespresso.com/about/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Meet+the+Team<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank">
									<?php _e('Meet the Team', 'event_espresso'); ?>
								</a></li>
							<li><a href="http://eventespresso.com/rich-features/sponsor-new-features/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Sponsor+New+Features<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank">
									<?php _e('Sponsor New Features!', 'event_espresso'); ?>
								</a></li>
							<li>
									<?php echo '<a href="http://eventespresso.com/pricing/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Plugins&utm_campaign=admin_sidebar" target="_blank">'.__('Plugins', 'event_espresso'). '</a> &amp; <a href="http://eventespresso.com/add-ons/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Addons+ee_version_'.EVENT_ESPRESSO_VERSION .'&utm_campaign=admin_sidebar" target="_blank">' .__('Addons', 'event_espresso').'</a>'; ?><br />
									<br />
									<ol>
						<li><a href="http://eventespresso.com/product/espresso-json-api/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+JSON+API<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank"><?php _e('JSON API', 'event_espresso'); ?></a></li>
						<li><a href="http://eventespresso.com/product/espresso-ticketing/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Ticket+Scanning<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank"><?php _e('Ticket Scanning', 'event_espresso'); ?></a></li>
						<li><a href="http://eventespresso.com/product/espresso-multiple/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Multiple+Event+Registration<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank"><?php _e('Multiple Event Registration', 'event_espresso'); ?></a></li>
						<li><a href="http://eventespresso.com/product/espresso-recurring/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Recurring+Events<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank"><?php _e('Recurring Events', 'event_espresso'); ?></a></li>
						<li><a href="http://eventespresso.com/product/espresso-members/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+WP+User+Integration<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank"><?php _e('WP User Integration', 'event_espresso'); ?></a></li>
						<li><a href="http://eventespresso.com/product/espresso-seating/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Helpful+Plugin+Links+-+Seating+Chart<?php echo '+ee_version_'.EVENT_ESPRESSO_VERSION; ?>&utm_campaign=admin_sidebar" target="_blank"><?php _e('Seating Chart', 'event_espresso'); ?></a></li>
					</ol>
								</li>
						</ul>
					</div>
				</div>
			</div>
			<?php
			global $espresso_premium;
			if ($espresso_premium != true) {
				?>
				<div id="submitdiv2" class="postbox " >
					<h3>
						<?php _e('Sponsors', 'event_espresso'); ?>
					</h3>
					<div class="inside">
						<div class="padding">
							<?php
							$event_regis_sponsors = wp_remote_retrieve_body(wp_remote_get('http://ee-updates.s3.amazonaws.com/plugin-sponsors.html'));
							echo $event_regis_sponsors;
							?>
						</div>
					</div>
				</div>
			<?php }
			return ob_get_clean();
}

// prefered approach to a display function?
/*
  function event_espresso_display_right_column() {
  echo event_espresso_get_right_column();
  }
 */
function event_espresso_get_right_column() {
	global $espresso_premium;
	$output = '<div id="side-info-column" class="inner-sidebar"><div id="side-sortables" class="meta-box-sortables">';
	$output .= '<div id="submitdiv" class="postbox " ><div title="Click to toggle" class="handlediv"><br /></div><h3 class="hndle">' . __('New @ Event Espresso', 'event_espresso') . '</h3>';
	$output .= '<div class="inside"><div class="padding"><div class="infolinks">';
	$output .= '<h4 style="margin:0">' . __('From the Blog', 'event_espresso') . '</h4>';
	ob_start();
	// Get RSS Feed(s)
	@wp_widget_rss_output('http://eventespresso.com/feed/', array('show_date' => 0, 'items' => 6));
	$output .= ob_get_contents();
	ob_end_clean();
	/*$output .= '<h4 style="margin:0">' . __('From the Forums', 'event_espresso') . '</h4>';
	ob_start();
	if ($espresso_premium == true){
		@wp_widget_rss_output('http://eventespresso.com/forum/event-espresso-support/feed', array('show_date' => 0, 'items' => 4));
	}else{
		@wp_widget_rss_output('http://eventespresso.com/forum/event-espresso-public/feed', array('show_date' => 0, 'items' => 4));
	}*/
		
	$output .= ob_get_contents();
	ob_end_clean();
	$output .= '</div></div></div></div><div id="submitdiv2" class="postbox " >';
	$output .= '<div title="Click to toggle" class="handlediv"><br /></div><h3 class="hndle">' . __('Helpful Plugin Links', 'event_espresso') . '</h3>';
	$output .= '<div class="inside"><div class="padding"><ul class="infolinks">';
	$output .= '<li><a href="http://eventespresso.com/wiki/installation/" target="_blank">' . __('Installation', 'event_espresso') . '</a>  &amp; <a href="http://eventespresso.com/wiki/setting-up-event-espresso/" target="_blank">' . __('Usage Guide').'</a></li>';
	$output .= '<li><a href="http://eventespresso.com/wiki/put-custom-templates/" target="_blank">' . __('Template Customization', 'event_espresso') . '</a></li>';
	$output .= '<li><a href="http://eventespresso.com/support/forums/" target="_blank">' . __('Support Forums', 'event_espresso') . '</a></li>';
	$output .= '<li><a href="http://eventespresso.com/rich-features/sponsor-new-features/" target="_blank">' . __('Sponsor New Features!', 'event_espresso') . '</a></li>';
	$output .= '<li><a href="http://eventespresso.com/support/forums/" target="_blank">' . __('Bug Submission Forums', 'event_espresso') . '</a></li>';
	$output .= '<li><a href="http://eventespresso.com/wiki/change-log/" target="_blank">' . __('Changelog', 'event_espresso') . '</a></li>';
	$output .= '<li><a href="http://eventespresso.com/add-ons/">' . __('Plugins and Addons', 'event_espresso') . '</a>
					<ol>
						<li><a href="http://eventespresso.com/product/espresso-ticketing/" target="_blank">Ticket Scanning</a></li>
						<li><a href="http://eventespresso.com/product/espresso-multiple/" target="_blank">Multiple Event Registration</a></li>
						<li><a href="http://eventespresso.com/product/espresso-recurring/" target="_blank">Recurring Events</a></li>
						<li><a href="http://eventespresso.com/product/espresso-members/" target="_blank">WP User Integration</a></li>
						<li><a href="http://eventespresso.com/product/espresso-seating/" target="_blank">Seating Chart</a></li>
					</ol>
				</li>';
	$output .= '</ul></div></div></div>';
	global $espresso_premium;
	if ($espresso_premium != true) {
		$output .= '<div id="submitdiv2" class="postbox " ><h3>' . __('Sponsors', 'event_espresso') . '</h3>';
		$output .= '<div class="inside"><div class="padding">';
		$output .= wp_remote_retrieve_body(wp_remote_get('http://ee-updates.s3.amazonaws.com/plugin-sponsors.html'));
		$output .= '</div></div></div>';
	}
	$output .= '</div></div>';
	return $output;
}

//Displays what email tags are available
function event_espresso_custom_email_info() {
	?>
	<div style="display: none;">
		<div id="custom_email_info" class="pop-help" >
			<div class="TB-ee-frame">
				<h2>
					<?php _e('Email Confirmations', 'event_espresso'); ?>
				</h2>
				<p>
					<?php _e('The following shortcodes can be used to customize the contents of the confirmation emails.', 'event_espresso'); ?>
				</p>
				<p>[registration_id], [fname], [lname], [phone], [edit_attendee_link], [event], [event_link], [event_url], [ticket_type], [ticket_link], [qr_code], [description], [cost], [company], [co_add1], [co_add2], [co_city],[co_state], [co_zip],[contact], [payment_url], [invoice_link], [start_date], [start_time], [end_date], [end_time], [location], [location_phone], [google_map_link], [venue_title], [venue_address], [venue_url], [venue_image], [venue_phone], [custom_questions], [seating_tag]</p>
			</div>
		</div>
	</div>
	<div style="display: none;">
		<div id="custom_email_example" class="pop-help" >
			<div class="TB-ee-frame">
				<h2>
					<?php _e('Sample Mail Send:', 'event_espresso'); ?>
				</h2>
				<p style="font-size:10px;">***This is an automated response - Do Not Reply***</p>
				<p style="font-size:10px;">Thank you [fname] [lname] for registering for [event]. We hope that you will find this event both informative and enjoyable. Should have any questions, please contact [contact].</p>
				<p style="font-size:10px;"><strong>Ticket type:</strong> [ticket_type]</p>
				<p style="font-size:10px;"><strong>Print Tickets:</strong> [ticket_link] (A link <a href="http://eventespresso.com/product/espresso-ticketing/" target="_blank">Your Customized Ticket</a> if the ticketing addon is installed.)</p>
				<p style="font-size:10px;">[qr_code] (generated by the QR Code addon, if installed)</p>
				<p style="font-size:10px;">If you have not done so already, please submit your payment in the amount of [cost].</p>
				<p style="font-size:10px;">Click here to review your payment information [payment_url].</p>
				<p style="font-size:10px;">[edit_attendee_link].</p>
				<p style="font-size:10px;">Your questions: [custom_questions].</p>
			</div>
		</div>
	</div>
	<?php
}

//Function to check if registration ids are missing
function event_espresso_verify_attendee_data() {
	if (!is_admin())
		return;
	global $wpdb;
	$table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . EVENTS_ATTENDEE_TABLE . "'");
	if (!empty($table_exists)) {
		$sql = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id IS NULL OR registration_id = '' OR registration_id = '0' OR quantity IS NULL OR quantity = '' OR quantity = '0' ";
		$wpdb->get_results($sql);
		if ($wpdb->num_rows > 0) {
			return true;
		}
	}
}

function event_espresso_update_attendee_data() {
	global $wpdb;
	//$wpdb->show_errors();

	$sql = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id IS NULL OR registration_id = '' OR registration_id = '0' ";
	$attendees = $wpdb->get_results($sql);

	if ($wpdb->num_rows > 0) {

		//echo $sql;
		foreach ($attendees as $attendee) {

			/**********************************
			 * ******	Update single registrations
			 * ********************************* */
			$registration_id = uniqid('', true);
			$update_attendee = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET registration_id = '" . $registration_id . "' WHERE id = '" . $attendee->id . "'";
			$wpdb->query($update_attendee);
		}
	}

	$sql2 = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE quantity IS NULL OR quantity = '' OR quantity = '0' ";
	$attendees2 = $wpdb->get_results($sql2);
	if ($wpdb->num_rows > 0) {
		//echo $sql;
		foreach ($attendees2 as $attendee2) {

			/**			 * *******************************
			 * ******	Update pricing
			 * ********************************* */
			$update_attendee2 = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET quantity = '1' WHERE id = '" . $attendee2->id . "'";
			$wpdb->query($update_attendee2);
		}
	}
}

//Function to show an admin message if the main pages are not setup.
function event_espresso_activation_notice() {
	if (function_exists('admin_url')) {
		echo '<div class="error fade"><p><strong>' . __('Event Espresso must be configured. Go to', 'event_espresso') . ' <a href="' . admin_url('admin.php?page=event_espresso#page_settings') . '">' . __('the Organization Settings page', 'event_espresso') . '</a>  ' . __('to configure the plugin "Page Settings."', 'event_espresso') . '</strong></p></div>';
	} else {
		echo '<div class="error fade" ><p><strong>' . __('Event Espresso must be configured. Go to', 'event_espresso') . ' <a href="' . admin_url('admin.php?page=event_espresso#page_settings') . '">' . __('the Organization Settings page', 'event_espresso') . '</a> ' . __('to configure the plugin "Page Settings."', 'event_espresso') . '</strong></p></div>';
	}
}

//Function to show an admin message if registration id's are missing.
function event_espresso_registration_id_notice() {
	if (function_exists('admin_url')) {
		echo '<div class="error fade"><p><strong>' . __('Event Espresso attendee data needs to be updated. Please visit the ', 'event_espresso') . ' <a href="' . admin_url('admin.php?page=support#attendee_data') . '">' . __('Support page', 'event_espresso') . '</a>  ' . __('to configure update the attendee information.', 'event_espresso') . '</strong></p></div>';
	} else {
		echo '<div class="error fade"><p><strong>' . __('Event Espresso attendee data needs to be updated. Please visit the ', 'event_espresso') . ' <a href="' . admin_url('admin.php?page=support#attendee_data') . '">' . __('Support page', 'event_espresso') . '</a>  ' . __('to configure update the attendee information.', 'event_espresso') . '</strong></p></div>';
	}
}

//This function returns a dropdown of secondary events
if (!function_exists('espresso_secondary_events_dd')) {

	function espresso_secondary_events_dd($current_value = '0', $allow_overflow = 'N') {
		global $wpdb;
		$sql = "SELECT id, event_name FROM " . EVENTS_DETAIL_TABLE;
		$sql .= " WHERE event_status = 'S' ";

		$events = $wpdb->get_results($sql);
		$num_rows = $wpdb->num_rows;
		//return print_r( $events );
		if ($num_rows > 0) {
			$field = '<select name="overflow_event_id" id="overflow_event_id">\n';
			$field .= '<option value="0">Select an event</option>';

			foreach ($events as $event) {
				$selected = $event->id == $current_value ? 'selected="selected"' : '';
				$field .= '<option ' . $selected . ' value="' . $event->id . '">' . $event->event_name . '</option>\n';
			}
			$field .= "</select>";
			$values = array(array('id' => 'Y', 'text' => __('Yes', 'event_espresso')), array('id' => 'N', 'text' => __('No', 'event_espresso')));
			$html = '<p><label>' . __('Assign a Waitlist Event? ', 'event_espresso') . '</label> ' . select_input('allow_overflow', $values, $allow_overflow) . ' <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=secondary_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a> </p>' .
							'<p class="inputunder"><label>' . __('Overflow Event', 'event_espresso') . ': </label><br />' . $field . '</p>';

			return $html;
		}
	}

}

// Function espresso_db_dropdown creates a drop-down box
// by dynamically querying ID-Name pair from a lookup table
//
// Parameters:
// intIdField = Integer "ID" field of table, usually the primary key
// strNameField = Name field that user picks as a value
// strTableName = Name of MySQL table containing intIDField and strNameField
// strOrderField = Which field you want results sorted by
// strMethod = Sort as asc=ascending (default) or desc for descending
// $current_value = The current select value
// $strDDName = The name of the field
//
//
// Returns:
// HTML Drop-Down Box Mark-up Code
function espresso_db_dropdown($intIdField, $strNameField, $strTableName, $strOrderField, $current_value, $strMethod = "desc", $strDDName = "") {
	global $wpdb;

	$strQuery = "select $intIdField, $strNameField from $strTableName order by $strOrderField $strMethod";
	//$rsrcResult = mysql_query($strQuery);
	$data = $wpdb->get_results($strQuery, ARRAY_A);
	//print_r($data);
	$strDDName = $strDDName != "" ? $strDDName : $strNameField;
	if ($wpdb->num_rows > 0) {
		echo '<select name="' . $strDDName . '">';
		echo '<option value="">' . __('Select Value', 'event_espresso') . '</option>';

		/*		 * * loop over the results ** */
		foreach ($data as $row) {
			/*			 * * create the options ** */
			echo '<option value="' . $row["$intIdField"] . '"';
			if ($row["$intIdField"] == $current_value) {
				echo ' selected';
			}
			echo '>' . stripslashes_deep($row["$strNameField"]) . '</option>' . "\n";
		}
		echo "</select>";
	} else {
		_e('No Results', 'event_espresso');
	}
}

function espresso_email_message($id) {
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM " . EVENTS_EMAIL_TABLE . " WHERE id =" . $id);
	foreach ($results as $result) {
		$email_id = $result->id;
		$email_name = stripslashes_deep($result->email_name);
		$email_subject = stripslashes_deep($result->email_subject);
		$email_text = stripslashes_deep($result->email_text);
	}
	$email_data = array('id' => $id, 'email_name' => $email_name, 'email_subject' => $email_subject, 'email_text' => $email_text);
	return $email_data;
}

/**
 * Echoes out the HTML for the even category dropdown. 
 * Returns true if its outputted, false if we decdied not to output it.
 * @global type $wpdb
 * @param type $current_value
 * @return boolean
 */
function espresso_category_dropdown($current_value = '') {
	global $wpdb;

	$strQuery = "select id, category_name from " . EVENTS_CATEGORY_TABLE;
	$data = $wpdb->get_results($strQuery, ARRAY_A);
	//print_r($data);

	if ($wpdb->num_rows > 0) {
		echo '<select name="category_id">';
		echo '<option value="">' . __('Show All Categories', 'event_espresso') . '</option>';

		/*		 * * loop over the results ** */
		foreach ($data as $row) {
			/*			 * * create the options ** */
			echo '<option value="' . $row["id"] . '"';
			if ($row["id"] == $current_value) {
				echo ' selected';
			}
			echo '>' . stripslashes_deep($row["category_name"]) . '</option>' . "\n";
		}
		echo "</select>";
		return true;
	} else {
		return false;
	}
}

//This function grabs the event categories.
//@param optional $event_id = pass the event id to get the categories assigned to the event.
function event_espresso_list_categories($event_id = 0) {
	global $wpdb;
	$event_categories = $wpdb->get_results("SELECT * FROM " . EVENTS_CATEGORY_TABLE);

	foreach ($event_categories as $category) {
		$category_id = $category->id;
		$category_name = $category->category_name;

		$in_event_categories = $wpdb->get_results("SELECT * FROM " . EVENTS_CATEGORY_REL_TABLE . " WHERE event_id='" . $event_id . "' AND cat_id='" . $category_id . "'");
		if ($wpdb->num_rows > 0) {
			echo '<ul>';
			foreach ($in_event_categories as $in_category) {
				$in_event_category = $in_category->cat_id;
			}
			echo $in_event_category != 0 ? '<li>' . $category_name . '</li>' : '';
			echo '</ul>';
		} else {
			return 0;
		}
	}
}

//These functions were movedto main.php on 08-30-2011 by Seth

/* //Retrives the attendee count based on an attendee ids
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
  } */

//End

function espresso_attendees_by_month_dropdown($current_value = '') {
	global $wpdb;

	$strQuery = "select id, date from " . EVENTS_ATTENDEE_TABLE . " group by YEAR(date), MONTH(date) ";
	//$rsrcResult = mysql_query($strQuery);
	$data = $wpdb->get_results($strQuery, ARRAY_A);
	//print_r($data);

	if ($wpdb->num_rows > 0) {
		echo '<select name="month_range">';
		echo '<option value="">' . __('Select a Month/Year', 'event_espresso') . '</option>';

		/*		 * * loop over the results ** */
		foreach ($data as $row) {
			/*			 * * create the options ** */
			echo '<option value="' . event_espresso_no_format_date($row["date"], $format = 'Y-m-d') . '"';
			if (event_espresso_no_format_date($row["date"], $format = 'Y-m-d') == $current_value) {
				echo ' selected';
			}
			echo '>' . event_espresso_no_format_date($row["date"], $format = 'F  Y') . '</option>' . "\n";
		}
		echo "</select>";
	} else {
		_e('No Results', 'event_espresso');
	}
}

//This function installs the required pages
function espresso_create_default_pages() {
	global $wpdb, $org_options;
	$default_pages = array('Event Registration', 'Thank You', 'Registration Cancelled', 'Transactions');
	$existing_pages = get_pages();
	foreach ($existing_pages as $page) {
		$temp[] = $page->post_title;
	}
	$pages_to_create = array_diff($default_pages, $temp);
	foreach ($pages_to_create as $new_page_title) {

		// Create post object
		$my_post = array();
		$my_post['post_title'] = $new_page_title;
		//$my_post['post_content'] = 'This is my '.$new_page_title.' page.';
		$my_post['post_status'] = 'publish';
		$my_post['post_type'] = 'page';
		$my_post['comment_status'] = 'closed';
		// Insert the post into the database
		//$result = wp_insert_post( $my_post );

		switch ($new_page_title) {
			case 'Event Registration':
				if (empty($org_options['event_page_id'])) {
					$my_post['post_content'] = '[ESPRESSO_EVENTS]';
					$event_page_id = wp_insert_post($my_post);
					$org_options['event_page_id'] = $event_page_id;
				}
				break;
			case 'Thank You':
				if (empty($org_options['return_url'])) {
					$my_post['post_content'] = '[ESPRESSO_PAYMENTS]';
					$return_url = wp_insert_post($my_post);
					$org_options['return_url'] = $return_url;
				}
				break;
			case 'Registration Cancelled':
				if (empty($org_options['cancel_return'])) {
					$my_post['post_content'] = 'You have cancelled your registration.<br />[ESPRESSO_CANCELLED]';
					$cancel_return = wp_insert_post($my_post);
					$org_options['cancel_return'] = $cancel_return;
				}
				break;
			case 'Transactions':
				if (empty($org_options['notify_url'])) {
					$my_post['post_content'] = '[ESPRESSO_TXN_PAGE]';
					$notify_url = wp_insert_post($my_post);
					$org_options['notify_url'] = $notify_url;
				}
				break;
		}
	}
	update_option('events_organization_settings', $org_options);
	//print_r($org_options);
}

if (!function_exists('espresso_event_list_attendee_title')) {

	function espresso_event_list_attendee_title($event_id = NULL) {
		global $wpdb;

		$events = $wpdb->get_results("SELECT event_name FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $event_id . "' ");

		foreach ($events as $event) {
			$title_event_name = stripslashes_deep($event->event_name);
		}

		$content = $title_event_name;
		$content .= ' | ';
		$content .= 'ID: ' . $event_id;
		$content .= ' | ';
		$content .= espresso_event_time($event_id, 'start_date_time');
		return $content;
	}

}

function espresso_payment_reports($atts) {
	global $wpdb;
	extract($atts);
	$sql = "SELECT SUM(a.amount_pd) quantity FROM " . EVENTS_ATTENDEE_TABLE . " a WHERE a.quantity >= 1 AND (a.payment_status='Completed' OR a.payment_status='Refund') AND a.event_id = '" . $event_id . "' ";
	$payments = $wpdb->get_results($sql, ARRAY_A);
	$total = 0;
	if ($wpdb->num_rows > 0 && $wpdb->last_result[0]->quantity != NULL) {
		$total = $wpdb->last_result[0]->quantity;
	}
	//echo $sql;
	switch ($type) {
		case 'total_payments':
			return $total;
			break;
	}
}

function espresso_performance($visible = false) {

	$stat = sprintf('%d queries in %.3f seconds, using %.2fMB memory', get_num_queries(), timer_stop(0, 3), memory_get_peak_usage() / 1024 / 1024
	);

	echo $visible ? $stat : "<!-- {$stat} -->";
}

add_action('wp_footer', 'espresso_performance', 20);

function espresso_files_in_uploads() {
	
	$fileinfo = '';
	if ( is_dir( EVENT_ESPRESSO_TEMPLATE_DIR )) {
	    $dir = new RecursiveDirectoryIterator( EVENT_ESPRESSO_TEMPLATE_DIR );
	    $files = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );
		// Maximum depth is 1 level deeper than the base folder
		$files->setMaxDepth(1);
	    foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				$fileinfo .= sprintf( "Dir:  %s\n", $file->getFilename() );
			} elseif ( $file->isFile() ) {
				$fileinfo .= sprintf( "File: %s/%s\n", $files->getSubPath(), $file->getFilename() );
			}
	    }
	}	
	echo "\r\n\n<!--Event Espresso Template Files:\r\n\n{$fileinfo}\n-->\r\n";
	
}
add_action('wp_footer', 'espresso_files_in_uploads', 20);


function espresso_admin_performance($show = 0) {
	if ($show == 0)
		return;

	global $wpdb, $EZSQL_ERROR;
	$out = '';
	$total_time = 0;

	if (!empty($wpdb->queries)) {
		$show_many = isset($_GET['debug_queries']);

		if ($wpdb->num_queries > 500 && !$show_many)
			$out .= "<p>" . sprintf(__('There are too many queries to show easily! <a href="%s">Show them anyway</a>', 'event_espresso'), add_query_arg('debug_queries', 'true')) . "</p>";

		$out .= '<ol class="wpd-queries">';
		$counter = 0;

		foreach ($wpdb->queries as $q) {
			list($query, $elapsed, $debug) = $q;

			$total_time += $elapsed;

			if (++$counter > 500 && !$show_many)
				continue;

			$debug = explode(', ', $debug);
			$debug = array_diff($debug, array('require_once', 'require', 'include_once', 'include'));
			$debug = implode(', ', $debug);
			$debug = str_replace(array('do_action, call_user_func_array'), array('do_action'), $debug);
			$query = nl2br(esc_html($query));

			$out .= "<li>$query<br/><div class='qdebug'>$debug <span>#{$counter} (" . number_format(sprintf('%0.1f', $elapsed * 1000), 1, '.', ',') . "ms)</span></div></li>\n";
		}
		$out .= '</ol>';
	} else {
		if ($wpdb->num_queries == 0)
			$out .= "<p><strong>" . __('There are no queries on this page.', 'event_espresso') . "</strong></p>";
		else
			$out .= "<p><strong>" . __('SAVEQUERIES must be defined to show the query log.', 'event_espresso') . "</strong></p>";
	}

	if (!empty($EZSQL_ERROR)) {
		$out .= '<h3>' . __('Database Errors', 'event_espresso') . '</h3>';
		$out .= '<ol class="wpd-queries">';

		foreach ($EZSQL_ERROR as $e) {
			$query = nl2br(esc_html($e['query']));
			$out .= "<li>$query<br/><div class='qdebug'>{$e['error_str']}</div></li>\n";
		}
		$out .= '</ol>';
	}

	$heading = '';
	if ($wpdb->num_queries)
		$heading .= '<h2><span>Total Queries:</span>' . number_format($wpdb->num_queries) . "</h2>\n";
	if ($total_time)
		$heading .= '<h2><span>Total query time:</span>' . number_format(sprintf('%0.1f', $total_time * 1000), 1) . " ms</h2>\n";
	if (!empty($EZSQL_ERROR))
		$heading .= '<h2><span>Total DB Errors:</span>' . number_format(count($EZSQL_ERROR)) . "</h2>\n";

	$out = $heading . $out;

	echo $out;
}

add_filter('admin_footer_text', 'espresso_admin_performance');

function espresso_admin_footer() {
	echo 'Event Registration and Ticketing Powered by <a href="http://eventespresso.com/?utm_source=ee_plugin_admin&utm_medium=link&utm_content=Event+Registration+and+Ticketing+Powered+by+ee_version_'.EVENT_ESPRESSO_VERSION .'&utm_campaign=espresso_admin_footer" title="Event Registration Powered by Event Espresso" target="_blank">' . EVENT_ESPRESSO_POWERED_BY . '</a>';
}

add_filter('admin_footer_text', 'espresso_admin_footer');

function espresso_choose_layout($main_post_content = '', $sidebar_content = '', $center_metabox_content = '') {
	global $wp_version;
	if (version_compare($wp_version, '3.3.2', '>')) {
		espresso_post_3_4_layout($main_post_content, $sidebar_content, $center_metabox_content);
	} else {
		espresso_pre_3_4_layout($main_post_content, $sidebar_content, $center_metabox_content);
	}
}

function espresso_post_3_4_layout($main_post_content = '', $sidebar_content = '', $center_metabox_content = '') {
	?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<!-- main post stuff here -->
				<?php echo $main_post_content; ?>
			</div> <!-- post-body-content -->
			<div id="postbox-container-1" class="postbox-container">
				<!-- sidebar stuff here -->
				<?php echo $sidebar_content; ?>
			</div> <!-- postbox-container-1 -->
			<div id="postbox-container-2" class="postbox-container">
				<!-- main column metaboxes under the post content here -->
				<?php echo $center_metabox_content; ?>
			</div> <!-- postbox-container-2 -->
		</div> <!-- post-body -->
	</div> <!-- poststuff -->
	<?php
}

function espresso_pre_3_4_layout($main_post_content = '', $sidebar_content = '', $center_metabox_content = '') {
	?>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<!-- sidebar stuff here -->
				<?php echo $sidebar_content; ?>
			</div> <!-- side-sortables -->
		</div> <!-- side-info-column -->
		<div id="post-body">
			<div id="post-body-content">
				<!-- post stuff here -->
				<?php echo $main_post_content; ?>
				<?php echo $center_metabox_content; ?>
			</div> <!-- post-body-content -->
		</div> <!-- post-body -->
	</div> <!-- poststuff -->
	<?php
}

/**
 * [espresso_get_user_questions]
 * used to get the questions for a given user_id (and system questions only if indicated)
 * 
 * @param  int  $user_id user_id to be retrieved.
 * @param bool $use_filters if true (default) filters will be run.  If false then no filters are run.
 * @param bool $num used to indicate that this is being used in the context of retrieving the number of rows (if true).
 * @return array|bool  returns an array of question objects if there are values and false if none.
 */
function espresso_get_user_questions($user_id = null, $question_id = null, $use_filters = true, $num = false, $limit = null ) {
	global $wpdb;

	//first let's satisfy the query.
	$sql = "SELECT * FROM " . EVENTS_QUESTION_TABLE . " AS q ";
	if ( !empty($user_id) ) {
  		$sql .= $use_filters ? apply_filters('espresso_get_user_questions_where', $wpdb->prepare(" WHERE (q.wp_user = '%d' OR q.wp_user = '%d') ", 0, 1), $user_id, $num) : $wpdb->prepare(" WHERE (q.wp_user = '%d' OR q.wp_user = '%d') ", 0, 1);
  	}

  	if ( !empty($question_id) ) {
		$sql .= $wpdb->prepare(" WHERE q.id = '%d' ", $question_id);
	}

	$sql .= " ORDER BY sequence, id ASC ";

	$questions = $wpdb->get_results( $sql );
	
	return ( $use_filters) ? apply_filters('espresso_get_user_questions_questions', $questions, $user_id, $num) : $questions;
}

function espresso_get_user_questions_for_group( $group_id, $user_id = null, $use_filters = true ) {
	global $wpdb;
	$setup_questions = $q_attached = $remaining_questions = array();

	$sql = " SELECT q.id, q.question, qgr.id as rel_id, q.system_name, qg.system_group, qg.id AS group_id ";
	$sql .= " FROM " . EVENTS_QUESTION_TABLE . " AS q ";
    $sql .= " JOIN " . EVENTS_QST_GROUP_REL_TABLE . " AS qgr ";
    $sql .= " on q.id = qgr.question_id ";
    $sql .= " LEFT JOIN " . EVENTS_QST_GROUP_TABLE . " AS qg ";
    $sql .= " on qg.id = qgr.group_id ";
    //$sql .= $use_filters ? apply_filters('espresso_get_user_questions_for_group', " WHERE q.wp_user = '0' OR q.wp_user = '1' ", $group_id, $user_id) : " WHERE q.wp_user = '0' OR q.wp_user = '1' ";
    $sql .= " WHERE qgr.group_id = %d " ;
    $sql .= " ORDER BY q.sequence, q.id ASC ";

    $questions = $wpdb->get_results($wpdb->prepare($sql, $group_id) );

    foreach ( $questions as $question ) {
  		$q_attached[] = $question->id;
    	if ( $question->group_id == $group_id ) {
    		$setup_questions['questions_in_group'][] = $question;
    	} else {
    		$remaining_questions[] = $question;
    	}
    }

    //okay it's possible that we'll have questions not included in this group but we still need to display them.
	$e_sql_where = $use_filters ? apply_filters('espresso_get_user_questions_for_group_extra_attached', " WHERE ( q.wp_user = '0' OR q.wp_user = '1') ", $group_id, $user_id) :  " AND ( q.wp_user = '0' OR q.wp_user = '1') ";
	$e_sql_where .= ( !empty($q_attached) ) ? " AND q.id NOT IN (" . implode(',', $q_attached) . ")" : '';
    $e_sql = "SELECT q.id, q.question, q.system_name FROM " . EVENTS_QUESTION_TABLE . " AS q " . $e_sql_where;
    $ex_questions = $wpdb->get_results($e_sql);
    $remaining_questions = array_merge( $remaining_questions, $ex_questions);
 

	$setup_questions['remaining_questions'] = $remaining_questions;
	
    return $setup_questions;
}

/**
 * [espresso_set_default_user_questions_groups]
 * This function is used when a user doesn't have system questions or groups associated with their id (when there are bugs from previous versions).  This will take care of fixing that by saving system questions/groups for their user_id.  NOTE, if there are no system questions then it means that the system group has not been set up for this user either. 
 * @param  int $user_id          
 * @param array $return_type whether we should return the new question groups or the questions
 * @return array returns the new array of question objects (for the given user
 */
function espresso_set_default_user_questions_groups($user_id, $return_type = 'questions') {
	global $wpdb;
	$user_id = (int) $user_id;

	//let's check and see if there are any system groups first.
	
}

/**
 * utility function to get user question groups.
 * @param  int $user_id
 * @param bool $use_filters if true (default) filters will be run.  If false then no filters are run.
 * @param bool $num used to indicate that this is being used in the context of retrieving the number of rows (if true).
 * @return array          array of group objects
 */
function espresso_get_user_question_groups($user_id = null, $use_filters = true, $num = false, $group_id = null ) {
	global $wpdb;
	$sql = "SELECT * FROM " . EVENTS_QST_GROUP_TABLE . " AS qg ";
	if ( !empty($user_id) ) {
  		$sql .= $use_filters ? apply_filters('espresso_get_user_question_groups_where', $wpdb->prepare( " WHERE (qg.wp_user = '%d' OR qg.wp_user = '%d' ) ", 0, 1 ), $user_id, $num) : $wpdb->prepare( " WHERE (qg.wp_user = '%d' OR qg.wp_user = '%d' ) ", 0, 1);
  	}

  	if ( !empty($group_id) ) {
  		$sql .= $wpdb->prepare(" WHERE qg.id = '%d' ", $group_id);
  	} 

	$sql .= ( empty($group_id) ) ? " ORDER BY group_order " : " ORDER BY id ASC ";

	$groups = $wpdb->get_results( $sql );
	
	return $use_filters ? apply_filters('espresso_get_user_groups_groups', $groups, $user_id, $num) : $groups;		
}

function espresso_get_question_groups_for_event( $existing_question_groups = array(), $limit = null, $use_filters = true, $event ) {
	global $wpdb;
	$event_groups = array();
	$selected = $unselected = array();
	$sql = "SELECT qg.* FROM " . EVENTS_QST_GROUP_TABLE . " AS qg ";
	$sql .= $use_filters ? apply_filters('espresso_get_question_groups_for_event_where', " WHERE (qg.wp_user = '0' OR qg.wp_user = '1' ) ", $existing_question_groups, $event ) : " WHERE (qg.wp_user = '0' OR qg.wp_user = '1' ) ";
	$sql .= " GROUP BY qg.id ORDER BY qg.system_group, qg.group_order "; 

	$question_groups = $wpdb->get_results( $sql );

	//let's setup data.
	$count_row = 0;  
	if ( count($question_groups) > 0 ) {
		foreach ( $question_groups  as $group ) {
			if ( $group->system_group == 1 || in_array($group->id, $existing_question_groups) )
				$selected[] = $group;
			else
				$unselected[] = $group;
			$count_row++;
		}
		$event_groups = array_merge($selected, $unselected);
		$event_groups = empty($limit) ? $event_groups : array_slice( $event_groups, 0 , 2 );
	}

	return ($use_filters) ? apply_filters('espresso_get_question_groups_for_event_groups', $event_groups, $existing_question_groups, $event) : $event_groups;

}

if (!function_exists('espresso_check_ssl')) {

	function espresso_check_ssl() {
		$home = str_replace("http://", "https://", home_url());
		@$handle = fopen($home, "r");
		if(empty($handle)){ 
			return FALSE;
		}
		return TRUE;
	}

}

//EE4 Available Notice
function espresso_ee4_admin_notice() {
	global $current_user;
	$user_id = $current_user->ID;
	// Check that the user hasn't already clicked to ignore the message and that they're an admin
	if ( ! get_user_meta($user_id, 'espresso_ee4_admin_notice_ignore_notice') && current_user_can( 'activate_plugins' ) ) {
		$hide_url = add_query_arg( 'espresso_ee4_admin_notice_nag_ignore', '0' );
		$text = sprintf( __('Just a friendly reminder to check out <a href="%1$s" target="_blank">Event Espresso 4</a>. While we are currently migrating all of the features from EE3 to EE4, it is our newest brew. | <a href="%2$s">Hide this message</a>', 'event_espresso'), 'https://wordpress.org/plugins/event-espresso-decaf/', $hide_url );

		echo '<div class="updated"><h4>'.__('Event Espresso 4 Now Available!', 'event_espresso').'</h4><p>' . $text . '</p></div>';
	}
}

add_action('admin_init', 'espresso_ee4_admin_notice_nag_ignore');

function espresso_ee4_admin_notice_nag_ignore() {
	global $current_user;
    $user_id = $current_user->ID;
    /* If user clicks to ignore the notice, add that to their user meta */
    if ( isset($_GET['espresso_ee4_admin_notice_nag_ignore']) && '0' == $_GET['espresso_ee4_admin_notice_nag_ignore'] ) {
		add_user_meta($user_id, 'espresso_ee4_admin_notice_ignore_notice', 'true', true);
	}
}


/**
*
* Update notifications
*
**/

//Setup default values
/*global $ee_pue_checkPeriod, $lang_domain, $ee_pue_option_key;
$ee_pue_checkPeriod = 1;
$lang_domain = 'event_espresso';
$ee_pue_option_key = 'site_license_key';*/

add_action('action_hook_espresso_core_update_api', 'ee_core_load_pue_update');
function ee_core_load_pue_update() {
	global $org_options, $espresso_check_for_updates;
	
	if ( $espresso_check_for_updates == false )
		return;

	$ueip_optin = get_option('ee_ueip_optin');
	$ueip_has_notified = isset($_POST['ueip_optin']) ? TRUE : get_option('ee_ueip_has_notified');

	//has optin been selected for datacollection?
	$espresso_data_optin = !empty($ueip_optin) ? $ueip_optin : NULL;

	if ( empty($ueip_has_notified) ) {
		add_action('admin_notices', 'espresso_data_collection_optin_notice', 10 );
		add_action('admin_enqueue_scripts', 'espresso_data_collection_enqueue_scripts', 10 );
		add_action('wp_ajax_espresso_data_optin', 'espresso_data_optin_ajax_handler', 10 );
		update_option('ee_ueip_optin', 'yes');
		$espresso_data_optin = 'yes';
	}

	//let's prepare extra stats
	$extra_stats = array();

	//only collect extra stats if the plugin user has opted in.
	if ( !empty($espresso_data_optin) && $espresso_data_optin == 'yes' ) {
		//let's only setup extra data if transient has expired
		if ( false === ( $transient = get_transient('ee_extra_data') ) ) {
			//active gateways
			$active_gateways = get_option('event_espresso_active_gateways');
			if ( !empty($active_gateways ) ) {
				foreach ( (array) $active_gateways as $gateway => $ignore ) {
					$extra_stats[$gateway . '_gateway_active'] = 1;
				}
			}

			//MER active? 1 if yes. not set if not.
			$active_plugins = get_option('active_plugins');
			if ( preg_match('/espresso-multi-registration/', implode(',', $active_plugins ) ) )
				$extra_stats['MER_active'] = 1;

			//calendar active? considered active if the calendar page has been loaded in the past week (we use the espresso_calendar shortcode for this check)
			//if it is active (meeting the criteria), we send the timestamp.  if it isn't then we dont' set.
			$active_calendar = get_option('uxip_ee_calendar_active');
			if ( strtotime('+ 1week', (int) $active_calendar) >= time() ) {
				$extra_stats['calendar_active'] = $active_calendar;
			}


			//ticketing addon in use?  considered active if "espresso_ticket_launch" has been called with the corresponding _REQUEST var that triggers ticket generation.
			//if it is active we send the timestamp for the last time a ticket was generated.  If NOT active we don't set.
			$active_ticketing = get_option('uxip_ee_ticketing_active');
			if ( !empty( $active_ticketing ) )
				$extra_stats['ticketing_active'] = $active_ticketing;

			
			//REM active? if there are any recurring events present then its in use.
			//if IS active then we return the count of recurring events.
			$active_rem = get_option('uxip_ee_rem_active');
			if ( !empty( $active_rem ) )
				$extra_stats['rem_active'] = $active_rem;


			//seating chart active?  if there are any seating charts attached to an even then its considered active and we'll send along the count of seating charts in use.  Otherwise nothing is sent.
			$active_sc = get_option('uxip_ee_seating_chart_active');
			if ( !empty( $active_sc ) )
				$extra_stats['seating_chart_active'] = $active_sc;

			//member only events being run?
			$member_only_events = get_option('uxip_ee_members_events');
			if ( !empty( $member_only_events ) )
				$extra_stats['member_only_events'] = $member_only_events;


			//what is the current active theme?
			$active_theme = get_option('uxip_ee_active_theme');
			if ( !empty( $active_theme ) )
				$extra_stats['active_theme'] = $active_theme;

			//event info regarding an all event count and all "active" event count
			$all_events_count = get_option('uxip_ee_all_events_count');
			if ( !empty( $all_events_count ) )
				$extra_stats['all_events_count'] = $all_events_count;
			$active_events_count = get_option('uxip_ee_active_events_count');
			if ( !empty( $active_events_count ) )
				$extra_stats['active_events_count'] = $active_events_count;

			//phpversion checking
			$extra_stats['phpversion'] = function_exists('phpversion') ? phpversion() : 'unknown';


			//set transient
			set_transient( 'ee_extra_data', $extra_stats, WEEK_IN_SECONDS );
		}
	}

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file 
			require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
			$api_key = isset($org_options['site_license_key']) ? $org_options['site_license_key'] : '';
			$host_server_url = 'http://eventespresso.com'; //this needs to be the host server where plugin update engine is installed.
			$plugin_slug = array(
				'free' => array('L' => 'event-espresso-free'),
				'premium' => array('P' => 'event-espresso'),
				'prerelease' => array('B' => 'event-espresso-pr'),
				); 
			$options = array(
			//	'optionName' => '', //(optional) - used as the reference for saving update information in the clients options table.  Will be automatically set if left blank.
				'apikey' => $api_key, //(required), you will need to obtain the apikey that the client gets from your site and then saves in their sites options table (see 'getting an api-key' below)
				'lang_domain' => 'event_espresso', //(optional) - put here whatever reference you are using for the localization of your plugin (if it's localized).  That way strings in this file will be included in the translation for your plugin.
				'checkPeriod' => '12', //(optional) - use this parameter to indicate how often you want the client's install to ping your server for update checks.  The integer indicates hours.  If you don't include this parameter it will default to 12 hours.
				'option_key' => 'site_license_key', //this is what is used to reference the api_key in your plugin options.  PUE uses this to trigger updating your information message whenever this option_key is modified.
				'options_page_slug' => 'event_espresso',
				'plugin_basename' => EVENT_ESPRESSO_WPPLUGINPATH,
				'use_wp_update' => TRUE, //if TRUE then you want FREE versions of the plugin to be updated from WP
				'extra_stats' => $extra_stats,
				'turn_on_notices_saved' => true
			);
			$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
		}
}


/**
 * The purpose of this function is to display information about Event Espresso data collection and a optin selection for extra data collecting by users.
 * @return string html.
 */
 function espresso_data_collection_optin_text() {
	 echo '<h4>'.__('User eXperience Improvement Program (UXIP)', 'event_espresso').'</h4>';
	 echo sprintf( __('%sPlease help us make Event Espresso better and vote for your favorite features.%s With this version of Event Espresso a feature, called the %sUser eXperience Improvement Program (UXIP)%s, has been implemented to automatically send information to us about how you use our products and services, and support-related data. We use this information to improve our products and features, that you use most often, and to help track problems. Participation in the program is enabled by default, and the end results are software improvements to better meet the needs of our customers. The data we collect will never be sold, traded, or misused in any way. %sPlease see our %sPrivacy Policy%s for more information. You can choose to not be part of the solution and opt-out of this program by changing the %sEvent Espresso > General Settings > UXIP Settings%s within your WordPress General Settings.', 'event_espresso'), '<em>', '</em><br />','<a href="http://eventespresso.com/about/user-experience-improvement-program-uxip/" target="_blank">','</a>','<br><br>','<a href="http://eventespresso.com/about/privacy-policy/" target="_blank">','</a>','<a href="admin.php?page=event_espresso#ueip_optin">','</a>' );
}

function espresso_data_collection_optin_notice() {
	$ueip_has_notified = get_option('ee_ueip_has_notified');
	?>
	<div class="updated data-collect-optin" id="espresso-data-collect-optin-container">
		<p><?php echo espresso_data_collection_optin_text(); ?></p>
		<div id="data-collect-optin-options-container">
			<span style="display: none" id="data-optin-nonce"><?php echo wp_create_nonce('ee-data-optin'); ?></span>
			<?php
			if ( empty($ueip_has_notified) ) {
				echo '<a href="admin.php?page=event_espresso#ueip_optin">'.__('Opt-out now?', 'event_espresso').'</a>';
			}
			?>
			<button class="button-secondary data-optin-button" value="no"><?php _e('Dismiss', 'event_espresso'); ?></button>
			<!--<button class="button-primary data-optin-button" value="yes"><?php _e('Yes! I\'m In', 'event_espresso'); ?></button>-->
			<div style="clear:both"></div>
		</div>
	</div>
	<?php
}


	
/**
 * enqueue scripts/styles needed for data collection optin
 * @return void
 */
function espresso_data_collection_enqueue_scripts() {
	wp_register_script( 'ee-data-optin-js', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/ee-data-optin.js', array('jquery'), EVENT_ESPRESSO_VERSION, TRUE );
	wp_register_style( 'ee-data-optin-css', EVENT_ESPRESSO_PLUGINFULLURL . 'css/ee-data-optin.css', array(), EVENT_ESPRESSO_VERSION );

	wp_enqueue_script('ee-data-optin-js');
	wp_enqueue_style('ee-data-optin-css');
}


/**
 * This just handles the setting of the selected option for data optin via ajax
 * @return void
 */
function espresso_data_optin_ajax_handler() {

	//verify nonce
	if ( isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'ee-data-optin') ) exit();

	//made it here so let's save the selection
	$ueip_optin = isset( $_POST['selection'] ) ? $_POST['selection'] : 'no';

	//update_option('ee_ueip_optin', $ueip_optin);
	update_option('ee_ueip_has_notified', 1);
	exit();
}



/**
 * specific uxip tracking hooks for addons that are NOT restricted to is_admin() because we need to be able to hook into addon runtimes.
 */
require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/uxip-hooks.php';