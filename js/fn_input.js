function CreatePosStr()
{
	var tObj= document.getElementById("posTable");
	var registTime = $("#registTime").val();
	
	rowNum = tObj.rows["length"]-1;
	var colNum = tObj.rows[0].cells["length"];
	var mode = $("#feedbackMode").val();
	var posStr = "";
	
	var startCol = 2;
	var endCol   = 4;
	
	if(registTime != "")
	{
		startCol = 1;
		endCol   = 3;
	}
	
	for(var j=1; j<=rowNum; j++)
	{
		for(var i=startCol; i<=endCol; i++)
		{
			posStr += (tObj.rows[j].cells[i].innerHTML + '^');
		}
		
		var tmpStr = tObj.rows[j].cells[endCol+1].innerHTML.substr(0,6);
		
		//if(tmpStr == 'check')  posStr += 'BT^';
		if(tmpStr == 'check')  posStr += CheckNearestHiddenTP(j) + '^';
		else                   posStr += (tObj.rows[j].cells[endCol+1].innerHTML + '^');
	
		if(mode == "consensual")
		{
			posStr += (tObj.rows[j].cells[endCol+2].innerHTML.replace(' ','') + '^');
			posStr += (tObj.rows[j].cells[endCol+3].innerHTML.replace(' ','') + '^');
		}
		else
		{
			 posStr += ($("#userID").val() + '^^');
		}
	}

	return posStr;
}

function JumpImgNumber(imgNum)
{
	var posStr = CreatePosStr();

	var tObj= document.getElementById("posTable");
	var rowNum = tObj.rows["length"]-1;

	var address = 'fn_input.php'
                + '?execID=' + $("#execID").val()
                + '&cadName=' + $("#cadName").val()
                + '&version=' + $("#version").val()
                + '&studyInstanceUID=' + $("#studyInstanceUID").val()
                + '&seriesInstanceUID=' + $("#seriesInstanceUID").val()
                + '&feedbackMode=' + $("#feedbackMode").val()
				+ '&posStr=' + posStr
                + '&rowNum=' + rowNum
                + '&imgNum=' + imgNum
                + '&interruptFNFlg=' + $("#interruptFNFlg").val()
                + '&registFNFlg=' + $("#registFNFlg").val()
                + '&registTime=' + $("#registTime").val()
                + '&visibleFlg=' + $("#visibleFlg").val()
				+ '&grayscaleStr=' + $("#grayscaleStr").val()
				+ '&presetName=' + $("#presetName").val()
				+ '&windowLevel=' + $("#windowLevel").val()
				+ '&windowWidth=' + $("#windowWidth").val()
                + '&ticket=' + $("#ticket").val();

	if($("#feedbackMode").val() == "consensual")
	{
		address += '&userStr=' + $("#userStr").val();
	}

	location.replace(address);
}

function JumpImgNumBySliceLocation(origin, pitch, offset, fNum)
{
	var sliceLoc = $("#sliceLoc").val();
	
	var imgNum = parseInt(((sliceLoc - origin) / pitch) + offset + 1.5);
	
	if(imgNum < (offset + 1))    imgNum = offset + 1;
	else if(imgNum > fNum)  imgNum = fNum;
	
	JumpImgNumber(imgNum);
}

function ClickPositionTable(id, imgNum)
{
	//ChangeBgColor(id);

	if(!$('#checkVisibleFN').attr('checked'))
	{
		$('#checkVisibleFN').attr('checked', true);
		$("#visibleFlg").val(1);
	}

	JumpImgNumber(imgNum);
}


function plotDots(id, x, y, set)
{
	var dotOffsetX = -1;
	var dotOffsetY = -1;
	var labelOffsetX = 0;
	var labelOffsetY = 0;

	var labelBaseX = 0;
	var labelBaseY = 0;
	var color = "#ff00ff";

	switch(set)
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

	var htmlStr = '<span id="dot' + id + '" class="dot" style="top:' + (y+dotOffsetY) + 'px; left:' + (x+dotOffsetX) + 'px; '
        	    + 'height:3px; width:3px; padding:0px; background-color:' + color + ';"></span>'
				+ '<span id="label' + id + '" class="dot" style="top:' + (y+labelBaseY+labelOffsetY) + 'px;'
				+ ' left:' + (x+labelBaseX+labelOffsetX) + 'px; color:' + color + ';'
				+ ' filter:dropshadow(color=#000000 offX=1 offY=0) dropshadow(color=#000000 offX=-1 offY=0)'
				+ ' dropshadow(color=#000000 offX=0 offY=1) dropshadow(color=#000000 offX=0 offY=-1);'
				+ ' font-weight:bold;">' + id + '</span>';

	$("#imgBlock").append(htmlStr);
}


