<?php
	session_cache_limiter('nocache');
	session_start();

	include_once("../common.php");
	
	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: ../index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$param = array('toTopDir'   => '../',
	               'pluginType' => 2,
                   'execID'     => $_REQUEST['execID'],
	               'srcList'    => (isset($_REQUEST['srcList'])) ? $_REQUEST['srcList'] : "");
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//echo json_encode($dstData);
		$sqlStr .= "SELECT el.plugin_name, el.version, el.executed_at, sm.path, sm.apache_alias"
				.  " FROM executed_plugin_list el, storage_master sm"
				.  " WHERE el.exec_id=? AND el.storage_id = sm.storage_id";
				
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $param['execID']);
		$stmt->execute();
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
		$param['pluginName'] = $result[0];
		$param['version']    = $result[1];
		$param['executedAt'] = $result[2];
		$param['resPath']    = $result[3] . $DIR_SEPARATOR . $param['execID'] . $DIR_SEPARATOR;
		$param['resPathWeb'] = "../" . $result[4] . $param['execID'] . $DIR_SEPARATOR_WEB;

		//--------------------------------------------------------------------------------------------------------------
		// Retrieve tag data
		//--------------------------------------------------------------------------------------------------------------
		$param['tagArray'] = array();
		
		$stmt = $pdo->prepare("SELECT tag, entered_by FROM executed_plugin_tag WHERE exec_id=? ORDER BY tag_id ASC");
		$stmt->bindValue(1, $param['execID']);
		$stmt->execute();
		$tagNum = $stmt->rowCount();
			
		for($i=0; $i<$tagNum; $i++)
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);
		
			$param['tagArray'][$i] = $result[0];
			if($i == 0) $param['tagEnteredBy'] = $result[1];
		}	
		//--------------------------------------------------------------------------------------------------------------
		
		$templateName = 'plugin_template/show_' . $param['pluginName'] . '_v.' . $param['version'] . '.php';
		include($templateName);

		//echo $dstHtml;
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
