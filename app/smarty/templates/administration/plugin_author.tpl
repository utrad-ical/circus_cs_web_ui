{capture name="require"}
jq/ui/jquery-ui.min.js
{/capture}
{capture name="extra"}
{literal}
<style>
#tbl { width: 100%; }
#tbl th { width: 150px; padding: 1em 0; }
#tbl td { text-align: left; }
div.desc {
	color: gray;
	display: none;
	margin: 2px;
}
input:focus ~ div.desc {
	display: block;
}
</style>
<script>
//
</script>
{/literal}
{/capture}
{include file="header.tpl"
	head_extra=$smarty.capture.extra body_class="spot"}

<h2>Plugin Author</h2>

<table class="col-tbl" id="tbl">
	<tbody>
		<tr>
			<th>Plugin Title</th>
			<td>
				<input type="text" />
				<div class="desc">
					Title of the plugin (eg "MRA-CAD", "Pancreas-Extractor").
				</div>
			</td>
		</tr>
		<tr>
			<th>Plugin Version</th>
			<td>
				<input type="text" />
				<div class="desc">
					Version of the plugin (eg. "1.0", "2.1").
				</div>
			</td>
		</tr>
		<tr>
			<th>Plugin Type</th>
			<td>
				<label>
					<input type="radio" name="plugin_type" value="1" checked="checked" />CAD
				</label>
			</td>
		</tr>
		<tr>
			<th>Architecture</th>
			<td>
				<label>
					<input type="radio" name="plugin_type" value="x86" />x86
				</label>
				<label>
					<input type="radio" name="plugin_type" value="x64" />x64
				</label>
				<label>
					<input type="radio" name="plugin_type" value="x86/x64" />x86/x64
				</label>
				<div class="desc">
					Plug-in architecture.
				</div>
			</td>
		</tr>
		<tr>
			<th>Description</th>
			<td>
				<input type="text" name="description" />
				<div class="desc">
					Short description of this plug-in.
				</div>
			</td>
		</tr>
		<tr>
			<th>Input Type</th>
			<td>
				<label>
					<input type="radio" name="plugin_type" value="1" />Single series
				</label>
				<label>
					<input type="radio" name="plugin_type" value="2" />Single study
				</label>
				<label>
					<input type="radio" name="plugin_type" value="3" />All study within patient
				</label>
				<div class="desc">
					Scope of the series which this plug-in can process.
				</div>
			</td>
		</tr>
	<tbody>
</table>

{include file="footer.tpl"}