jQuery(document).ready(function($) {
	$('#securepay_aus_payment_form').validate();
	$('#securepay_aus_payment_form').submit(function(){
		if ($('#securepay_aus_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="securepay_aus_submit"]').attr('disabled', 'disabled');
		}
	})
});