<?

	include('drawRocCurve.php');
	
	$data = array();

	//------------------------------------------------------------------------------------------------------------------
	// Road parameter file
	//------------------------------------------------------------------------------------------------------------------
	$fp = fopen($param['resPath']."calcRocResult_0_param.txt", "r"); 

	$data['caseNum']      = rtrim(fgets($fp));
	$data['tpNum']        = rtrim(fgets($fp));
	$data['fpNum']        = rtrim(fgets($fp));
	$data['underRocArea'] = sprintf("%.3f",rtrim(fgets($fp)));

	fclose($fp);
	//------------------------------------------------------------------------------------------------------------------

	$tmpFname = 'ROC' . $param['execID'] . '_' . microtime(true) . '.png';

	$curveFname    = $APACHE_DOCUMENT_ROOT . $DIR_SEPARATOR . 'CIRCUS-CS' . $DIR_SEPARATOR . 'tmp'
	               . $DIR_SEPARATOR . $tmpFname;
	$curveFnameWeb = '../tmp/' . $tmpFname;

	CreateRocCurve(0, 0, $param['resPath'], $curveFname);

	$param['resPath'] = addslashes($param['resPath']);
	
	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('param',         $param);
	$smarty->assign('data',          $data);
	$smarty->assign('curveFnameWeb', $curveFnameWeb);

	$smarty->display('research/disp_roc_v1.tpl');
	//------------------------------------------------------------------------------------------------------------------

?>