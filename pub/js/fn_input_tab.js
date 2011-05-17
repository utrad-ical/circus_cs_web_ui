/**
 * FN Input related initialization.
 */

$(function() {
	// Prepares an image viewer widget for FN locating
	var viewer = $('#fn-input-viewer').imageviewer({
		study_instance_uid: $('#study-instance-uid').val(),
		series_instance_uid: $('#series-instance-uid').val(),
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
			$.each(
				['location_x', 'location_y', 'location_z', 'entered_by'],
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

	// Handles FN location input
	viewer.bind('locate', updateTable);

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