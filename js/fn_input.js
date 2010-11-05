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
					var tbody= document.getElementById("posTable").tBodies.item(0);
					var rowNum = tbody.rows["length"];

					var colOffset = ($("#registTime").val() == "" || $("#status").val() != 2) ? 1 : 0;
					var posX, posY, posZ;

					for(var j=0; j<rowNum; j++)
					{
						posID = tbody.rows[j].cells[colOffset].innerHTML;
						posX  = tbody.rows[j].cells[colOffset+1].innerHTML;
						posY  = tbody.rows[j].cells[colOffset+2].innerHTML;
						posZ  = tbody.rows[j].cells[colOffset+3].innerHTML;

						var colorSet = ($("#feedbackMode").val()=="consensual") ? parseInt(tbody.rows[j].cells[colOffset+7].innerHTML) : 0;
	
						if(posZ == data.imgNum)
						{
							plotClickedLocation(posID, posX, posY, colorSet);
						}
					}
				}
			}
		}, "json");
}

function plotClickedLocation(id, x, y, colorSet)
{
	var xPos = parseInt(x * parseFloat($("#dispWidth").val())  / parseFloat($("#orgWidth").val())  + 0.5);
	var yPos = parseInt(y * parseFloat($("#dispHeight").val()) / parseFloat($("#orgHeight").val()) + 0.5);
	
	 plotDots(id, xPos, yPos, colorSet);
	 
}

function ClickPosTable(id, imgNum)
{
	$('#checkVisibleFN').attr('checked', 'checked');
	JumpImgNumber(imgNum);
}

function JumpImgNumBySliceLocation(origin, pitch, offset, fNum)
{
	var sliceLoc = $("#sliceLocation").val();
	
	var imgNum = parseInt(((sliceLoc - origin) / pitch) + offset + 1.5);
	
	if(imgNum < (offset + 1))   imgNum = offset + 1;
	else if(imgNum > fNum)  	imgNum = fNum;
	
	JumpImgNumber(imgNum);
}

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
		case 1: 
			labelBaseX = 3;
			labelBaseY = -20;
			color = "#228b22";
			break;
	
		case 2:
			labelBaseX = (id < 10) ? -11 : -20;
			labelBaseY = -20;
			color = "#ff8000";
			break;
	
		case 3:
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
	if(confirm('Do you remove all FN locations?'))
	{
		ClickRowCheckList();
		JumpImgNumber($("#slider").slider("value"));
		$("#checkVisibleFN").attr('checked', 'checked');
		$("#posTable tbody").html("");
	}
}

function UndoFnTable()
{
	if(confirm('Undo FN locations?'))
	{
		var posArr = $("#posStr").val().split('^');
		var enteredFnNum = posArr.length / 6;

		$("#posTable tbody").html("");

		for(i=0; i<enteredFnNum; i++)
		{
			AddPosTable(i+1, posArr[i*6], posArr[i*6+1], posArr[i*6+2], 
                        posArr[i*6+4], posArr[i*6+5]);
		}
		ClickRowCheckList();
		JumpImgNumber($("#slider").slider("value"));
		$("#checkVisibleFN").attr('checked', 'checked');
		$("#undoBtn").hide();
	}
}

