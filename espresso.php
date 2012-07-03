<?php 
/*
Plugin Name: Event Espresso
Plugin URI: http://eventespresso.com/
Description: Out-of-the-box Events Registration integrated with PayPal IPN for your Wordpress blog/website. <a href="admin.php?page=support" >Support</a>

Reporting features provide a list of events, list of attendees, and excel export.

Version: 3.0.18.b.24

Author: Seth Shoultes
Author URI: http://www.eventespresso.com
 
Copyright (c) 2008-2010 Seth Shoultes  All Rights Reserved.

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

//Start our session.
$a = session_id();

//If you are having problems with sessions already started, comment out the next line
if ($a == '') @session_start();

if ($_SESSION['espresso_session_id'] =='')
{
	$_SESSION['espresso_session_id'] = (mt_rand(100,999).time());//Using a random number and time to create.
}

//Define the version of the plugin
function espresso_version() {
	return '3.0.18.b.24';
}
function espresso_info_header() {
	print( "<meta name='generator' content='Event Espresso Version " . EVENT_ESPRESSO_VERSION . "' />");
}
add_action('wp_head', 'espresso_info_header');

define("EVENT_ESPRESSO_VERSION", espresso_version() );
define('EVENT_ESPRESSO_POWERED_BY', 'Event Espresso - ' . EVENT_ESPRESSO_VERSION);
//Define the plugin directory and path
define("EVENT_ESPRESSO_PLUGINPATH", "/" . plugin_basename( dirname(__FILE__) ) . "/");
define("EVENT_ESPRESSO_PLUGINFULLPATH", WP_PLUGIN_DIR . EVENT_ESPRESSO_PLUGINPATH  );
define("EVENT_ESPRESSO_PLUGINFULLURL", WP_PLUGIN_URL . EVENT_ESPRESSO_PLUGINPATH );
//End - Define the plugin directory and path

//Define dierectory structure for uploads
if ( !defined('WP_CONTENT_DIR') ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content');
}
$upload_path = WP_CONTENT_DIR."/uploads";
$event_espresso_upload_dir = "{$upload_path}/espresso/";
$event_espresso_template_dir = "{$event_espresso_upload_dir}templates/";

$includes_directory = EVENT_ESPRESSO_PLUGINFULLPATH.'includes/';
define("EVENT_ESPRESSO_INCLUDES_DIR", $includes_directory);

define("EVENT_ESPRESSO_UPLOAD_DIR", $event_espresso_upload_dir);
define("EVENT_ESPRESSO_UPLOAD_URL", WP_CONTENT_URL . '/uploads/espresso/');
define("EVENT_ESPRESSO_TEMPLATE_DIR", $event_espresso_template_dir);
$event_espresso_gateway_dir = EVENT_ESPRESSO_UPLOAD_DIR."gateways/";	
define("EVENT_ESPRESSO_GATEWAY_DIR", $event_espresso_gateway_dir);
define("EVENT_ESPRESSO_GATEWAY_URL", WP_CONTENT_URL . '/uploads/espresso/gateways/');
//End - Define dierectory structure for uploads

require_once EVENT_ESPRESSO_PLUGINFULLPATH . 'class/SimpleMath.php';
global $simpleMath;
$simpleMath  = new SimpleMath();

//Set the default time zone
//If the default time zone is set up in the WP Settings, then we will use that as the default.
if (get_option('timezone_string') != ''){
	date_default_timezone_set(get_option('timezone_string'));
}

//Build the addon files
require_once("includes/addons_includes.php");

//Call the required function files
require_once("includes/functions/main.php");
require_once("includes/functions/admin.php");
require_once("includes/functions/time_date.php");

//Install/Update Tables when plugin is activated
require_once("includes/database_install.new.php"); 
register_activation_hook(__FILE__,'events_data_tables_install');

//Define all of the plugins database tables
define("EVENTS_CATEGORY_TABLE", get_option('events_category_detail_tbl') );
define("EVENTS_CATEGORY_REL_TABLE", get_option('events_category_rel_tbl') );
define("EVENTS_DETAIL_TABLE", get_option('events_detail_tbl') );
define("EVENTS_ORGANIZATION_TABLE", get_option('events_organization_tbl') );
define("EVENTS_ATTENDEE_TABLE", get_option('events_attendee_tbl') );
define("ADDITIONAL_ATTENDEES_TABLE", get_option('additional_attendees_tbl') );
define("EVENTS_START_END_TABLE", get_option('events_start_end_tbl') );
define("EVENTS_PAYMENT_GATEWAYS_TABLE", get_option('events_payment_gateways_tbl') );
define("EVENTS_QUESTION_TABLE", get_option('events_question_tbl') );
define("EVENTS_QST_GROUP_REL_TABLE", get_option('events_qst_group_rel_tbl') );
define("EVENTS_QST_GROUP_TABLE", get_option('events_qst_group_tbl') );
define("EVENTS_ANSWER_TABLE",get_option('events_answer_tbl') );
define("EVENTS_DISCOUNT_CODES_TABLE", get_option('events_discount_codes_tbl') );
define("EVENTS_DISCOUNT_REL_TABLE", get_option('events_discount_rel_tbl') );
define("EVENTS_PRICES_TABLE", get_option('events_prices_tbl') );
define("EVENTS_EMAIL_TABLE", get_option('events_email_tbl') );
define("EVENTS_SESSION_TABLE", get_option('events_sessions_tbl') );

//Globals
global $org_options;
$org_options = get_option('events_organization_settings');

//Wordpress function for setting the locale.
//print get_locale();
//setlocale(LC_ALL, get_locale());
setlocale(LC_TIME, get_locale());

//Get language files
load_plugin_textdomain( 'event_espresso', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

//Registration forms
require_once("includes/event_espresso_form_build.inc.php");

//New form builder
require_once("includes/form-builder/index.php");
require_once("includes/form-builder/groups/index.php");

//Payment/Registration Processing - Used to display the payment options and the payment link in the email. Used with the [ESPRESSO_PAYMENTS] tag
require_once("includes/process-registration/payment_page.php");

//Add attendees to the database
require_once("includes/process-registration/add_attendees_to_db.php");

//Payment processing - Used for onsite payment processing. Used with the [ESPRESSO_TXN_PAGE] tag
if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/process_payments.php")){
	require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/process_payments.php");
}else{
	require_once("gateways/process_payments.php");
}
//Get the payment settings page
if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/payment_gateways.php")){
	require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/payment_gateways.php");
}else{
	require_once("gateways/payment_gateways.php");
}

//Get the payment gateways class
if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/PaymentGateway.php")){
	require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/PaymentGateway.php");
}else{
	require_once(EVENT_ESPRESSO_PLUGINFULLPATH."gateways/PaymentGateway.php");
}

/*Core template files used by this plugin*/
//Events Listing - Shows the events on your page. Used with the [ESPRESSO_EVENTS] tag
if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR."event_list.php")){
	require_once(EVENT_ESPRESSO_TEMPLATE_DIR."event_list.php");//This is the path to the template file if available
}else{
	require_once("templates/event_list.php");
}

