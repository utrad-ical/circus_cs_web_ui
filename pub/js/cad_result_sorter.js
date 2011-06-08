$(function () {
	var sort = circus.cadresult.presentation.extensions.BlockSorter;
	if (sort.defaultKey && (sort.defaultOrder == 'asc' || sort.order == 'desc'))
	{
		circus.cadresult.sortBlocks(sort.defaultKey, sort.defaultOrder);
	}
	if ($('#sorterArea'))
	{
		$('#sorterArea select[name=sortKey]').val(sort.defaultKey);
		$('#sorterArea input[name=sortOrder]').val([sort.defaultOrder]);
		$('#sorterArea input, #sorterArea select').change(function() {
			var key = $('#sorterArea select[name=sortKey]').val();
			var order = $('#sorterArea input[name=sortOrder]:checked').val();
			circus.cadresult.sortBlocks(key, order);
		});
	}
});

