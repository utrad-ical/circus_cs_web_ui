//************************************************//
// jQuery plugin for menu button rollovers.
//************************************************//
(function($){
	$.fn.rolloverBtn=function(_switch){
		return this.each(function(){
			var _this=$(this);
			if(!_this.data('rolloverBtnInit')){
				/***** bind rollover mouse handlers *****/
				_this.bind('mouseover',
					function(){
						if( _this.data('rolloverBtnEnable') )
							_this.css('background-position','0 '+$.curCSS(this,'height'));
					}
				).bind('mouseout',
					function(){_this.css('background-position','0 0');}
				).data('rolloverBtnInit',true)
				.data('rolloverBtnEnable',true)
			}
			if(_switch && _switch.match(/on/i)){
				_this.data('rolloverBtnEnable',true);
			}else if(_switch && _switch.match(/off/i)){
				_this.data('rolloverBtnEnable',false);
			}
		});
	};

	$.fn.hoverStyle=function(styles){
		var SetStyle=function(jq,_mode){
			$.each( styles, function(k,v){
				jq.removeClass( v );
			});
			jq.addClass(styles[_mode]);
		};

		return this.each(function(){
			var _this=$(this);
			_this.hover(
				function(){
					if( !_this.attr('disabled') ) SetStyle(_this,'hover');
				},
				function(){
					if( !_this.attr('disabled') ) SetStyle(_this,'normal');
				}
			);
			if( _this.attr('disabled') ){
				SetStyle(_this,'disabled');
			}else{
				SetStyle(_this,'normal');
			}
		});
	}
})(jQuery);

//************************************************//
// Initialization
//************************************************//
$(function(){
	// Enable rollover actions for elements with these classes
	var classes='.jq-btn';
	$(classes).rolloverBtn();

	// Disable rollover action if the class of jq-btn is
	// the same of the ID of the body of the current page.
	var bodyId=$('body').attr('id');
	if(bodyId){
		$(classes).filter('.'+bodyId).each(function(){
			var h=$.curCSS(this,'height');
			$(this).unbind()
			.css({backgroundPosition:'0 '+h, cursor:'default'})
			.unbind('click').bind('click',function(){return false});
		})
	}
});


