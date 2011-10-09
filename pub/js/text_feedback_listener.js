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
			var txt = value && value instanceof Object ? value.text : '';
			$('.evaluation-text', target).val(txt);
		},
		get: function (target)
		{
			return {text: $('.evaluation-text', target).val()};
		},
		validate: function (target)
		{
			var params = circus.cadresult.presentation.feedbackListener;
			var ok = true;
			var input = $('.evaluation-text', target);
			var val = input.val();
			if (params.regex && !val.match(params.regex))
				ok = false;
			if (params.minLength > 0 && val.length < params.minLength)
				ok = false;
			if (params.maxLength > 0 && val.length > params.maxLength)
				ok = false;
			if (!ok)
			{
				input.addClass('evaluation-error');
				return { register_ok: false, message: 'Incomplete' };
			}
			else
			{
				input.removeClass('evaluation-error');
				return { register_ok: true };
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