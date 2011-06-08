/**
 * FN Input related initialization.
 */

circus.feedback.additional = circus.feedback.additional || [];

(function () {
	var f = {
		name: 'fn_input',
		initialize: function(data)
		{
			var canEdit = circus.feedback.feedbackStatus == 'normal';
			if (!(data instanceof Array)) data = [];
			// Prepares an image viewer widget for FN locating
			f._viewer = $('#fn-input-viewer').imageviewer({
				study_instance_uid: circus.cadresult.studyUID,
				series_instance_uid: circus.cadresult.seriesUID,
				max: circus.cadresult.seriesNumImages,
				toTopDir: '../',
				role: (canEdit ? 'locator' : 'viewer'),
				markers: data
			});
			if (data.length > 0)
				f._updateTable();
			var tbl = $('#fn-input-table tbody');
			// Handles FN location input
			f._viewer.bind('locate', f._updateTable).bind('locating', f._locating);
			$('#fn-delete').click(function(){
				var indexes = [];
				$('tr:has(input:checked)', tbl).each(
					function () { indexes.push(tbl.find('tr').index(this)); } );
				var markers = f._viewer.imageviewer('option', 'markers');
				for (var i = indexes.length-1; i >= 0; i--)
				{
					markers.splice(indexes[i], 1);
				}
				$('input:checked', tbl).attr('checked', '');
				f._viewer.imageviewer('option', 'markers', markers); // commit and redraw
				f._updateTable();
			});
			if (!canEdit)
				$('input:checkbox', tbl).attr('disabled', 'disabled');

			$('#fn-integrate').click(function(){
				var indexes = [];
				$('tr:has(input:checked)', tbl).each(
					function () { indexes.push(tbl.index(this)); } );
				var markers = f._viewer.imageviewer('option', 'markers');
				var sum = {x:0, y:0, z:0};
				for (var i = indexes.length-1; i >= 0; i--)
				{
					markers.splice(indexes[i], 1);
				}
				$('input:checked', tbl).attr('checked', '');
				f._viewer.imageviewer('option', 'markers', markers); // commit and redraw
				updateTable();
			});

			$('#fn-found, #fn-not-found').click(function () {
				circus.feedback.change();
			});

			$('#jump-fn-input').click(function () {
				$('#fn-found').click();
				circus.cadresult.showTabLabel('FN Input');
			});

			// Jump to an image by clicking rows on the FN location table
			tbl.click(function (event) {
				var tr = $(event.target).closest('tr');
				var id = tbl.find('tr').index(tr);
				var markers = f._viewer.imageviewer('option', 'markers');
				f._viewer.imageviewer('changeImage', markers[id].location_z);
			});

			// Back to main tab button
			$('.backMain').click(function () {
				circus.cadresult.showTabLabel('CAD Result');
			});

			if (circus.feedback.feedbackStatus == 'disabled')
			{
				var cnt = $('#fn-found-container');
				$('input[type=radio], input[type=button]', cnt)
					.attr('disabled', 'disabled')
			}
		},
		validate: function()
		{
			var markers = $('#fn-input-viewer').imageviewer('option', 'markers');
			var ok = $('#fn-found:checked').length > 0 && markers.length > 0;
			ok = ok || $('#fn-not-found:checked').length > 0;
			if (ok) {
				return {
					register_ok: true,
					message: 'FN Input: <span class="register-ok">Complete</span>'
				};
			} else {
				return {
					register_ok: false,
					message: 'FN Input: <span class="register-not-ok">Incomplete</span>'
				};
			}
		},
		collect: function()
		{
			var fns = [];
			var markers = $('#fn-input-viewer').imageviewer('option', 'markers');
			for (var i in markers)
			{
				fns.push(markers[i]);
			}
			return fns;
		},
		_updateTable: function()
		{
			var tbl = $('#fn-input-table tbody');
			tbl.find('tr').remove();
			var markers = f._viewer.imageviewer('option', 'markers');
			var max = markers.length;
			for (var i = 0; i < max; i++)
			{
				var tr = $('<tr>');
				$('<td><input type="checkbox"/></td>').appendTo(tr);
				$('<td>').text(i+1).appendTo(tr);
				$.each(
					['location_x', 'location_y', 'location_z', 'nearest_lesion_id', 'entered_by'],
					function (dum, key) {
						$('<td>').appendTo(tr).text(markers[i][key]);
					}
				);
				$('input[type=checkbox]', tr).click(function () {
					var cnt = $('input:checked', tbl).length;
					$('#fn-delete').attr('disabled', cnt > 0 ? '' : 'disabled').trigger('flush');
					$('#fn-integrate').attr('disabled', cnt > 1 ? '' : 'disabled').trigger('flush');
				});
				tbl.append(tr);
			}
			circus.feedback.change();
		},
		_locating: function(event)
		{
			var newitem = event.newItem;
			newitem.entered_by = circus.userID;
			newitem.nearest_lesion_id = f._checkNearestHiddenFP(
				newitem.location_x, newitem.location_y, newitem.location_z);
		},
		_checkNearestHiddenFP: function(posX, posY, posZ)
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
		},
	};
	circus.feedback.additional.push(f);
})();
