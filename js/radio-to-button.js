/*
  radio-to-button.js
    written by UTF-8
*/


//************************************************//
// ÉvÉâÉOÉCÉì
//************************************************//

(function($){
	$.fn.radioToButton=function( styles ){

		var SetStyle=function(jq,_mode){
			$.each( styles, function(k,v){
				jq.removeClass( v );
			});
			jq.addClass(styles[_mode]);
			
		};

		return this.each(function(){
			var _radio=$(this);
			if(!_radio.is('input[type=radio]')) return;
			
			if(_radio.attr('label')){
/*				var btn=$(document.createElement('input')).attr({
					type: 'button',
					value: _radio.attr('label'),
					disabled: _radio.attr('disabled'),
					className: _radio.attr('className')
				});
*/			var btn=$(document.createElement('a'))
							.click(function(){return false;})
							.addClass( _radio.attr('className') )
							.text(_radio.attr('label'))
							.attr('title',_radio.attr('title'));
				
				btn.css({
					fontFamily: 'Arial'
				});
				
				
				btn.hover(
					function(){
						if( !_radio.attr('checked') && !_radio.attr('disabled') ) SetStyle($(this),'hover');
					},
					function(){
						if( !_radio.attr('checked') && !_radio.attr('disabled') ) SetStyle($(this),'normal');
					}
				).click(function(){
					_radio.click();
					$(':radio[name='+_radio.attr('name')+']').trigger('flush');
				})
				.insertAfter(_radio);

				_radio.bind('flush',function(){
					// disabled
					if( _radio.attr('disabled') ){
						SetStyle( btn, 'disabled' );
					}
					// checked
					else if( _radio.attr('checked') ){
						SetStyle( btn, 'checked' );
					}
					// normal
					else {
						SetStyle( btn, 'normal' );
					}
				});
				
				_radio.trigger('flush');
				_radio.hide();
			}

		});
	}
})(jQuery);

//************************************************//
// èâä˙âª
//************************************************//

$(function(){
	$('.radio-to-button').radioToButton({
		normal: 'radio-to-button-normal',
		hover: 'radio-to-button-hover',
		checked: 'radio-to-button-checked',
		disabled: 'radio-to-button-disabled'
	});
	$('.radio-to-button-l').radioToButton({
		normal: 'radio-to-button-l-normal',
		hover: 'radio-to-button-l-hover',
		checked: 'radio-to-button-l-checked',
		disabled: 'radio-to-button-l-disabled'
	});
});



//************************************************//
// CSS Example
//************************************************//
/*
.radio-to-button {
	display:block;
	cursor:pointer;
	
	border:0px none;
	background-color:transparent;
	background-repeat:repeat;
	padding:0;
	margin:0;
	
	outline:0;
	overflow:hidden;
	
	float: left;
	margin-right: 1px;
}

.radio-to-button {
	background-image: url(./btn-radio.jpg);
}

.radio-to-button-normal {
	color: #333;
	border: 1px solid #333;
	background-position: 0 0;
}
.radio-to-button-hover {
	color: #333;
	border: 1px solid #ff6600;
	background-position: 0 -23px;
}
.radio-to-button-checked {
	color: #fff;
	border: 1px solid #8a3b2b;
	background-position: 0 -46px;
}
.radio-to-button-disabled {
	color: #ccc;
	border: 1px solid #ccc;
	background-position: 0 -69px;
}
</style>
*/

//************************************************//
// HTML Example
//************************************************//
/*
<form>
<div style="background-color:#ffc; padding:20px;">
	<input type="radio" name="hoge" value="1" label="ílÇÕÇPÇæ" />
	<input type="radio" name="hoge" value="2" label="ílÇÕÇQÇæ" />
	<input type="radio" name="hoge" value="3" label="ílÇÕÇRÇæ" />
</div>

<div style="background-color:#cff; padding:20px;">
	<input type="radio" name="hoge" value="1" label="ílÇÕÇPÇæ" />
	<input type="radio" name="hoge" value="2" label="ílÇÕÇQÇæ" disabled="disabled" />
	<input type="radio" name="hoge" value="3" label="ílÇÕÇRÇæ" />
</div>

<div style="background-color:#fcf; padding:20px;">
	<input type="radio" name="hoge" value="1" label="ílÇÕÇPÇæAAAAAAAA" />
	<input type="radio" name="hoge" value="2" label="ílÇÕÇQÇæ" />
	<input type="radio" name="hoge" value="3" label="ílÇÕÇRÇæ" />
</div>
</form>
*/


