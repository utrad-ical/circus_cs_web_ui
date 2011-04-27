{*
CIRCUS CS template
Requires:
	layout.css
	radio-to-button.js
*}
<form>
<div>
{foreach from=$feedbackListenerParams.selections item=option}<input type="radio" name="radioCand" value="{$option.value|escape}" class="radio-to-button" label="{$option.label}" />{/foreach}
</div>
</form>