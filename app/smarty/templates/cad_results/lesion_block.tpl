{*
CIRCUS CS template.
Renders one block.

Individual plugins can override this template to customize the layout of
blocks. But it may be better to override block_content.tpl
Blocks are made of one 'block content' (typically a lesion candidate) and
an associated 'evaluation listener'.
Classes of each div elements (evaluation-area, etc.) are important, so please
preserve them.

Required CSS file:
	layout.css
*}
<div class="lesion-block">
<input type="hidden" class="block-id" name="block_id" value="{$block.id}" />
<div class="block-content">
{blockContent}
</div><!-- /block-content -->
<div class="evaluation-area">
{evalListener}
</div><!-- /evaluation-area -->
</div><!-- /lesion-block -->
