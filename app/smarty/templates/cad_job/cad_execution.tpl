{capture name="require"}
../js/series_ruleset.js
{/capture}
{capture name="extra"}
<script type="text/javascript">
<!--
{literal}
$(function(){
	var registered = false;

	$('#messages p').hide();

	$('.filter').each(function() {
		var f = $(this);
		var data = JSON.parse(f.text());
		f.empty().append(circus.ruleset.stringifyNode(data));
	});

	$('.series-list').each(function() {
		if ($('.r', this).length == 1)
			$('.r', this).attr('checked', 'checked');
		$('.r').each(function() {
			$(this).data('series_uid', $(this).next('.series-uid').text());
		});
	});

	$('.volume-area').click(function(event) {
		if (registered) return;
		// check radio button
		var r = $(event.target).closest('.series-list tr').find('.r');
		var series_uid = r.data('series_uid');
		// uncheck duplicate
		$('.r').each(function() {
			if ($(this).data('series_uid') == series_uid)
				$(this).removeAttr('checked');
		});
		r.attr('checked', 'checked');
		updateStatus();
	});

	$('#register').click(function() {
		var uids = [];

		$('.volume-area').each(function() {
			var vid = parseInt($('.volume-id', this).text());
			var sid = $('.r:checked', this).data('series_uid');
			uids[vid] = sid;
		});

		var params = {
			pluginName: $('#cadName').val(),
			pluginVersion: $('#version').val(),
			seriesUID: uids,
			resultPolicy: $('#cadResultPolicy').val()
		};

		$.webapi({
			action: 'InternalExecutePlugin',
			params: params,
			onSuccess: function(data) {
				$('.job-id').text(data.jobID);
				registered = true;
				$('#buttons input, #buttons select, .r').disable();
				message('#success');
			},
			onFail: function(data) {
				message($('#error').text(data));
			}
		});
	});

	updateStatus();

	function updateStatus()
	{
		if (registered) return;
		var selectable = true;
		var ready = true;
		$('.volume-area').each(function() {
			var t = $(this).removeClass('volume-ready').find('tbody');
			t.find('tr').removeClass('row-selected');
			if (t.find('tr').length == 0)
				selectable = false;
			var sel = t.find('tr:has(input:radio:checked)');
			if (sel.length > 0)
			{
				sel.addClass('row-selected');
				$(this).addClass('volume-ready');
			}
			else
				ready = false;
		});
		$('#register').enable(ready);
		if (selectable && ready)
			message('#confirm');
		else if (selectable)
			message('#select');
		else
			message('#error2');
	}

	function message(selector)
	{
		$('div#messages p').hide();
		$(selector).show();
	}

});
-->
</script>

