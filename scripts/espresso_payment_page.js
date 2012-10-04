jQuery(document).ready(function($) {

	$('.hide-if-js').hide();
	$('.payment_container').toggleClass('payment-option-closed'); 
	$('.payment-option-dv').toggleClass('payment-option-closed'); 

	
	// generic click event for displaying and giving focus to an element and hiding control 
	$('.display-the-hidden').click(function() {
		// get target element from "this" (the control element's) "rel" attribute
		var item_to_display = $(this).attr("rel"); 
		// display the target's div container - use slideToggle or removeClass
		$('#'+item_to_display+'-dv').slideToggle( 500, function() {
			$(this).parent().toggleClass('payment-option-closed'); 
		}); 
		return false;
	});

	// generic click event for re-hiding an element and displaying it's display control 
	$('.hide-the-displayed').click(function() {
		// get target element from "this" (the control element's) "rel" attribute
		var item_to_hide = $(this).attr("rel"); 
		// hide the target's div container - use slideToggle or addClass
		$('#'+item_to_hide+'-dv').slideToggle( 500, function() {
			$(this).parent().toggleClass('payment-option-closed'); 
		}); 
		return false;
	});	
	
});	