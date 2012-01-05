<h2>Processed Series Information</h2>
<script type="text/javascript">
{literal}
function inspector_view_series(sid)
{
	location.href = "../series_detail.php?sid=" + sid;
}
{/literal}
</script>
<table class="col-tbl">
  <thead>
    <tr>
      <th>Volume ID</th>
      <th>Modality</th>
      <th>Series ID</th>
      <th>Time</th>
      <th>Images</th>
      <th>Series description</th>
      <th>View</th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$cadResult->Series key=vid item=s}
    <tr>
      <td class="name themeColor">{$vid|escape}</td>
      <td>{$s->modality|escape}</td>
      <td>{$s->series_number|escape}</td>
      <td>{$s->series_date|escape} {$s->series_time|escape}</td>
      <td>{$s->image_number|escape}</td>
      <td>{$s->series_description|escape}</td>
      <td><input type="button" class="form-btn inspector-view-series" value="view" onclick="inspector_view_series({$s->sid})"/></td>
    </tr>
  {/foreach}
  </tbody>
</table>