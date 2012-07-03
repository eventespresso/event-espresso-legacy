<?php
function espresso_system_check() {
    return true;
}

if ( !function_exists( 'event_espresso_custom_questions_output' ) ){
    function event_espresso_custom_questions_output( $atts ) {
    global $wpdb;

     extract( $atts );
//Get the questions for the attendee
        $questions = $wpdb->get_results( "SELECT ea.answer, eq.question
								FROM " . EVENTS_ANSWER_TABLE . " ea
								LEFT JOIN " . EVENTS_QUESTION_TABLE . " eq ON eq.id = ea.question_id
								WHERE ea.attendee_id = '" . $attendee_id . "' AND system_name IS NULL AND eq.admin_only = 'N' ORDER BY eq.sequence asc " );
        //echo $wpdb->last_query . '<br />';

        $email_questions = '';
        $q_counter = 0;
        $q_num_rows = $wpdb->num_rows;
        if ( $q_num_rows > 0 )
        {
            
            foreach ( $questions as $question ) {
                $email_questions .= $question->answer != '' ? wpautop( '<strong>' . $question->question . ':</strong><br /> ' . str_replace( ',', '<br />', $question->answer ) ) : '';
                $q_counter++;
                if ( $q_counter == $q_num_rows )
                   return $email_questions;
            }
        }
        return $email_questions;
    }

}

if ( !function_exists( 'espresso_venue_dd' ) ){
	function espresso_venue_dd($current_value=0){
		global $espresso_premium; if ($espresso_premium != true) return;
		global $wpdb;
			$sql = "SELECT id, name, city, state FROM " . EVENTS_VENUE_TABLE;//. EVENTS_DETAIL_TABLE;
			$sql .= " WHERE name != '' GROUP BY name ";
			
			$venues = $wpdb->get_results($sql);
			$num_rows = $wpdb->num_rows;
			//return print_r( $events );
			if ($num_rows > 0) {
				$field = '<select name="venue_id[]" id="venue_id">\n';
				$field .= '<option value="0">'.__('Select a Venue', 'event_espresso').'</option>';
				
				foreach ($venues as $venue){
					$selected = $venue->id == $current_value ? 'selected="selected"' : '';
					$field .= '<option '. $selected .' value="' . $venue->id .'">' . $venue->name . ' (' . $venue->city. ', ' . $venue->state . ') </option>\n';
				}
				$field .= "</select>";
				$html = '<p>' . $field .'</p>';
				return $html;
			}else{
				return '<a href="admin.php?page=event_venues&amp;action=add_new_venue">'.__('Please add at least one venue.', 'event_espresso').'</a>';
			}
	}
}

if ( !function_exists( 'espresso_personnel_cb' ) ){
	function espresso_personnel_cb($event_id = 0){
		global $espresso_premium; if ($espresso_premium != true) return;
		global $wpdb;
		$event_personnel = $wpdb->get_results("SELECT * FROM " . EVENTS_PERSONNEL_TABLE);
		$num_rows = $wpdb->num_rows;
		if ($num_rows > 0){
			$html= '';
			foreach ($event_personnel as $person){
				$person_id = $person->id;
				$person_name = $person->name;
							
				$meta = unserialize($person->meta);
				$person_title = $meta['title']!=''? ' (' . $meta['title'] . ')':'';
	
				$in_event_personnel = $wpdb->get_results("SELECT * FROM " . EVENTS_PERSONNEL_REL_TABLE . " WHERE event_id='".$event_id."' AND person_id='".$person_id."'");
				foreach ($in_event_personnel as $in_person){
					$in_event_person = $in_person->person_id;
				}
				
				$html .= '<p id="event-person-' . $person_id . '"><label for="in-event-person-' . $person_id . '" class="selectit"><input value="' . $person_id . '" type="checkbox" name="event_person[]" id="in-event-person-' . $person_id . '"' . ($in_event_person == $person_id ? ' checked="checked"' : "" ) . '/> ' . $person_name . $person_title."</label></p>";
				
			}
			
			if ($num_rows > 10){
				$top_div = '<div style="height:250px;overflow:auto;">';
				$bottom_div = '</div>';
			}
			$html = $top_div.$html.$bottom_div;
			return $html;
				
		}else{
			return '<a href="admin.php?page=event_staff&amp;action=add_new_person">'.__('Please add at least one person.', 'event_espresso').'</a>';
		}
	}
}

if ( !function_exists( 'espresso_personnel_dd' ) ){
	function espresso_personnel_dd(){
		global $espresso_premium; if ($espresso_premium != true) return;
		global $wpdb;
			$sql = "SELECT name, title FROM EVENTS_PERSONNEL_TABLE ";//. EVENTS_DETAIL_TABLE;
			$sql .= " WHERE name != '' GROUP BY name ";
			
			$people = $wpdb->get_results($sql);
			$num_rows = $wpdb->num_rows;
			//return print_r( $events );
			if ($num_rows > 0) {
				$field = '<select name="event_primary_person id="event_primary_person">\n';
				$field .= '<option value="0">'.__('Select a Person', 'event_espresso').'</option>';
				
				foreach ($people as $person){
					$selected = $event->name == $current_value ? 'selected="selected"' : '';
					$meta = unserialize($person->meta);
					$title = $meta['title']!=''? ' (' . $meta['title'] . ')':'';
					$field .= '<option '. $selected .' value="' . $person->id .'">' . $person->name .  $title . '</option>\n';
				}
				$field .= "</select>";
				$html = '<p>' .__('Primary','event_espresso') . ': ' . $field .'</p>';
				return $html;
			}
	}
}