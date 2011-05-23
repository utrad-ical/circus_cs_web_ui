<?php

/**
 * Auth is the static class that handles user authentications and
 * sessions.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Auth
{
	private static $currentUser;

	/**
	 * Name of 'cadExec' privilege.
	 * @var string
	 */
	const CAD_EXEC = 'cadExec';

	/**
	 * Name of 'personalFeedback' privilege.
	 * @var string
	 */
	const PERSONAL_FEEDBACK = 'personalFeedback';

	/**
	 * Name of 'consensualFeedback' privilege.
	 * @var string
	 */
	const CONSENSUAL_FEEDBACK = 'consensualFeedback';

	/**
	 * Name of 'consensualFeedback' privilege.
	 * @var string
	 */
	const VIEW_ALL_STATISTICS = 'viewAllStatistics';

	/**
	 * Name of 'consensualFeedback' privilege.
	 * @var string
	 */
	const VOLUME_DOWNLOAD = 'volumeDownload';

	/**
	 * Name of 'consensualFeedback' privilege.
	 * @var string
	 */
	const SERVER_SETTINGS = 'serverSettings';

	/**
	 * Name of 'rawNameShow' privilege.
	 * @var string
	 */
	const RAW_NAME_SHOW = 'rawNameShow';

	/**
	 * Name of 'researchExec' privilege.
	 * @var string
	 */
	const RESEARCH_EXEC = 'researchExec';

	/**
	 * Name of 'researchShow' privilege.
	 * @var string
	 */
	const RESEARCH_SHOW = 'researchShow';

	/**
	 * Name of 'modifyConsensual' privilege.
	 * @var string
	 */
	const MODIFY_CONSENSUAL = 'modifyConsensual';

	/**
	 * Name of 'dateDelete' privilege.
	 * @var string
	 */
	const DATA_DELETE = 'dataDelete';

	/**
	 * Name of 'serverOperation' privilege.
	 * @var string
	 */
	const SERVER_OPERATION = 'serverOperation';

	/**
	 * Private (thus unchangable) data that holds the privileges information.
	 * @var array
	 */
	private static $privs = array(
		array (
		),
		array (
			self::PERSONAL_FEEDBACK,
			'Can register personal feedback.'
		),
		array (
			self::CONSENSUAL_FEEDBACK,
			'Can register consensual feedback.'
		),
		array (
			self::MODIFY_CONSENSUAL,
			'(Not implemented) Unregister the consensual feedback.'
		),
		array (
			self::VIEW_ALL_STATISTICS,
			'Can view statistics for all users.'
		),
		array (
			self::VOLUME_DOWNLOAD,
			'Can download any volume data from the series detail page.'
		),
		array (
			self::SERVER_SETTINGS,
			'Can modify all of the server settings.'
		),
		array (
			self::SERVER_OPERATION,
			'Can do some of the server administration task.',
			self::SERVER_SETTINGS // upper level
		),
		array (
			self::RAW_NAME_SHOW,
			'Can skip anonymization and show real names from DICOM images.'
		),
		array (
			self::RESEARCH_EXEC,
			'Can execute any research plugin.'
		),
		array (
			self::RESEARCH_SHOW,
			'Can view the results of any research plugin.',
			self::RESEARCH_EXEC // upper level
		),
		array (
			self::DATA_DELETE,
			'(Not implemented) Can delete series/patient/study data.'
		)
	);

	/**
	 * Returns the list of availabe privilege types.
	 */
	public static function getPrivilegeTypes()
	{
		return $self::privs;
	}

	/**
	 * Returns the instance of User currently logged-in.
	 * @return User The User instance.
	 */
	public static function currentUser()
	{
		return self::$currentUser;
	}

	/**
	 * Returns the instance of Group which the current user belongs to.
	 * @return Group The Group instance.
	 */
	public static function currentGroup()
	{
		if ($u = self::currentUser())
			return $u->Group;
		return null;
	}

	/**
	 * Login to the CIRCUS CS.
	 * If login succeeds, creates a new session ID and prepares the session
	 * variables, and return true.
	 * If login fails, return false.
	 * @param string $id
	 * @param string $passwd The MD5 hash of the password.
	 * @return bool True if the authentication succeeds, false otherwise.
	 */
	public static function login($id, $passwd)
	{
		global $LOGIN_LOG, $CIRCUS_CS_VERSION, $SESSION_TIME_LIMIT;
		$user = new User($id);
		if (!$user || !$user->passcode)
		{
			self::log_failure();
			return false;
		}
		if (md5($passwd) == $user->passcode)
		{
			// login succeed
			$loginDateTime = date("Y-m-d H:i:s");
			$_SESSION['circusVersion'] = $CIRCUS_CS_VERSION;
			$_SESSION['userID']        = $user->user_id;
			$_SESSION['userName']      = $user->user_name;
			$_SESSION['key']           = sha1($user->user_id);
			$_SESSION['lastLogin']     = $user->last_login_dt;
			$_SESSION['lastIPAddr']    = $user->ip_address;
			$_SESSION['nowIPAddr']     = getenv("REMOTE_ADDR");
			$_SESSION['groupID']       = $user->group_id;
			$_SESSION['todayDisp']     = $user->today_disp;
			$_SESSION['darkroomFlg']   = ($user->darkroom == 't') ? 1 : 0;
			$_SESSION['anonymizeFlg']  = ($user->anonymized == 't') ? 1 : 0;
			$_SESSION['showMissed']    = $user->show_missed;

			$pdo = DBConnector::getConnection();
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
			$_SESSION['anonymizeGroupFlg']   = ($result['anonymized'] == 't') ? 1 : 0;

			if($_SESSION['anonymizeGroupFlg'] == 1)  $_SESSION['anonymizeFlg'] = 1;

			$_SESSION['adminModeFlg'] = 0;
			$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;

			self::log(
				sprintf("Login: userID=%s", $_SESSION['userID']),
				$LOGIN_LOG
			);
			var_dump($_SESSION);
			return true;
		} else {
			// login failed
			self::log_failure();
			return false;
		}
	}

	public static function manualLogout()
	{
		global $LOGIN_LOG;
		self::log(
			sprintf('Logout: userID=%s', $_SESSION['userID']),
			$LOGIN_LOG
		);
		self::logout();
	}

	public static function logout()
	{
		self::$currentUser = null;
		session_destroy();
	}

	/**
	 * Starts session, checks for session timeout, and redirects the user to
	 * the login screen if timeout occurs.
	 * Otherwise, extend the auto logout time.
	 * @param bool $redirect If false, only extends the session without timeout.
	 */
	public static function checkSession($redirect = true)
	{
		global $SESSION_TIME_LIMIT, $LOGIN_LOG;
		session_cache_limiter('nocache');
		session_start();

		self::$currentUser = new User($_SESSION['userID']);
		$userID = self::$currentUser->user_id;

		if($redirect && time() > $_SESSION['timeLimit'])
		{
			self::log(sprintf("Auto logout: userID=%s", $userID), $LOGIN_LOG);
			self::purge('timeout');
		}
		else
		{
			$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
		}
	}

	/**
	 * Immediately forces logout and redirects the user to the login screen, and
	 * exit from PHP execution.
	 * Call this function before printing anything to the browser.
	 */
	public static function purge($mode = null)
	{
		global $params;
		$toTopDir = (isset($params['toTopDir'])) ? $params['toTopDir'] : '';
		Auth::logout();
		$mode_str = $mode ? "?mode=$mode" : '';
		header('location: ' . $toTopDir . 'index.php' . $mode_str);
		exit();
	}

	/**
	 * Guarantee that the current user has the specified group privilege.
	 * Otherwise redirect to the login page.
	 * @param string $priv_name The group privilege type.
	 */
	public static function purgeUnlessGranted($priv_name)
	{
		$group = self::currentGroup();
		if (!is_subclass_of($group, 'Group'))
			Auth::purge();
		if (!$group->hasPrivilege($priv_name))
			Auth::purge();
	}

	private static function log($str, $logfile)
	{
		global $LOG_DIR, $DIR_SEPARATOR;
		$datetime = date('Y-m-d H:i:s');
		$ip = getenv('REMOTE_ADDR');
		$fp = fopen($LOG_DIR . $DIR_SEPARATOR . $logfile, "a");
		fwrite($fp, "[$datetime] " . $str . " (Accessed from $ip)\n");
		fclose($fp);
	}

	private static function log_failure()
	{
		global $LOGIN_ERROR_LOG;
		self::log(
			sprintf(
				"Login error: userID=%s, password=%s",
				$_POST['userID'], MD5($_POST['pswd'])
			),
			$LOGIN_ERROR_LOG
		);
	}
}

?>