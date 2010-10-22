
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
		var ptID   = encodeURIComponent($("#" + list + "Search input[name='filterPtID']").val());
		var ptName = encodeURIComponent($("#" + list + "Search input[name='filterPtName']").val());
		var sex    = encodeURIComponent($("#" + list + "Search input[name='filterSex']:checked").val());

		if(ptID != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterPtID=' + ptID;
			conditionNum++;
		}
	
		if(ptName != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterPtName=' + ptName;
			conditionNum++;
		}

		if(sex == "M" || sex == "F")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterSex=' + sex;
			conditionNum++;
		}
	}

	if(list != "patient")
	{
		var ageMin     = encodeURIComponent($("#" + list + "Search input[name='filterAgeMin']").val());
		var ageMax     = encodeURIComponent($("#" + list + "Search input[name='filterAgeMax']").val());
		var modality   = encodeURIComponent($("#" + list + "Search select[name='filterModality'] option:selected").text());

		if(mode != 'study')
		{
			if(ageMin != "")
			{
				address += ((conditionNum ==0) ? '?' : '&') + 'filterAgeMin=' + ageMin;
				conditionNum++;
			}

			if(ageMax != "")
			{
				address += ((conditionNum == 0) ? '?' : '&') + 'filterAgeMax=' + ageMax;
				conditionNum++;
			}
		}

		if(modality != "all")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterModality=' + modality;
			conditionNum++;
		}
	}

	if(list == "study")
	{
		var stDateFrom = encodeURIComponent($("#studySearch input[name='stDateFrom']").val());
		var stDateTo   = encodeURIComponent($("#studySearch input[name='stDateTo']").val());

		if(stDateFrom != "")
		{
			address += ((conditionNum ==0) ? '?' : '&') + 'stDateFrom=' + stDateFrom;
			conditionNum++;
		}

		if(stDateTo != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'stDateTo=' + stDateTo;
			conditionNum++;
		}
	}

	if(list == "series" || list == "cad")
	{
		var filterTag = encodeURIComponent($("#" + list + "Search input[name='filterTag']").val());

		if(filterTag != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterTag=' + filterTag;
			conditionNum++;
		}

		if(mode != 'today')
		{
			var srDateFrom = encodeURIComponent($("#" + list + "Search input[name='srDateFrom']").val());
			var srDateTo   = encodeURIComponent($("#" + list + "Search input[name='srDateTo']").val());

			if(srDateFrom != "")
			{
				address += ((conditionNum ==0) ? '?' : '&') + 'srDateFrom=' + srDateFrom;
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
		var description = encodeURIComponent($("#seriesSearch input[name='filterSrDescription']").val());

		if(description != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterSrDescription=' + description;
			conditionNum++;
		}
	}

	if(list == "cad")
	{
		var cadDateFrom   = encodeURIComponent($("#cadSearch input[name='cadDateFrom']").val());
		var cadDateTo     = encodeURIComponent($("#cadSearch input[name='cadDateTo']").val());
		var filterCadID   = encodeURIComponent($("#cadSearch input[name='filterCadID']").val());
		var filterCAD     = encodeURIComponent($("#cadSearch select[name='filterCAD'] option:selected").text());
		var filterVersion = encodeURIComponent($("#cadSearch select[name='filterVersion']").val());
		var personalFB    = encodeURIComponent($("#cadSearch input[name='personalFB']:checked").val());
		var consensualFB  = encodeURIComponent($("#cadSearch input[name='consensualFB']:checked").val());
		var filterFBUser  = encodeURIComponent($("#cadSearch input[name='filterFBUser']").val());
		var filterTP      = encodeURIComponent($("#cadSearch input[name='filterTP']:checked").val());
		var filterFN      = encodeURIComponent($("#cadSearch input[name='filterFN']:checked").val());

		if(mode != 'today')
		{
			if(cadDateFrom != "")
			{
				address += ((conditionNum == 0) ? '?' : '&') + 'cadDateFrom=' + cadDateFrom;
				conditionNum++;
			}

			if(cadDateTo != "")
			{
				address += ((conditionNum == 0) ? '?' : '&') + 'cadDateTo=' + cadDateTo;
				conditionNum++;
			}
		}

		if(filterCadID != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterCadID=' + filterCadID;
			conditionNum++;
		}

		if(filterCAD != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterCAD=' + filterCAD;
			conditionNum++;
		}

		if(filterVersion != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterVersion=' + filterVersion;
			conditionNum++;
		}

		if(personalFB != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'personalFB=' + personalFB;
			conditionNum++;
		}

		if(consensualFB != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'consensualFB=' + consensualFB;
			conditionNum++;
		}


		if(filterTP != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterTP=' + filterTP;
			conditionNum++;
		}

		if(filterFBUser != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterFBUser=' + filterFBUser;
			conditionNum++;
		}

		if(filterFN != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterFN=' + filterFN;
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