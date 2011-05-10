{*
Smarty Template for General CAD Result.
*}
{capture name="require"}
js/radio-to-button.js
js/cad_result.js
{foreach from=$displayPresenter->requiringFiles() item=file}{$file}
{/foreach}

{foreach from=$feedbackListener->requiringFiles() item=file}{$file}
{/foreach}
{/capture}
{capture name="extra"}
<script type="text/javascript">
data = {$displays|@json_encode};
feedbacks = {$feedbacks|@json_encode};
sort = {$sort|@json_encode};
</script>
{/capture}
{include file="header.tpl" body_class="cad-result"
	require=$smarty.capture.require head_extra=$smarty.capture.extra}
<div id="cadResultTab" class="tabArea">
<ul>
	<li><a class="btn-tab" href="#">CAD Result</a></li>
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


{include file="cad_results/block_layout.tpl"}

<div class="register-pane">
<input id="register" type="button" value="Register Feedback" class="registration" disabled="disabled" />
</div>

</div><!-- /cadResult -->
</div><!-- /tab-content -->
{include file="footer.tpl"}