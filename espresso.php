<?php

/*
  Plugin Name: Event Espresso
  Plugin URI: http://eventespresso.com/
  Description: Out-of-the-box Events Registration integrated with PayPal IPN for your Wordpress blog/website. <a href="admin.php?page=support" >Support</a>

  Reporting features provide a list of events, list of attendees, and excel export.

  Version: 3.2.S

  Author: Seth Shoultes
  Author URI: http://www.eventespresso.com

  Copyright (c) 2008-2011 Event Espresso  All Rights Reserved.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

function espresso_version() {
	return '3.2.P';
}

define("EVENT_ESPRESSO_VERSION", espresso_version());

// Define all plugin database tables
define("EVENTS_ANSWER_TABLE", $wpdb->prefix . "events_answer");

define("EVENTS_ATTENDEE_TABLE", $wpdb->prefix . "events_attendee");
define("EVENTS_ATTENDEE_COST_TABLE", $wpdb->prefix . "events_attendee_cost");
define("EVENTS_ATTENDEE_META_TABLE", $wpdb->prefix . "events_attendee_meta");

define("EVENTS_CATEGORY_TABLE", $wpdb->prefix . "events_category_detail");
define("EVENTS_CATEGORY_REL_TABLE", $wpdb->prefix . "events_category_rel");

define("EVENTS_DETAIL_TABLE", $wpdb->prefix . "events_detail");

define("EVENTS_DISCOUNT_CODES_TABLE", $wpdb->prefix . "events_discount_codes");
define("EVENTS_DISCOUNT_REL_TABLE", $wpdb->prefix . "events_discount_rel");

define("EVENTS_EMAIL_TABLE", $wpdb->prefix . "events_email");

define("EVENTS_LOCALE_TABLE", $wpdb->prefix . "events_locale");
define("EVENTS_LOCALE_REL_TABLE", $wpdb->prefix . "events_locale_rel");

define("EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE", $wpdb->prefix . "events_multi_event_registration_id_group");

define("EVENTS_PERSONNEL_TABLE", $wpdb->prefix . "events_personnel");
define("EVENTS_PERSONNEL_REL_TABLE", $wpdb->prefix . "events_personnel_rel");

define("EVENTS_PRICES_TABLE", $wpdb->prefix . "events_prices");

define("EVENTS_QST_GROUP_TABLE", $wpdb->prefix . "events_qst_group");
define("EVENTS_QST_GROUP_REL_TABLE", $wpdb->prefix . "events_qst_group_rel");
define("EVENTS_QUESTION_TABLE", $wpdb->prefix . "events_question");

define("EVENTS_START_END_TABLE", $wpdb->prefix . "events_start_end");

define("EVENTS_VENUE_TABLE", $wpdb->prefix . "events_venue");
define("EVENTS_VENUE_REL_TABLE", $wpdb->prefix . "events_venue_rel");

// End table definitions

define('EVENT_ESPRESSO_POWERED_BY', 'Event Espresso - ' . EVENT_ESPRESSO_VERSION);

//Get the plugin url and content directories.
//These variables are used to define the plugin and content directories in the constants below.
$wp_plugin_url = WP_PLUGIN_URL;
$wp_content_url = WP_CONTENT_URL;

//Define the plugin directory and path
define("EVENT_ESPRESSO_PLUGINPATH", "/" . plugin_basename(dirname(__FILE__)) . "/");
define("EVENT_ESPRESSO_PLUGINFULLPATH", WP_PLUGIN_DIR . EVENT_ESPRESSO_PLUGINPATH);
define("EVENT_ESPRESSO_PLUGINFULLURL", $wp_plugin_url . EVENT_ESPRESSO_PLUGINPATH);

//Define dierectory structure for uploads
//Create the paths
$wp_content_dir = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : ABSPATH . 'wp-content';

$upload_path = $wp_content_dir . '/uploads';
$event_espresso_upload_dir = $upload_path . '/espresso/';
$event_espresso_template_dir = $event_espresso_upload_dir . 'templates/';

//Define the includes directory
$includes_directory = EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/';
define("EVENT_ESPRESSO_INCLUDES_DIR", $includes_directory);

//Define the uploads directory and url
define("EVENT_ESPRESSO_UPLOAD_DIR", $event_espresso_upload_dir);
define("EVENT_ESPRESSO_UPLOAD_URL", $wp_content_url . '/uploads/espresso/');

//Define the templates dirrectory
define("EVENT_ESPRESSO_TEMPLATE_DIR", $event_espresso_template_dir);

//Define the gateway directory and url
$event_espresso_gateway_dir = EVENT_ESPRESSO_UPLOAD_DIR . "gateways/";
define("EVENT_ESPRESSO_GATEWAY_DIR", $event_espresso_gateway_dir);
define("EVENT_ESPRESSO_GATEWAY_URL", $wp_content_url . '/uploads/espresso/gateways/');

//End - Define dierectory structure for uploads

//Start the session
function espresso_init_session() {
	global $org_options;
	
	//logging
	if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
		espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => ''));
	}
	
	if (!isset($_SESSION)) {
		session_start();
	}
	
	if ( (isset($_REQUEST['page_id']) && ($_REQUEST['page_id'] == $org_options['return_url'] 
		|| $_REQUEST['page_id'] == $org_options['notify_url'])) 
		|| !isset($_SESSION['espresso_session']['id']) || $_SESSION['espresso_session']['id'] == array()) {
		
		$_SESSION['espresso_session'] = '';
		//Debug
		//echo "<pre>espresso_session - ".print_r($_SESSION['espresso_session'],true)."</pre>";
		$_SESSION['espresso_session'] = array();
		//Debug
		//echo "<pre>espresso_session array - ".print_r($_SESSION['espresso_session'],true)."</pre>";
		$_SESSION['espresso_session']['id'] = session_id().'-'.uniqid('',true);
		//Debug
		//echo "<pre>".print_r($_SESSION,true)."</pre>";		
    }
}

add_action('plugins_loaded', 'espresso_init_session', 1);

do_action('hook_espresso_after_init_session');

//Handles importing of csv files
function espresso_check_for_export() {
	if (isset($_REQUEST['export'])) {
		if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/EE_Export.class.php')) {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/EE_Export.class.php');
			$EE_Export = EE_Export::instance();
			$EE_Export->export();
		}
	}
}

add_action('plugins_loaded', 'espresso_check_for_export');

//Handles exporting of csv files
function espresso_check_for_import() {
	if (isset($_REQUEST['import'])) {
		if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/EE_Import.class.php')) {
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/EE_Import.class.php');
			$EE_Import = EE_Import::instance();
			$EE_Import->import();
		}
	}
}

add_action('plugins_loaded', 'espresso_check_for_import');

//Load the Event Espresso HTML meta
function espresso_info_header() {
	print( "<meta name='generator' content='Event Espresso Version " . EVENT_ESPRESSO_VERSION . "' />");
}

add_action('wp_head', 'espresso_info_header');


//Globals used throughout the site
global $org_options, $wpdb, $this_is_a_reg_page, $notices;
$org_options = get_option('events_organization_settings');
$page_id = isset($_REQUEST['page_id']) ? $_REQUEST['page_id'] : '';
$notices = array('updates' => array(), 'errors' => array());

//Check if SSL is loaded
if (is_ssl()) {

	//Create the server name
	$server_name = str_replace('https://', '', site_url());

	//If the site is using SSL, we need to make sure our files get loaded in SSL.
	//This will (should) make sure everything is loaded via SSL
	//So that the "..not everything is secure.." message doesn't appear
	//Still will be a problem if other themes and plugins do not implement ssl correctly
	$wp_plugin_url = str_replace('http://', 'https://', WP_PLUGIN_URL);
	$wp_content_url = str_replace('http://', 'https://', WP_CONTENT_URL);
} else {
	$server_name = str_replace('http://', '', site_url());
}


//Registration page check
//From Brent C. http://events.codebasehq.com/projects/event-espresso/tickets/99
$this_is_a_reg_page = FALSE;
if (isset($_REQUEST['ee']) || isset($_REQUEST['page_id']) || is_admin()) {
	$this_is_a_reg_page = TRUE;
} else {
	$reg_page_ids = array(
			'event_page_id' => $org_options['event_page_id'],
			'return_url' => $org_options['return_url'],
			'cancel_return' => $org_options['cancel_return'],
			'notify_url' => $org_options['notify_url']
	);

	$server_name = str_replace($_SERVER['SERVER_NAME'], '', $server_name);
	$uri_string = str_replace($server_name, '', $_SERVER['REQUEST_URI']);
	$uri_string = str_replace($_SERVER['QUERY_STRING'], '', $uri_string);
	$uri_string = rtrim($uri_string, '?');
	$uri_string = trim($uri_string, '/');
	$this_page = basename($uri_string);
	$uri_segments = explode('/', $uri_string);
	foreach ($uri_segments as $uri_segment) {
		$seg_page_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $wpdb->posts WHERE post_name = %s ", $uri_segment));
		if ($wpdb->num_rows > 0) {
			if (in_array($seg_page_id, $reg_page_ids)) {
				$this_is_a_reg_page = TRUE;
			}
		}
	}
}


require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/SimpleMath.php';
global $simpleMath;
$simpleMath = new SimpleMath();

require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/Event.php';
require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/Attendee.php';

require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/espresso_log.php';
if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
	$message = "REQUEST variables:\n";
	foreach ($_REQUEST as $key => $value) {
		$message .= $key . " = " . $value . "\n";
	}
	espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => $message));
}


//Set the default time zone
//If the default time zone is set up in the WP Settings, then we will use that as the default.
if (get_option('timezone_string') != '') {
	date_default_timezone_set(get_option('timezone_string'));
}


//Wordpress function for setting the locale.
//print get_locale();
//setlocale(LC_ALL, get_locale());
setlocale(LC_TIME, get_locale());

//Get language files
load_plugin_textdomain('event_espresso', false, dirname(plugin_basename(__FILE__)) . '/languages/');


//Addons
//Ticketing
if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "/ticketing/template.php") || function_exists('espresso_ticket_launch')) {
	global $ticketing_installed;
	$ticketing_installed = true;
}


//Global files
//Premium funtions. If this is a paid version, then we need to include these files.
if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/misc_functions.php')) {
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/misc_functions.php');
	global $espresso_premium;
	$espresso_premium = espresso_system_check();
}


//Core function files
require_once("includes/functions/main.php");
require_once("includes/functions/pricing.php");
require_once("includes/functions/time_date.php");
require_once("includes/shortcodes.php");
require_once("includes/functions/actions.php");
require_once("includes/functions/filters.php");
require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "includes/functions/ical.php");

/* Core template files used by this plugin */
//These may be loaded in posts and pages outside of the default EE pages
//Events Listing - Shows the events on your page. Used with the [ESPRESSO_EVENTS] shortcode
$event_list_template = 'event_list.php';
// HOOK - change event list template
$event_list_template = apply_filters('hook_espresso_event_list_template', $event_list_template);
event_espresso_require_template($event_list_template);

