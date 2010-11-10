<?php
	session_cache_limiter('none');
	session_start();

	include("common.php");
	require_once('class/validator.class.php');	

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"category" => array(
			"type" => "int",
			"min" => 1,
			"max" => 5,
			"required" => true,
			"errorMes" => "Cagegory is invalid."),
		"referenceID" => array(
			"type" => "int",
			"min" => 1,
			"required" => true,
			"errorMes" => "Reference ID is invalid."),
		));	

	if($validator->validate($_GET))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}
	$params['toTopDir'] = './';
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		if($params['errorMessage'] == '')
		{
			$pdo = new PDO($connStrPDO);
			$sqlStr = "SELECT sid, tag, entered_by FROM tag_list"
					. " WHERE category=? AND reference_id=? ORDER BY sid ASC";
			$sqlParams = array($params['category'], $params['referenceID']);
			$tagArray = PdoQueryOne($pdo, $sqlStr, $sqlParams, 'ALL_NUM');
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
