<?php
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::PROCESS_MANAGE);

//------------------------------------------------------------------------------
// Settings for Smarty
//------------------------------------------------------------------------------
$smarty = new SmartyEx();
$smarty->display('administration/show_job_queue.tpl');
//------------------------------------------------------------------------------
