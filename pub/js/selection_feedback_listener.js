/**
 * Feedback Listener for selection.
 */

var circus = circus || {};

circus.evalListener = (function() {
	var global = {
		setup: function ()
		{
			$('.feedback-pane a.radio-to-button').click(function (event) {
				var btn = $(event.currentTarget);
				var display_id = btn.closest('.result-block').data('displayid');
				var label = $.trim(btn.clone().find('*').remove().end().text());
				$(window).trigger('actionlog', {
					action: 'classify',
					options: 'Candidate ' + display_id + ':' + label
				});
				circus.feedback.change();
			});
			if (circus.feedback.feedbackMode == 'consensual')
			{
				var map = {};
				$.each(circus.cadresult.presentation.feedbackListener.personal, function() {
					if ('consensualMapsTo' in this)
						map[this.value] = this.consensualMapsTo
					else
						map[this.value] = this.value;
				});
				var initData = circus.feedback.initdata.blockFeedback;
				$('.feedback-pane input:radio').each(function() {
					var radio = $(this);
					var val = radio.val();
					var block = radio.closest('.result-block');
					var display_id = block.data('displayid');
					var a = radio.next('a');
					if (display_id in initData)
					{
						var opinions = [];
						$.each(circus.feedback.personalOpinions, function() {
							if (map[this.blockFeedback[display_id]] == val)
								opinions.push(this.entered_by);
						});
						if (opinions.length > 0)
						{
							$('<span class="opinions-count">').text(opinions.length).appendTo(a);
							var txt = opinions.join(', ');
							a.tooltip(txt);
						}
					}
				});
			}
		},
		set: function (target, value)
		{
			$('.feedback-pane input[type=radio]', target).each(function() {
				if ($(this).val() == value) {
					$(this).click().trigger('flush');
				}
			});
		},
		get: function (target)
		{
			return $('.feedback-pane input[type=radio]:checked', target).val()
		},
		validate: function (target)
		{
			if ($('.feedback-pane input[type=radio]:checked', target).length > 0) {
				return { register_ok: true };
			} else {
				return {
					register_ok: false,
					message: 'Incomplete'
				};
			}
		},
		disable: function (target)
		{
			$('.feedback-pane input[type=radio]', target).disable();
		},
		enable: function (target)
		{
			$('.feedback-pane input[type=radio]', target).enable();
		}
	};
	return global;
})();