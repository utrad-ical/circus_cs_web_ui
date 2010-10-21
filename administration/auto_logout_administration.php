<?php
	
	$SESSION_TIME_LIMIT = 1800;
	$toTopDir = (!isset($params['toTopDir'])) ? $params['toTopDir'] : '../';

	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'] || $_SESSION['superUserFlg'] == 0)
	{
		header('location: ' . $toTopDir . 'index.php?mode=timeout');
		exit();
	}
	else
	{
		$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	}
	//-----------------------------------------------------------------------------------------------------------------

?>