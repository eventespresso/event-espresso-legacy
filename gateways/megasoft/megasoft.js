jQuery(document).ready(function($) {
	$('#megasoft_payment_form').validate();
	$('#megasoft_payment_form').submit(function(){
		if ($('#megasoft_payment_form').valid()){
			$('#processing').html('<img class="ee-ajax-loader-img" src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="paypal_pro_submit"]').attr('disabled', 'disabled');
		}
	})
});