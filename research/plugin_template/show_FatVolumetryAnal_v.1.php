<?	
	//------------------------------------------------------------------------------------------------------------------
	// Road parameter file
	//------------------------------------------------------------------------------------------------------------------
	$fp = fopen($param['resPath']."VATstat.txt", "r"); 

	$caseNum    = rtrim(fgets($fp));
	$volumeMean = rtrim(fgets($fp));
	$areaMean   = rtrim(fgets($fp));
	$volumeSD   = rtrim(fgets($fp));
	$areaSD     = rtrim(fgets($fp));
	$spearman   = rtrim(fgets($fp));
	$b          = rtrim(fgets($fp));
	$a          = rtrim(fgets($fp));
	$R2         = rtrim(fgets($fp));
	fclose($fp);
	//------------------------------------------------------------------------------------------------------------------

	$dataFile = $param['resPath'] . "VAT.txt";
	
	$tmpFname = 'VATplot_' . microtime(true) . '.png';

	$plotFname = $APACHE_DOCUMENT_ROOT . $DIR_SEPARATOR . 'CIRCUS-CS' . $DIR_SEPARATOR . 'tmp'
               . $DIR_SEPARATOR . $tmpFname;
	$plotFnameWeb = '../tmp/' . $tmpFname;

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
	
	// 初期設定
	fwrite($pipes[0], "set term png size 360, 360\n");
	fwrite($pipes[0], "set grid\n");
	fwrite($pipes[0], "set border\n");
	fwrite($pipes[0], "unset key\n");
	fwrite($pipes[0], "set size square\n");
	fwrite($pipes[0], "set xtics 2000 font \"Verdana,9\"\n");
	fwrite($pipes[0], "set ytics font \"Verdana,9\"\n");
	fwrite($pipes[0], "set xlabel \"VAT volume [mm3]\"\n");
	fwrite($pipes[0], "set ylabel \"VAT area [mm2]\" 1.5,0.0\n");
	
	// グラフの種類
	fwrite($pipes[0], "plot ".$a."*x+".$b." lw 2 lc rgb \"black\", '" . $dataFile . "' with points 1 13\n");
	fwrite($pipes[0], "set output '" . $plotFname . "'\n");	
	fwrite($pipes[0], "replot\n");	
	fclose($pipes[0]);

	// グラフ出力
	//header("Content-type: image/png");
	//fpassthru($pipes[1]);
	//fclose($pipes[1]);
 
	// エラー出力
	if (!empty($pipes[2]))
	{
		error_log($pipes[2], 0);
	}
	fclose($pipes[2]);
 
	// 終わり
	proc_close($gnuplot);

	$dstHtml .= '<table>'
				 .  '<tr>'
					. '<td width="360" valign=top>'
						.  '<img id="vatPlot" src="' . $plotFnameWeb . '" width="360" height="360" />'
					.  '</td>'
					.  '<td>'
						 .  '<table>'
							 .  '<tr><td class="detail-panel">'
							 .  '<table class="detail-tbl">'
								 .  '<tr>'
									 .  '<th style="width:15em;"><span class="trim01">Number of cases</span></th>'
									 .  '<td>' . $caseNum . '</td>'
								 .  '</tr>'
								 .  '<tr>'
									 .  '<th><span class="trim01">Mean of VAT volume</span></th>'
									 .  '<td>' . sprintf("%.2f",$volumeMean) . ' [mm3]</td>'
								 .  '</tr>'
								 .  '<tr>'
									 .  '<th><span class="trim01">S.D. of VAT volume</span></th>'
									 .  '<td>' . sprintf("%.2f",$volumeSD) . '</td>'
								 .  '</tr>'
									 .  '<th><span class="trim01">Mean of VAT area</span></th>'
									 .  '<td>' . sprintf("%.2f",$areaMean) . ' [mm2]</td>'
								 .  '</tr>'
								 .  '<tr>'
									 .  '<th><span class="trim01">S.D. of VAT area</span></th>'
									 .  '<td>' . sprintf("%.2f",$areaSD) . '</td>'
								 .  '</tr>'
								 .  '<tr>'
									 .  '<th><span class="trim01">Correlation (Spearman)</span></th>'
									 .  '<td>' . sprintf("%.3f", $spearman) . '</span></td>'
								 .  '</tr>'
									 .  '<th><span class="trim01">R^2</span></th>'
									 .  '<td>' . sprintf("%.3f", $R2) . '</span></td>'
								 .  '</tr>'
							 .  '</table>'
							 .  '</td></tr>'
			 			.  '</table>'
					. '</td>'
				. '</tr>'
			. '</table>';
			 
	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('param',   $param);
	$smarty->assign('dstHtml', $dstHtml);

	$smarty->display('research/fat_volumetry_anal_v1.tpl');
	//------------------------------------------------------------------------------------------------------------------
?>