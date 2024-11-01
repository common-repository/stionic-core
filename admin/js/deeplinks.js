jQuery(document).ready(function ($) {
	var url_scheme = $('input[name="url_scheme"]');
	var button_deeplinks = $('input[name="button_deeplinks"]');
	var auto_open = $('input[name="auto_open"]');
	if (url_scheme) {
		function url_scheme_update() {
			if (url_scheme.val()) button_deeplinks.removeAttr('disabled');
			else {
				button_deeplinks.attr('checked', false);
				button_deeplinks.attr('disabled', true);
				auto_open.attr('checked', false);
				auto_open.attr('disabled', true);
			}
		}
		url_scheme_update();
		url_scheme.keyup(url_scheme_update);
	}
	if (button_deeplinks[0]) {
		function button_deeplinks_update() {
			if (button_deeplinks[0].checked) auto_open.removeAttr('disabled');
			else {
				auto_open.attr('checked', false);
				auto_open.attr('disabled', true);
			}
		}
		button_deeplinks_update();
		button_deeplinks.change(button_deeplinks_update);
	}
});