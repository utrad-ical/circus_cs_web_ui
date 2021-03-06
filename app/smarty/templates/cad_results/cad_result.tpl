{*
Smarty Template for General CAD Result.
*}
{capture name="require"}
js/cad_result.js
css/darkroom.css
js/edit_tags.js
jq/jquery.blockUI.js
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
jq/jquery.mousewheel.js
js/jquery.imageviewer.js
{$requiringFiles}
{/capture}
{capture name="extra"}
<script type="text/javascript">
circus.jobID = {$cadResult->job_id};
circus.userID = "{$smarty.session.userID|escape:javascript}";
circus.cadresult.status = {$cadResult->status|escape};
circus.cadresult.displays = {$displays|@json_encode};
circus.cadresult.seriesList = {$seriesList|@json_encode};
circus.cadresult.presentation = {$presentationParams|@json_encode};
circus.cadresult.attributes = {$attr|@json_encode};
circus.feedback.initdata = {$feedbacks|@json_encode};
circus.feedback.personalOpinions = {$personalOpinions|@json_encode};
circus.feedback.feedbackMode = "{$feedbackMode}";
circus.feedback.feedbackStatus = "{$feedbackStatus}";
circus.feedback.feedbackTemporary = {$feedbackTemporary|@json_encode};
circus.feedback.personalFeedbackAvail = "{$avail_pfb}";
circus.feedback.consensualFeedbackAvail = "{$avail_cfb}";
</script>

{foreach from=$extensions item=ext}
{$ext->head()}
{/foreach}
{/capture}
{include file="header.tpl" body_class="cad-result"
	require=$smarty.capture.require head_extra=$smarty.capture.extra}
{include file="darkroom_button.tpl"}

{if $currentUser->hasPrivilege('dataDelete')}
<div id="cad-result-admin-menu-pane">
<button id="cad-result-admin-menu" class="form-btn">Administration menu</button>
<ul id="cad-result-admin-menu-items">
<li><a href="#" id="invalidate-btn">Invalidate this CAD job</a></li>
</ul>
</div>
{/if}

<div id="cadResultTab" class="tabArea">
<ul>
	<li><a class="btn-tab btn-tab-active">CAD Result</a></li>
	{foreach from=$tabs item=tab}
	<li><a class="btn-tab">{$tab.label|escape}</a>
	{/foreach}
</ul>
</div>
<div class="tab-content">
<div class="cadResult">

{if $cadResult->status == constant('Job::JOB_INVALIDATED')}
<div id="invalidated-message" class="warning-message">
<strong>INVALID:</strong> This CAD job is marked as invalid.
</div>
{/if}

{if $warn_outdated}
<div class="warning-message">
<strong>Warning:</strong> Some of the images in processed series were
modified after this job was executed. This CAD result may be invalid.
</div>
{/if}

<h2>CAD Result [{$cadResult->Plugin->plugin_name|escape}
  v.{$cadResult->Plugin->version|escape} ID:{$cadResult->job_id}]</h2>
  <div class="headerArea">
    {$series->Study->Patient->patient_name|escape} ({$series->Study->Patient->patient_id})
    {$series->Study->age}{$series->Study->Patient->sex} /
    {$series->Study->study_date} ({$series->Study->study_id}) /
    {$series->Study->modality|escape}, {$series->series_description|escape} ({$series->series_number})
  </div>

  {if !$noFeedback}
  <form id="mode-form" method="get" action="cad_result.php">
  <div>
    <input type="hidden" name="jobID" value="{$cadResult->job_id|escape}" />
    <input type="radio" class="radio-to-button radio-to-button-l" name="feedbackMode" value="personal"
      label="Personal Mode" id="personal-mode" title="{$avail_pfb_reason|escape}" />
    <input type="radio" class="radio-to-button radio-to-button-l" name="feedbackMode" value="consensual"
      label="Consensual Mode" disabled="disabled" id="consensual-mode"
      title="{$avail_cfb_reason|escape}" />
  </div>
  </form>
  {/if}

  <div style="clear: both"></div>

{foreach from=$extensions item=ext}
{$ext->beforeBlocks()}
{/foreach}

{include file="block_layout.tpl"}

<div style="clear: both"></div>

{foreach from=$extensions item=ext}
{$ext->afterBlocks()}
{/foreach}

{if !$noFeedback}
<div id="register-pane">
<input id="register" type="button" value="Register Feedback" class="form-btn registration" disabled="disabled" /><br />
<ul id="register-error"></ul>
<ul id="register-message"></ul>
{if $feedbacks->status == 1}<p>Registered at: {$feedbacks->registered_at|escape}
  {if $feedbacks->is_consensual}(by {$feedbacks->entered_by|escape}){/if}</p>
  <p id="unregister_pane"><a id="unregister">Unregister</a></p>
{/if}
</div>
{/if}

<p id="tagArea">Tags: <span id="cad-tags">Loading Tags...</span> <a id="edit-cad-tags">(Edit)</a></p>

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