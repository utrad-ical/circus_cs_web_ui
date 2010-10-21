<?php
	session_cache_limiter('none');
	session_start();

	include("common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------	
	$params = array('toTopDir'          => './',
	                'message'           => '',
	                'seriesInstanceUID' => $_REQUEST['seriesInstanceUID']);
	//--------------------------------------------------------------------------------------------------------	

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$sqlStr = "SELECT tag_id, tag FROM series_tag WHERE series_instance_uid=? ORDER BY tag_id ASC";
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(1, $params['seriesInstanceUID']);
		$stmt->execute();

		$tagArray = $stmt->fetchAll(PDO::FETCH_NUM);

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('./smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params',   $params);
		$smarty->assign('tagArray', $tagArray);
		
		$smarty->display('edit_series_tag.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
