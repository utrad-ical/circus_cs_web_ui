/**
 * CIRCUS CS: Action log extension.
 * Tracks user operations.
 *
 * To log an event, call:
 * $(window).trigger('actionlog', { action: 'myAction', options: 'foo' });
 *
 * Author:
 *   Soichiro Miki
 */

circus = circus || {};

circus.actionLog = (function()
{
	var global = {
		register: function(action, options)
		{
			var params = { jobID: circus.jobID, action: action }
			if (options !== undefined && options !== null)
				params.options = options;
			$.post(
				'action_log.php',
				params,
				function(data) {
					if (data.status != 'OK')
						alert("Action log error:\n" + data.error.message);
				},
				'json'
			);
		}
	};
	return global;
})();

$(window).bind('actionlog', function (event, params) {
	if (circus.feedback.feedbackStatus != 'normal') return;
	circus.actionLog.register(params.action, params.options);
});