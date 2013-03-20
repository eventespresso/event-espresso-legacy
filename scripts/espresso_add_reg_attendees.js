jQuery(document).ready(function($) {

	var additional_limit = espresso_add_reg_attendees.additional_limit;

	function markup( attendee_num ) {
		var attendee_form = espresso_add_reg_attendees.attendee_form;
		attendee_form = attendee_form.replace( /XXXXXX/g, attendee_num );
		return attendee_form;
	}

	$( '#registration_form' ).on( 'click', '.add-additional-attendee-lnk', function(e) {
		// grab this attendee's form #'
		var att_nmbr = parseInt( $(this).attr('rel'));		
		var next_att = parseInt( att_nmbr ) + 1;
		var prev_att = parseInt( att_nmbr ) - 1;
		// don't process beyond additional_limit'
		if ( next_att <= additional_limit ) {
			// add form for next attendee
			$('.event_form_submit').before( markup( next_att ));
			// hide add/remove buttons for this attendee
			$( '#add-additional-attendee-' + att_nmbr ).hide();
			$( '#remove-additional-attendee-' + att_nmbr ).hide();
			if ( next_att == ( additional_limit )) {
				$('#add-additional-attendee-' + next_att ).remove();
			}			
		}
		// don't process link
		e.preventDefault();
	});	

	$( '#registration_form' ).on( 'click', '.remove-additional-attendee-lnk', function(e){
		// grab this attendee's form #'
		var att_nmbr = parseInt( $(this).attr('rel') );
		var prev_att = att_nmbr - 1;
		// remove this attendee
		$( '#additional_attendee_' + att_nmbr ).remove();
		// show add/remove buttons for the previous attendee
		$( '#add-additional-attendee-' + prev_att ).show();
		$( '#remove-additional-attendee-' + prev_att ).show();		
		// don't process link
		e.preventDefault();
	});
	
});
