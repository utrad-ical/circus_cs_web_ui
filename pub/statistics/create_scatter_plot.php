<?php

	function CreateScatterPlot($plotData, $section, $ofname,
	                           $knownTpFlg, $missedTpFlg, $subTpFlg, $fpFlg, $pendingFlg)
	{
		//---------------------------------------------------------------------------------------------
		// Set parameters
		//---------------------------------------------------------------------------------------------
		$width = 320;
		$height = 320;
	
		$plotOrgX   = 45;
		$plotOrgY   = 40;
		$plotWidth  = 250;
		$plotHeight = 250;
		
		$minX = 0.0;	$maxX = 1.0;	$stepX = 0.2;
		$minY = 0.0;	$maxY = 1.0;	$stepY = 0.2;
	
		$CROSS_SIZE     = 3;
		$PLUS_SIZE      = 3;
		$CIRCLE_SIZE    = 6;
		$RECTANGLE_SIZE = 2;
		$TRIANGLE_SIZE  = 3;

		$xId = 1;
		$yId = 2;
		$baseFname = '../images/statistics/ps_scatter_plot_base_xy.png';
	
		switch($section)
		{
			case 'XY':
				$xId = 1;
				$yId = 2;
				$baseFname = '../images/statistics/ps_scatter_plot_base_xy.png';
				break;
	
			case 'XZ':
				$xId = 1;
				$yId = 3;
				$baseFname = '../images/statistics/ps_scatter_plot_base_xz.png';
				break;
	
			case 'YZ':
				$xId = 2;
				$yId = 3;
				$baseFname = '../images/statistics/ps_scatter_plot_base_yz.png';
				break;
		}
		//---------------------------------------------------------------------------------------------
		
		//---------------------------------------------------------------------------------------------
		// Draw points
		//---------------------------------------------------------------------------------------------
		$img = @imagecreatefrompng($baseFname);

		// Set colors
		$knownTpColor  = imagecolorallocate($img, 255,   0, 255);  // fuchsia
		$missedTpColor = imagecolorallocate($img,   0,   0, 128);  // navy
		$subTpColor    = imagecolorallocate($img,   0,   0,   0);  // black
		$fpColor       = imagecolorallocate($img,   0, 100,   0);  // darkgreen
		$pendingColor  = imagecolorallocate($img, 255, 165,   0);  // orange	

		$length = count($plotData);
		
		$count = 0;
		
		for($j=0; $j<$length; $j++)
		{
			$posX = $plotData[$j][$xId] * $plotWidth + $plotOrgX;
			$posY = $plotData[$j][$yId] * $plotHeight + $plotOrgY;	
		
			switch($plotData[$j][0])
			{
				case -1:  // FP
					if($fpFlg == 1)
					{
						imagefilledrectangle($img,
											 $posX - $RECTANGLE_SIZE,
											 $posY - $RECTANGLE_SIZE,
											 $posX + $RECTANGLE_SIZE,
											 $posY + $RECTANGLE_SIZE,
											 $fpColor);
						$count++;
					}
					break;
					
				case 1:  // known TP
					if($knownTpFlg == 1)
					{			
						imagefilledellipse($img,
										   $posX,
										   $posY,
										   $CIRCLE_SIZE,
										   $CIRCLE_SIZE,
										   $knownTpColor);
						$count++;
					}
					break;
				
				case 2: // missed TP
					if($missedTpFlg == 1)
					{
						$points = array( $posX,                  $posY - 1.155 * $TRIANGLE_SIZE,
						                 $posX - $TRIANGLE_SIZE, $posY + 0.577 * $TRIANGLE_SIZE,
										 $posX + $TRIANGLE_SIZE, $posY + 0.577 * $TRIANGLE_SIZE);
						imagefilledpolygon ($img, $points , 3, $missedTpColor);
						$count++;
					}
					break;

				case 3: // sub TP
					if($subTpFlg == 1)
					{
						imageline ($img,
								   $posX - $PLUS_SIZE, $posY,
								   $posX + $PLUS_SIZE, $posY,
								   $subTpColor);

						imageline ($img,
								   $posX, $posY - $PLUS_SIZE,
								   $posX, $posY + $PLUS_SIZE,
								   $subTpColor);
						$count++;
					}
					break;
			
				case 0: // pending
					if($pendingFlg == 1)
					{
						imageline ($img,
								   $posX - $CROSS_SIZE, $posY + $CROSS_SIZE,
								   $posX + $CROSS_SIZE, $posY - $CROSS_SIZE,
								   $pendingColor);

						imageline ($img,
								   $posX - $CROSS_SIZE, $posY - $CROSS_SIZE,
								   $posX + $CROSS_SIZE, $posY + $CROSS_SIZE,
								   $pendingColor);
						$count++;
					}
					break;
			}
		}
		
		imagepng($img, $ofname);
		imagedestroy($img);
		//---------------------------------------------------------------------------------------------

		return true;
	}
?>
