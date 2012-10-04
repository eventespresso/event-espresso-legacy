jQuery(document).ready(function($) {

	$('.hide-if-js').hide();
	$('.payment_container').css({ 'display' : 'inline-block' });
	$('.payment-option-dv').css({ 'display' : 'inline-block' });

	
	// generic click event for displaying and giving focus to an element and hiding control 
	$('.display-the-hidden').click(function() {
		// get target element from "this" (the control element's) "rel" attribute
		var item_to_display = $(this).attr("rel"); 
		// display the target's div container - use slideToggle or removeClass
		$('#'+item_to_display+'-dv').slideToggle( 500, function() {
			$('#'+item_to_display+'-dv').parent().css({ 'display' : 'block' }); 
		}); 
		return false;
	});

	// generic click event for re-hiding an element and displaying it's display control 
	$('.hide-the-displayed').click(function() {
		// get target element from "this" (the control element's) "rel" attribute
		var item_to_hide = $(this).attr("rel"); 
		// hide the target's div container - use slideToggle or addClass
		$('#'+item_to_hide+'-dv').slideToggle( 500, function() {
			$('#'+item_to_hide+'-dv').parent().css({ 'display' : 'inline-block' });
		}); 
		return false;
	});	
	
});	