{capture name="extra"}
<script type="text/javascript">;
<!--
{literal}

$(function () {
	function getJobQueueList()
	{
		$('#refresh').disable();
		$('#busy').show();
		$.webapi({
			action: 'queryJobQueue',
			onSuccess: refresh,
			onFail: onMessage
		});
	}

	function relax()
	{
		$('#refresh').enable();
		$('#busy').hide();
	}

	function onMessage(text)
	{
		$('#message').text(text);
		relax();
	}

	function refresh(data)
	{
		var cols = ['job_id', 'registered_at', 'exec_user', 'plugin_name',
			'plugin_type', 'patient_id', 'study_id', 'series_id', 'priority', 'pm_id'];
		var tbody = $('#jobList tbody').empty();
		jobs = data.jobs;
		for (var i = 0; i < jobs.length; i++)
		{
			var job = jobs[i];
			var tr = $('<tr>');
			for (var j = 0; j < cols.length; j++)
				$('<td>').addClass(cols[j]).appendTo(tr).text(job[cols[j]]);
			if (job.status == 1)
				$('<td>In Queue <input type="button" class="form-btn" value="delete"></td>').appendTo(tr);
			else
				$('<td>Processing</td>').appendTo(tr);
			tr.appendTo(tbody);
		}
		tbody.find('tr:odd').addClass('column');
		relax();
	}

	$('#jobList').click(function(event) {
		if (!$(event.target).is(':button'))
			return;
		var id = $(event.target).closest('tr').find('.job_id').text();
		if(confirm('Do you delete the job (JobID:'+ id + ') ?'))
		{
			$('#busy').show();
			$.webapi({
				action: 'deleteJob',
				params: { jobID: id },
				onSuccess: function() {
					onMessage('Successfully deleted the job ' + id);
					getJobQueueList();
				},
				onFail: onMessage
			});
		}
	});

	$('#refresh').click(function () {
		onMessage('');
		getJobQueueList();
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

	getJobQueueList();
});

-->
</script>

<style type="text/css">

#message { margin: 0; padding: 1em 1em 0 1em; font-weight: bold; color: red; }
#jobList table { margin: 1em 0; }
#resetQueue table td { padding: 0.5em; }

</style>
{/literal}
{/capture}
{include file="header.tpl"
	head_extra=$smarty.capture.extra body_class="spot"}

</head>

<h2>Plug-in job queue</h2>

<div id="message"></div>

<form id="form1" name="form1">
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
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

	<div id="list-btn">
		<input type="button" id="refresh" class="form-btn" value="Refresh" />
		<span id="busy"> Loading... <img src="../images/busy.gif" /></span>
	</div>

	{*<h3>Reset job queue</h3>
	<div id="resetQueue">
		<table>
			<tbody>
				<tr>
					<td>Reset job queue</td>
					<td>&nbsp;<input type="button" id="reset-button" class="form-btn" value="Reset" /></td>
				</tr>
			</tbody>
		</table>
	</div>*}
</form>

{include file="footer.tpl"}

