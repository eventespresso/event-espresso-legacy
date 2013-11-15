jQuery(document).ready(function($) {
	$('#evertec_payment_form').validate({
		rules:{
			//credit card fields are only required when making a credit card pruhcase
			//same goes for bank fields
			//credit card fields
			"card_num": {
					required: evertec_form_is_using_credit_card_payment_method
			},
			"expmonth": {
					required: evertec_form_is_using_credit_card_payment_method
			},
			"expyear": {
					required: evertec_form_is_using_credit_card_payment_method
			},
			"cvv": {
					required: evertec_form_is_using_credit_card_payment_method
			},
			//bank fields
			"bankRoutingNumber": {
					required: evertec_form_is_using_bank_payment_method
			},
			"bankAccountNumber": {
					required: evertec_form_is_using_bank_payment_method
			},
			"bankClientName": {
					required: evertec_form_is_using_bank_payment_method
			},
			"authorizationBit": {
					required: evertec_form_is_using_bank_payment_method
			}
			
		}
	});
	$('#evertec_payment_form').submit(function(){
		if ($('#evertec_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			$(':input[name="evertec_submit"]').attr('disabled', 'disabled');
		}
	});
	$('#evertec_payment_method').change(function(event){
		method = jQuery(this).val();
		jQuery('#evertec-credit-card-info-dv').hide();
		jQuery('#evertec-bank-info-dv').hide();
		
		if(is_credit_card_method(method)){
				jQuery('#evertec-credit-card-info-dv').toggle('slow');
		}else if(is_bank_method(method)){
				jQuery('#evertec-bank-info-dv').toggle('slow');
		}
	});
});

function evertec_form_is_using_credit_card_payment_method(element){
	method = get_evertec_payment_method();
	return is_credit_card_method(method);
}
function evertec_form_is_using_bank_payment_method(element){
	method = get_evertec_payment_method();
	return is_bank_method(method);
}

function get_evertec_payment_method(){
	return jQuery('#evertec_payment_method').val();
}
function is_credit_card_method(method){
	return (method == 'A' ||
			method == 'V' ||
			method == 'M' ||
			method == 'X');
}
function is_bank_method(method){
	return (method == 'W' ||
			method == 'S' ||
			method == 'C');
}