<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include("auto_logout_administration.php");	
	require_once('../class/validator.class.php');		
	require_once('../class/PersonalInfoScramble.class.php');
	
	//-----------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//-----------------------------------------------------------------------------------------------------------------
	$dstData = array();
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"jobID" => array(
			"type" => "int", 
			"min" => 1),
		));
		
	if($validator->validate($_POST))
	{
		$dstData = $validator->output;
		$dstData['message'] = "";
	}
	else
	{
		$dstData = $validator->output;
		$dstData['message'] = implode('<br/>', $validator->errors);
	}

	$userID = $_SESSION['userID'];
	//-----------------------------------------------------------------------------------------------------------------			
	
	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		if($dstData['message'] == "")
		{

			//----------------------------------------------------------------------------------------------------
			// Delete the selected CAD job (unprocessed)
			//----------------------------------------------------------------------------------------------------
			$sqlStr  = "SELECT exec_flg, plugin_type FROM plugin_job_list where job_id=?";
			$result = PdoQueryOne($pdo, $sqlStr, $dstData['jobID'], 'ARRAY_NUM');
	
			$execFlg = $result[0];
			$pluginType = $result[1];

			if(!$execFlg)
			{
				switch($pluginType)
				{
					case 1:  $sqlStr = "DELETE FROM job_series_list WHERE job_id=:jobID;";    break;
					case 2:  $sqlStr = "DELETE FROM job_cad_list WHERE job_id=:jobID;";       break;
					case 3:  $sqlStr = "DELETE FROM job_research_list WHERE job_id=:jobID;";  break;
				}
					
				$sqlStr .= "DELETE FROM plugin_job_list WHERE job_id=:jobID;";
				
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(":jobID", $dstData['jobID']);
				$stmt->execute();

				if($stmt->errorCode() != '00000')
				{
					$dsaData['message'] .= '[ERROR] Fail to delete plug-in job (jobID:' . $dstData['jobID'];
					//$errorMessage = $stmt->errorInfo();
					//$dsaData['message'] .= $errorMessage[2] . '<br/>';
				}
			}

			//--------------------------------------------------------------------------------------------------------
			// Create job list
			//--------------------------------------------------------------------------------------------------------
			include('make_job_list.php');
			//--------------------------------------------------------------------------------------------------------
	
			$dstData['jobListHtml'] = "";
		
			foreach($jobList as $item)
			{
				$dstData['jobListHtml'] .= '<tr>'
										.  '<td>' . $item[0] . '</td>'
										.  '<td>' . $item[1] . '</td>'
										.  '<td>' . $item[2] . '</td>'
										.  '<td>' . $item[3] . '</td>'
										.  '<td>' . $item[4] . '</td>'
										.  '<td>' . $item[5] . '</td>'
										.  '<td>' . $item[6] . '</td>'
										.  '<td>' . $item[7] . '</td>';
										//.  '<td>'
										//. '<input type="button" class="form-btn" value="detail"'
										//.  ' onClick="ShowJobDetail(' . $item[0] . ');" />'
										//.  '</td>';
	
				if($item[8] == 't')
				{
					$dstData['jobListHtml'] .= '<td>Processing</td>';
				}
				else if($_SESSION['serverOperationFlg'] == 1 || $_SESSION['serverSettingsFlg'] == 1 || $userID == $item[2])
				{
					$dstData['jobListHtml'] .= '<td>'
										    .  '<input type="button" class="form-btn" value="delete"'
											.  ' onClick="DeleteJob(' . $item[0] . ');">'
											.  '</td>';
				}
				else
				{
					$dstData['jobListHtml'] .= '<td>&nbsp;</td>';
				}
				
				$dstData['jobListHtml'] .= '</tr>';
			
			}
		}
		echo json_encode($dstData);
	
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
