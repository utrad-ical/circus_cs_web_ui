/**
 * CAD Results
 */

function CreateEvalStr(lesionArr)
{
	var evalArr = new Array();

	for(var j=0; j<lesionArr.length; j++)
	{
		if($("#lesionBlock" + lesionArr[j] + " input[name:'radioCand" + lesionArr[j] + "']:checked").val() == undefined)
		{
			evalArr.push(-99);
		}
		else
		{
			evalArr.push($("#lesionBlock" + lesionArr[j] + " input[name:'radioCand" + lesionArr[j] + "']:checked").val());
		}
	}
	return evalArr.join("^");
}

function RegistFeedback(feedbackMode, interruptFlg, candStr, evalStr, dstAddress)
{
	$.post("feedback_registration.php",
			{
				jobID:  $("#jobID").val(),
				cadName: $("#cadName").val(),
				version: $("#version").val(),
				interruptFlg: interruptFlg,
				fnFoundFlg: $('input[name="fnFoundFlg"]:checked').val(),
				feedbackMode: feedbackMode,
				candStr: candStr,
				evalStr: evalStr
			},
			function(data){
				if(interruptFlg == 0)	alert(data.message);
				if(dstAddress != "")
				{
					if(dstAddress == "historyBack")  history.back();
					else						   	 location.replace(dstAddress);
				}
			},
			"json");
}

function MovePageWithTempRegistration(address)
{
	if($("#registTime").val() == "" && $("#interruptFlg").val() == 1)
	{
		var candStr = $("#candStr").val();
		var lesionArr = candStr.split("^");
		var evalStr = CreateEvalStr(lesionArr);

		RegistFeedback($("#feedbackMode").val(), 1, candStr, evalStr, address);
	}
	else
	{
		if(address == "historyBack")
			history.back();
		else
			location.href=address;
	}
}


function ShowFNinput()
{
	var address = 'fn_input.php'
		+ '?jobID=' + $("#jobID").val()
		+ '&feedbackMode=' + $("#feedbackMode").val();
	MovePageWithTempRegistration(address);
}

function ChangeCondition(mode, feedbackMode)
{
	var address = 'show_cad_results.php?jobID=' + $("#jobID").val()
		+ '&feedbackMode=' + feedbackMode
		+ '&sortKey=' + $("#sortKey").val()
		+ '&sortOrder=' + $(".sort-by input[name='sortOrder']:checked").val();

	if($("#remarkCand").val() > 0)  address += '&remarkCand=' + $("#remarkCand").val();

	if((feedbackMode == "personal" || feedbackMode == "consensual") && $("#registTime").val() == "")
	{
		var candStr = $("#candStr").val();
		var lesionArr = candStr.split("^");
		var evalStr = CreateEvalStr(lesionArr);

		if(mode == 'registration')
		{
			evalArr = evalStr.split("^");
			RegistFeedback(feedbackMode, 0, candStr, evalStr, address);
		}
		else if(mode == 'changeSort' && $("#interruptFlg").val()==1)
		{
			RegistFeedback(feedbackMode, 1, candStr, evalStr, address);
		}
		else  location.replace(address);
	}
	else location.replace(address);
}

function ChangeFeedbackMode(feedbackMode)
{
	var address = 'show_cad_results.php?jobID=' + $("#jobID").val()
		+ '&feedbackMode=' + feedbackMode;

	if($("#remarkCand").val() > 0)  address += '&remarkCand=' + $("#remarkCand").val();

	MovePageWithTempRegistration(address);
}

