<?php
	session_cache_limiter('nocache');
	session_start();

	include_once("../common.php");
	
	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$pluginNameTmp = $_POST['pluginName'];
	$pluginName = "";
	$version = "";
	
	if($pluginNameTmp != "all" && $pluginNameTmp != "undefined")
	{
		$pluginName    = substr($pluginNameTmp, 0, strpos($pluginNameTmp, " v."));
		$version       = substr($pluginNameTmp, strrpos($pluginNameTmp, " v.")+3);
	}
	
	$resDateFrom = (isset($_POST['resDateFrom']) && $_POST['resDateFrom'] != "undefined") ? $_POST['resDateFrom'] : "";
	$resDateTo   = (isset($_POST['resDateTo']) && $_POST['resDateTo'] != "undefined") ? $_POST['resDateTo'] : "";
	$resTimeTo   = (isset($_POST['resTimeTo']) && $_POST['resTimeTo'] != "undefined") ? $_POST['resTimeTo'] : "";

	$resTag = (isset($_POST['resTag']) && $_POST['resTag'] != "undefined") ? $_POST['resTag'] : "";

	$orderCol    = (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "ID";
	$orderMode   = ($_REQUEST['orderMode'] === 'DESC') ? 'DESC' : 'ASC';
	//$totalNum    = (isset($_REQUEST['totalNum'])) ? $_REQUEST['totalNum'] : 0;
	$pageNum     = (isset($_REQUEST['pageNum'])) ? $_REQUEST['pageNum'] : 1;
	$showing     = (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 10;
	$startNum    = 1;
	$endNum      = 10;
	$maxPageNum  = 1;
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//--------------------------------------------------------------------------------------------------------------
		// Create SQL queries 
		//--------------------------------------------------------------------------------------------------------------
		$condArr = array();

		$sqlStr = "SELECT exec_id, plugin_name, version, executed_at FROM executed_plugin_list ";
		
		$sqlCond =" WHERE plugin_type=2";

		if($resDateFrom != "" && $resDateTo != "" && $resDateFrom == $resDateTo)
		{
			$sqlCond .= " AND executed_at>=? AND executed_at<=?";
			array_push($condArr, $param['cadDateFrom'] . ' 00:00:00');
			array_push($condArr, $param['cadDateFrom'] . ' 23:59:59');
		}
		else
		{
			if($resDateFrom != "")
			{
				$sqlCond .= " AND ?<=executed_at";
				array_push($condArr, $resDateFrom.' 00:00:00');
			}
		
			if($resDateTo != "")
			{
				if($resimeTo != "")
				{
					$sqlCond .= " AND executed_at<=?";
					array_push($condArr, $resDateTo . ' ' . $resTimeTo);
				}
				else
				{
					$sqlCond .= " AND executed_at<=?";
					array_push($condArr, $resDateTo . ' 23:59:59');
				}
			}
		}

		if($pluginName != "" && $version != "")
		{
			$sqlCond .= " AND plugin_name=? AND version=?";
			array_push($condArr, $pluginName);
			array_push($condArr, $version);
		}

		//-------------------------------------------------------------------------------------------------------------
		// count total number
		//-------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM executed_plugin_list" . $sqlCond);
		$stmt->execute($condArr);
		
		$totalNum     = $stmt->fetchColumn();
		$maxPageNum   = ($showing == "all") ? 1 : ceil($totalNum / $showing);
		$startPageNum = max($pageNum - $PAGER_DELTA, 1);
		$endPageNum   = min($pageNum + $PAGER_DELTA, $maxPageNum);		
		//-------------------------------------------------------------------------------------------------------------

		$sqlStr .= $sqlCond . " ORDER BY ";
		
		switch($orderCol)
		{
			case 'Plugin':	$sqlStr .= " plugin_name " .  $orderMode . ", version " . $orderMode;	break;
			case 'Time':	$sqlStr .= " executed_at " . $orderMode;								break;
			default:		$sqlStr .= " exec_id " . $orderMode;									break;
		}

		//echo $sqlStr;

		$dstHtml = array();

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($condArr);
		
		$rowNum = $stmt->rowCount();

		//--------------------------------------------------------------------------------------------------------------
		// Hedder HTML
		//--------------------------------------------------------------------------------------------------------------
		$startNum = ($rowNum == 0) ? 0 : $showing * ($pageNum-1) + 1;
		$endNum   = ($rowNum == 0) ? 0 : $startNum + $rowNum - 1;			
		$dstHtml['headder'] = "Showing" . $startNum . " - " . $endNum . " of " . $rowNum . " results";
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// List thead
		//--------------------------------------------------------------------------------------------------------------
		$dstHtml['thead'] = "<tr>"
						  . "<th>ID</th>"
						  . "<th>Research</th>"
						  . "<th>Research date</th>"
					//	  . "<th>Tag</th>"
						  . "<th>&nbsp;</th>"
						  . "</tr>";
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// List tbody
		//--------------------------------------------------------------------------------------------------------------
		$dstHtml['tbody'] = "";
		
		while($result = $stmt->fetch(PDO::FETCH_NUM))
		{
			$dstHtml['tbody'] .= '<tr>'
			                  .  '<td>' . $result[0] . '</td>'
			                  .  '<td>' . $result[1] . ' v.' . $result[2] . '</td>'
			                  .  '<td>' . $result[3] . '</td>'
			                  //.  '<td>' . $result[4] . '</td>'
							  .  '<td><input name="" type="button" value="show" class="s-btn form-btn"' 
							  .  ' onclick="ShowResearchResult(\'' . $result[0] . '\');" /></td>'
							  .  '</tr>';
		}
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Hooter
		//--------------------------------------------------------------------------------------------------------------
		$dstHtml['footer'] = "";
		
		if($maxPageNum > 1)
		{
			if($pageNum > 1)
			{
				$dstHtml['footer'] .= '<div><a href=""><span style="color: red">&laquo;</span>&nbsp;Previous</a></div>';
			}

			if($startPageNum > 1)
			{
				$dstHtml['footer'] .= '<div><a href="">1</a></div>';
				
				if($startPageNum > 2)  $dstHtml['footer'] .= '<div>...</div>';
			}	

			for($i=$startPageNum; $i<=$endPageNum; $i++)
			{
	    		if($i==$pageNum)
				{
					$dstHtml['footer'] .= '<div><span style="color: red" class="fw-bold">' . $i . '</span></div>';
				}
				else
				{
					$dstHtml['footer'] .= '<div><a href="">' . $i . '</a></div>';
				}
			}

			if($endPageNum < $maxPageNum)
			{
				if($maxPageNum-1 > $endPageNum)
				{
					$dstHtml['footer'] .= "<div>...</div>";
				}
				
				$dstHtml['footer'] .= '<div><a href="">' . $maxPageNum . '</a></div>';
				

				if($pageNum < $maxPageNum)
				{
					$dstHtml['footer'] .= '<div><a href="">Next&nbsp;<span style="color: red">&raquo;</span></a></div>';
				}
			}
		}
		//--------------------------------------------------------------------------------------------------------------

		echo json_encode($dstHtml);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
