//--------------------------------------------------------------------------------------------------
// Delete data (common function)
//--------------------------------------------------------------------------------------------------
function DeleteData(mode)
{
	// Get selected sids (checkbox)
	var sids=[];
    $("[name='sidList[]']:checked").each(function(){ sids.push(this.value); });

	if(sids.length == 0)
	{
	   alert('Please select at least one ' + mode + '!');
	}
	else if(confirm('Do you delete selected ' + mode + '?'))
	{

		$.post("delete_list.php",
               {mode: mode, 'sidArr[]': sids, ticket: $("#ticket").val()},
				function(data){
					alert(data.message);
					location.reload();
				}, "json");
	}
}
//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
// Show sub window for editing tag (common function)
//--------------------------------------------------------------------------------------------------
function EditTag(category, sid)
{
	var title ="";

	switch(category)
	{
		case 1: title = "Edit patient tags";  break;
		case 2: title = "Edit study tags";  break;
		case 3: title = "Edit series tags";  break;
		case 4: title = "Edit CAD tags";  break;
		case 5: title = "Edit tags for Lesion candidate";  break;
	}

	var dstAddress = "edit_tags.php?category=" + category + "&referenceID=" + sid;
	window.open(dstAddress, title, "width=400,height=250,location=no,resizable=no,scrollbars=1");
}
//--------------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------------
// For patient list
//--------------------------------------------------------------------------------------------------
function ShowStudyList(idNum, encryptedPtID)
{
	location.href = 'study_list.php?mode=patient&encryptedPtID='
                  + encodeURIComponent(encryptedPtID);
}

function ChangeOrderOfPatientList(orderCol, orderMode)
{
	var params = { orderCol: orderCol,
				   orderMode: orderMode,
				   filterPtID: $("#hiddenFilterPtID").val(),
				   filterPtName: $("#hiddenFilterPtName").val(),
				   filterSex: $("#hiddenFilterSex").val(),
				   showing: $("#hiddenShowing").val() };

	var address = 'patient_list.php?' + $.param(params);
	location.replace(address);
}
//--------------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------------
// For study list
//--------------------------------------------------------------------------------------------------
function ShowSeriesList(idNum, studyInstanceUID)
{
	location.href = 'series_list.php?mode=study&studyInstanceUID='
                  + encodeURIComponent(studyInstanceUID);
}

function ChangeOrderOfStudyList(orderCol, orderMode)
{
	var params = { orderCol: orderCol,
                   orderMode:orderMode,
                   filterModality: $("#hiddenFilterModality").val(),
				   filterAgeMin: $("#hiddenFilterAgeMin").val(),
	               filterAgeMax: $("#hiddenFilterAgeMax").val(),
	               stDateKind: $("#hiddenStDateKind").val(),
	               stDateFrom: $("#hiddenStDateFrom").val(),
	               stDateTo: $("#hiddenStDateTo").val(),
	               stTimeTo: $("#hiddenStTimeTo").val(),
	               showing: $("#hiddenShowing").val() };

	if($("#mode").val() == 'patient')
	{
		params.mode = "patient";
        params.encryptedPtID = $("#encryptedPtID").val();
	}
	else
	{
		params.filterPtID   = $("#encryptedPtID").val();
		params.filterPtName = $("#hiddenFilterPtName").val();
		params.filterSex    = $("#hiddenFilterSex").val();
	}

	var address = 'study_list.php?' + $.param(params);
	location.replace(address);
}

//--------------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------------
// For series list
//--------------------------------------------------------------------------------------------------
function CreateListAddressForSeriesList(mode, orderCol, orderMode)
{
	var params = { orderCol:       orderCol,
                   orderMode:      orderMode,
                   filterModality:      $("#hiddenFilterModality").val(),
                   filterSrDescription: $("#hiddenFilterSrDescription").val(),
	               showing:             $("#hiddenShowing").val() };

	if(mode == 'study')
	{
		params.mode = 'study';
        params.studyInstanceUID = $("#studyInstanceUID").val();
	}
	else
	{
		if(mode == 'today')
		{
			params.mode = 'today';
		}
		else
		{
			params.srDateKind = $("#hiddenSrDateKind").val();
			params.srDateFrom = $("#hiddenSrDateFrom").val();
			params.srDateTo   = $("#hiddenSrDateTo").val();
			params.srTimeTo   = $("#hiddenSrTimeTo").val();
		}

		params.filterPtID   = $("#hiddenFilterPtID").val();
		params.filterPtName = $("#hiddenFilterPtName").val();
		params.filterSex    = $("#hiddenFilterSex").val();
		params.filterAgeMin = $("#hiddenFilterAgeMin").val();
		params.filterAgeMax = $("#hiddenFilterAgeMax").val();
	}

	return  'series_list.php?' + $.param(params);
}


function ChangeOrderOfSeriesList(orderCol, orderMode)
{
	location.replace(CreateListAddressForSeriesList($("#mode").val(), orderCol, orderMode));
}


