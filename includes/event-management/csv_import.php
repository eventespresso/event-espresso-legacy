<?php

function csv_import() { ?>
	<h3>Event Import</h3>
	<ul>
		<li>
			<p><?php _e('This page is for importing your events from a comma separated file (CSV) directly into the the events database.  The limitation of this upload is that it does not support the extra questions, only the core event configuration.', 'event_espresso'); ?> </p>
			<ul>
				<li><?php _e('Please use Y where you want to say Yes and N where you want No.', 'event_espresso'); ?></li>
				<li><?php _e('Dates should be formatted YYYY-MM-DD (2009-07-04).', 'event_espresso'); ?></li>
				<li><?php _e('We have included a template file', 'event_espresso'); ?> <a href="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>events.csv"><?php _e('here', 'event_espresso'); ?></a> <?php _e('that I recommend you download and use.  It is very easy to work with it in excel, just remember to save it as a CSV and not excel sheet.', 'event_espresso'); ?></li>
				<li><?php _e('The file name should be events.csv in order for it to work. I will fix this issue later, I just wanted to get this working first.', 'event_espresso'); ?></li>
			</ul>
			<p><?php _e('One final note, you will see that the header row, fist column has a 0 while other rows have a 1.  This tells the upload to ignore rows that have the 0 identifier and only use rows with the 1.', 'event_espresso'); ?></p>
			<p><?php _e('This is the first pass at the uploader, but for those of you who have alot of events, particularly events that are similar in setup, this will be a time saver.', 'event_espresso'); ?></p>
			<?php
			espresso_uploader();
			if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
				load_events_to_db();
			}
			
			?>
		</li>
	</ul>
	<?php
}

/*
  espresso_uploader([int num_uploads [, arr file_types [, int file_size [, str upload_dir ]]]]);

  num_uploads = Number of uploads to handle at once.

  file_types = An array of all the file types you wish to use. The default is txt only.

  file_size = The maximum file size of EACH file. A non-number will results in using the default 1mb filesize.

  upload_dir = The directory to upload to, make sure this ends with a /
 */

function espresso_uploader($num_of_uploads = 1, $file_types_array = array("csv"), $max_file_size = 1048576, $upload_dir = WP_CONTENT_DIR . "/uploads/") {
	if (!is_numeric($max_file_size)) {
		$max_file_size = 1048576;
	}
	if (!isset($_POST["submitted"])) {
		$form = "<form action='admin.php?page=events&action=csv_import' method='post' enctype='multipart/form-data'><p>Upload files:</p><input type='hidden' name='submitted' value='TRUE' id='" . time() . "'><input name='action' type='hidden' value='csv_import' /><input type='hidden' name='MAX_FILE_SIZE' value='" . $max_file_size . "'>";
		for ($x = 0; $x < $num_of_uploads; $x++) {
			$form .= "<p><font color='red'>*</font><input type='file' name='file[]'>";
		}
		$form .= "<input class='button-primary' type='submit' value='Upload File & Add Event(s)'></p><p><font color='red'>*</font>Maximum file name length (minus extension) is 15 characters. Anything over that will be cut to only 15 characters. Valid file type(s): ";
		for ($x = 0; $x < count($file_types_array); $x++) {
			if ($x < count($file_types_array) - 1) {
				$form .= $file_types_array[$x] . ", ";
			} else {
				$form .= $file_types_array[$x] . ".</p>";
			}
		}
		$form .= "</form>";
		echo($form);
	} else {
		foreach ($_FILES["file"]["error"] as $key => $value) {
			if ($_FILES["file"]["name"][$key] != "") {
				if ($value == UPLOAD_ERR_OK) {
					$origfilename = $_FILES["file"]["name"][$key];
					$filename_array = explode(".", $_FILES["file"]["name"][$key]);
					$filenameext = $filename_array[count($filename_array) - 1];
					unset($filename_array[count($filename_array) - 1]);
					$filename = implode(".", $filename_array);
					$filename = substr($filename, 0, 15) . "." . $filenameext;
					$file_ext_allow = FALSE;
					for ($x = 0; $x < count($file_types_array); $x++) {
						if ($filenameext == $file_types_array[$x]) {
							$file_ext_allow = TRUE;
						}
					}
					if ($file_ext_allow) {
						if ($_FILES["file"]["size"][$key] < $max_file_size) {
							if (move_uploaded_file($_FILES["file"]["tmp_name"][$key], $upload_dir . $filename)) {
								echo("<p>File uploaded successfully. - <a href='" . $upload_dir . $filename . "' target='_blank'>" . $filename . "</a></p>");
							} else {
								echo($origfilename . " was not successfully uploaded<br />");
							}
						} else {
							echo($origfilename . " was too big, not uploaded<br />");
						}
					} else {
						echo($origfilename . " had an invalid file extension, not uploaded<br />");
					}
				} else {
					echo($origfilename . " was not successfully uploaded<br />");
				}
			}
		}
	}
}

