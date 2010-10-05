<?php

	$DEFAULT_COL_NUM = 6;
	$DIST_THRESHOLD = 5.0;

	function CheckNearestLesionId($posX, $posY, $posZ, $candStr, $distTh)
	{
		$distTh = $distTh * $distTh;
	
		$candPos = explode('^', $candStr);
		$candNum = count($candPos)/4;
	
		$distMin = 10000;
		$minId = 0;
		
		for($i=0; $i<$candNum; $i++)
		{
			$candX = $candPos[ $i * 4 + 1 ];
			$candY = $candPos[ $i * 4 + 2 ];
			$candZ = $candPos[ $i * 4 + 3 ];
		
			$dist = ($candX-$posX)*($candX-$posX) + ($candY-$posY)*($candY-$posY)
			      + ($candZ-$posZ)*($candZ-$posZ);
				  
			if($dist < $distMin)
			{
				$distMin = $dist;
				$minId = $i * 4;
			}
		}
		
		if($distMin < $distTh)
		{
			$tmpStr = sprintf("%d / %.2f", $candPos[$minId], sqrt($distMin));
			return $tmpStr;
		}
		
		return '- / -';
	}

?>