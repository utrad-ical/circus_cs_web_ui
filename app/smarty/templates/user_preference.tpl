{capture name="require"}
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
js/jquery.formserializer.js
{/capture}

{capture name="extra"}

<script type="text/javascript">
var user_data = {$user|@json_encode};
var plugins = {$plugins|@json_encode};
{literal}

$(function() {
	function success(data) {
		if ($.isPlainObject(data) && 'message' in data) $.alert(data.message);
		else $.alert(JSON.stringify(data));
	}

	$('#password_change_button').click(function() {
		var $new = $('#new_password');
		var $old = $('#old_password');
		var $re = $('#re_password');
		if (!$old.val().length || !$new.val().length || !$re.val().length) {
			$.alert('Fill in all the text boxes.');
			return;
		}
		if ($new.val() != $re.val()) {
			$.alert('The two new passwords do not match.');
			return;
		}
		$.webapi({
			action: 'updateUserPreference',
			params: {
				mode: 'change_password',
				newPassword: $new.val(),
				oldPassword: $old.val()
			},
			onSuccess: function(data) {
				success(data);
				$('#password_section').clearForm();
			}
		});
	});

	$('#pagepref_section').fromObject(user_data);
	$('#pagepref_change_button').click(function() {
		var params = $.extend(
			{ mode: 'change_page_preference' },
			$('#pagepref_section').toObject()
		);
		$.webapi({
			action: 'updateUserPreference',
			params: params,
			onSuccess: success
		});
	});

	var $cad_name = $('#cad_name');
	var $cad_version = $('#cad_version');

	function cadChanged() {
		var val = $cad_name.val();
		var versions = plugins[val];
		$cad_version.empty();
		$.each(versions, function(i, version) {
			$('<option>').text(version).appendTo($cad_version);
		});
	}

	$cad_name.change(cadChanged);
	$.each(plugins, function(plugin_name) {
		$('<option>').text(plugin_name).appendTo($cad_name);
	});
	cadChanged();

	$('#cad_select_button').click(function() {
		$.webapi({
			action: 'updateUserPreference',
			params: {
				mode: 'get_cad_preference',
				plugin_name: $cad_name.val(),
				version: $cad_version.val()
			},
			onSuccess: function(data) {
				var form = data.form;
				if (!form) form = 'There is no preference available in this CAD.';
				$('#editing_cad').text($cad_name.val() + ' ver.' + $cad_version.val());
				$('#cad_pref_content').html(form).fromObject(data.preference);
				$('#cad_pref_content th').wrapInner('<span class="trim01" />');
				$('#cad_pref_form').show();
				$('#cad_selector').hide();
			}
		});
	});

	$('#cad_pref_reset_button').click(function() {
		var params = {
			mode: 'reset_cad_preference',
			plugin_name: $cad_name.val(),
			version: $cad_version.val()
		};
		$.confirm('Reset this preference and use default settings?', function(ans) {
			if (!ans) return;
			$.webapi({
				action: 'updateUserPreference',
				params: params,
				onSuccess: function(data) {
					success(data);
					$('#cad_pref_form').hide();
					$('#cad_selector').show();
				}
			});
		});
	});

	$('#cad_pref_update_button').click(function() {
		var params = {
			mode: 'set_cad_preference',
			plugin_name: $cad_name.val(),
			version: $cad_version.val(),
			preferences: $('#cad_pref_content').toObject()
		};
		$.webapi({
			action: 'updateUserPreference',
			params: params,
			onSuccess: function(data) {
				success(data);
				$('#cad_pref_form').hide();
				$('#cad_selector').show();
			},
			onFail: function(message) {
				$.alert(message);
			}
		});
	});

	$('#cad_pref_cancel_button').click(function() {
		$('#cad_pref_form').hide();
		$('#cad_selector').show();
		return false;
	});

});

</script>

