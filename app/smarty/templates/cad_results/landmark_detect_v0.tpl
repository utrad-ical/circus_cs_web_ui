<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

<title>CIRCUS CS {$smarty.session.circusVersion}</title>

<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/ui/jquery-ui-1.7.3.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jquery.mousewheel.min.js"></script>
<script language="javascript" type="text/javascript" src="../js/circus-common.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<script language="javascript" type="text/javascript" src="../js/edit_tag.js"></script>

<link rel="shortcut icon" href="favicon.ico" />
<link href="../jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/darkroom.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/radio-to-button.js"></script>

{literal}
<style type="text/css">
div.imgArea {
  background-color:#888888;
  border-width:2px; 
  border-style:solid;
  overflow:hidden;
}

#resultBody {
  width: 100%;
  float:left;
  margin-top:5px;
}

#resultBody .imgDisp {
  float:left;
  width:560px;
  display:inline;
}


#resultBody .imgDisp td {
  padding: 1px;
}

#resultBody .rightColumn  {
  float:right;
  width:380px;
  margin:0px 0px 0px 0px;
  padding: 0px;    
}

#resultBody .posTable {
/*  overflow:auto; */
  overflow-y:scroll;  
  overflow-x:hidden;
  width: 350px;
  height: 400px;
}

#resultBody .scrollTable {
  font-family: verdana,"Hiragino Kaku Gothic Pro",Osaka,"MS PGothic",Sans-Serif;
  line-height: 1.5;
  border-collapse: collapse;
}

.scrollTable th, .scrollTable tr, .scrollTable td {
	border: 1px solid #9eb6ce;
    /*padding: 3px 6px 3px 4px;*/
}

.scrollTable th {
	background-color: #CFDCEE;
}

.landmarkName {
 text-align:left;
}

.landmarkID, .landmarkXpos, .landmarkYpos, .landmarkZpos {
  text-align:right;
}

.scrollTable-Fixed {
	background-color: #e4ecf7;
}

/*
.scrollTable tbody {
  overflow:auto;  
  overflow-x:hidden; 
  height: 400px;
}  
*/

.editPos, .delPos, .savePos{
  margin: 0px 1px 0px 1px;
  padding:0px;
}
{/literal}
</style>
</head>

