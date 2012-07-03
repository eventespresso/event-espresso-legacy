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
		extract(shortcode_atts(array('event_category_id' => __('No Category ID Supplied','event_espresso')), $atts));
		$event_category_id = "{$event_category_id}";
		ob_start();
		display_event_espresso_categories($event_category_id);//This function is called from the "/templates/event_list.php" file.
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
* [LISTATTENDEES show_recurrence="false"]
* [LISTATTENDEES event_identifier="your_event_identifier"]
* [LISTATTENDEES category_identifier="your_category_identifier"]
*/
if (!function_exists('event_espresso_attendee_list')) {
	function event_espresso_attendee_list($event_identifier='NULL', $category_identifier='NULL',$show_gravatar='false',$show_expired='false',$show_secondary='false',$show_deleted='false',$show_recurrence='true',$limit='0'){	
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
			event_espresso_show_attendess($sql,$show_gravatar);
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
			event_espresso_show_attendess($sql,$show_gravatar);
		}else{
			$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
			$sql .= " WHERE e.is_active='Y' ";
			$sql .= $show_secondary;
			$sql .= $show_expired;
			$sql .= $show_deleted;
			$sql .= $show_recurrence;
			$sql .= $limit;
			event_espresso_show_attendess($sql,$show_gravatar);
		}
	}
}

if (!function_exists('event_espresso_list_attendees')) {
	function event_espresso_list_attendees($atts) {
		//echo $atts;
		extract(shortcode_atts(array('event_identifier' => 'NULL', 'single_event_id' => 'NULL', 'category_identifier' => 'NULL', 'event_category_id' => 'NULL', 'show_gravatar' => 'NULL', 'show_expired' => 'NULL','show_secondary'=>'NULL','show_deleted'=>'NULL','show_recurrence'=>'NULL', 'limit' => 'NULL'),$atts));
		
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
		
		ob_start();
		event_espresso_attendee_list($event_identifier, $category_identifier, $show_gravatar, $show_expired, $show_secondary, $show_deleted, $show_recurrence, $limit);
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
		extract(shortcode_atts(array('event_id' =>'0'), $atts));
		$event_id = "{$event_id}";
		return register_attendees(NULL, $event_id);
	}
}
add_shortcode('ESPRESSO_REG_PAGE', 'espresso_reg_page_sc');

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
		global $wpdb, $org_options;
		extract(shortcode_atts(array('event_id' =>'0','number' =>'0'), $atts));
		$event_id = "{$event_id}";
		$number = "{$number}";
		
		$number = $number == 0? '0,1': $number. ','. $number;
		
		$results = $wpdb->get_results("SELECT id, event_cost, surcharge FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='".$event_id."' ORDER BY id ASC LIMIT ". $number);
		foreach ($results as $result){
			if ($wpdb->num_rows >= 1) {
				if ($result->event_cost > 0.00){
					$event_cost = $result->surcharge > 0.00 && $result->event_cost > 0.00 ? $result->event_cost + number_format($result->event_cost * $result->surcharge / 100, 2, '.', '') : $result->event_cost;
					
					// Addition for Early Registration discount
					if (early_discount_amount($event_id, $event_cost) != false){
						$early_price_data = array();
						$early_price_data = early_discount_amount($event_id, $event_cost);
						$event_cost = $early_price_data['event_price'];
					}
				}else{
					$event_cost = __('0.00','event_espresso');
				}
			}else if ($wpdb->num_rows == 0){
				$event_cost = __('0.00','event_espresso');
			}
		}

		return $event_cost;
	}
}
add_shortcode('EVENT_PRICE', 'get_espresso_price_sc');


/*
* 
* Price Dropdown
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
		
		extract(shortcode_atts(array('category_identifier' => 'NULL','show_expired' => 'false', 'show_secondary'=>'false','show_deleted'=>'false','show_recurrence'=>'false', 'limit' => '0', 'order_by' => 'NULL'),$atts));		
		
		if ($category_identifier != 'NULL'){
			$type = 'category';
		}
		
		$show_expired = $show_expired == 'false' ? " AND e.start_date >= '".date ( 'Y-m-d' )."' " : '';
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
		event_espresso_get_event_details($sql);
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