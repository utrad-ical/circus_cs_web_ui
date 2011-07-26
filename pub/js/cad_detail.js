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

	if (circus.cadresult.attributes.window_level !== undefined)
		wl = circus.cadresult.attributes.window_level;
	if (circus.cadresult.attributes.window_width !== undefined)
		ww = circus.cadresult.attributes.window_width;
	//if (circus.cadresult.cadDetailGrayscalePresets.length > 0)
	//{
	//	presets = circus.cadresult.cadDetailGrayscalePresets;
	//	wl = presets[0].wl;
	//	ww = presets[0].ww;
	//}

	var minImg = 1;
	if (circus.cadresult.attributes.start_img_num)
		minImg = Number(circus.cadresult.attributes.start_img_num);

	var viewer = $('#cad-detail-viewer').imageviewer({
		source: new DicomDynamicImageSource(circus.cadresult.seriesUID, '../'),
		min: minImg,
		max: circus.cadresult.seriesNumImages,
		markers: markers,
		markerStyle: 'circle',
		//grayscalePresets: presets,
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