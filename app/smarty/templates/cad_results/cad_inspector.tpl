{*
  Smarty template for CAD inspector extension.
*}
<div class="cad-inspector">
{if $inspector_warn == 1}
<p class="caution">Error: CadInspector requires 'visibleGroups' parameter in the presentation.json file.</p>
{else}
<p class="caution">CAUTION: CAD inspector is for debugging use.</p>
{foreach from=$inspector_modules item=module}
{include file=$module}

{foreachelse}
<p class="caution">ERROR: No valid inspector module specified. Check presentation.json file.</p>
{/foreach}
{/if}
</div>