//This is the form page for registering the attendee
event_espresso_require_template('registration_page.php');

//Registration forms
require_once("includes/functions/form_build.php");

//List Attendees - Used with the [LISTATTENDEES] shortcode
event_espresso_require_template('attendee_list.php');

require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "includes/functions/cart.php");

//Custom post type integration
if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/custom_post_type.php') && isset($org_options['template_settings']['use_custom_post_types']) && $org_options['template_settings']['use_custom_post_types'] == 'Y') {
	require('includes/admin-files/custom_post_type.php');
}

//Widget - Display the list of events in your sidebar
//The widget can be over-ridden with the custom files addon
event_espresso_require_template('widget.php');

function espresso_widget() {
	register_widget('Event_Espresso_Widget');
}

add_action('widgets_init', 'espresso_widget');

/* End Core template files used by this plugin */

//Google map include file
if (!is_admin()) {
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/gmap_incl.php');
}

//Load these files if we are in an actuial registration page
function espresso_load_reg_page_files() {

	define("ESPRESSO_REG_PAGE_FILES_LOADED", "true");

	//Check to see if this a reg page
	//May cause admin and front facing pages to break if turned on
	//global $this_is_a_reg_page;
	//echo '<p>$this_is_a_reg_page ='.$this_is_a_reg_page .'</p>';
	//Process email confirmations
	require_once("includes/functions/email.php");

	//Various attendee functions
	require_once("includes/functions/attendee_functions.php");


	//Payment/Registration Processing - Used to display the payment options and the payment link in the email. Used with the [ESPRESSO_PAYMENTS] tag
	require_once("includes/process-registration/payment_page.php");

	//Add attendees to the database
	require_once("includes/process-registration/add_attendees_to_db.php");

	//Payment processing - Used for onsite payment processing. Used with the [ESPRESSO_TXN_PAGE] shortcode
	event_espresso_require_gateway('process_payments.php');
	event_espresso_require_gateway('PaymentGateway.php');

	// AJAX functions
	add_action('wp_ajax_event_espresso_add_item', 'event_espresso_add_item_to_session');
	add_action('wp_ajax_nopriv_event_espresso_add_item', 'event_espresso_add_item_to_session');
	add_action('wp_ajax_event_espresso_delete_item', 'event_espresso_delete_item_from_session');
	add_action('wp_ajax_nopriv_event_espresso_delete_item', 'event_espresso_delete_item_from_session');
	add_action('wp_ajax_event_espresso_update_item', 'event_espresso_update_item_in_session');
	add_action('wp_ajax_nopriv_event_espresso_update_item', 'event_espresso_update_item_in_session');
	add_action('wp_ajax_event_espresso_calculate_total', 'event_espresso_calculate_total');
	add_action('wp_ajax_nopriv_event_espresso_calculate_total', 'event_espresso_calculate_total');
	add_action('wp_ajax_event_espresso_load_regis_form', 'event_espresso_load_regis_form');
	add_action('wp_ajax_nopriv_event_espresso_load_regis_form', 'event_espresso_load_regis_form');
	add_action('wp_ajax_event_espresso_confirm_and_pay', 'event_espresso_confirm_and_pay');
	add_action('wp_ajax_nopriv_event_espresso_confirm_and_pay', 'event_espresso_confirm_and_pay');
}