<style type="text/css">
.pref_section { margin: 20px; }
.form-btn { width: 100px; }
.form_menu { padding-left: 20px; margin-bottom: 20px; margin-top: 10px; }
table.detail-tbl th { padding-right: 10px; }
#cad_pref_form { display: none; }
#cad_selector td { min-width: 150px; }
#editing_cad { font-weight: bold; }
label ~ label { margin-left: 10px; }
#cad_pref_content th { min-width: 100px; }
#cad_pref_content { background-color: #f9f9f9; margin-top: 10px; }

</style>

{/literal}
{/capture}
{include file="header.tpl" body_class="spot" require=$smarty.capture.require
	head_extra=$smarty.capture.extra}

<h2>User preference</h2>

<h3>Change password</h3>
<div class="pref_section" id="password_section">
	<table class="detail-tbl" style="width: 50%;">
		<tr>
			<th><span class="trim01">Current password</span></th>
			<td>
				<input id="old_password" type="password" style="width: 150px;" />
			</td>
		</tr>
		<tr>
			<th><span class="trim01">New password</span></th>
			<td><input id="new_password" type="password" style="width: 150px;" /></td>
		</tr>
		<tr>
			<th><span class="trim01">Re-enter new password</span></th>
			<td><input id="re_password" type="password" style="width: 150px;" /></td>
		</tr>
	</table>
	<div class="form_menu">
		<input id="password_change_button" type="button" value="Change" class="form-btn" />
	</div>
</div>

<h3>Page preference</h3>
<div class="pref_section" id="pagepref_section">
	<table class="detail-tbl" style="min-width: 50%;">
		<tr>
			<th><span class="trim01">Display today's list</span></th>
			<td>
				<label><input name="today_disp" type="radio" value="series" />Today's Series</label>
				<label><input name="today_disp" type="radio" value="cad" />Today's CAD</label>
			</td>
		</tr>
		<tr>
			<th><span class="trim01">Darkroom mode</span></th>
			<td>
				<label><input name="darkroom" type="radio" value="t" />ON</label>
				<label><input name="darkroom" type="radio" value="f" />OFF</label>
			</td>
		</tr>
		<tr>
			<th><span class="trim01">Anonymization</span></th>
			<td>
				<label><input name="anonymized" type="radio" value="t" {if !$currentUser->hasPrivilege("personalInfoView")} disabled="disabled"{/if} />ON</label>
				<label><input name="anonymized" type="radio" value="f" {if !$currentUser->hasPrivilege("personalInfoView")} disabled="disabled"{/if} />OFF</label>
			</td>
		</tr>
		<tr>
			<th><span class="trim01">Display latest missed lesions in home page</span></th>
			<td>
				<label><input name="show_missed" type="radio" value="own" />own</label>
				<label><input name="show_missed" type="radio" value="all" />all</label>
				<label><input name="show_missed" type="radio" value="none" />none</label>
			</td>
		</tr>
	</table>
	<div class="form_menu">
		<input id="pagepref_change_button" type="button" value="Change" class="form-btn" />
	</div>
</div>

<h3>CAD preference</h3>
<div class="pref_section" id="cadpref_section">
	<div id="cad_selector">
		<table class="detail-tbl" style="min-width: 50%;">
			<tr>
				<th><span class="trim01">CAD</span></th>
				<td>
					<select id="cad_name"></select>
				</td>
				<th><span class="trim01">Version</span></th>
				<td>
					<select id="cad_version"></select>
				</td>
			</tr>
		</table>
		<div class="form_menu">
			<p><input id="cad_select_button" type="button" value="Select" class="form-btn" /></p>
		</div>
	</div>

	<div id="cad_pref_form">
		<div>Preference for: <span id="editing_cad"></span></div>
		<table id="cad_pref_content" class="detail-tbl">
		</table>
		<div class="form_menu">
			<a href="#" id="cad_pref_cancel_button">Cancel</a>
			<input id="cad_pref_reset_button" type="button" value="Reset" class="form-btn" />
			<input id="cad_pref_update_button" type="button" value="Update" class="form-btn" />
		</div>
	</div>
</div>
</form>

{include file="footer.tpl"}