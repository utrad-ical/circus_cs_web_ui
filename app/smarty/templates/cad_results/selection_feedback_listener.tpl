{*
CIRCUS CS template
Requires:
	layout.css
	radio-to-button.js
*}
<form>
<div>
{if $feedbackMode == 'personal'}
{foreach from=$feedbackListenerParams.personal item=option}<input type="radio" name="radioCand" value="{$option.value|escape}" class="radio-to-button" label="{$option.label}" />{/foreach}
{else}
{foreach from=$feedbackListenerParams.consensual item=option}<input type="radio" name="radioCand" value="{$option.value|escape}" class="radio-to-button" label="{$option.label}"/>{/foreach}
{/if}
</div>
</form>