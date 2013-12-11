<?php

require("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(AUTH::SERVER_SETTINGS);

$smarty = new SmartyEx();
$smarty->assign('keys', SeriesFilter::availableKeys());
$smarty->display('administration/plugin_author.tpl');