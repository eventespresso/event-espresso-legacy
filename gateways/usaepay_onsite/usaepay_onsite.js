jQuery(document).ready(function($) {
	$('#usaepay_onsite_payment_form').validate();
	$('#usaepay_onsite_payment_form').submit(function(){
		if ($('#usaepay_onsite_payment_form').valid()){
			$('#processing').html('<img class="ee-ajax-loader-img" src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="usaepay_onsite_submit"]').attr('disabled', 'disabled');
		}
	})
});