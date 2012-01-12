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
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />

<title>CIRCUS CS {$smarty.session.circusVersion}</title>

<link href="{$totop}css/import.css" rel="stylesheet" type="text/css" media="all" />
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