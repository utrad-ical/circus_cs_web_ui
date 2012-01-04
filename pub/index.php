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
		case 'unauthorized':
			displayLoginPage('Login as a user with sufficient privilege.');
			break;
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
				$valid_user = Auth::checkAuth($_POST['userID'], md5($_POST['pswd']));
				if($valid_user)
				{
					Auth::createSession($valid_user);
					$_SESSION['topdir'] = dirname($_SERVER['PHP_SELF']);
					header('location: home.php');
					exit;
				} else {
					Auth::log_failure($_POST['userID'], md5($_POST['pswd']));
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

