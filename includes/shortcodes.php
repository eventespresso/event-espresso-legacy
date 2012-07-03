<?php
//These are the core shortcodes used by the plugin. 
//If you would like to add your own shortcodes, please puchasse the custom shortcodes addon from http://eventespresso.com/download/plugins-and-addons/custom-files-addon/
//For a list and description of available shortcodes, please refer to http://eventespresso.com/forums/2010/10/post-type-variables-and-shortcodes/

/*
* 
* Single Event
* Displays a single event
*
*/
//[SINGLEEVENT single_event_id="your_event_identifier"]
if (!function_exists('show_single_event')) {
	function show_single_event($atts) {
		extract(shortcode_atts(array('single_event_id' => __('No ID Supplied','event_espresso')), $atts));
		$single_event_id = "{$single_event_id}";
		global $load_espresso_scripts;	
		$load_espresso_scripts = true;//This tells the plugin to load the required scripts				
		//echo $single_event_id;
		ob_start();
		register_attendees($single_event_id);
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
add_shortcode('SINGLEEVENT', 'show_single_event');

/*
* 
* Event Category
* Displays a list of events by category
* [EVENT_ESPRESSO_CATEGORY event_category_id="your_category_identifier"]
*
*/

if (!function_exists('show_event_category')) {
	function show_event_category($atts) {
		extract(shortcode_atts(array('event_category_id' => __('No Category ID Supplied','event_espresso'),'css_class' => ''), $atts));
		$event_category_id = "{$event_category_id}";
		$css_class = "{$css_class}";
		global $load_espresso_scripts;	
		$load_espresso_scripts = true;//This tells the plugin to load the required scripts
		ob_start();
		display_event_espresso_categories($event_category_id, $css_class);//This function is called from the "/templates/event_list.php" file.
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
add_shortcode('EVENT_ESPRESSO_CATEGORY', 'show_event_category');

/*
* 
* List of Attendees
* Displays a lsit of attendees
* [LISTATTENDEES]
* [LISTATTENDEES limit="30"]
* [LISTATTENDEES show_expired="false"]
* [LISTATTENDEES show_deleted="false"]
* [LISTATTENDEES show_secondary="false"]
* [LISTATTENDEES show_gravatar="true"]
//[LISTATTENDEES paid_only="true"]
* [LISTATTENDEES show_recurrence="false"]
* [LISTATTENDEES event_identifier="your_event_identifier"]
* [LISTATTENDEES category_identifier="your_category_identifier"]
*/
if (!function_exists('event_espresso_attendee_list')) {
	function event_espresso_attendee_list($event_identifier='NULL', $category_identifier='NULL',$show_gravatar='false',$show_expired='false',$show_secondary='false',$show_deleted='false',$show_recurrence='true',$limit='0', $paid_only='false'){	
		$show_expired = $show_expired == 'false' ? " AND e.start_date >= '".date ( 'Y-m-d' )."' " : '';
		$show_secondary = $show_secondary == 'false' ? " AND e.event_status != 'S' " : '';
		$show_deleted = $show_deleted == 'false' ? " AND e.event_status != 'D' " : '';
		$show_recurrence = $show_recurrence == 'false' ? " AND e.recurrence_id = '0' " : '';
		$limit = $limit > 0 ? " LIMIT 0," . $limit . " " : '';
		if ($event_identifier != 'NULL'){
			$type = 'event';

		}else if ($category_identifier != 'NULL'){
			$type = 'category';
		}
		
		if ($type == 'event'){
			$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
			$sql .= " WHERE e.is_active = 'Y' ";
			$sql .= " AND e.event_identifier = '" . $event_identifier . "' ";
			$sql .= $show_secondary;
			$sql .= $show_expired;
			$sql .= $show_deleted;
			$sql .= $show_recurrence;
			$sql .= $limit;
			event_espresso_show_attendess($sql,$show_gravatar,$paid_only);
		}else if ($type == 'category'){
			$sql = "SELECT e.* FROM " . EVENTS_CATEGORY_TABLE . " c ";
			$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.cat_id = c.id ";
			$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " e ON e.id = r.event_id ";
			$sql .= " WHERE c.category_identifier = '" . $category_identifier . "' ";
			$sql .= " AND e.is_active = 'Y' ";
			$sql .= $show_secondary;
			$sql .= $show_expired;
			$sql .= $show_deleted;
			$sql .= $show_recurrence;
			$sql .= $limit;
			event_espresso_show_attendess($sql,$show_gravatar,$paid_only);
		}else{
			$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
			$sql .= " WHERE e.is_active='Y' ";
			$sql .= $show_secondary;
			$sql .= $show_expired;
			$sql .= $show_deleted;
			$sql .= $show_recurrence;
			$sql .= $limit;
			event_espresso_show_attendess($sql,$show_gravatar,$paid_only);
		}
	}
}

if (!function_exists('event_espresso_list_attendees')) {
	function event_espresso_list_attendees($atts) {
		//echo $atts;
		extract(shortcode_atts(array('event_identifier' => 'NULL', 'single_event_id' => 'NULL', 'category_identifier' => 'NULL', 'event_category_id' => 'NULL', 'show_gravatar' => 'NULL', 'show_expired' => 'NULL','show_secondary'=>'NULL','show_deleted'=>'NULL','show_recurrence'=>'NULL', 'limit' => 'NULL', 'paid_only'=>'NULL'),$atts));
		global $load_espresso_scripts;	
		$load_espresso_scripts = true;//This tells the plugin to load the required scripts
		//get the event identifiers
		$event_identifier = "{$event_identifier}";
		$single_event_id = "{$single_event_id}";
		$event_identifier = ($single_event_id != 'NULL') ? $single_event_id : $event_identifier;
		$show_gravatar = "{$show_gravatar}";
		
		//get the category identifiers
		$category_identifier = "{$category_identifier}";
		$event_category_id = "{$event_category_id}";
		$category_identifier = ($event_category_id != 'NULL') ? $event_category_id : $category_identifier;
		
		//Get the extra parameters
		$show_expired="{$show_expired}";
		$show_secondary="{$show_secondary}";
		$show_deleted="{$show_deleted}";
		$show_recurrence="{$show_recurrence}";
		$paid_only="{$paid_only}";
		
		ob_start();
		event_espresso_attendee_list($event_identifier, $category_identifier, $show_gravatar, $show_expired, $show_secondary, $show_deleted, $show_recurrence, $limit, $paid_only);
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
add_shortcode('LISTATTENDEES', 'event_espresso_list_attendees');

/*
* 
* Event Times
* Returs the times for an event. Sucha s start and end times, registration start and end times, etc.
* Please refer to http://php.net/manual/en/function.date.php for date formats
*
*/
if (!function_exists('espresso_event_time_sc')) {
	function espresso_event_time_sc($atts){
		extract(shortcode_atts(array('event_id' =>'0','type' =>'','format' =>''), $atts));
		$event_id = "{$event_id}";
		$type = "{$type}";
		$format = "{$format}";
		return espresso_event_time($event_id, $type, $format);
	}
}
add_shortcode('EVENT_TIME', 'espresso_event_time_sc');

/*
* 
* Registration Page
* Returns the registration page for an event
*
*/
if (!function_exists('espresso_reg_page_sc')) {
	function espresso_reg_page_sc($atts){
		global $wpdb, $org_options;
		global $load_espresso_scripts;	
		$load_espresso_scripts = true;//This tells the plugin to load the required scripts
		extract(shortcode_atts(array('event_id' =>'0'), $atts));
		$event_id = "{$event_id}";
		return register_attendees(NULL, $event_id);
	}
}
add_shortcode('ESPRESSO_REG_PAGE', 'espresso_reg_page_sc');

if (!function_exists('espresso_reg_form_sc')) {
	function espresso_reg_form_sc($atts){
		global $wpdb, $org_options;
		global $load_espresso_scripts;	
		$load_espresso_scripts = true;//This tells the plugin to load the required scripts
		extract(shortcode_atts(array('event_id' =>'0'), $atts));
		$event_id = "{$event_id}";
		
		//The following variables are used to get information about your organization
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
		
		$message =$org_options['message'];
		$use_captcha =$org_options['use_captcha'];
		$paypal_id =$org_options['paypal_id'];
		
		$sql  = "SELECT * FROM " . EVENTS_DETAIL_TABLE;
		$sql.= " WHERE is_active='Y' ";
		$sql.= " AND event_status != 'D' ";
		$sql.= " AND id = '" . $event_id . "' LIMIT 0,1";
		
		if ($wpdb->get_results($sql)){
			$events = $wpdb->get_results($sql);
			//These are the variables that can be used throughout the regsitration page
			foreach ($events as $event){ 
					$event_id = $event->id;
					$event_name = stripslashes_deep($event->event_name);
					$event_desc = stripslashes_deep($event->event_desc);
					$display_desc = $event->display_desc;
					$display_reg_form = $event->display_reg_form;
					$event_address = $event->address;
					$event_address2 = $event->address2;
					$event_city = $event->city;
					$event_state = $event->state;
					$event_zip = $event->zip;
					$event_country = $event->country;
					$event_description = stripslashes_deep($event->event_desc);
					$event_identifier = $event->event_identifier;
					$event_cost = $event->event_cost;
					$member_only = $event->member_only;
					$reg_limit = $event->reg_limit;
					$allow_multiple = $event->allow_multiple;
					$start_date =  $event->start_date;
					$end_date =  $event->end_date;
					$allow_overflow = $event->allow_overflow;
					$overflow_event_id = $event->overflow_event_id;
					
					$virtual_url=stripslashes_deep($event->virtual_url);
					$virtual_phone=stripslashes_deep($event->virtual_phone);
			
					//Address formatting
					$location = ($event_address != '' ? $event_address :'') . ($event_address2 != '' ? '<br />' . $event_address2 :'') . ($event_city != '' ? '<br />' . $event_city :'') . ($event_state != '' ? ', ' . $event_state :'') . ($event_zip != '' ? '<br />' . $event_zip :'') . ($event_country != '' ? '<br />' . $event_country :'');
					
					//Google map link creation
					$google_map_link = espresso_google_map_link(array( 'address'=>$event_address, 'city'=>$event_city, 'state'=>$event_state, 'zip'=>$event_zip, 'country'=>$event_country, 'text'=> 'Map and Directions', 'type'=> 'text') );

					$reg_start_date = $event->registration_start;
					$reg_end_date = $event->registration_end;
					$today = date("Y-m-d");
					
					$reg_limit=$event->reg_limit;
					$additional_limit = $event->additional_limit;
					
					
					$question_groups = unserialize($event->question_groups);
					$item_groups = unserialize($event->item_groups);

					//This function gets the status of the event.
					$is_active = array();
					$is_active = event_espresso_get_is_active($event_id);
					
					//If the coupon code system is intalled then use it
					if (function_exists('event_espresso_coupon_registration_page')) {
						$use_coupon_code = $event->use_coupon_code;
					}
					
					//If the groupon code addon is installed, then use it
					if (function_exists('event_espresso_groupon_payment_page')) {
						$use_groupon_code=$event->use_groupon_code;
					}
					
					//Set a default value for additional limit
					if ($additional_limit == ''){
						$additional_limit = '5';
					}
			}//End foreach ($events as $event)
		}
		
//This is the registration form.
//This is a template file for displaying a registration form for an event on a page.
//There should be a copy of this file in your wp-content/uploads/espresso/ folder.
?>
				<div id="event_espresso_registration_form">
                    <form method="post" action="<?php echo home_url()?>/?page_id=<?php echo $event_page_id?>" id="registration_form">
                    <?php
					//print_r( event_espresso_get_is_active($event_id));
					
                    switch ($is_active['status']){
						case 'EXPIRED': //only show the event description.
							_e('<h3 class="expired_event">This event has passed.</h3>', 'event_espresso');
						break;
						
						case 'REGISTRATION_CLOSED': //only show the event description.
							// if todays date is after $reg_end_date
						?>
							<p class="event_full"><strong><?php _e('We are sorry but registration for this event is now closed.','event_espresso'); ?></strong></p>
							<p class="event_full"><strong><?php _e('Please <a href="contact" title="contact us">contact us</a> if you would like to know if spaces are still available.','event_espresso'); ?></strong></p>
						<?php
						break;
						
						case 'REGISTRATION_NOT_OPEN': //only show the event description.
						// if todays date is after $reg_end_date
						// if todays date is prior to $reg_start_date
						?>
							<p class="event_full"><strong><?php _e('We are sorry but this event is not yet open for registration.','event_espresso'); ?></strong></p>
							<p class="event_full"><strong><?php _e('You will be able to register starting ' . event_espresso_no_format_date($reg_start_date, 'F d, Y'),'event_espresso'); ?></strong></p>
						<?php 
						break;
						
						default:
							//If the event is in an active or ongoing status, then show the registration form.
							//echo $is_active['status'];//Show event status
							if ($display_reg_form == 'Y') {      
							?>  
							<p class="event_time">
								<?php 
								//This block of code is used to display the times of an event in either a dropdown or text format.
								if ($time_selected == true){//If the customer is coming from a page where the time was preselected.
									event_espresso_display_selected_time($time_id);//Optional parameters start, end, default
								}else if ($time_selected == false){
									event_espresso_time_dropdown($event_id);	
								}//End time selected
								?>   
							</p>
								
								<p class="event_prices"><?php echo event_espresso_price_dropdown($event_id);//Show pricing in a dropdown or text ?></p>	        
							<?php
								//Outputs the custom form questions. This function can be overridden using the custom files addon
								echo event_espresso_add_question_groups($question_groups);
								
								
								//Coupons						
								if (function_exists('event_espresso_coupon_registration_page')) {
									echo event_espresso_coupon_registration_page($use_coupon_code, $event_id);
								}//End coupons display
								
								//Groupons
								if (function_exists('event_espresso_groupon_registration_page')) {
									echo event_espresso_groupon_registration_page($use_groupon_code, $event_id);
								}//End groupons display
			
								?>
									  <input type="hidden" name="num_people" id="num_people-<?php echo $event_id;?>" value="1"> 
						
								
								<input type="hidden" name="regevent_action" id="regevent_action-<?php echo $event_id;?>" value="post_attendee">
								<input type="hidden" name="event_id" id="event_id-<?php echo $event_id;?>" value="<?php echo $event_id;?>">
								<?php
								//Recaptcha portion
								if ($use_captcha == 'Y'){
									if (!function_exists('recaptcha_get_html')) {
										require_once(EVENT_ESPRESSO_PLUGINFULLPATH. 'includes/recaptchalib.php');
									}//End require captcha library
									
									# the response from reCAPTCHA
									$resp = null;
									# the error code from reCAPTCHA, if any
									$error = null;
									?>
									<p class="event_form_field" id="captcha-<?php echo $event_id;?>"><?php _e('Anti-Spam Measure: Please enter the following phrase','event_espresso'); ?>
									<?php echo recaptcha_get_html($org_options['recaptcha_publickey'], $error); ?>
									</p>
						<?php 	} //End use captcha ?>
								<p class="event_form_submit" id="event_form_submit-<?php echo $event_id;?>">
                                
								<input class="btn_event_form_submit" id="event_form_field-<?php echo $event_id;?>" type="submit" name="Submit" value="<?php _e('Submit','event_espresso');?>">
								</p>
						<?php
							}
						break;
				    }//End Switch statement to check the status of the event?>
					</form>
                    </div><?php
	}
	}

add_shortcode('ESPRESSO_REG_FORM', 'espresso_reg_form_sc');

/*
* 
* Category Name
* Returns an array of category data based on an event id
*
*/
if (!function_exists('espresso_category_name_sc')) {
	function espresso_category_name_sc($atts){
		global $wpdb, $org_options;
		extract(shortcode_atts(array('event_id' =>'0'), $atts));
		$event_id = "{$event_id}";
		$category_name = espresso_event_category_data($event_id);
		return $category_name['category_name'];
	}
}
add_shortcode('CATEGORY_NAME', 'espresso_category_name_sc');

/*
* 
* Price Dropdown
* Returns a price dropdown if multiple prices are associated with an event, based on an event id
*
*/
if (!function_exists('espresso_price_dd_sc')) {
	function espresso_price_dd_sc($atts){
		global $wpdb, $org_options;
		extract(shortcode_atts(array('event_id' =>'0'), $atts));
		$event_id = "{$event_id}";
		$data = event_espresso_price_dropdown($event_id);
		return $data['category_name'];
	}
}
add_shortcode('EVENT_PRICE_DROPDOWN', 'espresso_price_dd_sc');

/*
* 
* Event Price
* Returns a price for a single event, based on an event id
*
*/
if (!function_exists('get_espresso_price_sc')) {
	function get_espresso_price_sc($atts) {
		extract(shortcode_atts(array('event_id' =>'0','number' =>'0'), $atts));
		$event_id = "{$event_id}";
		$number = "{$number}";

		return espresso_return_single_price($event_id,$number);
	}
}
add_shortcode('EVENT_PRICE', 'get_espresso_price_sc');


/*
* 
* Returns the number of attendees, registration limits, etc based on an event id
*
*/
if (!function_exists('espresso_attendees_data_sc')) {
	function espresso_attendees_data_sc($atts){
		global $wpdb, $org_options;
		extract(shortcode_atts(array('event_id' =>'0','type' =>''), $atts));
		$event_id = "{$event_id}";
		$type = "{$type}";
		$data = get_number_of_attendees_reg_limit($event_id, $type);
		return $data;
	}
}
add_shortcode('ATTENDEE_NUMBERS', 'espresso_attendees_data_sc');

/*
* 
* Event List
* Returns a list of events
* [EVENT_LIST]
* [EVENT_LIST limit=1]
* [EVENT_LIST css_class=my-custom-class]
* [EVENT_LIST show_expired=true]
* [EVENT_LIST show_deleted=true]
* [EVENT_LIST show_secondary=true]
* [EVENT_LIST show_recurrence=true]
* [EVENT_LIST category_identifier=your_category_identifier]
*
*/
if (!function_exists('display_event_list_sc')) {
	function display_event_list_sc($atts){	
		global $wpdb;
		global $load_espresso_scripts;	
		$load_espresso_scripts = true;//This tells the plugin to load the required scripts
		extract(shortcode_atts(array('category_identifier' => 'NULL','show_expired' => 'false', 'show_secondary'=>'false','show_deleted'=>'false','show_recurrence'=>'false', 'limit' => '0', 'order_by' => 'NULL', 'css_class' => 'NULL'),$atts));		
		
		if ($category_identifier != 'NULL'){
			$type = 'category';
		}
		
		$show_expired = $show_expired == 'false' ? " AND (e.start_date >= '".date ( 'Y-m-d' )."' OR e.event_status = 'O' OR e.registration_end >= '".date ( 'Y-m-d' )."') " : '';
		$show_secondary = $show_secondary == 'false' ? " AND e.event_status != 'S' " : '';
		$show_deleted = $show_deleted == 'false' ? " AND e.event_status != 'D' " : '';
		$show_recurrence = $show_recurrence == 'false' ? " AND e.recurrence_id = '0' " : '';
		$limit = $limit > 0 ? " LIMIT 0," . $limit . " " : '';
		$order_by = $order_by != 'NULL'? " ORDER BY ". $order_by ." ASC " : " ORDER BY date(start_date), id ASC ";

		if ($type == 'category'){
			$sql = "SELECT e.* FROM " . EVENTS_CATEGORY_TABLE . " c ";
			$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.cat_id = c.id ";
			$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " e ON e.id = r.event_id ";
			$sql .= " WHERE c.category_identifier = '" . $category_identifier . "' ";
			$sql .= " AND e.is_active = 'Y' ";
		}else{
			$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
			$sql .= " WHERE e.is_active = 'Y' ";
		}
		$sql .= $show_expired;
		$sql .= $show_secondary;
		$sql .= $show_deleted;
		$sql .= $show_recurrence;
		$sql .= $order_by;
		$sql .= $limit;
		//template located in event_list_dsiplay.php
		ob_start();
		//echo $sql;
		event_espresso_get_event_details($sql, $css_class);
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
add_shortcode('EVENT_LIST', 'display_event_list_sc');

//Returns the price
/*function espresso_get_price_sc($atts){
	global $wpdb, $org_options;
	extract(shortcode_atts(array('event_id' =>'0'), $atts));
	$event_id = "{$event_id}";
	return event_espresso_get_price($event_id);
}
add_shortcode('EVENT_PRICE', 'espresso_get_price_sc');*/

function espresso_session_id_sc(){
	return event_espresso_session_id();
}
add_shortcode('SESSION_ID', 'espresso_session_id_sc');
