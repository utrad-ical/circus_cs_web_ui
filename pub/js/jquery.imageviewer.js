/**
 * jQuery UI custom widget: Stackable image viewer.
 *
 * Author:
 *   Soichiro Miki
 * Depends:
 *   jquery-ui-1.8.x.js with Slider widget (or newer)
 *   jquery.mousewheel.(min).js (optional)
 *   layout.css
 */

$.widget('ui.imageviewer', {
	options: {
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
		useMarkerLabel: true,
		useWheel: true,
		cropRect: null,
		markers: []
	},

	_create: function()
	{
		var self = this;
		if (this.options.source instanceof Object)
		{
			this.options.source.onLoad = function(data) {
				self._imageDeterminedHandler(data);
			}
			this.options.source.onError = function(message) {
				self._criticalErrorMode(message);
			}
		}
		else
		{
			self._criticalErrorMode('Image source not set correctly.');
		}
		this._initialized = false;
		delete this._imageWidth;
		delete this._imageHeight;
		delete this._scale;
		delete this._cache;
		delete this._waiting;
		delete this._criticalError;
		this._draw();
		this._initialized = true;
	},

	_draw: function ()
	{
		var root = this.element.empty();
		var self = this;
		root.addClass('ui-imageviewer');

		var stage = $('<div class="ui-imageviewer-stage">').appendTo(root);
		$('<div class="ui-imageviewer-markers">').appendTo(stage);

		var img = $('<img class="ui-imageviewer-image">').appendTo(stage);
		if ('_imageWidth' in this)
			this._adjustImageSize();

		$('<div class="ui-imageviewer-error">').appendTo(stage).hide();

		if (this._criticalError)
		{
			this._drawError(this._criticalError);
		}

		$('<div class="ui-imageviewer-loading">').appendTo(stage).hide(0);
		img.mousedown(function(){return false;}); // prevent selection/drag
		if (this.options.useWheel && img.mousewheel)
		{
			stage.mousewheel(function (event, delta) {
				if (self.options.disabled)
					return;
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
						if (!self._imageChanging)
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
			location_z: this.options.index
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
		var container = $('div.ui-imageviewer-markers', this.element).empty();
		var index = this.options.index;
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
			var labelx = 3;
			var labely = -1;
			switch (this.options.markerStyle)
			{
				case 'circle':
					$('<div class="ui-imageviewer-circle" />')
						.css({left: x - 12, top: y - 12})
						.appendTo(container);
					labelx = 6;
					labely = 6;
					break;
				case 'cross':
					$('<div class="ui-imageviewer-cross" />')
						.css({left: x - 25, top: y - 25})
						.appendTo(container);
					labelx = 12;
					labely = 12;
					break;
				case 'dot':
				default:
					$('<div class="ui-imageviewer-dot" />')
						.css({left: x - 1, top:  y - 1})
						.appendTo(container);
			}
			if (this.options.useMarkerLabel)
				$('<div class="ui-imageviewer-markerlabel" />')
					.text(mark.display_id || i+1)
					.css({left: x + labelx, top: y + labely})
					.appendTo(container);
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

	_criticalErrorMode: function(message)
	{
		this._criticalError = message;
		this._drawError(message);
	},

	_imageLoadHandler: function(data, image)
	{
		var w = this._waiting;
		if (w && w.index == data.sliceNumber && w.wl == data.windowLevel && w.ww == data.windowWidth)
		{
			this.options.sliceLocation = data.sliceLocation;
			this._label(data.sliceNumber);
			this._waiting = null;

			var img = $('.ui-imageviewer-image', this.element);
			var errdiv = $('.ui-imageviewer-error', this.element);
			if (image)
			{
				if (!('_imageWidth' in this))
				{
					this._imageWidth = image.width;
					this._imageHeight = image.height;
					this._adjustImageSize();
				}
				img.css('cursor', this._cursor).attr('src', image.src).show();
				errdiv.hide();
			}
			else
			{
				this._drawError(data.error);
			}

			this._clearTimeout();
			this._drawMarkers();
			this.element.trigger('imagechange');
			$('.ui-imageviewer-loading', this.element).hide();
		}
	},

	_drawError: function(errorMessage)
	{
		$('.ui-imageviewer-image, .ui-imageviewer-loading', this.element).hide();
		var errdiv = $('.ui-imageviewer-error', this.element).show();
		errdiv.empty().append('Error while loading the image.');
		if (errorMessage)
			errdiv.append('<br>').append(errorMessage);
	},

	_imageDeterminedHandler: function(data)
	{
		if (data.fileName)
		{
			var img = new Image(); // just starts preloading the image
			var self = this;
			img.onload = function (event) { self._imageLoadHandler(data, img); };
			img.src = data.fileName;
		}
		else
		{
			this._imageLoadHandler(data, null);
		}
	},

	preload: function()
	{
		for (var i = this.options.min; i <= this.options.max; i++)
		{
			this.options.source.query(i, this.options.wl, this.options.ww);
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
		this._imageChanging = true;
		if (this._criticalError) return;
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
		this._imageChanging = false;
	},

	_clearTimeout: function()
	{
		if (this._timerID)
		{
			clearTimeout(this._timerID);
			this._timerID = null;
		}
	},

	_loadingIndicate: function()
	{
		if (this._waiting != null)
		{
			$('.ui-imageviewer-image', this.element).css('cursor', 'wait');
			$('.ui-imageviewer-loading', this.element).show(0);
		}
	},

	_setOption: function(key, value, animated)
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
		$.Widget.prototype._setOption.apply(this, arguments);
		switch (key) {
			case 'markers':
			case 'showMarkers':
			case 'markerStyle':
			case 'useMarkerLabel':
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
				this._create();
				break;
			case 'disabled':
				this.element.find('.ui-imageviewer-slider').slider('option', 'disabled', value);
				this.element.find(
					'.updown button, .ui-imageviewer-grayscale-preset select'
				).prop('disabled', value);
				break;
		}
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
		cache = cacheRoot[series_uid] || {};
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
		var result = data.result;
		var req = data.request;
		if (req.seriesInstanceUID != series_uid)
			return;
		var key = cacheKey(req.imgNum, req.windowLevel, req.windowWidth);
		var cacheval = {
			sliceNumber: req.imgNum,
			windowLevel: req.windowLevel,
			windowWidth: req.windowWidth
		};
		if (result.status != 'OK')
		{
			cacheval.error = result.error.message;
			console && console.log(result.error.message, req);
		}
		if (result.imgFname && result.sliceNumber)
		{
			cacheval.fileName = top + result.imgFname;
			cacheval.sliceLocation = result.sliceLocation;
		}
		cache[key] = cacheval;
		if (self.onLoad instanceof Function)
			self.onLoad(cacheval);
	}

	this.query = function(index, wl, ww)
	{
		var key = cacheKey(index, wl, ww);
		if (cache[key] && cache[key].sliceNumber)
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
				function (result) {
					$('body').trigger(
						'imageviewerImageload',
						{request: param, result: result}
					); // broadcast
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
				if (parseInt(width) > 0)
					return pad(parseInt(index), parseInt(width), zero == '0' ? '0' : ' ');
				else
					return parseInt(index);
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
				windowWidth: ww
			};
			this.onLoad(ev);
		}
	}
};

