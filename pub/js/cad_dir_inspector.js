(function() {
	var self;
	var main;
	var options;
	var upload;
	var isBusy = false;
	var deleteButton;
	var deleteTarget;

	function listHandler(data)
	{
		main.empty();
		for (var i = 0; i < data.length; i++)
		{
			var entry = data[i];
			var div = $('<div>').addClass('job-file-entry').
				data('file', entry.file).
				data('url', entry.url).
				data('deletable', entry.deletable).
				hover(entryEnter, entryLeave);
			var a = $('<a>').text(entry.link).attr('href', entry.download);
			$('<span>').addClass('job-file-name').append(a).appendTo(div);
			$('<span>').addClass('job-file-size').text('(' + entry.size + ' bytes)').appendTo(div);
			div.appendTo(main);
		}
	}

	function entryEnter(event)
	{
		var target = $(event.currentTarget);
		if (!target.is('.job-file-entry') || !target.data('deletable')) return;
		deleteTarget = target;
		align();
	}

	function align()
	{
		if (!deleteTarget) return;
		deleteButton.appendTo(deleteTarget).show().position({
			my: 'right top',
			at: 'right top',
			of: deleteTarget,
			offset: '-5px 3px'
		});
	}

	function entryLeave(event)
	{
		deleteButton.appendTo(self).hide();
		deleteTarget = null;
	}

	function deleteClicked(event)
	{
		if (!deleteTarget) return;
		var fileName = deleteTarget.data('file');
		if (confirm('Delete ' + fileName + '?'))
		{
			$.webapi({
				action: 'inspectJobDirectory',
				params: { jobID: circus.jobID, delete: fileName },
				onSuccess: refresh
			});
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
			align();
		}
		else
		{
			row.addClass('job-file-expanded');
			preview(row);
			align();
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
						file.val('');
						refresh();
						submit.enable();
					},
					'text'
				);
			});
		}

		deleteButton = $('<button>').addClass('job-file-delete').button({
			icons: { primary: 'ui-icon-close' },
			text: false
		}).click(deleteClicked).appendTo(self).hide();
	}

	function refresh()
	{
		entryLeave();
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