if ($this_is_a_reg_page == TRUE) {
	espresso_load_reg_page_files();
}

//Build the addon files
if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/addons_includes.php')) {
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/addons_includes.php');
}
if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/coupon-management/index.php')) {
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/coupon-management/index.php');
	//Include dicount codes
	require_once("includes/admin-files/coupon-management/use_coupon_code.php");
} else {
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/lite-files/coupon_management.php');
}
require_once("includes/functions/admin.php");

//Admin only files
if (is_admin()) {
	// New form builder
	require_once("includes/form-builder/index.php");
	require_once("includes/form-builder/groups/index.php");

	if ($espresso_premium != true)
		require_once("includes/lite-files/test_drive_pro.php");

	// Install/Update Tables when plugin is activated
	require_once("includes/functions/database_install.php");
	register_activation_hook(__FILE__, 'events_data_tables_install');

	// Premium upgrade options if the paid plugin is not installed
	require_once("includes/lite-files/premium_upgrade.php");

	// Get the payment settings page
	event_espresso_require_gateway('payment_gateways.php');

	// Email Manager
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/email-manager/index.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/email-manager/index.php');
	} else {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/lite-files/email-manager.php');
	}

	// Event Registration Subpage - Add/Delete/Edit Venues
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/venue-management/index.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/venue-management/index.php');
	} else {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/lite-files/venue_management.php');
	}

	// Add/Delete/Edit Locales
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/locale-management/index.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/locale-management/index.php');
	} else {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/lite-files/locale_management.php');
	}

	// Add/Delete/Edit Staff
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/staff-management/index.php')) {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/staff-management/index.php');
	} else {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/lite-files/staff-management.php');
	}

	// Event editor premium functions
	event_espresso_require_file('functions.php', EVENT_ESPRESSO_PLUGINFULLPATH . "includes/admin-files/", '', false, true);

	// Available addons
	event_espresso_require_file('admin_addons.php', EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/', EVENT_ESPRESSO_PLUGINFULLPATH . '/includes/lite-files/', true, true);

	// Google Map Settings
	event_espresso_require_file('template_map_confg.php', EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/templates/', EVENT_ESPRESSO_PLUGINFULLPATH . '/includes/lite-files/', true, true);

	// Admin Widget - Display event stats in your admin dashboard
	event_espresso_require_file('dashboard_widget.php', EVENT_ESPRESSO_PLUGINFULLPATH . "includes/admin-files/", '', false, true);


	// Admin only functions
	event_espresso_require_file('admin_menu.php', EVENT_ESPRESSO_PLUGINFULLPATH . "includes/functions/", '', false, true);

	// Event Registration Subpage - Configure Organization
	event_espresso_require_file('organization_config.php', EVENT_ESPRESSO_PLUGINFULLPATH . "includes/settings/", '', false, true);

	// Event Registration Subpage - Add/Delete/Edit Events
	event_espresso_require_file('index.php', EVENT_ESPRESSO_PLUGINFULLPATH . "includes/event-management/", '', false, true);
	event_espresso_require_file('index.php', EVENT_ESPRESSO_PLUGINFULLPATH . "includes/admin-reports/", '', false, true);

	// Event styles & template layouts Subpage
	event_espresso_require_file('template_confg.php', EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin-files/templates/', EVENT_ESPRESSO_PLUGINFULLPATH . '/includes/lite-files/', true, true);


	// Plugin Support
	require_once("includes/admin_support.php");

	// Admin Reporting
	// require_once("includes/admin-reports/index.php");
	// Event Registration Subpage - Category Manager
	require_once("includes/category-management/index.php");

	// Load scripts and styles for the admin
	if (isset($_REQUEST['page'])) {
		$espresso_pages = array(
				'event_espresso',
				'discounts',
				'groupons',
				'event_categories',
				'admin_reports',
				'form_builder',
				'form_groups',
				'my-events',
				'event_emails',
				'event_venues',
				'event_staff',
				'events',
				'attendees',
				'espresso_reports',
				'support',
				'template_confg',
				'template_map_confg',
				'payment_gateways',
				'members',
				'espresso_social',
				'admin_addons',
				'espresso_calendar',
				'event_tickets',
				'event_certificates',
				'espresso-mailchimp',
				'espresso_permissions',
				'roles',
				'event_locales',
				'event_groups',
				'test_drive',
				'espresso_https'
		);
		if (in_array($_REQUEST['page'], $espresso_pages)) {
			add_action('admin_print_scripts', 'event_espresso_config_page_scripts');
			add_action('admin_print_styles', 'event_espresso_config_page_styles');
		}
	}

	// Update the question sequences
	add_action('wp_ajax_update_sequence', 'event_espresso_questions_config_mnu');
	// Update the question group sequences
	add_action('wp_ajax_update_qgr_sequence', 'event_espresso_question_groups_config_mnu');
}

//Load the required Javascripts
if (!function_exists('espresso_load_javascript_files')) {

	function espresso_load_javascript_files() {
		global $load_espresso_scripts;

		if (!$load_espresso_scripts)
			return;
		wp_register_script('reCopy', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/reCopy.js"), false, '1.1.0');
		wp_print_scripts('reCopy');

		wp_register_script('jquery.validate.js', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/jquery.validate.min.js"), false, '1.8.1');
		wp_print_scripts('jquery.validate.js');

		wp_register_script('validation', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/validation.js"), false, EVENT_ESPRESSO_VERSION);
		wp_print_scripts('validation');
	}

}
add_action('wp_footer', 'espresso_load_javascript_files');

if (!function_exists('espresso_load_jquery')) {

	function espresso_load_jquery() {
		global $org_options;
		if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
			espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => ''));
		}
		wp_enqueue_script('jquery');
		if (function_exists('event_espresso_multi_reg_init') || (isset($_REQUEST['page']) && ( $_REQUEST['page'] == 'form_builder' || $_REQUEST['page'] == 'form_groups'))) {
			wp_enqueue_script('ee_ajax_request', EVENT_ESPRESSO_PLUGINFULLURL . 'scripts/espresso_cart_functions.js', array('jquery'));
			wp_localize_script('ee_ajax_request', 'EEGlobals', array('ajaxurl' => admin_url('admin-ajax.php'), 'plugin_url' => EVENT_ESPRESSO_PLUGINFULLURL, 'event_page_id' => $org_options['event_page_id']));
		}
	}

}
add_action('init', 'espresso_load_jquery', 10);
// End Javascript files
// Load the style sheets for the reegistration pages
if (!function_exists('add_espresso_stylesheet')) {

	function add_espresso_stylesheet() {
		global $org_options;
		if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
			espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => ''));
		}
		/* 	if ($org_options['style_settings']['enable_default_style'] != 'Y')
		  return; */

		//Load the ThemeRoller styles if enabled
		if (!empty($org_options['style_settings']['enable_default_style']) && $org_options['style_settings']['enable_default_style'] == 'Y') {

			//Define the path to the ThemeRoller files
			if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "themeroller/index.php")) {
				$themeroller_style_path = EVENT_ESPRESSO_UPLOAD_URL . 'themeroller/';
			} else {
				$themeroller_style_path = EVENT_ESPRESSO_PLUGINFULLURL . 'templates/css/themeroller/';
			}

			//Load custom style sheet if available
			if (!empty($org_options['style_settings']['css_name'])) {
				wp_register_style('espresso_custom_css', EVENT_ESPRESSO_UPLOAD_URL . 'css/' . $org_options['style_settings']['css_name']);
				wp_enqueue_style('espresso_custom_css');
			}

			//Register the ThemeRoller styles
			wp_register_style('espresso_themeroller_base', $themeroller_style_path . 'themeroller-base.css');
			wp_enqueue_style('espresso_themeroller_base');
			wp_register_style('espresso_themeroller', $themeroller_style_path . $org_options['themeroller']['themeroller_style'] . '/style.css');
			wp_enqueue_style('espresso_themeroller');
		} else {
			//For backwards compatibilty we check options to see if event_espresso_style.css is set. If it is set, or no option is set, we load it from original folder.
			if (empty($org_options['style_settings']['selected_style']) || $org_options['style_settings']['selected_style'] == 'event_espresso_style.css') {
				$style_path = 'templates/event_espresso_style.css';
			} else {
				$style_path = 'templates/css/' . $org_options['style_settings']['selected_style'];
			}

			$event_espresso_style_sheet = EVENT_ESPRESSO_PLUGINFULLURL . $style_path;


			if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "templates/event_espresso_style.css")) {
				$event_espresso_style_sheet = EVENT_ESPRESSO_UPLOAD_URL . 'templates/event_espresso_style.css';
			}

			wp_register_style('event_espresso_style_sheets', $event_espresso_style_sheet);
			wp_enqueue_style('event_espresso_style_sheets');

			if (!file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "templates/event_espresso_style.css") && !empty($org_options['style_settings']['style_color'])) {

				$event_espresso_style_color = EVENT_ESPRESSO_PLUGINFULLURL . 'templates/css/colors/' . $org_options['style_settings']['style_color'];

				wp_register_style('_espresso_style_color', $event_espresso_style_color);
				wp_enqueue_style('espresso_style_color');
			}
		}
	}

}
add_action('wp_print_styles', 'add_espresso_stylesheet', 20);

