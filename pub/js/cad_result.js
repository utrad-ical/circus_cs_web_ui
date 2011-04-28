CIRCUSFeedback = function() {
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
			var ok = CIRCUSFeedback.register_ok();
			$('#register').attr('disabled', ok ? '' : 'disabled');
			var data = CIRCUSFeedback.collect();
			$('#result').val(JSON.stringify(data));
		},
		register: function() {

		}
	};
	return global;
}();


$(function(){
	// Initialize the evaluator status.
	CIRCUSFeedback.initialize(feedbacks);
	evalListener.setup();

	// The following codes are for testing purpose
	$('#enable').click(function() {
		evalListener.enable();
	});

	$('#disable').click(function() {
		evalListener.disable();
	});
});