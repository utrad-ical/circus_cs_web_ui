/**
 * jQuery UI custom widget: Stackable image viewer.
 *
 * Author:
 *   Soichiro Miki
 * Depends:
 *   jquery-ui-1.7.x.js with Slider widget (or newer)
 *   layout.css
 */

$.widget('ui.imageviewer', {
	_init: function()
	{
		this._draw();
	},

	_draw: function ()
	{
		var root = this.element;
		root.addClass('ui-imageviewer')
		var imgdiv = $('<div class="ui-imageviewer-image">')
			.css({ width: this.options.width, height: this.options.height })
			.appendTo(root);
		this._scale = this.options.width / this.options.imageWidth;
		var height = this.options.imageHeight * this._scale;
		$('<img>')
			.css({ width: this.options.width, height: height })
			.appendTo(imgdiv);
		if (this.options.useSlider)
		{
			var table = $(this._sliderHtml()).appendTo(root);
			var self = this;
			table.find('td').eq(0).find('button').click(function () { self.step(-1); });
			table.find('td').eq(2).find('button').click(function () { self.step(1); });
			var sliderdiv = $('<div class="ui-imageviewer-slider">')
				.slider({
					min: this.options.min,
					max: this.options.max,
					step: 1,
					value: this.options.index,
					slide: function(event, ui) {
						self._label(ui.value);
					},
					change: function(event, ui) {
						self.changeImage(ui.value);
					}
				});
			table.find('td').eq(1).append(sliderdiv);
		}
		if (this.options.useLocationText)
		{
			$('<div class="ui-imageviewer-location" />').appendTo(root);
		}
		this.changeImage(this.options.index);
	},

	_drawMarkers: function()
	{
		var imgdiv = $('div.ui-imageviewer-image', this.element);
		var index = this.options.index;
		imgdiv.find('div.ui-imageviewer-dot, div.ui-imageviewer-dotlabel').remove();
		if (!this.options.showMarkers)
			return;
		var max = this.options.markers.length;
		for (var i = 0; i < max; i++)
		{
			var mark = this.options.markers[i];
			var x = mark.location_x * this._scale;
			var y = mark.location_y * this._scale;
			if (mark.location_z == index)
			{
				$('<div class="ui-imageviewer-dot" />')
					.css({left: x - 1, top:  y - 1})
					.appendTo(imgdiv);
				$('<div class="ui-imageviewer-dotlabel" />')
					.text(mark.display_id)
					.css({left: x + 3, top: y - 1})
					.appendTo(imgdiv);
			}
		}
	},

	_sliderHtml: function()
	{
		return '<table class="ui-imageviewer-navi"><tr><td class="updown"><button>-</button></td><td /><td class="updown"><button>+</button></td></tr></table>';
	},

	step: function(delta)
	{
		this.changeImage(this.options.index + delta);
	},

	_label: function(index) {
		$('.ui-imageviewer-location', this.element).text(this.options.locationLabel + index);
	},

	changeImage: function(index)
	{
		this.options.index = Math.min(Math.max(index, this.options.min), this.options.max);
		$('.ui-imageviewer-slider').slider('option', 'value', index);
		var param = {
				studyInstanceUID: this.options.study_instance_uid,
				seriesInstanceUID: this.options.series_instance_uid,
				imgNum: index,
		};
		var self = this;
		var toTopDir = this.options.toTopDir;
		$.post(
			toTopDir + 'jump_image.php',
			param,
			function (data) {
				if (data.errorMessage)
				{
					console.log(data.errorMessage);
				}
				else if(data.imgFname != "")
				{
					$('img', self.element).attr('src', toTopDir + data.imgFname);
					self._label(data.sliceNumber);
				}
			},
			'json'
		);
		if (this.options.markers instanceof Array) this._drawMarkers();
		$(this.element).trigger('imagechange');
	},

});

$.extend($.ui.imageviewer, {
	defaults: {
		min: 1,
		max: 100,
		windowLevel: 10,
		windowWidth: 100,
		index: 1,
		width: 300,
		imageWidth: 512,
		imageHeight: 512,
		useSlider: true,
		useLocationText: true,
		locationLabel: 'Image Number: ',
		toTopDir: '',
		showMarkers: true,
		markers: []
	}
});
