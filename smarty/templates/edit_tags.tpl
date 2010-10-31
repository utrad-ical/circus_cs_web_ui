<?xml version="1.0" encoding="shift_jis"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<title>Edit tags</title>
<link href="./css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="./jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="./jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="./js/hover.js"></script>
<script language="javascript" type="text/javascript" src="./js/viewControl.js"></script>

{literal}
<script language="javascript">
<!-- 

function EditTag(mode, sid) 
{
	if((mode=='add' && $("#addTagText").val() != "")
       || (mode=='delete' && confirm('Do you want to delete "' + $("#tagStr" + sid).html() + '"?')))
	{
		$.post('tag_registration.php', 
           	   {mode: mode,
 				category: encodeURIComponent($("#category").val()),
 				referenceID: encodeURIComponent($("#referenceID").val()),
				sid: sid,
				tagStr: encodeURIComponent($("#addTagText").val()) },
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

						if($("#category").val() >= 3)
						{
							opener.document.getElementById('tagArea').innerHTML = data.parentTagHtml;
						}
					}
		  	}, "json");
	}
}

--> 
</script>

<link rel="shortcut icon" href="./favicon.ico" />

<style type="text/css" media="all,screen">
<!--

-->
</style>
{/literal}

<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/popup.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
	<form>
	<input type="hidden" id="category"    value="{$params.category|escape}" />
	<input type="hidden" id="referenceID" value="{$params.referenceID|escape}" />

	<!-- <h4 class="mb10">Tags for execID: {$param.execID}</h4> -->

	<div id="tagList" class="block-al-c" style="width:350px;">

		<table id="tagTable" class="col-tbl">
			<thead>
				<th>ID</th>
				<th style="width:200px;">Tag</th>
				<th>Entered by</th>
				<th style="width:40px;">&nbsp;</th>
			</thead>
			<tbody>
			{foreach from=$tagArray item=item name=tagList}
				<tr>
					<td>{$smarty.foreach.tagList.iteration}</td>
					<td id="tagStr{$item[0]|escape}" class="al-l">{$item[1]|escape}</td>
					<td class="al-l">{$item[2]|escape}</td>
					<td class="al-l">
						<input type="button" id="del{$item[0]|escape}" class="s-btn form-btn" value="delete" onclick="EditTag('delete', {$item[0]|escape});" />
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>

	</div><!-- / .detail-panel END -->

	<div class="block-al-c" style="width:270px;">
		<p class="mt10 mb20">
			<input type="textbox" id="addTagText" style="width:200px;">
			<input type="button"  id="addTag" value="add" onclick="EditTag('add','');" />
		</p>
	</div>

	</form>
</body>
</html>
