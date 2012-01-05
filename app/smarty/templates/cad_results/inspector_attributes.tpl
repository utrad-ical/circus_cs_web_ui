<h2>CAD Attributes</h2>
<table class="col-tbl">
  <thead>
    <tr><th>Key</th><th>Value</th></tr>
  </thead>
  <tbody>
    {foreach from=$attr key=key item=item}
    <tr><td class="name themeColor">{$key|escape}</td><td>{$item|escape}</td></tr>
    {/foreach}
  </tbody>
</table>