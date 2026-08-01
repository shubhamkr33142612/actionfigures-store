(function () {
	'use strict';

	var wraps = document.querySelectorAll('.cdc-personalization');

	wraps.forEach(function (wrap) {
		var input = wrap.querySelector('input[name="cdc_personalized_text"]');
		var count = wrap.querySelector('.cdc-count');
		if (!input) {
			return;
		}

		var max = parseInt(input.getAttribute('maxlength') || '0', 10);

		var update = function () {
			if (count && max) {
				count.textContent = input.value.length + '/' + max;
			}
		};
		input.addEventListener('input', update);
		update();

		if (wrap.getAttribute('data-required') === 'yes') {
			var form = input.closest('form.cart') || input.closest('form');
			if (form) {
				form.addEventListener('submit', function (e) {
					if (input.value.trim() === '') {
						e.preventDefault();
						input.focus();
						input.setCustomValidity('Please enter personalized text.');
						input.reportValidity();
					} else {
						input.setCustomValidity('');
					}
				});
			}
		}
	});
})();
