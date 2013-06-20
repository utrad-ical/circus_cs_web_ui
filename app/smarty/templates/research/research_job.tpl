{capture name="require"}
jq/ui/jquery-ui.min.js
js/jquery.daterange.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{capture name="extra"}
<script language="Javascript">;
<!--
{literal}

function RegistResearchJob()
{
	var checkCnt = $("input[name='cadCheckList[]']:checked").length;

    var checks = $("input[name='cadCheckList[]']:checked");
    var checkedCadIdStr = checks.map(function() {
      return $(this).val();
    }).get().join('^');

	$.post("research_job_registration.php",
          { pluginName: $("#researchJob select[name='researchMenu'] option:selected").text(),
			checkedCadIdStr: checkedCadIdStr},
			function(data){
				$("#registMessage").html(data.message).show();
		 }, "json");
}

function ChangeResearchMenu()
{
	var researchMenuStr = $("#researchJob select[name='researchMenu'] option:selected").val().split("/");

	var cadOptionStr = "";
	var versionOptionStr = "";

	if(researchMenuStr != "")
	{
		for(var j=0; j<researchMenuStr.length; i++)
		{
			var tmpStr = researchMenuStr[j].split("^");
			var versionStr  = researchMenuStr[j].substr(tmpStr[0].length+1);

			if(tmpStr[j] != '')
			{
				cadOptionStr += '<option value="' + versionStr + '">' + tmpStr[0] + '</option>';
			}

			if(j==0)
			{
				for(var i=1; i<tmpStr.length; i++)
				{
					versionOptionStr += '<option value="' + tmpStr[i] + '">' + tmpStr[i] + '</option>';
				}
			}
		}
	}

	$("#cadMenu").html(cadOptionStr);
	$("#versionMenu").html(versionOptionStr);
}


function ChangeCadMenu()
{
	var versionStr = $("#cadMenu option:selected").val().split("^");

	var optionStr = "";

	if(versionStr != "")
	{
		for(var i=0; i<versionStr.length; i++)
		{
			if(versionStr[i] != 'all')
			{
				optionStr += '<option value="' + versionStr[i] + '">' + versionStr[i] + '</option>';
			}
		}
	}

	$("#versionMenu").html(optionStr);
}

function ResetCondition()
{
    $("#researchJob .srDateRange").daterange('option', 'kind', 'all');
    $("#researchJob .cadDateRange").daterange('option', 'kind', 'all');
	$("#researchJob input[name^='filterAge'], #researchJob input[name='filterTag']").removeAttr("value");
	$("#researchJob select[name='researchMenu'], #cadMenu, #versionMenu").children().removeAttr("selected");
	//$("#researchJob input[name='filterSex']").removeAttr("disabled").filter(function(){ return ($(this).val() == "all") }).attr("checked", true);
	ChangeResearchMenu();
	$("#cadList, #registMessage").hide();
}

function ShowCadList()
{
	$.post("show_cad_list.php",
       		{ cadName:     $("#cadMenu option:selected").text(),
			  version:     $("#versionMenu").val(),
			  cadDateFrom: $("#researchJob .cadDateRange").daterange('option', 'fromDate'),
			  cadDateTo:   $("#researchJob .cadDateRange").daterange('option', 'toDate'),
			  srDateFrom:  $("#researchJob .srDateRange").daterange('option', 'fromDate'),
			  srDateTo:    $("#researchJob .srDateRange").daterange('option', 'toDate'),
	          filterSex:   $("#researchJob input[name='filterSex']:checked").val(),
			  filterAgeMin:  $("#researchJob input[name='filterAgeMin']").val(),
              filterAgeMax:  $("#researchJob input[name='filterAgeMax']").val(),
			  filterTag:  $("#researchJob input[name='filterTag']").val()},

 			  function(data){

				var tableHtml = "";

				for(i=0; i<data.length; i++)
			 	{
					tableHtml += '<tr';
					if(i%2==1)  tableHtml += ' class="column"';
					tableHtml += '><td><input type="checkbox" name="cadCheckList[]"'
                              +  ' value="' + data[i].job_id + '" checked="checked" /></td>'
                              +  '<td class="al-l">' + data[i].job_id + '</td>'
                              +  '<td class="al-l">' + data[i].patient_id + '</td>'
							  +  '<td class="al-l">' + data[i].patient_name + '</td>'
							  +  '<td>' + data[i].age + '</td>'
							  +  '<td>' + data[i].sex + '</td>'
							  +  '<td>' + data[i].series_date + '</td>'
							  +  '<td>' + data[i].series_time + '</td>'
							  +  '<td>' + data[i].executed_at + '</td>'
							  +  '</tr>';
			  	}

			  	$("#cadList .col-tbl tbody").empty().append(tableHtml);
     		 	$("#cadList, #execution").show();
			  }, "json");
}

