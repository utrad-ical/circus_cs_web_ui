{capture name="extra"}
{literal}
<style type="text/css" media="all,screen">
#content h1 {
	font-size: 20px;
	margin-top: 25px;
	margin-bottom: -44px;
}

#content h2 {
	margin-top: 10px;
	margin-bottom: 10px;
	border-bottom: 2px solid #000;
}

.news, .plugin_execution, .help {
	margin-left: 15px;
}

.plugin_execution ul{
	margin-top: -3px;
}

.news li {
	list-style:none;
}
</style>
{/literal}
{/capture}

{include file="header.tpl" head_extra=$smarty.capture.extra body_class=home}

<div>
	<h1>Welcome to CIRCUS clinical server</h1>
	<span style="margin-left:10px;">User: {$smarty.session.userID} (from {$smarty.session.nowIPAddr})
	<span class="last_login">Last login: {$smarty.session.lastLogin} (from {$smarty.session.lastIPAddr})</span>
</div>

<h2>News</h2>
<div class="news">
	<ul>
		{foreach from=$newsData item=item}
			<li>{$item.plugin_name|escape}&nbsp;v.{$item.version|escape} was installed.&nbsp;({$item.install_dt|escape})</li>
		{/foreach}
	</ul>
</div>

<h2>Plug-in execution</h2>
<div class="plugin_execution">
	<h4>Total of plug-in execution: {$executionNum|escape} (since {$oldestExecDate|escape})</h4>

	{if $executionNum > 0}
		[Top {$cadExecutionData|@count}]</p>
		<ul>
			{foreach from=$cadExecutionData item=item}
				<li class="ml10">{$item.plugin_name|escape}&nbsp;v.{$item.version|escape}: {$item.cnt|escape}</li>
			{/foreach}
		</ul>
	{/if}
</div>

{if $smarty.session.personalFBFlg==1 && $smarty.session.latestResults!='none' && $latestHtml !=""}
	<h2>Latest results</h2>
	<div class="ml15">
		{$latestHtml}
	</div>
	<div class="fl-r"></div>
{/if}

<h2>Help</h2>
<div class="help mb20">
	<a href="manual/CIRCUS-CS1.0RC2_SimpleManual.pdf">Simple manual (in Japanese)</a> is available (PDF format, 2.2 MByte).
</div>

{include file="footer.tpl"}