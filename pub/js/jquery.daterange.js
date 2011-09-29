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
		icon: 'images/calendar_view_month.png',
		dateFormat: 'yy-mm-dd',
		fromDate: null,
		toDate: null,
		kind: null
	},

	_types: [
		{label: 'all', from: '', to: '' },
		{label: 'today', from: '0', to: '0', stripe: true },
		{label: 'yesterday', from: '-1d', to: '-1d', stripe: true},
		{label: 'last 1 week', from: '-7d', to: '0' },
		{label: 'last 1 month', from: '-1m', to: '0'},
		{label: 'last 3 months', from: '-3m', to: '0'},
		{label: 'last 6 months', from: '-6m', to: '0'},
		{label: 'last 1 year', from: '-12m', to: '0' },
		{label: 'custom...', custom: true, stripe: true }
	],

	_create: function()
	{
		var self = this;
		var root = this.element.empty().addClass('ui-daterange')

		var kindChanged = function()
		{
			var selected = $('option:selected', kindSelect);
			var info = selected.data('info');
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
				$.each([[from, info.from], [to, info.to]], function() {
					var target = $(this[0]);
					var value = this[1];
					if (value)
					{
						target.val(self._format(self._calcDateDiff(value)));
					}
					else
						target.val('');
					target.attr('disabled', 'disabled').datepicker('disable');
				});
			}
		};

		var kindSelect = $('<select>')
		.addClass('ui-daterange-kind')
		.appendTo(root);
		kindSelect.change(function() {
			kindChanged();
			self._updateInternalValue();
		});
		for (var i = 0; i < self._types.length; i++)
		{
			var item = self._types[i];
			var opt = $('<option>')
			.data('info', item)
			.attr('value', item.label)
			.append(item.label)
			.appendTo(kindSelect);
			if (item.stripe)
				opt.addClass('ui-daterange-stripe')
		}
		self.kindSelect = kindSelect;

		var customField = $('<span>')
		.addClass('ui-daterange-custom')
		.appendTo(root);

		// 'FROM' field
		var from = self._createDateInput('ui-daterange-from', this.options.fromName);
		from.appendTo(customField);
		from.datepicker('option', 'onSelect', function(text, inst) {
			to.datepicker('option', 'minDate', from.val());
			self._updateInternalValue();
		}).val(this.options.fromDate);
		self.from = from;

		// ' - '
		customField.append(self.options.dash);

		// 'TO' field
		var to = self._createDateInput('ui-daterange-to', this.options.toName);
		to.appendTo(customField);
		to.datepicker('option', 'onSelect', function(text, inst) {
			from.datepicker('option', 'maxDate', to.val());
			self._updateInternalValue();
		}).val(this.options.toDate);
		self.to = to;

		kindSelect.val(this.options.kind);
		kindChanged();
		self._updateInternalValue();
	},

	_updateInternalValue: function()
	{
		var self = this;
		self.options.fromDate = self.from.val();
		self.options.toDate = self.to.val();
		self.options.kind = self.kindSelect.val();
		// console.log('value updated', self.options.fromDate, self.options.toDate, self.options.kind);
	},

	_commitFromDate: function()
	{
		this.from.val(this.options.fromDate);
	},

	_commitToDate: function()
	{
		this.to.val(this.options.toDate);
	},

	_commitKind: function()
	{
		this.kindSelect.val(this.options.kind);
		this.options.kind = this.kindSelect.val(); // invalid assignment does not take effect
		this.kindSelect.change();
	},

	_createDateInput: function(className, name)
	{
		var self = this;
		var datePickerOpts = {
			buttonText: 'select',
			buttonImage: this.options.icon,
			buttonImageOnly: true,
			showOn: 'button',
			constrainInput: false,
			dateFormat: this.options.dateFormat,
			changeMonth: true,
			changeYear: true
		};

		var result = $('<input type="text">');
		result
			.addClass(className)
			.datepicker(datePickerOpts)
			.change(function() { self._updateInternalValue(); })
			.keyup(function() { self._updateInternalValue(); });
		if (name)
			result.attr('name', name);
		return result;
	},

	_calcDateDiff: function(diff, from)
	{
		var m = diff.match(/^(\-?)(\d+)([dwmy]?)$/);
		if (!m) return null;

		var dt = new Date();
		if (from instanceof Date) dt = from;
		if (m[3] == 'd' || m[3] == 'w' || !m[3])
		{
			var days = m[3] == 'w' ? m[2] * 7 : m[2];
			if (m[1]) days = -days;
			dt.setTime(dt.getTime() + 86400000 * days);
			return dt;
		}
		else
		{
			var months = m[3] == 'y' ? m[2] * 12 : m[2];
			if (m[1]) months = -months;
			var year = dt.getFullYear();
			var month = dt.getMonth() + parseInt(months);
			var date = dt.getDate();
			// round date in such a way that '1 month before 2011/Mar/30' is
			// 2011/Feb/28
			var lastDayOfTargetMonth = new Date(year, month + 1, 0).getDate();
			if (date > lastDayOfTargetMonth) date = lastDayOfTargetMonth;
			return new Date(year, month, date);
		}
	},

	_format: function(date)
	{
		return $.datepicker.formatDate(this.options.dateFormat, date);
	},

	_setOption: function(key, value)
	{
		$.Widget.prototype._setOption.apply(this, arguments);
		switch (key)
		{
			case 'fromDate':
				this._commitFromDate();
				break;
			case 'toDate':
				this._commitToDate();
				break;
			case 'kind':
				this._commitKind();
				break;
			case 'icon':
			case 'dash':
			case 'dateFormat':
				this._create();
				break;
		}
	}
});