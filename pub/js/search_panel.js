
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

	var ageMin     = $("#" + list + "Search input[name='filterAgeMin']").val();
	var ageMax     = $("#" + list + "Search input[name='filterAgeMax']").val();
	var modality   = $("#" + list + "Search select[name='filterModality'] option:selected").text();

	if(mode != 'patient' && mode != 'study')
	{
		if(ageMin != "")  params.filterAgeMin= ageMin;
		if(ageMax != "")  params.filterAgeMax= ageMax;
	}

	if(list != "patient")
	{
		if(modality != "all")
		{
			params.filterModality = modality;
		}
	}

	if(list == "study")
	{
		var stDateKind = $("#studySearch .stDateRange").daterange('option', 'kind');
		var stDateFrom = $("#studySearch .stDateRange").daterange('option', 'fromDate');
		var stDateTo   = $("#studySearch .stDateRange").daterange('option', 'toDate');

		if(stDateKind && stDateKind != "all")
		{
			params.stDateKind = stDateKind;

			if(stDateFrom)   params.stDateFrom = stDateFrom;
			if(stDateTo)     params.stDateTo   = stDateTo;
		}
	}

	if(list == "series" || list == "cad")
	{
		var filterTag = $("#" + list + "Search input[name='filterTag']").val();

		if(filterTag)  params.filterTag = filterTag;

		if(mode != 'today')
		{
			var srDateKind = $("#" + list + "Search .srDateRange").daterange('option', 'kind');
			var srDateFrom = $("#" + list + "Search .srDateRange").daterange('option', 'fromDate');
			var srDateTo   = $("#" + list + "Search .srDateRange").daterange('option', 'toDate');

			if(srDateKind && srDateKind != "all")
			{
				params.srDateKind = srDateKind;

				if(srDateFrom)   params.srDateFrom = srDateFrom;
				if(srDateTo)     params.srDateTo   = srDateTo;
			}
		}
	}

	if(list == "series")
	{
		var description = $("#seriesSearch input[name='filterSrDescription']").val();
		if(description != "")  params.filterSrDescription = description;
	}

	if(list == "cad")
	{
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
			var cadDateKind = $("#cadSearch .cadDateRange").daterange('option', 'kind');
			var cadDateFrom = $("#cadSearch .cadDateRange").daterange('option', 'fromDate');
			var cadDateTo   = $("#cadSearch .cadDateRange").daterange('option', 'toDate');

			if(cadDateKind && cadDateKind != "all")
			{
				params.cadDateKind = cadDateKind;

				if(cadDateFrom)   params.cadDateFrom = cadDateFrom;
				if(cadDateTo)     params.cadDateTo   = cadDateTo;
			}
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
	var targetPanel = $('#' + list + 'Search');

	// select
	$("select option", targetPanel).removeAttr('selected').eq(0).attr('selected', 'selected');

	// radio
	$("input[type='radio']", targetPanel).enable();
	$("input[type='radio'][value='all']", targetPanel).attr('checked', 'checked');

	// text
	$("input[type='text']", targetPanel).enable().val('');

	// date range
	$('.ui-daterange', targetPanel).daterange('option', 'kind', 'all');

	if (mode == "today")
	{
		$('.srDateRange, .cadDateRange', targetPanel)
			.daterange('option', 'kind', 'today')
			.find('select').disable();
	}

	// others (CAD only)
	if(list == "cad")
	{
		ChangefilterModality();
		ChangefilterCad();
	}
}
