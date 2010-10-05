<script language="Javascript">
<!--

jQuery(function() {ldelim}
	jQuery("#slider").slider({ldelim}
		value:{$data.imgNum},
		min: 1,
		max: {$data.fNum},
		step: 1,
		slide: function(event, ui) {ldelim}
			jQuery("#sliderValue").html(ui.value);
		{rdelim},
		change: function(event, ui) {ldelim}
			jQuery("#sliderValue").html(ui.value);
			JumpImgNumber(ui.value);
		{rdelim}
	{rdelim});
	jQuery("#slider").css("width", "220px");
	jQuery("#sliderValue").html(jQuery("#slider").slider("value"));	

	ChangeMenuPage('{$data.encryptedPatientID}', '{$data.encryptedPatientName}',
                   '{$data.studyInstanceUID}', '{$data.seriesInstanceUID}', 0);
{rdelim});

{literal}
function Plus()
{
	var value = jQuery("#slider").slider("value");

	if(value < jQuery("#slider").slider("option", "max"))
	{
		value++;
		jQuery("#sliderValue").html(value);
		jQuery("#slider").slider("value", value);
	}
}

function Minus()
{
	var value = jQuery("#slider").slider("value");

	if(jQuery("#slider").slider("option", "min") <= value)
	{
		value--;
		jQuery("#sliderValue").html(value);
		jQuery("#slider").slider("value", value);
	}
}

function ReturnCADResult()
{
	var address = 'cad_results/show_cad_results.php'
                + '?execID=' + jQuery("#cadName").val()
                + '&cadName=' + jQuery("#cadName").val()
                + '&version=' + jQuery("#version").val()
                + '&studyInstanceUID=' + jQuery("#studyInstanceUID").val()
                + '&seriesInstanceUID=' + jQuery("#seriesInstanceUID").val()
                + '&feedbackMode=' + jQuery("#feedbackMode").val();

	self.location.href = address;
}

function JumpImgNumber(imgNum)
{
	jQuery("#imgNum").val(imgNum);
	document.form1.action = 'show_series_detail.php';
	document.form1.target = '_self';
	document.form1.method = 'POST';
	document.form1.submit();
}

function ChangePresetMenu(imgNum)
{
	var tmpStr = jQuery("#presetMenu").val().split("^");
	jQuery("#windowLevel").val(tmpStr[0]);
	jQuery("#windowWidth").val(tmpStr[1]);
	jQuery("#presetName").val(jQuery("#presetMenu option:selected").text());
	
	JumpImgNumber(imgNum);
}

function DownloadVolume(imgNum)
{
	if(confirm('Do you download raw volume data? (It takes several minute for creating data)'))
	{
		document.form1.action = 'developper/download_volume.php';
		document.form1.target = '_self';
		document.form1.method = 'POST';
		document.form1.submit();
	}
	else JumpImgNumber(imgNum);
}