// End styles

/**
 * Add a settings link to the Plugins page, so people can go straight from the plugin page to the
 * settings page.
 */
function event_espresso_filter_plugin_actions($links, $file) {
	// Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if (!$this_plugin)
		$this_plugin = plugin_basename(__FILE__);

	if ($file == $this_plugin) {
		$org_settings_link = '<a href="admin.php?page=event_espresso">' . __('Settings') . '</a>';
		$events_link = '<a href="admin.php?page=events">' . __('Events') . '</a>';
		array_unshift($links, $org_settings_link, $events_link); // before other links
	}
	return $links;
}

add_filter('plugin_action_links', 'event_espresso_filter_plugin_actions', 10, 2);


// Run the program
if (!function_exists('event_espresso_run')) {

	function event_espresso_run() {

		// grab some globals
		global $wpdb, $org_options, $load_espresso_scripts;
		if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
			espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => ''));
		}
		// tell the plugin to load the required scripts
		$load_espresso_scripts = true;

		// begin output buffering
		ob_start();

		//Make sure scripts are loading
		echo espresso_check_scripts();

		// Get action type
		$regevent_action = isset($_REQUEST['regevent_action']) ? $_REQUEST['regevent_action'] : '';

		if (isset($_REQUEST['ee']) or isset($_REQUEST['edit_attendee'])) {
			$regevent_action = "register";
		}

		switch ($regevent_action) {

			case "post_attendee":
				event_espresso_add_attendees_to_db();
				break;

			case "register":
				register_attendees();
				break;

			case "add_to_session":
				break;

			case "show_shopping_cart":
				//This is the form page for registering the attendee
				event_espresso_require_template('shopping_cart.php');
				event_espresso_shopping_cart();
				break;

			case "load_checkout_page":
				if ($_POST)
					event_espresso_calculate_total('details');
				event_espresso_load_checkout_page();
				break;

			case "post_multi_attendee":
				//echo " YESssss";
				event_espresso_update_item_in_session('attendees');
				event_espresso_add_attendees_to_db_multi();
				break;

			default:

				$display_all_events = TRUE;
				// allow others to hook into regevent action and reroute the progam flow
				do_action('hook_espresso_reroute_regevent_action', $regevent_action, $display_all_events);
				// use filter to change the value of $display_all_events to FALSE
				$display_all_events = apply_filters('hook_espresso_display_all_events', $display_all_events);
				// the above hook was not used to reroute the progam flow, then display_all_events
				if ($display_all_events != FALSE) {
					display_all_events();
				}
		}

		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

}
add_shortcode('ESPRESSO_EVENTS', 'event_espresso_run');


