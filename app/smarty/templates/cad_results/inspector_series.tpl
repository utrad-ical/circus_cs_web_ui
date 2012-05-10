<h2>Processed Series Information</h2>
<script type="text/javascript">
{literal}
function inspector_view_series(sid)
{
	location.href = "../series_detail.php?sid=" + sid;
}
function inspector_download_volume(series_uid, job_id, volume_id)
{
	circus.download_volume.openDialogForJob(series_uid, job_id, volume_id);
}
$.getScript(circus.totop + 'js/download_volume.js');
{/literal}
</script>
<table class="col-tbl">
  <thead>
    <tr>
      <th>Volume ID</th>
      <th>Modality</th>
      <th>Series</th>
      <th>Time</th>
      <th>Images</th>
      <th>Volume range</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$cadResult->ExecutedSeries item=es}{assign var=s value=$es->Series}
    <tr>
      <td class="name themeColor">{$es->volume_id|escape}</td>
      <td>{$s->modality|escape}</td>
      <td><strong>[{$s->series_number|escape}]</strong> {$s->series_description|escape}</td>
      <td>{$s->series_date|escape} {$s->series_time|escape}</td>
      <td>
        {$s->image_number|escape}
        {if $s->min_image_number != 1 || $s->max_image_number != $s->image_number}
        ({$s->min_image_number} - {$s->max_image_number})
        {/if}
      </td>
      <td>{imageRange z_org_img_num=$es->z_org_img_num image_delta=$es->image_delta image_count=$es->image_count}</td>
      <td>
        <input type="button" class="form-btn inspector-view-series" value="view" onclick="inspector_view_series({$s->sid})" />
        {if $currentUser->hasPrivilege('volumeDownload')}
        <input type="button" class="form-btn" value="download"
          onclick="inspector_download_volume('{$s->series_instance_uid|escape:javascript}', {$cadResult->job_id}, {$es->volume_id})" />
        {/if}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>