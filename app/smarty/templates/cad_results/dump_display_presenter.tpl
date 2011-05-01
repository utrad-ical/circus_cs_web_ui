<div class="dump-result-container">
<table class="dump-result">
{foreach from=$display key=key item=value}
  <tr>
    <th>{$key|escape}</th><td>{$value|escape}</td>
  </tr>
{/foreach}
</table>
</div>