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