//This is the form page for registering the attendee
if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR."registration_page.php")){
	require_once(EVENT_ESPRESSO_TEMPLATE_DIR."registration_page.php");//This is the path to the template file if available
	//require_once(EVENT_ESPRESSO_TEMPLATE_DIR."multi_registration_page.php");//This is the path to the template file if available
}else{
	require_once("templates/registration_page.php");
	//require_once("templates/multi_registration_page.php");
}

//List Attendees - Used with the [LISTATTENDEES] shortcode
if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR."attendee_list.php")){
	require_once(EVENT_ESPRESSO_TEMPLATE_DIR."attendee_list.php");//This is the path to the template file if available
}else{
	require_once("templates/attendee_list.php");
}
/*End Core template files used by this plugin*/

//Widget - Display the list of events in your sidebar
//The widget can be over-ridden with the custom files addon
if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR."widget.php")){
	require_once(EVENT_ESPRESSO_TEMPLATE_DIR."widget.php");//This is the path to the template file if available
}else{
	require_once("templates/widget.php");
}

function load_event_espresso_widget() {
	register_widget( 'Event_Espresso_Widget' );
}
add_action( 'widgets_init', 'load_event_espresso_widget' );

//Admin Widget - Display event stats in your admin dashboard
require_once("includes/admin-files/dashboard_widget.php");

