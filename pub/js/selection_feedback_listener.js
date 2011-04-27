/**
 * Feedback Listener for selection.
 */
var evalListener = (function() {
	var global = {
		setup: function () {
			$('.feedback-area a.radio-to-button').click(function () {
				CIRCUSFeedback.change();
			});
		},
		set: function (target, value)
		{
			$('.feedback-area input[type=radio]', target).each(function() {
				if ($(this).val() == value) {
					$(this).click().trigger('flush');
				}
			});
		},
		get: function (target)
		{
			return $('.feedback-area input[type=radio]:checked', target).val();
		},
		validate: function (target)
		{
			return $('.feedback-area input[type=radio]:checked', target).length > 0;
		},
		disable: function (target)
		{
			$('.feedback-area input[type=radio]', target)
				.attr('disabled', 'disabled')
				.trigger('flush');
		},
		enable: function (target)
		{
			$('.feedback-area input[type=radio]', target)
				.attr('disabled', '')
				.trigger('flush');
		}
	};
	return global;
})();