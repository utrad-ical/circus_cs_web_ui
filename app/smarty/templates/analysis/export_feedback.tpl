{capture name="require"}
jq/ui/jquery-ui.min.js
js/jquery.daterange.js
js/jquery.formserializer.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{capture name="extra"}
<script type="text/javascript">

var plugins = {$plugins|@json_encode};

{literal}

$(function() {
	var $cad_date = $('#cad_date').daterange({ icon: circus.totop + 'images/calendar_view_month.png' });

	var $cad_name = $('#cad_name');
	var $cad_version = $('#cad_version');

	var feedback = null; // raw feedback data

	function cadChanged() {
		var val = $cad_name.val();
		if (!val) { $cad_version.val(''); return; }
		var versions = plugins[val];
		$cad_version.empty();
		$.each(versions, function(i, version) {
			$('<option>').text(version).appendTo($cad_version);
		});
	}

	$cad_name.change(cadChanged);
	$.each(plugins, function(plugin_name) {
		$('<option>').text(plugin_name).appendTo($cad_name);
	});
	cadChanged();

	$('#apply_btn').click(function() {
		if (!$cad_name.val() || !$cad_version.val()) {
			alert('Please specify CAD.');
			return;
		}
		$.webapi({
			action: 'exportFeedback',
			params: $('#condition').toObject(),
			onSuccess: dataLoaded
		});
	});

	function dataLoaded(data) {
		if (!$.isPlainObject(data) || !$.isArray(data.data)) return;
		$('#response').text('Number of jobs: ' + data.data.length +
			', response time: ' + data.query_time.toFixed(3) + 'sec.');
		feedback = data;
		update();
	}

	$('#format_pane').on('change', update);

	function update() {
		var style = $('#format').val();
		var result_str =
			{
				csv: exportAsCsv,
				json: exportAsJson,
				json_pretty: exportAsJson,
				json_linebyline: exportAsJsonLineByLine
			}[style](style);
		$('#result').val(result_str);
		$('#result_pane').show();
	}

	function escapeCsv(input) {
		if (typeof input !== 'string') {
			input = '' + input;
		}
		if (input.match(/\"|\,/)) {
			return '"' + input.replace(/\"/g, '""') + '"';
		} else {
			return input;
		}
	}

	function exportAsCsv(style) {
		var result = 'job_id,executed_at,is_consensual,entered_by,fb_id,content\n';
		$.each(feedback.data, function(i, job) {
			$.each(job.feedback, function(j, fb) {
				var pre = [job.job_id, job.executed_at, fb.is_consensual, fb.entered_by].map(escapeCsv).join(',');
				$.each(fb.blockFeedback, function(fb_id, content) {
					result += pre + ',' + fb_id + ',' + escapeCsv(content) + "\n";
				});
			});
		});
		return result;
	}

	function exportAsJsonLineByLine(style) {
		var result = '';
		$.each(feedback.data, function(idx, item) {
			result += JSON.stringify(item) + "\n";
		});
		return result;
	}

	function exportAsJson(style) {
		return JSON.stringify(feedback.data, null, style == 'json_pretty' ? '  ' : '');
	}
});

</script>

<style type="text/css">
.search-tbl th { padding-right: 2em; }
input.form-btn { padding-right: 10px; padding-left: 10px; }
#result_pane { margin-top: 30px; display: none; }
#format_pane { margin: 10px 0; }
#result { width: 100%; height: 500px; }
</style>

{/literal}
{/capture}
{include file="header.tpl" body_class="spot"
	require=$smarty.capture.require head_extra=$smarty.capture.extra}

<h2><div class="breadcrumb"><a href="index.php">Analysis</a> &gt;</div>
Export feedback data</h2>

<h3>Export</h3>
<div style="padding: 20px;">
	<table id="condition" class="search-tbl">
		<tr>
			<th><span class="trim01">CAD</span></th>
			<td>
				<select id="cad_name" name="plugin_name">
					<option value="" selected="selected">(Select)</option>
				</select>
				ver.
				<select id="cad_version" name="version">></select>
			</td>
		</tr>
		<tr>
			<th><span class="trim01">CAD date</span></th>
			<td><span id="cad_date"></span></td>
		</tr>
		<tr>
			<th><span class="trim01">Feedback Mode</span></th>
			<td>
				<label><input type="radio" name="feedback_mode" value="consensual" checked="checked">Only consensual feedback</label>
				<label><input type="radio" name="feedback_mode" value="all">Both personal + consensual</label>
			</td>
		</tr>
	</table>
	<div class="al-l" style="margin-top: 10px; margin-left: 20px; width: 100%;">
		<input type="button" id="apply_btn" value="load feedback data" class="form-btn" />
	</div>

	<div id="result_pane">
		<p id="response"></p>
		<div id="format_pane">
			<span class="trim01">Export format</span>
			<select id="format" name="format">
				<option value="json_pretty">JSON, all, pretty print</option>
				<option value="json">JSON, all, condensed</option>
				<option value="json_linebyline">JSON, one feedback per line</option>
				<option value="csv">CSV, one block per line</option>
			</select>
		</div>
		<textarea id="result" readonly="readonly"></textarea>
	</div>

</div>

{include file="footer.tpl"}