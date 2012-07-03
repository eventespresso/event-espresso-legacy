<?php
//Function to check if an array is empty
function isEmptyArray($array) {
	$my_not_empty = create_function('$v', 'return strlen($v) > 0;');
	return (count(array_filter($array, $my_not_empty)) == 0) ? 1 : 0;
}

//This function pulls HTML entities back into HTML format first then strips it.
//Use it if you want to strip the HTML from the event_desc column in the daatabase.
//I have to store HTML as special chars in the database, because the html was breaking the sql queries.
//I tried doing add_slashes, then strip_slashes, but it kept adding to many slashes and not removing the extras. It was a nightmare so i decided to jsut make all HTML into special chars.
function event_espresso_strip_html_from_entity($html_entity){
	$stripped_html_entity = strip_tags(html_entity_decode($html_entity));
	return $stripped_html_entity;
}

//For testing email functions
function event_espresso_test_email($optional_message = 'None'){
	global $org_options;

	$to	= $org_options['contact_email'];
	$subject = 'Event Espresso Test Message from' . $org_options['organization'];
	$message = 'Event Espresso email is working properly. Optional message: ' . $optional_message;
	$headers = 'From: ' . $org_options['contact_email'] . "\r\n" .
	'Reply-To: ' . $org_options['contact_email'] . "\r\n" .
	'X-Mailer: PHP/' . phpversion();
	wp_mail($to, $subject, $message, $headers);
}

//This function is not currently used
function event_espresso_session_start(){
	/*if(!isset($_SESSION['event_espresso_sessionid'])){
		$sessionid = (mt_rand(100,999).time());
		$_SESSION['event_espresso_sessionid'] = $sessionid;
	}*/
	//print_r( $_SESSION['event_espresso_sessionid']); //See if the session already exists
}

//This function just returns the session id.
function event_espresso_session_id(){
	if(!isset($_SESSION['event_espresso_sessionid'])){
		$sessionid = (mt_rand(100,999).time());
		$_SESSION['event_espresso_sessionid'] = $sessionid;
	}
	return $_SESSION['event_espresso_sessionid'];
}

//This function just returns the session id.
function espresso_reg_sessionid( $registration_id ){
	if(empty($_SESSION['espresso_reg_sessionid'])){
		$sessionid =  $registration_id;
		//$sessionid = (mt_rand(100,999).time());
		$_SESSION['espresso_reg_sessionid'] = $sessionid;
	}
	return $_SESSION['espresso_reg_sessionid'];
}

//Function to display additional attendee fields.
if (!function_exists('event_espresso_additional_attendees')) {
	function event_espresso_additional_attendees($additional_limit, $available_spaces){

	while (($i <= $additional_limit) && ($i < $available_spaces)) {
		$i++;
	}
	$i = $i-1;
?>
<p class="event_form_field additional_header" id="additional_header"><a onclick="return false;" href="#"><?php _e('Add More Attendees? (click to toggle, limit ' . $i . ')'  ,'event_espresso'); ?></a> </p>
<div id="additional_attendees">
	<div class="clone espresso_add_attendee">
        <p><label for="x_attendee_fname"><?php _e('First Name', 'event_espresso'); ?>:</label> <input type="text" name="x_attendee_fname[]" class='input'/></p>
        <p><label for="x_attendee_lname"><?php _e('Last Name', 'event_espresso'); ?>:</label> <input type="text" name="x_attendee_lname[]" class='input'/></p>
        <p><label for="x_attendee_email"><?php _e('Email', 'event_espresso'); ?>:</label> <input type="text" name="x_attendee_email[]" class='input'/></p>
        <p><label for="x_attendee_phone"><?php _e('Phone', 'event_espresso'); ?>:</label> <input type="text" name="x_attendee_phone[]" class='input'/></p>
   <a href="#" class="add" rel=".clone" title="<?php _e('Add an Additonal Attendee', 'event_espresso'); ?>"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL . "images/icons/add.png"; ?>" alt="<?php _e('Add an Additonal Attendee', 'event_espresso'); ?>" /></a>
    </div> <hr /></div>

<script type="text/javascript">

$jaer = jQuery.noConflict();
jQuery(document).ready(function($jaer) {
	$jaer(function(){
	  	var removeLink = '<a style="" class="remove" href="#" onclick="$jaer(this).parent().slideUp(function(){ $jaer(this).remove() }); return false"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL . "images/icons/remove.gif"; ?>" alt="<?php _e('Remove Attendee', 'event_espresso'); ?>" /></a>';
		$jaer('a.add').relCopy({limit: <?php echo $i; ?>, append: removeLink});
		
		 $jaer("#additional_attendees").hide();
		  //toggle the componenet with class msg_body
		  $jaer("#additional_header").click(function()
		  {
			$jaer(this).next("#additional_attendees").slideToggle(500);
		  });
	});
});
</script>
<?php
	}
}