//Include functionality for the Calendar plugin
require_once("includes/calendar_support.php");
add_action('admin_head', 'event_espresso_calendar_support');

//Event Registration Subpage - Configure Organization
require_once("includes/organization_config.php");

//Event Registration Subpage - Add/Delete/Edit Events
require_once("includes/event-management/index.php");

//Event Registration Subpage - Add/Delete/Edit Discount Codes
require_once("includes/coupon-management/index.php");
//Include dicount codes
require_once("includes/coupon-management/use_coupon_code.php");

//Event Registration Subpage - Admin Reporting
//require_once("includes/admin-reports/index.php");

//Event Registration Subpage - Category Manager
require_once("includes/category-management/index.php");

//Event Registration Subpage - Email Manager
require_once("includes/email-manager/index.php");

//Event Registration Subpage - Plugin Support
require_once("includes/admin_support.php");

//Process email confirmations
require_once("includes/functions/email.php");

//Process email confirmations
require_once("includes/functions/attendee_functions.php");

//Admin Widget - Display the list of events in your admin dashboard
require_once("includes/admin_addons.php");

//Core shortcode support
require_once("includes/shortcodes.php");

/*
 * 
 * turning off db session handling for now
 * will turn it back on after the multi reg is ready
 * 
 */
//require_once("includes/functions/session.php");
/* Set the session to expire in 30 minutes
ini_set('session.gc_maxlifetime',30*60);
ini_set('session.gc_probability',1);
ini_set('session.gc_divisor',1);
$session = new Session();
session_set_save_handler(array($session, 'open'),
                         array($session, 'close'),
                         array($session, 'read'),
                         array($session, 'write'),
                         array($session, 'destroy'),
                         array($session, 'gc'));

*/
/*session_start();
//session_regenerate_id(true);

if ($_SESSION['espresso_session_id'] =='')
{
	$_SESSION['espresso_session_id'] =  session_id();
}*/


$events_in_session = $_SESSION['events_in_session'];
global $events_in_session;

wp_localize_script( 'ee_ajax_request', 'EEGlobals', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => EVENT_ESPRESSO_PLUGINFULLURL ) ) ;

