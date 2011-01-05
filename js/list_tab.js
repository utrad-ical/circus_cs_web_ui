
//--------------------------------------------------------------------------------------------------
// Delete data (common function)
//--------------------------------------------------------------------------------------------------
function DeleteData(mode)
{
	//選択されたチェックボックスの値を配列に保存
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
	location.href = 'study_list.php?mode=patient&encryptedPtID=' + encodeURIComponent(encryptedPtID);
}

function ChangeOrderOfPatientList(orderCol, orderMode)
{
	var id      = $("#hiddenFilterPtID").val();
	var name    = $("#hiddenFilterPtName").val();
	var sex     = $("#hiddenFfilterSex").val();
	var showing = $("#hiddenShowing").val();

	var address = 'patient_list.php?orderCol=' + encodeURIComponent(orderCol)
                + '&orderMode=' + encodeURIComponent(orderMode);

	if(id != "")	              address += '&filterPtID=' + encodeURIComponent(id);
	if(name != "")	              address += '&filterPtName=' + encodeURIComponent(name);
	if(sex == "M" || sex == "F")  address += '&filterSex=' + encodeURIComponent(sex);
	if(showing != 10)             address += '&showing=' + encodeURIComponent(showing);

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
	var id         = $("#encryptedPtID").val();
	var name       = $("#hiddenFilterPtName").val();
	var sex        = $("#hiddenFilterSex").val();
	var ageMin     = $("#hiddenFilterAgeMin").val();
	var ageMax     = $("#hiddenFilterAgeMax").val();
	var modality   = $("#hiddenFilterModality").val();
	var stDateFrom = $("#hiddenStDateFrom").val();
	var stDateTo   = $("#hiddenStDateTo").val();
	var stTimeTo   = $("#hiddenStTimeTo").val();
	var showing    = $("#hiddenShowing").val();

	var address = 'study_list.php?'

	if($("#mode").val() == 'patient')  
	{
		address += 'mode=patient&orderCol=' + encodeURIComponent(orderCol)
                +  '&orderMode=' + encodeURIComponent(orderMode)
                +  '&encryptedPtID=' + encodeURIComponent($("#encryptedPtID").val());
	}
	else
	{
		address += 'orderCol=' + encodeURIComponent(orderCol)
                +  '&orderMode=' + encodeURIComponent(orderMode);
		if(id != "")	              address += '&filterPtID=' + encodeURIComponent(id);
		if(name != "")                address += '&filterPtName=' + encodeURIComponent(name);
		if(sex == "M" || sex == "F")  address += '&filterSex=' + encodeURIComponent(sex);
	}

	if(modality != "all")	address += '&filterModality=' + encodeURIComponent(modality);
	if(ageMin != "")		address += '&filterAgeMin=' + encodeURIComponent(ageMin);
	if(ageMax != "")		address += '&filterAgeMax=' + encodeURIComponent(ageMax);
	if(stDateFrom != "")	address += '&stDateFrom=' + encodeURIComponent(stDateFrom);
	if(stDateTo != "")		address += '&stDateTo=' + encodeURIComponent(stDateTo);
	if(stTimeTo != "")		address += '&stTimeTo=' + encodeURIComponent(stTimeTo);
	if(showing != 10)		address += '&showing=' + encodeURIComponent(showing);

	location.replace(address);
}

