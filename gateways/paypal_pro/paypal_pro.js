jQuery(document).ready(function($) {
	$('#paypal_pro_payment_form').validate();
	$('#paypal_pro_payment_form').submit(function(){
		if ($('#paypal_pro_payment_form').valid()){
			$('#processing').html('<img class="ee-ajax-loader-img" src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="paypal_pro_submit"]').attr('disabled', 'disabled');
		}
	})
});