function ChangeRegistCondition()
{
	var checkCnt = $("input[name^='radioCand']:checked").length;

	var tmpStr = 'Candidate classification: <span style="color:'
		+ (($("#candNum").val()==checkCnt) ? 'blue;">complete' : 'red;">incomplete') + '</span><br/>'
		+ 'FN input: <span style="color:'
		+ (($("#fnInputStatus").val()==1) ? 'blue;">complete' : 'red;">incomplete') + '</span>';

	if($("#registTime").val() =="" && $("#candNum").val()==checkCnt && $("#fnInputStatus").val()==1)
	{
		$("#registBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		$("#interruptFlg").val(0);
	}
	else
	{
		$("#registBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
		$("#interruptFlg").val(1);
	}

	if($("#groupID").val() != 'demo')
	{
		$("#registCaution").html(tmpStr);

		$("#interruptFlg").val(1);

		// Measures to click button of menu bar during lesion candidate classification
		$("#linkAbout, #menu a, #listTab").click(
			function(event){
				if(!event.isDefaultPrevented())
				{
					event.preventDefault();  // prevent link action

					if(confirm("Do you want to save the changes?"))
					{
						MovePageWithTempRegistration(event.currentTarget.href);
					}
				}
			}
		);
	}
}


function ChangeLesionClassification(candID, label)
{
	if($("#feedbackMode").val()=="personal" && $("#registTime").val()=="")
	{
		var options = "Candidate " + candID + ":" + label;

		$.post("write_feedback_action_log.php",
			{
				jobID: $("#jobID").val(),
				action: 'classify',
				options: options
			}
		);
	}

	ChangeRegistCondition();
}


function ShowCADDetail(imgNum)
{
	$("#slider").slider("value", imgNum);

	if($("#registTime").val() == "" && $("#interruptFlg").val() == 1)
	{
		var candStr = $("#candStr").val();
		var lesionArr = candStr.split("^");
		var evalStr = CreateEvalStr(lesionArr);

		RegistFeedback($("#feedbackMode").val(), 1, candStr, evalStr, "");
	}

	$("#cadResult, #cadResultTab").hide();
	$("#cadDetailTab, #cadDetail").show();
	$('#container').height( $(document).height() - 10 );

}

function ShowCADResult()
{
	$("#cadDetailTab, #cadDetail").hide();
	$("#cadResult, #cadResultTab").show();
	$('#container').height( $(document).height() - 10 );
}

function Plus()
{
	var value = $("#slider").slider("value");

	if(value < $("#slider").slider("option", "max"))
	{
		value++;
		$("#sliderValue").html(value);
		$("#slider").slider("value", value);
	}
}

function Minus()
{
	var value = $("#slider").slider("value");

	if($("#slider").slider("option", "min") <= value)
	{
		value--;
		$("#sliderValue").html(value);
		$("#slider").slider("value", value);
	}
}

function ChangePresetMenu()
{
	var tmpStr = $("#presetMenu").val().split("^");
	$("#windowLevel").val(tmpStr[0]);
	$("#windowWidth").val(tmpStr[1]);
	$("#presetName").val($("#presetMenu option:selected").text());

	JumpImgNumber($("#slider").slider("value"));
}

function JumpImgNumber(imgNum)
{
	$.post("../jump_image.php",
		{ studyInstanceUID: $("#studyInstanceUID").val(),
			seriesInstanceUID: $("#seriesInstanceUID").val(),
			imgNum: imgNum,
			windowLevel: $("#windowLevel").val(),
			windowWidth: $("#windowWidth").val(),
			presetName:  $("#presetName").val()},
			function(data){

			if(data.errorMessage != "")
			{
				alert(data.errorMessage);
			}
			else if(data.imgFname != "")
			{
				$("#imgArea").attr("src", '../' + data.imgFname);
				$("#imgBlock span").remove();
				$("#sliceLocation").val(data.sliceLocation);

				if($("#checkVisibleCand").is(':checked'))
				{
					for(var i=0; i<candData.length; i++)
					{
						if(candData[i][4] == data.imgNum)
						{
							var xPos = parseInt(candData[i][2] * parseFloat($("#detailDispWidth").val())
																				 / parseFloat($("#detailOrgWidth").val())  + 0.5);
							var yPos = parseInt(candData[i][3] * parseFloat($("#detailDispHeight").val())
																								/ parseFloat($("#detailOrgHeight").val()) + 0.5);

							plotDots(i+1, xPos, yPos, 0);
						}
					}
				}
			}
		}, "json");
}

function plotDots(id, x, y, colorSet)
{
	var dotOffsetX = -1;
	var dotOffsetY = -1;
	var labelOffsetX = 0;
	var labelOffsetY = 0;

	var labelBaseX = 3;
	var labelBaseY = 0;
	var color = "#ff00ff";

	// for IE
	if (document.all)
	{
		dotOffsetX = 2;
		labelOffsetX = 3;
		labelOffsetY = 1;
	}

	var htmlStr = '<span id="dot' + id + '" class="dot" style="top:' + (y+dotOffsetY) + 'px; '
								+ 'left:' + (x+dotOffsetX) + 'px; height:3px; width:3px; padding:0px; '
								+ 'background-color:' + color + ';position:absolute;"></span>'
				+ '<span id="label' + id + '" class="dot" style="top:' + (y+labelBaseY+labelOffsetY) + 'px;'
				+ ' left:' + (x+labelBaseX+labelOffsetX) + 'px; color:' + color + ';'
				+ ' filter:dropshadow(color=#000000 offX=1 offY=0) dropshadow(color=#000000 offX=-1 offY=0)'
				+ ' dropshadow(color=#000000 offX=0 offY=1) dropshadow(color=#000000 offX=0 offY=-1);'
				+ ' font-weight:bold;position:absolute;">' + id + '</span>';

	$("#imgBlock").append(htmlStr);
}

function EditCandidateTag(jobID, candID, feedbackMode, userID)
{
	var dstAddress = "../cad_results/edit_candidate_tag.php?jobID=" + jobID + "&candID=" + candID
									 + "&feedbackMode=" + feedbackMode + "&userID=" + userID;
	window.open(dstAddress,"Edit lesion candidate tag", "width=400,height=250,location=no,resizable=no,scrollbars=1");
}

function ChangeVisibleCand()
{
	if($("#checkVisibleCand").is(':checked'))
		JumpImgNumber($("#slider").slider("value"));
	else
		$("#imgBlock span").remove();
}

var rowClickHandler = function(event) {

	if ($(event.target).parents('td.tagColumn').length == 0)
	{
		$('#checkVisibleFN').attr('checked', 'checked');
		// for jQuery 1.3.2
		var imgNum = $(event.target).parents('tr').children('td.z').html();
		$("#slider").slider("value", imgNum);

		// for jQuery 1.4.3
		//var idx = $(event.target).parents('tr').index();
		//var item = fnData[idx];
		//$("#slider").slider("value", item.z);
	}
}

function setupSlider(value, min, max)
{
	$("#slider").slider({
		value: value,
		min: min,
		max: max,
		step: 1,
		slide: function(event, ui) {
			$("#sliderValue").html(ui.value);
		},
		change: function(event, ui) {
			$("#sliderValue").html(ui.value);
			JumpImgNumber(ui.value);
		}
	});
	$("#slider").css("width", "220px");
	$("#sliderValue").html(jQuery("#slider").slider("value"));
}

$(function() {
	$('#posTable tbody tr').live('click', rowClickHandler);
	$("input[name='fnFoundFlg']").change(function() {

		var options = "";

		if($(this).val() == 0)
		{
			$("#fnInputStatus").val(1);
			$("#fnInputBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
			options = "FN  not found";
		}
		else
		{
			$("#fnInputBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
				$("#fnInputStatus").val(($("#fnNum").val() > 0) ? 1 : 0);
			options = "FN  found";
		}

		if($("#feedbackMode").val()=="personal")
		{
			$.post("write_feedback_action_log.php",
					{ jobID: $("#jobID").val(),
						action: 'select',
						options: options
					});
		}

		ChangeRegistCondition();
	});
});
