function JumpImgNumber(imgNum)
{
	$.post("../jump_image.php",
		{ studyInstanceUID: $("#studyInstanceUID").val(),
		  seriesInstanceUID: $("#seriesInstanceUID").val(),
		  imgNum: imgNum,
		  windowLevel: $("#windowLevel").val(),
		  windowWidth: $("#windowWidth").val(),
		  presetName:  $("#presetName").val() },
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

				if($('#checkVisibleFN').attr('checked'))
				{
					for(var i=0; i<fnData.length; i++)
					{
						if(fnData[i].z == data.imgNum)
						{
							var colorSet = ($("#feedbackMode").val()=="consensual") ? fnData[i].colorSet : "0";

							var xPos = parseInt(fnData[i].x * parseFloat($("#dispWidth").val())
				                                 / parseFloat($("#orgWidth").val())  + 0.5);
							var yPos = parseInt(fnData[i].y * parseFloat($("#dispHeight").val())
                                                 / parseFloat($("#orgHeight").val()) + 0.5);

							plotDots(i+1, xPos, yPos, colorSet);
						}
					}
				}
			}
		}, "json");
}

//function JumpImgNumBySliceLocation(origin, pitch, offset, fNum)
//{
//	var sliceLoc = $("#sliceLocation").val();
//	
//	var imgNum = parseInt(((sliceLoc - origin) / pitch) + offset + 1.5);
//	
//	if(imgNum < (offset + 1))   imgNum = offset + 1;
//	else if(imgNum > fNum)  	imgNum = fNum;
//	
//	JumpImgNumber(imgNum);
//}

