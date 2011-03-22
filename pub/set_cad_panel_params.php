<?php

	$modalityMenuVal = array();
	$cadList = array();	
	$modalityNum = count($modalityList);

	for($i=0; $i<$modalityNum; $i++)
	{
		$tmpStr = "";
		$prevCadName = "";

		$sqlStr = "SELECT DISTINCT el.plugin_name, el.version FROM executed_plugin_list el, cad_series cs"
	            . " WHERE cs.plugin_name=el.plugin_name AND cs.version=el.version AND cs.series_id=1";

		if($modalityList[$i] != 'all')  $sqlStr .= " AND cs.modality=?";
		$sqlStr .= " ORDER BY el.plugin_name ASC, el.version DESC";
		
		$stmt = $pdo->prepare($sqlStr);
		if($modalityList[$i] != 'all')  $stmt->bindParam(1, $modalityList[$i]);
		$stmt->execute();
	
		while($result = $stmt->fetch(PDO::FETCH_NUM))
		{
			if($result[0] != $prevCadName)
			{
				if($prevCadName != "")  $tmpStr .= '/';
				$tmpStr .= $result[0];
				$prevCadName = $result[0];
			}
			$tmpStr .= '^' . $result[1];
		}
		$modalityMenuVal[] = $tmpStr;
	}
	
	$cadMenuStr = explode('/', $modalityMenuVal[0]);

	$cadNum = count($cadMenuStr);
			
	for($i=0; $i<$cadNum; $i++)
	{
		$tmpStr = explode('^', $cadMenuStr[$i]);

		$cadList[$i][0] =  $tmpStr[0];                                     // plug-in (CAD) name
		$cadList[$i][1] =  substr($cadMenuStr[$i], strlen($tmpStr[0])+1);  // version str
	}
?>