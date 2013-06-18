{*

CIRCUS CS Common Header Template.
Almost all templates, excluding login page, should include this template.

Parameters:
  require:
    Additional CSS (*.css) and JavaScript (*.js) files to
    include in this HTML file. Specify one file path per line.
    File path should be relative to the approot.
  head_extra:
    Anything passed by this parameter will be added
    to the head section of the rendered html.
  body_class:
    The class of the body tag.

*}
<!doctype html>
<html>
<head>
<meta charset="UTF-8">

<title>CIRCUS CS {$smarty.session.circusVersion}</title>

<link href="{$totop}css/layout.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="{$totop}jq/jquery.min.js"></script>
<script type="text/javascript" src="{$totop}js/circus-common.js"></script>
<script type="text/javascript">circus.totop = "{$totop|escape:javascript}";</script>

<link rel="shortcut icon" href="{$totop}favicon.ico" />
<link href="{$totop}css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />

<!-- template specific inclusions -->
{require require=$require}
{$head_extra}
<!-- / tempalte specific inclusions END -->
</head>

<body class="{$body_class}{if !$currentUser->hasPrivilege('menuShow')} nomenu{/if}">
<div id="page">
<div id="container" class="menu-back">

{if $currentUser->hasPrivilege('menuShow')}
<!-- ***** #leftside ***** -->
<div id="leftside">
	{include file='menu.tpl'}
</div>
<!-- / #leftside END -->
{/if}
<div id="content">