function plotClickedLocation(id, x, y, set)
{
	// parseIntは応急処置
	var xOffset = parseInt($("#imgArea").position().left);
	var yOffset = parseInt($("#imgArea").position().top);

	var xPos = parseInt(x * parseFloat($("#dispWidth").val())  / parseFloat($("#orgWidth").val())  + 0.5);
	var yPos = parseInt(y * parseFloat($("#dispHeight").val()) / parseFloat($("#orgHeight").val()) + 0.5);
	
	 plotDots(id, xPos+xOffset, yPos+yOffset, set);
	 
}


function DeleteLocationRows()
{
	var checkObj = document.form1.elements['rowCheckList[]'];
	if(checkObj == null)		return;	

	//----------------------------------------------------------------------------------------------
	// チェックボックスが最低1個でも選択されていることを確認する
	//----------------------------------------------------------------------------------------------
	var flg = false;
	var checkCnt = 0;
	var checkedId = new Array();

	if(checkObj.length == null)
	{
		if(checkObj.checked)
		{
			 flg = true;
			 checkedId[checkCnt] = checkObj.value;
			 checkCnt++;
		}
	}
	else
	{
		for(var i=0; i<checkObj.length; i++)
		{
			if(checkObj[i].checked)
			{
				flg = true;
			 checkedId[checkCnt] = checkObj[i].value;
			 checkCnt++;
			}
		}
	}
	//----------------------------------------------------------------------------------------------

	if(!flg)
	{
	   alert('Please select at least one location!');
	}
	else
	{
		var str = 'Do you delete selected location';
		if(checkCnt > 1)  str += 's?';
		else		 	  str += '?';
	
		if(confirm(str))
		{
			var imgNum   = $('#imgNum').val();
			var mode     = $("#feedbackMode").val();

			for(var j=0; j<checkCnt; j++)
			{
				$("#row" + checkedId[j]).remove();
			}
			$('#interruptFNFlg').val(1);

			if($('#checkVisibleFN').attr('checked') == false)
			{
				$('#checkVisibleFN').attr('checked', true);
				$('#visibleFlg').val(1);
			}
			JumpImgNumber(imgNum);
		}
		else
		{
			for(var i=0; i<checkObj.length; i++)
			{
				checkObj[i].checked = false;
			}
		}
	}
}



