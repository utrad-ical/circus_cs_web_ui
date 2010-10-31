<div id="tagArea" style="margin-top:20px; width:500px;">
	Tags:
	{foreach from=$params.tagArray item=tag}
		{if $params.pluginType == 1}
			<a href="../cad_log.php?filterTag={$tag[0]|escape}" title="Entered by {$tag[1]|escape}">{$tag[0]|escape}</a>&nbsp;
		{elseif $params.pluginType == 2}
			<a href="research_list.php?filterTag={$tag[0]|escape}" title="Entered by {$tag[1]|escape}">{$tag[0]|escape}</a>&nbsp;
		{/if}
	{/foreach}
	{if $smarty.session.personalFBFlg}
		<a href="#" onclick="EditPluginTag({$params.execID|escape});">(Edit)</a>
	{/if}
</div>

{literal}
<script language="javascript">
<!-- 
function EditPluginTag(execID)
{
	var dstAddress = "../edit_tags.php?category=4&reference_id=" + execID;
	window.open(dstAddress,"Edit tags for CAD result", "width=400,height=250,location=no,resizable=no,scrollbars=1");
}

-->
</script>
{/literal}

