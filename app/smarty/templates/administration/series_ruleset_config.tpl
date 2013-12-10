{capture name="require"}
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
css/jquery.ruleseteditor.css
js/series_ruleset.js
js/jquery.formserializer.js
js/jquery.ruleseteditor.js
administration/series_ruleset_config.js
{/capture}
{capture name="extra"}
{literal}

<style type="text/css">

h3 { margin-bottom: 15px; }

#content div.vol-id {
	border-top: 1px solid gray;
	font-weight: bold;
	margin: 0 5px 3px 0;
}

#content div.vol-id .volume-label {
	color: #8a3b2b;
}

#selected-plugin-pane {
	margin: 0 0 15px; 0;
}

#selector-pane {
	width: 300px;
	background-color: white;
	float: left;
	min-height: 510px;
	word-wrap: break-word;
}

#editor-pane {
	margin-left: 300px;
	border: 5px solid #eee;
	padding: 5px;
	min-height: 500px;
}

#editor-pane.active {
	border-color: #ebbe8c;
}

#select-help {
	margin: 50px;
	text-align: center;
}

.rulesets {
	margin-bottom: 2px;
}

.rulesets li {
	margin: 5px 0;
	background-color: #eee;
	cursor: pointer;
	border-right: 5px solid white;
}

.rulesets li div.rule-no {
	font-weight: bold;
	float: left;
	background-color: gray;
	color: white;
	margin-right: 1em;
}

.rulesets li .rule-rule {
	float: left;
}

.rulesets li:hover {
	background-color: #ffddae;
}

.rulesets li.active {
	background-color: #ebbe8c;
	border-color: #ebbe8c;
}

.rulesets li.active div.rule-no {
	background-color: #8a3b2b;
}

.ruleset-tools { text-align: right; margin: 0 5px 15px 0; }
.ruleset-toolbutton { width: 18px; height: 18px; margin: 0 3px; }
.ruleset-toolbutton span.ui-button-icon-primary { left: 0; }
.rule-box { margin-top: 10px; }
.rule-box th { font-weight: bold; padding: 5px 15px; }

#down { font-size: 20px; text-align: center; }

#save-pane { text-align: right; margin: 30px 5px 0 0; border-top: 1px solid gray; padding: 10px; }
#save-button { padding: 0.5em 2em; }

</style>

{/literal}

<script type="text/javascript">
var keys = {$keys|@json_encode};
</script>

{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
require=$smarty.capture.require body_class="spot"}

<h2><div class="breadcrumb"><a href="administration.php">Administration</a> &gt;</div>
Series Ruleset Configuration</h2>

<div id="plugin-selector-pane">
	<b>Plugin:</b>&nbsp;
	<select id="plugin-select">
		<option value="">Select Plugin</option>
	{foreach from=$plugins item=item}
		<option value="{$item.id|escape}">{$item.name|escape}</option>
	{/foreach}
	</select>
</div>

<div id="selector-pane">
	<div id="selected-plugin-pane"><b>Plugin:</b>&nbsp;<span id="selected-plugin-name"></span></div>
	<div id="rulesets-list"></div>
	<div id="save-pane">
		<a href="#" id="close-button">Close</a>&nbsp;
		<input type="button" class="form-btn" id="save-button"
		value="Save settings" />
	</div>
</div>
<div id="editor-pane">
	<div id="select-help">Select Rule Set</div>

	<div id="editor-contents">
		<h3>Condition</h3>
		<div id="condition"></div>

		<div id="down">&downarrow;</div>

		<h3>Rule</h3>

		<table class="rule-box">
			<tbody>
				<tr>
					<th>Start image number</th>
					<td><input type="text"name="start_img_num"  id="start-img-num" /></td>
				</tr>
				<tr>
					<th>End image number</th>
					<td><input type="text"name="end_img_num"  id="end-img-num" /></td>
				</tr>
				<tr>
					<th>Required private DICOM tags</th>
					<td><input type="text"name="required_private_tags"  id="required-private-tags" size="30" /></td>
				</tr>
				<tr>
					<th>Direction</th>
					<td>
						<select id="image-delta">
							<option value="0">auto (head to foot)</option>
							<option value="1">forward</option>
							<option value="-1">reverse</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Provide environment</th>
					<td><input type="text"name="environment"  id="environment" /></td>
				</tr>
				<tr>
					<th>Force continuous series</th>
					<td><input type="checkbox"name="continuous"  id="continuous" /></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

{include file="footer.tpl"}