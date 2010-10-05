<div id="tagArea" style="margin-top:20px; width:500px;">
	Tags:
	{foreach from=$param.tagArray item=tag}
		{if $param.pluginType == 1}
			<a href="../cad_log.php?filterTag={$tag}">{$tag}</a>&nbsp;
		{elseif $param.pluginType == 2}
			<a href="research_list.php?filterTag={$tag}">{$tag}</a>&nbsp;
		{/if}
	{/foreach}
	{if $smarty.session.researchFlg==1}<a href="#" onclick="EditPluginTag({$param.execID}, {$param.pluginType});">(Edit)</a>{/if}
</div>

{literal}
<script language="javascript">
<!-- 
function EditPluginTag(execID, pluginType)
{
	var dstAddress = "../cad_results/edit_plugin_tag.php?execID=" + execID + "&pluginType=" + pluginType;
	window.open(dstAddress,"Edit tag", "width=400,height=250,location=no,resizable=no,scrollbars=1");
}

-->
</script>
{/literal}