//--------------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------------
// For series list
//--------------------------------------------------------------------------------------------------
function CreateListAddressForSeriesList(mode, orderCol, orderMode)
{
	var id          = $("#hiddenFilterPtID").val();
	var name        = $("#hiddenFilterPtName").val();
	var sex         = $("#hiddenFilterSex").val();
	var ageMin      = $("#hiddenFilterAgeMin").val();
	var ageMax      = $("#hiddenFilterAgeMax").val();
	var modality    = $("#hiddenFilterModality").val();
	var srDateFrom  = $("#hiddenSrDateFrom").val();
	var srDateTo    = $("#hiddenSrDateTo").val();
	var srTimeTo    = $("#hiddenSrTimeTo").val();
	var description = $("#hiddenFilterSrDescription").val();
	var showing     = $("#hiddenShowing").val();

	var address = 'series_list.php?'

	if(mode == 'study')  
	{
		address += 'mode=study&orderCol=' + encodeURIComponent(orderCol)
                +  '&orderMode=' + encodeURIComponent(orderMode)
                +  '&studyInstanceUID=' + encodeURIComponent($("#studyInstanceUID").val());
	}
	else
	{
		if(mode == 'today')
		{
			address += 'mode=today&';
		}
		address += 'orderCol=' + encodeURIComponent(orderCol)
		        +  '&orderMode=' + encodeURIComponent(orderMode);

		if(id != "")	              address += '&filterPtID=' + encodeURIComponent(id);
		if(name != "")	              address += '&filterPtName=' + encodeURIComponent(name);
		if(sex == "M" || sex == "F")  address += '&filterSex=' + encodeURIComponent(sex);
		if(ageMin != "")              address += '&filterAgeMin=' + encodeURIComponent(ageMin);
		if(ageMax != "")              address += '&filterAgeMax=' + encodeURIComponent(ageMax);
	}

	if(mode != 'today')
	{
		if(srDateFrom != "")  address += '&srDateFrom=' + encodeURIComponent(srDateFrom);
		if(srDateTo != "")	  address += '&srDateTo=' + encodeURIComponent(srDateTo);
		if(srTimeTo != "")	  address += '&srTimeTo=' + encodeURIComponent(srTimeTo);
	}

	if(modality != "all")   address += '&filterModality=' + encodeURIComponent(modality);
	if(description != "")	address += '&filterSrDescription=' + encodeURIComponent(description);
	if(showing != 10)		address += '&showing=' + encodeURIComponent(showing);

	return address;
}


function ChangeOrderOfSeriesList(orderCol, orderMode)
{
	location.replace(CreateListAddressForSeriesList($("#mode").val(), orderCol, orderMode));
}


function ShowSeriesDetail(colorSet, studyInstanceUID, seriesInstanceUID)
{
	var mode    = $("#mode").val();

	location.href = "series_detail.php?studyInstanceUID=" + encodeURIComponent(studyInstanceUID)
                  + "&seriesInstanceUID=" + encodeURIComponent(seriesInstanceUID)
                  + "&listTabName=" + ((mode == 'today') ? "Today's series" : "Series list");
}

function ShowCADResultFromSeriesList(seriesID, studyInstanceUID, seriesInstanceUID, personalFeedbackFlg)
{
	// プルダウンメニューで選択されたOptionの値を取得
	var tmpStr = $("#cadMenu"+seriesID).val().split("^");

	var cadName = tmpStr[0];
	var version = tmpStr[1];

	var address = 'cad_results/show_cad_results.php'
                + '?cadName=' + encodeURIComponent(cadName) + '&version=' + encodeURIComponent(version)
                + '&studyInstanceUID=' + encodeURIComponent(studyInstanceUID)
                + '&seriesInstanceUID=' + encodeURIComponent(seriesInstanceUID);
	
	if(personalFeedbackFlg == 1)  address += '&feedbackMode=personal';

	if($("#mode").val() == "today")		address += '&srcList=todaysSeries';
	else								address += '&srcList=series';
	
	location.href = address;
}

