/**
 * Feedback Listener by text input.
 */
var evalListener = (function() {
	var global = {
		setup: function ()
		{
			$('.evaluation-text').change(function() {
				CircusFeedback.change();
			})
			.keyup(function() {
				CircusFeedback.change();
			});
		},
		set: function (target, value)
		{
			$('.evaluation-text', target).val(value);
		},
		get: function (target)
		{
			return $('.evaluation-text', target).val();
		},
		validate: function (target)
		{
			return $('.evaluation-text', target).val().length > 0;
		},
		disable: function (target)
		{
			$('.evaluationtext', target)
				.attr('disabled', 'disabled');
		},
		enable: function (target)
		{
			$('.evaluation-text', target)
				.attr('disabled', '');
		}
	};
	return global;
})();