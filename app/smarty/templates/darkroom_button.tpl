<div style="position:absolute; top: 0; right:0;">
	<input type="button" id="darkroom" name="darkroom" value="darkroom" onclick="ToggleDarkroomBtn();" />
</div>

<script language="javascript">
<!--
{literal}
function ToggleDarkroomBtn()
{
	$('body').toggleClass('darkroom');
}
{/literal}

{if $smarty.session.darkroomFlg==1}
{literal}
$(function(){
	 ToggleDarkroomBtn();
 });
{/literal}
{/if}

-->
</script>