function ChangeCADMenu(source, seriesID, menuID, execCADFlg)
{
	// プルダウンメニューで選択されたOptionの値を取得
	var tmpStr = $("#cadMenu"+seriesID).val().split("^");

	var flg      = parseInt(tmpStr[2]);
	var dateTime = tmpStr[3];

	//alert(execCADFlg + ' ' + flg);

	if(source == 'todaysSeriesList')	dateTime = dateTime.substr(11);

	if(execCADFlg==1)
	{
		if(flg == 0)	$("#execButton"+seriesID).show();
		else			$("#execButton"+seriesID).hide()
	}

	if(flg == 2)
	{
		$("#resultButton" + seriesID).show();
		$("#cadInfo"+seriesID).html('Executed at ' + dateTime);
	}
	else
	{
		$("#resultButton" + seriesID).hide();
	
		if(flg == 1)
		{
			$("#cadInfo"+seriesID).html('Registered in CAD job list');
		}
		else
		{
			if(source == 'todaysSeriesList')
			{
				$("#cadInfo"+seriesID).html('<span style="color:#fff;">Not executed</span>');
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
	// プルダウンメニューで選択されたOptionの値を取得
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
function ShowCADResultFromCADLog(cadName, version, studyInstanceUID, seriesInstanceUID, personalFBFlg)
{
	var address = 'cad_results/show_cad_results.php'
                + '?cadName=' + encodeURIComponent(cadName)
                + '&version=' + encodeURIComponent(version)
                + '&studyInstanceUID=' + encodeURIComponent(studyInstanceUID)
                + '&seriesInstanceUID=' + encodeURIComponent(seriesInstanceUID);
	
	if(personalFBFlg == 1)  address += '&feedbackMode=personal';

	if($("#mode").val() == "today")		address += '&srcList=todaysCAD';
	else								address += '&srcList=cadLog';

	location.href = address;
}



function ChangeOrderOfCADList(orderCol, orderMode)
{
	var mode          = $("#mode").val();
	var id            = $("#hiddenFilterPtID").val();
	var name          = $("#hiddenFilterPtName").val();
	var sex           = $("#hiddenFilterSex").val();
	var ageMin        = $("#hiddenFilterAgeMin").val();
	var ageMax        = $("#hiddenFilterAgeMax").val();
	var modality      = $("#hiddenFilterModality").val();
	var srDateFrom    = $("#hiddenSrDateFrom").val();
	var srDateTo      = $("#hiddenSrDateTo").val();
	var srTimeTo      = $("#hiddenSrTimeTo").val();
	var cadDateFrom   = $("#hiddenCadDateFrom").val();
	var cadDateTo     = $("#hiddenCadDateTo").val();
	var cadTimeTo     = $("#hiddenCadTimeTo").val();
	var filterCadID   = $("#hiddenFilterCadID ").val();
	var filterCAD     = $("#hiddenFilterCAD").val();
	var filterVersion = $("#hiddenFilterVersion").val();
	var personalFB    = $("#hiddenFilterPersonalFB").val();
	var consensualFB  = $("#hiddenFilterConsensualFB").val();
	var filterTP      = $("#hiddenFilterTP").val();
	var filterFN      = $("#hiddenFilterFN").val();
	var showing       = $("#hiddenShowing").val();

	var address = 'cad_log.php?'

	if(mode == 'today')
	{
		address += 'mode=today&';
	}
	
	address += 'orderCol=' + encodeURIComponent(orderCol)
            +  '&orderMode=' + encodeURIComponent(orderMode);

	if(id != "")	              address += '&filterPtID=' + encodeURIComponent(id);
	if(name != "")	              address += '&filterPtName=' + encodeURIComponent(name);
	if(sex == "M" || sex == "F")  address += '&filterSex=' + encodeURIComponent(sex);
	if(ageMin != "")              address += '&filterAgeMin=' + encodeURIComponent(ageMin);
	if(ageMax != "")              address += '&filterAgeMax=' + encodeURIComponent(ageMax);

	if(mode != 'today')
	{
		if(cadDateFrom != "")	address += '&cadDateFrom=' + encodeURIComponent(cadDateFrom);
		if(cadDateTo != "")		address += '&cadDateTo=' + encodeURIComponent(cadDateTo);
		if(cadTimeTo != "")		address += '&cadTimeTo=' + encodeURIComponent(cadTimeTo);
	}

	if(srDateFrom != "")		address += '&srDateFrom=' + encodeURIComponent(srDateFrom);
	if(srDateTo != "")			address += '&srDateTo=' + encodeURIComponent(srDateTo);
	if(srTimeTo != "")			address += '&srTimeTo=' + encodeURIComponent(srTimeTo);
	if(modality != "all")       address += '&filterModality=' + encodeURIComponent(modality);
	if(showing != 10)		    address += '&showing=' + encodeURIComponent(showing);
	if(filterCadID != "all")    address += '&filterCadID=' + encodeURIComponent(filterCadID);
	if(filterCAD != "all")	    address += '&filterCAD=' + encodeURIComponent(filterCAD);
	if(filterVersion != "all")  address += '&filterVersion=' + encodeURIComponent(filterVersion);
	if(personalFB != "all")     address += '&personalFB=' + encodeURIComponent(personalFB);
	if(consensualFB != "all")	address += '&consensualFB=' + encodeURIComponent(consensualFB);
	if(filterTP != "all")       address += '&filterTP=' + encodeURIComponent(filterTP);
	if(filterFN != "all")       address += '&filterFN=' + encodeURIComponent(filterFN);

	location.href = address;
}
//--------------------------------------------------------------------------------------------------
