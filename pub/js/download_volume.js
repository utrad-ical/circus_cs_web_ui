if (typeof circus == 'undefined') circus = {};

circus.download_volume = (function() {
	var target_series; // download target
	var series_info;
	var target_job_id;
	var target_volume_id;
	var callback;
	var dialog;
	var slider;

	function busy() {
		$('#download-volume-begin').disable();
		$('#download-volume-busy').show();
	}

	function relax() {
		$('#download-volume-begin').enable();
		$('#download-volume-busy').hide();
	}

	function beginArchive()
	{
		var priv = $('#download-volume-private-tags').val();
		if (priv.match(/^(\d\d\d\d,\d\d\d\d;)*(\d\d\d\d,\d\d\d\d)?$/) === null)
		{
			alert("Wrong syntax in required private tags field.");
			return;
		}

		busy();
		$.ajaxSetup({ timeout: false });
		var mode = $(':radio:checked', dialog).val();
		var params = { mode: mode, requiredPrivateTags: priv };

		switch (mode)
		{
			case 'series':
				var v = slider.slider('option', 'values');
				params.seriesInstanceUID = target_series;
				params.startImgNum = v[0];
				params.endImgNum   = v[1];
				params.imageDelta = $('#download-volume-delta', dialog).val();
				break;
			case 'job':
				params.jobID = target_job_id;
				params.volumeID = target_volume_id;
				break;
		}

		$.webapi({
			action: 'createVolume',
			params: params,
			onSuccess: onVolumeLoad,
			onFail: function(error) {
				alert(error);
				relax();
			}
		});
	}

	function radioClicked() {
		var val = $(':radio:checked', dialog).val();
		$('#download-volume-range :input').enable(val != 'job');
		slider.slider('option', 'disabled', val == 'job');
	}

	function sliderChange(event, ui) {
		var v = ui.values;
		$('#download-volume-range-start').val(v[0]);
		$('#download-volume-range-end').val(v[1]);
	}

	function onVolumeLoad(data)
	{
		if (!dialog) return;
		relax();
		// start download by using <iframe>
		url = circus.totop + data.location;
		$('<iframe>').attr('src', url).attr('width', 1).attr('height', 1).hide().appendTo(dialog);
	}

	function onHtmlLoad()
	{
		if (!dialog) return;
		dialog.autoStylize();
		slider = $('#download-volume-range-slider').slider({
			range: true,
			min: series_info.minImageNumber,
			max: series_info.maxImageNumber,
			values: [series_info.minImageNumber, series_info.maxImageNumber],
			change: sliderChange,
			slide: sliderChange
		});
		sliderChange(null, { values: [series_info.minImageNumber, series_info.maxImageNumber] })

		$('#download-volume-close').click(function() {
			$.unblockUI();
			if (callback instanceof Function) callback();
			dialog = null;
		})
		$('#download-volume-busy').hide();
		if (target_job_id === null)
		{
			$('#download-volume-type').hide();
			$(':radio[name="dltype"]', dialog).val(['series']);
		}
		else
		{
			$(':radio[name="dltype"]', dialog).val(['job']);
		}

		$('#download-volume-range-start').blur(function(event) {
			var val = slider.slider('option', 'values');
			var newval = parseInt($('#download-volume-range-start').val());
			if (isNaN(newval))
				newval = series_info.minImageNumber;
			newval = Math.max(newval, series_info.minImageNumber);
			newval = Math.min(newval, val[1]);
			slider.slider('option', 'values', [newval, val[1]]);
		});

		$('#download-volume-range-end').blur(function(event) {
			var val = slider.slider('option', 'values');
			var newval = parseInt($('#download-volume-range-end').val());
			if (isNaN(newval))
				newval = series_info.maxImageNumber;
			newval = Math.min(newval, series_info.maxImageNumber);
			newval = Math.max(newval, val[0]);
			slider.slider('option', 'values', [val[0], newval]);
		});

		$('#download-volume-all').click(function() {
			slider.slider(
				'option',
				'values',
				[series_info.minImageNumber, series_info.maxImageNumber]
			);
		});

		$(':radio', dialog).click(radioClicked);
		$('#download-volume-begin').click(function () {
			beginArchive()
		});
		radioClicked();
	}

	function onSeriesInfoLoad(data)
	{
		if (!(data instanceof Array) || !('number' in data[0]))
		{
			alert("Error: failed to retrieve series information.");
			return;
		}
		series_info = data[0];
		dialog = $('<div>').addClass('download-volume');
		$.ajaxSetup({ cache: false });
		dialog.load(circus.totop + 'research/download_volume.html', onHtmlLoad);
		$.blockUI({
			message: dialog,
			css: { cursor: 'auto' },
			overlayCSS: { cursor: 'auto' }
		});
	}

	function openDialog()
	{
		$.webapi({
			action: 'internalCountImages',
			params: {
				seriesInstanceUID: [target_series]
			},
			onSuccess: onSeriesInfoLoad,
			onError: function(message) {
				alert("Error: failed to retrieve series information.\n" + message);
			}
		});
	}

	var global = {
		openDialogForJob: function(series_uid, job_id, volume_id, onClose)
		{
			dialog = null;
			series_info = null;
			target_job_id = job_id;
			target_volume_id = volume_id;
			target_series = series_uid;
			callback = onClose;
			openDialog();
		},
		openDialogForSeries: function(series_uid, onClose) {
			this.openDialogForJob(series_uid, null, null, onClose);
		}
	};
	return global;
})();