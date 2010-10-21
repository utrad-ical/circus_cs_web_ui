<?php
	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	

	try
	{
		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables 
		//--------------------------------------------------------------------------------------------------------------
		$params['message'] = '';
		$params['execID']  = $_REQUEST['execID'];
		$params['candID']  = (isset($_REQUEST['candID'])) ? $_REQUEST['candID'] : 1;
		$params['feedbackMode'] = (isset($_REQUEST['feedbackMode'])) ? $_REQUEST['feedbackMode'] : "personal";
		$params['userID']       = (isset($_REQUEST['userID'])) ? $_REQUEST['userID'] : $_SESSION['userID'];
		//--------------------------------------------------------------------------------------------------------------

		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$sqlStr = "SELECT tag_id, tag FROM lesion_candidate_tag WHERE exec_id=? AND candidate_id=?";
		if($params['feedbackMode'] == "consensual")
		{
			$sqlStr .= " AND consensual_flg='t'";
		}
		else
		{
			$sqlStr .= " AND consensual_flg='f' AND entered_by=?";
		}
		$sqlStr .= " ORDER BY tag_id ASC";
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $params['execID']);
		$stmt->bindParam(2, $params['candID']);
		if($params['feedbackMode'] == "personal")  $stmt->bindParam(3, $params['userID']);

		$stmt->execute();

		$tagArray = $stmt->fetchAll(PDO::FETCH_NUM);

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params',   $params);
		$smarty->assign('tagArray', $tagArray);
		
		$smarty->display('cad_results/edit_candidate_tag.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
