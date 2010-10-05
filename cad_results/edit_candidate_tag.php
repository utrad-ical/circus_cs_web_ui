<?php
	session_cache_limiter('none');
	session_start();

	include("../common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$param = array('toTopDir'     => '../',
	               'message'      => '',
	               'execID'       => $_REQUEST['execID'],
	               'candID'       => (isset($_REQUEST['candID'])) ? $_REQUEST['candID'] : 1,
				   'feedbackMode' => (isset($_REQUEST['feedbackMode'])) ? $_REQUEST['feedbackMode'] : "personal",
				   'userID'       => (isset($_REQUEST['userID'])) ? $_REQUEST['userID'] : $_SESSION['userID']);
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$sqlStr = "SELECT tag_id, tag FROM lesion_candidate_tag WHERE exec_id=? AND candidate_id=?";
		if($param['feedbackMode'] == "consensual")
		{
			$sqlStr .= " AND consensual_flg='t'";
		}
		else
		{
			$sqlStr .= " AND consensual_flg='f' AND entered_by=?";
		}
		$sqlStr .= " ORDER BY tag_id ASC";
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $param['execID']);
		$stmt->bindParam(2, $param['candID']);
		if($param['feedbackMode'] == "personal")  $stmt->bindParam(3, $param['userID']);

		$stmt->execute();

		$tagArray = $stmt->fetchAll(PDO::FETCH_NUM);

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('param',    $param);
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
