<?php
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");

	$cadList = array();

	try
	{
		// Connect to SQL Server
		$pdo = DB::getConnection();

		$stmt = $pdo->prepare("SELECT DISTINCT cad_name FROM cad_master WHERE result_type=1");
		$stmt->execute();

		$sqlStr = "SELECT DISTINCT version FROM cad_master"
				. " WHERE cad_name=? AND result_type=1";

		$stmtVersion = $pdo->prepare($sqlStr);

		while($result = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$stmtVersion->bindParam(1, $result['cad_name']);
			$stmtVersion->execute();

			$tmpStr = "";
			$cnt = 0;

			while($resultVersion = $stmtVersion->fetch(PDO::FETCH_ASSOC))
			{
				if($cnt > 0) $tmpStr .= '^';
				$tmpStr .= $resultVersion['version'];
			}

			array_push($cadList, array($result['cad_name'], $tmpStr));
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
