jQuery(document).ready(function($) {
	$('#eway_rapid3_payment_form').validate();
	$('#eway_rapid3_payment_form').submit(function(){
		if ($('#eway_rapid3_payment_form').valid()){
			$('#processing').html('<img class="ee-ajax-loader-img" src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="eway_rapid3_submit"]').attr('disabled', 'disabled');
		}
	})
});