//This function returns the condition of an event
if (!function_exists('event_espresso_get_is_active')) {
	function event_espresso_get_is_active($event_id){
		global $wpdb;
		//If the timezome is set in the wordpress database, then lets use it as the default timezone.
		if (get_option('timezone_string') != ''){
			date_default_timezone_set(get_option('timezone_string'));
		}
		
		$sql = "SELECT e.id, e.start_date start_date, e.is_active is_active, e.event_status event_status, ese.start_time start_time ";
		$sql .= "FROM ". EVENTS_DETAIL_TABLE . " e ";
		$sql .= "LEFT JOIN " . EVENTS_START_END_TABLE . " ese ON ese.event_id = e.id ";
		$sql .= "WHERE e.id = '" . $event_id . "' LIMIT 0,1";

		$events = $wpdb->get_results($sql);
		$start_date = $wpdb->last_result[0]->start_date;
		$is_active = $wpdb->last_result[0]->is_active;
		$event_status = $wpdb->last_result[0]->event_status;
		$start_time = $wpdb->last_result[0]->start_time;
		$timestamp = strtotime($start_date . ' '. $start_time );//Creates a timestamp from the event date and time
		//echo $timestamp;
		//echo date('Y-m-d h:i:s A', time());
		//echo time('', $timestamp);
		//echo date(time());
		//echo ' event date = '.date( $timestamp);
		if ($is_active == "Y" && $event_status == "O"){
			$event_status = array('status'=>'ONGOING', 'display'=>'<span style="color: #090; font-weight:bold;">'.__('ONGOING','event_espresso').'</span>');
		}
		elseif ($is_active == "Y" && $event_status == "S"){
			$event_status = array('status'=>'SECONDARY', 'display'=>'<span style="color: #090; font-weight:bold;">'.__('SECONDARY','event_espresso').'</span>');
		}
		elseif ($is_active == "Y" && date($timestamp) <= date(time()) && $event_status != "D"){
			$event_status = array('status'=>'EXPIRED', 'display'=>'<span style="color: #F00; font-weight:bold;">'.__('EXPIRED','event_espresso').'</span>');
		}
		elseif ($is_active == "Y" && date($timestamp) >= date(time()) && $event_status != "D"){
			$event_status = array('status'=>'ACTIVE', 'display'=>'<span style="color: #090; font-weight:bold;">'.__('ACTIVE','event_espresso').'</span>');
		}
		elseif ($is_active == "N" && $event_status != "D"){
			$event_status = array('status'=>'NOT_ACTIVE', 'display'=>'<span style="color: #F00; font-weight:bold;">'.__('NOT_ACTIVE','event_espresso').'</span>');
		}
		elseif ($event_status == "D"){
			$event_status = array('status'=>'DELETED', 'display'=>'<span style="color: #000; font-weight:bold;">'.__('DELETED','event_espresso').'</span>');;
		}

		return $event_status;
	}
}

//This function returns the overall status of an event
if (!function_exists('event_espresso_get_status')) {
	function event_espresso_get_status($event_id){
		$event_status = event_espresso_get_is_active($event_id);
		switch ($event_status['status']){
			case 'EXPIRED':
			case 'NOT_ACTIVE':
			case 'DELETED':
				return 'NOT_ACTIVE';
			break;

			case 'ACTIVE':
			case 'ONGOING':
				return 'ACTIVE';
			break;

			default:
			break;
		}
	}
}
/*Formats the event address*/
if (!function_exists('event_espresso_format_address')) {
	function event_espresso_format_address($event_address){
		$event_address=str_replace(array("\r\n", "\n", "\r"),"<br>",$event_address);
		return $event_address;
	}
}
//Function for merging arrrays
function event_espresso_array_merge($array1, $array2){
	$result = array_merge($array1, $array2);
	return $result;
}

