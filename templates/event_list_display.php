<?php
//This is the event list template page.
//This is a template file for displaying an event lsit on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
/*
 * use the following shortcodes in a page or post:
 * [EVENT_LIST]
 * [EVENT_LIST limit=1]
 * [EVENT_LIST css_class=my-custom-class]
 * [EVENT_LIST show_expired=true]
 * [EVENT_LIST show_deleted=true]
 * [EVENT_LIST show_secondary=false]
 * [EVENT_LIST show_recurrence=true]
 * [EVENT_LIST category_identifier=your_category_identifier]
 *
 * Example:
 * [EVENT_LIST limit=5 show_recurrence=true category_identifier=your_category_identifier]
 *
 */

//Print out the array of event status options
//print_r (event_espresso_get_is_active($event_id));
//Here we can create messages based on the event status. These variables can be echoed anywhere on the page to display your status message.
$status = event_espresso_get_is_active(0,$event_meta);
$status_display = ' - ' . $status['display_custom'];
$status_display_ongoing = $status['status'] == 'ONGOING' ? ' - ' . $status['display_custom'] : '';
$status_display_deleted = $status['status'] == 'DELETED' ? ' - ' . $status['display_custom'] : '';
$status_display_secondary = $status['status'] == 'SECONDARY' ? ' - ' . $status['display_custom'] : ''; //Waitlist event
$status_display_draft = $status['status'] == 'DRAFT' ? ' - ' . $status['display_custom'] : '';
$status_display_pending = $status['status'] == 'PENDING' ? ' - ' . $status['display_custom'] : '';
$status_display_denied = $status['status'] == 'DENIED' ? ' - ' . $status['display_custom'] : '';
$status_display_expired = $status['status'] == 'EXPIRED' ? ' - ' . $status['display_custom'] : '';
$status_display_reg_closed = $status['status'] == 'REGISTRATION_CLOSED' ? ' - ' . $status['display_custom'] : '';
$status_display_not_open = $status['status'] == 'REGISTRATION_NOT_OPEN' ? ' - ' . $status['display_custom'] : '';
$status_display_open = $status['status'] == 'REGISTRATION_OPEN' ? ' - ' . $status['display_custom'] : '';

//You can also display a custom message. For example, this is a custom registration not open message:
$status_display_custom_closed = $status['status'] == 'REGISTRATION_CLOSED' ? ' - <span class="'.espresso_template_css_class('espresso_closed','espresso_closed', false).'">' . __('Registration is closed', 'event_espresso') . '</span>' : '';
global $this_event_id;
$this_event_id = $event_id;
?>
<div id="event_data-<?php echo $event_id ?>" class="<?php espresso_template_css_class('event_data_display','event_data '.$css_class.' '.$category_identifier.' event-data-display event-list-display event-display-boxes ui-widget'); ?>">
	<h3 id="event_title-<?php echo $event_id ?>" class="<?php espresso_template_css_class('event_title','event_title ui-widget-header ui-corner-top'); ?>"><a title="<?php echo stripslashes_deep($event_name) ?>" class="<?php espresso_template_css_class('a_event_title','a_event_title'); ?>" id="a_event_title-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>"><?php echo stripslashes_deep($event_name) ?></a>
		<?php /* These are custom messages that can be displayed based on the event status. Just un-comment the one you want to use. */ ?>
		<?php //echo $status_display; //Turn this on to display the overall status of the event.  ?>
		<?php //echo $status_display_ongoing; //Turn this on to display the ongoing message. ?>
		<?php //echo $status_display_deleted; //Turn this on to display the deleted message. ?>
		<?php //echo $status_display_secondary; //Turn this on to display the waitlist message. ?>
		<?php //echo $status_display_reg_closed; //Turn this on to display the registration closed message. ?>
		<?php //echo $status_display_not_open; //Turn this on to display the secondary message. ?>
		<?php //echo $status_display_open; //Turn this on to display the not open message. ?>
		<?php //echo $status_display_custom_closed; //Turn this on to display the closed message. ?>
	</h3>
