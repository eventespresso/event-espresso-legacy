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
		!empty($show_admin) && $show_admin == TRUE ? '':$sql .= " AND eq.admin_only = 'N' ";
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
global $ee_pue_checkPeriod, $lang_domain;
$ee_pue_checkPeriod = 1;
$lang_domain = 'event_espresso';

add_action('plugins_loaded', 'ee_core_load_pue_update');
function ee_core_load_pue_update() {
	global $org_options, $ee_pue_checkPeriod, $lang_domain, $espresso_check_for_updates;
	
	if ( $espresso_check_for_updates == false )
		return;
		
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file 
		require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
		$api_key = $org_options['site_license_key'];
		$host_server_url = 'http://beta.eventespresso.com';
		$plugin_slug = 'event-espresso';
		$options = array(
			'apikey' => $api_key,
			'lang_domain' => $lang_domain,
			'checkPeriod' => $ee_pue_checkPeriod,
		);
		$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
	}
}