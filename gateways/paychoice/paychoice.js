jQuery(document).ready(function($) {
	$('#paychoice_payment_form').validate();
	$('#paychoice_payment_form').submit(function(){
		if ($('#paychoice_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
		}
	})
});