if (isset($_REQUEST['authAmountString'])) {
	add_action('posts_selection', 'event_espresso_txn');
}

// Load the admin menu styles
wp_enqueue_style('espresso_menu', EVENT_ESPRESSO_PLUGINFULLURL . 'css/admin-menu-styles.css');

// These actions need to be loaded a the bottom of this script to prevent errors when post/get requests are received.

if (is_admin()) {
	// Check to make sure there are no empty registration id fields in the database.
	if (event_espresso_verify_attendee_data() == true &&
					(empty($_POST['action']) || $_POST['action'] != 'event_espresso_update_attendee_data')) {
		add_action('admin_notices', 'event_espresso_registration_id_notice');
	}

	// copy themes to template directory
	if (isset($_REQUEST['event_espresso_admin_action'])) {
		if ($_REQUEST['event_espresso_admin_action'] == 'copy_templates') {
			add_action('admin_init', 'event_espresso_trigger_copy_templates');
		}
	}
	// copy gateways to gateway directory
	if (isset($_REQUEST['event_espresso_admin_action'])) {
		if ($_REQUEST['event_espresso_admin_action'] == 'copy_gateways') {
			add_action('admin_init', 'event_espresso_trigger_copy_gateways');
		}
	}
	// Check to make sure all of the main pages are setup properly, if not show an admin message.
	if (empty($org_options['event_page_id'])
					|| empty($org_options['return_url'])
					|| empty($org_options['notify_url'])) {
		add_action('admin_notices', 'event_espresso_activation_notice');
	}
}