function load_events_to_db() {
	
	global $wpdb, $current_user;

	$csvfile = WP_CONTENT_DIR . "/uploads/events.csv";

	if (!function_exists('espresso_member_data')) {
		$current_user->ID = 1;
	}


	if (!file_exists($csvfile)) {
		echo "File not found. Make sure you specified the correct path.\n";
		exit;
	}

	$file_handle = fopen($csvfile, "r");

	if (!$file_handle) {
		echo "Error opening data file.\n";
		exit;
	}

	$file_size = filesize($csvfile);
	if (empty($file_size)) {
		echo "File is empty.\n";
		exit;
	}

	$num_of_imported_events = 0;
	while ($strings = fgetcsv($file_handle)) {
		
		$question_groups_rs = $wpdb->get_results("select * from " . EVENTS_QST_GROUP_TABLE . " where wp_user = " . $current_user->ID . " and system_group = 1");
		$question_groups_ar = array();
		foreach ($question_groups_rs as $question_group) {
			$question_groups_ar[] = $question_group->id;
		}
		$question_groups = serialize($question_groups_ar);

		//echo "valid is :'".$valid."'";
		if (array_key_exists('2', $strings)) {
			//echo "The  element is in the array";
			$skip = $strings[0];
			if ($skip >= "1") {
				++$num_of_imported_events;
				// Event meta info -
				$event_meta = array();
				$event_meta['default_payment_status'] = "";
				$event_meta['venue_id'] = '';
				$event_meta['additional_attendee_reg_info'] = 1;
				$event_meta['add_attendee_question_groups'] = unserialize($question_groups);
				$event_meta['date_submitted'] = date("Y-m-d H:i:s");
				$event_meta = serialize($event_meta);
				
				$strings_sql_array = array(
					'event_name' => sanitize_text_field($strings[1]), //event_name
					'event_desc' => $strings[2],
					'address' => sanitize_text_field($strings[3]),
					'address2' => sanitize_text_field($strings[4]),
					'city' => sanitize_text_field($strings[5]),
					'state' => sanitize_text_field($strings[6]),
					'country' => sanitize_text_field($strings[7]),
					'zip' => sanitize_text_field($strings[8]),
					'phone' => sanitize_text_field($strings[9]),
					'display_desc' => sanitize_text_field($strings[10]),
					'event_identifier' => sanitize_title_with_dashes($strings[11]),
					'start_date' => date('Y-m-d',strtotime($strings[12])),
					'end_date' => date('Y-m-d', strtotime($strings[13])),
					'reg_limit' => sanitize_text_field($strings[16]),//skip 17, it's the price and needs to be accounted for differently
					'allow_multiple' => sanitize_text_field($strings[18]),
					'additional_limit' => (int) $strings[19],
					'send_mail' => sanitize_text_field($strings[20]),
					'is_active' => sanitize_text_field($strings[21]),
					'conf_mail' => $strings[22],
					'registration_start' => date('Y-m-d', strtotime($strings[23])),
					'registration_end' => date('Y-m-d', strtotime($strings[24])),
					'question_groups' => $question_groups,
					'event_meta' => $event_meta
					);

				$sql_format = array(
					'%s', //event_name
					'%s', //event_desc
					'%s', //address
					'%s', //address2
					'%s', //city
					'%s', //state
					'%s', //country
					'%s', //zip
					'%s', //phone
					'%s', //display_desc
					'%s', //event_identifier
					'%s', //start_date
					'%s', //end_date
					'%s', //reg_limit
					'%s', //allow_multiple
					'%d', //additional_limit
					'%s', //send_mail
					'%s', //is_active
					'%s', //conf_mail
					'%s', //registration_start
					'%s', //registration_end
					'%s', //question_groups
					'%s', //event_meta
					);

				if ( $wpdb->insert(EVENTS_DETAIL_TABLE, $strings_sql_array, $sql_format ) === FALSE ) {
					print $wpdb->print_error();
				} else {
					$last_event_id = $wpdb->insert_id;
				}


				//Add times data
				$times_sql = array(
					'event_id' => $last_event_id,
					'start_time' => date("h:i A", strtotime($strings[14])),
					'end_time' => date("h:i A", strtotime($strings[15]))
					);

				$times_sql_format = array(
					'%d',
					'%s',
					'%s'
					);
				
				//echo $times_sql;
				//$wpdb->query ( $wpdb->prepare( $times_sql ) );
				if ($wpdb->insert(EVENTS_START_END_TABLE, $times_sql, $times_sql_format ) === false) {
					print $wpdb->print_error();
				}

				//Add price data
				$prices_sql = array(
					'event_id' => $last_event_id,
					'event_cost' => (float) $strings[17]
					);
				$prices_sql_format = array( '%d', '%f' );
				
				//echo $prices_sql;
				// $wpdb->query ( $wpdb->prepare( $prices_sql ) );
				if ($wpdb->insert(EVENTS_PRICES_TABLE, $prices_sql, $prices_sql_format ) === false) {
					print $wpdb->print_error();
				}

				//Add category data
				$category_names = explode("|", $strings[25]);
				foreach ($category_names as $category_name) {
					$category_name = trim($category_name);
					$category_sql = "SELECT cd.id FROM " . EVENTS_CATEGORY_TABLE . " cd WHERE category_name='%s'";
					$cat_id = $wpdb->get_var($wpdb->prepare($category_sql, $category_name));
					if ($cat_id == false) {
						//fool the admin code into thinking we're adding a category via a POSt
						$old_REQUEST_action = $_REQUEST['action'];
						$_REQUEST['action'] = 'add';
						$_REQUEST['category_name'] = $category_name;
						require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/category-management/add_cat_to_db.php');
						add_cat_to_db();
						$cat_id = $wpdb->get_var($wpdb->prepare($category_sql, $category_name));
						//ok, now that we've fooled it and added the category, revert the $_REQUEST params
						$_REQUEST['action'] = $old_REQUEST_action;
					}
					$cat_sql_2 = "INSERT INTO " . EVENTS_CATEGORY_REL_TABLE . " (event_id, cat_id) VALUES (%d, %d)";
					if ($wpdb->query($wpdb->prepare($cat_sql_2, $last_event_id, $cat_id)) === false) {
						print $wpdb->print_error();
					}
				}
			}
		}
	}


	unlink($csvfile);
	if (!file_exists($csvfile)) {
		echo "<br>
Upload file has been deleted.<br>";
	}
	echo "Added a total of $num_of_imported_events events to the database.<br>";
	
	remove_query_arg("action");
}
