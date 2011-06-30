

$(function(){// <body onload="...." />

	$('.registration').hoverStyle({
		normal: 'registration-normal',
		hover: 'registration-hover',
		disabled: 'registration-disabled'
	});
	makeFormBtn();

});


function makeFormBtn()
{
	$('.form-btn').hoverStyle({
		normal: 'form-btn-normal',
		hover: 'form-btn-hover',
		disabled: 'form-btn-disabled'
	});
}



