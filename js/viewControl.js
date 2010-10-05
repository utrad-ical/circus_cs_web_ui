//************************************************//
// ‰Šú‰»
//************************************************//
$(function(){
	$('#container').height( $(document).height() - 10 );
	
	$('#agresearch,#aresearch,#acad,#aseries,#astudy,#apatient').tabSwitch(
		'#agresearch,#aresearch,#acad,#aseries,#astudy,#apatient',
		'#groupResearchSearch,#researchSearch,#cadSearch,#seriesSearch,#studySearch,#patientSearch',
		{
			selected: 'selected-btn-search',
			unselected: 'btn-search'
		}
	);
});

//************************************************//
// ƒvƒ‰ƒOƒCƒ“
//************************************************//
(function($){
	$.fn.tabSwitch=function( triggers, targets, styles ){
		return this.each(function(){
			$(this).click(function(){
				$(triggers).removeClass(styles.selected).addClass(styles.unselected);
				$(this).removeClass(styles.unselected).addClass(styles.selected);
				$(targets)
					.hide().find('select').hide().end()
				.eq( $(triggers).index(this) ).show().find('select').show();
			});
		});
	}
})(jQuery);

