{*
Smarty Template for General CAD Result.
*}
{capture name="require"}
js/radio-to-button.js
js/cad_result.js
css/darkroom.css
{$requiringFiles}
{/capture}
{capture name="extra"}
<script type="text/javascript">
circus.userID = "{$smarty.session.userID|escape:javascript}";
circus.cadresult.displays = {$displays|@json_encode};
circus.cadresult.sort = {$sort|@json_encode};
circus.cadresult.studyUID = "{$series->Study->study_instance_uid|escape:javascript}";
circus.cadresult.seriesUID = "{$series->series_instance_uid|escape:javascript}";
circus.cadresult.seriesNumImages = {$series->image_number|escape:javascript};
circus.feedback.initdata = {$feedbacks|@json_encode};
circus.feedback.feedbackMode = "{$feedbackMode}";
</script>
{/capture}
{include file="header.tpl" body_class="cad-result"
	require=$smarty.capture.require head_extra=$smarty.capture.extra}
{include file="darkroom_button.tpl"}
<div id="cadResultTab" class="tabArea">
<ul>
	<li><a class="btn-tab btn-tab-active" href="#">CAD Result</a></li>
	{foreach from=$tabs item=tab}
	<li><a class="btn-tab" href="#">{$tab.label|escape}</a>
	{/foreach}
</ul>
</div>
<div class="tab-content">
<div class="cadResult">
<h2>CAD Result [{$cadResult->Plugin->plugin_name|escape}&nbsp;
  v.{$cadResult->Plugin->version|escape} ID:{$cadResult->job_id}]</h2>
  <div class="headerArea">
    {$series->Study->Patient->patient_name|escape} ({$series->Study->Patient->patient_id})
    {$series->Study->Patient->age()}{$series->Study->Patient->sex} /
    {$series->Study->study_date} ({$series->Study->study_id}) /
    {$series->Study->modality|escape}, {$series->series_description|escape} ({$series->series_number})
  </div>
  <div>
    <input type="radio" class="radio-to-button-l" name="mode" value="1" label="Personal Mode"/>
    <input type="radio" class="radio-to-button-l" name="mode" value="2" label="Consensual Mode"/>
  </div>
  <div style="clear: both"></div>
  {if $sorter.visible}
  <div id="sorterArea" style="text-align: right;"><form name="sorter">
    Sort:
    <select id="sorter" name="sortKey">
      {foreach from=$sorter.options item=sort}
      <option value="{$sort.key|escape}">{$sort.label|escape}</option>
      {/foreach}
    </select>
    <input type="radio" name="sortOrder" value="asc" />Asc.&nbsp;
    <input type="radio" name="sortOrder" value="desc" />Desc.
  </form></div><!-- /sorter -->
  {/if}

{include file="block_layout.tpl"}

<div class="register-pane">
<input id="register" type="button" value="Register Feedback" class="registration" disabled="disabled" />
</div>
<form>
<input type="hidden" id="job-id" value="{$cadResult->job_id|escape}" />
</form>
</div><!-- /cadResult -->

{* Additional Tabs *}
{foreach from=$tabs item=tab}
<div style="display: none">
{include file=$tab.template}
</div>
{/foreach}

</div><!-- /tab-content -->

{include file="footer.tpl"}