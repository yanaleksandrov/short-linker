(function($) {
"use strict";
	function isValidUrl(url) {
		try {
			new URL(url);
			return true;
		} catch (error) {
			return false;
		}
	}

	$(document)
		.on( 'change', '.short-linker-url', function() {
			let redirectUrl = $(this).val();
			if (!isValidUrl(redirectUrl)) {
				$(this).val('');
			}
		});
})(jQuery);
