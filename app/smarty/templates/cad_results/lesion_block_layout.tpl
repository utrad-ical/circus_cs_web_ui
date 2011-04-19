{*
CIRCUS CS Template
Block layout template.

This template lays out the blocks of CAD results.
By default, each block is displayed as inline-block and
inserted into one div element.
Individual plugins can override this template for custom layouts.
*}
<div class="lesions">
{foreach from=$blocks item=block}
{include file="cad_results/lesion_block.tpl}
{/foreach}
</div>