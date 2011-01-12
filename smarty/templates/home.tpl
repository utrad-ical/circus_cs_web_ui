<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/base.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CIRCUS CS {$smarty.session.circusVersion}</title>
<!-- InstanceEndEditable -->

<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="js/hover.js"></script>
<script language="javascript" type="text/javascript" src="js/viewControl.js"></script>

<link rel="shortcut icon" href="favicon.ico" />
<!-- InstanceBeginEditable name="head" -->
<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />

{literal}
<style type="text/css" media="all,screen">
<!--

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


.news, .cad_execution, .help {
	margin-left: 15px;
}

.cad_execution ul{
	margin-top: -3px;
}

.news li {
	list-style:none;
}

-->
</style>
{/literal}

<!-- InstanceEndEditable -->
</head>

<!-- InstanceParam name="class" type="text" value="home" -->
<body class="home">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->
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

			<h2>CAD execution</h2>
			<div class="cad_execution">
				<h4>Total of CAD execution: {$executionNum|escape} (since {$oldestExecDate|escape})</h4>

				{if $executionNum > 0}
					[Top {$cadExecutionData|@count}]</p>
					<ul>
						{foreach from=$cadExecutionData item=item}
							<li class="ml10">{$item.plugin_name|escape}&nbsp;v.{$item.version|escape}: {$item.cnt|escape}</li>
						{/foreach}
					</ul>
				{/if}
			</div>

			{if $smarty.session.personalFBFlg==1 && $smarty.session.latestResults!='none'}
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

<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>

