<?php 
//List Attendees Template
//Show a list of attendees using a shortcode
//[LISTATTENDEES]
//[LISTATTENDEES limit="30"]
//[LISTATTENDEES show_expired="false"]
//[LISTATTENDEES show_deleted="false"]
//[LISTATTENDEES show_secondary="false"]
//[LISTATTENDEES show_gravatar="true"]
//[LISTATTENDEES show_recurrence="false"]
//[LISTATTENDEES event_identifier="your_event_identifier"]
//[LISTATTENDEES category_identifier="your_category_identifier"]

//Please refer to this page for an updated lsit of shortcodes: http://eventespresso.com/forums/?p=592


/*Example CSS for your themes style sheet:

li.attendee_details{
	display:block;
	margin-bottom:20px;
	background: #ECECEC;
	border:#CCC 1px solid;
}
.espresso_attendee{
	width:400px;
	padding:5px;
}
.espresso_attendee img.avatar{
	float:left;
	padding:5px;
}
.clear{
	clear:both;
}
*/

//The following code displays your list of attendees.
//The processing for this function is managed in the shortcodes.php file.
if (!function_exists('event_espresso_show_attendess')) {
	function event_espresso_show_attendess($sql,$show_gravatar){
		//echo $sql;
		global $wpdb;
		$events = $wpdb->get_results($sql);
		foreach ($events as $event){	
			$event_id = $event->id;
			$event_name = $event->event_name;
			$event_desc = $event->event_desc;
			
			//This variable is only available using the espresso_event_status function which is loacted in the Custom Files Addon (http://eventespresso.com/download/plugins-and-addons/custom-files-addon/)
			$event_status = function_exists('espresso_event_status') ? espresso_event_status($event_id) : '';
			//Example usage in the event title:
			/*<h2><?php _e('Attendee Listing For: ','event_espresso'); ?><?php echo $event_name . ' - ' . $event_status?> </h2>*/
	?>
				<h2><?php _e('Attendee Listing For: ','event_espresso'); ?><?php echo $event_name . ' - ' . $event_status?> </h2>
					<?php echo wpautop($event_desc); ?>
					<ol class="attendee_list">
					  <?php
							$attendees = $wpdb->get_results("SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE event_id='" . $event_id . "'");
							foreach ($attendees as $attendee){
								$id = $attendee->id;
								$lname = $attendee->lname;
								$fname = $attendee->fname;
								$city = $attendee->city;
								$state = $attendee->state;
								$country = $attendee->state;
								$email = $attendee->email;
								
								$gravatar = $show_gravatar == 'true'? get_avatar( $email, $size = '100', $default = 'http://www.gravatar.com/avatar/' ) : '';
								
					?>
					  <li class="attendee_details">
						<div class="espresso_attendee"><?php echo $gravatar ?><?php echo '<p><strong>' . $fname . ' ' . $lname . '</strong><br />' . ($city != '' ? $city :'') . ($state != '' ? ', ' . $state :' ') . '</p>'; ?> </div>
						<div class="clear"></div>
					  </li>
					  <?php	} ?>
					</ol>
	<?php 
		}
	}
}

