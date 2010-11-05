<?php

	if($params['cadName'] == 'Lung-CAD')
	{
		$radioButtonList[0][0][0] = 'knownTP';  	$radioButtonList[0][0][1] =  1;
		$radioButtonList[0][1][0] = 'missedTP'; 	$radioButtonList[0][1][1] =  2;
		$radioButtonList[0][2][0] = 'subTP';		$radioButtonList[0][2][1] =  3;
		$radioButtonList[0][3][0] = 'FP';			$radioButtonList[0][3][1] =  0;
		$radioButtonList[0][4][0] = 'pending';		$radioButtonList[0][4][1] = -1;

		$radioButtonList[1][0][0] = 'TP';			$radioButtonList[1][0][1] =  1;
		$radioButtonList[1][1][0] = 'sub TP';		$radioButtonList[1][1][1] =  3;
		$radioButtonList[1][2][0] = 'FP';			$radioButtonList[1][2][1] =  0;
		$radioButtonList[1][3][0] = 'pending';		$radioButtonList[1][3][1] = -1;
	}
	else
	{
		$radioButtonList[0][0][0] = 'known TP';  													$radioButtonList[0][0][1] =  1;
		$radioButtonList[0][1][0] = 'missed TP'; 													$radioButtonList[0][1][1] =  2;
		$radioButtonList[0][2][0] = '&nbsp;&nbsp;&nbsp;&nbsp;F&nbsp;P&nbsp;&nbsp;&nbsp;&nbsp;';		$radioButtonList[0][2][1] =  0;
		$radioButtonList[0][3][0] = 'pending';														$radioButtonList[0][3][1] = -1;

		$radioButtonList[1][0][0] = 'TP';			$radioButtonList[1][0][1] =  1;
		$radioButtonList[1][1][0] = 'FP';			$radioButtonList[1][1][1] =  0;
		$radioButtonList[1][2][0] = 'pending';		$radioButtonList[1][2][1] = -1;
	}
?>