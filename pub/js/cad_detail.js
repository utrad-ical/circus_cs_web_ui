/**
 * CAD Detail related initialization.
 */

$(function() {
	// Set up the stackable image viewer.
	markers = [];
	var data = circus.cadresult.displays;
	for (var dp in data)
	{
		markers.push(data[dp]);
	}
	var presets = [];
	var wl = 0;
	var ww = 0;
	if (circus.cadresult.cadDetailGrayscalePresets.length > 0)
	{
		presets = circus.cadresult.cadDetailGrayscalePresets;
	}
	if (circus.cadresult.attributes.window_level !== undefined &&
		circus.cadresult.attributes.window_width !== undefined)
	{
		presets.unshift({
			label: "CAD default",
			wl: circus.cadresult.attributes.window_level,
			ww: circus.cadresult.attributes.window_width
		});
	}
	if (presets.length > 0)
	{
		wl = presets[0].wl;
		ww = presets[0].ww;
	}

	var pSeries = circus.cadresult.seriesList[0]; // primary series
	var sImg = pSeries.start_img_num;
	var eImg = pSeries.start_img_num + (pSeries.image_count - 1) * pSeries.image_delta;
	var minImg = Math.min(sImg, eImg);
	var maxImg = Math.max(sImg, eImg);

	var viewer = $('#cad-detail-viewer').imageviewer({
		source: new DicomDynamicImageSource(pSeries.seriesUID, '../'),
		min: minImg,
		max: maxImg,
		markers: markers,
		markerStyle: 'circle',
		grayscalePresets: presets.length >= 2 ? presets : false,
		maxWidth: 512,
		wl: wl,
		ww: ww
	});

	$('#cad-detail-marker-type').change(function () {
		var val = $('#cad-detail-marker-type').val();
		viewer.imageviewer('option', 'markerStyle', val);
	});

	$('#cad-detail-show-markers').change(function() {
		var checked = $('#cad-detail-show-markers').is(':checked');
		viewer.imageviewer('option', 'showMarkers', checked);
	});

	// Emphasize the rows of detail result table
	$('#cad-detail-viewer').bind('imagechange', function (event) {
		var z = $('#cad-detail-viewer').imageviewer('option', 'index');
		$('#cad-detail-table tr').each(function () {
			if ($('td', this).eq(3).text() == z)
			{
				$(this).addClass('emphasis');
			}
			else
			{
				$(this).removeClass('emphasis');
			}
		});
	});

	// Add double click handlers.
	$('.display-pane').dblclick(function(event) {
		var display_id = $(event.target).closest('div.result-block').data('displayid');
		circus.cadresult.showTabLabel('CAD Detail');
		var pos_z = data[display_id].location_z;
		$('#cad-detail-viewer').imageviewer('changeImage', pos_z);
	})

	// Add click handler to the rows of the detail table
	$('#cad-detail-table tr:has(td)').click(function(event) {
		var tr = $(event.target).closest('tr');
		var pos_z = tr.find('td').eq(3).text();
		$('#cad-detail-viewer').imageviewer('changeImage', pos_z);
	});
});