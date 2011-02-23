<?php

	try
	{
		$cadList = array();

		// Connect to SQL Server
		$pdo = DB::getConnection();

		$sqlStr = "SELECT DISTINCT el.plugin_name FROM executed_plugin_list el, cad_master cm"
				. " WHERE el.plugin_name=cm.cad_name AND el.version=cm.version AND cm.result_type=1"
				. " ORDER BY el.plugin_name ASC";

		$resultCad = PdoQueryOne($pdo, $sqlStr, null, 'ALL_COLUMN');

		if(count($resultCad) > 0)
		{
			foreach($resultCad as $key => $item)
			{
				$cadList[$key][0] = $item;

				$sqlStr  = "SELECT DISTINCT version FROM executed_plugin_list WHERE plugin_name=?";
				$resultVersion = PdoQueryOne($pdo, $sqlStr, $item, 'ALL_COLUMN');

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
