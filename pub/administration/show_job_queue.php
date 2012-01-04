<?php
$params = array('toTopDir' => "../");
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

//------------------------------------------------------------------------------
// Settings for Smarty
//------------------------------------------------------------------------------
$smarty = new SmartyEx();

$smarty->assign('params', $params);
//$smarty->assign('userID',  $_SESSION['userID']);

$smarty->display('administration/show_job_queue.tpl');
//------------------------------------------------------------------------------


