/**
 * Feedback Listener for selection.
 */

var circus = circus || {};

circus.evalListener = (function() {
	var global = {
		setup: function ()
		{
			$('.feedback-pane a.radio-to-button').click(function () {
				circus.feedback.change();
			});
			if (circus.feedback.feedbackMode == 'consensual')
			{
				var initData = circus.feedback.initdata.blockFeedback;
				$('.feedback-pane input:radio').each(function() {
					var radio = $(this);
					var val = radio.val();
					var block = radio.closest('.result-block');
					var display_id = block.data('displayid');
					var a = $('input:radio[value=' + val + '] + a', block);
					if (initData[display_id] && initData[display_id].opinions)
					{
						var opinions = initData[display_id].opinions[val];
						if (opinions instanceof Object)
						{
							a.text(a.text() + ' ' + opinions.length);
							a.attr('title', opinions.join(', '));
						}
					}
				});
			}
		},
		set: function (target, value)
		{
			if (!value) value = {};
			$('.feedback-pane input[type=radio]', target).each(function() {
				if ($(this).val() == value.selection) {
					$(this).click().trigger('flush');
				}
			});
		},
		get: function (target)
		{
			return {
				selection: $('.feedback-pane input[type=radio]:checked', target).val()
			};
		},
		validate: function (target)
		{
			if ($('.feedback-pane input[type=radio]:checked', target).length > 0) {
				return { register_ok: true };
			} else {
				return {
					register_ok: false,
					message: 'Imcomplete'
				};
			}
		},
		disable: function (target)
		{
			$('.feedback-pane input[type=radio]', target)
				.attr('disabled', 'disabled')
				.trigger('flush');
		},
		enable: function (target)
		{
			$('.feedback-pane input[type=radio]', target)
				.attr('disabled', '')
				.trigger('flush');
		}
	};
	return global;
})();