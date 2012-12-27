(function() {
	var self;
	var main;
	var options;
	var upload;
	var isBusy = false;

	function listHandler(data)
	{
		main.empty();
		for (var i = 0; i < data.length; i++)
		{
			var entry = data[i];
			var div = $('<div>').addClass('job-file-entry').data('url', entry.url);
			var a = $('<a>').text(entry.link).attr('href', entry.download);
			$('<span>').addClass('job-file-name').append(a).appendTo(div);
			$('<span>').addClass('job-file-size').text('(' + entry.size + ' bytes)').appendTo(div);
			div.appendTo(main);
		}
	}

	function errorHandler(message)
	{
		var p = $('<p>').addClass('error').text(message);
		main.empty().append(p);
	}

	function preview(row)
	{
		var url = row.data('url');
		var div = $('<div>').addClass('job-file-preview').appendTo(row);
		var m;
		if (url.match(/\.(jpe?g|png|gif)$/i))
		{
			$('<img>').addClass('job-file-thumbnail').attr('src', url).appendTo(div);
		}
		else if (m = url.match(/\.(mp4|m4v)$/i))
		{
			var ext = m[1].toLowerCase();
			if (ext == 'mp4') ext = 'm4v';
			var media = {};
			media[ext] = url;
			$('<div>').appendTo(div).jPlayer({
				swfPath: circus.toTop + 'jq',
				supplied: ext,
				width: '320px',
				nativeVideoControls: { all: /./ },
				ready: function() {
					$(this).jPlayer('setMedia', media).jPlayer('play');
				}
			});
		}
		else if (url.match(/\.(txt|csv)$/i))
		{
			var t = $('<textarea>').appendTo(div);
			$.ajax({
				url: url,
				dataType: 'text',
				type: 'GET',
				cache: false,
				success: function (data) { t.text(data); }
			});
		}
	}

	function clickHandler(event)
	{
		if (!options['enablePreview'] || isBusy) return;
		var target = $(event.target);
		if (!target.is('.job-file-entry,.job-file-preview')) return;
		var row = target.closest('.job-file-entry');
		if (row.is('.job-file-expanded'))
		{
			row.removeClass('job-file-expanded');
			row.find('.job-file-preview').remove();
		}
		else
		{
			row.addClass('job-file-expanded');
			preview(row);
		}
	}

	function busy()
	{
		isBusy = true;
		self.css('opacity', 0.5);
	}

	function relax()
	{
		isBusy = false;
		self.css('opacity', '');
	}

	function init()
	{
		self.empty();
		main = $('<div>').addClass('job-file-list').appendTo(self);
		main.click(clickHandler);

		options = circus.cadresult.presentation.extensions.CadFileManagerExtension;
		if (upload)
		{
			var uploader = $('<div>').appendTo(self);
			var file = $('<input type="file" name="upfile">').appendTo(uploader);
			var submit = $('<button class="form-btn">').text('Upload').appendTo(uploader);
			submit.click(function(event) {
				if (isBusy) return;
				submit.disable();
				file.upload(
					'attach_file.php',
					{jobID: circus.jobID},
					function (data) {
						if (data != 'OK')
						{
							alert("Error\n" + data);
						}
						refresh();
						submit.enable();
					},
					'text'
				);
			});
		}
	}

	function refresh()
	{
		$.webapi({
			action: 'inspectJobDirectory',
			params: {
				jobID: circus.jobID,
			},
			onSuccess: listHandler,
			onFail: errorHandler
		});
	}

	var fn = function(uploadable) {
		upload = uploadable;
		self = this;
		init();
		refresh();
	};

	$.fn.cadDirInspector = fn;
})();