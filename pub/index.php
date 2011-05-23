<?php
	include('common.php');
	session_cache_limiter('nocache');
	session_start();

	function displayLoginPage($message = '')
	{
		global $CIRCUS_CS_VERSION;
		$smarty = new SmartyEx();
		$smarty->assign('version', $CIRCUS_CS_VERSION);
		$smarty->assign('message', $message);
		$smarty->display('login_disp.tpl');
	}

	$mode = $_REQUEST['mode'];

	switch ($mode)
	{
		case 'timeout':
			displayLoginPage('Login has expired. Please login again.');
			break;
		case 'logout':
			Auth::manualLogout();
			displayLoginPage('Logged out.');
			break;
		case 'Login':
			try
			{
				$success = Auth::login($_POST['userID'], $_POST['pswd']);
				if($success)
				{
					header('location: home.php');
					exit;
				} else {
					$message = 'Authentication credentials not accepted!!';
					displayLoginPage($message);
				}
			}
			catch (PDOException $e)
			{
				$message = $e->getMessage();
				displayLoginPage($message);
			}
			break;
		default:
			displayLoginPage('');
	}
?>
