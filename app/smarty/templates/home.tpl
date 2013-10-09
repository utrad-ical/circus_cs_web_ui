{capture name="extra"}
{literal}
<style type="text/css">
#title {
	background-color: #eee;
	border: 1px solid #ccc;
	padding: 1em;
	border-radius: 8px;
	margin-bottom: 1em;
}

#content h1 {
	background-color: transparent;
	font-size: 20px;
	height: auto;
	text-shadow: 0 0 4px white;
}

#side {
	width: 400px;
	float: right;
}

#center {
	width: 580px;
	background-color: #888;
}

.module {
	margin-bottom: 10px;
	margin-left: 10px;
}

.module h2 {
	margin: 0 0 10px -10px;
	border-bottom: 1px solid black;
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

<div id="title">
<h1 class="themeColor">Welcome to CIRCUS Clinical Server</h1>
<p>User: {$currentUser->user_id|escape} (from {$smarty.server.REMOTE_ADDR})</p>
<p>Last login: {$smarty.session.lastLogin|escape} (from {$smarty.session.lastIPAddr})</p>
</div>

{foreach from=$modules item=module}

{/foreach}

<div id="side">
	<div class="module news">
	<h2>News</h2>
		<ul>
			{foreach from=$plugins item=item}
			<li><strong>{$item.plugin_name|escape}&nbsp;v.{$item.version|escape}</strong> was installed.&nbsp;({$item.install_dt|escape})</li>
			{/foreach}
		</ul>
	</div>

	<div class="module plugin_execution">
	<h2>Plug-in Execution</h2>
		<h4>Total of plug-in execution: {$executionNum|escape} (since {$oldestExecDate|escape})</h4>

		{if $executionNum > 0}
			[Top {$cadExecutionData|@count}]</p>
			<ul>
				{foreach from=$cadExecutionData item=item}
					<li>{$item.plugin_name|escape}&nbsp;v.{$item.version|escape}: {$item.cnt|escape}</li>
				{/foreach}
			</ul>
		{/if}
	</div>
</div>

<div id="center">

<div id="top_message">
{$topMessage}
</div>


{if $smarty.session.personalFBFlg==1 && $smarty.session.latestResults!='none' && $latestHtml !=""}
<h2>Latest Results</h2>
<div style="margin-left: 15px; border: 1px solid red;">
	{$latestHtml}
</div>
{/if}
</div>


{include file="footer.tpl"}