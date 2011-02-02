<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

<title>CIRCUS CS {$smarty.session.circusVersion}</title>

<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jquery.upload-1.0.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<link rel="shortcut icon" href="../favicon.ico" />

<script language="Javascript">;
<!--
{literal}

function DeleteJob(jobID)
{
	if(confirm('Do you delete the job (JobID:'+ jobID + ') ?'))
	{
		$.post("delete_plugin_job.php",
			{ jobID: jobID },
			  function(data){

				if(data.message=="")
				{
					$("#jobList tbody").html(data.jobListHtml);
					$('.form-btn').hoverStyle({normal: 'form-btn-normal', hover: 'form-btn-hover',disabled: 'form-btn-disabled'});
					alert("Successfully deleted the plug-in job (jobID:" + jobID + ")");
				}
				else
				{
					alert(data.message);
				}

		  }, "json");

	}
}

function ShowJobDetail(idNum, jobID)
{
	ChangeBgColor('row' + idNum);
	JumpClickedRow('row' + idNum);
	parent.document.getElementById('frameJobList').rows = "150,*";		
	var address = 'cad_job_list_detail.php?{$sessionName}={$sessionID}&jobID=' + jobID;
	parent.bottom_detail.location.replace(address);
}

-->
{/literal}

</script>

<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />

{literal}
<style type="text/css">

div.line{
	margin-top: 10px;
	margin-bottom: 10px;
	border-bottom: solid 2px #8a3b2b;
}


</style>
{/literal}
</head>

<body class="spot">
<div id="page">
	<div id="container" class="menu-back">
		<!-- ***** #leftside ***** -->
		<div id="leftside">
			{include file='menu.tpl'}
		</div>
		<!-- / #leftside END -->
		<div id="content">
			<h2>Plug-in job list</h2>

			<form id="form1" name="form1">
				<input type="hidden" id="ticket" name="ticket" value="{$params.ticket|escape}" />
				<div id="jobList">
					<table class="col-tbl" style="width:100%;">
						<thead>
							<tr>
								<th>Job ID</th>
								<th>Registered at</th>
								<th>Ordered by</th>
								<th>Plug-in name</th>
								<th>Type</th>
								<th>Patient ID</th>
								<th>Study ID</th>
								<th>Series ID</th>
								{*<th>Detail</th>*}
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$jobList item=item}
								<tr>
									<td>{$item[0]}</td>
									<td>{$item[1]}</td>
									<td>{$item[2]}</td>
									<td>{$item[3]}</td>
									<td>{$item[4]}</td>
									<td>{$item[5]}</td>
									<td>{$item[6]}</td>
									<td>{$item[7]}</td>
								{*	<td>
										<input type="button" class="form-btn" value="detail" onClick="ShowJobDetail({$item[0]});" />
									</td>*}

									{if $item[8] == 't'}
										<td>Processing</td>
									{elseif $smarty.session.serverOperationFlg == 1 || $smarty.session.serverSettingsFlg == 1
											|| $userID == $item[2]}
										<td>
											<input type="button" class="form-btn" value="delete" onClick="DeleteJob({$item[0]});">
										</td>
									{else}
										<td>&nbsp;</td>
									{/if}
								</tr>
							{/foreach}
						</tbody>

				</div>

				{*<div class="line"></div>*}

				<div id="message"></div>

			</form>
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
