<?php
	session_cache_limiter('nocache');
	session_start();

	include('common.php');

	function DispLoginPage($message, $version)
	{
		$smarty = new SmartyEx();

		$smarty->assign('version', $version);
		$smarty->assign('message', $message);
		$smarty->display('login_disp.tpl');
	}

	$message = "";
	$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "";

	if(!isset($_SESSION['userID']))
	{
	    //----------------------------------------------------------------------------------------------------
		// Registration of session
		//----------------------------------------------------------------------------------------------------
		if($mode == 'Login')
		{
			try
			{
				// Connect to SQL Server
				$pdo = DB::getConnection();

				$sqlStr = "SELECT * FROM users WHERE user_id=? AND passcode=?";
				$result = PdoQueryOne($pdo, $sqlStr, array($_POST['userID'], MD5($_POST['pswd'])), 'ARRAY_ASSOC');

    			if($result == null)
				{
					$message = 'Authentication credentials not accepted!!';

					DispLoginPage($message, $CIRCUS_CS_VERSION);

					$fp = fopen($LOG_DIR.$DIR_SEPARATOR.$LOGIN_ERROR_LOG, "a");
					fprintf($fp, "[%s] Login error: userID=%s, password=%s (Accessed from %s)\r\n",
					         date("Y-m-d H:i:s"), $_POST['userID'], MD5($_POST['pswd']), getenv("REMOTE_ADDR"));
					fclose($fp);

					$pdo = null;
				}
				else
				{
					$loginDateTime = date("Y-m-d H:i:s");

					$_SESSION['circusVersion'] = $CIRCUS_CS_VERSION;
					$_SESSION['userID']        = $result['user_id'];
					$_SESSION['userName']      = $result['user_name'];
					$_SESSION['key']           = sha1($result['user_id']);
					$_SESSION['lastLogin']     = $result['last_login_dt'];
					$_SESSION['lastIPAddr']    = $result['ip_address'];
					$_SESSION['nowIPAddr']     = getenv("REMOTE_ADDR");
					$_SESSION['groupID']       = $result['group_id'];
					$_SESSION['todayDisp']     = $result['today_disp'];
					$_SESSION['darkroomFlg']   = ($result['darkroom_flg'] == 't') ? 1 : 0;
					$_SESSION['anonymizeFlg']  = ($result['anonymize_flg'] == 't') ? 1 : 0;
					$_SESSION['latestResults'] = $result['latest_results'];

					$stmt = $pdo->prepare("UPDATE users SET last_login_dt=?, ip_address=? WHERE user_id=?");
					$stmt->execute(array($loginDateTime, $_SESSION['nowIPAddr'], $_SESSION['userID']));

					$stmt = $pdo->prepare("SELECT * FROM groups WHERE group_id=?");
					$stmt->execute(array($_SESSION['groupID']));

					$result = $stmt->fetch(PDO::FETCH_ASSOC);

					$_SESSION['colorSet']            = $result['color_set'];
					$_SESSION['execCADFlg']          = ($result['exec_cad'] == 't') ? 1 : 0;
					$_SESSION['personalFBFlg']       = ($result['personal_feedback'] == 't') ? 1 : 0;
					$_SESSION['consensualFBFlg']     = ($result['consensual_feedback'] == 't') ? 1 : 0;
					$_SESSION['modifyConsensualFlg'] = ($result['modify_consensual'] == 't') ? 1 : 0;
					$_SESSION['allStatFlg']          = ($result['view_all_statistics'] == 't') ? 1 : 0;
					$_SESSION['researchShowFlg']     = ($result['research_show'] == 't') ? 1 : 0;
					$_SESSION['researchExecFlg']     = ($result['research_exec'] == 't') ? 1 : 0;
					$_SESSION['volumeDLFlg']         = ($result['volume_download'] == 't') ? 1 : 0;
					$_SESSION['dataDeleteFlg']       = ($result['data_delete'] == 't') ? 1 : 0;
					$_SESSION['serverOperationFlg']  = ($result['server_operation'] == 't') ? 1 : 0;
					$_SESSION['serverSettingsFlg']   = ($result['server_settings'] == 't') ? 1 : 0;

					if($_SESSION['anonymizeGroupFlg'] == 1)  $_SESSION['anonymizeFlg'] = 1;

					$_SESSION['adminModeFlg'] = 0;
					$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;

					$fp = fopen($LOG_DIR.$DIR_SEPARATOR.$LOGIN_LOG, "a");
					fprintf($fp, "[%s] Login: userID=%s (Accessed from %s)\r\n",
							$loginDateTime, $_SESSION['userID'], $_SESSION['lastIPAddr']);
					fclose($fp);

					$pdo = null;

					header("location:home.php");
				}
			}
			catch (PDOException $e)
			{
    			var_dump($e->getMessage());
			}
		}
		else	DispLoginPage($message, $CIRCUS_CS_VERSION);
	}
	else
	{
		if($mode == 'logout' || $mode == 'timeout')
		{
			$logoutDateTime = date("Y-m-d H:i:s");
			$userID =$_SESSION['userID'];
			$ipAddr = $_SESSION['lastIPAddr'];

			$_SESSION = array();
			session_destroy();

			$fp = fopen($LOG_DIR.$DIR_SEPARATOR.$LOGIN_LOG, "a");

			if($mode == 'timeout')
			{
				$message = 'Login has expired. Please login again.';
				fprintf($fp, "[%s] Auto logout: userID=%s (Accessed from %s)\r\n", $logoutDateTime, $userID, $ipAddr);
			}
			else
			{
				fprintf($fp, "[%s] Logout: userID=%s (Accessed from %s)\r\n", $logoutDateTime, $userID, $ipAddr);
			}
			fclose($fp);

			DispLoginPage($message, $CIRCUS_CS_VERSION);
		}
		else	header("location:home.php");
	}
?>
