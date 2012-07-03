<?php
//Custom includes support
if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "custom_includes.php")){
	require_once(EVENT_ESPRESSO_UPLOAD_DIR . "custom_includes.php");
}

//Custom functions support
if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "custom_functions.php")){
	require_once(EVENT_ESPRESSO_UPLOAD_DIR . "custom_functions.php");
}

//Custom shortcode support
if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "custom_shortcodes.php")){
	require_once(EVENT_ESPRESSO_UPLOAD_DIR . "custom_shortcodes.php");
}

/*//Groupon Addon Include & db table setup if installed
if (file_exists(EVENT_ESPRESSO_UPLOAD_DIR . "groupons/groupon_files.php")){
	if (get_option('events_groupons_active') == 'true'){
		$event_espresso_groupon_dir = EVENT_ESPRESSO_UPLOAD_DIR."groupons/";
		define("EVENT_ESPRESSO_GROUPON_DIR", $event_espresso_groupon_dir);
		require_once(EVENT_ESPRESSO_GROUPON_DIR . "groupon_files.php"); //Load Groupon functions
		register_activation_hook(__FILE__,'event_espresso_groupon_install');//Install groupon tables
		define("EVENTS_GROUPON_CODES_TABLE", get_option('events_groupon_codes_tbl')); //Define Groupon db table shortname
	}
}*/

/*//Member Module Addon Include if installed
if (is_plugin_active('member-events/member-events.php')){
		//Define the plugin directory and path
		define("EVNT_MBR_PLUGINPATH", "/" . plugin_basename( dirname(__FILE__) ) . "/");
		define("EVNT_MBR_PLUGINFULLPATH", WP_PLUGIN_DIR . EVENT_ESPRESSO_PLUGINPATH  );
		$event_espresso_member_dir = EVENT_ESPRESSO_UPLOAD_DIR."members/";
		define("EVENT_ESPRESSO_MEMBERS_DIR", $event_espresso_member_dir);
		//require_once(EVENT_ESPRESSO_MEMBERS_DIR . "member_files.php"); //Load Members functions
		//register_activation_hook(__FILE__,'event_espresso_members_install');//Install members tables
		define("EVENTS_MEMBER_REL_TABLE", get_option('events_member_rel_tbl')); //Define members db table shortname
}*/