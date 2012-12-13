<div id="cad-downloader-pane">
{if $cad_downloader_title}<h2>{$cad_downloader_title|escape}</h2>{/if}
<table id="cad-downloader" class="col-tbl">
  <thead><tr><th>File</th><th>Size</th></tr></thead>
  <tbody>
{foreach from=$cad_downloader_items item="item"}
    <tr>
      <td class="cad-downloader-file"><a href="{$item.url|escape}">{$item.link|escape}</a></td>
      <td class="cad-downloader-size">{$item.size|escape|number_format}</td>
    </tr>
{/foreach}
  </tbody>
</table>
</div>