// Append associative array elements
function event_espresso_array_push_associative(&$arr) {
   $args = func_get_args();
   foreach ($args as $arg) {
       if (is_array($arg)) {
           foreach ($arg as $key => $value) {
               $arr[$key] = $value;
           }
       }else{
           $arr[$arg] = "";
       }
   }
}

/*
* Display the amount of attendees and/or registration limit
* Available parameters for the get_number_of_attendees_reg_limit() function
*  @ $event_id - required
*  @ $type -
*		available_spaces = returns the number of available spaces
*		num_attendees = returns the number of attendees
*		reg_limit = returns the total number of spaces
*		num_incomplete = returns the number of incomplete (non paid) registrations
*		num_completed = returns the number of completed (paid) registrations
*		num_completed_slash_incomplete = returns the number of completed and incomplete registrations separated by a slash (eg. 3/1)
*		num_attendees_slash_reg_limit = returns the number of attendees and the registration limit separated by a slash (eg. 4/30)
*	@ $full_text - the text to display when the event is full
*/
if (!function_exists('get_number_of_attendees_reg_limit')) {
	function get_number_of_attendees_reg_limit($event_id, $type = 'NULL', $full_text = 'EVENT FULL'){
			global $wpdb;

			switch($type){
				case 'number_available_spaces' :
					$wpdb->get_results("SELECT id FROM ". EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "'");
					$num_attendees = $wpdb->num_rows;
					$sql_reg_limit = "SELECT reg_limit FROM ". EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'";
					$reg_limit = $wpdb->get_var($sql_reg_limit);

					if ($reg_limit > $num_attendees){
						$number_available_spaces = $reg_limit - $num_attendees;
					}
					return $number_available_spaces;
				break;
				case 'available_spaces' :
					$wpdb->get_results("SELECT id FROM ". EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "'");
					$num_attendees = $wpdb->num_rows;
					$sql_reg_limit = "SELECT reg_limit FROM ". EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'";
					$reg_limit = $wpdb->get_var($sql_reg_limit);

					if ($reg_limit > $num_attendees){
						$available_spaces = $reg_limit - $num_attendees;
					}else if ($reg_limit <= $num_attendees){
						$available_spaces = '<span style="color: #F00; font-weight:bold;">'. $full_text .'</span>';
					}
					if ($reg_limit == "" || $reg_limit == " " || $reg_limit == "999"){
						$available_spaces = "Unlimited";
					}
					return $available_spaces;
				break;
				case 'num_attendees' :
					$wpdb->get_results("SELECT id FROM ". EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "'");
					$num_attendees = $wpdb->num_rows;
					return $num_attendees;
				break;
				case 'reg_limit' :
					$sql_reg_limit = "SELECT reg_limit FROM ". EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'";
					$reg_limit = $wpdb->get_var($sql_reg_limit);

					if ($reg_limit == "" || $reg_limit == " " || $reg_limit == "999"){
						$available_spaces = "Unlimited";
					}
					return $reg_limit;
				break;
				case 'num_incomplete' :
					$wpdb->get_results("SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND payment_status='Incomplete'");
					$num_incomplete = $wpdb->num_rows;
					return $num_incomplete;
				break;
				case 'num_completed' :
					$wpdb->get_results("SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND payment_status='Completed'");
					$num_completed = $wpdb->num_rows;
					return $num_completed;
				break;
				case 'num_completed_slash_incomplete' :
					$wpdb->get_results("SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND payment_status='Completed'");
					$num_completed = $wpdb->num_rows;
					$wpdb->get_results("SELECT id FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "' AND payment_status='Incomplete'");
					$num_incomplete = $wpdb->num_rows;
					return '<font color="green">' . $num_completed . '</font>/<font color="red">' . $num_incomplete . '</font>/';
				break;
				case 'num_attendees_slash_reg_limit' :
				default:$wpdb->get_results("SELECT id FROM ". EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "'");
					$num_attendees = $wpdb->num_rows;
					$sql_reg_limit = "SELECT reg_limit FROM ". EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'";
					$reg_limit = $wpdb->get_var($sql_reg_limit);

					if ($reg_limit == "" || $reg_limit == " " || $reg_limit == "999"){
						$reg_limit = "Unlimited";
					}
					return $num_attendees . '/'. $reg_limit;
					//return $num_attendees . '/'. '<font color="green">' . $num_completed . '</font>/<font color="red">' . $num_incomplete . '</font>/'. $reg_limit;
				break;
			}
	}
}




/*
Returns the price of an event
*
* @params string $date
*/
if (!function_exists('event_espresso_get_price')) {
	function event_espresso_get_price($event_id) {
		global $wpdb, $org_options;
		$results = $wpdb->get_results("SELECT id, event_cost, surcharge, price_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='".$event_id."' ORDER BY id ASC LIMIT 1");
		foreach ($results as $result){
			if ($wpdb->num_rows == 1) {
				if ($result->event_cost > 0.00){
					
					$event_cost = $org_options['currency_symbol'] . $result->event_cost;
					
					// Addition for Early Registration discount
					if (early_discount_amount($event_id, $result->event_cost) != false){
						$early_price_data = array();
						$early_price_data = early_discount_amount($event_id, $result->event_cost);
						$result->event_cost = $early_price_data['event_price'];
						$message = __(' (including ' . $early_price_data['early_disc'] . ' early discount)', 'event_espresso');
						//$surcharge = ($result->surcharge > 0.00 && $result->event_cost > 0.00)?" +{$result->surcharge}% " . __('Surcharge','event_espresso'):'';
						$event_cost = '<span class="event_price_value">' . $org_options['currency_symbol'] . number_format($result->event_cost,2) . $message . $surcharge . '</span>';
					}

					$event_cost .= '<input type="hidden"name="event_cost" value="' . $result->event_cost . '">';
				}else{
					$event_cost = __('Free Event','event_espresso');
				}
			}else if ($wpdb->num_rows == 0){
				$event_cost = __('Free Event','event_espresso');
			}
		}

		return $event_cost;
	}
}

/*
Returns the final price of an event
*
* @params int $price_id
* @params int $event_id
*/
if (!function_exists('event_espresso_get_final_price')) {
	function event_espresso_get_final_price($price_id, $event_id = 0) {
		global $wpdb, $org_options;
		$results = $wpdb->get_results("SELECT id, event_cost, surcharge FROM " . EVENTS_PRICES_TABLE . " WHERE id='".$price_id."' ORDER BY id ASC LIMIT 1");
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


//Get the early bird pricing
if (!function_exists('early_discount_amount')) {
	function early_discount_amount($event_id, $event_cost, $message=''){
		global $wpdb,$org_options;
	
		//$message = ' ' . __('Early Pricing','event_espresso');
		$eventdata = $wpdb->get_results("SELECT early_disc, early_disc_date, early_disc_percentage FROM " . EVENTS_DETAIL_TABLE . " WHERE id='".$event_id."' LIMIT 1");
		if ((strlen($eventdata[0]->early_disc)>0) && (strtotime($eventdata[0]->early_disc_date) > strtotime(date("Y-m-d")))) {
			$early_price_display = $eventdata[0]->early_disc_percentage == 'Y' ? $eventdata[0]->early_disc.'%' : $org_options['currency_symbol'].$eventdata[0]->early_disc;
				if($eventdata[0]->early_disc_percentage == 'Y'){
					$pdisc  = $eventdata[0]->early_disc / 100;
					$event_cost = $event_cost - ($event_cost * $pdisc);
				}else{
					$event_cost = $event_cost - $eventdata[0]->early_disc;
				}
		//$extra = " " . $message;
		$early_price_data = array('event_price'=>$event_cost, 'early_disc'=>$early_price_display);
		return $early_price_data;
		}else{
			return false;
		}
	}
}

//Creates dropdowns if multiple prices are associated with an event
if (!function_exists('event_espresso_price_dropdown')) {
	function event_espresso_price_dropdown($event_id) {
		global $wpdb,$org_options;
		$results = $wpdb->get_results("SELECT id, event_cost, surcharge, price_type FROM " . EVENTS_PRICES_TABLE . " WHERE event_id='".$event_id."' ORDER BY id ASC");
		if ($wpdb->num_rows > 1) {
			echo '<label for="event_cost">' . __('Choose an Option: ','event_espresso') . '</label>';
			echo '<select name="price_option" id="price_option-' . $event_id . '">';
			foreach ($results as $result){

				// Addition for Early Registration discount
				if (early_discount_amount($event_id, $result->event_cost) != false){
					$early_price_data = array();
					$early_price_data = early_discount_amount($event_id, $result->event_cost);
					$result->event_cost = $early_price_data['event_price'];
					$message = __(' Early Pricing','event_espresso');
				}

				$surcharge = ($result->surcharge > 0.00 && $result->event_cost > 0.00)?" +{$result->surcharge}% " . __('Surcharge','event_espresso'):'';

				//echo '<option value="' . number_format($result->event_cost,2) . '|' . $result->price_type . '|' . $result->surcharge . '">' . $result->price_type . ' (' . $org_options['currency_symbol'] .  number_format($result->event_cost,2) . $message  . ') '. $surcharge . ' </option>';
				
				//Using price ID
				echo '<option value="' . $result->id . '|' . $result->price_type . '">' . $result->price_type . ' (' . $org_options['currency_symbol'] .  number_format($result->event_cost,2) . $message  . ') '. $surcharge . ' </option>';
			}
			echo '</select><input type="hidden" name="price_select" id="price_select-' . $event_id . '" value="true">';
		}else if ($wpdb->num_rows == 1) {
			foreach ($results as $result){

				// Addition for Early Registration discount
				if (early_discount_amount($event_id, $result->event_cost) != false){
					$early_price_data = array();
					$early_price_data = early_discount_amount($event_id, $result->event_cost);
					$result->event_cost = $early_price_data['event_price'];
					$message = __(' (including ' . $early_price_data['early_disc'] . ' early discount)', 'event_espresso');
				}

				$surcharge = ($result->surcharge > 0.00 && $result->event_cost > 0.00)?" +{$result->surcharge}% " . __('Surcharge','event_espresso'):'';

				echo '<span class="event_price_label">' . __('Price: ','event_espresso') . '</span> <span class="event_price_value">' . $org_options['currency_symbol'] . number_format($result->event_cost,2) . $message . $surcharge . '</span>';
				echo '<input type="hidden" name="price_id" id="price_id-' . $event_id . '" value="' . $result->id . '">';
			}
		}else if ($wpdb->num_rows < 0){
			echo '<span class="free_event">' . __('Free Event','event_espresso') . '</span>';
			echo '<input type="hidden" name="payment" id="payment-' . $event_id . '" value="' . __('free event','event_espresso') . '">';
		}
	}
}

function event_espresso_update_alert($url=''){
	return wp_remote_retrieve_body( wp_remote_get($url) );
}

//Gets the current page url. Used for redirecting back to a page
function event_espresso_cur_pageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
  		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
 		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

//This function simply returns a custom capability, nothing else. Can be used to change admin capability of the Event Manager menu without the admin losing rights to certain menus. Should be used with the custom files addon.
function event_espresso_management_capability( $default, $custom ) {
	return $custom;
}
add_filter( 'event_espresso_management_capability', 'event_espresso_management_capability', 10, 3 );

//Build the form questions. This function can be overridden using the custom files addon
if (!function_exists('event_espresso_add_question_groups')) {
	function event_espresso_add_question_groups($question_groups){
		global $wpdb;
		if (count($question_groups) > 0){
			$questions_in = '';
	
			foreach ($question_groups as $g_id) $questions_in .= $g_id . ',';
	
				$questions_in = substr($questions_in,0,-1);
				$group_name = '';
				$counter = 0;
	
				$questions = $wpdb->get_results("SELECT q.*, qg.group_name,qg.group_description, qg.show_group_name, qg.show_group_description, qg.group_identifier
                                                                FROM " . EVENTS_QUESTION_TABLE . " q
                                                                JOIN " .  EVENTS_QST_GROUP_REL_TABLE . " qgr
                                                                ON q.id = qgr.question_id
                                                                JOIN " . EVENTS_QST_GROUP_TABLE . " qg
                                                                ON qg.id = qgr.group_id
                                                                WHERE qgr.group_id in ( " .   $questions_in
                                                                . ") ORDER BY qg.system_group DESC, q.sequence, q.id ASC");
				$num_rows = $wpdb->num_rows;
                                
				if ($num_rows > 0 ){
					foreach($questions as $question){
	
						//if new group, close fieldset
						echo ($group_name != '' &&  $group_name != $question->group_name) ?'</div>':'';
	
						if ($group_name != $question->group_name){
							echo '<div class="event_questions" id="' . $question->group_identifier . '">';
							echo $question->show_group_name != 0?"<h3>$question->group_name</h3>":'';
                                                        echo $question->show_group_description != 0?"<p>$question->group_description</p>":'';
							$group_name = $question->group_name;
	 
						}
													   
						event_form_build($question);
	
						$counter++;
						echo $counter == $num_rows?'</div>':'';
		
				}
											  
			}//end questions display
		}
	}
}



//Social media buttons
if (!function_exists('espresso_show_social_media')) {
	function espresso_show_social_media($event_id, $type = 'twitter'){
		switch ($type) {
			case 'twitter':
				if (function_exists('espresso_twitter_button')) { echo  espresso_twitter_button ($event_id); }
			break;
			case 'facebook':
				if (function_exists('espresso_facebook_button')) { echo  espresso_facebook_button ($event_id); }
			break;
			default:
			break;
		}
	}
}

//This function returns an array of category data based on an event id
if (!function_exists('espresso_event_category_data')) {
	function espresso_event_category_data($event_id){
		global $wpdb;
		$sql = "SELECT c.category_identifier, c.category_name, c.category_desc, c.display_desc FROM ". EVENTS_DETAIL_TABLE . " e ";
		$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.event_id = e.id ";
		$sql .= " JOIN " . EVENTS_CATEGORY_TABLE . " c ON  c.id = r.cat_id ";
		$sql .= " WHERE e.id = '" . $event_id . "' ";
		
		$wpdb->get_results($sql);
		
		$category_data = array( 'category_identifier'=>$wpdb->last_result[0]->category_identifier, 'category_name'=>$wpdb->last_result[0]->category_name, 'category_desc'=>$wpdb->last_result[0]->category_desc, 'display_desc'=>$wpdb->last_result[0]->display_desc );
		
		return $category_data;
	}
}

if (!function_exists('espresso_registration_id')) {
	function espresso_registration_id($attendee_id){
		global $wpdb;
		$sql = $wpdb->get_results("SELECT registration_id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE id ='" . $attendee_id . "'");
		$num_rows = $wpdb->num_rows;
                                
		if ($num_rows > 0 ){
			return $wpdb->last_result[0]->registration_id;
			
		}else{
			return 0;
		}
	}
}
if (!function_exists('espresso_attendee_id')) {
	function espresso_attendee_id($registration_id){
		global $wpdb;
		$sql = $wpdb->get_results("SELECT id FROM ". EVENTS_ATTENDEE_TABLE ." WHERE registration_id ='" . $registration_id . "'");
		$num_rows = $wpdb->num_rows;
                                
		if ($num_rows > 0 ){
			return $wpdb->last_result[0]->id;
			
		}else{
			return 0;
		}
	}
}
//Creates a Google Map Link
if (!function_exists('espresso_google_map_link')) {
	function espresso_google_map_link($atts){
		extract($atts);
		
		$address = "{$address}";
		$city = "{$city}";
		$state = "{$state}";
		$zip = "{$zip}";
		$country = "{$country}";
		$text = "{$text}";
		$type = "{$type}";
		
		$gaddress = ($address != '' ? $address :'') . ($city != '' ? ',' . $city :'') . ($state != '' ? ',' . $state :'') . ($zip != '' ? ',' . $zip :'') . 
		($country != '' ? ',' . $country :'');
		
		$google_map = htmlentities2('http://maps.google.com/maps?q='.$gaddress);
		
		switch ($type){
			
			
			case 'text':
			default:
				$text = $text == '' ? 'Map and Directions' : $text;
			break;
			
			case 'url':
				$text = $google_map;
			break;
		}
		
		return $google_map_link = '<a href="'.$google_map.'" target="_blank">' . $text . '</a>';
	}
}