<div id="darkroom-pane">
	<button id="darkroom">darkroom</button>
</div>

<script type="text/javascript" language="javascript">
<!--

{literal}
$(function() {
	$('#darkroom').click(function() {
		var $body = $('body');
		$body.toggleClass('darkroom');
		$.webapi({
			action: 'updateUserPreference',
			params: {
				mode: 'change_darkroom',
				darkroom: $body.hasClass('darkroom') ? 't' : 'f'
			},
			onSuccess: $.noop,
			onFail: $.noop
		});
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