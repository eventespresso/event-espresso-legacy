jQuery(document).ready(function($) {

	$('#beanstream_payment_form').validate();

	$('#beanstream_payment_form').submit(function(){
		if ($('#beanstream_payment_form').valid()){
			$('#processing').html('<img class="ee-ajax-loader-img" src="' + EEGlobals.plugin_url + 'images/ajax-loader.gif">');
			//$(':input[name="beanstream_submit"]').attr('disabled', 'disabled');
		}
	})
});