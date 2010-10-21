<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$params['pluginType'] = 2;
	$params['execID'] = $_REQUEST['execID'];
	$params['srcList'] = (isset($_REQUEST['srcList'])) ? $_REQUEST['srcList'] : "";
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
		$stmt->bindParam(1, $params['execID']);
		$stmt->execute();
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
		$params['pluginName'] = $result[0];
		$params['version']    = $result[1];
		$params['executedAt'] = $result[2];
		$params['resPath']    = $result[3] . $DIR_SEPARATOR . $params['execID'] . $DIR_SEPARATOR;
		$params['resPathWeb'] = "../" . $result[4] . $params['execID'] . $DIR_SEPARATOR_WEB;

		//--------------------------------------------------------------------------------------------------------------
		// Retrieve tag data
		//--------------------------------------------------------------------------------------------------------------
		$params['tagArray'] = array();
		
		$stmt = $pdo->prepare("SELECT tag, entered_by FROM executed_plugin_tag WHERE exec_id=? ORDER BY tag_id ASC");
		$stmt->bindValue(1, $params['execID']);
		$stmt->execute();
		$tagNum = $stmt->rowCount();
			
		for($i=0; $i<$tagNum; $i++)
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);
		
			$params['tagArray'][$i] = $result[0];
			if($i == 0) $params['tagEnteredBy'] = $result[1];
		}	
		//--------------------------------------------------------------------------------------------------------------
		
		$templateName = 'plugin_template/show_' . $params['pluginName'] . '_v.' . $params['version'] . '.php';
		include($templateName);

		//echo $dstHtml;
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
