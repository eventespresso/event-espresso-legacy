jQuery(document).ready(function($) {
	$('#myvirtualmerchant_payment_form').validate();
	$('#myvirtualmerchant_payment_form').submit(function(){
		if ($('#myvirtualmerchant_payment_form').valid()){
			$('#processing').html('<img class="ee-ajax-loader-img" src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="myvirtualmerchant_submit"]').attr('disabled', 'disabled');
		}
	})
});