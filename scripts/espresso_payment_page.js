jQuery(document).ready(function($) {

	$('.hide-if-js').hide();
	$('.payment_container').toggleClass('payment-option-closed'); 
	$('.payment-option-dv').toggleClass('payment-option-closed'); 
	
	var preventLeavePage = true;
	
	// generic click event for displaying and giving focus to an element and hiding control 
	$('.display-the-hidden').on( 'click', function() {
		// get target element from "this" (the control element's) "rel" attribute
		var item_to_display = $(this).attr("rel"); 
		// display the target's div container - use slideToggle or removeClass
		$('#'+item_to_display+'-dv').slideToggle( 500, function() {
			$(this).parent().toggleClass('payment-option-closed'); 
		}); 
		return false;
	});

	// generic click event for re-hiding an element and displaying it's display control 
	$('.hide-the-displayed').on( 'click', function() {
		// get target element from "this" (the control element's) "rel" attribute
		var item_to_hide = $(this).attr("rel"); 
		// hide the target's div container - use slideToggle or addClass
		$('#'+item_to_hide+'-dv').slideToggle( 500, function() {
			$(this).parent().toggleClass('payment-option-closed'); 
		}); 
		return false;
	});	


	$('.payment-option-lnk').on( 'click', function() {
		preventLeavePage = false;
	});
	
	$('.finalize_button').on( 'click', function() {
		preventLeavePage = false;
	});
	
	window.onbeforeunload = function() {
		if ( preventLeavePage ) {
	  	  return 'Warning!!! Using the back button will overwrite your existing registration.';
		}
	}	
});	