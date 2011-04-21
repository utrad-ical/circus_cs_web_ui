<?php

	$modalityMenuVal = array();
	$cadList = array();	
	$modalityNum = count($modalityList);

	for($i=0; $i<$modalityNum; $i++)
	{
		$tmpStr = "";
		$prevCadName = "";

		$sqlStr = "SELECT DISTINCT pm.plugin_name, pm.version"
				. " FROM executed_plugin_list el, plugin_master pm, plugin_cad_series cs"
				. " WHERE el.status=?"
				. " AND pm.plugin_id=el.plugin_id AND cs.plugin_id=el.plugin_id"
				. " AND cs.series_id=0";

		if($modalityList[$i] != 'all')  $sqlStr .= " AND cs.modality=?";
		$sqlStr .= " ORDER BY pm.plugin_name ASC, pm.version DESC";
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $PLUGIN_SUCESSED);
		if($modalityList[$i] != 'all')  $stmt->bindParam(2, $modalityList[$i]);
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