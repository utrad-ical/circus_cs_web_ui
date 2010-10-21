<?php
	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$params['pluginName']  = "";
	$params['version']     = "";
	$params['resDateFrom'] = (isset($_REQUEST['resDateFrom']) && $_REQUEST['resDateFrom'] != "undefined") ? $_REQUEST['resDateFrom'] : "";
	$params['resDateTo']   = (isset($_REQUEST['resDateTo']) && $_REQUEST['resDateTo'] != "undefined") ? $_REQUEST['resDateTo'] : "";
	$params['resTimeTo']   = (isset($_REQUEST['resTimeTo']) && $_REQUEST['resTimeTo'] != "undefined") ? $_REQUEST['resTimeTo'] : "";
	$params['filterTag']   = (isset($_REQUEST['filterTag']) && $_REQUEST['filterTag'] != "undefined") ? $_REQUEST['filterTag'] : "";
	$params['orderCol']    = (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "ID";
	$params['orderMode']   = ($_REQUEST['orderMode'] === "ASC") ? "ASC" : "DESC";
	$params['totalNum']    = (isset($_REQUEST['totalNum']))  ? $_REQUEST['totalNum'] : 0;
	$params['pageNum']     = (isset($_REQUEST['pageNum']))   ? $_REQUEST['pageNum']  : 1;
	$params['showing']     = (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 10;
	$params['startNum']    = 1;
	$params['endNum']      = 10;
	$params['maxPageNum']  = 1;
	$params['pageAddress'] = 'research_list.php?';
				   
	$pluginNameTmp = $_POST['pluginName'];
	
	if($pluginNameTmp != "all" && $pluginNameTmp != "undefined")
	{
		$params['pluginName'] = substr($pluginNameTmp, 0, strpos($pluginNameTmp, " v."));
		$params['version']    = substr($pluginNameTmp, strrpos($pluginNameTmp, " v.")+3);
	}
	//------------------------------------------------------------------------------------------------------------------

	$data = array();

	try
	{	
		$pluginList = array();
		
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//--------------------------------------------------------------------------------------------------------------
		// Create SQL queries 
		//--------------------------------------------------------------------------------------------------------------
		$optionNum = 0;
		$condArr = array();

		$sqlStr = "SELECT exec_id, plugin_name, version, executed_at FROM executed_plugin_list ";
		
		$sqlCond =" WHERE plugin_type=2";

		if($params['resDateFrom'] != "" && $params['resDateTo'] != "" && $params['resDateFrom'] == $params['resDateTo'])
		{
			$sqlCond .= " AND executed_at>=? AND executed_at<=?";
			array_push($condArr, $params['resDateFrom'] . ' 00:00:00');
			array_push($condArr, $params['resDateFrom'] . ' 23:59:59');
			
			$params['pageAddress'] .= 'resDateFrom=' . $params['resDateFrom'] . '&resDateTo=' . $params['resDateTo'];
			$optionNum++;			
		}
		else
		{
			if($params['resDateFrom'] != "")
			{
				$sqlCond .= " AND ?<=executed_at";
				array_push($condArr, $params['resDateFrom'].' 00:00:00');

				$params['pageAddress'] .= 'resDateFrom=' . $params['resDateFrom'];
				$optionNum++;
			}
		
			if($params['resDateTo'] != "")
			{
				$sqlCond .= " AND executed_at<=?";

				if(0<$optionNum)  $params['pageAddress'] .= "&";
				$params['pageAddress'] .= 'resDateTo=' . $params['resDateTo'];

				if($params['resTimeTo'] != "")
				{
					array_push($condArr, $params['resDateTo'] . ' ' . $params['resTimeTo']);
					$params['pageAddress'] .= '&resTimeTo=' . $params['resTimeTo'];
				}
				else
				{
					array_push($condArr, $params['resDateTo'] . ' 23:59:59');
				}
				$optionNum++;				
			}
		}
		
		if($params['pluginName'] != "" && $params['version'] != "")
		{
			$sqlCond .= " AND plugin_name=? AND version=?";
			array_push($condArr, $params['pluginName']);
			array_push($condArr, $params['version']);
			
			if(0<$optionNum)  $params['pageAddress'] .= "&";
			$params['pageAddress'] .= 'pluginName=' . $pluginNameTmp;
			$optionNum++;
		}

		if($params['filterTag'] != "")
		{		
		 	$sqlCond .= " AND exec_id IN (SELECT DISTINCT exec_id FROM executed_plugin_tag WHERE tag~*?)";
			array_push($condArr, $params['filterTag']);

			if(0<$optionNum)  $params['pageAddress'] .= "&";
			$params['pageAddress'] .= 'filterTag=' . $params['filterTag'];

			$optionNum++;
		}
	
		//-------------------------------------------------------------------------------------------------------------
		// count total number
		//-------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM executed_plugin_list" . $sqlCond);
		$stmt->execute($condArr);
		
		$params['totalNum'] = $stmt->fetchColumn();
		$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
		$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
		$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
		//--------------------------------------------------------------------------------------------------------------

		$sqlStr .= $sqlCond . " ORDER BY ";
		
		switch($params['orderCol'])
		{
			case 'Plugin':	$sqlStr .= " plugin_name ".$params['orderMode'].", version ".$params['orderMode'];  break;
			case 'Time':	$sqlStr .= " executed_at ".$params['orderMode'];									  break;
			default:		$sqlStr .= " exec_id ".$params['orderMode'];										  break;
		}

		if(0<$optionNum)  $params['pageAddress'] .= "&";
		$params['pageAddress'] .= 'orderCol=' . $params['orderCol'] . '&orderMode=' .  $params['orderMode']
		                      .  '&showing=' . $params['showing'];
							  
		$_SESSION['listAddress'] = $params['pageAddress'];

		if($params['showing'] != "all")
		{
			$sqlStr .= " LIMIT ? OFFSET ?";
			array_push($condArr, $params['showing']);
			array_push($condArr, $params['showing'] * ($params['pageNum']-1));
		}
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($condArr);
		
		$rowNum = $stmt->rowCount();
		$params['startNum'] = ($rowNum == 0) ? 0 : $params['showing'] * ($params['pageNum']-1) + 1;
		$params['endNum']   = ($rowNum == 0) ? 0 : $params['startNum'] + $rowNum - 1;			
		
		while($result = $stmt->fetch(PDO::FETCH_NUM))
		{
			$colArr = array($result[0], $result[1].' v.'.$result[2], $result[3]);
			array_push($data, $colArr);
		}
		//--------------------------------------------------------------------------------------------------------------
		
		$stmtCad = $pdo->prepare("SELECT DISTINCT plugin_name, version FROM executed_plugin_list WHERE plugin_type=2");
		$stmtCad->execute();
		$pluginList = $stmtCad->fetchAll(PDO::FETCH_NUM);
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		//エラーが発生した場合にエラー表示をする設定
		ini_set( 'display_errors', 1 );

		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params',     $params);
		$smarty->assign('data',       $data);
		$smarty->assign('pluginList', $pluginList);

		$smarty->display('research/research_list.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
	//--------------------------------------------------------------------------------------------------------
?>

