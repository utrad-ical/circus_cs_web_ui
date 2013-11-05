<?php

	try
	{
		$cadList = array();

		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$sqlStr = "SELECT DISTINCT pm.plugin_name"
				. " FROM executed_plugin_list el, plugin_master pm, plugin_cad_master cm"
				. " WHERE pm.plugin_id=el.plugin_id"
				. " AND cm.plugin_id=pm.plugin_id"
				. " AND cm.result_type=1"
				. " ORDER BY pm.plugin_name ASC";

		$resultCad = DBConnector::query($sqlStr, null, 'ALL_COLUMN');

		if(count($resultCad) > 0)
		{
			foreach($resultCad as $key => $item)
			{
				$cadList[$key][0] = $item;

				$sqlStr = "SELECT DISTINCT pm.version"
						. " FROM executed_plugin_list el, plugin_master pm"
						. " WHERE pm.plugin_name=? AND el.plugin_id=pm.plugin_id";
				$resultVersion = DBConnector::query($sqlStr, $item, 'ALL_COLUMN');

				$cadList[$key][1] = implode('^', $resultVersion);
			}
		}

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