//Custom post type integration
if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH.'includes/admin-files/custom_post_type.php') && $org_options['use_custom_post_types']=='Y'){
	require('includes/admin-files/custom_post_type.php');
}
//Load the required Javascripts
add_action('wp_footer', 'espresso_load_javascript_files');
add_action('init', 'espresso_load_jquery');
if (!function_exists('espresso_load_javascript_files')) {
	function espresso_load_javascript_files() {
		global $load_espresso_scripts;
	 
		if ( ! $load_espresso_scripts )
			return;		
		wp_register_script('reCopy', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/reCopy.js"), false, '1.1.0'); 
		wp_print_scripts('reCopy');
		
		wp_register_script('jquery.validate.pack', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/jquery.validate.pack.js"), false, '1.7');
		wp_print_scripts('jquery.validate.pack');
		
		wp_register_script('validation', (EVENT_ESPRESSO_PLUGINFULLURL . "scripts/validation.js"), false,  EVENT_ESPRESSO_VERSION);
		wp_print_scripts('validation');
	}
}
if (!function_exists('espresso_load_jquery')) {
	function espresso_load_jquery() {
		wp_enqueue_script('jquery');
	}
}
//Build the admin menu
if (!function_exists('add_event_espresso_menus')) {
	function add_event_espresso_menus() {
	
		//Main menu tab
		add_menu_page(__('Event Espresso','event_espresso'), __('Event Espresso','event_espresso'), 'administrator', 'event_espresso', 'organization_config_mnu', EVENT_ESPRESSO_PLUGINFULLURL.'images/events_icon_16.png');
		
		//General Settings
		add_submenu_page('event_espresso', __('Event Espresso - General Settings','event_espresso'), __('General Settings','event_espresso'), 'administrator',  'event_espresso', 'organization_config_mnu');
		
		//Event Setup
		add_submenu_page('event_espresso', __('Event Espresso - Event Overview','event_espresso'), __('Event Overview','event_espresso'), 'administrator', 'events', 'event_espresso_manage_events');
		
		//Attendees/Payments
		//add_submenu_page('event_espresso', __('Event Espresso - Attendees and Payments','event_espresso'), __('Attendees/Payments','event_espresso'), 'administrator', 'admin_reports', 'event_admin_reports');
		
		//EventCategories
		add_submenu_page('event_espresso', __('Event Espresso - Manage Event Categories','event_espresso'), __('Categories','event_espresso'), 'administrator', 'event_categories', 'event_espresso_categories_config_mnu');
			
		//Discounts
		add_submenu_page('event_espresso', __('Event Espresso - Discounts','event_espresso'), __('Discounts','event_espresso'), 'administrator', 'discounts', 'event_espresso_discount_config_mnu');
		
		//Groupons
		if (function_exists('event_espresso_groupon_config_mnu')) {
			add_submenu_page('event_espresso', __('Groupons','event_espresso'), __('Groupon Codes','event_espresso'), 'administrator', 'groupons', 'event_espresso_groupon_config_mnu');
		}
		
		
		//Form Questions
		add_submenu_page('event_espresso', __('Event Espresso - Questions','event_espresso'), __('Questions','event_espresso'), 'administrator', 'form_builder', 'event_espresso_questions_config_mnu');
		
		//Questions Groups
		add_submenu_page('event_espresso', __('Event Espresso - Question Groups','event_espresso'), __('Question Groups','event_espresso'), 'administrator', 'form_groups', 'event_espresso_question_groups_config_mnu');
		
		//Email Manager
		add_submenu_page('event_espresso', __('Event Espresso - Email Manager','event_espresso'), __('Email Manager','event_espresso'), 'administrator', 'event_emails', 'event_espresso_email_config_mnu');
				
		
		//Payment Settings
		if (function_exists('event_espresso_agteways_mnu')) {
			add_submenu_page('event_espresso', __('Event Espresso - Payment Settings','event_espresso'), __('Payment Settings','event_espresso'), 'administrator', 'payment_gateways', 'event_espresso_agteways_mnu');
		}
		
		//Member Settings
		if (function_exists('event_espresso_member_config_mnu')) {
			add_submenu_page('event_espresso', __('Event Espresso - Member Settings','event_espresso'), __('Member Settings','event_espresso'), 'administrator', 'members', 'event_espresso_member_config_mnu');
		}
		
		//Calendar Settings
		if (is_plugin_active('espresso-calendar/espresso-calendar.php')) {
			add_submenu_page('event_espresso', __('Event Espresso - Calendar Settings','event_espresso'), __('Calendar Settings','event_espresso'), 'administrator', 'espresso_calendar', 'espresso_calendar_config_mnu');
		}
		
		//Social Media Settings
		if (is_plugin_active('espresso-social/espresso-social.php')) {
			add_submenu_page('event_espresso', __('Event Espresso - Social Media Settings','event_espresso'), __('Social Media','event_espresso'), 'administrator', 'espresso_social', 'espresso_social_config_mnu');
		}
		
		//Addons
		add_submenu_page('event_espresso', __('Event Espresso - Addons','event_espresso'), __('Addons','event_espresso'), 'administrator', 'admin_addons', 'event_espresso_addons_mnu');
		
		//Help/Support
		add_submenu_page('event_espresso', __('Event Espresso - Help/Support','event_espresso'), __('Help/Support','event_espresso'), 'administrator', 'support', 'event_espresso_support');

	}
}



/**
 * Add a settings link to the Plugins page, so people can go straight from the plugin page to the
 * settings page.
 */
function event_espresso_filter_plugin_actions( $links, $file ){
	// Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	
	if ( $file == $this_plugin ){
		$org_settings_link = '<a href="admin.php?page=event_espresso">' . __('Settings') . '</a>';
		$events_link = '<a href="admin.php?page=events">' . __('Events') . '</a>';
		array_unshift( $links, $org_settings_link, $events_link ); // before other links
	}
	return $links;
}
add_filter( 'plugin_action_links', 'event_espresso_filter_plugin_actions', 10, 2 );



//ADMIN MENU
add_action('admin_menu', 'add_event_espresso_menus');
add_action('admin_print_scripts', 'event_espresso_config_page_scripts');
add_action('admin_print_styles', 'event_espresso_config_page_styles');

//Run the program
if (!function_exists('event_espresso_run')) {
	function event_espresso_run(){
		global $wpdb, $org_options, $load_espresso_scripts;	
 
		$load_espresso_scripts = true;//This tells the plugin to load the required scripts				
		ob_start();
			if ($_REQUEST['regevent_action'] == "post_attendee"){
					event_espresso_add_attendees_to_db();
			}else if ($_REQUEST['regevent_action'] == "register"){
				register_attendees();
			}else if ($_REQUEST['regevent_action'] == "add_to_session"){
                            
			}else if ($_REQUEST['regevent_action'] == "show_shopping_cart"){
                            //This is the form page for registering the attendee
                            if (file_exists(EVENT_ESPRESSO_TEMPLATE_DIR."shopping_cart.php")){
                                    require_once(EVENT_ESPRESSO_TEMPLATE_DIR."shopping_cart.php");//This is the path to the template file if available
                            }else{
                                    require_once("templates/shopping_cart.php");
                            }
                            event_espresso_shopping_cart();
                        }
			else if ($_REQUEST['regevent_action'] == "load_checkout_page"){


                            event_espresso_load_checkout_page();
                        }
			else if ($_POST['regevent_action'] == "post_multi_attendee"){


                            event_espresso_add_attendees_to_db_multi();
                        }
                        else {
				display_all_events();
			}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}

//New way of doing it with showrtcodes
add_shortcode('ESPRESSO_PAYMENTS', 'event_espresso_pay');
add_shortcode('ESPRESSO_TXN_PAGE', 'event_espresso_txn');
add_shortcode('ESPRESSO_EVENTS', 'event_espresso_run');

//Export data to Excel file
if (isset($_REQUEST['export'])){
	switch ($_REQUEST['export']) {
	 case "report";
		global $wpdb;
		
		$id= $_REQUEST['id'];
		$today = date("Y-m-d-Hi",time()); 
		
		$results = $wpdb->get_row("SELECT id, event_name, event_desc, event_identifier, question_groups FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $id . "'", ARRAY_N);
		   
		list($event_id, $event_name, $event_description, $event_identifier, $question_groups) = $results;
		
		$basic_header = array(__('Group','event_espresso'),__('Reg ID','event_espresso'), __('Last Name','event_espresso'), __('First Name','event_espresso'), __('Email','event_espresso'), __('Address','event_espresso'), __('Address 2','event_espresso'), __('City','event_espresso'), __('State','event_espresso'), __('Zip','event_espresso'), __('Phone','event_espresso'), __('Payment Method','event_espresso'), __('Reg Date','event_espresso'), __('Pay Status','event_espresso'), __('Type of Payment','event_espresso'), __('Transaction ID','event_espresso'), __('Payment','event_espresso'), __('Coupon Code','event_espresso'), __('# Attendees','event_espresso'), __('Date Paid','event_espresso'), __('Event Name','event_espresso'), __('Price Option','event_espresso'), __('Event Date','event_espresso'), __('Event Time','event_espresso') );
		
		
		switch ($_REQUEST['action']) {
			case "payment";
				if (count($question_groups) > 0){
				$questions_in = '';
	$question_sequence = array();
		$question_groups = unserialize($question_groups);
				foreach ($question_groups as $g_id) $questions_in .= $g_id . ',';
	
					$questions_in = substr($questions_in,0,-1);
					$group_name = '';
					$counter = 0;
					
					$quest_sql = "SELECT q.question FROM " . EVENTS_QUESTION_TABLE . " q ";
					$quest_sql .= " JOIN " .  EVENTS_QST_GROUP_REL_TABLE . " qgr on q.id = qgr.question_id ";
					$quest_sql .= " JOIN " . EVENTS_QST_GROUP_TABLE . " qg on qg.id = qgr.group_id ";
					$quest_sql .= " WHERE qgr.group_id in ( " . $questions_in . ") ";
					
					//Fix from Jesse in the forums (http://eventespresso.com/forums/2010/10/form-questions-appearing-in-wrong-columns-in-excel-export/)
					//$quest_sql .= " AND q.system_name is null ORDER BY qg.id, q.id ASC ";
					$quest_sql .= " AND q.system_name is null ORDER BY q.sequence ASC ";
									
					$questions = $wpdb->get_results($quest_sql);
	
					$num_rows = $wpdb->num_rows;
					if ($num_rows > 0){
						foreach ($questions as $question) {
							array_push($basic_header, $question->question);
							//array_push($question_sequence, $question->sequence);
						}
					}
				}
	
			//$participants = $wpdb->get_results("SELECT * FROM ".EVENTS_ATTENDEE_TABLE." WHERE event_id = '$event_id'");
					
			//$participants = $wpdb->get_results("SELECT ed.event_name, ed.start_date, a.id, a.lname, a.fname, a.email, a.address, a.city, a.state, a.zip, a.phone, a.payment, a.date, a.payment_status, a.txn_type, a.txn_id, a.amount_pd, a.quantity, a.coupon_code, a.payment_date, a.event_time, a.price_option FROM " . EVENTS_ATTENDEE_TABLE . " a JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=a.event_id WHERE ed.id = '" . $event_id . "'");
					
				$sql = "SELECT ed.event_name, ed.start_date, a.id, a.registration_id, a.lname, a.fname, a.email, a.address, a.address2, a.city";
				$sql .= ", a.state, a.zip, a.phone, a.payment, a.date, a.payment_status, a.txn_type, a.txn_id";
				$sql .= ", a.amount_pd, a.quantity, a.coupon_code";
					
				//Add groupon reference if installed
				if (file_exists("addons/groupon_functions.php")){
					$sql .= ", a.groupon_code";
				}
					
				$sql .= ", a.payment_date, a.event_time, a.price_option";
				$sql .= " FROM " . EVENTS_ATTENDEE_TABLE . " a ";
				$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " ed ON ed.id=a.event_id ";
				$sql .= $_REQUEST['all_events'] == "true"? '' :	" WHERE ed.id = '" . $event_id . "' ";
				$sql .= " ORDER BY a.id ";
				
				$participants = $wpdb->get_results($sql);
	
				$filename = sanitize_title_with_dashes($event_name) . "-" . $today ;
				switch ($_REQUEST['type']) {
					case "csv" :
						$st = "";
						$et = ",";
						$s = $et . $st;
						header("Content-type: application/x-msdownload");
						header("Content-Disposition: attachment; filename=" . $filename . ".csv");
						//header("Content-Disposition: attachment; filename='" .$filename .".csv'"); 
						header("Pragma: no-cache"); 
						header("Expires: 0"); 
						//echo header
						echo implode($s, $basic_header) . "\r\n";
					break;
						 
					default :
						$st = "";
						$et = "\t";
						$s = $et . $st;
						header("Content-Disposition: attachment; filename=" . $filename . ".xls");
						//header("Content-Disposition: attachment; filename='" .$filename .".xls'");
						header("Content-Type: application/vnd.ms-excel");
						header("Pragma: no-cache"); 
						header("Expires: 0"); 
						//echo header
						echo implode($s, $basic_header) . $et . "\r\n";
					break;
				}
					//echo data
					if ($participants) {
						$temp_reg_id = ''; //will temporarily hold the registration id for checking with the next row
						$attendees_group = ''; //will hold the names of the group members
						$group_counter = 1;
						$amount_pd = 0;
	
						foreach ($participants as $participant) {
	
							if ( $temp_reg_id == '' ){
								$temp_reg_id = $participant->registration_id;
								$amount_pd = $participant->amount_pd;
							}
	
	
										if ( $temp_reg_id == $participant->registration_id )
										{
											
										} else {
	
											$group_counter++;
											$temp_reg_id = $participant->registration_id;
	
										}
											$attendees_group = "Group $group_counter";
	
							echo $attendees_group
							. $s . $participant->id
													. $s . $participant->lname
							. $s . $participant->fname
							. $s . $participant->email
							. $s . $participant->address
							. $s . $participant->address2
							. $s . $participant->city
							. $s . $participant->state
							. $s . $participant->zip
							. $s . $participant->phone
							. $s . $participant->payment
							. $s . $participant->date
							. $s . $participant->payment_status
							. $s . $participant->txn_type
							. $s . $participant->txn_id
							. $s . $participant->amount_pd
							. $s . $participant->coupon_code
							. $s . $participant->quantity
							. $s . $participant->payment_date
							. $s . $participant->event_name
							. $s . $participant->price_option
							. $s . $participant->start_date
							. $s . $participant->event_time
							;
							$answers = $wpdb->get_results("SELECT a.answer FROM " . EVENTS_ANSWER_TABLE . " a JOIN ".EVENTS_QUESTION_TABLE." q ON q.id = a.question_id WHERE registration_id = '" . $participant->registration_id . "' ORDER BY q.sequence asc");
	
							foreach($answers as $answer) {
								$search = array("\r", "\n", "\t");
								$clean_answer = str_replace($search, " ", $answer->answer);
								echo $s . $clean_answer;
							}
							switch ($_REQUEST['type']) {
								case "csv" :
									echo "\r\n";
								break;
								default :
									echo $et . "\r\n";
								break;
							}
					}
					} else {
						echo '<tr><td>'.__('No participant data has been collected.','event_espresso').'</td></tr>';
					}
					exit;
			break;
			
			default:
			echo '<p>'.__('This Is Not A Valid Selection!','event_espresso').'</p>';
			break;
		}
		
		default:
		break;
	}
}


/*
* These actions need to be loaded a the bottom of this script to prevent errors when post/get requests are received.
*/

//Export PDF invoice
if (isset($_REQUEST['download_invoice'])){
	if (get_option('events_invoice_payment_active') == 'true' && $_REQUEST['download_invoice'] == 'true'){		
		if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/template.php")){
			require_once(EVENT_ESPRESSO_GATEWAY_DIR . "/invoice/template.php");
		}else{
			require_once(EVENT_ESPRESSO_PLUGINFULLPATH. "gateways/invoice/template.php");
		}	
	}
}

//Export PDF Ticket
if (isset($_REQUEST['download_ticket'])){
	if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "/ticketing/template.php")){
		require_once(EVENT_ESPRESSO_UPLOAD_DIR . "/ticketing/template.php");
		espresso_ticket($_REQUEST['id'],$_REQUEST['registration_id']);
	}
}

//Check to make sure all of the main pages are setup properly, if not show an admin message.
if($_REQUEST['event_page_id'] == NULL && ($org_options['event_page_id']==('0'||'') || $org_options['return_url']==('0'||'') || $org_options['notify_url']==('0'||''))){
	add_action( 'admin_notices', 'event_espresso_activation_notice');
}

//Check to make sure there are no empty registration id fields in the database.
if (event_espresso_verify_attendee_data() == true && $_POST['action'] != 'event_espresso_update_attendee_data'){
	add_action( 'admin_notices', 'event_espresso_registration_id_notice');
}

//copy themes to template directory
if (isset($_REQUEST['event_espresso_admin_action'])){
	if($_REQUEST['event_espresso_admin_action'] == 'copy_templates') {
		add_action('admin_init', 'event_espresso_trigger_copy_templates');
	}
}
//copy gateways to gateway directory
if (isset($_REQUEST['event_espresso_admin_action'])){
	if($_REQUEST['event_espresso_admin_action'] == 'copy_gateways') {
		add_action('admin_init', 'event_espresso_trigger_copy_gateways');
	}
}

//Load the EE short URL
if 	(isset($_REQUEST['ee'])){
	espresso_redirect($_REQUEST['ee']);
}
