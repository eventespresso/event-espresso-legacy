<?php
global $wpdb;

	if (isset($_REQUEST['id'])) {
    $id=$_REQUEST['id'];
    $event_id=$_REQUEST['id'];
	} else {
		if (isset($last_event_id)) {
      $id=$last_event_id;
      $event_id=$last_event_id;
		}
	}

  //The following variables are used to get information about your organization
  $org_options = get_option('events_organization_settings');
  $event_page_id =$org_options['event_page_id'];
  $Organization =stripslashes_deep($org_options['organization']);
  $Organization_street1 =$org_options['organization_street1'];
  $Organization_street2=$org_options['organization_street2'];
  $Organization_city =$org_options['organization_city'];
  $Organization_state=$org_options['organization_state'];
  $Organization_zip =$org_options['organization_zip'];
  $contact =$org_options['contact_email'];
  $registrar = $org_options['contact_email'];


        // use espresso_get_event to pull the event details.
        $this_event = espresso_get_event($event_id);
        
        $num_rows = $wpdb->num_rows;

        //Build the registration page
        if ($num_rows > 0) {
            do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
            //These are the variables that can be used throughout the registration page
            //foreach ($events as $event) {
            global $this_event_id;
            $event_id = $this_event->id;
            $this_event_id = $event_id;

            $event_name = stripslashes_deep($this_event->event_name);
            $event_desc = stripslashes_deep($this_event->event_desc);
            $display_desc = $this_event->display_desc;
            $display_reg_form = $this_event->display_reg_form;
            $event_address = $this_event->address;
            $event_address2 = $this_event->address2;
            $event_city = $this_event->city;
            $event_state = $this_event->state;
            $event_zip = $this_event->zip;
            $event_country = $this_event->country;


            $event_description = stripslashes_deep($this_event->event_desc);
            $event_identifier = $this_event->event_identifier;
            $event_cost = isset($this_event->event_cost) ? $this_event->event_cost : "0.00";
            $member_only = $this_event->member_only;
            $reg_limit = $this_event->reg_limit;
            $allow_multiple = $this_event->allow_multiple;
            $start_date = $this_event->start_date;
            $end_date = $this_event->end_date;
            $allow_overflow = $this_event->allow_overflow;
            $overflow_event_id = $this_event->overflow_event_id;

            //Venue details
            $venue_title = $this_event->venue_title;
            $venue_url = $this_event->venue_url;
            $venue_image = $this_event->venue_image;
            $venue_phone = $this_event->venue_phone;
            $venue_address = '';
            $venue_address2 = '';
            $venue_city = '';
            $venue_state = '';
            $venue_zip = '';
            $venue_country = '';

            global $event_meta;
            $event_meta = unserialize($this_event->event_meta);

            //Venue information
            if ($org_options['use_venue_manager'] == 'Y') {
                $event_address = $this_event->venue_address;
                $event_address2 = $this_event->venue_address2;
                $event_city = $this_event->venue_city;
                $event_state = $this_event->venue_state;
                $event_zip = $this_event->venue_zip;
                $event_country = $this_event->venue_country;

                //Leaving these variables intact, just in case people wnat to use them
                $venue_title = $this_event->venue_name;
                $venue_address = $this_event->venue_address;
                $venue_address2 = $this_event->venue_address2;
                $venue_city = $this_event->venue_city;
                $venue_state = $this_event->venue_state;
                $venue_zip = $this_event->venue_zip;
                $venue_country = $this_event->venue_country;
                global $venue_meta;
                $add_venue_meta = array(
                    'venue_title' => $this_event->venue_name,
                    'venue_address' => $this_event->venue_address,
                    'venue_address2' => $this_event->venue_address2,
                    'venue_city' => $this_event->venue_city,
                    'venue_state' => $this_event->venue_state,
                    'venue_country' => $this_event->venue_country,
                );
                $venue_meta = (isset($this_event->venue_meta) && $this_event->venue_meta != '') && (isset($add_venue_meta) && $add_venue_meta != '') ? array_merge(unserialize($this_event->venue_meta), $add_venue_meta) : '';
                //print_r($venue_meta);
            }

            $virtual_url = stripslashes_deep($this_event->virtual_url);
            $virtual_phone = stripslashes_deep($this_event->virtual_phone);

            //Address formatting
            $location = ($event_address != '' ? $event_address : '') . ($event_address2 != '' ? '<br />' . $event_address2 : '') . ($event_city != '' ? '<br />' . $event_city : '') . ($event_state != '' ? ', ' . $event_state : '') . ($event_zip != '' ? '<br />' . $event_zip : '') . ($event_country != '' ? '<br />' . $event_country : '');

            //Google map link creation
            $google_map_link = espresso_google_map_link(array('address' => $event_address, 'city' => $event_city, 'state' => $event_state, 'zip' => $event_zip, 'country' => $event_country, 'text' => 'Map and Directions', 'type' => 'text'));

            $question_groups = unserialize($this_event->question_groups);
            $reg_start_date = $this_event->registration_start;
            $reg_end_date = $this_event->registration_end;
            $today = date("Y-m-d");
            if (isset($this_event->timezone_string) && $this_event->timezone_string != '') {
                $timezone_string = $this_event->timezone_string;
            } else {
                $timezone_string = get_option('timezone_string');
                if (!isset($timezone_string) || $timezone_string == '') {
                    $timezone_string = 'America/New_York';
                }
            }

            $t = time();
            $today = date_at_timezone("Y-m-d H:i A", $timezone_string, $t);
            //echo event_date_display($today, get_option('date_format'). ' ' .get_option('time_format')) . ' ' . $timezone_string;
            //echo espresso_ddtimezone_simple();
            $reg_limit = $this_event->reg_limit;
            $additional_limit = $this_event->additional_limit;



            //If the coupon code system is intalled then use it
            if (function_exists('event_espresso_coupon_registration_page')) {
                $use_coupon_code = $this_event->use_coupon_code;
            }

            //If the groupon code addon is installed, then use it
            if (function_exists('event_espresso_groupon_payment_page')) {
                $use_groupon_code = $this_event->use_groupon_code;
            }

            //Set a default value for additional limit
            if ($additional_limit == '') {
                $additional_limit = '5';
            }

			$num_attendees = apply_filters('filter_hook_espresso_get_num_attendees', $event_id);//Get the number of attendees
			$available_spaces = apply_filters('filter_hook_espresso_available_spaces_text', $event_id);//Gets a count of the available spaces
			$number_available_spaces = apply_filters('filter_hook_espresso_get_num_available_spaces', $event_id);//Gets the number of available spaces
            //echo $number_available_spaces;


            global $all_meta;
            $all_meta = array(
                'event_name' => '<p class="section-title">' . stripslashes_deep($event_name) . '</span>',
                'event_desc' => stripslashes_deep($event_desc),
                'event_address' => $event_address,
                'event_address2' => $event_address2,
                'event_city' => $event_city,
                'event_state' => $event_state,
                'event_zip' => $event_zip,
                'event_country' => $event_country,
                'venue_title' => '<span class="section-title">' . $venue_title . '</span>',
                'venue_address' => $venue_address,
                'venue_address2' => $venue_address2,
                'venue_city' => $venue_city,
                'venue_state' => $venue_state,
                'venue_country' => $venue_country,

				'is_active' => $this_event->is_active,
				'event_status' => $this_event->event_status,
				'start_time' => $this_event->start_time,
				'start_time' => empty($this_event->start_time) ? '' : $this_event->start_time,

				'registration_startT' => $this_event->registration_startT,
				'registration_start' => $this_event->registration_start,

				'registration_endT' => $this_event->registration_endT,
				'registration_end' => $this_event->registration_end,
				'event_address' => empty($this_event->event_address) ? '' : $this_event->event_address,

				'start_date' => '<span class="section-title">' . event_espresso_no_format_date($start_date, get_option('date_format')) . '</span>',
                'end_date' => '<span class="section-title">' . event_date_display($end_date, get_option('date_format')) . '</span>',
                //'time' => event_espresso_time_dropdown($event_id, 0),
                'google_map_link' => $google_map_link,
                //'price' => event_espresso_price_dropdown($event_id, 0),
                //'registration' => event_espresso_add_question_groups($question_groups),
                //'additional_attendees' => $allow_multiple == "Y" && $number_available_spaces > 1 ? event_espresso_additional_attendees($event_id, $additional_limit, $number_available_spaces, '', false, $event_meta) : '<input type="hidden" name="num_people" id="num_people-' . $event_id . '" value="1">',
            );
            $registration_url = $externalURL != '' ? $externalURL : espresso_reg_url($event_id);
            //print_r($all_meta);
            
            //This function gets the status of the event.
            $is_active = array();
            $is_active = event_espresso_get_is_active(0, $all_meta); }

