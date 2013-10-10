{capture name="require"}
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
jq/jquery.mousewheel.js
js/jquery.imageviewer.js
{/capture}
{capture name="extra"}
{literal}
<style type="text/css">
#title {
	background-color: #eee;
	border: 2px solid #ccc;
	padding: 1em;
	margin-bottom: 1em;
}
#content h1 {
	background-color: transparent;
	font-size: 20px;
	height: auto;
	text-shadow: 1px 1px 2px silver, 0 0 0.5em white;
}
#content #side { width: 400px; float: right; }
#content #center { width: 580px; }
#content .module { margin-bottom: 10px;	margin-left: 10px; }
#content .module h2 {
	margin: 0 0 10px -10px;
	border-bottom: 1px solid black;
	clear: none;
}
.missed_block {
	display: inline-block;
	width: 280px;
	border: 1px solid gray;
}
.missed_block .detail {
	float: right;
	margin: 2px 2px;
}
</style>

<script type="text/javascript">
$(function() {
	$('.viewer').each(function() {
		var _this = $(this);
		var series_uid = _this.data('series-uid');
		var crop = _this.data('crop');
		var extra_opt = _this.data('opt');
		var loc = _this.data('loc').split(',').map(function(i){return parseInt(i);});
		var options = {
			source: new DicomDynamicImageSource(series_uid, ''),
			index: loc[2],
			min: loc[2],
			max: loc[2],
			width: 280,
			cropRect: crop,
			markers: [{
				location_x: loc[0],
				location_y: loc[1],
				location_z: loc[2]
			}],
			markerStyle: 'circle',
			useSlider: false,
			useLocationText: false,
			useMarkerLabel: false
		};
		options = $.extend(options, extra_opt);
		console.log(options);
		_this.imageviewer(options);
	});
});
</script>

{/literal}
{/capture}
{include file="header.tpl" require=$smarty.capture.require
	head_extra=$smarty.capture.extra body_class=home}

<div id="title">
<h1 class="themeColor">Welcome to CIRCUS Clinical Server</h1>
<p><strong>User:</strong> {$currentUser->user_id|escape} (from {$smarty.server.REMOTE_ADDR})</p>
<p><strong>Last login:</strong> {$smarty.session.lastLogin|escape} (from {$smarty.session.lastIPAddr})</p>
</div>

<div id="side">
	<div class="module news">
		<h2>News</h2>
		<ul>
			{foreach from=$plugins item=item}
			<li><strong>{$item.plugin_name|escape}&nbsp;v.{$item.version|escape}</strong> was installed.&nbsp;({$item.install_dt|escape})</li>
			{/foreach}
		</ul>
	</div>

	<div class="module plugin_execution">
		<h2>Plug-in Execution</h2>
		<h4>Total of plug-in execution: {$execStats.count|escape} (since {$execStats.first|escape})</h4>
		{if $execStats.count > 0}
		[Top {$cadExecutionData|@count}]</p>
		<ul>
			{foreach from=$cadExecutionData item=item}
				<li>{$item.plugin_name|escape}&nbsp;v.{$item.version|escape}: {$item.cnt|escape}</li>
			{/foreach}
		</ul>
		{/if}
	</div>
</div>

<div id="center">
	{if !is_null($topMessage) && strlen($topMessage)}
	<div class="module top_message">
		<h2>Message</h2>
		<div id="top_message">{$topMessage}</div>
	</div>
	{/if}

	{if count($recentMissed)}
	<div class="module recent_missed">
		<h2>Latest Results</h2>
		{foreach from=$recentMissed item=item}
			{assign var="job" value=$item.job}
			{assign var="d" value=$item.display}
			{assign var="pt" value=$job->Series[0]->Study->Patient}
			<div class="missed_block">
				<a class="detail form-btn" href="cad_results/cad_result.php?jobID={$job->job_id}">Detail</a>
				<p><strong>Pt.:</strong> {$pt->patient_name|escape} ({$pt->patient_id|escape})</p>
				<p><strong>St.:</strong> {$job->Series[0]->Study->study_date|escape}</p>
				<p><strong>CAD:</strong> {$job->Plugin->plugin_name|escape} v.{$job->Plugin->version|escape}</p>
				<div class="viewer"
					data-loc="{$d.location_x},{$d.location_y},{$d.location_z}"
					data-series-uid="{$job->Series[0]->series_instance_uid|escape}"
					data-crop="{$item.crop|@json_encode|escape}"
					data-opt="{$item.opt|@json_encode|escape}">
				</div>
			</div>
		{/foreach}
	</div>
	{/if}
</div>

{include file="footer.tpl"}