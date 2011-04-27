/**
 * Feedback Listener by text input.
 */
var evalListener = (function() {
	var global = {
		set: function (target, value)
		{
			$('.feedback-area input[type=text]', target).val(value);
		},
		get: function (target)
		{
			return $('.feedback-area input[type=text]', target).val();
		},
		disable: function (target)
		{
			$('.feedback-area input[type=text]', target)
				.attr('disabled', 'disabled');
		},
		enable: function (target)
		{
			$('.feedback-area input[type=text]', target)
				.attr('disabled', '');
		}
	};
	return global;
})();