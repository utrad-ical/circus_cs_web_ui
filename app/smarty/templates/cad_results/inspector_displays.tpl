<h2>Displays</h2>
<script type="text/javascript">
<!--
{literal}
$(function() {
	var current;
	var d = circus.cadresult.displays;
	var max = $('.inspector-displays tbody tr').length;

	function show(index)
	{
		index = parseInt(index);
		if (index < 0) index = 0;
		if (index >= max) index = max - 1;
		var display_id = $('.inspector-displays tbody tr').hide().eq(index).show().find('td:eq(0)').text();
		current = index;
		$('#inspector-display-id').val(display_id);
	}

	if (max > 0)
	{
		$('#inspector-display-prev').click(function() { show(current-1); });
		$('#inspector-display-next').click(function() { show(current+1); });
		$('#inspector-expand-all').click(function() {
			$('.inspector-displays tbody tr').show();
			$('.inspector-navi span input').disable();
			$('#inspector-expand-all').hide();
			$('#inspector-collapse-all').show();
		});
		$('#inspector-collapse-all').click(function() {
			$('.inspector-displays tbody tr').hide();
			$('.inspector-navi span input').enable();
			$('#inspector-expand-all').show();
			$('#inspector-collapse-all').hide();
			show(0);
		});
		$('#inspector-display-id').blur(function() {
			var id = parseInt($('#inspector-display-id').val());
			if (d[id])
			{
				var trs = $('.inspector-displays tbody tr');
				for (var i = 0; i < trs.length; i++)
				{
					if (parseInt(trs.eq(i).find('td:eq(0)').text()) == id)
					{
						show(i);
						break;
					}
					trs.end();
				}
			}
			else
			{
				show(current);
			}
		});
		show(0);
	}
	else
		$('.inspector-navi input').disable();
});
{/literal}
-->
</script>
<p class="inspector-navi">
	<span>
	<input id="inspector-display-prev" type="button" value="&lt;" class="form-btn" />
	<input id="inspector-display-id" type="text" value="0" /> / {$displays|@count|number_format}
	<input id="inspector-display-next" type="button" value="&gt;" class="form-btn" />
	</span>
	<input id="inspector-expand-all" type="button" value="Expand all" class="form-btn" />
	<input id="inspector-collapse-all" type="button" value="Collapse all" class="form-btn" style="display: none"/>
</p>
<table class="col-tbl inspector-displays">
	<thead>
		<tr>
			<th>Display ID</th>
			<th>Contents</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$displays key=display_id item=item}
		<tr style="">
			<td class="name themeColor">{$display_id|escape}</td>
			<td>{$item|@dumpParams}</td>
		</tr>
	{/foreach}
	</tbody>
</table>