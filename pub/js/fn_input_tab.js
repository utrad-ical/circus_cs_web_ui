/**
 * FN Input related initialization.
 */

circus.feedback.additional = circus.feedback.additional || [];

(function () {
	var initData;

	var f = {
		name: 'fn_input',
		initialize: function(data)
		{
			initData = data;
			var canEdit = circus.feedback.feedbackStatus == 'normal';
			f.params = circus.cadresult.presentation.extensions.FnInputTab;

			var presets = [];
			var wl = 0;
			var ww = 0;
			if (circus.cadresult.fnInputGrayscalePresets.length > 0)
			{
				presets = circus.cadresult.fnInputGrayscalePresets;
				wl = presets[0].wl;
				ww = presets[0].ww;
			}

			// Prepares an image viewer widget for FN locating
			f._viewer = $('#fn-input-viewer').imageviewer({
				series_instance_uid: circus.cadresult.seriesUID,
				max: circus.cadresult.seriesNumImages,
				toTopDir: '../',
				role: (canEdit ? 'locator' : 'viewer'),
				grayscalePresets: presets,
				wl: wl,
				ww: ww
			})
			.bind('locate', f._updateTable).bind('locating', f._locating);

			f._resetMarkers();

			var tbl = $('#fn-input-table tbody');
			if (canEdit) {
				$('#fn-delete').click(function() {
					if (confirm('Do you delete the selected location(s)?'))
						f._marker_process(f._fn_delete);
				});
				$('#fn-integrate').click(function() {
					f._marker_process(f._fn_integrate);
				});
				$('#fn-reset').click(function() {
					if (confirm('Discard changes?'))
						f._resetMarkers();
				})
			}

			if (!canEdit) {
				$('input:checkbox', tbl).attr('disabled', 'disabled');
				$('#fn-delete, #fn-integrate, #fn-reset')
					.attr('disabled', 'disabled').trigger('flush');
			}

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
		_resetMarkers: function()
		{
			var markers = [];
			if (initData instanceof Array) markers = data;
			if (initData instanceof Object && initData.to_integrate)
				markers = f._integrateConsensual(initData.to_integrate);
			f._viewer.imageviewer('option', 'markers', markers);
			if (markers.length > 0)
				f._updateTable();
		},
		_marker_process: function(action)
		{
			var tbl = $('#fn-input-table tbody');
			var indexes = [];
			var markers = f._viewer.imageviewer('option', 'markers');
			$('tr:has(input:checked)', tbl).each(
				function () { indexes.push(tbl.find('tr').index(this)); } );
			action(indexes, markers);
			$('input:checked', tbl).removeAttr('checked');
			f._viewer.imageviewer('option', 'markers', markers); // commit and redraw
			f._updateTable();
		},
		_fn_delete: function(indexes, markers)
		{
			for (var i = indexes.length-1; i >= 0; i--)
			{
				markers.splice(indexes[i], 1);
			}
		},
		_fn_integrate: function(indexes, markers)
		{
			var sum = {x:0, y:0, z:0};
			for (var i = indexes.length-1; i >= 0; i--)
			{
				var idx = indexes[i];
				sum.x += markers[idx].location_x;
				sum.y += markers[idx].location_y;
				sum.z += markers[idx].location_z;
				markers.splice(idx, 1);
			}
			var newFn = {
				location_x: Math.floor(sum.x / indexes.length + 0.5),
				location_y: Math.floor(sum.y / indexes.length + 0.5),
				location_z: Math.floor(sum.z / indexes.length + 0.5),
				entered_by: circus.userID
			};
			f._snapToNearestHiddenCand(newFn);
			markers.push(newFn);
		},
		_updateTable: function()
		{
			var tbl = $('#fn-input-table tbody');
			tbl.find('tr').remove();
			var markers = f._viewer.imageviewer('option', 'markers');
			var markerCount = markers.length;
			for (var i = 0; i < markerCount; i++)
			{
				var tr = $('<tr>');
				$('<td><input type="checkbox"/></td>').appendTo(tr);
				$('<td>').text(i+1).appendTo(tr);
				var nearest = '-';
				if (markers[i].nearest_lesion_id)
				{
					var lid = markers[i].nearest_lesion_id;
					var nearestLesion = circus.cadresult.displays[lid];
					nearest = lid + ' / ' +
						Math.sqrt(f._distance2(markers[i], nearestLesion)).toFixed(1);
				}
				$.each(
					[
						markers[i].location_x,
						markers[i].location_y,
						markers[i].location_z,
						nearest,
						markers[i].entered_by
					],
					function (dum, value) {
						$('<td>').appendTo(tr).text(value);
					}
				);
				$('input[type=checkbox]', tr).click(f._updateCheckState);
				tbl.append(tr);
			}
			f._updateCheckState();
			circus.feedback.change();
			$('#fn-count').text(markers.length);
			if (markerCount > 0)
			{
				$('#fn-not-found').attr('disabled', 'disabled').removeAttr('checked');
				$('#fn-found').removeAttr('disabled').attr('checked', 'checked');
			}
			else
			{
				$('#fn-not-found, #fn-found').removeAttr('disabled');
			}
		},
		_updateCheckState: function()
		{
			var cnt = $('#fn-input-table input:checked').length;
			$('#fn-delete').attr('disabled', cnt > 0 ? '' : 'disabled').trigger('flush');
			$('#fn-integrate').attr('disabled', cnt > 1 ? '' : 'disabled').trigger('flush');
		},
		_assignLoc: function(target, from)
		{
			target.location_x = from.location_x;
			target.location_y = from.location_y;
			target.location_z = from.location_z;
			return target;
		},
		_locating: function(event)
		{
			var newitem = event.newItem;
			newitem.entered_by = circus.userID;
			newitem.nearest_lesion_id = f._findNearestHiddenCand(newitem);
		},
		_distance2: function(a, b)
		{
			var dx = a.location_x - b.location_x;
			var dy = a.location_y - b.location_y;
			var dz = a.location_z - b.location_z;
			return dx * dx + dy * dy + dz * dz;
		},
		_findNearestHiddenCand: function(item)
		{
			var distTh = f.params.distThreshold;
			distTh = distTh * distTh;
			var distMin = 1000000;
			var ret = null;
			for (var id in circus.cadresult.displays)
			{
				var display = circus.cadresult.displays[id]
				var dist = f._distance2(display, item);
				if(dist < distMin)
				{
					distMin = dist;
					if(distMin < distTh)
						ret = id;
				}
			}
			return ret;
		},
		_snapToNearestHiddenCand: function(fn)
		{
			var nearest = f._findNearestHiddenCand(fn);
			if (nearest != null)
			{
				var item = circus.cadresult.displays[nearest];
				f._assignLoc(fn, item);
				fn.nearest_lesion_id = nearest;
			}
		},
		_integrateConsensual: function(fn_list)
		{
			var max = fn_list.length;
			var result = [];
			for (var i = 0; i < max; i++)
			{
				var fn = fn_list[i];
				var item = {
					entered_by: fn.entered_by
				};
				f._assignLoc(item, fn);
				f._snapToNearestHiddenCand(item);
				result.push(item);
			}
			result = f._makeUnique(result);
			return result;
		},
		_makeUnique: function(fn_list)
		{
			var buf = {};
			var result = [];
			var max = fn_list.length;
			for (var i = 0; i < max; i++)
			{
				var fn = fn_list[i];
				var key = fn.location_x + ',' + fn.location_y + ',' + fn.location_z;
				buf[key] = buf[key] || {
					nearest_lesion_id: fn.nearest_lesion_id,
					entered_by: {}
				};
				f._assignLoc(buf[key], fn);
				buf[key].entered_by[fn.entered_by] = 1;
			}
			for (var key in buf)
			{
				var joined = '';
				var item = buf[key];
				for (var user_id in item.entered_by)
					joined = joined ? joined + ',' + user_id : user_id;
				result.push({
					location_x: item.location_x,
					location_y: item.location_y,
					location_z: item.location_z,
					nearest_lesion_id: item.nearest_lesion_id,
					entered_by: joined
				});
			}
			return result;
		}
	};
	circus.feedback.additional.push(f);
})();
