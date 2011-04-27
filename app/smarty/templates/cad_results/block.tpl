{*
CIRCUS CS template.
Renders one block.

Individual plugins can override this template to customize the layout of
blocks. But it may be better to override display.tpl.
Blocks are made of one 'CAD display' (typically a lesion candidate) and
an associated 'feedback listener'.
Classes of each div elements (feedback-area, etc.) are important, so please
preserve them.

Required CSS file:
	layout.css
*}
<div class="result-block">
<input type="hidden" class="display-id" name="display_id" value="{$display.id}" />
<div class="display-area">
{displayPresenter}
</div><!-- /display-area -->
<div class="feedback-area">
{feedbackListener}
</div><!-- /feedback-area -->
</div><!-- /result-block -->
