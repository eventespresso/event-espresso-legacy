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
  $currency_format =$org_options['currency_format'];


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

					$google_map_link = espresso_google_map_link(array( 'address'=>$event_address, 'city'=>$event_city, 'state'=>$event_state, 'zip'=>$event_zip, 'country'=>$event_country) );
			}//End foreach ($events as $event)
	}
?>
<p><?php echo date('l F j, Y',strtotime($start_date)) . " - " . date('l F j, Y',strtotime($end_date)); ?></p>
<p><?php echo $event_address ?></p>
<p><img style="padding-right: 5px;" src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL?>/images/map.png" border="0" alt="<?php _e('View Map', 'event_espresso'); ?>" /><?php echo $google_map_link; ?> | <a class="event_espressoter_link" href="<?php echo $regurl; ?>"><?php _e('Register', 'event_espresso'); ?></a></p>
<?php
if ($display_desc == "Y"){ ?>
<?php /*?><!--more--><?php */ //Uncomment this part to show the Read More link?>
<?php _e('Description:','event_espresso'); ?>
<?php echo wpautop($event_desc); ?>
<p><a class="event_espressoter_link" href="<?php echo $regurl; ?>"><?php _e('Register', 'event_espresso'); ?></a></p>
<?php }//End display description ?>