//function SearchResearchList(orderCol, orderMode, pageNum)
//{
//	$.post("create_research_list.php",
//     		{ pluginName:  $("#researchList select[name='researchMenu'] option:selected").text(),
//			  resDateFrom: $("#researchList input[name='resDateFrom']").val(),
//			  resDateTo:   $("#researchList input[name='resDateTo']").val(),
//			  resTag:      $("#researchList input[name='resTag']").val(),
//			  orderCol:    orderCol,
//			  orderMode:   orderMode,
//			  pageNum:     pageNum,
//			  showing:     $("#researchList select[name='showing'] option:selected").val()},
//
//			  function(data){
//
//			  	$("#researchList .serp").empty().html(data.headder);
//			  	$("#researchList .col-tbl thead").empty().append(data.thead);
//			  	$("#researchList .col-tbl tbody").empty().append(data.tbody);
//			  	$("#serp-paging").html(data.footer);
//			  }, "json");
//}

//function ResetSearchBlock()
//{
//	$("#researchList input[name*='researchDate'], #researchList input[name='filterResearchTag']").removeAttr("value");
//	$("#researchList select[name='researchMenu'], #researchList select[name='showing']").children().removeAttr("selected");
//}


//function ShowResearchJob()
//{
//	$("#researchList,#researchResult,#researchListTab,#researchResultTab").hide();
//	$("#researchJob,#researchJobTab").show();
//	$('#container').height( $(document).height() - 10 );
//
//}

//function ShowResearchList()
//{
//	if($("#researchList .serp").html() == "")
//	{
//		SearchResearchList('', '', 1);
//	}
//	$("#researchJob,#researchResult,#researchJobTab,#researchResultTab").hide();
//	$("#researchList, #researchListTab").show();
//	$('#container').height( $(document).height() - 10 );
//}

$(function() {

	$("#researchCondition .srDateRange").daterange({ icon: "../images/calendar_view_month.png"});
	$("#researchCondition .cadDateRange").daterange({ icon: "../images/calendar_view_month.png"});

});

{/literal}
-->
</script>
{/capture}
{include file="header.tpl" body_class="research"
	head_extra=$smarty.capture.extra require=$smarty.capture.require}

<div class="tabArea">
	<ul>
		<li><a href="{if $params.srcList!="" && $smarty.session.listAddress!=""}{$smarty.session.listAddress}{else}research_list.php{/if}" class="btn-tab" title="Research list">Research list</a></li>
		<li><a href="#" class="btn-tab" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">Research job</a></li>
	</ul>
</div><!-- / .tabArea END -->

<div class="tab-content">

	<div id="researchJob">

		<p id="registMessage" style="color: orange; display:none; margin-bottom: 10px">&nbsp;</p>

		<form name="form1" onsubmit="return false;">

