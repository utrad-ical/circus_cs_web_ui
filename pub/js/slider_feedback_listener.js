/**
 * Feedback Listener using horizontal sliders.
 */

var circus = circus || {};

circus.evalListener = (function() {
	var params = {};
	var global = {
		setup: function ()
		{
			params = circus.cadresult.presentation.feedbackListener;
			$('.evaluation-slider-container').each(function() {
				var hint = $('<div class="evaluation-slider-value">').hide();
				var slider = $('.evaluation-slider', this);
				slider.slider({
					'min': params.min,
					'max': params.max,
					'value': params.initial,
					'step': params.step,
					'slide': function(event, ui) { global._onSlide(hint, slider, ui); },
					'stop': function() { global._onStop(hint); }
				}).after(hint);
				if (params.showValue == 'always')
					global._showValue(hint, slider, params.initial);
				if (params.showValue == 'active')
					$(this).addClass('hoverHint');
			});
		},
		set: function (target, value)
		{
			$('.evaluation-slider', target).slider('value', Number(value));
			$('.evaluation-slider-value', target).text(Number(value));
		},
		get: function (target)
		{
			return $('.evaluation-slider', target).slider('value');
		},
		validate: function (target)
		{
			return { register_ok: true };
		},
		disable: function (target)
		{
			$('.evaluation-slider', target).slider('disable');
		},
		enable: function (target)
		{
			$('.evaluation-slider', target).slider('enable');
		},
		_showValue: function(hint, slider, value)
		{
			hint.show().text(value).position({
				my: 'center top',
				at: 'center bottom',
				of: slider,
				offset: '0 5'
			});
		},
		_onSlide: function(hint, slider, ui)
		{
			if (params.showValue != 'never')
				global._showValue(hint, slider, ui.value);
		},
		_onStop: function(hint)
		{
			if (params.showValue == 'active')
				hint.hide();
		}

	};
	return global;
})();