<?php
	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("auto_logout_research.php");	
	require_once('../class/validator.class.php');
	
	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		//-----------------------------------------------------------------------------------------------------------------
		// Import $_GET variables and validation
		//-----------------------------------------------------------------------------------------------------------------
		$params = array();
	
		PgValidator::$conn = $pdo;
		$validator = new FormValidator();
		$validator->registerValidator('pgregex', 'PgRegexValidator');
	
		$validator->addRules(array(
			"pluginName" => array(
				"type" => "string",
				"regex" => "/^[\w\s-_\.]+$/",
				"errorMes" => "'Plugin name' is invalid."),
			"resDateFrom" => array(
				"type" => "date",
				"errorMes" => "'Research date' is invalid."),
			"resDateTo" => array(
				"type" => "date",
				"errorMes" => "'Research date' is invalid."),
			"resTimeTo" => array(
				"type" => "time",
				"errorMes" => "'Research time' is invalid."),
			"filterTag"=> array(
				"type" => "pgregex",
				"errorMes" => "'Tag' is invalid."),
			"orderCol" => array(
				"type" => "select",
				"options" => array('Plugin', 'Time', 'ID'),
				"default"=> 'ID',
				"oterwise" => 'ID'),
			"orderMode" => array(
				"type" => "select",
				"options" => array('DESC', 'ASC'),
				"default" => 'DESC',
				"oterwise" => 'DESC'),
			"showing" => array(
				"type" => "select",
				"options" => array('10', '25', '50', 'all'),
				"default" => '10',
				"oterwise" => '10')
			));
		
		if($validator->validate($_GET))
		{
			$params = $validator->output;
			$params['errorMessage'] = "&nbsp;";
			$params['pageNum']  = (ctype_digit($_GET['pageNum']) && $_GET['pageNum'] > 0) ? $_GET['pageNum'] : 1;
			$params['startNum'] = 0;
			$params['endNum'] = 0;
			$params['totalNum'] = 0;
			$params['maxPageNum'] = 1;
			$params['pageAddress'] = 'research_list.php?';
			
			if($params['pluginName'] != "all" && $params['pluginName'] != "undefined")
			{
				$pluginNameTmp = $params['pluginName'];
				$params['pluginName'] = substr($pluginNameTmp, 0, strpos($pluginNameTmp, " v."));
				$params['version']    = substr($pluginNameTmp, strrpos($pluginNameTmp, " v.")+3);
			}
		}
		else
		{
			$params = $validator->output;
			$params['errorMessage'] = implode('<br/>', $validator->errors);
		}
		
		$params['toTopDir'] = "../";		
		//--------------------------------------------------------------------------------------------------------------

		$data = array();

		if($params['errorMessage'] == "&nbsp;")
		{
			$pluginList = array();
		
			//----------------------------------------------------------------------------------------------------------	
			// Create SQL queries 
			//----------------------------------------------------------------------------------------------------------
			$optionNum = 0;
			$condArr = array();

			$sqlStr = "SELECT exec_id, plugin_name, version, executed_at FROM executed_plugin_list ";
		
			$sqlCond =" WHERE plugin_type=2";

			if($params['resDateFrom'] != "" && $params['resDateTo'] != ""
			   && $params['resDateFrom'] == $params['resDateTo'])
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
					$condArr[] = $params['resDateFrom'] . ' 00:00:00';

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
						$condArr[] = $params['resDateTo'] . ' ' . $params['resTimeTo'];
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
				$condArr[] = $params['pluginName'];
				$condArr[] = $params['version'];
			
				if(0<$optionNum)  $params['pageAddress'] .= "&";
				$params['pageAddress'] .= 'pluginName=' . $pluginNameTmp;
				$optionNum++;
			}

			if($params['filterTag'] != "")
			{		
			 	$sqlCond .= " AND exec_id IN (SELECT DISTINCT reference_id FROM tag_list WHERE category=6 AND tag~*?)";
				$condArr[] = $params['filterTag'];	

				if(0<$optionNum)  $params['pageAddress'] .= "&";
				$params['pageAddress'] .= 'filterTag=' . $params['filterTag'];

				$optionNum++;
			}
	
			//---------------------------------------------------------------------------------------------------------
			// count total number
			//---------------------------------------------------------------------------------------------------------
			$stmt = $pdo->prepare("SELECT COUNT(*) FROM executed_plugin_list" . $sqlCond);
			$stmt->execute($condArr);
			
			$params['totalNum'] = $stmt->fetchColumn();
			$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
			$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
			$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
			//----------------------------------------------------------------------------------------------------------

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
				$data[] = $colArr;
			}
			//----------------------------------------------------------------------------------------------------------
		
			$stmtCad = $pdo->prepare("SELECT DISTINCT plugin_name, version FROM executed_plugin_list WHERE plugin_type=2");
			$stmtCad->execute();
			$pluginList = $stmtCad->fetchAll(PDO::FETCH_NUM);
		}
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
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

