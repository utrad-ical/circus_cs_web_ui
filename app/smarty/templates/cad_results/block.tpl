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
<input type="hidden" class="display-id" name="display_id" value="{$display.display_id}" />
<div class="display-pane">
{displayPresenter}
</div><!-- /display-pane -->
<div class="feedback-pane">
{feedbackListener}
</div><!-- /feedback-pane -->
</div><!-- /result-block -->
