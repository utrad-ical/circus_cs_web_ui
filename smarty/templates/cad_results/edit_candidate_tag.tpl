<?xml version="1.0" encoding="shift_jis"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<title>Edit tags</title>
<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>

{literal}
<script language="javascript">
<!-- 

function AddTag() 
{
	if($("#addTagText").val() != "")
	{
		$.post('candidate_tag_registration.php', 
           	   {mode: 'add',
 				execID: $("#execID").val(),
				candID: $("#candID").val(),
				feedbackMode: $("#feedbackMode").val(),
				tagStr: $("#addTagText").val(),
				userID: $("#userID").val()},

				function(data){
		    		if(data.message != "")
					{
						alert(data.message);
					}
					else
					{
						$("#tagTable tbody").html(data.popupTableHtml);
						$('.form-btn').hoverStyle({normal: 'form-btn-normal', hover: 'form-btn-hover',disabled: 'form-btn-disabled'});
						$("#addTagText").removeAttr("value");
						opener.document.getElementById('candidateTagArea' + data.candID).innerHTML = data.parentTagHtml;
					}
		  	}, "json");
	}
}

function DeleteTag(tagID)
{
	if(confirm('Do you delete "' + $("#tagStr" + tagID).html() + '"?'))
	{
		$.post('candidate_tag_registration.php', 
           	   {mode: 'delete',
				execID: $("#execID").val(),
				candID: $("#candID").val(),
				feedbackMode: $("#feedbackMode").val(),
				tagID: tagID,
				tagStr: $("#tagStr" + tagID).html(),
				userID: $("#userID").val()},
				function(data){
			    	if(data.message != "")
					{
						alert(data.message);
					}
					else
					{
						$("#tagTable tbody").html(data.popupTableHtml);
						$('.form-btn').hoverStyle({normal: 'form-btn-normal', hover: 'form-btn-hover',disabled: 'form-btn-disabled'});
						opener.document.getElementById('candidateTagArea' + data.candID).innerHTML = data.parentTagHtml;
					}
			  	}, "json");
	}
}
--> 
</script>

<link rel="shortcut icon" href="../favicon.ico" />

<style type="text/css" media="all,screen">
<!--

-->
</style>
{/literal}

<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
	<form onsubmit="return false;">
	<input type="hidden" id="execID"  value="{$params.execID}" />
	<input type="hidden" id="candID"  value="{$params.candID}" />
	<input type="hidden" id="feedbackMode"  value="{$params.feedbackMode}" />
	<input type="hidden" id="userID"  value="{$params.userID}" />

	<h4>Candidate tags</h4>
	<p class="font-s mb10">
		(ID: {$params.execID}, candidate: {$params.candID},
		{if $params.feedbackMode == "personal"}personal, user ID:{$params.userID}{else}consensual{/if})
	</p>

	<div id="tagList" class="block-al-c" style="width:270px;">

		<table id="tagTable" class="col-tbl">
			<thead>
				<th>ID</th>
				<th>Tag</th>
				<th>&nbsp;</th>
			</thead>
			<tbody>
			{foreach from=$tagArray item=item}
				<tr>
					<td id="{$item[0]}">{$item[0]}</td>
					<td id="tagStr{$item[0]}" class="al-l" style="width:200px;">{$item[1]}</td>
					<td class="al-l">
						<input type="button" id="del{$item[0]}'" class="s-btn form-btn" value="delete" onclick="DeleteTag({$item[0]});" />
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>

	</div><!-- / .detail-panel END -->

	<div class="block-al-c" style="width:270px;">
		<p class="mt10 mb20">
			<input type="textbox" id="addTagText" style="width:200px;">
			<input type="button"  id="addTag" value="add" onclick="AddTag();" />
		</p>
	</div>

	</form>
</body>
</html>
