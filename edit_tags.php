<?php
	session_cache_limiter('none');
	session_start();

	include("common.php");
	require_once('class/validator.class.php');	

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables 
	//------------------------------------------------------------------------------------------------------------------
	$request = array('category'     => $_GET['category'],
	                 'referenceID' => $_GET['reference_id']);
	$params = array();
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Validation
	//------------------------------------------------------------------------------------------------------------------
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"category" => array(
			"type" => "int",
			"min" => 1,
			"max" => 5,
			"required" => 1,
			"errorMes" => "Cagegory is invalid."),
			"referenceID" => array(
			"type" => "int",
			"min" => 1,
			"required" => 1,
			"errorMes" => "Reference ID is invalid."),
		));	

	if($validator->validate($request))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
	}
	else
	{
		$params = $request;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}
	$params['toTopDir'] = './';
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		if($params['errorMessage'] == '')
		{
			$sqlStr = "SELECT sid, tag, entered_by FROM tag_list"
					. " WHERE category=? AND reference_id=? ORDER BY sid ASC";
		
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $params['category']);
			$stmt->bindValue(2, $params['referenceID']);
			$stmt->execute();	

			$tagArray = $stmt->fetchAll(PDO::FETCH_NUM);
		}
		else
		{
			$params['errorMessage'] = "[ERROR] URL is invalid.";
		}
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('./smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params',   $params);
		$smarty->assign('tagArray', $tagArray);
		
		$smarty->display('edit_tags.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
