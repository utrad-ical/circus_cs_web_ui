/**
 * jQuery UI custom widget: Stackable image viewer.
 *
 * Author:
 *   Soichiro Miki
 * Depends:
 *   jquery-ui-1.7.x.js with Slider widget (or newer)
 *   jquery.mousewheel.(min).js (optional)
 *   layout.css
 */

$.widget('ui.imageviewer', {
	_init: function()
	{
		var self = this;
		if (this.options.source instanceof Object)
		{
			this.options.source.onLoad = function(data) {
				self._imageDeterminedHandler(data);
			}
			this.options.source.onError = function(message) {
				self._errorMode(message);
			}
		}
		else
		{
			self._errorMode('Image source not set correctly.');
		}
		this._initialized = false;
		delete this._imageWidth;
		delete this._imageHeight;
		delete this._scale;
		delete this._cache;
		delete this._waiting;
		delete this._error;
		this._draw();
		this._initialized = true;
	},

	_draw: function ()
	{
		var root = this.element.empty();
		var self = this;
		root.addClass('ui-imageviewer');

		var stage = $('<div class="ui-imageviewer-stage">')
			.appendTo(root);

		var img = $('<img class="ui-imageviewer-image">').appendTo(stage);
		if ('_imageWidth' in this)
			this._adjustImageSize();

		if (this._error)
		{
			var errdiv = $('<div class="ui-imageviewer-error">')
				.append('Error while loading images.')
				.appendTo(stage);
			if (this._error)
				errdiv.append('<br>').append(this._error);
		}

		$('<div class="ui-imageviewer-loading">').appendTo(stage).hide(0);
		img.mousedown(function(){return false;}); // prevent selection/drag
		if (this.options.useWheel && img.mousewheel)
		{
			stage.mousewheel(function (event, delta) {
				self.step(delta > 0 ? -1 : 1);
				return false; // supress browser scroll
			})
		}

		if (this.options.useSlider)
		{
			var table = $(this._sliderHtml()).appendTo(root);
			table.find('td').eq(0).find('button').click(function () { self.step(-1); });
			table.find('td').eq(2).find('button').click(function () { self.step(1); });
			var sliderdiv = $('<div class="ui-imageviewer-slider">')
				.slider({
					min: this.options.min,
					max: this.options.max,
					step: 1,
					value: this.options.index,
					slide: function(event, ui) {
						if (self.options.useLocationText)
							self._label(ui.value);
						if (self.options.sliderHotTrack)
							self.changeImage(ui.value);
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
		if (this.options.grayscalePresets instanceof Array &&
			this.options.grayscalePresets.length > 0)
		{
			var pr = $('<div class="ui-imageviewer-grayscale-preset">')
			pr.appendTo(root).append(this.options.grayscaleLabel);
			var select = $('<select>').appendTo(pr);
			var max = this.options.grayscalePresets.length;
			var self = this;
			for (var i = 0; i < max; i++)
			{
				var preset = this.options.grayscalePresets[i];
				$('<option>').text(preset.label)
					.data('wl', preset.wl).data('ww', preset.ww)
					.appendTo(select);
			}
			select.change(function (event) {
				var sel = $('option:selected', select);
				self.changeWindow(sel.data('wl'), sel.data('ww'));
			});
		}
		this.changeImage(this.options.index);
		this._cursor = 'auto';

		if (this.options.role == 'locator')
		{
			img.click(function(e) {
				if (!e.offsetX){ e.offsetX = e.pageX - $(e.target).offset().left; }
				if (!e.offsetY){ e.offsetY = e.pageY - $(e.target).offset().top; }
				self._locate(e.offsetX, e.offsetY);
				return false; // prevent image selection on dblclick
			});
			this._cursor = 'crosshair';
		}
		img.css('cursor', this._cursor);
	},

	_adjustImageSize: function()
	{
		var img = $('.ui-imageviewer-image', this.element);
		var stage = $('.ui-imageviewer-stage', this.element);
		var crop = this.options.cropRect;
		if (!(crop instanceof Object))
			crop = { x: 0, y: 0, width: this._imageWidth, height: this._imageHeight };
		this._cropRect = crop; // save

		var max = this.options.maxWidth;
		var w = this.options.width;
		if (parseFloat(w) > 0)
			w = max < w ? max : w;
		else
			w = max < crop.width ? max : crop.width;
		this._scale = w / crop.width;

		stage.width(w).height(crop.height * this._scale);
		img.css('left', -crop.x * this._scale).css('top', -crop.y * this._scale)
			.width(this._imageWidth * this._scale)
			.height(this._imageHeight * this._scale);
	},

	_locate: function(x, y)
	{
		var newitem = {
			location_x: parseInt(x / this._scale + 0.5),
			location_y: parseInt(y / this._scale + 0.5),
			location_z: this.options.index,
		};
		// The handler for 'locating' event can modify the new item.
		var event = $.Event('locating');
		event.newItem = newitem;
		this.element.trigger(event);
		if (!event.isDefaultPrevented()) {
			this.options.markers.push(newitem);
			var event = $.Event('locate');
			event.newItem = newitem;
			this.element.trigger(event);
		}
		this._drawMarkers();
	},

	_drawMarkers: function()
	{
		if (!(this.options.markers instanceof Array))
			return;
		var stage = $('div.ui-imageviewer-stage', this.element);
		var index = this.options.index;
		stage.find('div.ui-imageviewer-dot, div.ui-imageviewer-dotlabel').remove();
		if (!this.options.showMarkers || !this._cropRect)
			return;
		var max = this.options.markers.length;
		for (var i = 0; i < max; i++)
		{
			var mark = this.options.markers[i];
			var x = (mark.location_x - this._cropRect.x) * this._scale;
			var y = (mark.location_y - this._cropRect.y) * this._scale;
			if (mark.location_z != index)
				continue;
			switch (this.options.markerStyle)
			{
				case 'circle':
					break;
				case 'dot':
				default:
					$('<div class="ui-imageviewer-dot" />')
						.css({left: x - 1, top:  y - 1})
						.appendTo(stage);
					$('<div class="ui-imageviewer-dotlabel" />')
						.text(mark.display_id || i+1)
						.css({left: x + 3, top: y - 1})
						.appendTo(stage);
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

	_errorMode: function(message)
	{
		this._waiting = null;
		this._error = message;
		this._draw();
	},

	_imageLoadHandler: function(data, image)
	{
		var w = this._waiting;
		if (w && w.index == data.sliceNumber && w.wl == data.windowLevel && w.ww == data.windowWidth)
		{
			this.options.sliceLocation = data.sliceLocation,
			this._label(data.sliceNumber);
			this._waiting = null;
			var img = $('.ui-imageviewer-image', this.element);
			if (!('_imageWidth' in this))
			{
				this._imageWidth = image.width;
				this._imageHeight = image.height;
				this._adjustImageSize();
			}

			img.attr('src', image.src);

			this._clearTimeout();
			this._drawMarkers();
			this.element.trigger('imagechange');
			img.css('cursor', this._cursor);
			$('.ui-imageviewer-loading', this.element).hide(0);
		}
	},

	_imageDeterminedHandler: function(data)
	{
		var img = new Image(); // just starts preloading the image
		var self = this;
		img.onload = function (event) { self._imageLoadHandler(data, img); };
		img.src = data.fileName;
	},

	preload: function()
	{
		for (var i = this.options.min; i <= this.options.max; i++)
		{
			this._query(i);
		}
	},

	changeImage: function(index)
	{
		this._internalChangeImage(index, this.options.wl, this.options.ww);
	},

	changeWindow: function(level, width)
	{
		this._internalChangeImage(this.options.index, level, width);
	},

	_internalChangeImage: function(index, wl, ww)
	{
		if (this._error) return;
		var oldIndex = this.options.index;
		var oldWL = this.options.wl;
		var oldWW = this.options.ww;
		var self = this;
		index = Math.min(Math.max(index, this.options.min), this.options.max);
		this.options.index = index;
		this.options.wl = wl;
		this.options.ww = ww;
		if (oldIndex == index && oldWL == wl && oldWW == ww && this._initialized)
			return;
		$('.ui-imageviewer-slider', this.element).slider('option', 'value', index);
		this._waiting = { index: index, wl: wl, ww: ww };
		this._timerID = setTimeout(function() {
			self._loadingIndicate();
		}, this.options.loadingIndicateDelay);
		this.options.source.query(index, wl, ww);
		this.element.trigger('imagechanging');
	},

	_clearTimeout: function()
	{
		if (this._timerID)
			clearTimeout(this._timerID);
	},

	_loadingIndicate: function()
	{
		if (this._waiting != null)
		{
			$('.ui-imageviewer-image', this.element).css('cursor', 'wait');
			$('.ui-imageviewer-loading', this.element).show(0);
		}
	},

	_setData: function(key, value, animated)
	{
		switch (key) {
			case 'index':
				this.changeImage(value);
				return;
			case 'ww':
				this.changeWindow(this.options.wl, value);
				return;
			case 'wl':
				this.changeWindow(value, this.options.ww);
				return;
		}
		$.widget.prototype._setData.apply(this, arguments);
		switch (key) {
			case 'markers':
			case 'showMarkers':
			case 'markerStyle':
				this._drawMarkers();
				break;
			case 'width':
			case 'maxWidth':
			case 'cropRect':
				this._adjustImageSize();
				this._drawMarkers();
				break;
			case 'role':
			case 'useSlider':
			case 'useLocationText':
			case 'grayscalePresets':
				this._initialized = false;
				this._draw();
				break;
			case 'source':
				this._initialized = false;
				this._init();
		}
	}
});

$.extend($.ui.imageviewer, {
	defaults: {
		min: 1,
		max: 100,
		index: 1,
		ww: 0,
		wl: 0,
		width: 'auto',
		useLocationText: true,
		useSlider: true,
		sliderHotTrack: true,
		loadingIndicateDelay: 300,
		locationLabel: 'Image Number: ',
		grayscalePresets: [],
		grayscaleLabel: 'Grayscale Preset: ',
		role: 'viewer',
		showMarkers: true,
		markerStyle: 'dot',
		useWheel: true,
		cropRect: null,
		markers: []
	}
});

/**
 * DicomDynamicImageSource asks jump_image.php to dynamically create
 * JPEG/PNG image file from DICOM file.
 */
var DicomDynamicImageSource = function(series_instance_uid, toTopDir){
	var self = this;
	var cache = {};
	var top = toTopDir;
	var series_uid = series_instance_uid;
	initialize();

	function initialize()
	{
		var body = $('body');
		var cacheRoot = body.data('imageviewerCache') || {};
		body.data('imageviewerCache', cacheRoot);
		cache = cacheRoot[self.series__uid] || {};
		cacheRoot[series_uid] = cache;
		body.bind('imageviewerImageload', function (event, data) {
			loadedHandler(data);
		});
	}

	function cacheKey(index, wl, ww)
	{
		return index + '_' + wl + '_' + ww;
	}

	function loadedHandler(data)
	{
		if (data.status != 'OK')
		{
			console && console.log(data.error.message);
			self.error = true;
			if (self.onError instanceof Function)
				self.onError(data.error.message);
		}
		else if (data.imgFname && data.sliceNumber)
		{
			var key = cacheKey(data.sliceNumber, data.windowLevel, data.windowWidth);
			cache[key] = {
				fileName: top + data.imgFname,
				sliceNumber: data.sliceNumber,
				sliceLocation: data.sliceLocation,
				windowLevel: data.windowLevel,
				windowWidth: data.windowWidth
			};
			if (self.onLoad instanceof Function)
				self.onLoad(cache[key]);
		}
	}

	this.query = function(index, wl, ww)
	{
		var key = cacheKey(index, wl, ww);
		if (cache[key] && cache[key].fileName)
		{
			if (this.onLoad instanceof Function)
				this.onLoad(cache[key]);
		}
		else
		{	var param = {
				seriesInstanceUID: series_uid,
				imgNum: index,
				windowLevel: wl,
				windowWidth: ww
			};
			if (cache[key] instanceof Date)
				return;
			$.get(
				top + 'jump_image.php',
				param,
				function (data) {
					$('body').trigger('imageviewerImageload', data); // broadcast
				},
				'json'
			);
			cache[key] = new Date();
		}
	}
};

/**
 * StaticImageSource just maps image index to static file name.
 */
var StaticImageSource = function(source)
{
	var self = this;
	var src = source;

	var pad = function(num, width, pad)
	{
		var str = '' + num;
		while (str.length < width) str = pad + str;
		return str;
	}

	var internalImageFunc = function(index, wl, ww)
	{
		if (src instanceof String) return null;
		return src.replace(
			/%((0?)\d)?d/g,
			function(str, width, zero) {
				if (width.length > 0)
					return pad(parseInt(index), parseInt(width), zero == '0' ? '0' : ' ');
				else
					return parsetInt(index);
			}
		);
	}

	this.query = function(index, wl, ww) {
		var func = internalImageFunc;
		if (src instanceof Function)
			func = src;
		var imageFile = func(index, wl, ww);

		if (this.onLoad instanceof Function)
		{
			var ev = {
				fileName: imageFile,
				sliceNumber: index,
				sliceLocation: 0,
				windowLevel: wl,
				windowWidth: ww,
			};
			this.onLoad(ev);
		}
	}
};