function plotDots(id, x, y, colorSet)
{
	var dotOffsetX = -1;
	var dotOffsetY = -1;
	var labelOffsetX = 0;
	var labelOffsetY = 0;

	var labelBaseX = 0;
	var labelBaseY = 0;
	var color = "#ff00ff";

	switch(colorSet)
	{
		case "1": 
			labelBaseX = 3;
			labelBaseY = -20;
			color = "#228b22";
			break;
	
		case "2":
			labelBaseX = (id < 10) ? -11 : -20;
			labelBaseY = -20;
			color = "#ff8000";
			break;
	
		case "3":
			labelBaseX = (id < 10) ? -11 : -20;
			color = "#ff0000";
			break;

		default: // case 0
			labelBaseX = 3;
			break;
	}

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

function ChangeVisibleFN()
{
	$("#visibleFlg").val($("#checkVisibleFN").is(':checked') ? 1 : 0);

	if($("#checkVisibleFN").is(':checked'))	JumpImgNumber($("#slider").slider("value"));
	else									$("#imgBlock span").remove();
}


//--------------------------------------------------------------------------------------------------
// Fnuctions for location table
//--------------------------------------------------------------------------------------------------
function ResetFnTable()
{
	if(confirm('Do you remove all item(s)?'))
	{
		$("#checkVisibleFN").attr('checked', 'checked');
		fnData.splice(0);
		RefreshTable();
		$("#resetBtn").hide();
	}
}

function UndoFnTable()
{
	if(confirm('Undo FN locations?'))
	{
		fnData.splice(0);

		for(var i=0; i<oldFnData.length; i++)
		{
			var item = {
				"x": oldFnData[i].x,
      			"y": oldFnData[i].y,
     	 		"z": oldFnData[i].z,
      			"rank": oldFnData[i].rank,
      			"enteredBy": oldFnData[i].enteredBy,
				"idStr": oldFnData[i].idStr,
				"colorSet": oldFnData[i].colorSet
			};
	
			fnData.push(item);
		}
		RefreshTable();
		$("#undoBtn").hide();
	}
}

function ConfirmFNLocation(address)
{
	if(confirm('Do you save entered FN locations?'))
	{
		$.post("fn_registration.php",
			{ execID: $("#execID").val(),
			  fnData: JSON.stringify(fnData),
			  feedbackMode: $("#feedbackMode").val(),
			  dstAddress:address},
  			  function(data){
				if(data.errorMessage != "")
				{
					alert(data.errorMessage);
				}
				else
				{
					if(data.dstAddress=="")
					{
						data.dstAddress = "fn_input.php";

						if(confirm('Successfully saved in feedback database. Do you back to CAD result?'))
						{
							data.dstAddress = 'show_cad_results.php';
						}

						data.dstAddress += '?execID=' + $("#execID").val()
	                                    + '&feedbackMode=' + $("#feedbackMode").val();

					}
					location.href=data.dstAddress;
				}
			}, "json");
	}
}

function ChangePresetMenu()
{
	var tmpStr = $("#presetMenu option:selected").val().split("^");
	
	$("#presetName").val($("#presetMenu option:selected").text());
	$("#windowLevel").val(tmpStr[0]);
	$("#windowWidth").val(tmpStr[1]);
	
	JumpImgNumber($("#slider").slider("value"));
}

function AddFnTable(id, item)
{
	var feedbackMode = $("#feedbackMode").val();

	var htmlStr = '<tr id="row' + id + '"' + ((id%2==1) ? ' class="column">' : '>');

	if($("#registTime").val() == "" || $("#status").val() != 2)
	{
		htmlStr += '<td class="operationColumn"><input type="checkbox" name="rowCheckList[]"'
                +  ' onclick="RefreshOperationButtons();"value="' + id + '"></td>';
	}

	var tdBaseStr = ' style="color:' + ((feedbackMode == "consensual") ? '#ff00ff;">' : 'black;">');

	htmlStr += '<td class="id"' + tdBaseStr + (id+1) + '</td>'
            +  '<td class="x"' + tdBaseStr + item.x + '</td>'
            +  '<td class="y"' + tdBaseStr + item.y + '</td>'
            +  '<td class="z"' + tdBaseStr + item.z + '</td>'
            +  '<td class="rank"' + tdBaseStr + item.rank + '</td>';

	if(feedbackMode == "consensual")
	{
		htmlStr += '<td class="enteredBy"' + tdBaseStr + item.enteredBy + '</td>'
				+  '<td class="idStr" style="display:none;">' + item.idStr + '</td>';
				+  '<td class="colorSet" style="display:none;">' + item.colorSet + '</td>';
	}

	$("#posTable tbody").append(htmlStr);
	$('.form-btn').hoverStyle({normal: 'form-btn-normal', hover: 'form-btn-hover',disabled: 'form-btn-disabled'});
}

function RefreshOperationButtons()
{
	var actionVal = $("#actionMenu").val();
	var checkCnt = $("input[name='rowCheckList[]']:checked").length;
	var htmlStr = "&nbsp;";

	if(checkCnt > 0)
	{
		if(actionVal == "delete") {
			htmlStr = '<span style="color:blue;">checked ' + checkCnt + ' item(s) delete.</span>';
		} else if(actionVal == "integrate") {
			if(checkCnt >= 2) {
				htmlStr = '<span style="color:blue;">checked ' + checkCnt + ' items itegrate to one.</span>';
			} else {
				htmlStr = '<span style="color:red;">more than 2 items required.</span>';
			}
		}
	}

	$("#tableActionMsg").html(htmlStr);

	if(checkCnt > 0)
	{
		$("#confirmBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
		$("#actionMenu").removeAttr("disabled");

		if((actionVal == "delete" && checkCnt > 0) || (actionVal == "integrate" && checkCnt >= 2))
		{
			$("#actionBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		}
		else
		{
			$("#actionBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
		}
	}
	else
	{
		$("#confirmBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		$("#actionBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
		$("#actionMenu").attr("disabled", "disabled");
	}
}

function TableOperation()
{
	var actionVal = $("#actionMenu").val();
	var checkCnt = $("input[name='rowCheckList[]']:checked").length;
	
	switch(actionVal)
	{
		case "delete":
			var str = 'Do you delete selected location(s)?';
			if(confirm(str))	DeleteLocationRows();
			else				$("input[name='rowCheckList[]']").removeAttr("checked");
		    break;
		
		case "integrate":
			IntegrateLocationRows();
			break;
	}
	RefreshOperationButtons();

	if($("#status").val()==0)			$("#resetBtn").show();
	else if($("#status").val()==1)		$("#undoBtn").show();

	// 候補分類入力中にメニューバーを押された場合の対策
	$("#linkAbout, #menu a, .tabArea a[title!='FN input']").click(
		function(event){ 

		if(!event.isDefaultPrevented())
		{
			event.preventDefault();  // prevent link action

			if(confirm("Do you want to save the changes?"))
			{
				ConfirmFNLocation(event.currentTarget.href);
			}
		}
	});
}

function RefreshTable()
{
	$('#posTable tbody tr').remove();

    for (var i=0; i< fnData.length; i++)
	{
		AddFnTable(i, fnData[i]);
    }
	$("#checkVisibleFN").attr('checked', 'checked');
    RefreshOperationButtons();
	JumpImgNumber($("#slider").slider("value"));

}

function DeleteLocationRows()
{
    var checks = $("input[name='rowCheckList[]']:checked");
    var indexes = checks.map(function() {
      return $(this).val();
    });

    for (i = indexes.length-1; i >=0; i--) {
		$("#row" + indexes[i]).remove();
		fnData.splice(indexes[i], 1);
    }
    RefreshTable();
}


function IntegrateLocationRows()
{
    var indexes = $("input[name='rowCheckList[]']:checked").map(function() { return $(this).val(); });

	if(confirm('Do you integrate selected items?'))
	{
		var registTime = $("#registTime").val();
		
		var xTmp = 0;
		var yTmp = 0;
		var zTmp = 0;
		var idStr = "";

		for(var i=0; i<indexes.length; i++)
		{
			// ↓Consensusモードで新たに追加した行を含む統合は行わない
			if(fnData[i].idStr == "")
			{
				alert('[ERROR] The integration including the row that you newly added in consensual mode is impossible.');
				JumpImgNumber($("#imgNum").val());
				return false;
			}
			else
			{
				xTmp += fnData[indexes[i]].x;
				yTmp += fnData[indexes[i]].y;
				zTmp += fnData[indexes[i]].z;
				if(i>0)  idStr += ',';
				idStr += fnData[indexes[i]].idStr;
			}
		}

		alert(xTmp +' ' + yTmp + ' ' +zTmp);

		var xPos = parseInt(parseFloat(xTmp) / parseFloat(indexes.length) + 0.5);
		var yPos = parseInt(parseFloat(yTmp) / parseFloat(indexes.length) + 0.5);
		var zPos = parseInt(parseFloat(zTmp) / parseFloat(indexes.length) + 0.5);

		var xOffset = parseInt($("#imgArea").position().left);
		var yOffset = parseInt($("#imgArea").position().top);

		//----------------------------------------------------------------------
		// For mobile safari (iPad/iPhone iOS:3.2 or 4.0.x)
		//----------------------------------------------------------------------
		var iOSPattern = /; CPU\sOS\s(?:3_2|4_0)/i;

		if(iOSPattern.test(navigator.userAgent) )
		{
			xOffset -= window.scrollX;
			yOffset -= window.scrollY;
		}
		//----------------------------------------------------------------------

		var xPos2 = parseInt(xPos * parseFloat($("#dispWidth").val())
                             / parseFloat($("#orgWidth").val())   + 0.5);
		var yPos2 = parseInt(yPos * parseFloat($("#dispHeight").val())
                             / parseFloat($("#orgHeight").val()) + 0.5);
    
		for (i = indexes.length-1; i >=0; i--) {
			$("#row" + indexes[i]).remove();
			fnData.splice(indexes[i], 1);
    	}

		var item = {
			"x": xPos,
      		"y": yPos,
     	 	"z": zPos,
      		"rank": CheckNearestHiddenTP(xPos, yPos, zPos),
      		"enteredBy": $("#userID").val(),
			"idStr": idStr,
			"colorSet": 0
		};

		//plotDots(fnData.length+1, xPos2+xOffset, yPos2+yOffset, 0);
		AddFnTable(fnData.length, item);
		fnData.push(item);
	    RefreshTable();
	}
}


function CheckNearestHiddenTP(posX, posY, posZ)
{
	var distTh = $("#distTh").val();
	distTh = distTh * distTh;

	var distMin = 10000;
	var ret = '- / -';

	for(var i=0; i<candPos.length; i++)
	{
		var candX = candPos[i].x - posX;
		var candY = candPos[i].y - posY;
		var candZ = candPos[i].z - posZ;

		var dist = candX * candX + candY * candY + candZ * candZ;

		if(dist < distMin)
		{
			distMin = dist;
			if(distMin < distTh) ret = candPos[i].id + ' / ' + Math.sqrt(distMin).toFixed(2);
		}
	}
	return ret;
}

//--------------------------------------------------------------------------------------------------

var rowClickHandler = function(event) {
	// targetが「操作」列の内部にある要素の場合は処理しない。
	// それ以外の行のどこかをクリックした場合のみ反応する。

	if ($(event.target).parents('td.operationColumn').length == 0)
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



$(document).ready(function(){

	$('#posTable tbody tr').live('click', rowClickHandler);
	
	$("img#imgArea.enter").click(function(e){

		//------------------------------------------------------------------------------------------
		// Retrieve clicked position and plot dot
		//------------------------------------------------------------------------------------------
		var x = e.pageX - $("#imgBlock").offset().left;
		var y = e.pageY - $("#imgBlock").offset().top;

		//----------------------------------------------------------------------
		// For mobile safari (iPad/iPhone iOS:3.2 or 4.0.x)
		//----------------------------------------------------------------------
		var iOSPattern = /; CPU\sOS\s(?:3_2|4_0)/i;

		if(iOSPattern.test(navigator.userAgent) )
		{
			x += window.scrollX;
			y += window.scrollY;
		}
		//----------------------------------------------------------------------

		var xPos = parseInt(x*parseFloat($("#orgWidth").val())/parseFloat($("#dispWidth").val())+0.5);
		var yPos = parseInt(y*parseFloat($("#orgHeight").val())/parseFloat($("#dispHeight").val())+0.5);

		//------------------------------------------------------------------------------------------

		var imgNum = $("#slider").slider("value");

		var item = {
			"x": xPos,
      		"y": yPos,
     	 	"z": imgNum,
      		"rank": CheckNearestHiddenTP(xPos, yPos, imgNum),
      		"enteredBy": $("#userID").val(),
			"idStr": "",
			"colorSet": 0
		};

		plotDots(fnData.length+1, x, y, 0);
		AddFnTable(fnData.length, item);
		fnData.push(item);
		
		if(!$("#checkVisibleFN").is(':checked'))
		{
			$("#checkVisibleFN").attr('checked', 'checked');
			JumpImgNumber(imgNum);
		}

		if($("#status").val()==0)			$("#resetBtn").show();
		else if($("#status").val()==1)		$("#undoBtn").show();

		// 候補分類入力中にメニューバーを押された場合の対策
		$("#linkAbout, #menu a, .tabArea a[title!='FN input']").click(
			function(event){ 

			if(!event.isDefaultPrevented())
			{
				event.preventDefault();  // prevent link action
					
				if(confirm("Do you want to save the changes?"))
				{
					ConfirmFNLocation(event.currentTarget.href);
				}
			}
		});

	});
});

