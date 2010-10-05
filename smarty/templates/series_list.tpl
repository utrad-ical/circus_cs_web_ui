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
<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="series-list" -->
</head>

<body class="series-list">
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
				<li><a href="" class="btn-tab" title="{if $param.mode=='today'}Today's series{else}Series list{/if}" style="background-image: url(img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">{if $param.mode=='today'}Today's series{else}Series list{/if}</a></li>
				{if $param.mode=='today'}
					<li><a href="cad_log.php?mode=today" class="btn-tab" title="Today's CAD">Today's CAD</a></li>
				{/if}
			</ul>
			{if $param.mode!='today'}<p class="add-favorite"><a href="" title="favorite"><img src="img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>{/if}
		</div><!-- / .tabArea END -->
		
		<div class="tab-content">

			{if $param.mode=='today'}
				<div id="todays_series">
					<!-- <h2>Today's series</h2> -->
			{else}
				<div id="series_list">
					<!-- <h2>Series list</h2> -->
			{/if}
			
			<!-- ***** Search ***** -->
				<form name="" onsubmit="return false;">
					<input type="hidden" id="mode"                      value="{$param.mode}" />
					<input type="hidden" id="studyInstanceUID"          value="{$param.studyInstanceUID}" />
					<input type="hidden" id="hiddenFilterPtID"          value="{$param.filterPtID}" />
					<input type="hidden" id="hiddenFilterPtName"        value="{$param.filterPtName}" />
					<input type="hidden" id="hiddenFilterSex"           value="{$param.filterSex}" />
					<input type="hidden" id="hiddenFilterAgeMin"        value="{$param.filterAgeMin}" />
					<input type="hidden" id="hiddenFilterAgeMax"        value="{$param.filterAgeMax}" />
					<input type="hidden" id="hiddenFilterModality"      value="{$param.filterModality}" />
					<input type="hidden" id="hiddenFilterSrDescription" value="{$param.filterSrDescription}" />
					<input type="hidden" id="hiddenSrDateFrom"          value="{$param.srDateFrom}" />
					<input type="hidden" id="hiddenSrDateTo"            value="{$param.srDateTo}" />
					<input type="hidden" id="hiddenSrTimeTo"            value="{$param.srTimeTo}" />
					<input type="hidden" id="hiddenShowing"             value="{$param.showing}" />

					<input type="hidden" id="orderMode"        value="{$param.orderMode}" />
					<input type="hidden" id="orderCol"         value="{$param.orderCol}" />

					{include file='series_search_panel.tpl'}
				</form>
			<!-- / Search End -->

			<!-- ***** List ***** -->

				<div class="serp">
					Showing {$param.startNum} - {$param.endNum} of {$param.totalNum} results
				</div>
				
				<table class="col-tbl" style="width: 100%;">
					<thead>
						<tr>
							{foreach from=$colParam item=item}
								{if !($param.mode=='today' && $item.colName=='Date')}
									{if $item.colName!='Detail' && $item.colName!='CAD' && !($param.mode!='today' && $item.colName=='Time')}
										<th>
											{if $param.orderCol==$item.colName}<span style="color:#fff; font-size:10px">{if $param.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('{$item.colName}', '{if $param.orderCol==$item.colName && $param.orderMode=="ASC"}DESC{else}ASC{/if}');">{$item.colName}</a></span>
										</th>
									{else}
										<th>{$item.colName}</th>
									{/if}
								{/if}
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach from=$data item=item name=cnt}
							<tr id="row{$smarty.foreach.cnt.iteration}" {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>
								<td class="al-l"><a href="series_list.php?filterPtID={$item[2]}">{$item[2]}</a></td>
								<td class="al-l">{$item[3]}</td>
								<td>{$item[4]}</td>
								<td>{$item[5]}</td>
								{if $param.mode!='today'}<td>{$item[6]}</td>{/if}
								<td>{$item[7]}</td>
								<td>{$item[8]}</td>
								<td>{$item[9]}</td>
								<td class="al-r">{$item[10]}</td>
								<td class="al-l">{$item[11]}</td>
								<td>
									<input name="" type="button" value="show" class="s-btn form-btn" onclick="ShowSeriesDetail('{$smarty.session.colorSet}', '{$item[0]}', '{$item[1]}');"/>
								</td>

								{* ----- CAD column ----- *}
								<td class="al-l">
									{if $item[12] > 0}
										{* ----- pull-down menu ----- *}
										<select id="cadMenu{$smarty.foreach.cnt.iteration}" onchange="ChangeCADMenu({if $param.mode=='today'}'todaysSeriesList'{else}'seriesList'{/if},'{$smarty.foreach.cnt.iteration}', this.selectedIndex, {$smarty.session.execCADFlg})" style="width:100px;">
											{section name=i start=0 loop=$item[12]}
										
												{assign var="i"         value=$smarty.section.i.index}
												{assign var="optionFlg" value=0}
												{assign var="tmp"       value=$item[13][$i][3]*2+$item[13][$i][4]}

												{if $item[13][$i][2] == 1 || $item[13][$i][3] == 1}

													<option value="{$item[13][$i][0]}^{$item[13][$i][1]}^{$tmp}^{$item[13][$i][5]}" 
							
													{if $item[13][$i][2] && $optionFlg == 0 && $item[13][$i][6] == $item[10]}
												 		selected="selected"
										 				{assign var="optionFlg" value=1}
													{/if}
													>
													{$item[13][$i][0]} v.{$item[13][$i][1]}</option>
												{/if}
											{/section}
										</select>

										{if $smarty.session.execCADFlg == 1}
											<input type="button" id="execButton{$smarty.foreach.cnt.iteration}" name="execButton{$smarty.foreach.cnt.iteration}" value="Exec" onClick="RegistCADJob('{$smarty.foreach.cnt.iteration}', '{$item[0]}', '{$item[1]}');"
											{if $item[13][$selectedID][2] || $item[13][0][3] == 1 || $item[13][0][4] == 1} disabled="disabled"{/if}
								 			class="s-btn form-btn{if $item[13][$selectedID][2] || $item[13][0][3] == 1 || $item[13][0][4] == 1} form-btn-disabled{/if}" />
										{/if}

										<input type="button" id="resultButton{$smarty.foreach.cnt.iteration}" name="resultButton{$smarty.foreach.cnt.iteration}" value="Result" onClick="ShowCADResultFromSeriesList({$smarty.foreach.cnt.iteration}, '{$item[0]}', '{$item[1]}', {$smarty.session.personalFBFlg});" {if $item[13][0][3] == 0}disabled="disabled"{/if} class="s-btn form-btn {if $item[13][0][3] == 0} form-btn-disabled{/if}" />
										<div id="cadInfo{$smarty.foreach.cnt.iteration}">
											{if $item[13][0][5] != ''}
												Executed at: {$item[13][0][5]}
											{elseif $item[13][0][3] == 0 && $item[13][0][4] == 1}
												Registered in CAD job list
											{/if}
										</div>

									{else}
										&nbsp;
									{/if}
								</td>
								{* ----- End of CAD column ----- *}
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
			</div>	
		<!-- / Series list END -->

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