function IntegrateLocationRows()
{
	var checkObj = document.form1.elements['rowCheckList[]'];
	if(checkObj == null)		return;	

	//----------------------------------------------------------------------------------------------
	// チェックボックスにチェックが入っている個数をカウント
	//----------------------------------------------------------------------------------------------
	var checkCnt = 0;
	var checkedId = new Array();

	if(checkObj.length == null)
	{
		if(checkObj.checked)
		{
			 checkedId[checkCnt] = checkObj.value;
			 checkCnt++;
		}
	}
	else
	{
		for(var i=0; i<checkObj.length; i++)
		{
			if(checkObj[i].checked)
			{
				 checkedId[checkCnt] = checkObj[i].value;
				 checkCnt++;
			}
		}
	}
	//----------------------------------------------------------------------------------------------
	
	if(checkCnt < 2)
	{
	   alert('Please select at least two locations!');
	}
	else
	{
		var str = 'Do you integrate selected locations?';
		
		if(confirm(str))
		{
			var registTime = $("#registTime").val();

			if(registTime == "")
			{
				$("#interruptFNFlg").val(1);
			}

			var tObj = document.getElementById("posTable")
		
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
				if(tObj.rows[checkedId[j]].cells[7].innerHTML == "")
				{
					alert('Error: The integration including the row that you newly added in consensual mode is impossible.');
					JumpImgNumber($("#imgNum").val());
					return false;
				}
				else
				{
					xTmp += parseInt(tObj.rows[checkedId[j]].cells[2].innerHTML);
					yTmp += parseInt(tObj.rows[checkedId[j]].cells[3].innerHTML);
					zTmp += parseInt(tObj.rows[checkedId[j]].cells[4].innerHTML);
					if(j>=1)  idStr += ',';
					idStr += tObj.rows[checkedId[j]].cells[7].innerHTML;
				}
			}

			var xPos = parseInt(parseFloat(xTmp) / parseFloat(checkCnt) + 0.5);
			var yPos = parseInt(parseFloat(yTmp) / parseFloat(checkCnt) + 0.5);
			var zPos = parseInt(parseFloat(zTmp) / parseFloat(checkCnt) + 0.5);

			for(var j=0; j<checkCnt; j++)
			{
				$("#row" + checkedId[j]).remove();
			}

			//alert(xPos + ', ' + yPos + ', ' + zPos + ', ' + idStr);			
			
			// parseIntは応急処置
			var xOffset = parseInt($("#imgArea").position().left);
			var yOffset = parseInt($("#imgArea").position().top);
        
			var xPos2 = parseInt(xPos * parseFloat(dispWidth) / parseFloat(orgWidth)   + 0.5);
			var yPos2 = parseInt(yPos * parseFloat(dispHeight) / parseFloat(orgHeight) + 0.5);

			var rowNum   = tObj.rows["length"];	
	
			plotDots(rowNum, xPos2+xOffset, yPos2+yOffset, 0);

			var candStr = $("#candStr").val();
			var mode    = $("#feedbackMode").val();
    
			var insObj=tObj.insertRow(rowNum);
			insObj.id = "row" + rowNum;
			insObj.onclick = "ClickPositionTable('" + insObj.id + "'," + zPos + ");";

			var colCheck        = insObj.insertCell(0);
			var colId           = insObj.insertCell(1);
			var colX            = insObj.insertCell(2);
			var colY            = insObj.insertCell(3);
			var colZ            = insObj.insertCell(4);
			var colNearest      = insObj.insertCell(5);
			var colEnteredBy    = insObj.insertCell(6);
			var colIntegratedId = insObj.insertCell(7);
	
			// 挿入行の文字色を決定(consensual feedback)
			if(mode == "consensual")
			{
				insObj.style.color ='#ff00ff';
			}

			colCheck.align="center";
			colCheck.innerHTML = '<input type="checkbox" name="rowCheckList[]" value="' + rowNum + '">';
	
    		colId.align = "right";
			colX.align = colY.align = colZ.align = colId.align;
			colNearest.align = colEnteredBy.align = "center";	
	
			colId.innerHTML = rowNum;
			colX.innerHTML  = xPos;
			colY.innerHTML  = yPos;
			colZ.innerHTML  = zPos;
	
			colNearest.style.color ='#ffffff';
			colNearest.innerHTML += 'check';

			colEnteredBy.innerHTML = $("#userID").val();
		
			colIntegratedId.innerHTML = idStr;
			insObj.style.display ='none';
			
			if($("#checkVisibleFN").attr('checkVisibleFN') == false)
			{
				$("#checkVisibleFN").attr('checked') = true;
				$("#visibleFlg").val(1);
			}
			JumpImgNumber(zPos);
		}

	}
}


function CheckNearestHiddenTP(rowNum)
{

	var tObj=document.getElementById("posTable");
	
	var posX = tObj.rows[rowNum].cells[2].innerHTML;
	var posY = tObj.rows[rowNum].cells[3].innerHTML;
	var posZ = tObj.rows[rowNum].cells[4].innerHTML;

	var distTh = $("#distTh").val();
	distTh = distTh * distTh;

	var candPos = $("#candStr").val().split('^');
	var candNum = candPos.length/4;
	
	var distMin = 10000;
	var minId = 0;
	var ret = "";
		
	for(var i=0; i<candNum; i++)
	{
		var candX = candPos[ i * 4 + 1 ];
		var candY = candPos[ i * 4 + 2 ];
		var candZ = candPos[ i * 4 + 3 ];
				
		var dist = (candX-posX)*(candX-posX)+(candY-posY)*(candY-posY)+(candZ-posZ)*(candZ-posZ);
		
		if(dist < distMin)
		{
			distMin = dist;
			minId = i * 4;
		}
	}

	if(distMin < distTh)  ret = candPos[minId] + ' / ' + Math.sqrt(distMin).toFixed(2);
	else  				  ret = '- / -';

	return ret;
}


