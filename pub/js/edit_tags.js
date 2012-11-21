/**
 * Tag editor.
 */

if (typeof circus == 'undefined') circus = {};

circus.edittag = (function() {
	var sid;
	var tags = [];
	var cat_id;
	var callback;

	function editorLoad(extra)
	{
		internalLoad(
			cat_id,
			sid,
			function(result) {
				tags = result;
				refresh();
			},
			extra
		);
	}

	function internalLoad(category, referenceID, onLoad, extra)
	{
		var params = { category: category, referenceID: referenceID };
		if (extra instanceof Object)
			for (var key in extra)
				params[key] = extra[key];
		$.post(
			circus.totop + 'tag_registration.php',
			params,
			function(data) {
				var tmp = JSON.parse(data);
				if (tmp.status == 'OK')
				{
					onLoad(tmp.result.tags);
				}
				else
				{
					alert(tmp.error.message);
				}
			},
			'text'
		);
	}

	function add(tag) {
		editorLoad({ mode: 'add', tag: tag });
	}

	function del(tag) {
		editorLoad({ mode: 'delete', tag: tag });
	}

	function refresh() {
		var tbody = $('#tags-list tbody');
		tbody.empty();
		var max = tags.length;
		for (var i = 0; i < max; i++)
		{
			var tag = tags[i];
			var tr = $('<tr>');
			$('<td>').text(tag.tag).appendTo(tr);
			$('<td>').text(tag.entered_by).appendTo(tr);
			var btn = $('<input type="button" value="Delete" class="form-btn">');
			btn.click(function(event) {
				var tagname = $(event.currentTarget).closest('tr').find('td:eq(0)').text();
				del(tagname);
			});
			$('<td>').append(btn).appendTo(tr);
			tbody.append(tr);
		}
		tbody.autoStylize();
		$('#edit-tags-loading').hide(0);
		$('#tags-list').show(0);
		$('#edit-tags-add').enable();
		$('#new-tag-name').val('');
	}

	var global = {
		cat_title: {
			1: 'Patient',
			2: 'Study',
			3: 'Series',
			4: 'CAD result',
			5: 'CAD result element'
		},
		load: function(category, referenceID, onLoad) {
			internalLoad(category, referenceID, onLoad);
		},
		openEditor: function(category, referenceID, onClose) {
			sid = referenceID;
			cat_id = category;
			callback = onClose;
			var div = $('<div>').addClass('edit-tag');
			div.load(circus.totop + 'edit_tags.html', function() {
				$('#edit-tags-title').text(global.cat_title[category]);
				$('#edit-tags-close').click(function() {
					$.unblockUI();
					if (callback instanceof Function) callback(tags);
				});
				$('#edit-tags-add').click(function() {
					if ($('#new-tag-name').val().length > 0)
						add($('#new-tag-name').val());
				});
				$('#new-tag-name').keydown(function(event) {
					if (event.keyCode == 13)
						$('#edit-tags-add').click();
				});
				$('#edit-tags-add, #edit-tags-close').autoStylize();
				editorLoad();
			});
			$.blockUI({
				message: div,
				css: { cursor: 'auto' },
				overlayCSS: { cursor: 'auto' }
			});
		    $(document).keypress(function(event) {
				if(event.keyCode == 27)
					$.unblockUI();
			});
		}
	};
	return global;
})();

$.fn.refreshTags = function(tags, link, filterKey)
{
	return this.each(function() {
		var self = $(this);
		self.empty();
		$.each(tags, function(dummy, tag) {
			var tmp = {};
			tmp[filterKey] = tag.tag;
			var a = $('<a>')
				.attr('href', link + '?' + $.param(tmp))
				.attr('title', 'Entered by ' + tag.entered_by)
				.text(tag.tag);
			self.append(a).append(' | ');
		});
	})
}