<div id="tagArea" style="margin-top:20px; width:500px;">
	Tags:
	{foreach from=$params.tagArray item=item}
		{if $params.pluginType == 1}
			<a href="../cad_log.php?filterTag={$item[0]|escape}" title="Entered by {$item[1]|escape}">{$item[0]|escape}</a>&nbsp;
		{elseif $params.pluginType == 2}
			<a href="research_list.php?filterTag={$item[0]|escape}" title="Entered by {$item[1]|escape}">{$item[0]|escape}</a>&nbsp;
		{/if}
	{/foreach}
	{if $smarty.session.personalFBFlg}
		<a href="#" onclick="EditTag({if $params.pluginType == 1}4{else if $params.pluginType == 2}6{/if}, '{$params.execID|escape}', '../');">(Edit)</a>
	{/if}
</div>