<style type="text/css">
#content h3 {
	clear: both;
	margin: 0 0 5px 0;
	background-color: transparent;
	color: black;
	letter-spacing: 0;
	border-bottom: 2px solid silver;
	padding: 0;
}
.detail-panel { float: none !important; }
#exec-header { position: relative; margin: 0 0 1em 0; }
#buttons { position: absolute; right: 1em; bottom: 0px; }
#buttons input { height: 2em; width: 130px; }
#buttons p { margin: 5px; text-align: right; }
#messages p { font-weight: bold; margin: 1em 0; padding: 0.5em; }
#messages #select { color: red; }
#messages .error { color: red; border: 1px solid salmon; }
#success { color: orange; }
.volume-area { clear: both; border: 1px solid red; padding: 3px; margin: 5px 0; }
.volume-ready { border: 1px solid silver; }
.series-list { width: 100%; cursor: pointer; color: #888 }
.series-list tr:hover { background-color: #ffa; }
.series-list tr.row-selected { color: black; background-color: #ff8; }
.series-not-found { font-weight: bold; color: red; text-align: center; margin: 1em; }
ul.filters { margin: 0.5em 0 0.5em 2em; }
ul.filters li { list-style-type: disc; }
</style>
{/literal}
{/capture}
{include file="header.tpl" require=$smarty.capture.require
	head_extra=$smarty.capture.extra body_class="cad_execution"}

<div class="tabArea">
	<ul>
		{if $params.srcList!="" && $smarty.session.listAddress!=""}
			<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$params.listTabTitle}">{$params.listTabTitle}</a></li>
		{else}
			<li><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
		{/if}
		<li><a href="" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD execution</a></li>
	</ul>
</div><!-- / .tabArea END -->

<div class="tab-content">
	<form id="form1" name="form1" onsubmit="return false;">
	<input type="hidden" id="cadName"      value="{$plugin->plugin_name|escape}" />
	<input type="hidden" id="version"      value="{$plugin->version|escape}" />

	<h2>Execute CAD Job</h2>
	<div id="messages">
		<p id="select">Multiple series matched. Select DICOM series, and press the
		<span style="color: blue">[OK]</span> button after selection.</p>
		<p id="confirm">Do you register the following CAD job?</p>
		<p id="success">CAD job is successfully registered. (Job ID: <span class="job-id"></span>)</p>
		<p id="error2" class="error">Required volume(s) does not match any of the series.</p>
		<p id="error" class="error"></p>
	</div>

	<div id="exec-header">
		<div class="detail-panel">
			<table class="detail-tbl">
				<tr>
					<th style="width: 10em;"><span class="trim01">CAD name</span></th>
					<td>{$plugin->fullName()|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Patient ID</span></th>
					<td>{$patient->patient_id|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Pateint name</span></th>
					<td>{$patient->patient_name|escape}</td>
				</tr>
			</table>
		</div>
		<div id="buttons">
			<p><label>Result policy: <select id="cadResultPolicy">
				{foreach from=$policies item=item}
				<option value="{$item->policy_name|escape}"{if $item->policy_name=="default"} selected="selected"{/if}>{$item->policy_name|escape}</option>
				{/foreach}
			</select></label></p>
			<input name="" type="button" value="Cancel" id="cancel" class="form-btn" onclick="history.back(1);" />
			<input name="" type="button" value="OK" id="register" class="form-btn" />
		</div>
	</div>

	<div id="volume-list">
		{foreach from=$volumeInfo item=volume}
		<div class="volume-area">
			<h3>Volume <span class="volume-id">{$volume.id}</span>
			{if $volume.id==0} (Primary Volume){/if}
			{if $volume.label != ""}: {$volume.label|escape}{/if}</h3>

			<ul class="filters">
				{foreach from=$volume.ruleSetList item=ruleSet}
				<li class="filter">{$ruleSet.filter|@json_encode|escape}</li>
				{/foreach}
			</ul>

			{if count($volume.targetSeries) > 0}
			<table class="series-list col-tbl">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>Study ID</th>
						<th>Modality</th>
						<th>Series ID</th>
						<th>Series date/time</th>
						<th>Img.</th>
						<th>Series description</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$volume.targetSeries item=series}
					<tr>
						<td>
							<input type="radio" name="volume{$volume.id}" class="r"/>
							<span style="display: none" class="series-uid">{$series->series_instance_uid|escape}</span>
						</td>
						<td>{$series->Study->sid|escape}</td>
						<td>{$series->Study->modality|escape}</td>
						<td>{$series->series_number|escape}</td>
						<td>{$series->series_date|escape} {$series->series_time|escape}</td>
						<td>{$series->image_number|escape}</td>
						<td>{$series->series_description|escape}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			{else}
			<div class="series-not-found">[Error] There are no series that match the filter.</div>
			{/if}
		</div>
		{/foreach}
	</div>
	<!-- / volume-list -->

	</form>

	<div>
		<p class="pagetop"><a href="#page">page top</a></p>
	</div>
</div><!-- / .tab-content END -->

{include file="footer.tpl"}
