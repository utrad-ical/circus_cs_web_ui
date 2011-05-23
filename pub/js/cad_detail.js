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
	$('#cad-detail-viewer').imageviewer({
		study_instance_uid: $('#study-instance-uid').val(),
		series_instance_uid: $('#series-instance-uid').val(),
		toTopDir: '../',
		markers: markers
	})

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