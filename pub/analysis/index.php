<?php
include_once('../common.php');
Auth::checkSession();

$smarty = new SmartyEx();
$smarty->display('analysis/index.tpl');