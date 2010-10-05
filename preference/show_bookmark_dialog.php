<?php

	session_start();

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variable
	//--------------------------------------------------------------------------------------------------------
	$address = (isset($_REQUEST['bookmarkAddress'])) ? $_REQUEST['bookmarkAddress'] : "";
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	// Connect to SQL Server
	//--------------------------------------------------------------------------------------------------------
	$dbConn = pg_connect($connStr) or die("A connection error occurred. Please try again later.");
	//--------------------------------------------------------------------------------------------------------	

	$label = "";
	$title = "";
	$comment = "";

	if($address == "")
	{
		die("Error: Fail to retrieve the bookmark address!!");
	}
	else
	{
		$address = str_replace('&'.session_name().'='.session_id(), '', $address);
		$address = str_replace('?'.session_name().'='.session_id().'&', '?', $address);
		$address = str_replace('?'.session_name().'='.session_id(), '', $address);
		
		$paramStr = substr(strrchr($address, "?"), 1);
		$paramArr = explode('&', $paramStr);		
	
		//---------------------------------------------------------------------------------------------------
		// retrieve parameters from $adress (common)
		//---------------------------------------------------------------------------------------------------
		$srcPage = "";
		$encryptedPatientID   = "";
		$encryptedPatientName = "";
		$filterPatientID   = "";
		$filterPatientName = "";
		$studyInstanceUID = "";
		$seriesInstanceUID = "";
		$modality = "";
		$filterSeriesDescription = "";
		$startDate = "";
		$endDate = "";
		$endTime = "";
		$mode      = "";
		$orderMode = "";
		$orderCol  = "";
		$execID = "";
		$cadName = "";
		$version = "";
		$feedbackMode = "";
		$frameFlg = "";
		$totalNum = "";
		$pageNum = "";
		$tpFlg = "";
		$fnFlg = "";
		$topList = "";
		
		for($i=0; $i<count($paramArr); $i++)
		{
			$tmpArr = explode('=', $paramArr[$i]);
			
			switch($tmpArr[0])
			{
				case 'srcPage':                  $srcPage= $tmpArr[1];                   break;
				case 'encryptedPatientID':       $encryptedPatientID = $tmpArr[1];       break;
				case 'encryptedPatientName':     $encryptedPatientName = $tmpArr[1];     break;
				case 'filterPatientID':          $filterPatientID = $tmpArr[1];          break;
				case 'filterPatientName':        $filterPatientName = $tmpArr[1];        break;
				case 'studyInstanceUID':         $studyInstanceUID = $tmpArr[1];         break;
				case 'seriesInstanceUID':        $seriesInstanceUID = $tmpArr[1];        break;
				case 'filterSeriesDescription':  $filterSeriesDescription = $tmpArr[1];  break;
				case 'modality':                 $modality = $tmpArr[1];                 break;
				case 'startDate':                $startDate = $tmpArr[1];                break;
				case 'endDate':                  $endDate = $tmpArr[1];                  break;
				case 'endTime':                  $endTime = $tmpArr[1];                  break;
				case 'mode':                     $mode = $tmpArr[1];                     break;
				case 'orderMode':                $orderMode = $tmpArr[1];                break;
				case 'orderCol':                 $orderCol = $tmpArr[1];                 break;
				case 'execID':                   $execID = $tmpArr[1];                   break;
				case 'cadName':                  $cadName = $tmpArr[1];                  break;
				case 'version':                  $version = $tmpArr[1];                  break;
				case 'feedbackMode':             $feedbackMode = $tmpArr[1];             break;
				case 'frameFlg':                 $frameFlg = $tmpArr[1];                 break;
				case 'totalNum':                 $totalNum = $tmpArr[1];                 break;
				case 'pageNum':                  $pageNum = $tmpArr[1];                  break;
				case 'tpFlg':                    $tpFlg = $tmpArr[1];                    break;
				case 'fnFlg':                    $fnFlg = $tmpArr[1];                    break;
				case 'topList':                  $topList = $tmpArr[1];                  break;
				case 'bottomPage':               $bottomPage = $tmpArr[1];               break;
				case 'clickedSeriesID':          $clickedSeriesID = $tmpArr[1];          break;
			}
		}
		//---------------------------------------------------------------------------------------------------
		
		if(strpos($address, 'show_cad_results.php') !== FALSE)
		{
			//--------------------------------------------------------------------------------------
			// Addrress
			//--------------------------------------------------------------------------------------
			$address = substr($address, 0, strpos($address, "?")+1)
			         . 'cadName=' . $cadName
			         . '&version=' . $version
			         . '&studyInstanceUID=' . $studyInstanceUID
		             . '&seriesInstanceUID=' . $seriesInstanceUID
		             . '&feedbackMode=' . $feedbackMode;
			//--------------------------------------------------------------------------------------

			//--------------------------------------------------------------------------------------
			// Label and comment
			//--------------------------------------------------------------------------------------
			
			$sqlStr = "SELECT pt.patient_id, pt.patient_name, sr.series_number,"
			        . " sr.series_date, sr.series_time, sr.series_description" 
					. " FROM patient_list pt, study_list st, series_list sr" 
					. " WHERE sr.series_instance_uid='" .  $seriesInstanceUID . "'" 
					. " AND sr.study_instance_uid='" .  $studyInstanceUID . "'" 
					. " AND sr.study_instance_uid=st.study_instance_uid" 
					. " AND pt.patient_id=st.patient_id";
	
			$res = pg_query($dbConn, $sqlStr);
			$row = pg_fetch_row($res);
	
			$comment = "1st series: " . $row[3] . ' ' . $row[4] . ' ' . $row[5];

			$title = $cadName . ' v.' . $version . ' (';
			if($_SESSION['anonymizeFlg'] == 0)		$title .= $row[1] . ', '; // patient name

			//$title .= $row[3] . " " . $row[4];
			$title .= $row[3];  // Series date
						
			if($feedbackMode == 'personalFeedback')         $title .= ', personal mode';
			else if($feedbackMode == 'consensualFeedback')  $title .= ', consensual mode';
			
			$title .= ')';

			$sqlStr = "SELECT el.exec_id FROM executed_plugin_list el, executed_cad_series es"
			        . " WHERE el.plugin_name='" .  $cadName . "'"
					. " AND el.version='" . $version . "'"
					. " AND el.exec_id=es.exec_id"	
					. " AND es.series_id=1"
					. " AND es.series_instance_uid='" .  $seriesInstanceUID . "'"
					. " AND es.study_instance_uid='" .  $studyInstanceUID . "'";
					
			$res = pg_query($dbConn, $sqlStr);
			$row = pg_fetch_row($res);	
			$execID = $row[0];

			$sqlStr = "SELECT * FROM executed_cad_series WHERE exec_id=" . $execID
					. " ORDER BY series_id ASC";	
			
			$res = pg_query($dbConn, $sqlStr);
			$seriesNum = pg_num_rows($res);

			if($seriesNum > 1)
			{
				$row = pg_fetch_assoc($res);
			
				for($j=1; $j<$seriesNum; $j++)
				{
					$row = pg_fetch_assoc($res);

					$sqlStr = "SELECT sr.series_number, sr.series_date, sr.series_time, sr.series_description" 
							. " FROM series_list sr" 
							. " WHERE sr.series_instance_uid='" .  $row['series_instance_uid'] . "'" 
							. " AND sr.study_instance_uid='" .  $row['study_instance_uid'] . "'";

					$resSeries = pg_query($dbConn, $sqlStr);
					$rowSeries = pg_fetch_row($resSeries);	

					if($j == 1)        $comment .= ', 2nd series: ';
					else if($j == 2)   $comment .= ', 3rd series: ';
					else               $comment .= ', ' . ($j+1) . 'th series: ';
					
					$comment .= $rowSeries[1] . ' ' . $rowSeries[2] . ' ' . $rowSeries[3];
				}
			}
			
			$label = $title . ', ' . $comment;
		}
		else if(strpos($address, 'false_negative_location.php') !== FALSE)
		{
			//--------------------------------------------------------------------------------------
			// Addrress
			//--------------------------------------------------------------------------------------
			$address = substr($address, 0, strpos($address, "?")+1)
			         . 'execID=' . $execID
			         . '&cadName=' . $cadName
			         . '&version=' . $version
			         . '&studyInstanceUID=' . $studyInstanceUID
		             . '&seriesInstanceUID=' . $seriesInstanceUID
		             . '&feedbackMode=' . $feedbackMode;
			//--------------------------------------------------------------------------------------
			
			$title = 'FN location entry (' . $cadName . ' v.' . $version . ', ';

			if($_SESSION['anonymizeFlg'] == 0)
			{
				$sqlStr = "SELECT pt.patient_id, pt.patient_name"
				        . " FROM patient_list pt, study_list st" 
					    . " WHERE st.study_instance_uid='" .  $studyInstanceUID . "'" 
					    . " AND pt.patient_id=st.patient_id";
	
				$res = pg_query($dbConn, $sqlStr);
				$row = pg_fetch_row($res);
				
				$title .=  $row[1]; // patient name
			}
			
			$comment = '1st series: ';

			$sqlStr = "SELECT * FROM executed_cad_series WHERE exec_id=" . $execID
					. " ORDER BY series_id ASC";	
			
			$res = pg_query($dbConn, $sqlStr);
			$seriesNum = pg_num_rows($res);

			for($j=0; $j<$seriesNum; $j++)
			{
				$row = pg_fetch_assoc($res);

				$sqlStr = "SELECT sr.series_number, sr.series_date, sr.series_time, sr.series_description" 
						. " FROM series_list sr" 
						. " WHERE sr.series_instance_uid='" .  $row['series_instance_uid'] . "'" 
						. " AND sr.study_instance_uid='" .  $row['study_instance_uid'] . "'";
						
				$resSeries = pg_query($dbConn, $sqlStr);
				$rowSeries = pg_fetch_row($resSeries);

				if($j == 0)
				{
					//$title .= $rowSeries[1] . " " . $rowSeries[2];
					$title .= ', ' . $rowSeries[1];

					if($feedbackMode == 'personalFeedback')         $title .= ', personal mode';
					else if($feedbackMode == 'consensualFeedback')  $title .= ', consensual mode';
			
					$title .= ')';
				}
				else if($j == 1)   $comment .= ', 2nd series: ';
				else if($j == 2)   $comment .= ', 3rd series: ';
				else               $comment .= ', ' . ($j+1) . 'th series: ';
				
				$comment .= $rowSeries[1] . ' ' . $rowSeries[2] . ' ' . $rowSeries[3];
			}
			
			$label = $title . ', ' . $comment;
		}
		else if(strpos($address, 'study_list.php') !== FALSE)
		{		
			//---------------------------------------------------------------------------------------------------------
			// title, label and address
			//---------------------------------------------------------------------------------------------------------
			$address = substr($address, 0, strpos($address, "?")+1);
			$title = 'Study list (';
			
			if($srcPage == 'listSearch')
			{
				$address .= 'srcPage=listSearch';
			
				$cnt = 0;

				if($startDate != "")
				{
					$address .= '&startDate=' . $startDate; 
					$title .= "Study date>=" . $startDate; 
					$cnt++;
				}
	
				if($endDate != "")
				{
					if(0<$cnt)	$title .= ", ";
		
					if($endTime != "")
					{
						$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
						$title .= "Study date<=" . $endDate . ' ' . $endTime;
					}
					else
					{
						$address .= "&endDate=" . $endDate;
						if($startDate == $endDate)	$title  = "Study list (Study date=" . $endDate;
						else						$title .= "Study date<=" . $endDate;
					}
					$cnt++;
				}
				else
				{
					if(0<$cnt)	$title .= ", ";

					$endDate = date('Y-m-d');
					$endTime = date('H:i:s');

					$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
					$title   .= "Study date<=" . $endDate . ' ' . $endTime;
					
					$cnt++;
				}
	
				if($filterPatientID != "")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&patientID=" . $filterPatientID;
					$title   .= "Patient ID:" . $filterPatientID;
					$cnt++;
				}

				if($filterPatientName != "")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&patientName=" . $filterPatientName;
					$title   .= "Patient name:" . $filterPatientName;
					$cnt++;
				}

				if($modality != "" && $modality != "All")
				{
					if(0<$cnt)	$label .= ", ";
					$address .= "&modality=" . $modality;
					$label   .= "Modality=" . $modality;
					$cnt++;
				}
				
				$title .= ")";
			}
			else
			{
				$address .= 'encryptedPatientID=' . $encryptedPatientID;
				$title .= 'Patient ID=';
				
				if($_SESSION['anonymizeFlg'] == 0) $title .= PinfoDecrypter($encryptedPatientID, $_SESSION['key']) . ')';
				else                                    $title .= $encryptedPatientID . ')';
			}
			
			$label = $title;
			//---------------------------------------------------------------------------------------------------------
		}
		else if(strpos($address, 'series_list.php') !== FALSE)
		{

			//---------------------------------------------------------------------------------------------------------
			// address and label
			//---------------------------------------------------------------------------------------------------------
			$address = substr($address, 0, strpos($address, "?")+1);
			$title = 'Series list (';
			
			if($srcPage == 'listSearch')
			{
				$address .= 'srcPage=listSearch';
			
				$cnt = 0;

				if($startDate != "")
				{
					$address .= '&startDate=' . $startDate; 
					$title .= "Series date>=" . $startDate; 
					$cnt++;
				}
	
				if($endDate != "")
				{
					if(0<$cnt)	$title .= ", ";
		
					if($endTime != "")
					{
						$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
						$title .= "Series date<=" . $endDate . ' ' . $endTime;
					}
					else
					{
						$address .= "&endDate=" . $endDate;
						if($startDate == $endDate)	$title  = "Series list (Study date=" . $endDate;
						else						$title .= "Series date<=" . $endDate;
					}
					$cnt++;
				}
				else
				{
					if(0<$cnt)	$title .= ", ";

					$endDate = date('Y-m-d');
					$endTime = date('H:i:s');

					$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
					$title   .= "Series date<=" . $endDate . ' ' . $endTime;
					
					$cnt++;
				}
	
				if($filterPatientID != "")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&filterPatientID=" . $filterPatientID;
					$title   .= "Patient ID:" . $filterPatientID;
					$cnt++;
				}

				if($filterPatientName != "")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&filterPatientName=" . $filterPatientName;
					$title   .= "Patient name:" . $filterPatientName;
					$cnt++;
				}

				if($modality != "" && $modality != "All")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&modality=" . $modality;
					$title   .= "Modality=" . $modality;
					$cnt++;
				}
	
				if($filterSeriesDescription != "")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&filterSeriesDescription:" . $filterSeriesDescription;
					$title   .= "Series description=" . $filterSeriesDescription;
					$cnt++;
				}
				
				$title .= ")";
			}
			else
			{
				$address .= 'encryptedPatientID=' . $encryptedPatientID
				         .  '&encryptedPatientName=' . $encryptedPatientName
			             .  '&studyInstanceUID=' . $studyInstanceUID;				
				
				$title .= 'Patient ID=';
				
				if($_SESSION['anonymizeFlg'] == 0) $title .= PinfoDecrypter($encryptedPatientID, $_SESSION['key']) . ', ';
				else                                    $title .= $encryptedPatientID . ', ';

				$sqlStr = "SELECT * FROM study_list WHERE study_instance_uid='" . $studyInstanceUID . "'";
				
				$res = pg_query($dbConn, $sqlStr);
				$row = pg_fetch_assoc($res);
				
				$title .= 'Study ID=' . $row['study_id'] . ', Modality(Study)=' . $row['modality'] . ')';
			}
			
			$label = $title;
			//---------------------------------------------------------------------------------------------------------
		}
		else if(strpos($address, 'series_info.php') !== FALSE)
		{
			//---------------------------------------------------------------------------------------------------------
			// Address
			//---------------------------------------------------------------------------------------------------------
			$address = substr($address, 0, strpos($address, "?")+1) 
			         . 'topList=seriesList&bottomPage=seriesDetail';

			if($encryptedPatientID != "")    $address .= "&encryptedPatientID=" . $encryptedPatientID;
			if($encryptedPatientName != "")  $address .= "&encryptedPatientName=" . $encryptedPatientName;
			if($studyInstanceUID != "")      $address .= "&studyInstanceUID=" . $studyInstanceUID;
			if($seriesInstanceUID != "")     $address .= "&seriesInstanceUID=" . $seriesInstanceUID;
			if($startDate != "")             $address .= '&startDate=' . $startDate; 
	
			if($endDate != "")
			{
				if($endTime != "")
				{
					$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
				}
				else
				{
					$address .= "&endDate=" . $endDate;
				}
			}
			else
			{
				$endDate = date('Y-m-d');
				$endTime = date('H:i:s');
				$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
			}

			if($filterPatientID != "")                 $address .= "&patientID=" . $filterPatientID;
			if($filterPatientName != "")               $address .= "&patientName=" . $filterPatientName;
			if($modality != "" && $modality != "All")  $address .= "&modality=" . $modality;
			if($filterSeriesDescription != "")         $address .= "&filterSeriesDescription:" . $filterSeriesDescription;
			if($orderCol != "")                        $address .= "&orderCol:" . $orderCol;
			if($orderMode != "")                       $address .= "&orderMode:" . $orderMode;
			if($clickedSeriesID != "")                 $address .= "&clickedSeriesID:" . $clickedSeriesID;
			//---------------------------------------------------------------------------------------------------------

			//---------------------------------------------------------------------------------------------------------
			// title and label
			//---------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT st.patient_id, sr.series_number, sr.series_date, sr.series_time, sr.series_description" 
			        . " FROM study_list st, series_list sr" 
					. " WHERE sr.series_instance_uid='" .  $seriesInstanceUID .  "'" 
					. " AND sr.study_instance_uid='" .  $studyInstanceUID . "'"
					. " AND st.study_instance_uid=sr.study_instance_uid";
						
			$res = pg_query($dbConn, $sqlStr);
			$row = pg_fetch_row($res);
						
			$title = 'Series detail (Patient ID:';
		
			if($_SESSION['anonymizeFlg'] == 1)  $title .= PinfoEncrypter($row[0], $_SESSION['key']);
			else                                     $title .= $row[0];
			
			$title .= ', ' . $row[2] . ' ' . $row[3] . ' ' . $row[4] . ')';
		
			$label = $title;
			//---------------------------------------------------------------------------------------------------------
		}
		else if(strpos($address, 'show_cad_log.php') !== FALSE)
		{
			//---------------------------------------------------------------------------------------------------------
			// title, label and address
			//---------------------------------------------------------------------------------------------------------
			$title = 'CAD log (';
			
			if($srcPage == 'listSearch')
			{
				$address = substr($address, 0, strpos($address, "?")+1)
				         . 'srcPage=listSearch';
				$cnt = 0;

				if($startDate != "")
				{
					$address .= '&startDate=' . $startDate; 
					$title .= "Series date>=" . $startDate; 
					$cnt++;
				}
	
				if($endDate != "")
				{
					if(0<$cnt)	$title .= ", ";
		
					if($endTime != "")
					{
						$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
						$title .= "Series date<=" . $endDate . ' ' . $endTime;
					}
					else
					{
						$address .= "&endDate=" . $endDate;
						if($startDate == $endDate)	$title  = "Series list (Study date=" . $endDate;
						else						$title .= "Series date<=" . $endDate;
					}
					$cnt++;
				}
				else
				{
					if(0<$cnt)	$title .= ", ";

					$endDate = date('Y-m-d');
					$endTime = date('H:i:s');

					$address .= "&endDate=" . $endDate . "&endTime=" . $endTime;
					$title   .= "Series date<=" . $endDate . ' ' . $endTime;
					
					$cnt++;
				}
	
				if($filterPatientID != "")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&filterPatientID=" . $filterPatientID;
					$title   .= "Patient ID:" . $filterPatientID;
					$cnt++;
				}

				if($filterPatientName != "")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&filterPatientName=" . $filterPatientName;
					$title   .= "Patient name:" . $filterPatientName;
					$cnt++;
				}

				if($modality != "" && $modality != "All")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&modality=" . $modality;
					$title   .= "Modality=" . $modality;
					$cnt++;
				}
				
				if($cadName != "" && $cadName != "All")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&cadName=" . $cadName;
					$title   .= "CAD name=" . $cadName;
					$cnt++;
				}				
			
				if($version != "" && $version != "All")
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&version=" . $version;
					$title   .= "Version=" . $version;
					$cnt++;
				}
				else if(($cadName != "" && $cadName != "All") && $version == "All")
				{
					$title   .= "Version=All";
				}
						
				if($tpFlg == 1)
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&tpFlg=1";
					$title   .= "include TP";
					$cnt++;
				}
				else
				{
					$address .= "&tpFlg=0";
				}

				if($fnFlg == 1)
				{
					if(0<$cnt)	$title .= ", ";
					$address .= "&fnFlg=1";
					$title   .= "include FN";
					$cnt++;
				}
				else
				{
					$address .= "&fnFlg=0";
				}
				$title .= ")";
			}
			else
			{
				$endDate = date('Y-m-d');
				$endTime = date('H:i:s');
			
				$address .= '?endDate=' . $endDate . '&endTime=' . $endTime;
				$title .= 'Series date<=' . $endDate . ' ' . $endTime . ')';
			}
			
			$label = $title;
			//---------------------------------------------------------------------------------------------------------		
		}
	}

	$dateTime = date('Y-m-d H:i:s');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">

