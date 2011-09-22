<?php

function CreateRocCurve($pendigType, $curveType, $inputPath, $dstFname)
{
	define('GPLOT', 'C:\\gnuplot\\binary\\gnuplot.exe');

	// initialization of gnuplot
	$dspec = array(	0 => array("pipe", "r"),  // stdin
					1 => array("pipe", "w"),  // stdout
					2 => array("pipe", "w")); // stderr

	$gnuplot = proc_open(GPLOT, $dspec, $pipes);

	if ( !is_resource($gnuplot) )
	{
		print "proc_open error\n";
		exit(1);
	}

	$dataFile = $inputPath;

	if($pendigType == 0)  // 0:pending as FP, 1:pending as TP
	{
		if($curveType == 0)		$dataFile .= "CAD-SummarizerResult_0_roc.txt";
		else					$dataFile .= "CAD-SummarizerResult_0_froc.txt";
	}
	else
	{
		if($curveType == 0)		$dataFile .= "CAD-SummarizerResult_1_roc.txt";
		else					$dataFile .= "CAD-SummarizerResult_1_froc.txt";
	}

	// Intialization
	fwrite($pipes[0], "set term png size 360, 320\n");
	fwrite($pipes[0], "set grid\n");
	fwrite($pipes[0], "set border\n");
	fwrite($pipes[0], "set nokey\n");
	fwrite($pipes[0], "set size square\n");
	
	if($curveType == 0) // ROC
	{
		fwrite($pipes[0], "set xtics 0.2 font \"Verdana,9\"\n");
		fwrite($pipes[0], "set ytics 0.2 font \"Verdana,9\"\n");
		fwrite($pipes[0], "set xlabel \"False positive fraction\"\n");
		fwrite($pipes[0], "set ylabel \"True positive fraction\" 1.5,0.0\n");
	}
	else // FROC
	{
		fwrite($pipes[0], "set xtics font \"Verdana,9\"\n");
		fwrite($pipes[0], "set ytics 20 font \"Verdana,9\"\n");
		fwrite($pipes[0], "set xlabel \"Number of false positives [/case]\"\n");
		fwrite($pipes[0], "set ylabel \"Sensitivity [%]\" 1.5,0.0\n");
	}

	//fwrite($pipes[0], "set style line 1 lt 3 lw 2 pt 0\n");

	// Plot ROC(FROC) curvre
	fwrite($pipes[0], "plot '" . $dataFile . "' with lines lc rgb \"red\" lw 2.5\n");	
	fwrite($pipes[0], "set output '" . $dstFname . "'\n");	
	fwrite($pipes[0], "replot\n");	
	fclose($pipes[0]);

	// Graph output
	//header("Content-type: image/png");
	//fpassthru($pipes[1]);
	//fclose($pipes[1]);

	// Error output
	//if (!empty($pipes[2]))
	//{
	//	error_log($pipes[2], 0);
	//}
	//fclose($pipes[2]);

	// 終わり
	proc_close($gnuplot);
}
?>
