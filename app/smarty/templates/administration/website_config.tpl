{capture name="require"}
js/jquery.formserializer.js
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{capture name="extra"}
<script type="text/javascript">
var initial = {$initial|@json_encode};

{literal}
$(function () {
	$('#config_form').fromObject(initial);
});
</script>

<style type="text/css">

#message { margin: 0.5em 0; padding: 1em; font-weight: bold; color: red; }
h3 { margin-bottom: 0.5em; }
input[name="session_time_limit"] { width: 5em; }
.form-btn { padding: 0.3em 1em; margin-top: 1em; }

#top_message {
	width: 600px;
	height: 10em;
}

form p { margin-bottom: 0.5em; }

label > span { font-weight: bold; }

</style>

{/literal}
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
require=$smarty.capture.require body_class="spot"}

<h2><div class="breadcrumb"><a href="administration.php">Administration</a> &gt;</div>
Website configuration</h2>

<div id="message">{$message|escape}</div>

<form id="config_form" method="post">
<input type="hidden" name="mode" value="set" />
<input type="hidden" name="token" value="{$token|escape}" />
<h3>"Home" page message</h3>
<p>
	<textarea id="top_message" name="top_message"></textarea>
</p>
<p>
	<label><input type="radio" name="top_message_style" value="plain" />Plain Text</label>
	&nbsp;
	<label><input type="radio" name="top_message_style" value="html" />HTML</label>
</p>

<h3>Session time limit</h3>
<p>
	<label>Automatic logout after: <input type="text" name="session_time_limit" /> min.
	</label>
</p>
<p>
	<label><input type="checkbox" name="keep_session" value="1" />Keep login status over browser sessions</label>
    (Changes will take effect after next login.)
<p>
	<input class="form-btn" type="submit" value="Submit" />
</p>
</form>
</div>

{include file="footer.tpl"}