<link rel="stylesheet" type="text/css" href="../js/jquery/css/jquery-ui-1.7.2.custom.css">	
<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/jquery/ui/jquery-ui-1.7.2.custom.js"></script>

<script language="Javascript">
<!--
jQuery(document).ready(function() {
	jQuery('#okButton').click( function() {
			jQuery.get("bookmark_registration.php",
					    { label: jQuery("#bookmarkLabel").val(),
					      title: jQuery("#bookmarkTitle").val(),
					      address: jQuery("#bookmarkAddress").val(),
					      comment: jQuery("#bookmarkComment").val()
				        },
						function(ret){
							alert(ret);
							window.close();
			   			});
	});

	jQuery('#cancelButton').click( function() { window.close(); });
	
	jQuery("#bookmarkTitle").focus(function() {
		if(jQuery("#bookmarkTitle").val()=="(Untitled bookmark on <? echo $dateTime; ?>)")
		   jQuery("#bookmarkTitle").select();
	});
		
	jQuery("#bookmarkComment").focus(function() {
		if(jQuery("#bookmarkComment").val()=="(Add your comment)")   jQuery("#bookmarkComment").val("");
	});
	
	jQuery("#bookmarkTitle").select();
});

-->
</script>

</head>

<body>
<form id="form1">
<input type="hidden" id="bookmarkLabel"   value="<? echo $label; ?>">
<input type="hidden" id="bookmarkAddress" value="<? echo $address; ?>">

<table width=600>
	<tr>
		<td>label:</td>
		<td><? echo $label; ?></td>
	</tr>
	<tr>
		<td>Title:</td>
		<td><input type="text" id="bookmarkTitle" size="75"
		           value="(Untitled bookmark on <? echo $dateTime; ?>)"></td>
	</tr>
	<tr>
		<td>Comment:</td>
		<td><textarea id="bookmarkComment" rows="2" cols="60">(Add your comment)</textarea></td>
	</tr>
	<tr>
	    <td colspan=2 align=right>
			<input type="button" id="okButton" value="OK">
			<input type="button" id="cancelButton" value="Cancel">
		</td>	
	</tr>
	
</table>

</form>



</body>
</html>