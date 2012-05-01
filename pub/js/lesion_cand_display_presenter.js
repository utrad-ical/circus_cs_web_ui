$(function() {
	$('.result-block').each(function() {
		var block = $(this);
		var id = block.data('displayid');
		var display = circus.cadresult.displays[id];
		var cropRect = null;
		var attr = circus.cadresult.attributes;
		if (attr.crop_org_x !== undefined && attr.crop_org_y !== undefined
			&& attr.crop_width !== undefined && attr.crop_height !== undefined)
		{
			cropRect = {
				x: attr.crop_org_x,
				y: attr.crop_org_y,
				width: attr.crop_width,
				height: attr.crop_height
			};
		}

		var pSeries = circus.cadresult.seriesList[0]; // primary series
		// var sImg = pSeries.start_img_num;
		// var eImg = pSeries.start_img_num + (pSeries.image_count - 1) * pSeries.image_delta;
		var sImg = 1;
		var eImg = pSeries.numImages;
		var minImg = Math.min(sImg, eImg);
		var maxImg = Math.max(sImg, eImg);

		var range = circus.cadresult.presentation.displayPresenter.scrollRange;

		var options = {
			source: new DicomDynamicImageSource(pSeries.seriesUID, '../'),
			index: display.location_z,
			min: Math.max(minImg, display.location_z - range),
			max: Math.min(maxImg, display.location_z + range),
			width: circus.cadresult.presentation.displayPresenter.dispWidth,
			markers: [{
				location_x: display.location_x,
				location_y: display.location_y,
				location_z: display.location_z
			}],
			markerStyle: 'circle',
			cropRect: cropRect,
			useSlider: false,
			useLocationText: false,
			useMarkerLabel: false
		};
		if (attr.window_level !== undefined)
			options.wl = attr.window_level;
		if (attr.window_width !== undefined)
			options.ww = attr.window_width;
		$('.viewer', block).imageviewer(options);
	});
});

$(window).load(function() {
	$('.result-block .viewer').imageviewer('preload');
});