function ConfirmFNLocation()
{
	if(confirm('Do you define all FN(s)?'))
	{
		$("#registFNFlg").val(1);
		$("#interruptFNFlg").val(0);
		
		JumpImgNumber($("#imgNum").val());
	}
}

function ChangeVisibleFN()
{
	$("#visibleFlg").val($("#checkVisibleFN").is(':checked') ? 1 : 0);

	JumpImgNumber($("#imgNum").val());
}

function ChangePresetMenu(imgNum)
{
	var tmpStr = $("#presetMenu option:selected").val().split("^");
	
	$("#presetName").val($("#presetMenu option:selected").text());
	$("#windowLevel").val(tmpStr[0]);
	$("#windowWidth").val(tmpStr[1]);
	
	JumpImgNumber(imgNum);
}

$(document).ready(function(){
	
	$("img#imgArea.enter").click(function(e){

		var tObj    = document.getElementById("posTable");
		var candStr = $("#candStr").val();
		var mode    = $("#feedbackMode").val();
		var imgNum  = $("#imgNum").val();

		// Number of rows
    	var rowNum=tObj.rows["length"];

		plotDots(rowNum, e.pageX, e.pageY, 0);

		var x = e.pageX - $("img#imgArea").position().left;
		var y = e.pageY - $("img#imgArea").position().top;

		var xPos = parseInt(x*parseFloat($("#orgWidth").val())/parseFloat($("#dispWidth").val())+0.5);
		var yPos = parseInt(y*parseFloat($("#orgHeight").val())/parseFloat($("#dispHeight").val())+0.5);
	
		var insObj=tObj.insertRow(rowNum);
		insObj.id = "row" + rowNum;
		insObj.onclick = "ClickPositionTable('" + insObj.id + "'," + imgNum + ");";

		var colCheck   = insObj.insertCell(0);
		var colId      = insObj.insertCell(1);
		var colX       = insObj.insertCell(2);
		var colY       = insObj.insertCell(3);
		var colZ       = insObj.insertCell(4);
		var colNearest = insObj.insertCell(5);
	
		// 挿入行の文字色を決定(consensual feedback)
		if(mode == "consensual")
		{
			insObj.style.color ='#ff00ff';
		}

		colCheck.align="center";
		colCheck.innerHTML = '<input type="checkbox" name="rowCheckList[]" value="' + rowNum + '">';
	
    	colId.align = "right";
		colX.align = colY.align = colZ.align = colId.align;
		colNearest.align = "center";	
	
		colId.innerHTML = rowNum;
		colX.innerHTML  = xPos;
		colY.innerHTML  = yPos;
		colZ.innerHTML  = imgNum;
	
		colNearest.style.color ='#ffffff';
		colNearest.innerHTML = 'check';

		if(mode == "consensual")
		{
			var colEnteredBy =insObj.insertCell(6);
		    colEnteredBy.align="center";
			colEnteredBy.innerHTML = $("#userID").val();
		
			var colIntegratedId = insObj.insertCell(7);
			colIntegratedId.style.display ='none';
		}
	
		if(rowNum == 1)
		{
			$("#blockDeleteButton").html('<input type="button" id="delButton" value="delete" onclick="DeleteLocationRows();">');

			if(mode == "consensual")
			{
				$("#blockDeleteButton").append('&nbsp;&nbsp;<input type="button" id="integrationButton" value="integration">');
			}
		}	
	
		if($("#checkVisibleFN").attr('checkVisibleFN') == false)
		{
			$("#checkVisibleFN").attr('checked') = true;
			$("#visibleFlg").val(1);
		}
		JumpImgNumber(imgNum);
	});
});





