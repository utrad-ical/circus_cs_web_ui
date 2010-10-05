
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
		address += '?mode=patient&encryptedPtID=' + $("#encryptedPtID").val();
		conditionNum++;
	}
	else if(list == 'series' && mode == 'study')
	{
		address += '?mode=study&studyInstanceUID=' + $("#studyInstanceUID").val();
		conditionNum++;
	}
	else if(mode == 'today')
	{
		address = '?mode=today';
		conditionNum++;
	}

	if(mode != 'patient' && mode != 'study')
	{
		var id   = $("#" + list + "Search input[name='filterPtID']").val();
		var name = $("#" + list + "Search input[name='filterPtName']").val();
		var sex  = $("#" + list + "Search input[name='filterSex']:checked").val();

		if(id != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterPtID=' + id;
			conditionNum++;
		}
	
		if(name != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterPtName=' + name;
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
		var ageMin     = $("#" + list + "Search input[name='filterAgeMin']").val();
		var ageMax     = $("#" + list + "Search input[name='filterAgeMax']").val();
		var modality   = $("#" + list + "Search select[name='filterModality'] option:selected").text();

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
		var stDateFrom = $("#studySearch input[name='stDateFrom']").val();
		var stDateTo   = $("#studySearch input[name='stDateTo']").val();

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
		var filterTag = $("#" + list + "Search input[name='filterTag']").val();

		if(filterTag != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterTag=' + filterTag;
			conditionNum++;
		}

		if(mode != 'today')
		{
			var srDateFrom = $("#" + list + "Search input[name='srDateFrom']").val();
			var srDateTo   = $("#" + list + "Search input[name='srDateTo']").val();

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
		var description = $("#seriesSearch input[name='filterSrDescription']").val();

		if(description != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterSrDescription=' + description;
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
		var filterTP      = $("#cadSearch input[name='filterTP']:checked").val();
		var filterFN      = $("#cadSearch input[name='filterFN']:checked").val();

		if(mode != 'today')
		{
			if(cadDateFrom != "")
			{
				address += ((conditionNum ==0) ? '?' : '&') + 'cadDateFrom=' + cadDateFrom;
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
			address += ((conditionNum ==0) ? '?' : '&') + 'filterCadID=' + filterCadID;
			conditionNum++;
		}

		if(filterCAD != "")
		{
			address += ((conditionNum ==0) ? '?' : '&') + 'filterCAD=' + filterCAD;
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

		if(filterFN != "")
		{
			address += ((conditionNum == 0) ? '?' : '&') + 'filterFN=' + filterFN;
			conditionNum++;
		}

	}

	address += ((conditionNum ==0) ? '?' : '&') + 'showing=' + $("#" + list + "Search select[name='showing']").val();

	location.href=address;
}



function ResetSearchBlock(list, mode)
{
	$("#" + list + "Search select[name='showing']").children("[value='10']").attr("selected", true);
	$("#" + list + "Search input[name='filterPtID'], #" + list + "Search input[name='filterPtName']").removeAttr("disabled").removeAttr("value");
	$("#" + list + "Search input[name='filterSex']").removeAttr("disabled").filter(function(){ return ($(this).val() == "all") }).attr("checked", true)
	if(list != "patient")
	{
		$("#" + list + "Search input[name='filterAgeMin'], #" + list + "Search input[name='filterAgeMax']").removeAttr("disabled").removeAttr("value");
		$("#" + list + "Search select[name='filterModality']").children().removeAttr("selected");
	}

	if(list == "study")
	{
		$("#studySearch input[name='stDateFrom'], #studySearch input[name='stDateTo']").removeAttr("value");
	}

	if(list == "series")
	{
		$("#seriesSearch input[name='filterSrDescription'], #seriesSearch input[name='filterTag']").removeAttr("value");
		
		if(mode != "today")
		{
			$("#seriesSearch input[name='srDateFrom'], #seriesSearch input[name='srDateTo']").removeAttr("value");
		}
	}

	if(list == "cad")
	{
		if(mode != "today")
		{
			$("#cadSearch input[name='cadDateFrom'], #cadSearch input[name='cadDateTo']").removeAttr("value");
		}

		$("#cadSearch input[name='filterCadID'], #cadSearch input[name='srDateFrom'], #cadSearch input[name='srDateTo'], #cadSearch input[name='filterTag']").removeAttr("value");
		$("#cadSearch select[name='filterCAD'], #cadSearch select[name='filterVersion']").children().removeAttr("selected");
		$("#cadSearch input[name='personalFB'], #cadSearch input[name='consensualFB'], #cadSearch input[name='filterTP'], #cadSearch input[name='filterFN']").filter(function(){ return ($(this).val() == "all") }).attr("checked", true);
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