<div class="<?php espresso_template_css_class('event_data_display','event-data-display ui-widget-content ui-corner-bottom'); ?>">
	<?php /* Venue details. Un-comment to display. */ ?>
	<?php //echo $venue_title != ''?'<p id="event_venue_name-'.$event_id.'" class="event_venue_name">'.stripslashes_deep($venue_title).'</p>':'' ?>
	<?php //echo $venue_address != ''?'<p id="event_venue_address-'.$event_id.'" class="event_venue_address">'.stripslashes_deep($venue_address).'</p>':''?>
	<?php //echo $venue_address2 != ''?'<p id="event_venue_address2-'.$event_id.'" class="event_venue_address2">'.stripslashes_deep($venue_address2).'</p>':''?>
	<?php //echo $venue_city != ''?'<p id="event_venue_city-'.$event_id.'" class="event_venue_city">'.stripslashes_deep($venue_city).'</p>':''?>
	<?php //echo $venue_state != ''?'<p id="event_venue_state-'.$event_id.'" class="event_venue_state">'.stripslashes_deep($venue_state).'</p>':''?>
	<?php //echo $venue_zip != ''?'<p id="event_venue_zip-'.$event_id.'" class="event_venue_zip">'.stripslashes_deep($venue_zip).'</p>':''?>
	<?php //echo $venue_country != ''?'<p id="event_venue_country-'.$event_id.'" class="event_venue_country">'.stripslashes_deep($venue_country).'</p>':''?>
	<?php
	//Show short descriptions
	if (!empty($event_desc) && isset($org_options['display_short_description_in_event_list']) && $org_options['display_short_description_in_event_list'] == 'Y') {
		?>
		<div class="event-desc">
		
			<?php echo espresso_format_content($event_desc); ?>
		</div>
		<?php
	}
	?>
	<div class="<?php espresso_template_css_class('event_meta','event-meta clearfix'); ?>">
		<?php 
			if ( function_exists('espresso_above_member_threshold') && espresso_above_member_threshold() == true ) {
				$event->member_price = empty($event->member_price) ? '' : $event->member_price;
				$event_cost = $event->member_price;
			} else {
				$event->event_cost = empty($event->event_cost) ? '' : $event->event_cost;
				$event_cost = $event->event_cost;
			}
			//Featured image
			echo apply_filters('filter_hook_espresso_display_featured_image', $event_id, !empty($event_meta['event_thumbnail_url']) ? $event_meta['event_thumbnail_url'] : '');
 			echo do_action('action_hook_espresso_price_display', $event_id, $event_cost, isset($org_options['price_display_in_event_list']) ? $org_options['price_display_in_event_list'] : 'default' );
		?>
		<p id="event_date-<?php echo $event_id ?>"><span class="<?php espresso_template_css_class('section_title','section-title'); ?>"><?php _e('Date:', 'event_espresso'); ?></span>  <?php echo event_date_display($start_date, get_option('date_format')) ?> 
			<?php //Add to calendar button
			echo apply_filters('filter_hook_espresso_display_ical', $all_meta);?>
		</p>
	</div>

	<?php if ( (isset($location) && $location != '' ) && (isset($org_options['display_address_in_event_list']) && $org_options['display_address_in_event_list'] == 'Y') ) { ?>
		<p class="<?php espresso_template_css_class('event_address','event_address'); ?>" id="event_address-<?php echo $event_id ?>"><span class="<?php espresso_template_css_class('section_title','section-title'); ?>"><?php echo __('Address:', 'event_espresso'); ?></span> <br />
			
			<span class="<?php espresso_template_css_class('address_block','address-block'); ?>">
			<?php echo stripslashes_deep($venue_title); ?><br />
			<?php echo stripslashes_deep($location); ?>
				<span class="<?php espresso_template_css_class('google_map_link','google-map-link'); ?>"><?php echo $google_map_link; ?></span></span>
		</p>
		<?php
	}

	$num_attendees = apply_filters('filter_hook_espresso_get_num_attendees', $event_id); 
	if ($num_attendees >= $reg_limit) {
		?>
		<p id="available_spaces-<?php echo $event_id ?>" class="<?php espresso_template_css_class('available_spaces','available-spaces'); ?>"><span class="<?php espresso_template_css_class('section_title','section-title'); ?>"><?php _e('Available Spaces:', 'event_espresso') ?> </span><?php echo apply_filters('filter_hook_espresso_available_spaces_text', $event_id) ?></p>
		<?php if ($overflow_event_id != '0' && $allow_overflow == 'Y') { ?>
			<p id="register_link-<?php echo $overflow_event_id ?>" class="<?php espresso_template_css_class('register_link-footer','register-link-footer'); ?>"><a class="<?php espresso_template_css_class('a_register_link','a_register_link ui-button ui-button-big ui-priority-primary ui-state-default ui-state-hover ui-state-focus ui-corner-all'); ?>" id="a_register_link-<?php echo $overflow_event_id ?>" href="<?php echo espresso_reg_url($overflow_event_id); ?>" title="<?php echo stripslashes_deep($event_name) ?>"><?php _e('Join Waiting List', 'event_espresso'); ?></a></p>
			<?php
		}
	} else {
		if ($display_reg_form == 'Y' && $externalURL == '') {
			?> <p id="available_spaces-<?php echo $event_id ?>" class="<?php espresso_template_css_class('spaces_available','spaces-available'); ?>"><span class="<?php espresso_template_css_class('section_title','section-title'); ?>"><?php _e('Available Spaces:', 'event_espresso') ?></span> <?php echo apply_filters('filter_hook_espresso_available_spaces_text', $event_id) ?></p>
			<?php
		}

		/**
		 * Load the multi event link.
		 * */
		//Un-comment these next lines to check if the event is active
		//echo event_espresso_get_status($event_id);
		//print_r( event_espresso_get_is_active($event_id));

		if ($multi_reg && event_espresso_get_status($event_id) == 'ACTIVE'/* && $display_reg_form == 'Y'*/) { 
		// Uncomment && $display_reg_form == 'Y' in the line above to hide the add to cart link/button form the event list when the registration form is turned off.

			$params = array(
				//REQUIRED, the id of the event that needs to be added to the cart
				'event_id' => $event_id,
				//REQUIRED, Anchor of the link, can use text or image
				'anchor' => __("Add to Cart", 'event_espresso'), //'anchor' => '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/cart_add.png" />',
				//REQUIRED, if not available at this point, use the next line before this array declaration
				// $event_name = get_event_field('event_name', EVENTS_DETAIL_TABLE, ' WHERE id = ' . $event_id);
				'event_name' => $event_name,
				//OPTIONAL, will place this term before the link
				'separator' => __(" or ", 'event_espresso')
			);

			$cart_link = event_espresso_cart_link($params);
		}else{
			$cart_link = false;
		}
		if ($display_reg_form == 'Y') {
			//Check to see if the Members plugin is installed.
			$member_options = get_option('events_member_settings');
			if ( function_exists('espresso_members_installed') && espresso_members_installed() == true && !is_user_logged_in() && ($member_only == 'Y' || $member_options['member_only_all'] == 'Y') ) {
				echo '<p class="'.espresso_template_css_class('ee_member_only','ee_member_only', false).'">'.__('Member Only Event', 'event_espresso').'</p>';
			}else{
			?>
				<p id="register_link-<?php echo $event_id ?>" class="<?php espresso_template_css_class('register_link_footer','register-link-footer'); ?>">
					<a class="<?php espresso_template_css_class('a_register_link','a_register_link ui-button ui-button-big ui-priority-primary ui-state-default ui-state-hover ui-state-focus ui-corner-all'); ?>" id="a_register_link-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>" title="<?php echo stripslashes_deep($event_name) ?>"><?php _e('Register', 'event_espresso'); ?></a>
					<?php echo isset($cart_link) && $externalURL == '' ? $cart_link : ''; ?>
				</p>
	<?php 
			}
		} else { 
	?>
			<p id="register_link-<?php echo $event_id ?>" class="<?php espresso_template_css_class('register_link_footer','register-link-footer'); ?>">
				<a class="<?php espresso_template_css_class('a_register_link','a_register_link ui-button ui-button-big ui-priority-primary ui-state-default ui-state-hover ui-state-focus ui-corner-all'); ?>" id="a_register_link-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>" title="<?php echo stripslashes_deep($event_name) ?>"><?php _e('View Details', 'event_espresso'); ?></a> <?php echo isset($cart_link) && $externalURL == '' ? $cart_link : ''; ?>
			</p>
			
		<?php
		}
	}
	?>
	
</div><!-- / .event-data-display -->
</div><!-- / .event-display-boxes -->