function ConfirmFNLocation(address)
{
   	var rowNum= document.getElementById('posTable').tBodies.item(0).rows["length"];
	var posStr = CreatePosStr();
	var that = this;

	if(confirm('Do you save entered FN locations?'))
	{
		$.post("fn_registration.php",
			{ execID: $("#execID").val(),
			  posStr: posStr,
			  rowNum: rowNum,
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

function ClickRowCheckList()
{
	var checkCnt = $("input[name='rowCheckList[]']:checked").length;
	var htmlStr = '<option value="">(action)</option>';

	if(checkCnt > 0)
	{
		$("#confirmBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
		
		htmlStr += '<option value="delete">delete</option>';

		if (checkCnt>=2 && $("#feedbackMode").val()=="consensual")
		{
			htmlStr += '<option value="integrate">integrate</option>'
		}

		$("#actionMenu").removeAttr("disabled").html(htmlStr);
	}
	else
	{
		$("#confirmBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		$("#actionMenu").attr("disabled", "disabled").html(htmlStr);
		$("#actionBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
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

function AddPosTable(rowNum, xPos, yPos, zPos, enteredBy, idStr)
{
	var feedbackMode = $("#feedbackMode").val();
	var htmlStr = '<tr id="row' + rowNum + '"';
	htmlStr += (rowNum%2==0) ? ' class="column">' : '>';

	if($("#registTime").val() == "" || $("#status").val() != 2)
	{
		htmlStr += '<td><input type="checkbox" name="rowCheckList[]"'
                +  ' onclick="ClickRowCheckList();"value="' + rowNum + '"></td>';
	}

	var tdBaseStr = ' onclick="ClickPosTable(\'row' + rowNum + '\',' + zPos + ');" style="color:';
	tdBaseStr += (feedbackMode == "consensual") ? '#ff00ff;">' : 'black;">';

	htmlStr += '<td class="al-r"' + tdBaseStr + rowNum + '</td>'
            +  '<td class="al-r"' + tdBaseStr + xPos + '</td>'
            +  '<td class="al-r"' + tdBaseStr + yPos + '</td>'
            +  '<td class="al-r"' + tdBaseStr + zPos + '</td>'
            +  '<td' + tdBaseStr + CheckNearestHiddenTP(xPos, yPos, zPos) + '</td>';

	if(feedbackMode == "consensual")
	{
		htmlStr += '<td' + tdBaseStr + enteredBy + '</td>'
				+  '<td style="display:none;">' + idStr + '</td>';
				+  '<td style="display:none;">0</td>';
	}

	$("#posTable tbody").append(htmlStr);
	$('.form-btn').hoverStyle({normal: 'form-btn-normal', hover: 'form-btn-hover',disabled: 'form-btn-disabled'});
}

function ChangeAction()
{
	var actionVal = $("#actionMenu").val();

	if(actionVal != "")
	{
		$("#actionBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	}
	else
	{
		$("#actionBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
	}
}

function TableOperation()
{
	var actionVal = $("#actionMenu").val();
	var checkCnt = $("input[name='rowCheckList[]']:checked").length;
	
	switch(actionVal)
	{
		case "delete":
			var str = 'Do you delete selected location' + ((checkCnt > 1) ? 's?' : '?');
			if(confirm(str))	DeleteLocationRows();
			else				$("input[name='rowCheckList[]']").removeAttr("checked");
		    break;
		
		case "integrate":
			IntegrateLocationRows();
			break;
	}
	ClickRowCheckList();

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

function DeleteLocationRows()
{
	$("input[name='rowCheckList[]']:checked").each(function() {
		$("#row" + $(this).val()).remove();
	});

	var posArr = CreatePosStr().split('^');
	var enteredFnNum = (posArr.length-1) / 6;

	$("#posTable tbody").html("");

	for(i=0; i<enteredFnNum; i++)
	{
		AddPosTable(i+1, posArr[i*6], posArr[i*6+1], posArr[i*6+2], 
                   posArr[i*6+4], posArr[i*6+5]);
	}

	ClickRowCheckList();
	JumpImgNumber($("#slider").slider("value"));
	$("#checkVisibleFN").attr('checked', 'checked');
}

function IntegrateLocationRows()
{
	var checkCnt = $("input[name='rowCheckList[]']:checked").length;

	// チェックされたcheckboxの値を取得
	var checkedIdArr = $("input[name='rowCheckList[]']:checked").map(function() { return $(this).val(); })
                                                                .get().join(",").split(',');

	if(checkCnt < 2)
	{
	   alert('Please select at least two locations.');
	}
	else
	{
		var str = 'Do you integrate selected locations?';
		
		if(confirm(str))
		{
			var registTime = $("#registTime").val();

			var tbody = document.getElementById("posTable").tBodies.item(0);
		
			var xTmp = 0;
			var yTmp = 0;
			var zTmp = 0;
			var idStr = "";
			
			var orgWidth   = $("#orgWidth").val();
			var orgHeight  = $("#orgHeight").val();
			var dispWidth  = $("#dispWidth").val();
			var dispHeight = $("#dispHeight").val();

			for(var j=0; j<checkCnt; j++)
			{
				// ↓Consensusモードで新たに追加した行を含む統合は行わない
				if(tbody.rows[checkedIdArr[j]-1].cells[7].innerHTML == "")
				{
					alert('Error: The integration including the row that you newly added in consensual mode is impossible.');
					JumpImgNumber($("#imgNum").val());
					return false;
				}
				else
				{
					xTmp += parseInt(tbody.rows[checkedIdArr[j]-1].cells[2].innerHTML);
					yTmp += parseInt(tbody.rows[checkedIdArr[j]-1].cells[3].innerHTML);
					zTmp += parseInt(tbody.rows[checkedIdArr[j]-1].cells[4].innerHTML);
					if(j>=1)  idStr += ',';
					idStr += tbody.rows[checkedIdArr[j]-1].cells[7].innerHTML;
				}
			}

			var xPos = parseInt(parseFloat(xTmp) / parseFloat(checkCnt) + 0.5);
			var yPos = parseInt(parseFloat(yTmp) / parseFloat(checkCnt) + 0.5);
			var zPos = parseInt(parseFloat(zTmp) / parseFloat(checkCnt) + 0.5);

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

			var xPos2 = parseInt(xPos * parseFloat(dispWidth) / parseFloat(orgWidth)   + 0.5);
			var yPos2 = parseInt(yPos * parseFloat(dispHeight) / parseFloat(orgHeight) + 0.5);


			DeleteLocationRows();
			var rowNum = tbody.rows["length"];	
	
			plotDots(rowNum+1, xPos2+xOffset, yPos2+yOffset, 0);

			AddPosTable(rowNum+1, xPos, yPos, zPos, $("#userID").val(), idStr);

			$("#checkVisibleFN").attr('checked', 'checked');
			JumpImgNumber(zPos);
		}

	}
}


function CheckNearestHiddenTP(posX, posY, posZ)
{
	var distTh = $("#distTh").val();
	distTh = distTh * distTh;

	var candPos = $("#candPosStr").val().split('^');
	var candNum = candPos.length/4;
	
	var distMin = 10000;
	var minId = 0;
	var ret = '- / -';
		
	for(var i=0; i<candNum; i++)
	{
		var candX = candPos[i * 4 + 1] - posX;
		var candY = candPos[i * 4 + 2] - posY;
		var candZ = candPos[i * 4 + 3] - posZ;
				
		var dist = candX * candX + candY * candY + candZ * candZ;

		if(dist < distMin)
		{
			distMin = dist;
			if(distMin < distTh) ret = candPos[i*4] + ' / ' + Math.sqrt(distMin).toFixed(2);
		}
	}
	return ret;
}

function CreatePosStr()
{
	var tbody= document.getElementById("posTable").tBodies.item(0);
	var rowNum = tbody.rows["length"];
	var startCol = 2;
	var endCol   = 5;
	
	if($("#registTime").val() != "" && $("#status").val() == 2)	// Modify用
	{
		startCol = 1;
		endCol   = 4;
	}

	var posStr = "";
	
	for(var j=0; j<rowNum; j++)
	{
		for(var i=startCol; i<=endCol; i++)
		{
			posStr += (tbody.rows[j].cells[i].innerHTML + '^');
		}
		
		if($("#feedbackMode").val() == "consensual")
		{
			posStr += (tbody.rows[j].cells[endCol+1].innerHTML.replace(' ','') + '^');
			posStr += (tbody.rows[j].cells[endCol+2].innerHTML.replace(' ','') + '^');
		}
		else
		{
			 posStr += ($("#userID").val() + '^^');
		}
	}
	return posStr;
}

//--------------------------------------------------------------------------------------------------

$(document).ready(function(){
	
	$("img#imgArea.enter").click(function(e){

		var table = document.getElementById('posTable');
		var tbody = table.tBodies.item(0);

		// Number of rows
    	var rowNum=tbody.rows["length"];

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

		plotDots(rowNum+1, x, y, 0);
		//------------------------------------------------------------------------------------------

		var feedbackMode = $("#feedbackMode").val();
		var imgNum       = $("#slider").slider("value");

		AddPosTable(rowNum+1, xPos, yPos, imgNum, $("#userID").val(), '');
	
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





