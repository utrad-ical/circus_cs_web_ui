<?xml version="1.0" encoding="shift_jis"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<title>Convert DICOM series to volume data</title>
<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>

{literal}
<script language="javascript">
<!-- 
	$(function() {
		$.post('dcm_to_vol_volume.php', 
               {studyInstanceUID:  $("#studyInstanceUID").val(),
                seriesInstanceUID: $("#seriesInstanceUID").val()},
		        function(ret){
			    	if(ret == 0)
		   		    {
						var address = 'download_volume.php?studyInstanceUID=' + $("#studyInstanceUID").val()
                                    + '&seriesInstanceUID=' + $("#seriesInstanceUID").val();
					    location.replace(address);
					}
					else
					{
						alert('Fail to create volume data!!');
					}
			  	}, "json");
		}); 
--> 
</script>

<link rel="shortcut icon" href="../favicon.ico" />

<style type="text/css" media="all,screen">
<!--
input.close-btn {
	background:url(../images/login_btn_bk_new2.jpg) repeat-x;
	border: 1px solid #444;
	padding-bottom: 3px;
	height: 23px;
	cursor: pointer;
}
-->
</style>
{/literal}



<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
	<form>
	<input type="hidden" id="studyInstanceUID"  value="{$params.studyInstanceUID}" />
	<input type="hidden" id="seriesInstanceUID" value="{$params.seriesInstanceUID}" />

	{if $params.message == ""}
		<h4 class="mb10"><img src="../images/busy.gif" />&nbsp;&nbsp;Creating volume data, please,wait...</h4>

		<div class="block-al-c" style="width:350px;">
			<table class="detail-tbl block-al-c">
				<tr>
					<th style="width: 12em;"><span class="trim01">Patient ID</span></th>
					<td class="al-l">{$params.patientID}</td>
				</tr>
				<tr>
					<th><span class="trim01">Series time</span></th>
					<td class="al-l">{$params.seriesTime}</td>
				</tr>
				<tr>
					<th><span class="trim01">Modality</span></th>
					<td class="al-l">{$params.modality}</td>
				</tr>
				<tr>
					<th><span class="trim01">Series description</span></th>
					<td class="al-l">{$params.seriesDescription}</td>
				</tr>
			</table>
		</div><!-- / .detail-panel END -->

		<div class="mt15">
			<input name="" value="cancel" type="button" class="close-btn" style="width: 100px;" onclick="window.close()" />
		</div>
	{else}
		<h4>{$params.message}</h4>
		<div class="mt15">
			<input name="" value="close" type="button" class="close-btn" style="width: 100px;" onclick="window.close()" />
		</div>
	{/if}

	</form>
</body>
</html>
