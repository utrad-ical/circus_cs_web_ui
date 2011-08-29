/**
 * jQuery UI custom widget: data range.
 * Author:
 *   Soichiro Miki
 * Depends:
 *   jquery-ui-1.8.x.js with Datepicker widget (or newer)
 *   layout.css
 */


$.widget('ui.daterange', {
	options: {
		dash: ' &mdash; ',
		icon: 'images/calendar_view_month.png'
	},

	_types: [
		{label: 'all', from: '', to: '' },
		{label: 'today', from: 'today', to: 'today', stripe: true },
		{label: 'yesterday', from: 'yesterday', to: 'yesterday', stripe: true},
		{label: 'last 1 week', from: '1 week ago', to: 'today' },
		{label: 'last 1 month', from: '1 month ago', to: 'today'},
		{label: 'last 3 months', from: '3 months ago', to: 'today'},
		{label: 'last 6 months', from: '6 months ago', to: 'today'},
		{label: 'last 1 year', from: '-1year', to: 'today' },
		{label: 'custom...', custom: true, stripe: true }
	],

	_create: function()
	{
		var self = this;
		var root = this.element.empty().addClass('ui-daterange')

		var datePickerOpts = {
			buttonText: 'select',
			buttonImage: self.options.icon,
			buttonImageOnly: true,
			showOn: 'button',
			constrainInput: false,
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		};

		var refresh = function()
		{
			var selected = $('option:selected', kindSelect);
			var info = selected.data('info');
			from.val(info.from);
			to.val(info.to);
			if (info.custom)
			{
				$.each([from, to], function() {
					$(this).removeAttr('disabled')
					.datepicker('enable')
					.datepicker('option', {maxDate: '', minDate: ''});
				});
			}
			else
			{
				$.each([from, to], function() {
					$(this).attr('disabled', 'disabled').datepicker('disable');
				});
			}
		};

		var kindSelect = $('<select>')
		.addClass('ui-daterange-kind')
		.appendTo(root);
		kindSelect.change(refresh);
		for (var i = 0; i < self._types.length; i++)
		{
			var item = self._types[i];
			var opt = $('<option>')
			.data('info', item)
			.append(item.label)
			.appendTo(kindSelect);
			if (item.stripe)
				opt.addClass('ui-daterange-stripe')
		}

		var customField = $('<span>')
		.addClass('ui-daterange-custom')
		.appendTo(root);

		// 'FROM' field
		var from = $('<input type="text">')
		.addClass('ui-daterange-from')
		.appendTo(customField)
		.datepicker(datePickerOpts)
		.datepicker('option', 'onSelect', function(text, inst) {
			to.datepicker('option', 'minDate', from.val());
		});
		if (this.options.fromName)
			from.attr('name', this.options.fromName);

		// ' - '
		customField.append(self.options.dash);

		// 'TO' field
		var to = $('<input type="text">')
		.addClass('ui-daterange-to')
		.appendTo(customField)
		.datepicker(datePickerOpts)
		.datepicker('option', 'onSelect', function(text, inst) {
			from.datepicker('option', 'maxDate', to.val());
		});
		if (this.options.toName)
			to.attr('name', this.options.toName);

		refresh();
	},

	_setOptions: function(key, value, animated)
	{
		$.Widget.prototype._setOption.apply(this, arguments);
	}
});