/**
 * this is the original database stuff
 */
/*
	$sql  = "SELECT * FROM " .EVENTS_DETAIL_TABLE. " WHERE event_status != 'D' AND id = " . $event_id;

	if ($wpdb->get_results($sql)){
			$events = $wpdb->get_results($sql);
			foreach ($events as $event){ //These are the variables that can be used throughout the regsitration page
					$event_id = $event->id;
					$event_name = stripslashes_deep($event->event_name);
					$event_desc = stripslashes_deep($event->event_desc);
					$display_desc = $event->display_desc;
					$event_address = $event->address;
					$event_address2 = $event->address2;
					$event_city = $event->city;
					$event_state = $event->state;
					$event_zip = $event->zip;
					$event_country = $event->country;
					$event_description = stripslashes_deep($event->event_desc);
					$event_identifier = $event->event_identifier;
					$event_cost = empty($event->event_cost) ? 0 : $event->event_cost;
					$member_only = $event->member_only;
					$active = $event->is_active;
					$reg_limit = $event->reg_limit;
					$allow_multiple = $event->allow_multiple;
					$start_date =  $event->start_date;
					$end_date =  $event->end_date;
					$reg_limit=$event->reg_limit;
					$additional_limit = $event->additional_limit;

					$regurl=espresso_reg_url($event_id);

					$google_map_link = espresso_google_map_link(array('address' => $event_address, 'city' => $event_city, 'state' => $event_state, 'zip' => $event_zip, 'country' => $event_country, 'text' => 'Map and Directions', 'type' => 'text'));
			}//End foreach ($events as $event)
	} */
