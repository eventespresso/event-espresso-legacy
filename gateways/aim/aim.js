jQuery(document).ready(function($) {
	$('#aim_payment_form').validate();
	$('#aim_payment_form').submit(function(){
		if ($('#aim_payment_form').valid()){
			$('#processing').html('<img src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
		}
	})
});