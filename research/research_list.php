<?php
	session_cache_limiter('none');
	session_start();

	include("../common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: ../index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$param = array('toTopDir'    => '../',
				   'pluginName'  => "",
	               'version'     => "",
				   'resDateFrom' => (isset($_REQUEST['resDateFrom']) && $_REQUEST['resDateFrom'] != "undefined") ? $_REQUEST['resDateFrom'] : "",
				   'resDateTo'   => (isset($_REQUEST['resDateTo']) && $_REQUEST['resDateTo'] != "undefined") ? $_REQUEST['resDateTo'] : "",
				   'resTimeTo'   => (isset($_REQUEST['resTimeTo']) && $_REQUEST['resTimeTo'] != "undefined") ? $_REQUEST['resTimeTo'] : "",
				   'filterTag'   => (isset($_REQUEST['filterTag']) && $_REQUEST['filterTag'] != "undefined") ? $_REQUEST['filterTag'] : "",
				   'orderCol'    => (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "ID",
				   'orderMode'   => ($_REQUEST['orderMode'] === "ASC") ? "ASC" : "DESC",
				   'totalNum'    => (isset($_REQUEST['totalNum']))  ? $_REQUEST['totalNum'] : 0,
				   'pageNum'     => (isset($_REQUEST['pageNum']))   ? $_REQUEST['pageNum']  : 1,
				   'showing'     => (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 10,
				   'startNum'    => 1,
				   'endNum'      => 10,
				   'maxPageNum'  => 1,
				   'pageAddress' => 'research_list.php?');
				   
	$pluginNameTmp = $_POST['pluginName'];
	
	if($pluginNameTmp != "all" && $pluginNameTmp != "undefined")
	{
		$param['pluginName'] = substr($pluginNameTmp, 0, strpos($pluginNameTmp, " v."));
		$param['version']    = substr($pluginNameTmp, strrpos($pluginNameTmp, " v.")+3);
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

		if($param['resDateFrom'] != "" && $param['resDateTo'] != "" && $param['resDateFrom'] == $param['resDateTo'])
		{
			$sqlCond .= " AND executed_at>=? AND executed_at<=?";
			array_push($condArr, $param['resDateFrom'] . ' 00:00:00');
			array_push($condArr, $param['resDateFrom'] . ' 23:59:59');
			
			$param['pageAddress'] .= 'resDateFrom=' . $param['resDateFrom'] . '&resDateTo=' . $param['resDateTo'];
			$optionNum++;			
		}
		else
		{
			if($param['resDateFrom'] != "")
			{
				$sqlCond .= " AND ?<=executed_at";
				array_push($condArr, $param['resDateFrom'].' 00:00:00');

				$param['pageAddress'] .= 'resDateFrom=' . $param['resDateFrom'];
				$optionNum++;
			}
		
			if($param['resDateTo'] != "")
			{
				$sqlCond .= " AND executed_at<=?";

				if(0<$optionNum)  $param['pageAddress'] .= "&";
				$param['pageAddress'] .= 'resDateTo=' . $param['resDateTo'];

				if($param['resTimeTo'] != "")
				{
					array_push($condArr, $param['resDateTo'] . ' ' . $param['resTimeTo']);
					$param['pageAddress'] .= '&resTimeTo=' . $param['resTimeTo'];
				}
				else
				{
					array_push($condArr, $param['resDateTo'] . ' 23:59:59');
				}
				$optionNum++;				
			}
		}
		
		if($param['pluginName'] != "" && $param['version'] != "")
		{
			$sqlCond .= " AND plugin_name=? AND version=?";
			array_push($condArr, $param['pluginName']);
			array_push($condArr, $param['version']);
			
			if(0<$optionNum)  $param['pageAddress'] .= "&";
			$param['pageAddress'] .= 'pluginName=' . $pluginNameTmp;
			$optionNum++;
		}

		if($param['filterTag'] != "")
		{		
		 	$sqlCond .= " AND exec_id IN (SELECT DISTINCT exec_id FROM executed_plugin_tag WHERE tag~*?)";
			array_push($condArr, $param['filterTag']);

			if(0<$optionNum)  $param['pageAddress'] .= "&";
			$param['pageAddress'] .= 'filterTag=' . $param['filterTag'];

			$optionNum++;
		}
	
		//-------------------------------------------------------------------------------------------------------------
		// count total number
		//-------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM executed_plugin_list" . $sqlCond);
		$stmt->execute($condArr);
		
		$param['totalNum'] = $stmt->fetchColumn();
		$param['maxPageNum'] = ($param['showing'] == "all") ? 1 : ceil($param['totalNum'] / $param['showing']);
		$param['startPageNum'] = max($param['pageNum'] - $PAGER_DELTA, 1);
		$param['endPageNum']   = min($param['pageNum'] + $PAGER_DELTA, $param['maxPageNum']);		
		//--------------------------------------------------------------------------------------------------------------

		$sqlStr .= $sqlCond . " ORDER BY ";
		
		switch($param['orderCol'])
		{
			case 'Plugin':	$sqlStr .= " plugin_name ".$param['orderMode'].", version ".$param['orderMode'];  break;
			case 'Time':	$sqlStr .= " executed_at ".$param['orderMode'];									  break;
			default:		$sqlStr .= " exec_id ".$param['orderMode'];										  break;
		}

		if(0<$optionNum)  $param['pageAddress'] .= "&";
		$param['pageAddress'] .= 'orderCol=' . $param['orderCol'] . '&orderMode=' .  $param['orderMode']
		                      .  '&showing=' . $param['showing'];
							  
		$_SESSION['listAddress'] = $param['pageAddress'];

		if($param['showing'] != "all")
		{
			$sqlStr .= " LIMIT ? OFFSET ?";
			array_push($condArr, $param['showing']);
			array_push($condArr, $param['showing'] * ($param['pageNum']-1));
		}
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($condArr);
		
		$rowNum = $stmt->rowCount();
		$param['startNum'] = ($rowNum == 0) ? 0 : $param['showing'] * ($param['pageNum']-1) + 1;
		$param['endNum']   = ($rowNum == 0) ? 0 : $param['startNum'] + $rowNum - 1;			
		
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
		
		$smarty->assign('param',      $param);
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

