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

			if (circus.feedback.feedbackMode == 'consensual')
			{
				$('.evaluation-text').each(function() {
					var field = $(this);
					var display_id = field.closest('.result-block').data('displayid');
					var tip = $('<div>');
					$.each(circus.feedback.personalOpinions, function() {
						var txt = this.entered_by + ': ' + this.blockFeedback[display_id];
						tip.append(txt).append('<br>');
					});
					field.tooltip({
						content: tip,
						width: field.width()
					});
				});
			}
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