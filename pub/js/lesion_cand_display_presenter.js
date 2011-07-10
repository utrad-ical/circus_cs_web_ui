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
		var v = $('.viewer', block).imageviewer({
			source: new DicomDynamicImageSource(circus.cadresult.seriesUID, '../'),
			index: display.location_z,
			min: Math.max(0, display.location_z - 5),
			max: Math.min(circus.cadresult.seriesNumImages, display.location_z + 5),
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
		});
		if (attr.window_level !== undefined)
			v.imageviewer('option', 'wl', attr.window_level);
		if (attr.window_width !== undefined)
			v.imageviewer('option', 'ww', attr.window_width);
		v.imageviewer('preload');
	});
});

