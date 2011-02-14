<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

<title>CIRCUS CS {$smarty.session.circusVersion}</title>

<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="js/hover.js"></script>
<script language="javascript" type="text/javascript" src="js/viewControl.js"></script>
<script language="javascript" type="text/javascript" src="js/search_panel.js"></script>
<script language="javascript" type="text/javascript" src="js/list_tab.js"></script>
<script language="javascript" type="text/javascript" src="js/edit_tag.js"></script>


<link rel="shortcut icon" href="favicon.ico" />

<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body class="patient-list spot">
<div id="page">
	<div id="container" class="menu-back">
		<!-- ***** #leftside ***** -->
		<div id="leftside">
			{include file='menu.tpl'}
		</div>
		<!-- / #leftside END -->

		<div id="content">
			<h2 class="spot">Patient list</h2>
			
		<!-- ***** Search ***** -->
			<form name="" onsubmit="return false;">
				<input type="hidden" id="hiddenFilterPtID"   value="{$params.filterPtID|escape}" />
				<input type="hidden" id="hiddenFilterPtName" value="{$params.filterPtName|escape}" />
				<input type="hidden" id="hiddenFilterSex"    value="{$params.filterSex|escape}" />
				<input type="hidden" id="hiddenShowing"      value="{$params.showing|escape}" />
				{* {if $smarty.session.dataDeleteFlg}<input type="hidden" id="ticket" value="{$params.ticket|escape}" />{/if}*}

				{include file='patient_search_panel.tpl'}
			</form>
		<!-- / End of search -->
			
		<!-- / Search End -->

		<!-- ***** List ***** -->
			<div class="serp">
				{if $params.startNum>0 && $params.endNum>0}Showing {$params.startNum} - {$params.endNum} of {$params.totalNum} results{/if}
			</div>
			
			<table class="col-tbl" style="width:100%;">
				<thead>
					<tr>
						{* {if $smarty.session.dataDeleteFlg}<th>&nbsp;</th>{/if} *}
						<th>
							{if $params.orderCol == 'Patient ID'}<span style="color:#fff; font-size:10px">{if $params.orderMode == "ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfPatientList('Patient ID', '{if $params.orderCol == "Patient ID" && $params.orderMode == "ASC"}DESC{else}ASC{/if}');">Patient ID</a></span>
						</th>

						<th>
							{if $params.orderCol == 'Name'}<span style="color:#fff; font-size:10px">{if $params.orderMode == "ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfPatientList('Name', '{if $params.orderCol == "Name" && $params.orderMode == "ASC"}DESC{else}ASC{/if}');">Name</a></span>
						</th>

						<th>
							{if $params.orderCol == 'Sex'}<span style="color:#fff; font-size:10px">{if $params.orderMode == "ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfPatientList('Sex', '{if $params.orderCol == "Sex" && $params.orderMode == "ASC"}DESC{else}ASC{/if}');">Patient ID</a></span>
						</th>

						<th>
							{if $params.orderCol == 'Birth date'}<span style="color:#fff; font-size:10px">{if $params.orderMode == "ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfPatientList('Birth date', '{if $params.orderCol == "Birth date" && $params.orderMode == "ASC"}DESC{else}ASC{/if}');">Name</a></span>
						</th>

						<th style="width:3.5em;">Detail</th>
						{if $smarty.session.personalFBFlg}<th style="width:3.5em;">Tag</th>{/if}
					</tr>
				</thead>
				<tbody>
					{foreach from=$data item=item name=cnt}
						<tr id="row{$smarty.foreach.cnt.iteration}" {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>
						{* {if $smarty.session.dataDeleteFlg}<td><input type="checkbox" name="sidList[]" value="{$item[0]|escape}"></td>{/if} *}
							<td class="al-l">{$item[1]|escape}</td>
							<td class="al-l">{$item[2]|escape}</td>
							<td>{$item[3]|escape}</td>
							<td>{$item[4]|escape}</td>
							<td>
								<input name="" type="button" value="show" class="s-btn form-btn"
								       onclick="ShowStudyList({$smarty.foreach.cnt.iteration}, '{$item[5]|escape}')" />
							</td>
							{if $smarty.session.personalFBFlg}
							<td>
								<input id="tagBtn{$item[0]|escape}" type="button" value="tag" class="s-btn form-btn" onclick="EditTag(1, '{$item[0]|escape}', '')" title="{$item[6]|escape}" />
							</td>
							{/if}
						</tr>
					{/foreach}
				</tbody>
			</table>
			
			{* {if $smarty.session.dataDeleteFlg}
				<div class="mt10 ml10">
					<input type="button" value="delete" class="s-btn form-btn"  onclick="DeleteData('patient');" />
				</div>
			{/if} *}

			{* ------ Footer with page list --- *}
			<div id="serp-paging" class="al-c mt10">
				{if $params.maxPageNum > 1}
					{if $params.pageNum > 1}
						<div><a href="{$params.pageAddress}&pageNum={$params.pageNum-1}"><span style="color: red">&laquo;</span>&nbsp;Previous</a></div>
					{/if}

					{if $params.startPageNum > 1}
						<div><a href="{$params.pageAddress}&pageNum=1">1</a></div>
						{if $params.startPageNum > 2}<div>...</div>{/if}
					{/if}

					{section name=i start=$params.startPageNum loop=$params.endPageNum+1}
						{assign var="i" value=$smarty.section.i.index}

			    		{if $i==$params.pageNum}
							<div><span style="color: red" class="fw-bold">{$i}</span></div>
						{else}
							<div><a href="{$params.pageAddress}&pageNum={$i}">{$i}</a></div>
						{/if}
					{/section}

					{if $params.endPageNum < $params.maxPageNum}
						{if $params.maxPageNum-1 > $params.endPageNum}<div>...</div>{/if}
						<div><a href="{$params.pageAddress}&pageNum={$params.maxPageNum}">{$params.maxPageNum}</a></div>
					{/if}

					{if $params.pageNum < $params.maxPageNum}
						<div><a href="{$params.pageAddress}&pageNum={$params.pageNum+1}">Next&nbsp;<span style="color: red">&raquo;</span></a></div>
					{/if}
				{/if}
			</div>
			{* ------ / Hooter end --- *}
		<!-- / List End -->
			
			<div class="al-r">
				<p class="pagetop"><a href="#page">page top</a></p>
			</div>
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