// Export PDF Certificate
function espresso_export_certificate() {
	if (isset($_REQUEST['certificate_launch']) && $_REQUEST['certificate_launch'] == 'true') {
		echo espresso_certificate_launch($_REQUEST['id'], $_REQUEST['r_id']);
	}
}

add_action('plugins_loaded', 'espresso_export_certificate');

// Export Invoice
function espresso_export_invoice() {
	//Version 2.0
	if (isset($_REQUEST['invoice_launch']) && $_REQUEST['invoice_launch'] == 'true') {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/invoice/launch_invoice.php");
		echo espresso_invoice_launch($_REQUEST['id'], $_REQUEST['r_id']);
	}
	//End Version 2.0
	//Export pdf version
	if (isset($_REQUEST['download_invoice']) && $_REQUEST['download_invoice'] == 'true') {
		require_once(EVENT_ESPRESSO_PLUGINFULLPATH . "gateways/invoice/template.php");
	}
	//End pdf version
}

add_action('plugins_loaded', 'espresso_export_invoice');

// Export iCal file
if (!empty($_REQUEST['iCal'])) {
	espresso_ical();
}

// PUE Auto Upgrades stuff
if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) {
	//let's get the client api key for updates
	global $org_options;

	if (!empty($org_options['full_logging']) && $org_options['full_logging'] == 'Y') {
		espresso_log::singleton()->log(array('file' => __FILE__, 'function' => __FUNCTION__, 'status' => ''));
	}
	if ( empty($org_options['site_license_key']) ){
		$org_options['site_license_key'] = 0;
	}
	$api_key = $org_options['site_license_key'];
	$host_server_url = 'http://eventespresso.com.s128453.gridserver.com/';
	$plugin_slug = 'event-espresso';
	$options = array(
			'apikey' => $api_key,
			'lang_domain' => 'event_espresso'
	);

	require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
	//$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options);
}
