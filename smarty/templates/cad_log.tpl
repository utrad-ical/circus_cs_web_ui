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
<link href="./css/monochrome.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/popup.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="js/radio-to-button.js"></script>
<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="cad-log" -->
</head>

<body class="cad-log">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->

		<!-- ***** TAB ***** -->
		<div class="tabArea">
			<ul>
				{if $param.mode=='today'}
					<li><a href="series_list.php?mode=today" class="btn-tab" title="Today's series">Today's series</a></li>
				{/if}
				<li><a href="" class="btn-tab" title="{if $param.mode=='today'}Today's CAD{else}CAD log{/if}" style="background-image: url(img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">{if $param.mode=='today'}Today's CAD{else}CAD log{/if}</a></li>

			</ul>
			{if $param.mode!='today'}<p class="add-favorite"><a href="" title="favorite"><img src="img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>{/if}
			</ul>

		</div><!-- / .tabArea END -->
		
		<div class="tab-content">
			{if $param.mode=='today'}
				<div id="todays_cad">
					<!-- <h2>Today's CAD</h2> -->
			{else}
				<div id="cad_log">
					<!-- <h2>CAD log</h2> -->
			{/if}

				<!-- ***** Search ***** -->
					<form name="" onsubmit="return false;">
						<input type="hidden" id="mode"                     value="{$param.mode}" />
						<input type="hidden" id="hiddenFilterPtID"         value="{$param.filterPtID}" />
						<input type="hidden" id="hiddenFilterPtName"       value="{$param.filterPtName}" />
						<input type="hidden" id="hiddenFilterSex"          value="{$param.filterSex}" />
						<input type="hidden" id="hiddenFilterAgeMin"       value="{$param.filterAgeMin}" />
						<input type="hidden" id="hiddenFilterAgeMax"       value="{$param.filterAgeMax}" />
						<input type="hidden" id="hiddenFilterModality"     value="{$param.filterModality}" />
						<input type="hidden" id="hiddenFilterCAD"          value="{$param.filterCAD}" />
						<input type="hidden" id="hiddenFilterVersion"      value="{$param.filterVersion}" />
						<input type="hidden" id="hiddenFilterCadID"        value="{$param.filterCadID}" />
						<input type="hidden" id="hiddenFilterTP"           value="{$param.filterTP}" />
						<input type="hidden" id="hiddenFilterFN"           value="{$param.filterFN}" />
						<input type="hidden" id="hiddenFilterPersonalFB"   value="{$param.personalFB}" />
						<input type="hidden" id="hiddenFilterConsensualFB" value="{$param.consensualFB}" />
						<input type="hidden" id="hiddenSrDateFrom"         value="{$param.srDateFrom}" />
						<input type="hidden" id="hiddenSrDateTo"           value="{$param.srDateTo}" />
						<input type="hidden" id="hiddenSrTimeTo"           value="{$param.srTimeTo}" />
						<input type="hidden" id="hiddenCadDateFrom"        value="{$param.cadDateFrom}" />
						<input type="hidden" id="hiddenCadDateTo"          value="{$param.cadDateTo}" />
						<input type="hidden" id="hiddenCadTimeTo"          value="{$param.cadTimeTo}" />
						<input type="hidden" id="hiddenShowing"            value="{$param.showing}" />

						<input type="hidden" id="orderMode"        value="{$param.orderMode}" />
						<input type="hidden" id="orderCol"         value="{$param.orderCol}" />

						{include file='cad_search_panel.tpl'}
					</form>
				<!-- / Search End -->

				<!-- ***** List ***** -->

				<div class="serp">
					Showing {$param.startNum} - {$param.endNum} of {$param.totalNum} results
				</div>
				<table class="col-tbl" style="width: 100%;">
					<thead>
						<tr>
							<th rowspan="2">
								{if $param.orderCol=='Patient ID'}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Patient ID', '{if $param.orderCol=='Patient ID' && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">Patient ID</a></span>
							</th>

							<th rowspan="2">
								{if $param.orderCol=='Name'}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Name', '{if $param.orderCol=='Name' && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">Name</a></span>
							</th>

							<th rowspan="2">
								{if $param.orderCol=='Age'}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Age', '{if $param.orderCol=='Age' && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">Age</a></span>
							</th>

							<th rowspan="2">
								{if $param.orderCol=='Sex'}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Sex', '{if $param.orderCol=='Sex' && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">Sex</a></span>
							</th>

							<th colspan="2">
								{if $param.orderCol=='Series'}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Series', '{if $param.orderCol=='Series' && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">Series</a></span>

							<th rowspan="2">
								{if $param.orderCol=='CAD'}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('CAD', '{if $param.orderCol=='CAD' && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">CAD</a></span>
							</th>

							<th rowspan="2">
								{if $param.orderCol=='CAD date'}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('CAD date', '{if $param.orderCol=='CAD date' && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">CAD date</a></span>
							</th>

							<th rowspan="2">Result</th>

							{if $param.mode=='today'}
								{if $smarty.session.colorSet == "admin"}
									<th colspan="2">Feedback</th>
								{elseif $smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1}
									<th rowspan="2">Personal<br />feedback</th>
								{/if}
							{else}
								{if $smarty.session.colorSet == "admin" || ($smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1)}
									<th colspan="3">Feedback</th>
								{else}
									<th colspan="2">Feedback</th>
								{/if}
							{/if}
						</tr>
						<tr>
							<th>Date</th>
							<th>Time</th>

							{if $param.mode=='today'}
								{if $smarty.session.colorSet == "admin"}
									<th>Personal</th>
									<th>Cons.</th>
								{/if}
							{else}
								{if $smarty.session.colorSet == "admin" || ($smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1)}
									<th>Personal</th>
								{/if}
								<th>TP</th>
								<th>FN</th>
							{/if}
						</tr>
					</thead>
					<tbody>
						{foreach from=$data item=item name=cnt}

							<tr id="row{$smarty.foreach.cnt.iteration}" {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>

								<td class="al-l"><a href="cad_log.php?filterPtID={$item[0]}">{$item[0]}</td>
								<td class="al-l">{$item[1]}</td>
								<td>{$item[2]}</td>
								<td>{$item[3]}</td>
								<td>{$item[4]}</td>
								<td>{$item[5]}</td>
								<td>{$item[6]}</td>
								<td>{$item[7]}</td>
								<!-- <td class="al-r">{$item[8]}</td> -->
								<td><input name="" type="button" value="show" class="s-btn form-btn" onclick="ShowCADResultFromCADLog('{$item[8]}', '{$item[9]}', '{$item[10]}', '{$item[11]}', {$smarty.session.personalFBFlg});" /></td>
								
								{if $param.mode=='today'}
									{if $smarty.session.colorSet == "admin"}
										<td>{$item[12]}</td>
										<td>{$item[13]}</td>
									{elseif $smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1}
										<td>{$item[12]}</td>
									{/if}
								{else}
									<td>{$item[12]}</td>
									<td>{$item[13]}</td>
									{if $smarty.session.colorSet == "admin" || ($smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1)}
										<td>{$item[14]}</td>
									{/if}
								{/if}
							</tr>
						{/foreach}
					</tbody>
				</table>
					
				{* ------ Hooter with page list --- *}
				<div id="serp-paging" class="al-c mt10">
					{if $param.maxPageNum > 1}
						{if $param.pageNum > 1}
							<div><a href="{$param.pageAddress}&pageNum={$param.pageNum-1}"><span style="color: red">&laquo;</span>&nbsp;Previous</a></div>
						{/if}

						{if $param.startPageNum > 1}
							<div><a href="{$param.pageAddress}&pageNum=1">1</a></div>
							{if $param.startPageNum > 2}<div>...</div>{/if}
						{/if}

						{section name=i start=$param.startPageNum loop=$param.endPageNum+1}
							{assign var="i" value=$smarty.section.i.index}

				    		{if $i==$param.pageNum}
								<div><span style="color: red" class="fw-bold">{$i}</span></div>
							{else}
								<div><a href="{$param.pageAddress}&pageNum={$i}">{$i}</a></div>
							{/if}
						{/section}

						{if $param.endPageNum < $param.maxPageNum}
							{if $param.maxPageNum-1 > $param.endPageNum}<div>...</div>{/if}
							<div><a href="{$param.pageAddress}&pageNum={$param.maxPageNum}">{$param.maxPageNum}</a></div>
						{/if}

						{if $param.pageNum < $param.maxPageNum}
							<div><a href="{$param.pageAddress}&pageNum={$param.pageNum+1}">Next&nbsp;<span style="color: red">&raquo;</span></a></div>
						{/if}
					{/if}
				</div>
				{* ------ / Hooter end --- *}
			
			<!-- / List -->
			</div> <!-- / CAD log End -->

			<div class="al-r fl-clr">
				<p class="pagetop"><a href="#page">page top</a></p>
			</div>

		</div><!-- / .tab-content END -->

<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>
