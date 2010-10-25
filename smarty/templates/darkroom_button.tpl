<div style="position:absolute; top: 0; right:0;">
			<input type="button" id="darkroom" name="darkroom" value="darkroom" onclick="ToggleDarkroomBtn();" />
</div>

<script language="javascript">
<!-- 
function ToggleDarkroomBtn()
{ldelim}
	$('body').toggleClass('mono');
	$('h1').toggleClass('darkroom');
	$('#menu p').toggleClass('user-darkroom');
	$('#container').toggleClass('menu-back').toggleClass('menu-darkroom').height( $(document).height() - 10 );
{rdelim}

{if $smarty.session.darkroomFlg==1}
	$(function(){ldelim}
		$('h1').toggleClass('darkroom');
		$('#menu p').toggleClass('user-darkroom');
		$('#container').toggleClass('menu-back').toggleClass('menu-darkroom').height( $(document).height() - 10 );
	{rdelim});
{/if}

-->
</script>
