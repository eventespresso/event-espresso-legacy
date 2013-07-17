jQuery(document).ready(function($) {
	$('#nab_payment_form').validate();
	$('#nab_payment_form').submit(function(){
		if ($('#nab_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
		}
	})
});