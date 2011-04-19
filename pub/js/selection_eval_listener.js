/**
 * Evaluation Listener for selection.
 */
var evalListener = (function() {
	var global = {
		setup: function () {
			$('.evaluation-area a.radio-to-button').click(function () {
				CIRCUSFeedback.change();
			});
		},
		set: function (target, value)
		{
			$('.evaluation-area input[type=radio]', target).each(function() {
				if ($(this).val() == value) {
					$(this).click().trigger('flush');
				}
			});
		},
		get: function (target)
		{
			return $('.evaluation-area input[type=radio]:checked', target).val();
		},
		validate: function (target)
		{
			return $('.evaluation-area input[type=radio]:checked', target).length > 0;
		},
		disable: function (target)
		{
			$('.evaluation-area input[type=radio]', target)
				.attr('disabled', 'disabled')
				.trigger('flush');
		},
		enable: function (target)
		{
			$('.evaluation-area input[type=radio]', target)
				.attr('disabled', '')
				.trigger('flush');
		}
	};
	return global;
})();