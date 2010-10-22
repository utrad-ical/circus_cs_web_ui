<?xml version="1.0" encoding="shift_jis"?>
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
<script language="javascript" type="text/javascript" src="js/search_panel.js"></script>
<script language="javascript" type="text/javascript" src="js/list_tab.js"></script>
<link rel="shortcut icon" href="favicon.ico" />
<!-- InstanceBeginEditable name="head" -->
<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="study-list" -->
</head>

<body class="study-list spot">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->
			<h2>Study list</h2>
			
		<!-- ***** Search ***** -->
			<form name="" onsubmit="return false;">
				<input type="hidden" id="mode"                 value="{$params.mode|escape}" />
				<input type="hidden" id="encryptedPtID"        value="{$params.encryptedPtID|escape}" />
				<input type="hidden" id="hiddenFilterPtID"     value="{$params.filterPtID|escape}" />
				<input type="hidden" id="hiddenFilterPtName"   value="{$params.filterPtName|escape}" />
				<input type="hidden" id="hiddenFilterSex"      value="{$params.filterSex|escape}" />
				<input type="hidden" id="hiddenFilterAgeMin"   value="{$params.filterAgeMin|escape}" />
				<input type="hidden" id="hiddenFilterAgeMax"   value="{$params.filterAgeMax|escape}" />
				<input type="hidden" id="hiddenFilterModality" value="{$params.filterModality|escape}" />
				<input type="hidden" id="hiddenStDateFrom"     value="{$params.stDateFrom|escape}" />
				<input type="hidden" id="hiddenStDateTo"       value="{$params.stDateTo|escape}" />
				<input type="hidden" id="hiddenStTimeTo"       value="{$params.stTimeTo|escape}" />
				<input type="hidden" id="hiddenShowing"        value="{$params.showing}" />
				{include file='study_search_panel.tpl'}
			</form>
		<!-- / Search End -->

		<!-- ***** List ***** -->
			<div class="serp">
				Showing {$params.startNum} - {$params.endNum} of {$params.totalNum} results
			</div>
			
			<table class="col-tbl" style="width: 100%;">
				<thead>
					<tr>
						<th>
							{if $params.orderCol=='Patient ID'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfStudyList('Patient ID', '{if $params.orderCol=="Patient ID" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Patient ID</a></span>
						</th>

						<th>
							{if $params.orderCol=='Name'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfStudyList('Name', '{if $params.orderCol=="Patient ID" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Name</a></span>
						</th>

						<th>
							{if $params.orderCol=='Age'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfStudyList('Age', '{if $params.orderCol=="Age" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Age</a></span>
						</th>

						<th>
							{if $params.orderCol=='Sex'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfStudyList('Sex', '{if $params.orderCol=="Sex" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Sex</a></span>
						</th>

						<th>
							{if $params.orderCol=='Study ID'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfStudyList('Study ID', '{if $params.orderCol=="Study ID" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Study ID</a></span>
						</th>

						<th>
							{if $params.orderCol=='Study date'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfStudyList('Study date', '{if $params.orderCol=="Study date" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Study date</a></span>
						</th>

						<th>Study time</th>

						<th>
							{if $params.orderCol=='Modality'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfStudyList('Modality', '{if $params.orderCol=="Modality" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Modality</a></span>
						</th>

						<th>Accession No.</th>
						<th>Detail</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$data item=item name=cnt}
						<tr id="row{$smarty.foreach.cnt.iteration}" {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>
							<td class="al-l"><a href="study_list.php?filterPtID={$item[1]|escape}">{$item[1]|escape}</td>
							<td class="al-l">{$item[2]|escape}</td>
							<td class="al-r">{$item[3]|escape}</td>
							<td>{$item[4]|escape}</td>
							<td class="al-r">{$item[5]|escape}</td>
							<td>{$item[6]|escape}</td>
							<td>{$item[7]|escape}</td>
							<td>{$item[8]|escape}</td>
							<td>{$item[9]|escape}</td>
							<td>
								<input name="" type="button" value="show" class="s-btn form-btn"
									   onclick="ShowSeriesList({$smarty.foreach.cnt.iteration}, '{$item[0]}');" />
							</td>
						</tr>
					{/foreach}
					</tr>
				</tbody>
			</table>
			
			{* ------ Hooter with page list --- *}
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
			
			<div class="al-r">
				<p class="pagetop"><a href="#page">page top</a></p>
			</div>
		<!-- / List End -->
<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>
