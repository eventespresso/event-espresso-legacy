jQuery(document).ready(function($) {
	$('#paytrace_payment_form').validate();
	$('#paytrace_payment_form').submit(function(){
		if ($('#paytrace_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
		}
	})
});