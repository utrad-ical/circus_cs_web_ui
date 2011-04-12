<?php
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");

	$cadList = array();

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$sqlStr = "SELECT DISTINCT pm.plugin_name FROM plugin_master pm, plugin_cad_master cm"
				. " WHERE cm.plugin_id=pm.plugin_id AND cm.result_type=1";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute();

		$sqlStr = "SELECT version FROM plugin_master WHERE plugin_name=?";

		$stmtVersion = $pdo->prepare($sqlStr);

		while($result = $stmt->fetch(PDO::FETCH_NUM))
		{
			$stmtVersion->bindParam(1, $result[0]);
			$stmtVersion->execute();

			$tmpStr = "";
			$cnt = 0;

			while($resultVersion = $stmtVersion->fetch(PDO::FETCH_NUM))
			{
				if($cnt > 0) $tmpStr .= '^';
				$tmpStr .= $resultVersion[0];
			}

			array_push($cadList, array($result[0], $tmpStr));
		}

		//--------------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//--------------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//--------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('userID',    $_SESSION['userID']);
		$smarty->assign('cadList',   $cadList);
		$smarty->assign('verDetail', explode('^', $cadList[0][1]));
		$smarty->assign('sortStr',   array("Confidence", "Img. No.", "Volume"));
		$smarty->assign('ticket',    $_SESSION['ticket']);

		$smarty->display('user_preference/cad_preference.tpl');
		//--------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>
