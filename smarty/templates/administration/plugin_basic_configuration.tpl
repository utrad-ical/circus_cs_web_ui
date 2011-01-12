<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CIRCUS CS {$smarty.session.circusVersion}</title>
<!-- InstanceEndEditable -->

<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<link rel="shortcut icon" href="../favicon.ico" />

<script language="Javascript">;
<!--
{literal}

function ShowPluginConfDetail(){

	$.post("show_executable_cad_list.php",
			{ modality:  $("#modalityList").val()},
			  function(data){

				var executableHtml = "";
				var hiddenHtml = "";

				for(var i=0; i<data.executableList.length; i++)
				{
					executableHtml += "<option>" + data.executableList[i] + "</option>";
				}

				for(var i=0; i<data.hiddenList.length; i++)
				{
					hiddenHtml += "<option>" + data.hiddenList[i] + "</option>";
				}

				$("#executableList").html(executableHtml);
				$("#hiddenList").html(hiddenHtml);

				$("#executableStr").val(data.executableList.join('^'));
				$("#hiddenStr").val(data.hiddenList.join('^'));
				
				$("#pluginConfDetail").show();

		  }, "json");

}


function buttonStyle(mode)
{
	var leftBox = document.getElementById('executableList');
	var rightBox = document.getElementById('hiddenList');

	if(mode == 1)  // left box
	{
		rightBox.selectedIndex = -1;

		if(leftBox.options.length > 0)  $("#rightButton").removeAttr("disabled");
		else							$("#rightButton").attr("disabled", "disabled");

		$("#leftButton").attr("disabled", "disabled");

		for(var i=0; i<leftBox.options.length; i++)
		{
			if(leftBox.options[i].selected)
			{
				if(i!=0)							$("#upButton").removeAttr("disabled");
				else								$("#upButton").attr("disabled", "disabled");
			
				if(i!=(leftBox.options.length-1))	$("#downButton").removeAttr("disabled");
				else								$("#downButton").attr("disabled", "disabled");
			}
		}
	}
	else  // right box
	{
		leftBox.selectedIndex = -1;

		if(rightBox.options.length > 0)		$("#leftButton").removeAttr("disabled");
		else								$("#leftButton").attr("disabled", "disabled");

		$("#rightButton").attr("disabled", "disabled");

		$("#upButton").attr("disabled", "disabled");
		$("#downButton").attr("disabled", "disabled");
	}
}

function itemMove(mode)
{
	$("#saveBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("#resetBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("messageArea").html('&nbsp;');
	
	var leftBox = document.getElementById('executableList');
	var rightBox = document.getElementById('hiddenList');
	
	var fromBox, toBox;
	
	if(mode == 1)
	{
		fromBox = leftBox;
		toBox = rightBox;
	}
	else if(mode == -1)
	{
		fromBox = rightBox;
		toBox = leftBox;
	}

    if((fromBox != null) && (toBox != null))
	{
		while ( fromBox.selectedIndex >= 0 )
		{
			var newOption = new Option();
			newOption.text = fromBox.options[fromBox.selectedIndex].text; 
			newOption.value = fromBox.options[fromBox.selectedIndex].value;
			toBox.options[toBox.length] = newOption;
			fromBox.remove(fromBox.selectedIndex);
		}

		if(mode == 1)
		{
			$("#upButton").attr("disabled", "disabled");
			$("#downButton").attr("disabled", "disabled");
			if(fromBox.length < 1)  $("#rightButton").attr("disabled", "disabled");
		}
		else if(mode == -1)
		{
			if(fromBox.length < 1) $("#leftButton").attr("disabled", "disabled");
		}	

	}
	return false;
}

function optionMove(mode)
{
	$("#saveBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("#resetBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("messageArea").html('&nbsp;');

	var opt = document.getElementById('executableList');
	for(var i=0;i<opt.options.length;i++)
	if(opt.options[i].selected) break;
	var tmpOption = opt.removeChild(opt.options[i]);
	opt.insertBefore(tmpOption,opt.options[i+mode]);

	buttonStyle(1);
}

function SaveSettings()
{
	if(confirm('Save changed settings?'))
	{
		var leftBox = document.getElementById('executableList');
		var rightBox = document.getElementById('hiddenList');
		
		var executableStr = "";
		var hiddenStr = "";

		for(var i=0; i<leftBox.options.length; i++)
		{
			if(i > 0)  executableStr += "^";
			executableStr += leftBox.options[i].text;
		}
		
		for(var i=0; i<rightBox.options.length; i++)
		{
			if(i > 0)  hiddenStr += "^";
			hiddenStr += rightBox.options[i].text;
		}

		$.post("save_plugin_configuration.php",
			{ executableStr:  executableStr,
			  hiddenStr: hiddenStr},
			  function(data){

				if(data.errorFlg==0)
				{
					$("#executableStr").val(executableStr);
					$("#hiddenStr").val(hiddenStr);
				}

				$("#messageArea").html(data.message);

		  }, "json");
	}
}

function ResetSettings()
{
	var executableHtml = "";
	var hiddenHtml = "";

	var executableList = $("#executableStr").val().split('^');
	var hiddenList = $("#hiddenStr").val().split('^');

	for(var i=0; i<executableList.length; i++)
	{
		executableHtml += "<option>" + executableList[i] + "</option>";
	}

	for(var i=0; i<hiddenList.length; i++)
	{
		hiddenHtml += "<option>" + hiddenList[i] + "</option>";
	}

	$("#executableList").html(executableHtml);
	$("#hiddenList").html(hiddenHtml);
}



-->
{/literal}

</script>

<!-- InstanceBeginEditable name="head" -->
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />

{literal}
<style type="text/css">

div.line{
	margin-top: 10px;
	margin-bottom: 10px;
	border-bottom: solid 2px #8a3b2b;
}

#execControlArea td{
	padding-left:15px;
	padding-bottom:10px;
}

#execControlArea th{
	padding-left:15px;
}

