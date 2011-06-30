{capture name="extra"}
<script type="text/javascript">;
<!--
{literal}

function GetJobQueueList()
{
	$.ajax(
		{
			url: "get_job_queue_list.php",
			dataType: "json",
			success: function(data){
				if(data.message=="")
				{
					$("#jobList tbody").html(data.jobListHtml);
				}
				else
				{
					$("#message").append(data.message);
				}
			}
		}
	);
}

function DeleteJob(jobID)
{
	if(confirm('Do you delete the job (JobID:'+ jobID + ') ?'))
	{
		$.post(
			"delete_plugin_job.php",
			{ jobID: jobID },
			function(data){
				$("#message").text(data.message);
				GetJobQueueList();
			},
			"json"
		);
	}
}

$(function () {

	$('#refresh-button').click(function () {
		GetJobQueueList();
	});

	$('#reset-button').click(function () {
		if(confirm('Reset plug-in job queue?'))
		{
			$.ajax(
				{
					url: "reset_plugin_job.php",
					dataType: "json",
					success: function(data){
						$("#message").text(data.message);
						GetJobQueueList();
					}
				}
			);
		}
	});

	GetJobQueueList();
});

-->
</script>

<style type="text/css">

#message { margin: 0; padding: 1em 1em 0 1em; font-weight: bold; color: red; }
#jobList table { margin: 1em 0; }
#content h3 { margin: 1.5em 0 0.5em 0; }
#resetQueue table td { padding: 0.5em; }

</style>
{/literal}
{/capture}
{capture name="require"}
css/popup.css
js/hover.js
{/capture}
{include file="header.tpl" require=$smarty.capture.require
	head_extra=$smarty.capture.extra body_class="spot"}

</head>

<h2>Plug-in job queue</h2>

<div id="message"></div>

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
					<th>Priority</th>
					<th>PM ID</th>
					{*<th>Detail</th>*}
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

	<div id="list-btn">
		<input type="button" id="refresh-button" class="form-btn" value="Refresh" />
	</div>

	<h3>Reset job queue</h3>
	<div id="resetQueue">
		<table>
			<tbody>
				<tr>
					<td>Reset job queue</td>
					<td>&nbsp;<input type="button" id="reset-button" class="form-btn" value="Reset" /></td>
				</tr>
			</tbody>
		</table>
	</div>
</form>

{include file="footer.tpl"}

