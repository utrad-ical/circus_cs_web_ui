/**
 * FN Input related initialization.
 */

$(function() {
	// Prepares an image viewer widget for FN locating
	var viewer = $('#fn-input-viewer').imageviewer({
		study_instance_uid: circus.cadresult.studyUID,
		series_instance_uid: circus.cadresult.seriesUID,
		max: circus.cadresult.seriesNumImages,
		toTopDir: '../',
		role: 'locator',
		markers: []
	});
	var tbl = $('#fn-input-table').find('tbody');

	var updateTable = function () {
		tbl.find('tr').remove();
		var markers = viewer.imageviewer('option', 'markers');
		var max = markers.length;
		for (var i = 0; i < max; i++)
		{
			var tr = $('<tr>');
			$('<td><input type="checkbox"/></td>').appendTo(tr);
			$('<td>').text(i+1).appendTo(tr);
			$.each(
				['location_x', 'location_y', 'location_z', 'nearest', 'entered_by'],
				function (dum, key) {
					$('<td>').text(markers[i][key]).appendTo(tr);
				}
			);
			$('input[type=checkbox]', tr).click(function () {
				var cnt = $('input:checked', tbl).length;
				$('#fn-delete').attr('disabled', cnt > 0 ? '' : 'disabled').trigger('flush');
				$('#fn-integrate').attr('disabled', cnt > 1 ? '' : 'disabled').trigger('flush');
			});
			tbl.append(tr);
		}
	};

	var locating = function (event)
	{
		var newitem = event.newItem;
		newitem.entered_by = circus.userID;
		newitem.nearest = checkNearestHiddenFP(
			newitem.location_x, newitem.location_y, newitem.location_z);
	};

	var checkNearestHiddenFP = function (posX, posY, posZ)
	{
		var distTh = 5.0;
		distTh = distTh * distTh;
		var distMin = 10000;
		var ret = '- / -';
		for (var id in circus.cadresult.displays)
		{
			var item = circus.cadresult.displays[id];
			var dx = item.location_x - posX;
			var dy = item.location_y - posY;
			var dz = item.location_z - posZ;
			var dist = dx * dx + dy * dy + dz * dz;
			if(dist < distMin)
			{
				distMin = dist;
				if(distMin < distTh)
					ret = item.display_id + ' / ' + Math.sqrt(distMin).toFixed(2);
			}
		}
		return ret;
	};

	// Handles FN location input
	viewer.bind('locate', updateTable).bind('locating', locating);

	$('#fn-delete').click(function(){
		var indexes = [];
		$('tr:has(input:checked)', tbl).each(
			function () { indexes.push(tbl.find('tr').index(this)); } );
		var markers = viewer.imageviewer('option', 'markers');
		for (var i = indexes.length-1; i >= 0; i--)
		{
			markers.splice(indexes[i], 1);
		}
		$('input:checked', tbl).attr('checked', '');
		viewer.imageviewer('option', 'markers', markers); // commit and redraw
		updateTable();
	});

	$('#fn-integrate').click(function(){
		var indexes = [];
		$('tr:has(input:checked)', tbl).each(
			function () { indexes.push(tbl.index(this)); } );
		var markers = viewer.imageviewer('option', 'markers');
		var sum = {x:0, y:0, z:0};
		for (var i = indexes.length-1; i >= 0; i--)
		{
			markers.splice(indexes[i], 1);
		}
		$('input:checked', tbl).attr('checked', '');
		viewer.imageviewer('option', 'markers', markers); // commit and redraw
		updateTable();
	});

	// Jump to an image by clicking rows on the FN location table
	tbl.click(function (event) {
		var tr = $(event.target).closest('tr');
		var id = tbl.find('tr').index(tr);
		var markers = viewer.imageviewer('option', 'markers');
		viewer.imageviewer('changeImage', markers[id].location_z);
	});

	// Back to main tab button
	$('.backMain').click(function () {
		CircusCadResult.showTabLabel('CAD Result');
	});

});