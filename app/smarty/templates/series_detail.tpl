{capture name="require"}
jq/ui/jquery-ui-1.7.3.min.js
jq/jquery.blockUI.js
js/search_panel.js
js/edit_tags.js
jq/ui/css/jquery-ui-1.7.3.custom.css
jq/jquery.mousewheel.min.js
js/jquery.imageviewer.js
css/popup.css
css/darkroom.css
{/capture}

{capture name="extra"}

<script language="Javascript">

var sid = "{$series->sid|escape:javascript}";
var seriesInstanceUID = "{$series->series_instance_uid|escape:javascript}";
var viewer = {$viewer|@json_encode};

{literal}

$(function() {
	// sets up image viewer
	$('#download').click(function() {
		window.open("about:blank","Download", "width=400,height=200,location=no,resizable=no");
		$('#dl-form').submit();
	});

	var viewer_params = { series_instance_uid: seriesInstanceUID };
	$.extend(viewer_params, viewer);
	console.log(viewer_params);
	$('#series-detail-viewer').imageviewer(viewer_params);

	// tag editor
	var refresh = function(tags) {
		$('#series-tags').refreshTags(tags, 'series_list.php', 'filterTag');
	};
	$('#edit-tag').click(function() {
		circus.edittag.openEditor(3, sid, '', refresh);
	});
	circus.edittag.load(3, sid, '', refresh);
});

</script>

<style type="text/css">
#download-panel { margin: 10px 0 0 15px; }
</style>
{/literal}
{/capture}

{include file="header.tpl" require=$smarty.capture.require
	head_extra=$smarty.capture.extra}

<!-- ***** TAB ***** -->
<div class="tabArea">
	<ul>
		<li><a href="series_list.php?mode=study&studyInstanceUID={$study->study_instance_uid|escape:url}" class="btn-tab">Series list</a></li>
		<li><a href="" class="btn-tab btn-tab-active">Series detail</a></li>
	</ul>
</div><!-- / .tabArea END -->

<div class="tab-content">
{if $data.errorMessage != ""}
	<div style="color:#f00; font-weight:bold;">{$data.errorMessage|escape|nl2br}</div>
{else}
	<div id="series_detail">
		<h2>Series detail</h2>

		<div class="series-detail-img">
			<div id="series-detail-viewer"></div>{* Viewer Placeholder *}
		</div>

		<div class="detail-panel">
			<table class="detail-tbl">
				<tr>
					<th style="width: 12em;"><span class="trim01">Patient ID</span></th>
					<td>{$patient->patient_id|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Patient name</span></th>
					<td>{$patient->patient_name|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Sex</span></th>
					<td>{$patient->sex|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Age</span></th>
					<td>{$patient->age()|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Study ID</span></th>
					<td>{$study->study_id|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Series date</span></th>
					<td>{$series->series_date|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Series time</span></th>
					<td>{$series->series_time|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Modality</span></th>
					<td>{$series->modality|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Series description</span></th>
					<td>{$series->series_description|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Body part</span></th>
					<td>{$series->body_part|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Image number</span></th>
					<td><span id="sliceNumber">******</span></td>
				</tr>
				<tr>
					<th><span class="trim01">Slice location</span></th>
					<td><span id="sliceLocation">******</span></td>
				</tr>
			</table>
			{if $currentUser->hasPrivilege('volumeDownload')}
			<form id="dl-form" target="Download" action="research/convert_volume_data.php" method="post">
				<div id="download-panel">
					<input id="download" value="Download volume data" type="button" class="form-btn"/>
				</div>
				<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID"  value="{$data.seriesInstanceUID|escape}" />
			</form>
			{/if}
		</div><!-- / .detail-panel END -->
		<div class="fl-clr"></div>
	</div>
	<!-- / Series detail END -->

	<div id="tagArea">
		Tags: <span id="series-tags">Loading Tags...</span>
		{if $smarty.session.personalFBFlg==1}<a href="#" id="edit-tag">(Edit)</a>{/if}
	</div>

	<div class="al-r ">
		<p class="pagetop"><a href="#page">page top</a></p>
	</div>
{/if}
</div><!-- / .tab-content END -->

<!-- darkroom button -->
{include file='darkroom_button.tpl'}

{include file="footer.tpl"}