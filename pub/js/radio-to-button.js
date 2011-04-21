/*
 * radio-to-button.js
 *
 * Converts radio buttons which have 'radio-to-button' class
 * into <a> elements with the same class name.
 * <a> elements can be styled more easily by CSS.
 */


//************************************************//
// jQuery plugin definition
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
			var btn=$(document.createElement('a'))
							.click(function(){return false;})
							.addClass( _radio.attr('className') )
							.text(_radio.attr('label'))
							.attr('title',_radio.attr('title'));
				btn.hover(
					function(){
						if( !_radio.attr('checked') && !_radio.attr('disabled') )
							SetStyle($(this),'hover');
					},
					function(){
						if( !_radio.attr('checked') && !_radio.attr('disabled') )
							SetStyle($(this),'normal');
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
// Initialization
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
// HTML Example
//************************************************//
/*
<form>
<div>
	<input type="radio" name="foo" value="1" label="apples" />
	<input type="radio" name="foo" value="2" label="bananas" disabled="disabled" />
	<input type="radio" name="foo" value="3" label="oranges" />
</div>
</form>
*/
