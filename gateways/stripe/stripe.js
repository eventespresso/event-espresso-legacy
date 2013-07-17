jQuery(document).ready(function($) {
	$('#stripe_payment_form').validate();
	$('#stripe_payment_form').submit(function(){
		if ($('#stripe_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
		}
	})
});