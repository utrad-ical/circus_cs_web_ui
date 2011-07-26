/**
 * circus-common.js - contains codes widely used throughout the CIRCUS system.
 *
 * Currently this file contains scripts for dynamic layout elements.
 */


//Initialize
$(function(){
	// Enable rollover actions for elements with these classes
	$('.jq-btn').rolloverBtn();
	// Process elements with 'form-btn' and 'radio-to-button' classes
	$('body').autoStylize();
	// Adjust container height
	var resized = function() {
		$('#container').height($(document).height() - 10);
	};
	$(window).bind('resize', resized);
	resized();

	// Calendar in the menu
	var month = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
		'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
	$('#menu .month').text(month[(new Date()).getMonth()]);
	$('#menu .day').text((new Date()).getDate());
});

$.fn.autoStylize = function() {
	return this.each(function() {
		var _this = $(this);
		_this.find('.radio-to-button').andSelf().filter('.radio-to-button').radioToButton({
			normal: 'radio-to-button-normal',
			hover: 'radio-to-button-hover',
			checked: 'radio-to-button-checked',
			disabled: 'radio-to-button-disabled'
		});
		_this.find('.form-btn').andSelf().filter('.form-btn').hoverStyle({
			normal: 'form-btn-normal',
			hover: 'form-btn-hover',
			disabled: 'form-btn-disabled'
		});
	});
};

// 'enable' method enables given input element by removing 'disabled' attribute.
// It additionally triggers 'flush' event for custom UI buttons.
// If boolean value is passed as the first argument, it can disable/enable
// elements according to that value.
$.fn.enable = function() {
	var flag = true;
	if (arguments.length >= 1)
		flag = arguments[0];
	if (flag)
		return $(this).filter(':disabled')
			.removeAttr('disabled').trigger('flush').end();
	else
		return $(this).disable();
};

// 'disable' method disables given input elements by adding 'disabled' attribute.
// It also triggers 'flush' event.
$.fn.disable = function() {
	return $(this).filter(':not(:disabled)')
		.attr('disabled', 'disabled').trigger('flush').end();
};

// 'rollOverBtn' applies mouseover/mouseout handlers to highlight the target
// element by moving the background position.
$.fn.rolloverBtn = function(_switch) {
	return this.each(function() {
		var _this = $(this);
		if (!_this.data('rolloverBtnInit'))
		{
			_this
				.hover(
					function() {
						_this.css('background-position', '0 ' + _this.height() + 'px');
					},
					function(){ _this.css('background-position', '0 0'); }
				)
				.data('rolloverBtnInit', true);
		}
	});
};

// 'hoverStyle' applies CSS classes for 'disabled', 'hover' and 'normal' status.
$.fn.hoverStyle = function(styles) {
	return this.each(function() {
		var _this = $(this);
		var setStyle = function(hover)
		{
			$.each(styles, function(k, v) { _this.removeClass(v); });
			if (_this.is(':disabled')) {
				_this.addClass(styles['disabled']);
			} else if (hover) {
				_this.addClass(styles['hover']);
			} else {
				_this.addClass(styles['normal']);
			}
		};
		var flush = function() { setStyle(false); };
		_this
			.hover(
				function() { setStyle(true) },
				function() { setStyle(false) }
			)
			.bind('flush', function() { flush(); });
		flush();
	});
};

// 'radioToButton' can make radio buttons have appearance of normal buttons.
$.fn.radioToButton = function(styles) {
	return this.each(function() {
		var _radio = $(this);
		if (!_radio.is('input[type=radio]')) return;
		if (_radio.data('radioToButtonInit')) return;
		if (_radio.attr('label').length == 0) return;
		var setStyle = function(hover)
		{
			$.each(styles, function(k, v) { btn.removeClass(v); });
			if (_radio.attr('checked'))
			{
				btn.addClass(styles['checked']);
			} else if (_radio.is(':disabled')) {
				btn.addClass(styles['disabled']);
			} else if (hover) {
				btn.addClass(styles['hover']);
			} else {
				btn.addClass(styles['normal']);
			}
		};
		var flush = function() { setStyle(false); };
		var btn = $('<a>')
			.addClass(_radio.attr('class'))
			.text(_radio.attr('label'))
			.attr('title', _radio.attr('title') ? _radio.attr('title') : '')
			.hover(
				function() { setStyle(true); },
				function() { setStyle(false); }
			)
			.click(function(){
				if (_radio.is(':disabled')) return;
				_radio.click();
				var container = $(_radio).closest('form');
				if (container.length == 0) container = $('body');
				$(':radio[name='+_radio.attr('name')+']', container).trigger('flush');
				return false;
			})
			.mousedown(function() { return false; })
			.insertAfter(_radio);
		_radio
			.bind('flush', function() { flush(); })
			.hide(0)
			.data('radioToButtonInit', true);
		flush();
	});
};
