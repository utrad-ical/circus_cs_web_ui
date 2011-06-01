{capture name="extra"}
{literal}
<script type="text/javascript">
<!--
$(function () {
	$('#back').click(function () {
		history.back();
	});
});
-->
</script>

<style type="text/css">
#content #error-container {
	margin: 200px 50px 0 50px;
	padding: 20px;
	border: 1px solid black;
}

#content #error-title {
	font-size: 250%;
}

#content #error-body {
	font-weight: bold;
	color: red;
}

#content #back-p {
	text-align: right;
}

</style>
{/literal}
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra}

<div id="error-container">
<h2 id="error-title">
{if $errorTitle}{$errorTitle|escape|nl2br}{else}Error{/if}
</h2>
<p id="error-body">
{$message|escape|nl2br}
</p>
<p id="back-p"><input type="button" id="back" class="form-btn" value="Back" /></p>
</div>

{include file="footer.tpl"}