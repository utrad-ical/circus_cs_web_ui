$(function () {
	var sort = circus.cadresult.presentation.extensions.BlockSorter.initials;
	circus.cadresult.sortBlocks(sort.key, sort.order);

	var areas = $('.sorter-area');
	updateElement(sort.key, sort.order);

	$('input, select', areas).change(function(event) {
		var target = $(event.target).closest('.sorter-area');
		var key = $('select[name=sortKey]', target).val();
		var order = $('input[name=sortOrder]:checked', target).val();
		circus.cadresult.sortBlocks(key, order);
		updateElement(key, order);
	});

	function updateElement(key, order)
	{
		$('select[name=sortKey]', areas).val(key);
		$('input[name=sortOrder]', areas).val([order]);
	}
});

