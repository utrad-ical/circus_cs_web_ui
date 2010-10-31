
function DoSearch(list, mode)
{
	var address = "";
	var conditionNum = 0;

	switch(list)
	{
		case 'patient': address = 'patient_list.php'; break;
		case 'study':   address = 'study_list.php';   break;
		case 'series':  address = 'series_list.php';  break;
		case 'cad':     address = 'cad_log.php';      break;
	}

	if(list == 'study' && mode == 'patient')  
	{
		address += '?mode=patient&encryptedPtID=' + encodeURIComponent($("#encryptedPtID").val());
		conditionNum++;
	}
	else if(list == 'series' && mode == 'study')
	{
		address += '?mode=study&studyInstanceUID=' + encodeURIComponent($("#studyInstanceUID").val());
		conditionNum++;
	}
	else if(mode == 'today')
	{
		address = '?mode=today';
		conditionNum++;
	}

	if(mode != 'patient' && mode != 'study')
	{
		var ptID   = $("#" + list + "Search input[name='filterPtID']").val();
		var ptName = $("#" + list + "Search input[name='filterPtName']").val();
		var sex    = $("#" + list + "Search input[name='filterSex']:checked").val();

		if(ptID != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterPtID=' + encodeURIComponent(ptID);
			conditionNum++;
		}
	
		if(ptName != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterPtName=' + encodeURIComponent(ptName);
			conditionNum++;
		}

		if(sex == "M" || sex == "F")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterSex=' + encodeURIComponent(sex;
			conditionNum++;
		}
	}

	if(list != "patient")
	{
		var ageMin     = $("#" + list + "Search input[name='filterAgeMin']").val();
		var ageMax     = $("#" + list + "Search input[name='filterAgeMax']").val();
		var modality   = $("#" + list + "Search select[name='filterModality'] option:selected").text();

		if(mode != 'study')
		{
			if(ageMin != "")
			{
				address += ((conditionNum ==0) ? '?' : '&') + 'filterAgeMin=' + encodeURIComponent(ageMin);
				conditionNum++;
			}

			if(ageMax != "")
			{
				address += ((conditionNum == 0) ? '?' : '&') + 'filterAgeMax=' + encodeURIComponent(ageMax);
				conditionNum++;
			}
		}

		if(modality != "all")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterModality=' + encodeURIComponent(modality);
			conditionNum++;
		}
	}

	if(list == "study")
	{
		var stDateFrom = $("#studySearch input[name='stDateFrom']").val();
		var stDateTo   = $("#studySearch input[name='stDateTo']").val();

		if(stDateFrom != "")
		{
			address += ((conditionNum ==0) ? '?' : '&') + 'stDateFrom=' + encodeURIComponent(stDateFrom);
			conditionNum++;
		}

		if(stDateTo != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'stDateTo=' + encodeURIComponent(stDateTo);
			conditionNum++;
		}
	}

	if(list == "series" || list == "cad")
	{
		var filterTag = $("#" + list + "Search input[name='filterTag']").val();

		if(filterTag != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterTag=' + encodeURIComponent(filterTag);
			conditionNum++;
		}

		if(mode != 'today')
		{
			var srDateFrom = $("#" + list + "Search input[name='srDateFrom']").val();
			var srDateTo   = $("#" + list + "Search input[name='srDateTo']").val();

			if(srDateFrom != "")
			{
				address += ((conditionNum ==0) ? '?' : '&') + 'srDateFrom=' + encodeURIComponent(srDateFrom);
				conditionNum++;
			}

			if(srDateTo != "")
			{
				address += ((conditionNum == 0) ? '?' : '&') + 'srDateTo=' + srDateTo;
				conditionNum++;
			}
		}
	}

	if(list == "series")
	{
		var description = $("#seriesSearch input[name='filterSrDescription']").val();

		if(description != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterSrDescription=' + encodeURIComponent(description);
			conditionNum++;
		}
	}

	if(list == "cad")
	{
		var cadDateFrom   = $("#cadSearch input[name='cadDateFrom']").val();
		var cadDateTo     = $("#cadSearch input[name='cadDateTo']").val();
		var filterCadID   = $("#cadSearch input[name='filterCadID']").val();
		var filterCAD     = $("#cadSearch select[name='filterCAD'] option:selected").text();
		var filterVersion = $("#cadSearch select[name='filterVersion']").val();
		var personalFB    = $("#cadSearch input[name='personalFB']:checked").val();
		var consensualFB  = $("#cadSearch input[name='consensualFB']:checked").val();
		var filterFBUser  = $("#cadSearch input[name='filterFBUser']").val();
		var filterTP      = $("#cadSearch input[name='filterTP']:checked").val();
		var filterFN      = $("#cadSearch input[name='filterFN']:checked").val();

		if(mode != 'today')
		{
			if(cadDateFrom != "")
			{
				address += ((conditionNum == 0) ? '?' : '&') + 'cadDateFrom=' + encodeURIComponent(cadDateFrom);
				conditionNum++;
			}

			if(cadDateTo != "")
			{
				address += ((conditionNum == 0) ? '?' : '&') + 'cadDateTo=' + encodeURIComponent(cadDateTo);
				conditionNum++;
			}
		}

		if(filterCadID != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterCadID=' + encodeURIComponent(filterCadID);
			conditionNum++;
		}

		if(filterCAD != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterCAD=' + encodeURIComponent(filterCAD);
			conditionNum++;
		}

		if(filterVersion != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterVersion=' + encodeURIComponent(filterVersion);
			conditionNum++;
		}

		if(personalFB != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'personalFB=' + encodeURIComponent(personalFB);
			conditionNum++;
		}

		if(consensualFB != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'consensualFB=' + encodeURIComponent(consensualFB);
			conditionNum++;
		}


		if(filterTP != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterTP=' + encodeURIComponent(filterTP);
			conditionNum++;
		}

		if(filterFBUser != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterFBUser=' + encodeURIComponent(filterFBUser);
			conditionNum++;
		}

		if(filterFN != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterFN=' + encodeURIComponent(filterFN);
			conditionNum++;
		}

	}

	address += ((conditionNum == 0) ? '?' : '&') 
            +  'showing=' + encodeURIComponent($("#" + list + "Search select[name='showing']").val());

	location.href = address;
}