<body class="lesion_cad_display">
<div id="page">
	<div id="container" class="menu-back">
		<!-- ***** #leftside ***** -->
		<div id="leftside">
			{include file='menu.tpl'}
		</div>
		<!-- / #leftside END -->

		<div id="content">
			<!-- ***** TAB ***** -->
			<div class="tabArea">
				<ul>
					{if $params.srcList!="" && $smarty.session.listAddress!=""}
						<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$params.listTabTitle}">{$params.listTabTitle}</a></li>
					{else}
						<li><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
					{/if}
					<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD result</a></li>
				</ul>
				<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
			</div><!-- / .tabArea END -->

			<div class="tab-content">
				{*<div id="cadResult">*}
				<div id="resultBody" class="resultBody">

					<h2>CAD Result&nbsp;&nbsp;[{$params.cadName} v.{$params.version} ID:{$params.jobID}]</h2>
				
					<div class="headerArea">
						<div class="fl-l"><a href="../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID}">{$params.patientName}&nbsp;({$params.patientID})&nbsp;{$params.age}{$params.sex}</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" /><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}">{$params.studyDate}&nbsp;({$params.studyID})</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" />{$params.modality},&nbsp;{$params.seriesDescription}&nbsp;({$params.seriesID})</div>
					</div>

					<div class="fl-clr"></div>
						
					<form id="form1" name="form1">
					<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"  value="{$params.studyInstanceUID}" />
					<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID" value="{$params.seriesInstanceUID}" />
					<input type="hidden" id="cadName"           name="cadName"           value="{$params.cadName}" />
					<input type="hidden" id="version"           name="version"           value="{$params.version}" />
					<input type="hidden" id="jobID"             name="jobID"             value="{$params.jobID}" />

					<input type="hidden" id="orgWidth"   value="{$width}" />
					<input type="hidden" id="orgHeight"  value="{$height}" />
					<input type="hidden" id="orgDepth"   value="{$depth}" />
					<input type="hidden" id="dispWidth"  value="{$dispWidth}" />
					<input type="hidden" id="dispHeight" value="{$dispHeight}" />
					<input type="hidden" id="dispDepth"  value="{$dispDepth}" />
					<input type="hidden" id="xPos"       value="{$xPos}" />
					<input type="hidden" id="yPos"       value="{$yPos}" />
					<input type="hidden" id="zPos"       value="{$zPos}" />
					<input type="hidden" id="webPathOfCADReslut" value="../{$webPathOfCADReslut}" />

					{$dstHtml}

					<div class="fl-clr"></div>

					{literal}
					<script language="Javascript">
					<!--

					function fillZero( number, size ) {
						if ( number < 0 ) throw "illegal argument.";
						var s = number != 0 ? Math.log( number ) * Math.LOG10E : 0;
						for( i=1,n=size-s,str="";i<n;i++ ) str += "0";
						return str+number;
					}

					function changeXpos(pos)
					{
						var scale = $("#scaleMenu").val();
						var fileName = $("#webPathOfCADReslut").val() + '/sagittalSection' + $("#presetMenu").val() + '_' + fillZero(pos+1, 4) + '.jpg'
						$("#sagittal,#sagittalEnlarge").attr('src', fileName);
						$("#sagittalEnlarge").attr('width', $("#orgHeight").val()*scale).attr('height', $("#orgDepth").val()*scale);
						$("#axialCross,#coronalCross").css("left", (pos/2)-25);
						$("#axialEnlarge,#coronalEnlarge").css("left",  -pos*scale+50);
						$("#xPos").val(pos);
						$("#xPosDisp").html('X:' + pos);
						$("#currentEditXpos").val(pos);
					}
						
					function changeYpos(pos)
					{
						var scale = $("#scaleMenu").val();
						var fileName = $("#webPathOfCADReslut").val() + '/coronalSection' + $("#presetMenu").val() + '_' + fillZero(pos+1, 4) + '.jpg'
						$("#coronal,#coronalEnlarge").attr('src', fileName);
						$("#coronalEnlarge").attr('width', $("#orgWidth").val()*scale).attr('height', $("#orgDepth").val()*scale);
						$("#axialCross").css("top", (pos/2)-$("#dispHeight").val()-25);
						$("#sagittalCross").css("left", (pos/2)-25);
						$("#axialEnlarge").css("top",  -pos*scale+50);
						$("#sagittalEnlarge").css("left", -pos*scale+50);
						$("#yPos").val(pos);
						$("#yPosDisp").html('Y:' + pos);
						$("#currentEditYpos").val(pos);
					}	

					function changeZpos(pos)
					{
						var scale = $("#scaleMenu").val();
						var fileName = $("#webPathOfCADReslut").val() + '/axialSection' + $("#presetMenu").val() + '_' + fillZero(pos+1, 4) + '.jpg'
						$("#axial,#axialEnlarge").attr('src', fileName);
						$("#axialEnlarge").attr('width', $("#orgWidth").val()*scale).attr('height', $("#orgHeight").val()*scale);
						$("#coronalCross,#sagittalCross").css("top",  (pos/2)-$("#dispDepth").val()-25);
						$("#coronalEnlarge,#sagittalEnlarge").css("top",  -pos*scale+50);
						$("#zPos").val(pos);
						$("#zPosDisp").html('Z:' + pos);
						$("#currentEditZpos").val(pos);
					}


					function changeAllPos(id, x, y, z)
					{
						if($("#editID").val() == 0)
						{
							changeXpos(parseInt($("#sagittalSlider").slider("value", x)));
							changeYpos(parseInt($("#coronalSlider").slider("value", y)));
							changeZpos(parseInt($("#axialSlider").slider("value", z)));
							$(".scrollTable tr").css("background-color", "white");
							$("#row" + id).css("background-color", "#e4ecf7");
						}
					}
					{/literal}

					$(function() {ldelim}
						$("#axialSlider").slider({ldelim}
							value: {$zPos},
							min: 0,
							max: {$depth-1},
							step: 1,
							slide: function(event, ui)  {ldelim} changeZpos(ui.value); {rdelim},
							change: function(event, ui) {ldelim} changeZpos(ui.value); {rdelim}
						{rdelim});
						$("#axialSlider").css("width", "200px");

						$("#coronalSlider").slider({ldelim}
							value: {$yPos},
							min: 0,
							max: {$height-1},
							step: 1,
							slide: function(event, ui)  {ldelim} changeYpos(ui.value); {rdelim},
							change: function(event, ui) {ldelim} changeYpos(ui.value); {rdelim}
						{rdelim});
						$("#coronalSlider").css("width", "200px");

						$("#sagittalSlider").slider({ldelim}
							value: {$xPos},
							min: 0,
							max: {$width-1},
							step: 1,
							slide: function(event, ui)  {ldelim} changeXpos(ui.value); {rdelim},
							change: function(event, ui) {ldelim} changeXpos(ui.value); {rdelim}
						{rdelim});
						$("#sagittalSlider").css("width", "200px");

						{literal}
						$("#xPosMinusButton").click(function() {
							var tmp = $("#sagittalSlider").slider("value");
							if(tmp > $("#sagittalSlider").slider("option", "min"))
							{
								$("#sagittalSlider").slider("value", tmp-1);
								changeXpos(tmp-1); 
							}
						});

						$("#xPosPlusButton").click(function() { 
							var tmp = $("#sagittalSlider").slider("value");
							if(tmp < $("#sagittalSlider").slider("option", "max")) 
							{
								$("#sagittalSlider").slider("value", tmp+1);
								changeXpos(tmp+1); 
							}
						});

						$("#yPosMinusButton").click(function() { 
							var tmp = $("#coronalSlider").slider("value");
							if(tmp > $("#coronalSlider").slider("option", "min"))
							{
								$("#coronalSlider").slider("value", tmp-1);
								changeYpos(tmp-1);
							}
						});

						$("#yPosPlusButton").click(function() { 
							var tmp = $("#coronalSlider").slider("value");
							if(tmp < $("#coronalSlider").slider("option", "max"))
							{
								$("#coronalSlider").slider("value", tmp+1);
								changeYpos(tmp+1);
							}
						});

						$("#zPosMinusButton").click(function() { 
							var tmp = $("#axialSlider").slider("value");
							if(tmp > $("#axialSlider").slider("option", "min"))
							{
								$("#axialSlider").slider("value", tmp-1);
								changeZpos(tmp-1);
							}
						});

						$("#zPosPlusButton").click(function() { 
							var tmp = $("#axialSlider").slider("value");
							if(tmp < $("#axialSlider").slider("option", "max"))
							{
								$("#axialSlider").slider("value", tmp+1);
								changeZpos(tmp+1);
							}
						});
						
					    $("#axial").mousewheel(function(event, delta) {
							var pos = parseInt($("#axialSlider").slider("value") - delta + 0.5);
							if(pos < $("#axialSlider").slider("option", "min")) pos = $("#axialSlider").slider("option", "min");
							if(pos > $("#axialSlider").slider("option", "max")) pos = $("#axialSlider").slider("option", "max");
							$("#axialSlider").slider("value", pos);
							return false;
						});

					    $("#coronal").mousewheel(function(event, delta) {
							var pos = parseInt($("#coronalSlider").slider("value") - delta + 0.5);
							if(pos < $("#coronalSlider").slider("option", "min")) pos = $("#coronalSlider").slider("option", "min");
							if(pos > $("#coronalSlider").slider("option", "max")) pos = $("#coronalSlider").slider("option", "max");
							$("#coronalSlider").slider("value", pos);
							return false;
						});
						
					    $("#sagittal").mousewheel(function(event, delta) {
							var pos = parseInt($("#sagittalSlider").slider("value") - delta + 0.5);
							if(pos < $("#sagittalSlider").slider("option", "min")) pos = $("#sagittalSlider").slider("option", "min");
							if(pos > $("#sagittalSlider").slider("option", "max")) pos = $("#sagittalSlider").slider("option", "max");
							$("#sagittalSlider").slider("value", pos);
							return false;
						});	


						$("#axial,#axialCross").mousedown(function(e){
							changePosAxial(e.pageX, e.pageY);
							$().mousemove(mouseMoveAxial).mouseup(mouseUpAxial);
							return false;
						});

						function mouseMoveAxial(e){
							changePosAxial(e.pageX, e.pageY);
							return false;
					    }
					    
						function mouseUpAxial() {
							$().unbind('mousemove', mouseMoveAxial).unbind('mouseup', mouseUpAxial);
						}

						$("#coronal,#coronalCross").mousedown(function(e){
							changePosCoronal(e.pageX, e.pageY);
							$().mousemove(mouseMoveCoronal).mouseup(mouseUpCoronal);
							return false;
						});

						function mouseMoveCoronal(e){
							changePosCoronal(e.pageX, e.pageY);
							return false;
					    }
					    
						function mouseUpCoronal() {
							$().unbind('mousemove', mouseMoveCoronal).unbind('mouseup', mouseUpCoronal);
						}
						
						$("#sagittal,#sagittalCross").mousedown(function(e){
							changePosSagittal(e.pageX, e.pageY);
							$().mousemove(mouseMoveSagittal).mouseup(mouseUpSagittal);
							return false;
						});

						function mouseMoveSagittal(e){
							changePosSagittal(e.pageX, e.pageY);
							return false;
					    }
					    
						function mouseUpSagittal() {
							$().unbind('mousemove', mouseMoveSagittal).unbind('mouseup', mouseUpSagittal);
						}
						
						function changePosAxial(x, y) {
							var px = parseInt(x - $("#axial").position().left);
							var py = parseInt(y - $("#axial").position().top);
							$("#axialCross").css("left", px-25).css("top", py-$("#dispHeight").val()-25);
							$("#coronalSlider").slider("value", 2*py);
							$("#sagittalSlider").slider("value", 2*px);
						}

						function changePosCoronal(x, y) {
							var px = parseInt(x - $("#coronal").position().left);
							var py = parseInt(y - $("#coronal").position().top);
							$("#coronalCross").css("left", px-25).css("top", py-$("#dispDepth").val()-25);
							$("#axialSlider").slider("value", 2*py);
							$("#sagittalSlider").slider("value", 2*px);
						}

						function changePosSagittal(x, y) {
							var px = parseInt(x - $("#sagittal").position().left);
							var py = parseInt(y - $("#sagittal").position().top);
							$("#sagittalCross").css("left", px-25).css("top", py-$("#dispDepth").val()-25);
							$("#axialSlider").slider("value", 2*py);
							$("#coronalSlider").slider("value", 2*px);
						}	

						$("#presetMenu,#scaleMenu").change(function(e){
							changeXpos($("#sagittalSlider").slider("value"));
							changeYpos($("#coronalSlider").slider("value"));
							changeZpos($("#axialSlider").slider("value"));		
						});	

						$("#axialEnlargeCross,#coronalEnlargeCross,#sagittalEnlargeCross").mouseover(function(e){
							$(this).css("cursor", "move");
						});

						$("#axialEnlargeCross,#coronalEnlargeCross,#sagittalEnlargeCross").mouseout(function(e){
							$(this).css("cursor", "auto");
						});

						$("#axialEnlargeCross").mousedown(function(e){
						
							$(this).css("cursor", "move");
						
							var baseX = parseInt(e.pageX - ($("#axialEnlargeArea").offset().left + 2));
							var baseY = parseInt(e.pageY - ($("#axialEnlargeArea").offset().top + 2));
						
							$().bind('mousemove.mouseAxialEnlarge', function(e) {
								var scale = $("#scaleMenu").val();
								var deltaX = parseInt((parseInt(e.pageX - ($("#axialEnlargeArea").offset().left + 2)) - baseX)/ scale);
								var deltaY = parseInt((parseInt(e.pageY - ($("#axialEnlargeArea").offset().top + 2)) - baseY)/ scale);
						
								baseX = parseInt(e.pageX - ($("#axialEnlargeArea").offset().left + 2));
								baseY = parseInt(e.pageY - ($("#axialEnlargeArea").offset().top + 2));
								
								if(baseX < 0 || baseX > 101 || baseY < 0 || baseY > 101) return false;
						
								$("#sagittalSlider").slider("value", ($("#sagittalSlider").slider("value")-deltaX));
								$("#coronalSlider").slider("value",  ($("#coronalSlider").slider("value")-deltaY));
								return false;
							}).one('mouseup.mouseAxialEnlarge', function() {
								$().unbind('mousemove.mouseAxialEnlarge');
							});
							return false;
						});
						
						$("#coronalEnlargeCross").mousedown(function(e){
						
							$(this).css("cursor", "move");
						
							var baseX = parseInt(e.pageX - ($("#coronalEnlargeArea").offset().left + 2));
							var baseZ = parseInt(e.pageY - ($("#coronalEnlargeArea").offset().top + 2));
						
							$().bind('mousemove.mouseCoronalEnlarge', function(e) {
								var scale = $("#scaleMenu").val();
								var deltaX = parseInt((parseInt(e.pageX - ($("#coronalEnlargeArea").offset().left + 2)) - baseX)/ scale);
								var deltaZ = parseInt((parseInt(e.pageY - ($("#coronalEnlargeArea").offset().top + 2)) - baseZ)/ scale);
						
								baseX = parseInt(e.pageX - ($("#coronalEnlargeArea").offset().left + 2));
								baseZ = parseInt(e.pageY - ($("#coronalEnlargeArea").offset().top + 2));
								
								if(baseX < 0 || baseX > 101 || baseZ < 0 || baseZ > 101) return false;
						
								$("#sagittalSlider").slider("value", ($("#sagittalSlider").slider("value")-deltaX));
								$("#axialSlider").slider("value",  ($("#axialSlider").slider("value")-deltaZ));
								return false;
							}).one('mouseup.mouseCoronalEnlarge', function() {
								$().unbind('mousemove.mouseCoronalEnlarge');
							});
							return false;
						});
						
						$("#sagittalEnlargeCross").mousedown(function(e){
						
							$(this).css("cursor", "move");
						
							var baseY = parseInt(e.pageX - ($("#sagittalEnlargeArea").offset().left + 2));
							var baseZ = parseInt(e.pageY - ($("#sagittalEnlargeArea").offset().top + 2));
						
							$().bind('mousemove.mouseSagittalEnlarge', function(e) {
								var scale = $("#scaleMenu").val();
								var deltaY = parseInt((parseInt(e.pageX - ($("#sagittalEnlargeArea").offset().left + 2)) - baseY)/ scale);
								var deltaZ = parseInt((parseInt(e.pageY - ($("#sagittalEnlargeArea").offset().top + 2)) - baseZ)/ scale);
						
								baseY = parseInt(e.pageX - ($("#sagittalEnlargeArea").offset().left + 2));
								baseZ = parseInt(e.pageY - ($("#sagittalEnlargeArea").offset().top + 2));
								
								if(baseY < 0 || baseY > 101 || baseZ < 0 || baseZ > 101) return false;
						
								$("#coronalSlider").slider("value", ($("#coronalSlider").slider("value")-deltaY));
								$("#axialSlider").slider("value",  ($("#axialSlider").slider("value")-deltaZ));
								return false;
							}).one('mouseup.mouseSagittalEnlarge', function() {
								$().unbind('mousemove.mouseSagittalEnlarge');
							});
							return false;
						});	

						$("#axialEnlarge,#coronalEnlarge,#sagittalEnlarge").mousedown(function(){ return false; });
						
						
						$(".landmarkRow").click(function(e){
						
							var orgTarget = e.originalTarget.id;
							
							if(orgTarget.substr(0,4) != "edit" && orgTarget.substr(0,3) != "del")
							{
								var id = parseInt(this.id.substr(3));
							
								var xPos = parseInt($("#row"+id+">.landmarkXpos").html());
								var yPos = parseInt($("#row"+id+">.landmarkYpos").html());
								var zPos = parseInt($("#row"+id+">.landmarkZpos").html());
							
								//alert(xPos + ' ' + yPos + ' ' + zPos);
							
								changeAllPos(id, xPos, yPos, zPos);
							}
						});
						

						$(".editPos").click(function(e){
							var id = parseInt(this.id.substr(4));
							
							$("#addRow,[class^='editPos'],[class^='delPos']").attr("disabled", "disabled").removeClass("form-btn-normal").removeClass("form-btn-hover").addClass("form-btn-disabled");
							$("#cancelButton,#saveButton").removeAttr("disabled").removeClass("form-btn-disabled").addClass("form-btn-normal"); 

							var name = $("#row"+id+">.landmarkName").html();
							var xPos = parseInt($("#row"+id+">.landmarkXpos").html());
							var yPos = parseInt($("#row"+id+">.landmarkYpos").html());
							var zPos = parseInt($("#row"+id+">.landmarkZpos").html());
							
							//alert(xPos + ' ' + yPos + ' ' + zPos);
							
							changeAllPos(id, xPos, yPos, zPos);
							$("#editID").val(id);
									
							$("#oldLandmarkName").val(name);
							$("#oldLandmarkXpos").val(xPos);
							$("#oldLandmarkYpos").val(yPos);
							$("#oldLandmarkZpos").val(zPos);

							//$("#row"+id+">.landmarkName").html('<input type="textbox" id="currentEditName" style="width:100px;" value="'+name+'" />');
							$("#row"+id+">.landmarkXpos").html('<input type="textbox" id="currentEditXpos" size=3 value='+xPos+' />');
							$("#row"+id+">.landmarkYpos").html('<input type="textbox" id="currentEditYpos" size=3 value='+yPos+' />');
							$("#row"+id+">.landmarkZpos").html('<input type="textbox" id="currentEditZpos" size=3 value='+zPos+' />');
							
						});	

						$(".delPos").click(function(e){
							var id = parseInt(this.id.substr(3));

							if(confirm("Do you delete the selected landmark?"))
							{
								$.post("plugin_template/landmark_registration_v0.php",
								{ mode: 'delete',
								  jobID: $("#jobID").val(),
								  subID: id
								},
								function(ret){
								  if(ret == "Success to detele!!")
								  {
								  	  $("#row" + id).remove();
									  var tmp = parseInt($("#maxID").val());
									  if(id == tmp)
									  {
									      $("#nextID").val(tmp);
									  	  $("#maxID").val(id-1);
									  }
								  }
								  alert(ret);
								});
							}
						});
						
						$("#addRow").click(function(e){
							
							var id = parseInt($("#nextID").val());
							$("#nextID").val(id+1);
							$("#editID").val(id);
							  
							$("#addRow,[class^='editPos'],[class^='delPos']").attr("disabled", "disabled").removeClass("form-btn-normal").removeClass("form-btn-hover").addClass("form-btn-disabled");
							$("#cancelButton,#saveButton").removeAttr("disabled").removeClass("form-btn-disabled").addClass("form-btn-normal"); 
							
							var xPos = $("#sagittalSlider").slider("value");
							var yPos = $("#coronalSlider").slider("value");
							var zPos = $("#axialSlider").slider("value");
							
							var html = '<tr id="row' + id + '">'
							         + '<td class="landmarkID">' + id + '</td>'
							         + '<td class="landmarkName"><input type="textbox" id="currentEditName" style="width:100px;" value="" /></td>'
							         + '<td class="landmarkRank" align=right>1</td>'
									 + '<td class="landmarkXpos"><input type="textbox" id="currentEditXpos" size=3 value='+xPos+' /></td>'
									 + '<td class="landmarkYpos"><input type="textbox" id="currentEditYpos" size=3 value='+yPos+' /></td>'
									 + '<td class="landmarkZpos"><input type="textbox" id="currentEditZpos" size=3 value='+zPos+' /></td>'
									 + '<td class="colButtons"><input type="button" id="edit' + id + '" class="editPos form-btn form-btn-disabled" value="E" disabled="disabled" />'
									 + '<input type="button" id="del' + id + '" class="delPos form-btn form-btn-disabled" value="D" disabled="disabled" /></td>'
									 + '</tr>';
									 
							$(".scrollTable").append(html);
							//alert($("#row" + id).offset().top);
							//$(".posTable").animate({scrollTop:$(".posTable,#row" + id).offset().top}, 10);
							$(".posTable").scrollTop(100000);
						});
						
						$("#cancelButton").click(function(e){	
							
							var id = parseInt($("#editID").val());
							$("#editID").val(0);
							
							if(id > parseInt($("#maxID").val()) && id == parseInt($("#nextID").val())-1)
							{
								$("#row" + id).remove();
								$("#nextID").val(id);
								
							}
							else
							{
								$("#row"+id+">.landmarkName").html($("#oldLandmarkName").val());
								$("#row"+id+">.landmarkXpos").html($("#oldLandmarkXpos").val());
								$("#row"+id+">.landmarkYpos").html($("#oldLandmarkYpos").val());
								$("#row"+id+">.landmarkZpos").html($("#oldLandmarkZpos").val());
							}
							
							$("#addRow,[class^='editPos'],[class^='delPos']").removeAttr("disabled").removeClass("form-btn-disabled").addClass("form-btn-normal");
							$("#cancelButton,#saveButton").attr("disabled", "disabled").removeClass("form-btn-normal").removeClass("form-btn-hover").addClass("form-btn-disabled"); 		
						});

						$("#saveButton").click(function(e){	
							
							var id = $("#editID").val();
						
							if(confirm("Do you save the edtited landmark?"))
							{
								var mode ="update";
							
								if(id > parseInt($("#maxID").val()) && id == parseInt($("#nextID").val())-1)
								{
									mode = "insert";
								}
								
								$.post("plugin_template/landmark_registration_v0.php",
								{ mode: mode,
								  jobID: $("#jobID").val(),
								  subID: id,
								  landmarkName: $("#row"+id+">.landmarkName").html(),
								  xPos: parseInt($("#currentEditXpos").val()),
								  yPos: parseInt($("#currentEditYpos").val()),
								  zPos: parseInt($("#currentEditZpos").val())
								},
								function(ret){
								  if(ret.substr(0,10) == 'Success to')
								  {
									//$("#row"+id+">.landmarkName").html($("#currentEditName").val());
									$("#row"+id+">.landmarkXpos").html($("#currentEditXpos").val());
									$("#row"+id+">.landmarkYpos").html($("#currentEditYpos").val());
									$("#row"+id+">.landmarkZpos").html($("#currentEditZpos").val());
									$("#addRow,[class^='editPos'],[class^='delPos']").removeAttr("disabled").removeClass("form-btn-disabled").addClass("form-btn-normal");
									$("#cancelButton,#saveButton").attr("disabled", "disabled").removeClass("form-btn-normal").removeClass("form-btn-hover").addClass("form-btn-disabled"); 				
									
									if(ret.substr(11,3)=='add')
									{
										$("#maxID").val(id);
										$("#nextID").val(id+1);
									}
									$("#editID").val(0);
								  }
								  alert(ret);
								  
								});
							}		
						});

					});

					-->
					</script>
					{/literal}

				</div><!-- / resultBody(Result) -->

				<input type="hidden" id="maxID"            value="{$maxID}">	
				<input type="hidden" id="nextID"           value="{$maxID+1}">	
				<input type="hidden" id="editID"           value=0>
				<input type="hidden" id="oldLandmarkName"  value=0>
				<input type="hidden" id="oldLandmarkXpos"  value=0>
				<input type="hidden" id="oldLandmarkYpos"  value=0>
				<input type="hidden" id="oldLandmarkZpos"  value=0>
				

			</div><!-- / .tab-content END -->

			<!-- darkroom button -->
			{include file='darkroom_button.tpl'}

		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
