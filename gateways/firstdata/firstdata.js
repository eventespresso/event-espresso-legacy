jQuery(document).ready(function($) {
	$('#firstdata_payment_form').validate();
	$('#firstdata_payment_form').submit(function(){
		if ($('#firstdata_payment_form').valid()){
			$('#processing').html('<img class="ee-ajax-loader-img" src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="firstdata_submit"]').attr('disabled', 'disabled');
		}
	});
});