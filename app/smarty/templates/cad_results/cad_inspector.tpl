{*
  Smarty template for CAD inspector extension.
*}
<div class="cad-inspector">
<p class="caution">CAUTION: CAD inspector is for debugging use.</p>
{foreach from=$inspector_modules item=module}
{include file=$module}

{foreachelse}
<p class="caution">ERROR: No valid inspector module specified. Check presentation.json file.</p>
{/foreach}
</div>