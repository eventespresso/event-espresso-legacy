jQuery(document).ready(function($) {
	//Auto complete
	$("input#ee_autocomplete").autocomplete({
		source: ee_autocomplete_params,
		success: function(data) {
			response( $.map(data, function(item) {
				return {
					url: item.url,
					value: item.name
				}
			}));
		},
		select: function( event, ui ) {
			window.location.href = ui.item.url;
		},
		minLength: 2
	});
	//End auto complete
});