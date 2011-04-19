/**
 * Evaluation Listener by text input.
 */
var evalListener = (function() {
	var global = {
		set: function (target, value)
		{
			$('.evaluation-area input[type=text]', target).val(value);
		},
		get: function (target)
		{
			return $('.evaluation-area input[type=text]', target).val();
		},
		disable: function (target)
		{
			$('.evaluation-area input[type=text]', target)
				.attr('disabled', 'disabled');
		},
		enable: function (target)
		{
			$('.evaluation-area input[type=text]', target)
				.attr('disabled', '');
		}
	};
	return global;
})();