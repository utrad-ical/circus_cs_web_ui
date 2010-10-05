
function htmlspecialchars(ch) { 
    ch = ch.replace(/&/g,"&amp;");
    ch = ch.replace(/"/g,"&quot;");
    ch = ch.replace(/'/g,"&#039;");
    ch = ch.replace(/</g,"&lt;");
    ch = ch.replace(/>/g,"&gt;");
    return ch ;
}

//--------------------------------------------------------------------------------------------------
// For patient list
//--------------------------------------------------------------------------------------------------
function ShowStudyList(idNum, encryptedPtID)
{
	location.href = 'study_list.php?mode=patient&encryptedPtID=' + encryptedPtID;
}

function ChangeOrderOfPatientList(orderCol, orderMode)
{
	var id      = $("#hiddenFilterPtID").val();
	var name    = $("#hiddenFilterPtName").val();
	var sex     = $("#hiddenFfilterSex").val();
	var showing = $("#hiddenShowing").val();

	var address = 'patient_list.php?orderCol=' + orderCol + '&orderMode=' + orderMode;

	if(id != "")	              address += '&filterPtID=' + id;
	if(name != "")	              address += '&filterPtName=' + name;
	if(sex == "M" || sex == "F")  address += '&filterSex=' + sex;
	if(showing != 10)			  address += '&showing=' + showing;

	location.replace(address);
}
//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
// For study list
//--------------------------------------------------------------------------------------------------
function ShowSeriesList(idNum, studyInstanceUID)
{
	location.href = 'series_list.php?mode=study&studyInstanceUID=' + studyInstanceUID;
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
		address += 'mode=patient&orderCol=' + orderCol + '&orderMode=' + orderMode
                +  '&encryptedPtID=' + $("#encryptedPtID").val();
	}
	else
	{
		address += 'orderCol=' + orderCol + '&orderMode=' + orderMode;
		if(id != "")	              address += '&filterPtID=' + id;
		if(name != "")	              address += '&filterPtName=' + name;
		if(sex == "M" || sex == "F")  address += '&filterSex=' + sex;
	}

	if(modality != "all")	address += '&filterModality=' + modality;
	if(ageMin != "")		address += '&filterAgeMin=' + ageMin;
	if(ageMax != "")		address += '&filterAgeMax=' + ageMax;
	if(stDateFrom != "")	address += '&stDateFrom=' + stDateFrom;
	if(stDateTo != "")		address += '&stDateTo=' + stDateTo;
	if(stTimeTo != "")		address += '&stTimeTo=' + stTimeTo;
	if(showing != 10)		address += '&showing=' + showing;

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
		address += 'mode=study&orderCol=' + orderCol + '&orderMode=' + orderMode
                +  '&studyInstanceUID=' + $("#studyInstanceUID").val();
	}
	else
	{
		if(mode == 'today')
		{
			address += 'mode=today&orderCol=' + orderCol + '&orderMode=' + orderMode;
		}
		else
		{
			address += 'orderCol=' + orderCol + '&orderMode=' + orderMode;
		}

		if(id != "")	              address += '&filterPtID=' + id;
		if(name != "")	              address += '&filterPtName=' + name;
		if(sex == "M" || sex == "F")  address += '&filterSex=' + sex;
		if(ageMin != "")              address += '&filterAgeMin=' + ageMin;
		if(ageMax != "")              address += '&filterAgeMax=' + ageMax;
	}

	if(mode != 'today')
	{
		if(srDateFrom != "")	address += '&srDateFrom=' + srDateFrom;
		if(srDateTo != "")		address += '&srDateTo=' + srDateTo;
		if(srTimeTo != "")		address += '&srTimeTo=' + srTimeTo;
	}

	if(modality != "all")   address += '&filterModality=' + modality;
	if(description != "")	address += '&filterSrDescription=' + description;
	if(showing != 10)		address += '&showing=' + showing;

	return address;
}


function ChangeOrderOfSeriesList(orderCol, orderMode)
{
	location.replace(CreateListAddressForSeriesList($("#mode").val(), orderCol, orderMode));
}


function ShowSeriesDetail(colorSet, studyInstanceUID, seriesInstanceUID)
{
	var mode    = $("#mode").val();

	location.href = "series_detail.php?studyInstanceUID=" + studyInstanceUID
                  + "&seriesInstanceUID=" + seriesInstanceUID
                  + "&listTabName=" + ((mode == 'today') ? "Today's series" : "Series list");
}