function ShowSeriesDetail(sid)
{
	var params = { sid: sid,
                   listTabName: ($("#mode").val() == 'today') ? "Today's series" : "Series list" };

	location.href = "series_detail.php?" + $.param(params);
}


function ShowCADResultFromSeriesList(seriesID, personalFeedbackFlg)
{
	// Get option value from pulldown menu
	var tmpStr = $("#cadMenu"+seriesID).val().split("^");

	var params = { jobID:   tmpStr[4],
                   srcList: ($("#mode").val()=="today") ? 'todaysSeries' : 'series' };

	if(personalFeedbackFlg == 1)  params.feedbackMode = 'personal';

	location.href = 'cad_results/cad_result.php?' + $.param(params);
}

function ChangeCADMenu(source, seriesID, menuID)
{
	// Get option value from pulldown menu
	var tmpStr = $("#cadMenu"+seriesID).val().split("^");

	var jobStatus  = parseInt(tmpStr[2]);
	var dateTime   = tmpStr[3];

	if(jobStatus == 4) // Executed
	{
		$("#execButton"+seriesID).hide();
		$("#resultButton" + seriesID).show();
		$("#cadInfo"+seriesID).html('Executed at ' + dateTime);
	}
	else
	{
		$("#resultButton" + seriesID).hide();

		if(jobStatus > 0)
		{
			$("#cadInfo"+seriesID).html('Registered in CAD job list');
			$("#execButton"+seriesID).hide();
		}
		else
		{
			$("#execButton" + seriesID).show();

			if(jobStatus == -1)
			{
				$("#cadInfo"+seriesID).html('<span style="color:#f00;">Fail to execute</span>');
			}
			else if(source == 'todaysSeriesList')
			{
				$("#cadInfo"+seriesID).html('<span style="color:#f00;">Not executed</span>');
			}
			else
			{
				$("#cadInfo"+seriesID).html('&nbsp;');
			}
		}
	}
}

function RegistCADJob(seriesID, studyInstanceUID, seriesInstanceUID)
{
	// Get option value from pulldown menu
	var tmpStr = $("#cadMenu"+seriesID).val().split("^");

	var cadName = tmpStr[0];
	var version = tmpStr[1];

	var orderCol  = $("#orderCol").val();
	var orderMode = $("#orderMode").val();

	var address = 'cad_job/cad_execution.php?cadName=' + encodeURIComponent(cadName)
                + '&version=' + encodeURIComponent(version)
	            + '&studyInstanceUID=' + encodeURIComponent(studyInstanceUID)
                + '&seriesInstanceUID=' + encodeURIComponent(seriesInstanceUID);

	if($("#mode").val() == "today")		address += '&srcList=todaysSeries';
	else								address += '&srcList=series';

	location.href=address;
}
//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
// For CAD log
//--------------------------------------------------------------------------------------------------
function ShowCADResultFromCADLog(jobID, personalFBFlg)
{
	var params = {
		jobID: jobID,
		srcList: ($("#mode").val() == "today") ? 'todaysCAD' : 'cadLog'
	};
	if(personalFBFlg == 1) params.feedbackMode = 'personal';
	location.href = 'cad_results/cad_result.php?' + $.param(params);
}


function ChangeOrderOfCADList(orderCol, orderMode)
{
	var params = { orderCol:       orderCol,
                   orderMode:      orderMode,
				   filterPtID:     $("#hiddenFilterPtID").val(),
				   filterPtName:   $("#hiddenFilterPtName").val(),
				   filterSex:      $("#hiddenFilterSex").val(),
                   filterModality: $("#hiddenFilterModality").val(),
				   filterAgeMin:   $("#hiddenFilterAgeMin").val(),
	               filterAgeMax:   $("#hiddenFilterAgeMax").val(),
	               srDateKind:     $("#hiddenSrDateKind").val(),
	               srDateFrom:     $("#hiddenSrDateFrom").val(),
	               srDateTo:       $("#hiddenSrDateTo").val(),
	               srTimeTo:       $("#hiddenSrTimeTo").val(),
   	               filterCadID:    $("#hiddenFilterCadID").val(),
	               filterCAD:      $("#hiddenFilterCAD").val(),
	               filterVersion:  $("#hiddenFilterVersion").val(),
                   personalFB:     $("#hiddenFilterPersonalFB").val(),
                   consensualFB:   $("#hiddenFilterConsensualFB").val(),
                   filterTP:       $("#hiddenFilterTP").val(),
                   filterFN:       $("#hiddenFilterFN").val(),
	               showing:        $("#hiddenShowing").val() };

	if($("#mode").val() == 'today')
	{
		params.mode = 'today';
	}
	else
	{
		params.cadDateKind = $("#hiddenCadDateKind").val();
		params.cadDateFrom = $("#hiddenCadDateFrom").val();
		params.cadDateTo   = $("#hiddenCadDateTo").val();
		params.cadTimeTo   = $("#hiddenCadTimeTo").val();
	}

	location.href = 'cad_log.php?' + $.param(params);
}
//--------------------------------------------------------------------------------------------------
