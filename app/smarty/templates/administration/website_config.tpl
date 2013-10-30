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

#message { margin: 1em 0; padding: 1em; font-weight: bold; color: red; }

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
<p>
	<label><span>"Home" page message:</span><br/>
	<textarea id="top_message" name="top_message"></textarea></label>
</p>
<p>
	<label><input type="radio" name="top_message_style" value="plain" />Plain Text</label>
	&nbsp;
	<label><input type="radio" name="top_message_style" value="html" />HTML</label>
</p>
<p>
	<input class="form-btn" type="submit" value="Submit" />
</p>
</form>
</div>

{include file="footer.tpl"}