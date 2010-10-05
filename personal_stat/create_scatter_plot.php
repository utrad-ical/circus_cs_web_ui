<?php

	function CreateScatterPlot($plotData, $section, $ofname,
	                           $knownTpFlg, $missedTpFlg, $fpFlg, $pendingFlg)
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
	
		$CROSS_SIZE = 2.5;
		$CIRCLE_SIZE = 2;
		$RECTANGLE_SIZE = 2;
		$TRIANGLE_SIZE = 2.5;
		
		$KNOWN_TP_COLOR  = "fuchsia";
		$MISSED_TP_COLOR = "navy";
		$FP_COLOR        = "darkgreen";
		$PENDING_COLOR   = "orange";
		//---------------------------------------------------------------------------------------------

		//---------------------------------------------------------------------------------------------
		$xId = 1;				$yId = 2;
		$xlabel = 'x';			$ylabel = 'y';
		$xDistLabel = 'left';	$yDistLabel = 'post.';
		$baseFname = '../images/ps_scatter_plot_base_xy.png';
	
		switch($section)
		{
			case 'XY':
				$xId = 1;				$yId = 2;
				$xlabel = 'x';			$ylabel = 'y';	
				$xDistLabel = 'left';	$yDistLabel = 'post.';
				$baseFname = '../images/personal_stat/ps_scatter_plot_base_xy.png';
				break;
	
			case 'XZ':
				$xId = 1;				$yId = 3;
				$xlabel = 'x';			$ylabel = 'z';	
				$xDistLabel = 'left';	$yDistLabel = 'inf.';
				$baseFname = '../images/personal_stat/ps_scatter_plot_base_xz.png';
				break;
	
			case 'YZ':
				$xId = 2;				$yId = 3;
				$xlabel = 'y';			$ylabel = 'z';	
				$xDistLabel = 'post.';	$yDistLabel = 'inf.';
				$baseFname = '../images/personal_stat/ps_scatter_plot_base_yz.png';
				break;
		}
		//---------------------------------------------------------------------------------------------
		$img = new Imagick();
	
		if($img->readImage($baseFname) != TRUE)
		{
			$img->newImage($width, $height, 'white');
			$img->setImageFormat('png');
	
			$mainColor = new ImagickPixel();
			$mainColor->setColor("black");
	
			$subColor = new ImagickPixel();
			$subColor->setColor("#dfdfdf");
	
			$drawBase = new ImagickDraw($width, $height);
			$drawBase->setFillAlpha(0.0);
			$drawBase->setStrokeColor($mainColor);
			$drawBase->setStrokeWidth(2.0);
		
			$drawLabel = new ImagickDraw($width, $height);
			$drawLabel->setStrokeColor($mainColor);
			$drawLabel->setFont('Arial-Bold');
			$drawLabel->setFontSize(16);
			$drawLabel->setFillAlpha(1.0);
			$drawLabel->setStrokeAlpha(0.0);
			$drawLabel->setFillColor($mainColor);
			$drawLabel->setTextAntialias(TRUE);	
	
			$drawBase->rectangle($plotOrgX, $plotOrgY,
							  $plotOrgX+$plotWidth, $plotOrgY+$plotHeight);
	
			$drawBase->line($plotOrgX+$plotWidth-25, 10, $plotOrgX+$plotWidth-5, 10);
			$drawBase->line($plotOrgX+$plotWidth-10, 5, $plotOrgX+$plotWidth-5, 10);
			$drawBase->line($plotOrgX+$plotWidth-10, 15, $plotOrgX+$plotWidth-5, 10);	
						
			$drawBase->line(10, $plotOrgY+$plotHeight-25, 10, $plotOrgY+$plotHeight-5);
			$drawBase->line(15, $plotOrgY+$plotHeight-10, 10, $plotOrgY+$plotHeight-5);
			$drawBase->line(5, $plotOrgY+$plotHeight-10, 10, $plotOrgY+$plotHeight-5);		
	
			$drawLabel->setTextAlignment(imagick::ALIGN_CENTER);
	
			for($x = $minX; $x<=$maxX; $x+=$stepX)
			{
				$tmp = $plotWidth / ($maxX - $minX) * $x + $plotOrgX;
	
				$drawLabel->annotation($tmp, $plotOrgY-7, $x);
		
				if($x > $minX && $x < $maxX)
				{
					$drawBase->setStrokeColor($mainColor);
					$drawBase->setStrokeWidth(2.0);
					$drawBase->line($tmp, $plotOrgY, $tmp, $plotOrgY+5);	
		
					$drawBase->setStrokeColor($subColor);
					$drawBase->setStrokeWidth(1.0);		
					$drawBase->line($tmp, $plotOrgY+6, $tmp, $plotOrgY+$plotHeight-2);
				}
			}
		
			$drawLabel->setTextAlignment(imagick::ALIGN_RIGHT);
		
			for($y = $minY; $y<=$maxY; $y+=$stepY)
			{
				$tmp = $plotHeight / ($maxY - $minY) * $y + $plotOrgY;
	
				$drawLabel->annotation($plotOrgX-2, $tmp+6, $y);
	
				if($y > $minY && $y < $maxY)
				{
					$drawBase->setStrokeColor($mainColor);
					$drawBase->setStrokeWidth(2.0);
					$drawBase->line($plotOrgX, $tmp, $plotOrgX+5, $tmp);
		
					$drawBase->setStrokeColor($subColor);
					$drawBase->setStrokeWidth(1.0);		
					$drawBase->line($plotOrgX+6, $tmp, $plotOrgX+$plotWidth-2, $tmp);
				}
			}
		
			$img->drawImage($drawBase);
			$drawBase->destroy();
	
			$drawLabel->setTextAlignment(imagick::ALIGN_CENTER);	
			$drawLabel->annotation($plotOrgX+$plotWidth/2,15, $xlabel);
	
			$drawLabel->setTextAlignment(imagick::ALIGN_RIGHT);	
			$drawLabel->annotation($plotOrgX+$plotWidth-27, 15, $xDistLabel);
	
			$drawLabel->rotate(-90);
	
			$drawLabel->setTextAlignment(imagick::ALIGN_CENTER);	
			$drawLabel->annotation(-($plotOrgY+$plotHeight/2), 15, $ylabel);	
		
			$drawLabel->setTextAlignment(imagick::ALIGN_LEFT);	
			$drawLabel->annotation(-($plotOrgY+$plotHeight-27), 15, $yDistLabel);
		
			$img->drawImage($drawLabel);
			$drawLabel->destroy();	
		}
		
		//---------------------------------------------------------------------------------------------
	
		//---------------------------------------------------------------------------------------------
		$color = new ImagickPixel();
		$drawPlot = new ImagickDraw($width, $height);
	
		$drawPlot->setFillAlpha(1.0);
		$drawPlot->setStrokeAlpha(0.0);	
		
		$length = count($plotData);
		
		$count = 0;
	
		for($j=0; $j<$length; $j++)
		{
			$posX = $plotData[$j][$xId] * $plotWidth + $plotOrgX;
			$posY = $plotData[$j][$yId] * $plotHeight + $plotOrgY;	
		
			switch($plotData[$j][0])
			{
				case 0:  // FP
					if($fpFlg == 1)
					{
						$color->setColor($FP_COLOR);
						$drawPlot->setFillColor($color);
						$drawPlot->rectangle($posX-$RECTANGLE_SIZE, $posY-$RECTANGLE_SIZE,
						                      $posX+$RECTANGLE_SIZE, $posY+$RECTANGLE_SIZE);
						$count++;
					}
					break;
					
				case 1:  // known TP
					if($knownTpFlg == 1)
					{			
						$color->setColor($KNOWN_TP_COLOR);
						$drawPlot->setFillColor($color);
						$drawPlot->circle($posX, $posY, $posX+$CIRCLE_SIZE, $posY+$CIRCLE_SIZE);
						$count++;
					}
					break;
				
				case 2: // missed TP
					if($missedTpFlg == 1)
					{
						$color->setColor($MISSED_TP_COLOR);
						$drawPlot->setFillColor($color);
						//$drawPlot->circle($posX, $posY, $posX+$CIRCLE_SIZE, $posY+$CIRCLE_SIZE);
						$coordinates = array( array( 'x' => $posX, 'y' => $posY - 1.155 * $TRIANGLE_SIZE),
						                      array( 'x' => $posX - $TRIANGLE_SIZE, 'y' => $posY + 0.577 * $TRIANGLE_SIZE),
											  array( 'x' => $posX + $TRIANGLE_SIZE, 'y' => $posY + 0.577 * $TRIANGLE_SIZE),
											  array( 'x' => $posX, 'y' => $posY - 1.155 * $TRIANGLE_SIZE)); 
						$drawPlot->polygon($coordinates);
						$count++;
					}
					break;
			
				case -1: // pending
					if($pendingFlg == 1)
					{			
						$color->setColor($PENDING_COLOR);
						$drawPlot->setFillColor($color);
						$drawPlot->line($posX-$CROSS_SIZE, $posY+$CROSS_SIZE,
						                $posX+$CROSS_SIZE, $posY-$CROSS_SIZE);
						$drawPlot->line($posX-$CROSS_SIZE, $posY-$CROSS_SIZE,
						                $posX+$CROSS_SIZE, $posY+$CROSS_SIZE);
						$count++;
					}
					break;
			}
		}
	
		if($count > 0)  $img->drawImage($drawPlot);
		$drawPlot->destroy();
	
		//header("Content-type: image/png");
		//header("Cache-control: no-cache");	
		//echo $img;
		
		//$img->setImageDepth(8); // 16bit -> 8bit
		//$img->setImageColorspace(1);
	
		$img->writeImage($ofname);
		$img->destroy();
		
		return true;
	}
?>