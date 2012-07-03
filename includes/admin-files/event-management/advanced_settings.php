<?php
 $status=array(array('id'=>'A','text'=> __('Primary','event_espresso')), array('id'=>'S','text'=> __('Secondary','event_espresso')), array('id'=>'O','text'=> __('Ongoing','event_espresso')), array('id'=>'D','text'=> __('Deleted','event_espresso')));
    $additional_attendee_reg_info_dd = '';
$additional_attendee_reg_info = array(
			//array('id'=>'1','text'=> __('No info required','event_espresso')),
			array('id'=>'2','text'=> __('Personal Information only','event_espresso')),
			array('id'=>'3','text'=> __('Full registration information','event_espresso'))
    	);

     	if (get_option('event_espresso_multi_reg_active') == 1)
			$additional_attendee_reg_info_dd = '<p>' .__('Additional Attendee Registration info?','event_espresso') . ' ' . select_input('additional_attendee_reg_info', $additional_attendee_reg_info, $event_meta['additional_attendee_reg_info']) .'</p>';
	   $advanced_options = '<p><strong>' .__('Advanced Options:','event_espresso') . '</strong></p>' .
			'<p>' .__('Is this an active event? ','event_espresso') . __( select_input('is_active', $values, $is_active)) . '</p>' .
			'<p>' .__('Event Status: ','event_espresso') . __( select_input('event_status', $status, $event_status)) . ' <a class="ev_reg-fancylink" href="#status_types_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a></p>' . 
			'<p>' .__('Display  description? ','event_espresso') . select_input('display_desc', $values, $display_desc) . '</p>' .
			'<p>' .__('Display  registration form? ','event_espresso') . select_input('display_reg_form', $values, $display_reg_form) . '</p>' .
			($event_status != 'S' ? espresso_secondary_events_dd($overflow_event_id, $allow_overflow) :'' ) .
			'<p>' .__('Use an alternate registration page?','event_espresso') . '<br />
				<input name="externalURL" size="20" type="text" value="' . $externalURL . '"> <a class="ev_reg-fancylink" href="#external_URL_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a><br /></p>' .
			'<p>' .__('Use an alternate email address?','event_espresso') . '<br />
				<input name="alt_email" size="20" type="text" value="' . $alt_email . '"> <a class="ev_reg-fancylink" href="#alt_email_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a></p>';
?>