</style>
{/literal}

<!-- InstanceEndEditable -->
</head>

<!-- InstanceParam name="class" type="text" value="home" -->
<body class="spot">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->
			<h2>Basic configuration for plug-ins</h2>

			<form id="form1" name="form1">
				<input type="hidden" id="ticket" name="ticket" value="{$params.ticket|escape}" />
				<div id="field">
					<span style="font-weight:bold;">Main modality:</span>
					<select id="modalityList">
						{foreach from=$params.modalityList item=item}
							<option value="{$item}">{$item}</option>
						{/foreach}
					</select>&nbsp;
					<input type="button" id="applyBtn" class="form-btn" value="apply" onClick="ShowPluginConfDetail();">
				</div>

				<div class="line"></div>

				<div id="pluginConfDetail" style="display:none;">	

					<input type="hidden" id="executableStr" name="executableStr" value="">
					<input type="hidden" id="hiddenStr"     name="hiddenStr"     value="">

					<div id="messageArea">&nbsp;</div>

					<table id="execControlArea">
						<tr>
							<th style="font-size:14px;">Executable</td>
							<th></th>
							<th style="font-size:14px;">Hidden</td>
						</tr>

						<tr>
							<td valign=top>
								<select id="executableList" size="10" onchange="buttonStyle(1)" style="width:200px;"></select>
							</td>
					
							<td valign=middle>
								<input type="button" value="&rarr;" id="rightButton" disabled onclick="itemMove(1)" style="width:24px;height:24px;font-weight:bold;"><br/><br/>
								<input type="button" value="&larr;" id="leftButton" disabled onclick="itemMove(-1)" style="width:24px;height:24px;font-weight:bold;">
							</td>

							<td valign=top>
								<select id="hiddenList" size="10"  onchange="buttonStyle(2)" style="width:200px;"></select>
							</td>
						</tr>

						<tr>
							<td align=center>
								<input type="button" value="&uarr;" id="upButton" disabled onclick="optionMove(-1)" style="width:24px;height:24px;font-weight:bold;">&nbsp;&nbsp;
							<input type="button" value="&darr;" id="downButton" disabled onclick="optionMove(1)" style="width:24px;height:24px;font-weight:bold;">
							</td>
							<td colspan=2></td>
						</tr>

						<tr>
							<td colspan="3" align="center">
								<input type="button" id="saveBtn" class="form-btn form-btn-disabled" value="save" disabled="disabled" onclick="SaveSettings()" />&nbsp;
								<input type="button" id="resetBtn" class="form-btn form-btn-disabled" value="reset" disabled="disabled" onclick="ResetSettings()" />
							</td>
						</tr>
					</table>
				</div>
			</form>


<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>

