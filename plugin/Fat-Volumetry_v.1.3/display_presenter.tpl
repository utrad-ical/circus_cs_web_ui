{* Fat Volumetry Display Presenter Template *}
<script type="text/javascript">
var result_dir = "{$cadResult->webPathOfCadResult()|escape:javascript}";
</script>
<div id="fatvol-image-pane">
	<div id="viewer-orig"></div>
	<div id="viewer-measure"></div>
</div>
<div id="fatvol-detail-pane">
	<h2>Study Information</h2>
	<table id="fatvol-detail" class="detail-tbl">
		<tbody>
			<tr>
				<th style="width: 18em;">Body trunk volume</th>
				<td>{$display[0].body_trunk_volume|string_format:"%.2f"} [cm3]</td>
			</tr>
			<tr>
				<th>SAT volume</th>
				<td>{$display[0].sat_volume|string_format:"%.2f"} [cm3]</td>
			</tr>
			<tr>
				<th>VAT volume</th>
				<td>{$display[0].vat_volume|string_format:"%.2f"} [cm3]</td>
			</tr>
			<tr>
				<th>VAT/SAT</th>
				<td>{$display[0].vol_ratio|string_format:"%.3f"}</td>
			</tr>
		</tbody>
	</table>
	<h2>Slice Information</h2>
	<table class="detail-tbl">
		<tbody>
			<tr>
				<th>Slice number of DICOM series</th>
				<td><span id="dcm-slice-num" class=~slice-data"></span></td>
			</tr>
			<tr>
				<th>Slice location</th>
				<td><span id="slice-location" class="slice-data"></span> [mm]</td>
			</tr>
			<tr>
				<th>Body trunk area</th>
				<td><span id="body-trunk-area" class="slice-data"></span> [cm2]</td>
			</tr>
			<tr>
				<th>SAT area</th>
				<td><span id="sat-area" class="slice-data"></span> [cm2]</td>
			</tr>
			<tr>
				<th>VAT area</th>
				<td><span id="vat-area" class="slice-data"></span> [cm2]</td>
			</tr>
			<tr>
				<th>VAT/SAT</th>
				<td><span id="area-ratio" class="slice-data"></span></td>
			</tr>
			<tr>
				<th>Boundary length</th>
				<td><span id="boundary-length" class="slice-data"></span> [cm]</td>
			</tr>
		</tbody>
	</table>
</div>
