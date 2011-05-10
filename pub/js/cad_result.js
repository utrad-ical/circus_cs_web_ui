CircusFeedback = function() {
	var global = {
		initialize: function(feedbacks) {
			$('.result-block').each(function() {
				var block = this;
				var id = $("input.display-id", block).val();
				$(block).data('displayid', id);
				evalListener.set(block, feedbacks.blockFeedback[id]);
			});
		},
		collect: function() {
			var results = {};
			$('.result-block').each(function() {
				var block = this;
				var id = $(block).data('displayid');
				results[id] = evalListener.get(block);
			});
			return results;
		},
		register_ok: function() {
			var register_ok = true;
			$('.result-block').each(function() {
				var block = this;
				var id = $(block).data('displayid');
				if (!evalListener.validate(block))
					register_ok = false;
			});
			return register_ok;
		},
		change: function() {
			var ok = CircusFeedback.register_ok();
			$('#register').attr('disabled', ok ? '' : 'disabled').trigger('flush');
			var data = CircusFeedback.collect();
			$('#result').val(JSON.stringify(data));
		},
		register: function() {

		}
	};
	return global;
}();

CircusCadResult = function() {
	var global = {
		sortBlocks: function(key, order) {
			var sorted = $('#result-blocks .result-block').sort(function(a,b){
				var aid = $(a).data('displayid');
				var bid = $(b).data('displayid');
				var tmp = data[aid][key] - data[bid][key];
				return order == 'desc' ? -tmp : tmp;
			});
			$.each(sorted, function(index, item){
				$('#result-blocks').append(item);
			});
		}
	};
	return global;
}();


$(function(){
	// Initialize the evaluator status.
	CircusFeedback.initialize(feedbacks);
	evalListener.setup();

	if (sort.key && sort.order == 'asc' || sort.order == 'desc')
	{
		CircusCadResult.sortBlocks(sort.key, sort.order);
	}
	if ($('#sorterArea'))
	{
		$('#sorterArea select[name=sortKey]').val(sort.key);
		$('#sorterArea input[name=sortOrder]').val([sort.order]);
		$('#sorterArea input, #sorterArea select').change(function() {
			var key = $('#sorterArea select[name=sortKey]').val();
			var order = $('#sorterArea input[name=sortOrder]:checked').val();
			CircusCadResult.sortBlocks(key, order);
		});
	}

	$('#register').click(function(){
		alert(JSON.stringify(CircusFeedback.collect()));
	})

});