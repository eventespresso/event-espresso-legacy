<?php if (!defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
do_action('action_hook_espresso_log', __FILE__, 'FILE LOADED', '');	


//This function installs the tables
function event_espresso_run_install( $table_name, $table_version = '', $sql ) {

	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$wp_table_name = $wpdb->prefix . $table_name;

	$sql_create_table = "CREATE TABLE $wp_table_name ( $sql ) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
	dbDelta($sql_create_table);
	// update_option creates new option if no existing
	delete_option( $table_name . '_tbl_version', EVENT_ESPRESSO_VERSION );
	//create option for table name
	delete_option( $table_name . '_tbl', $wp_table_name );

}




//Install/update data tables in the Wordpress database
//This fixes some tables that may have been named wrong in an earlier version of the plugin
function event_espresso_rename_tables($old_table_name, $new_table_name) {
	global $wpdb;

	$old_table_name = $wpdb->prefix . $old_table_name;
	$new_table_name = $wpdb->prefix . $new_table_name;

	if ($wpdb->get_var("SHOW TABLES LIKE '" . $old_table_name . "'") == $old_table_name) {
		$wpdb->query("ALTER TABLE " . $old_table_name . " RENAME TO " . $new_table_name);
	}
}




function espresso_downgrade_error() {
	wp_die( '
<h2 style="color:red; font-size:2em; text-align:center;">' . __( 'Warning!', 'event_espresso' ) . '</h2>
<p style="font-size:1.4em; text-align:center;">
	' . __( 'THE DATABASE SCHEMA FOR EVENT ESPRESSO 4.x VERSIONS<br/> IS INCOMPATIBLE WITH OLDER 3.x VERSIONS.', 'event_espresso' ) . '</p>
<p style="font-size:1.2em; text-align:center;">
	' . __( 'If you really wish to downgrade and re-activate an older version of Event Espresso:', 'event_espresso' ) . '</p>
<ul style="font-size:1.4em; line-height:1em;">
	<li style="margin:0 0 .5em 25%;">' . __( 'utilize a fresh install of WordPress', 'event_espresso' ) . '</li>
	<li style="margin:0 0 .5em 25%;">' . __( 'or...', 'event_espresso' ) . '</li>
	<li style="margin:0 0 .5em 25%;">' . __( 'backup your existing database first (important)', 'event_espresso' ) . '</li>
	<li style="margin:0 0 .5em 25%;">' . __( 'remove all existing Event Espresso 4.x tables from the database', 'event_espresso' ) . '</li>
	<li style="margin:0 0 .5em 25%;">' . __( 'then activate the older version of Event Espresso', 'event_espresso' ) . '</li>
</ul>
<p style="font-size:1.1em; line-height:1.1em; text-align:center; color:#007BAE;">
	' . __( 'To exit this error screen:<br/>Please press the <b><span style="font-size:1.4em;">&lsaquo;</span> back button</b> on your browser to return to the WordPress Plugins page.', 'event_espresso' ) . '</p>'
	);		
}




/**
 * deactivate_event_espresso
 *
 * 	@return void
 */
function deactivate_event_espresso() {
	$active_plugins = array_flip( get_option( 'active_plugins' ));
	unset( $active_plugins[ EVENT_ESPRESSO_WPPLUGINPATH ] );
	update_option( 'active_plugins', array_flip( $active_plugins ));	
}




function events_data_tables_install() {

    if ( ! current_user_can( 'activate_plugins' )) {
		 return;
	}       
    
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "activate-plugin_{$plugin}" );


	function event_espresso_install_system_names() {
		global $wpdb;

		$system_name_data = "SELECT system_name FROM " . $wpdb->prefix . "events_question";

		$system_names = $wpdb->get_results($system_name_data);

		$fname = false;
		$lname = false;
		$email = false;
		$adress = false;
		$adress2 = false;
		$city = false;
		$state = false;
		$country = false;
		$zip = false;
		$phone = false;

		foreach ($system_names as $system_name) {
			switch ($system_name->system_name) {
				case 'fname':
					$fname = true;
					break;
				case 'lname':
					$lname = true;
					break;
				case 'email':
					$email = true;
					break;
				case 'address':
					$adress = true;
					break;
				case 'address2':
					$adress2 = true;
					break;
				case 'city':
					$city = true;
					break;
				case 'state':
					$state = true;
					break;
				case 'country':
					$country = true;
					break;
				case 'zip':
					$zip = true;
					break;
				case 'phone':
					$phone = true;
					break;
			}
		}

		if ($fname == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'First Name', 'question_type' => 'TEXT', 'system_name' => 'fname', 'required' => 'Y', 'sequence' => '0'), array('%s', '%s', '%s', '%s', '%s'));

		if ($lname == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'Last Name', 'question_type' => 'TEXT', 'system_name' => 'lname', 'required' => 'Y', 'sequence' => '1'), array('%s', '%s', '%s', '%s', '%s'));

		if ($email == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'Email', 'question_type' => 'TEXT', 'system_name' => 'email', 'required' => 'Y', 'sequence' => '2'), array('%s', '%s', '%s', '%s', '%s'));

		if ($adress == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'Address', 'system_name' => 'address', 'sequence' => '3'), array('%s', '%s', '%s'));

		if ($adress2 == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'Address 2', 'system_name' => 'address2', 'sequence' => '3'), array('%s', '%s', '%s'));

		if ($city == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'City', 'system_name' => 'city', 'sequence' => '4'), array('%s', '%s', '%s'));

		if ($state == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'State', 'system_name' => 'state', 'sequence' => '5'), array('%s', '%s', '%s'));

		if ($country == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'Country', 'system_name' => 'country', 'sequence' => '6'), array('%s', '%s', '%s'));
			
		if ($zip == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'Zip', 'system_name' => 'zip', 'sequence' => '6'), array('%s', '%s', '%s'));

		if ($phone == false)
			$wpdb->insert($wpdb->prefix . "events_question", array('question' => 'Phone', 'system_name' => 'phone', 'sequence' => '7'), array('%s', '%s', '%s'));

		$system_group = $wpdb->get_row("SELECT system_group FROM " . $wpdb->prefix . "events_qst_group" . " WHERE system_group = 1");

		if ($wpdb->num_rows == 0) {

			$wpdb->insert($wpdb->prefix . "events_qst_group", array('group_name' => 'Personal Information', 'group_identifier' => sanitize_title_with_dashes('personal_information-' . time()), 'system_group' => 1), array('%s', '%s', '%d'));

			$personal_group_id = $wpdb->insert_id;

			$wpdb->insert($wpdb->prefix . "events_qst_group", array('group_name' => 'Address Information', 'group_identifier' => sanitize_title_with_dashes('address_information-' . time()), 'system_group' => 0), array('%s', '%s', '%d'));

			$address_group_id = $wpdb->insert_id;

			$system_name_data = "SELECT id, system_name FROM " . $wpdb->prefix . "events_question" . " where system_name IN ('fname', 'lname', 'email')";
			$system_names = $wpdb->get_results($system_name_data);

			foreach ($system_names as $system_name) {

				$wpdb->insert($wpdb->prefix . "events_qst_group_rel", array('group_id' => $personal_group_id, 'question_id' => $system_name->id), array('%d', '%d'));
			}

			$system_name_data = "SELECT id, system_name FROM " . $wpdb->prefix . "events_question" . " where system_name IN ('address', 'city', 'state', 'zip' )";
			$system_names = $wpdb->get_results($system_name_data);

			foreach ($system_names as $system_name) {

				$wpdb->insert($wpdb->prefix . "events_qst_group_rel", array('group_id' => $address_group_id, 'question_id' => $system_name->id), array('%d', '%d'));
			}
		}
	}

	function espresso_update_event_ids() {
		global $wpdb;
		$event_data = "SELECT id FROM " . $wpdb->prefix . "events_detail WHERE event_code='0' ";
		if ($wpdb->num_rows == 0) {
			$wpdb->update($wpdb->prefix . "events_detail", array('group_name' => 'Personal Information', 'group_identifier' => sanitize_title_with_dashes('personal_information-' . time()), 'system_group' => 1), array('%s', '%s', '%d'));
		}
	}

	function event_espresso_update_shortcodes() {
		global $wpdb;
		$wpdb->query("SELECT id FROM " . $wpdb->prefix . "posts " . " WHERE (post_content LIKE '%{ESPRESSO_EVENTS}%' AND post_type = 'page') OR (post_content LIKE '%{ESPRESSO_PAYMENTS}%'  AND post_type = 'page') OR (post_content LIKE '%{ESPRESSO_TXN_PAGE}%'  AND post_type = 'page') ");

		if ($wpdb->num_rows > 0) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content,'{ESPRESSO_EVENTS}','[ESPRESSO_EVENTS]')");
			$wpdb->query("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content,'{ESPRESSO_PAYMENTS}','[ESPRESSO_PAYMENTS]')");
			$wpdb->query("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content,'{ESPRESSO_TXN_PAGE}','[ESPRESSO_TXN_PAGE]')");
		}

		$wpdb->query("SELECT id FROM " . $wpdb->prefix . "posts " . " WHERE (post_content LIKE '%{EVENTREGIS}%' AND post_type = 'page') OR (post_content LIKE '%{EVENTREGPAY}%' AND post_type = 'page') OR (post_content LIKE '%{EVENTPAYPALTXN}%' AND post_type = 'page') ");

		if ($wpdb->num_rows > 0) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content,'{EVENTREGIS}','[ESPRESSO_EVENTS]')");
			$wpdb->query("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content,'{EVENTREGPAY}','[ESPRESSO_PAYMENTS]')");
			$wpdb->query("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content,'{EVENTPAYPALTXN}','[ESPRESSO_TXN_PAGE]')");
		}
	}

	function espresso_update_attendee_qty() {
		global $wpdb;
		$sql = "SELECT id FROM " . $wpdb->prefix . "events_attendee WHERE quantity = 0 ";
		$results = $wpdb->get_results($sql);
		if ($wpdb->num_rows > 0) {
			$update_attendee_qty = $wpdb->query("UPDATE " . $wpdb->prefix . "events_attendee SET quantity = 1 OR quantity = '' WHERE quantity = 0");
			do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, " sqldump = " . var_export($results, true) . " ] [ rows affected = " . var_export($update_attendee_qty, true));
		}
	}

	function espresso_answer_fix() {
		global $wpdb;
		$sql = "SHOW COLUMNS FROM " . EVENTS_ANSWER_TABLE . " LIKE 'id';";
		$test = $wpdb->query($sql);
		if (empty($test)) {
			$sql = "ALTER TABLE " . EVENTS_ANSWER_TABLE . " DROP PRIMARY KEY";
			$wpdb->query($sql);
			$sql = "ALTER TABLE  " . EVENTS_ANSWER_TABLE . " ADD  id INT( 11 ) unsigned NOT NULL AUTO_INCREMENT FIRST , ADD PRIMARY KEY (  id )";
			$wpdb->query($sql);
		}
	}

	function espresso_added_by_admin_session_id_fix() {
		global $wpdb;
		$sql = "SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE attendee_session=''";
		$ids = $wpdb->get_col($sql);
		foreach ($ids as $id) {
			$wpdb->update(EVENTS_ATTENDEE_TABLE, array('attendee_session' => uniqid('', true)), array('id' => $id));
		}
	}

	//This function was creating a new psot everytime EE was activated
	function espresso_add_cancel_shortcode() {
		global $org_options;
		$org_options = get_option('events_organization_settings');
		if (!empty($org_options['cancel_return'])) {
			$cancel_page = get_page($org_options['cancel_return'], ARRAY_A);
			$test = strpos($cancel_page['post_content'], '[ESPRESSO_CANCELLED]');
			if ($test === false) {
				$cancel_page['post_content'] = $cancel_page['post_content'] . '[ESPRESSO_CANCELLED]';
				wp_update_post($cancel_page);
			}
		}
	}
	
	/**
	* espresso_ensure_event_status_is_not_blank
	* 
	* ensure that for each event has an event status
	* if the event status is missing, give it a default value of  'A'
	* 
	* @since 3.1.32
	* @return void
	*/
	function espresso_ensure_event_status_is_not_blank() {
	
		global $wpdb;
		
		$SQL = "SELECT id FROM " . $wpdb->prefix . "events_detail WHERE event_status = '' ORDER BY id";
		if ( $events = $wpdb->get_results( $SQL )) {
			foreach ( $events as $event ) {		
					$wpdb->update( 
							$wpdb->prefix . "events_detail", 
							array( 'event_status' => 'A' ), 
							array( 'id' => $event->id ),
							array( '%s' ),
							array( '%d' )
					);	
								
			}
		}
			
	}


	function events_organization_tbl_install() {
		global $wpdb;

		$table_name = $wpdb->prefix . "events_organization";
		//Check to see if upgrading from an earlier version.
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

			$payment_subject = (__('Payment Received for [event_name]', 'event_espresso'));
			$payment_message = (__('<p>***This Is An Automated Response*** </p><p>Thank You [fname] [lname]</p>  <p>We have just  received a payment in the amount of [event_price] for your registration to [event_name].</p> <p>Transaction ID: [txn_id]</p>', 'event_espresso'));

			$message = ( __('<p>***This is an automated response - Do Not Reply***</p> <p>Thank you [fname] [lname] for registering for [event].</p><p>This event starts at [start_time] on [start_date] and runs until [end_time] on [end_date].</p><p>Location:<br>[location]</p><p>Phone: [location_phone]</p><p>Google Map: [google_map_link]</p><p> We hope that you will find this event both informative and enjoyable. Should you have any questions, please contact [contact].</p><p>If you have not done so already, please submit your payment in the amount of [cost].</p><p>Click here to review your payment information [payment_url].</p><p>[edit_attendee_link]</p><p>Thank You.</p>', 'event_espresso'));

			$new_org_options = array(
					'organization' => get_bloginfo('name'),
					'organization_street1' => '123 West Somewhere',
					'organization_street2' => '',
					'organization_city' => 'Some City',
					'organization_state' => 'AZ',
					'organization_zip' => '84128',
					'contact_email' => get_bloginfo('admin_email'),
					'default_mail' => 'Y',
					'paypal_id' => 'my_email@my_website.com',
					'payment_subject' => $payment_subject,
					'payment_message' => $payment_message,
					'message' => $message,
					'default_payment_status' => '',
					'surcharge' => '0.00',
					'country_id' => '',
					'organization_country' => '64',
					'currency_symbol' => '$',
					'expire_on_registration_end' => 'Y',
					'email_before_payment' => 'N',
					'email_fancy_headers' => 'N',
					'enable_default_style' => 'N',
					'event_ssl_active' => 'N',
					'use_venue_manager' => 'Y',
					'enable_default_style' => 'N',
					'selected_style' => 'event_espresso_style.css',
					'show_pending_payment_options' => 'Y',
					'show_reg_footer' => 'Y',
					'skip_confirmation_page' => 'N',
					'allow_mer_discounts' => 'N',
					'allow_mer_vouchers' => 'N',
					'display_short_description_in_event_list' => 'N',
					'display_description_on_multi_reg_page' => 'N',
					'display_address_in_event_list' => 'N',
					'display_address_in_regform' => 'Y',
					'use_custom_post_types' => 'N',
					'display_ical_download' => 'Y',
'display_featured_image' => 'N',
					'themeroller' => array(
						'themeroller_style' => 'smoothness',
					),
					'style_settings' => array(
						'disable_legacy_styles' => 'Y',
						'enable_default_style' => 'Y',
						'css_name' => '',
					),
			);

			add_option('events_organization_settings', $new_org_options);
			
		} else if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			//If an earlier version is found
			$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "events_organization" . " WHERE id='1'");
			foreach ($results as $result) {
				$org_id = $result->id;
				$Organization = $result->organization;
				$Organization_street1 = $result->organization_street1;
				$Organization_street2 = $result->organization_street2;
				$Organization_city = $result->organization_city;
				$Organization_state = $result->organization_state;
				$Organization_zip = $result->organization_zip;
				$contact = $result->contact_email;
				$registrar = $result->contact_email;
				$paypal_id = $result->paypal_id;
				$paypal_cur = $result->currency_format;
				$event_page_id = $result->event_page_id;
				$return_url = $result->return_url;
				$cancel_return = $result->cancel_return;
				$notify_url = $result->notify_url;
				$use_sandbox = $result->use_sandbox;
				$image_url = $result->image_url;
				$default_mail = $result->default_mail;
				$payment_subject = $result->payment_subject;
				$payment_message = $result->payment_message;
				$message = $result->message;
			}

			switch ($paypal_cur) {
				case 'USD':
				case 'HKD':
				case 'NZD':
				case 'SGD':
					$currency_symbol = '$';
					break;

				case 'AUD':
					$currency_symbol = 'A $';
					break;

				case 'GBP':
					$currency_symbol = '&pound;';
					break;

				case 'CAD':
					$currency_symbol = 'C $';
					break;

				case 'EUR':
					$currency_symbol = 'Euro: ';
					break;

				case 'JPY':
					$currency_symbol = '&yen;';
					break;

				default:
					$currency_symbol = '$';
					break;
			}

			$org_options = array(
					'organization' => $Organization,
					'organization_street1' => $Organization_street1,
					'organization_street2' => $Organization_street2,
					'organization_city' => $Organization_city,
					'organization_state' => $Organization_state,
					'organization_zip' => $Organization_zip,
					'contact_email' => $contact,
					'paypal_id' => $paypal_id,
					'currency_format' => $paypal_cur,
					'currency_symbol' => $currency_symbol,
					'event_page_id' => $event_page_id,
					'return_url' => $return_url,
					'cancel_return' => $cancel_return,
					'notify_url' => $notify_url,
					'use_sandbox' => $use_sandbox,
					'image_url' => $image_url,
					'default_mail' => $default_mail,
					'payment_subject' => $payment_subject,
					'payment_message' => $payment_message,
					'message' => $message,
					'country_id' => $country_id,
					'email_before_payment' => 'N',
					'use_personnel_manager' => 'Y',
					'use_venue_manager' => 'Y',
					'enable_default_style' => 'N',
					'selected_style' => 'event_espresso_style.css',
					'show_pending_payment_options' => 'Y',
					'show_reg_footer' => 'Y',
					'skip_confirmation_page' => 'N',
					'allow_mer_discounts' => 'N',
					'allow_mer_vouchers' => 'N'
			);

			add_option('events_organization_settings', $org_options);
		}
	}

	$table_name = "events_attendee";
	$sql = "id int(11) unsigned NOT NULL AUTO_INCREMENT,
					  registration_id VARCHAR(23) DEFAULT '0',
					  is_primary tinyint(1) DEFAULT '0',
					  lname VARCHAR(45) DEFAULT NULL,
					  fname VARCHAR(45) DEFAULT NULL,
					  address VARCHAR(45) DEFAULT NULL,
					  address2 VARCHAR(45) DEFAULT NULL,
					  city VARCHAR(45) DEFAULT NULL,
					  state VARCHAR(45) DEFAULT NULL,
					  zip VARCHAR(45) DEFAULT NULL,
					  country_id VARCHAR(128) DEFAULT NULL,
					  organization_name VARCHAR(50) DEFAULT NULL,
					  vat_number VARCHAR(20) DEFAULT NULL,
					  email VARCHAR(45) DEFAULT NULL,
					  phone VARCHAR(45) DEFAULT NULL,
					  date timestamp NOT NULL default CURRENT_TIMESTAMP,
					  price_option VARCHAR(100) DEFAULT NULL,
					  orig_price decimal(20,2) DEFAULT '0.00',
					  final_price decimal(20,2) DEFAULT '0.00',
					  quantity tinyint(1) DEFAULT '0',
					  total_cost decimal(20,2) DEFAULT '0.00',
					  amount_pd decimal(20,2) DEFAULT '0.00',
					  coupon_code VARCHAR(45) DEFAULT NULL,
					  payment VARCHAR(45) DEFAULT NULL,
					  payment_status VARCHAR(45) DEFAULT 'Incomplete',
					  txn_type VARCHAR(45) DEFAULT NULL,
					  txn_id VARCHAR(45) DEFAULT NULL,
					  payment_date VARCHAR(45) DEFAULT NULL,
					  event_id VARCHAR(45) DEFAULT NULL,
					  event_time VARCHAR(15) DEFAULT NULL,
					  end_time VARCHAR(15) DEFAULT NULL,
					  start_date VARCHAR(45) DEFAULT NULL,
					  end_date VARCHAR(45) DEFAULT NULL,
					  attendee_session VARCHAR(250) DEFAULT NULL,
					  transaction_details TEXT,
					  pre_approve INT(11) DEFAULT '1',
					  checked_in INT(1) DEFAULT '0',
					  checked_in_quantity INT(11) DEFAULT '0',
					  hashSalt VARCHAR(250) DEFAULT NULL,
					PRIMARY KEY  (id),
					KEY registration_id (registration_id),
					KEY event_id (event_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_attendee_meta";
	$sql = "ameta_id bigint(20) NOT NULL AUTO_INCREMENT,
			  attendee_id int(11) DEFAULT NULL,
			  meta_key varchar(255) DEFAULT NULL,
			  meta_value longtext,
			  date_added datetime DEFAULT NULL,
			  date_updated datetime DEFAULT NULL,
  			  PRIMARY KEY  (ameta_id),
			  KEY attendee_id (attendee_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_detail";
	$sql = "id int(11) unsigned NOT NULL AUTO_INCREMENT,
				  event_code VARCHAR(26) DEFAULT '0',
				  event_name VARCHAR(100) DEFAULT NULL,
				  event_desc TEXT,
				  display_desc VARCHAR (1) DEFAULT 'Y',
				  display_reg_form VARCHAR (1) DEFAULT 'Y',
				  event_identifier VARCHAR(75) DEFAULT NULL,
				  start_date VARCHAR (15) DEFAULT NULL,
				  end_date VARCHAR (15) DEFAULT NULL,
				  registration_start VARCHAR (15) DEFAULT NULL,
				  registration_end VARCHAR (15) DEFAULT NULL,
				  registration_startT VARCHAR (15) DEFAULT NULL,
  				  registration_endT VARCHAR (15) DEFAULT NULL,
				  visible_on VARCHAR(15) DEFAULT NULL,
				  address TEXT,
				  address2 TEXT,
				  city VARCHAR(100) DEFAULT NULL,
				  state VARCHAR(100) DEFAULT NULL,
				  zip VARCHAR(11) DEFAULT NULL,
				  phone VARCHAR(15) DEFAULT NULL,
				  venue_title VARCHAR(250) DEFAULT NULL,
				  venue_url VARCHAR(250) DEFAULT NULL,
				  venue_image TEXT,
				  venue_phone VARCHAR(15) DEFAULT NULL,
				  virtual_url VARCHAR(250) DEFAULT NULL,
				  virtual_phone VARCHAR(15) DEFAULT NULL,
				  reg_limit VARCHAR (25) DEFAULT '999999',
				  allow_multiple VARCHAR (15) DEFAULT 'N',
				  additional_limit INT(10) DEFAULT '5',
				  send_mail VARCHAR(2) DEFAULT 'Y',
				  is_active VARCHAR(1) DEFAULT 'Y',
				  event_status VARCHAR(2) DEFAULT 'A',
				  conf_mail TEXT,
				  use_coupon_code VARCHAR(1) DEFAULT 'N',
				  use_groupon_code VARCHAR(1) DEFAULT 'N',
				  category_id TEXT,
				  coupon_id TEXT,
				  tax_percentage FLOAT,
				  tax_mode INT(11),
				  member_only VARCHAR(1),
					post_id INT(11) DEFAULT NULL,
					post_type VARCHAR(50) DEFAULT NULL,
					country VARCHAR(200) DEFAULT NULL,
					externalURL VARCHAR(255) DEFAULT NULL,
					early_disc VARCHAR(10) DEFAULT NULL,
					early_disc_date VARCHAR(15) DEFAULT NULL,
					early_disc_percentage VARCHAR(1) DEFAULT 'N',
					question_groups LONGTEXT NULL DEFAULT NULL,
					item_groups LONGTEXT NULL DEFAULT NULL,
					event_type VARCHAR(250) DEFAULT NULL,
					allow_overflow VARCHAR (1) DEFAULT 'N',
					overflow_event_id INT(10) DEFAULT '0',
					recurrence_id int(11) DEFAULT '0',
					email_id int(11) DEFAULT '0',
					alt_email TEXT,
					event_meta LONGTEXT DEFAULT NULL,
					wp_user int(22) DEFAULT '1',
					require_pre_approval int(11) DEFAULT '0',
					timezone_string VARCHAR(250) DEFAULT NULL,
					likes int(22) DEFAULT NULL,
					ticket_id int(22) DEFAULT '0',
					submitted datetime NOT NULL,
				 PRIMARY KEY  (id),
				 KEY event_code (event_code),
				 KEY wp_user (wp_user),
				 KEY event_name (event_name),
				 KEY city (city),
				 KEY state (state),
				 KEY start_date (start_date),
				 KEY end_date (end_date),
				 KEY registration_start (registration_start),
				 KEY registration_end (registration_end),
				 KEY reg_limit (reg_limit),
				 KEY event_status (event_status),
				 KEY recurrence_id (recurrence_id),
				 KEY submitted (submitted),
  				 KEY likes (likes)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_meta";
	$sql = "emeta_id bigint(20) NOT NULL AUTO_INCREMENT,
			  event_id int(11) DEFAULT NULL,
			  meta_key varchar(255) DEFAULT NULL,
			  meta_value longtext,
			  date_added datetime DEFAULT NULL,
  			  PRIMARY KEY  (emeta_id),
			  KEY event_id (event_id),
			  KEY meta_key (meta_key)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_email";
	$sql = "id int(11) unsigned NOT NULL AUTO_INCREMENT,
				email_name VARCHAR(100) DEFAULT NULL,
				email_subject VARCHAR(250) DEFAULT NULL,
				email_text TEXT,
				wp_user int(22) DEFAULT '1',
				PRIMARY KEY  (id),
				KEY wp_user (wp_user)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_category_detail";
	$sql = "id int(11) unsigned NOT NULL AUTO_INCREMENT,
				category_name VARCHAR(100) DEFAULT NULL,
				category_identifier VARCHAR(45) DEFAULT NULL,
				category_desc TEXT,
				category_meta TEXT,
				display_desc VARCHAR (4) DEFAULT NULL,
				wp_user int(22) DEFAULT '1',
				PRIMARY KEY  (id),
				KEY category_identifier (category_identifier),
				KEY wp_user (wp_user)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_category_rel";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				event_id int(11) DEFAULT NULL,
				cat_id int(11) DEFAULT NULL,
				PRIMARY KEY  (id),
			  	KEY event_id (event_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_venue";
	$sql = "id int(11) unsigned NOT NULL AUTO_INCREMENT,
				name VARCHAR(250) DEFAULT NULL,
				identifier VARCHAR(26) DEFAULT '0',
				address VARCHAR(250) DEFAULT NULL,
				address2 VARCHAR(250) DEFAULT NULL,
				city VARCHAR(250) DEFAULT NULL,
				state VARCHAR(250) DEFAULT NULL,
				zip VARCHAR(250) DEFAULT NULL,
				country VARCHAR(250) DEFAULT NULL,
				meta TEXT,
				wp_user int(22) DEFAULT '1',
				PRIMARY KEY  (id),
			  	KEY identifier (identifier),
				KEY wp_user (wp_user)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_venue_rel";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				event_id int(11) DEFAULT NULL,
				venue_id int(11) DEFAULT NULL,
				PRIMARY KEY  (id),
			  	KEY event_id (event_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_locale";
	$sql = "id int(11) unsigned NOT NULL AUTO_INCREMENT,
			  name varchar(250) DEFAULT NULL,
			  identifier varchar(26) DEFAULT '0',
			  wp_user int(22) DEFAULT '1',
			  PRIMARY KEY  (id),
			  KEY identifier (identifier),
			  KEY wp_user (wp_user)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_locale_rel";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				venue_id int(11) DEFAULT NULL,
				locale_id int(11) DEFAULT NULL,
				PRIMARY KEY  (id),
			  	KEY venue_id (venue_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_personnel";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				name VARCHAR(250) DEFAULT NULL,
				role VARCHAR(250) DEFAULT NULL,
				identifier VARCHAR(26) DEFAULT '0',
				email TEXT,
				meta TEXT,
				wp_user int(22) DEFAULT '1',
				PRIMARY KEY  (id),
			  	KEY identifier (identifier),
			  	KEY wp_user (wp_user)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_personnel_rel";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				event_id int(11) DEFAULT NULL,
				person_id int(11) DEFAULT NULL,
				PRIMARY KEY  (id),
			  	KEY event_id (event_id),
			  	KEY person_id (person_id)";
	event_espresso_run_install($table_name, '', $sql);
	
	$table_name = "events_discount_rel";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				event_id int(11) DEFAULT NULL,
				discount_id int(11) DEFAULT NULL,
				PRIMARY KEY  (id),
			  	KEY event_id (event_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_start_end";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				event_id int(11) DEFAULT NULL,
				start_time varchar(10) DEFAULT NULL,
				end_time varchar(10) DEFAULT NULL,
				reg_limit int (15) DEFAULT '0',
				PRIMARY KEY  (id),
			  	KEY event_id (event_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_prices";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				event_id int(11) DEFAULT NULL,
				price_type varchar(50) DEFAULT NULL,
				event_cost decimal(20,2) NOT NULL DEFAULT '0.00',
				surcharge decimal(10,2) NOT NULL DEFAULT '0.00',
				surcharge_type varchar(10) DEFAULT NULL,
				member_price_type varchar(50) DEFAULT NULL,
				member_price decimal(20,2) NOT NULL DEFAULT '0.00',
				max_qty int(7) DEFAULT '0',
				max_qty_members int(7) DEFAULT '0',
				PRIMARY KEY  (id),
			  	KEY event_id (event_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_discount_codes";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				coupon_code varchar(50) DEFAULT NULL,
				coupon_code_price decimal(20,2) DEFAULT NULL,
				use_percentage VARCHAR(1) DEFAULT NULL,
				coupon_code_description TEXT,
				each_attendee VARCHAR(1) DEFAULT NULL,
				wp_user int(22) DEFAULT '1',
				apply_to_all TINYINT(1) NOT NULL DEFAULT '0',
				PRIMARY KEY  (id),
			  	KEY coupon_code (coupon_code),
			  	KEY wp_user (wp_user)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_multi_event_registration_id_group";
	$sql = "primary_registration_id varchar(255) DEFAULT NULL,
			registration_id varchar(255) DEFAULT NULL,
			KEY primary_registration_id (primary_registration_id),
			KEY registration_id (registration_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_question";
	$sql = "id int(11) unsigned NOT NULL auto_increment,
			sequence INT(11) NOT NULL default '0',
			question_type enum('TEXT','TEXTAREA','MULTIPLE','SINGLE','DROPDOWN') NOT NULL default 'TEXT',
			question text NOT NULL,
			system_name varchar(15) DEFAULT NULL,
			response text NULL,
			required ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'N',
			price_mod ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'N',
			required_text text NULL,
			admin_only ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'N',
			wp_user int(22) DEFAULT '1',
			PRIMARY KEY  (id),
			KEY wp_user (wp_user),
			KEY system_name (system_name),
			KEY admin_only (admin_only)";
	$sql = apply_filters( 'espresso_filter_hook_events_question_sql', $sql );
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_qst_group";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				group_name VARCHAR(100) NOT NULL default 'NULL',
				group_identifier VARCHAR(45) NOT NULL default 'NULL',
				group_description TEXT,
				group_order int(11) DEFAULT '0',
				show_group_name TINYINT( 1 ) NOT NULL DEFAULT '1',
				show_group_description TINYINT( 1 ) NOT NULL DEFAULT '1',
				system_group TINYINT( 1 ) NOT NULL DEFAULT '0',
				wp_user int(22) DEFAULT '1',
				PRIMARY KEY  (id),
			  	KEY system_group (system_group),
			  	KEY wp_user (wp_user)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_qst_group_rel";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
				group_id int(11)  NOT NULL,
				question_id int(11) NOT NULL,
				PRIMARY KEY  (id),
			  	KEY group_id (group_id),
			  	KEY question_id (question_id)";
	event_espresso_run_install($table_name, '', $sql);

	$table_name = "events_answer";
	$sql = "id int(11) NOT NULL AUTO_INCREMENT,
			registration_id varchar(23) NOT NULL,
			attendee_id int(11) NOT NULL default '0',
			question_id int(11) NOT NULL default '0',
			answer text NOT NULL,
			PRIMARY KEY  (id),
			KEY registration_id (registration_id),
			KEY attendee_id (attendee_id)";
	event_espresso_run_install($table_name, '', $sql);



	events_organization_tbl_install();
	event_espresso_install_system_names();
	event_espresso_create_upload_directories();
	event_espresso_update_shortcodes();
	event_espresso_update_attendee_data();
	espresso_update_attendee_qty();
	espresso_answer_fix();
	espresso_added_by_admin_session_id_fix();
	espresso_add_cancel_shortcode();
	espresso_ensure_event_status_is_not_blank();
	
	// grab espresso_db_update option
	$espresso_db_update = get_option( 'espresso_db_update', array() );
	// make sure it's an array
	$espresso_db_update = is_array( $espresso_db_update ) ? $espresso_db_update : array( $espresso_db_update );
	// check if current version is already in list of activated versions
	if ( ! in_array( EVENT_ESPRESSO_VERSION, $espresso_db_update )) {
		// add current EE version to list
		$espresso_db_update[] = EVENT_ESPRESSO_VERSION;
	}
	// resave
	update_option( 'espresso_db_update', $espresso_db_update );

}