?>
<p><?php echo event_date_display($start_date, get_option('date_format')) . " - " . event_date_display($end_date, get_option('date_format')); ?></p>
<p><?php echo $event_address ?></p>
<p><img style="padding-right: 5px;" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>images/map.png" border="0" alt="<?php _e('View Map', 'event_espresso'); ?>" /><?php echo $google_map_link; ?> | <a class="a_register_link" id="a_register_link-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>" title="<?php echo stripslashes_deep($event_name) ?>"><?php _e('Register', 'event_espresso'); ?></a></p>
<?php
if ($display_desc == 'Y'){ ?>
<?php /*?><!--more--><?php */ //Uncomment this part to show the Read More link?>
<?php _e('Description:','event_espresso'); ?>
<?php // if there's a cart link shortcode in the post, replace the shortcode with one that includes the event_id
    if (preg_match("/ESPRESSO_CART_LINK/", $event_desc)) { $event_desc = preg_replace('/ESPRESSO_CART_LINK/', 'ESPRESSO_CART_LINK event_id=' . $event_id, $event_desc); } ?>
<?php echo wpautop($event_desc); ?>
<p><a class="a_register_link" id="a_register_link-<?php echo $event_id ?>" href="<?php echo $registration_url; ?>" title="<?php echo stripslashes_deep($event_name) ?>"><?php _e('Register', 'event_espresso'); ?></a></p>
<?php }//End display description ?>
