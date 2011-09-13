
function DoSearch(list, mode)
{
	var address = "";
	var params = {};

	switch(list)
	{
		case 'patient': address = 'patient_list.php?'; break;
		case 'study':   address = 'study_list.php?';   break;
		case 'series':  address = 'series_list.php?';  break;
		case 'cad':     address = 'cad_log.php?';      break;
	}

	if(list == 'study' && mode == 'patient')  
	{
		params.mode = 'patient';
		params.encryptedPtID = $("#encryptedPtID").val();
	}
	else if(list == 'series' && mode == 'study')
	{
		params.mode = 'study';
		params.studyInstanceUID = $("#studyInstanceUID").val();
	}
	else if(mode == 'today')
	{
		params.mode = 'today';
	}

	if(mode != 'patient' && mode != 'study')
	{
		var ptID   = $("#" + list + "Search input[name='filterPtID']").val();
		var ptName = $("#" + list + "Search input[name='filterPtName']").val();
		var sex    = $("#" + list + "Search input[name='filterSex']:checked").val();

		if(ptID != "")                params.filterPtID   = ptID;
		if(ptName != "")              params.filterPtName = ptName;
		if(sex == "M" || sex == "F")  params.filterSex    = sex;
	}

	if(list != "patient")
	{
		var ageMin     = $("#" + list + "Search input[name='filterAgeMin']").val();
		var ageMax     = $("#" + list + "Search input[name='filterAgeMax']").val();
		var modality   = $("#" + list + "Search select[name='filterModality'] option:selected").text();

		if(mode != 'study')
		{
			if(ageMin != "")  params.filterAgeMin= ageMin;
			if(ageMax != "")  params.filterAgeMax= ageMax;
		}

		if(modality != "all")
		{
			params.filterModality = modality;
		}
	}

	if(list == "study")
	{
		var stDateFrom = $("#studySearch input[name='stDateFrom']").val();
		var stDateTo   = $("#studySearch input[name='stDateTo']").val();

		if(stDateFrom != "")  params.stDateFrom = stDateFrom;
		if(stDateTo != "")    params.stDateTo   = stDateTo;
	}

	if(list == "series" || list == "cad")
	{
		var filterTag = $("#" + list + "Search input[name='filterTag']").val();

		if(filterTag != "")  params.filterTag = filterTag;

		if(mode != 'today')
		{
			var srDateFrom = $("#" + list + "Search input[name='srDateFrom']").val();
			var srDateTo   = $("#" + list + "Search input[name='srDateTo']").val();

			if(srDateFrom != "")  params.srDateFrom = srDateFrom;
			if(srDateTo != "")    params.srDateTo   = srDateTo;
		}
	}

	if(list == "series")
	{
		var description = $("#seriesSearch input[name='filterSrDescription']").val();
		if(description != "")  params.filterSrDescription = description;
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
			if(cadDateFrom != "")  params.cadDateFrom = cadDateFrom;
			if(cadDateTo != "")    params.cadDateTo   = cadDateTo;
		}

		if(filterCadID != "")    params.filterCadID   = filterCadID;
		if(filterCAD != "")      params.filterCAD     = filterCAD;
		if(filterVersion != "")  params.filterVersion = filterVersion;
		if(personalFB != "")     params.personalFB    = personalFB;
		if(consensualFB != "")   params.consensualFB  = consensualFB;
		if(filterTP != "")       params.filterTP      = filterTP;
		if(filterFBUser != "")   params.filterFBUser  = filterFBUser;
		if(filterFN != "")       params.filterFN      = filterFN;
	}

	params.showing = $("#" + list + "Search select[name='showing']").val();

	location.href = address + $.param(params);
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