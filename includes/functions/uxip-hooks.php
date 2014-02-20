<?php
if (!defined('EVENT_ESPRESSO_VERSION') )
	exit('NO direct script access allowed');
/**
 * This file just contains all the uxip tracking stuff that are hooked into various addons.
 */

if ( !defined( 'MONTH_IN_SECONDS' ) )
	define('MONTH_IN_SECONDS', WEEK_IN_SECONDS * 4);

/**
 * Espresso Calendar tracking
 */
/**
 * Record calendar shortcode being parsed.
 * @param  string $show_expired not used in this implementation
 * @return void
 */
function espresso_uxip_calendar_active($show_expired) {
	//update the option with a time stamp.
	update_option('uxip_ee_calendar_active', time() );
}
add_action('action_hook_espresso_calendar_do_stuff', 'espresso_uxip_calendar_active', 10);



/**
 * Espresso Ticketing addon tracking
 */
/**
 * record if ticketing addon is active.
 * @return void
 */
function espresso_uxip_ticketing_active() {
	//update the option with a timestamp
	update_option('uxip_ee_ticketing_active', time() );
}
add_action('action_hook_espresso_before_espresso_ticket_launch', 'espresso_uxip_ticketing_active');



/**
 * Espresso REM addon tracking
 */
/**
 * record if the recurrence addon is active
 * @return void
 */
function espresso_uxip_rem_active() {
	if ( !defined('EVENT_ESPRESSO_RECURRENCE_TABLE') )
		return; //get out cause rem isn't even active.

	//we don't want this running on EVERY admin page load but at least once every second week.
	if ( false === ( $transient = get_transient('ee_rem_check' ) ) ) {

		global $wpdb;
		$table = EVENT_ESPRESSO_RECURRENCE_TABLE;
		$query = "SELECT COUNT('recurrence_id') FROM $table";
		$count = $wpdb->get_var($query);
		if ( $count > 1 )
			update_option('uxip_ee_rem_active', $count);
		set_transient( 'ee_rem_check', 1, WEEK_IN_SECONDS * 2 );
	}
}
add_action('admin_init', 'espresso_uxip_rem_active');



/**
 * Espresso Seating Chart addon tracking
 */
/**
 * record if the seating chart addon is active (and being used)
 * @return void
 */
function espresso_uxip_seating_chart_active() {
	if ( !defined('ESPRESSO_SEATING_VERSION') )
		return; //get out cause seating chart isn't even active.

	//we don't wan this running on EVERY admin page load but at least once very month.
	if ( false === ( $transient = get_transient('ee_seating_chart_check' ) ) ) {
		global $wpdb;
		$table = EVENTS_SEATING_CHART_EVENT_TABLE;
		$query = "SELECT COUNT('event_id') FROM $table";
		$count = $wpdb->get_var($query);
		if ( $count > 0 )
			update_option('uxip_ee_seating_chart_active', $count);
		set_transient( 'ee_seating_chart_check', 1, MONTH_IN_SECONDS );
	}
}
add_action('admin_init', 'espresso_uxip_seating_chart_active');


/**
 * Espresso Members ADdon tracking (User Integration addon)
 */

function espresso_uxip_members_active() {
	if ( !defined('EVENTS_MEMBER_REL_TABLE') )
		return; //get out cause members isn't even active

	//we only check this once a month.
	if ( false === ( $transient = get_transient( 'ee_members_active_check' ) ) ) {
		global $wpdb;

		//first let's check if there are any member only events and set that.
		$table = EVENTS_MEMBER_REL_TABLE;
		$query = "SELECT COUNT('id') FROM $table";
		$count = $wpdb->get_var($query);
		if ( $count > 0 )
			update_option('uxip_ee_members_events', $count);
		set_transient('ee_members_active_check', 1, MONTH_IN_SECONDS );
	}

}
add_action('admin_init', 'espresso_uxip_members_active' );


/**
 * Track active theme info
 */
function espresso_uxip_track_active_theme() {
	//we only check this once a month.
	if ( false === ( $transient = get_transient( 'ee_active_theme_check' ) ) ) {
		$theme = wp_get_theme();
		update_option('uxip_ee_active_theme', $theme->get('Name') );
		set_transient('ee_active_theme_check', 1, MONTH_IN_SECONDS );
	}
}
add_action('admin_init', 'espresso_uxip_track_active_theme');


/**
 * track events info
 */
function espresso_uxip_track_event_info() {
	//we only check this once every couple weeks.
	if ( false === ( $transient = get_transient( 'ee_event_info_check') ) ) {
		//first let's get the number for ALL events
		global $wpdb;
		$table = EVENTS_DETAIL_TABLE;
		$query = "SELECT COUNT('id') FROM $table";
		$count = $wpdb->get_var($query);
		if ( $count > 0 )
			update_option('uxip_ee_all_events_count', $count);

		//next let's just get the number of ACTIVE events
		$query = $query . " WHERE is_active = 'Y' AND event_status != 'D' AND DATE(start_date) > NOW()";
		$count = $wpdb->get_var($query);
		if ( $count > 0 )
			update_option('uxip_ee_active_events_count', $count);
		set_transient( 'ee_event_info_check', 1, WEEK_IN_SECONDS * 2 );
	} 
}
add_action('admin_init', 'espresso_uxip_track_event_info');