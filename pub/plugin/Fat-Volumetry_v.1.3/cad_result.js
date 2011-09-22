/*
 * Fat Volumetry
 */

$(function() {

	var sync = function(self, pair) {
		var index = self.imageviewer('option', 'index');
		pair.imageviewer('option', 'index', index);

		var data = circus.cadresult.displays[1];
		var max = data.length;
		for (var i = 0; i < max; i++)
		{
			if (data[i].sub_id == index)
			{
				var item = data[i];
				$('#dcm-slice-num').text(item.image_num);
				$('#slice-location').text(item.slice_location);
				$('#body-trunk-area').text(parseFloat(item.body_trunk_area).toFixed(3));
				$('#sat-area').text(parseFloat(item.sat_area).toFixed(4));
				$('#vat-area').text(parseFloat(item.vat_area).toFixed(4));
				$('#area-ratio').text(parseFloat(item.area_ratio).toFixed(2));
				$('#boundary-length').text(parseFloat(item.boundary_length).toFixed(4));
				return;
			}
			$('.slice-data').text('');
		}
	};

	var vOrig = $('#viewer-orig');
	var vMeasure = $('#viewer-measure');

	vOrig.imageviewer({
		source: new StaticImageSource(result_dir + '/ct%03d.png'),
		useSlider: false,
		useLocationText: false,
		max: circus.cadresult.displays[1].length
	})
	vOrig.bind('imagechanging', function() { sync(vOrig, vMeasure); });

	vMeasure.imageviewer({
		source: new StaticImageSource(result_dir + '/result%03d.png'),
		useLocationText: false,
		max: circus.cadresult.displays[1].length
	})
	vMeasure.bind('imagechanging', function() { sync(vMeasure, vOrig); });

	sync(vOrig, vMeasure);
});