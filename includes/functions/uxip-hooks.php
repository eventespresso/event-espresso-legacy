<?php
if (!defined('EVENT_ESPRESSO_VERSION') )
	exit('NO direct script access allowed');
/**
 * This file just contains all the uxip tracking stuff that are hooked into various addons.
 */

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
		$table = $wpdb->prefix . EVENT_ESPRESSO_RECURRENCE_TABLE;
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
		$table = $wpdb->prefix . EVENTS_SEATING_CHART_EVENT_TABLE;
		$query = "SELECT COUNT('event_id') FROM $table";
		$count = $wpdb->get_var($query);
		if ( $count > 0 )
			update_option('uxip_ee_seating_chart_active', $count);
		set_transient( 'ee_seating_chart_check' );
	}
}
add_action('admin_init', 'espresso_uxip_seating_chart_active');