<!-- ***** Research Conditions ***** -->

		<div id="researchCondition" class="search-panel">
			<h3>Step 1: Set condition</h3>
			<div style="padding: 20px">
				<table class="search-tbl">
					<tr>
						<th style="width: 8.0em;"><span class="trim01">Research</span></th>
						<td style="width: 200px;">
							<select id="researchMenu" name="researchMenu" style="width: 180px;" onchange="ChangeResearchMenu();">
								{foreach from=$pluginList item=item}
									<option value="{$item[0]}">{$item[0]} v.{$item[1]}</option>
								{/foreach}
							</select>
						</td>
						<td style="width: 5.5em;">&nbsp;</td>
						<td style="width: 150px;">&nbsp;</td>
					</tr>
					<tr>
						<th><span class="trim01">CAD</span></th>
						<td>
							<select id="cadMenu" name="cadMenu" style="width: 120px;" onchange="ChangeCadMenu();">
								{foreach from=$cadList item=item}
									<option value="{$item[1]}">{$item[0]}</option>
								{/foreach}
							</select>
						</td>
						<th><span class="trim01">Version</span></th>
						<td>
							<select id="versionMenu" name="versionMenu" style="width: 60px;">
								{foreach from=$versionList item=item}
									<option value="{$item}">{$item}</option>
								{/foreach}
							</select>
						</td>
						<td colspan=2></td>
					</tr>
					<tr>
		     			<th><span class="trim01">Sex</span></th>
		    			 <td>
							<label><input name="filterSex" type="radio" value="M" />male</label>
							<label><input name="filterSex" type="radio" value="F" />female</label>
							<label><input name="filterSex" type="radio" value="all" checked="checked" />all</label>
						</td>
						<th><span class="trim01">Age</span></th>
					  	<td>
							<input name="filterAgeMin" type="text" size="4" value="" />
							-&nbsp;
							<input name="filterAgeMax" type="text" size="4" value="" />
						</td>
					</tr>
					<tr>
			            <th><span class="trim01">CAD date</span></th>
						<td colspan="3"><span class="cadDateRange"></span></td>
					</tr>
					<tr>
   						<th><span class="trim01">Series date</span></th>
						<td colspan="3"><span class="srDateRange"></span></td>
					</tr>
					<tr>
   						<th><span class="trim01">Tag</span></th>
						<td colspan="3"><input name="filterTag" type="text" style="width: 200px;" value="" /></td>
					</tr>
				</table>
				<div class="al-l" style="margin-top: 10px; margin-left: 20px; width: 100%;">
					<input name="" type="button" value="Show list" class="form-btn" style="width: 100px;" onclick="ShowCadList();" />
					<input name="" type="button" value="Reset" class="form-btn" style="width: 100px;" onclick="ResetCondition();" />
				</div>
			</div>
		</div><!-- / .search-panel END -->
		<!-- / Research Conditions -->

		<div id="cadList" style="display:none;">

			<h3 style="margin-bottom: 5px">Step 2: Select CAD results</h3>

			<div style="height: 300px; overflow-x: hidden; overflow-y: scroll;">

				<table class="col-tbl" style="width: 98%; margin-bottom: 10px;">
					<thead>
						<tr>
							<th rowspan="2">&nbsp;</th>
							<th rowspan="2">Job ID</th>
							<th rowspan="2">Patient ID</th>
							<th rowspan="2">Name</th>
							<th rowspan="2">Age</th>
							<th rowspan="2">Sex</th>
							<th colspan="2">Series</th>
							<!-- <th rowspan="2">CAD</th> -->
							<th rowspan="2">CAD date</th>
							<!-- <th colspan="2">Feedback</th> -->
						</tr>
						<tr>
							<th>Date</th>
							<th>Time</th>
							<!-- <th>TP</th> -->
							<!-- <th>FN</th> -->
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>

		<div id="execution" style="margin-top: 10px; display:none;">
			<h3 style="margin-bottom: 5px;">Step 3: Execution</h3>
			<div class="al-l" style="width: 100%; margin-left: 20px; margin-top: 10px;">
				<input name="" type="button" value="Execute" class="form-btn" style="width: 100px" onclick="RegistResearchJob();" />
			</div>
		</div>

		</form>
	</div><!-- / #researchJob END -->

</div><!-- / .tab-content END -->
{include file="footer.tpl"}
