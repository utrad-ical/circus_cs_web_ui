<div id="darkroom-pane">
	<button id="darkroom">darkroom</button>
</div>

<script type="text/javascript" language="javascript">
<!--

var root = "{$totop|escape:javascript}";

{literal}
$(function() {
	$('#darkroom').click(function() {
		var body = $('body');
		$('body').toggleClass('darkroom');
		$.post(
			root + 'preference/change_darkroom.php',
			{ darkroom: body.hasClass('darkroom') ? 't' : 'f' },
			$.noop,
			'text'
		);
	});
});
{/literal}

{if $currentUser->darkroom}
{literal}
$(function(){
	$('body').addClass('darkroom');
});
{/literal}
{/if}
-->
</script>