function ShowCADResultFromSeriesList(seriesID, studyInstanceUID, seriesInstanceUID, personalFeedbackFlg)
{
	// プルダウンメニューで選択されたOptionの値を取得
	var tmpStr = $("#cadMenu"+seriesID).val().split("^");

	var cadName = tmpStr[0];
	var version = tmpStr[1];

	var address = 'cad_results/show_cad_results.php'
                + '?cadName=' + cadName + '&version=' + version
                + '&studyInstanceUID=' + studyInstanceUID
                + '&seriesInstanceUID=' + seriesInstanceUID;
	
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
		if(flg == 0)	$("#execButton"+seriesID).removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		else			$("#execButton"+seriesID).attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
	}

	if(flg == 2)
	{
		$("#resultButton" + seriesID).removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		$("#cadInfo"+seriesID).html('Executed at: ' + dateTime).show();
	}
	else
	{
		$("#resultButton" + seriesID).attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
	
		if(flg == 1)
		{
			$("#cadInfo"+seriesID).html('Registered in CAD job list').show();
		}
		else
		{
			if(source == 'todaysSeriesList')
			{
				$("#cadInfo"+seriesID).html('<span style="color:#fff;">Not executed</span>').show();
			}
			else
			{
				$("#cadInfo"+seriesID).html('').hide();
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

	var address = 'cad_job/cad_execution.php?cadName=' + cadName + '&version=' + version
	            + '&studyInstanceUID=' + studyInstanceUID 
                + '&seriesInstanceUID=' + seriesInstanceUID;

	if($("#mode").val() == "today")		address += '&srcList=todaysSeries';
	else								address += '&srcList=series';

	location.href=address;
}


//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
// For CAD log
//--------------------------------------------------------------------------------------------------
function ShowCADResultFromCADLog(cadName, version, studyInstanceUID, seriesInstanceUID, personalFeedbackFlg)
{
	var address = 'cad_results/show_cad_results.php'
                + '?cadName=' + cadName + '&version=' + version
                + '&studyInstanceUID=' + studyInstanceUID
                + '&seriesInstanceUID=' + seriesInstanceUID;
	
	if(personalFeedbackFlg == 1)  address += '&feedbackMode=personal';

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
		address += 'mode=today&orderCol=' + orderCol + '&orderMode=' + orderMode;
	}
	else
	{
		address += 'orderCol=' + orderCol + '&orderMode=' + orderMode;
	}

	if(id != "")	              address += '&filterPtID=' + id;
	if(name != "")	              address += '&filterPtName=' + name;
	if(sex == "M" || sex == "F")  address += '&filterSex=' + sex;
	if(ageMin != "")              address += '&filterAgeMin=' + ageMin;
	if(ageMax != "")              address += '&filterAgeMax=' + ageMax;

	if(mode != 'today')
	{
		if(cadDateFrom != "")	address += '&cadDateFrom=' + cadDateFrom;
		if(cadDateTo != "")		address += '&cadDateTo=' + cadDateTo;
		if(cadTimeTo != "")		address += '&cadTimeTo=' + cadTimeTo;
	}

	if(srDateFrom != "")		address += '&srDateFrom=' + srDateFrom;
	if(srDateTo != "")			address += '&srDateTo=' + srDateTo;
	if(srTimeTo != "")			address += '&srTimeTo=' + srTimeTo;
	if(modality != "all")       address += '&filterModality=' + modality;
	if(showing != 10)		    address += '&showing=' + showing;
	if(filterCadID != "all")    address += '&filterCadID=' + filterCadID;
	if(filterCAD != "all")	    address += '&filterCAD=' + filterCAD;
	if(filterVersion != "all")  address += '&filterVersion=' + filterVersion;
	if(personalFB != "all")     address += '&personalFB=' + personalFB;
	if(consensualFB != "all")	address += '&consensualFB=' + consensualFB;
	if(filterTP != "all")       address += '&filterTP=' + filterTP;
	if(filterFN != "all")       address += '&filterFN=' + filterFN;

	location.href = address;
}
//--------------------------------------------------------------------------------------------------
