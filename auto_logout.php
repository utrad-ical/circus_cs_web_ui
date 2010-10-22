<?php
	
	$SESSION_TIME_LIMIT = 3600;
	$toTopDir = (isset($params['toTopDir'])) ? $params['toTopDir'] : './';

	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])
	{
		//echo  $toTopDir . ' ' . time() . ' ' . $_SESSION['timeLimit'];
		header('location: ' . $toTopDir . 'index.php?mode=timeout');
		exit();
	}
	else
	{
		$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
		//echo  $toTopDir . ' ' . time() . ' ' . $_SESSION['timeLimit'];
	}
	//-----------------------------------------------------------------------------------------------------------------

?>