<?php
function espresso_system_check() {
    return true;
}

if ( !function_exists( 'event_espresso_custom_questions_output' ) ){
    function event_espresso_custom_questions_output( $atts ) {
    global $wpdb;

     extract( $atts );
//Get the questions for the attendee

		$sql = "SELECT ea.answer, eq.question FROM " . EVENTS_ANSWER_TABLE . " ea ";
		$sql .= " LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id ";
		$sql .= " WHERE ea.attendee_id = '" . $attendee_id . "' ";
		$all_questions == TRUE ? '':$sql .= " AND system_name IS NULL ";
		!empty($show_admin) && $show_admin == TRUE ? '':$sql .= " AND eq.admin_only != 'Y' ";
		$sql .= " ORDER BY eq.sequence asc ";

        $questions = $wpdb->get_results( $sql );
        //echo $wpdb->last_query . '<br />';

        $email_questions = '';
        $q_counter = 0;
        $q_num_rows = $wpdb->num_rows;
        if ( $q_num_rows > 0 )
        {

            foreach ( $questions as $question ) {
                $email_questions .= $question->answer != '' ? wpautop( '<strong>' . $question->question . ':</strong><br /> ' . str_replace( ',', '<br />', $question->answer ) ) : '';
                $q_counter++;
                if ( $q_counter == $q_num_rows )
                   return $email_questions;
            }
        }
        return $email_questions;
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
	$ueip_has_notified = get_option('ee_ueip_has_notified');

	//has optin been selected for datacollection?
	$espresso_data_optin = !empty($ueip_optin) ? $ueip_optin : NULL;

	if ( empty($ueip_has_notified) ) {
		add_action('admin_notices', 'espresso_data_collection_optin_notice');
		add_action('admin_enqueue_scripts', 'espresso_data_collection_enqueue_scripts' );
		add_action('wp_ajax_espresso_data_optin', 'espresso_data_optin_ajax_handler');
		update_option('ee_ueip_optin', 'yes');
		$espresso_data_optin = 'yes';
	}

	//let's prepare extra stats
	$extra_stats = array();

	//only collect extra stats if the plugin user has opted in.
	if ( !empty($espresso_data_optin) && $espresso_data_optin == 'yes' ) {
	
		//active gateways
		$active_gateways = get_option('event_espresso_active_gateways');
		if ( !empty($active_gateways ) ) {
			foreach ( (array) $active_gateways as $gateway => $ignore ) {
				$extra_stats[$gateway . '_gateway_active'] = 1;
			}
		}
	}

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file 
			require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
			$api_key = isset($org_options['site_license_key']) ? $org_options['site_license_key'] : '';
			$host_server_url = 'http://eventespresso.com'; //this needs to be the host server where plugin update engine is installed.
			$plugin_slug = 'event-espresso-pr'; //this needs to be the slug of the plugin/addon that you want updated (and that pue-client.php is included with).  This slug should match what you've set as the value for plugin-slug when adding the plugin to the plugin list via plugin-update-engine on your server.
			//$options needs to be an array with the included keys as listed.
			$options = array(
			//	'optionName' => '', //(optional) - used as the reference for saving update information in the clients options table.  Will be automatically set if left blank.
				'apikey' => $api_key, //(required), you will need to obtain the apikey that the client gets from your site and then saves in their sites options table (see 'getting an api-key' below)
				'lang_domain' => 'event_espresso', //(optional) - put here whatever reference you are using for the localization of your plugin (if it's localized).  That way strings in this file will be included in the translation for your plugin.
				'checkPeriod' => '12', //(optional) - use this parameter to indicate how often you want the client's install to ping your server for update checks.  The integer indicates hours.  If you don't include this parameter it will default to 12 hours.
				'option_key' => 'site_license_key', //this is what is used to reference the api_key in your plugin options.  PUE uses this to trigger updating your information message whenever this option_key is modified.
				'options_page_slug' => 'event_espresso',
				'extra_stats' => $extra_stats
			);
			$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
		}
}


/**
 * The purpose of this function is to display information about Event Espresso data collection and a optin selection for extra data collecting by users.
 * @return string html.
 */
function espresso_data_collection_optin_notice() {
	?>
	<div class="updated data-collect-optin" id="espresso-data-collect-optin-container">
		<p><?php _e('<strong>Please help us make Event Espresso better</strong>! With this version of Event Espresso, a feature called User Experience Improvement Program (UEIP) has been implemented to automatically send information to us about how you use our products and about some of the problems you encounter. We use this information to improve our products and features that you use most often and to help solve problems. Participation in the program is enabled by default, and the end results are software improvements to better meet the needs of our customers. Your data will never be sold, traded or misused. Please see our privacy policy for more information. You can opt out of this program by changing the setting on the Event Espresso general options page.', 'event_espresso'); ?></p>
		<div id="data-collect-optin-options-container">
			<span style="display: none" id="data-optin-nonce"><?php echo wp_create_nonce('ee-data-optin'); ?></span>
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


function ee_load_jquery_autocomplete_scripts(){
	wp_enqueue_script('jquery-ui-core');
	wp_register_script('jquery-ui-autocomplete', plugins_url( 'js/jquery.ui.autocomplete.min.js', __FILE__ ), array( 'jquery-ui-widget', 'jquery-ui-position' ), '1.8.2', TRUE );
	wp_enqueue_script('jquery-ui-autocomplete');
	wp_enqueue_script('jquery-ui-datepicker');

}