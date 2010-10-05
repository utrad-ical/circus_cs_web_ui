<?php
	session_start();

	include("../common.php");
	
	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$param = array('cadName'             => (isset($_REQUEST['cadName'])) ? $_REQUEST['cadName'] : "",
				   'version'             => (isset($_REQUEST['version'])) ? $_REQUEST['version'] : "",
				   'userID'              =>  $_SESSION['userID'],
				   'preferenceFlg'       => 0,
			       'sortKey'             => "",
				   'sortOrder'           => "",
				   'maxDispNum'          => "",
				   'confidenceTh'        => "",
				   'defaultSortKey'      => "",
				   'defaultSortOrder'    => "",
				   'defaultMaxDispNum'   => "",
				   'defaultConfidenceTh' => "",
				   'message'             => "&nbsp;");

	//--------------------------------------------------------------------------------------------------------
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$stmt = $pdo->prepare("SELECT * FROM cad_master WHERE cad_name=? AND version=?");	
		$stmt->execute(array($param['cadName'], $param['version']));
		
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$param['defaultSortKey']      = $result['default_sort_key'];
		$param['defaultSortOrder']    = ($result['default_sort_order']==true) ? "t" : "f";
		$param['defaultMaxDispNum']   = $result['max_disp_num'];
		$param['defaultConfidenceTh'] = $result['confidence_threshold'];
		
		$stmt = $pdo->prepare("SELECT * FROM cad_preference WHERE cad_name=? AND version=? AND user_id=?");	
		$stmt->execute(array($param['cadName'], $param['version'], $param['userID']));
	
		if($stmt->rowCount() == 1)
		{
			$param['preferenceFlg'] = 1;

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$param['sortKey']      = $result['default_sort_key'];
			$param['sortOrder']    = ($result['default_sort_order']==true) ? "t" : "f";
			$param['maxDispNum']   = ($result['max_disp_num']==0) ? "all" : $result['max_disp_num'];
			$param['confidenceTh'] = $result['confidence_threshold'];
		}
		else
		{
			$param['preferenceFlg'] = 0;
			$param['message']      = 'Default settings.';
			$param['sortKey']      = $param['defaultSortKey'];
			$param['sortOrder']    = $param['defaultSortOrder'];
			$param['maxDispNum']   = $param['defaultMaxDispNum'];
			$param['confidenceTh'] = $param['defaultConfidenceTh'];
		}
		
		echo json_encode($param);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;	

?>


