/**
 * Feedback Listener by text input.
 */

var circus = circus || {};

circus.evalListener = (function() {
	var global = {
		setup: function ()
		{
			$('.evaluation-text').change(function() {
				circus.feedback.change();
			})
			.keyup(function() {
				circus.feedback.change();
			});
		},
		set: function (target, value)
		{
			$('.evaluation-text', target).val(value.text);
		},
		get: function (target)
		{
			return {text: $('.evaluation-text', target).val()};
		},
		validate: function (target)
		{
			if (!circus.cadresult.presentation.feedbackListener.required)
				return { register_ok: true };
			if ($('.evaluation-text', target).val().length > 0)
			{
				return { register_ok: true };
			}
			else
			{
				return { register_ok: false, message: 'Incomplete' };
			}
		},
		disable: function (target)
		{
			$('.evaluation-text', target).disable();
		},
		enable: function (target)
		{
			$('.evaluation-text', target).enable();
		}
	};
	return global;
})();