/**
 * Feedback Listener by text input.
 */
var evalListener = (function() {
	var global = {
		set: function (target, value)
		{
			$('.feedback-pane input[type=text]', target).val(value);
		},
		get: function (target)
		{
			return $('.feedback-pane input[type=text]', target).val();
		},
		disable: function (target)
		{
			$('.feedback-pane input[type=text]', target)
				.attr('disabled', 'disabled');
		},
		enable: function (target)
		{
			$('.feedback-pane input[type=text]', target)
				.attr('disabled', '');
		}
	};
	return global;
})();