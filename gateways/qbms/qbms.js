jQuery(document).ready(function($) {
	$('#qbms_payment_form').validate();
	$('#qbms_payment_form').submit(function(){
		if ($('#qbms_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
		}
	})
});