function ResetSearchBlock(list, mode)
{

	// select
	$("#" + list + "Search select[name='showing']").children("[value='10']").attr("selected", true);

	if(list != "patient")
	{
		$("#" + list + "Search select[name!='showing']").children().removeAttr("selected");
	}

	// radio
	$("#" + list + "Search input[type='radio']").removeAttr("disabled")
												.filter(function(){ return ($(this).val() == "all") })
												.attr("checked", true);
	// text
	if(mode == "today")
	{
		if(list == "series")
		{
			$("#seriesSearch input[type='text'][name!='srDateFrom'][name!='srDateTo']").removeAttr("value").removeAttr("disabled");
		}
		else if(list == "cad")
		{
			$("#cadSearch input[type='text'][name!='cadDateFrom'][name!='cadDateTo']").removeAttr("value").removeAttr("disabled");
		}
	}
	else
	{
		$("#" + list + "Search input[type='text']").removeAttr("disabled").removeAttr("value");
	}

	// others (CAD only)
	if(list == "cad")
	{
		ChangefilterModality();
		ChangefilterCad();
	}
}


function ChangefilterModality()
{
	var cadMenuStr = $("#cadSearch select[name='filterModality'] option:selected").val().split("/");

	var optionStr = '<option value="" selected="selected">all</option>';

	if(cadMenuStr != "")
	{
		for(var i=0; i<cadMenuStr.length; i++)
		{
			var tmpStr = cadMenuStr[i].split("^");
			var versionStr  = cadMenuStr[i].substr(tmpStr[0].length+1);

			if(tmpStr[i] != '')
			{
				optionStr += '<option value="' + versionStr + '">' + tmpStr[0] + '</option>';
			}
		}
	}

	$("#cadSearch select[name='filterCAD']").html(optionStr);
	$("#cadSearch select[name='filterVersion']").html('<option value="all">all</option>');

}


function ChangefilterCad()
{
	var versionStr = $("#cadSearch select[name='filterCAD'] option:selected").val().split("^");
	
	var optionStr = '<option value="all" selected="selected">all</option>';

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

	$("#cadSearch select[name='filterVersion']").html(optionStr);
}