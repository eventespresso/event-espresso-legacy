jQuery(document).ready(function($) {
	$('#espresso-data-collect-optin-container').on('click', '.data-optin-button', function() {
		var selection = $(this).val();
		var nonce = $('#data-optin-nonce').text();
		
		$.post(ajaxurl, {
			action: 'espresso_data_optin',
			nonce: nonce,
			selection: selection
		}, function(response) {
			return;
		});

		$('#espresso-data-collect-optin-container').slideUp();
	});


	$('#ee4-admin-notice-container').on('click', '.ee4-admin-notice-button', function() {
		var selection = $(this).val();
		var nonce = $('#ee4-admin-notice-nonce').text();
		
		$.post(ajaxurl, {
			action: 'espresso_ee4_admin_notice',
			nonce: nonce,
			selection: selection
		}, function(response) {
			return;
		});

		$('#espresso-ee4-admin-notice-container').slideUp();
	});
});
