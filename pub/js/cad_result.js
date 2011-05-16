CircusFeedback = function() {
	var global = {
		initialize: function(feedbacks) {
			$('.result-block').each(function() {
				var block = this;
				var id = $("input.display-id", block).val();
				$(block).data('displayid', id);
				if (feedbacks && feedbacks.blockFeedback)
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
			var blockFeedback = CircusFeedback.collect();
			$.post("register_feedback.php",
				{
					jobID: $("#job-id").val(),
					feedbackMode: 'personal',
					feedback: JSON.stringify({blockFeedback:blockFeedback})
				},
				function (data)
				{
					alert(data);
				},
				"text"
			);
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
		},
		showTab: function(index) {
			$('.tab-content > div').hide();
			$('.tab-content > div:nth-child(' + (index+1) + ')').show();
			$('.tabArea a').removeClass('btn-tab-active');
			$('.tabArea ul li:nth-child(' + (index+1) + ') a').addClass('btn-tab-active');
		},
		showTabLabel: function(label) {
			$('.tabArea a').each(function () {
				if ($(this).text() == label) {
					CircusCadResult.showTab($('.tabArea a').index($(this)));
				}
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

	$('#register').click(CircusFeedback.register);

	$('.tabArea a').click(function(event) {
		var target = $(event.target);
		var index = $('.tabArea a').index(target);
		CircusCadResult.showTab(index);
	});

});