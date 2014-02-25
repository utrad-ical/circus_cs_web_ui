/**
 * cad_result.js
 * Used in the cad_result.php page.
 */

var circus = circus || {};

circus.feedback = function() {
	var postUrl = null;

	var global = {
		initialize: function() {
			// setup blocks
			var idata = circus.feedback.initdata;
			$('.result-block').each(function() {
				var block = this;
				var id = $("input.display-id", block).val();
				$(block).data('displayid', id);
			});
			circus.evalListener.setup();

			// assign initial data
			if (idata && idata.blockFeedback instanceof Object)
			{
				$('.result-block').each(function() {
					var block = this;
					var id = $("input.display-id", block).val();
						circus.evalListener.set(this, idata.blockFeedback[id]);
				});
			}

			if (circus.feedback.additional instanceof Array)
			{
				$.each(circus.feedback.additional, function(key, additional) {
					if (idata && idata.additionalFeedback)
						data = idata.additionalFeedback[additional.name];
					else
						data = null;
					additional.initialize(data);
				});
			}
		},
		collect: function() {
			var blockFeedback = {};
			var additionalFeedback = {};
			$('.result-block').each(function() {
				var block = this;
				var id = $(block).data('displayid');
				blockFeedback[id] = circus.evalListener.get(block);
			});
			if (circus.feedback.additional instanceof Array)
				$.each(circus.feedback.additional, function (name, afb) {
					additionalFeedback[afb.name] = afb.collect();
				});
			return {
				blockFeedback: blockFeedback,
				additionalFeedback: additionalFeedback
			};
		},
		disable: function () {
			$('.result-block').each(function () {
				circus.evalListener.disable(this);
			});
		},
		enable: function () {
			$('.result-block').each(function () {
				circus.evalListener.enable(this);
			})
		},
		register_ok: function() {
			if (circus.feedback.feedbackStatus != 'normal')
				return { register_ok: false, messages: [] }
			var register_ok = true;
			var messages = [];
			var caption = circus.cadresult.presentation.displayPresenter.caption;
			$('.result-block').each(function() {
				var block = this;
				var id = $(block).data('displayid');
				var tmp = circus.evalListener.validate(block);
				if (!tmp.register_ok) {
					register_ok = false;
					if (tmp.message)
					{
						var mes = caption + ': <span class="register-not-ok">' + tmp.message + '</span>';
						if ($.inArray(mes, messages) == -1)
						messages.push(mes);
					}
				}
			});
			if (register_ok)
				messages.push(caption + ': <span class="register-ok">Complete</span>');
			if (circus.feedback.additional instanceof Array) {
				var ad = circus.feedback.additional;
				for (var i = 0; i < ad.length; i++) {
					var tmp = ad[i].validate();
					if (!tmp.register_ok) {
						register_ok = false;
					}
					if (tmp.message && $.inArray(tmp.message, messages) == -1)
						messages.push(tmp.message);
				}
			}
			return { register_ok: register_ok, messages: messages };
		},
		change: function() {
			circus.feedback.modified = true;
			var ok = circus.feedback.register_ok();
			if (ok.register_ok === true) {
				$('#register').enable();
				var data = circus.feedback.collect();
				$('#result').val(JSON.stringify(data));
			} else {
				$('#register').disable();
			}
			$('#register-error').empty();
			$.each(ok.messages, function (index, msg) {
				$('<li>').html(msg).appendTo('#register-error');
			})
		},
		register: function(temporary, postLocation) {
			var feedback = circus.feedback.collect();
			postUrl = temporary ? postLocation : null;
			$.post("register_feedback.php",
				{
					jobID: $("#job-id").val(),
					feedbackMode: circus.feedback.feedbackMode,
					temporary: temporary ? 1 : 0,
					feedback: JSON.stringify(feedback)
				},
				global.register_success,
				"text"
			);
		},
		register_success: function(result) {
			var obj = JSON.parse(result);
			if (obj)
			{
				if (obj.status == 'OK')
				{
					if (postUrl)
					{
						$(window).trigger('actionlog', {
							action: 'save',
							success: function() {
								location.replace(postUrl);
							}
						});
					}
					else
					{
						$(window).trigger('actionlog', {
							action: 'register',
							success: function() {
								location.reload(true);
							}
						});
					}
				}
				else
					alert("Error while registering feedback:\n" + obj.error.message);
			}
			else
				alert("System Error:\n" + result);
		},
		unregister_check: function() {
			$('#unregister_pane').hide();
			$.webapi({
				action: 'unregisterFeedback',
				params: {
					feedbackMode: circus.feedback.feedbackMode,
					jobID: circus.jobID,
					dryRun: 1
				},
				onSuccess: function(response) {
					if (response.canUnregister) {
						$('#unregister_pane').show();
						$('#unregister').click(circus.feedback.unregister);
					}
				}
			});
		},
		unregister: function(event) {
			$.choice(
				'Unregister this feedback and edit again?',
				['Cancel', 'Unregister'],
				function (choice) {
					if (choice == 1) {
						$.webapi({
							action: 'unregisterFeedback',
							params: {
								feedbackMode: circus.feedback.feedbackMode,
								jobID: circus.jobID
							},
							onSuccess: function(response) {
								$(window).trigger('actionlog', {
									action: 'unregister',
									force: true,
									success: function() {
										location.reload(true);
									}
								});
							},
							onFail: function(message) { $.alert(message); }
						});
					}
				}
			);
		},
		additional: []
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
			var text = $('.tabArea ul li:nth-child(' + (index+1) + ') a').addClass('btn-tab-active').text();
			$(window).trigger('actionlog', { action: 'switchtab', options: text });
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
	circus.feedback.change();
	if (circus.feedback.feedbackStatus != 'normal')
	{
		circus.feedback.disable();
	}

	if (circus.feedback.feedbackStatus == 'registered')
	{
		circus.feedback.unregister_check();
	}

	circus.feedback.modified = false;

	$('#register').click(function() {
		circus.feedback.register(false);
	});

	$('.tabArea a').click(function(event) {
		var target = $(event.target);
		var index = $('.tabArea a').index(target);
		circus.cadresult.showTab(index);
	}).mousedown(function() { return false; });

	$('#mode-form input[name=feedbackMode]')
		.val([circus.feedback.feedbackMode])
		.trigger('flush');
	$('#mode-form a.radio-to-button-l').click(function() {
		var mode = $('#mode-form input[name=feedbackMode]:checked').val();
		if (mode != circus.feedback.feedbackMode)
			$('#mode-form').submit();
	}).each(function() {
		var title = $(this).attr('title');
		if (!title) return;
		$(this).toolhint({
			content: title,
			my: 'left top',
			at: 'left bottom',
		}).removeAttr('title');
	});

	var postLocation = null;

	if (circus.feedback.feedbackStatus == 'normal')
	{
		$('#menu .topmenu, #about-circus-btn').click(function(event) {
			if (!circus.feedback.modified && !circus.feedback.feedbackTemporary)
				return;
			postLocation = $(event.currentTarget).attr('href');
			$.choice(
				'<p><strong>This feedback is not registered yet.</strong></p>' +
				'<p>Do you want to temporarily save changes before leaving this page?</p>',
				["Save", "Don't Save", "Cancel"],
				function(choice) {
					if (choice == 0) circus.feedback.register(true, postLocation);
					if (choice == 1) location.replace(postLocation);
				}
			);
			return false;
		});
	}

	function setModeIcon(status, target)
	{
		var className = '';
		if (status == 'locked') className = 'ui-icon-circle-minus';
		if (status == 'registered') className = 'ui-icon-check';
		if (status == 'disabled') className = 'ui-icon-locked';
		if (className)
			$('<span>')
				.addClass('ui-icon mode-icon').addClass(className)
				.appendTo($(target).next('a'));
	}
	setModeIcon(circus.feedback.personalFeedbackAvail, '#personal-mode');
	setModeIcon(circus.feedback.consensualFeedbackAvail, '#consensual-mode');


	if (circus.feedback.consensualFeedbackAvail != 'locked')
	{
		$('#consensual-mode').enable();
	}


	// admin menus
	var admin_btn = $('#cad-result-admin-menu');
	if (admin_btn.length) {
		var menu = $('#cad-result-admin-menu-items').menu();
		admin_btn.on('mouseenter', function() {
			menu.show().position({of: admin_btn, my: 'right top', at: 'right bottom'});
		});
		$('#cad-result-admin-menu-pane').on('mouseleave', function() {
			menu.hide();
		});
		$('#invalidate-btn').click(function() {
			if (circus.cadresult.status != 4) {
				$.alert('This CAD job is already marked as invalid.');
				return;
			}
			$.confirm('Invalidate this CAD Job?', function(ok) {
				if (ok == 0) return;
				$.webapi({
					action: 'invalidateJob',
					params: { jobID: [circus.jobID] },
					onSuccess: function(result) {
						location.reload(true);
					}
				});
			});
		});
	}

	// action logs
	$(window).bind('actionlog', function (event, params) {
		if (!('CadActionLog' in circus.cadresult.presentation.extensions)) {
			if ('success' in params && typeof params.success == 'function') {
				params.success();
			}
			return;
		}
		if (circus.feedback.feedbackStatus != 'normal' && !params.force) return;
		var data = { jobID: circus.jobID, action: params.action }
		if ('options' in params)
			data.options = params.options;
		$.post(
			'action_log.php',
			data,
			function(ret) {
				if (ret.status != 'OK') {
					alert("Action log error:\n" + ret.error.message);
				}
				if ('success' in params && typeof params.success == 'function') {
					params.success();
				}
			},
			'json'
		);
	});
	$(window).trigger('actionlog', { action: "open", options: "CAD result, " + circus.feedback.feedbackMode });

	// tags
	var refresh = function(tags) {
		$('#cad-tags').refreshTags(tags, '../cad_log.php', 'filterTag');
	};
	$('#edit-cad-tags').click(function() {
		circus.edittag.openEditor(4, circus.jobID, refresh);
	})
	circus.edittag.load(4, circus.jobID, refresh);
});