/**
 * cad_result.js
 * Used in the cad_result.php page.
 */

var circus = circus || {};

circus.feedback = function() {
	var global = {
		initialize: function() {
			$('.result-block').each(function() {
				var block = this;
				var id = $("input.display-id", block).val();
				$(block).data('displayid', id);
				if (circus.feedback.initdata && circus.feedback.initdata.blockFeedback instanceof Object)
					circus.evalListener.set(block, circus.feedback.initdata.blockFeedback[id]);
			});
		},
		collect: function() {
			var results = {};
			$('.result-block').each(function() {
				var block = this;
				var id = $(block).data('displayid');
				results[id] = circus.evalListener.get(block);
			});
			return results;
		},
		register_ok: function() {
			var register_ok = true;
			$('.result-block').each(function() {
				var block = this;
				var id = $(block).data('displayid');
				if (!circus.evalListener.validate(block))
					register_ok = false;
			});
			return register_ok;
		},
		change: function() {
			var ok = circus.feedback.register_ok();
			$('#register').attr('disabled', ok ? '' : 'disabled').trigger('flush');
			var data = circus.feedback.collect();
			$('#result').val(JSON.stringify(data));
		},
		register: function() {
			var blockFeedback = circus.feedback.collect();
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

circus.cadresult = function() {
	var global = {
		sortBlocks: function(key, order) {
			var data = circus.cadresult.displays;
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
					circus.cadresult.showTab($('.tabArea a').index($(this)));
				}
			});
		}
	};
	return global;
}();


$(function(){
	// Initialize the evaluator status.
	circus.feedback.initialize();
	circus.evalListener.setup();

	var sort = circus.cadresult.sort;
	if (sort.key && (sort.order == 'asc' || sort.order == 'desc'))
	{
		circus.cadresult.sortBlocks(sort.key, sort.order);
	}
	if ($('#sorterArea'))
	{
		$('#sorterArea select[name=sortKey]').val(sort.key);
		$('#sorterArea input[name=sortOrder]').val([sort.order]);
		$('#sorterArea input, #sorterArea select').change(function() {
			var key = $('#sorterArea select[name=sortKey]').val();
			var order = $('#sorterArea input[name=sortOrder]:checked').val();
			circus.cadresult.sortBlocks(key, order);
		});
	}

	$('#register').click(circus.feedback.register);

	$('.tabArea a').click(function(event) {
		var target = $(event.target);
		var index = $('.tabArea a').index(target);
		circus.cadresult.showTab(index);
	});

});