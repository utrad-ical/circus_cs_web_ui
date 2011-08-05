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
	 * Name of 'personalFeedbackEnter' privilege.
	 * @var string
	 */
	const PERSONAL_FEEDBACK_ENTER = 'personalFeedbackEnter';

	/**
	 * Name of 'consensualFeedbackEnter' privilege.
	 * @var string
	 */
	const CONSENSUAL_FEEDBACK_ENTER = 'consensualFeedbackEnter';

	/**
	 * Name of 'consensualFeedbackModify' privilege.
	 * @var string
	 */
	const CONSENSUAL_FEEDBACK_MODIFY = 'consensualFeedbackModify';

	/**
	 * Name of 'allStatisticsView' privilege.
	 * @var string
	 */
	const ALL_STATISTICS_VIEW = 'allStatisticsView';

	/**
	 * Name of 'volumeDownload' privilege.
	 * @var string
	 */
	const VOLUME_DOWNLOAD = 'volumeDownload';

	/**
	 * Name of 'serverSettings' privilege.
	 * @var string
	 */
	const SERVER_SETTINGS = 'serverSettings';

	/**
	 * Name of 'serverOperation' privilege.
	 * @var string
	 */
	const SERVER_OPERATION = 'serverOperation';

	/**
	 * Name of 'personalInfoView' privilege.
	 * @var string
	 */
	const PERSONAL_INFO_VIEW = 'personalInfoView';

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
	 * Name of 'dateDelete' privilege.
	 * @var string
	 */
	const DATA_DELETE = 'dataDelete';

	/**
	 * Name of 'menuShow' privilege.
	 * @var unknown_type
	 */
	const MENU_SHOW = 'menuShow';

	/**
	 * Name of 'ApiExec' privilege.
	 * @var unknown_type
	 */
	const API_EXEC = 'apiExec';

	/**
	 * Private (thus unchangable) data that holds the privileges information.
	 * @var array
	 */
	private static $privs = array(
		array (
			self::CAD_EXEC,
			'Can execute CAD plug-in.'
		),
		array (
			self::PERSONAL_FEEDBACK_ENTER,
			'Can register personal feedback.',
			self::CONSENSUAL_FEEDBACK_ENTER // upper level
		),
		array (
			self::CONSENSUAL_FEEDBACK_ENTER,
			'Can register consensual feedback.'
		),
		array (
			self::CONSENSUAL_FEEDBACK_MODIFY,
			'(Not implemented) Unregister the consensual feedback.'
		),
		array (
			self::ALL_STATISTICS_VIEW,
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
			self::PERSONAL_INFO_VIEW,
			'Can view personal information (names, birthdays, etc) without anonymization.'
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
		),
		array (
			self::MENU_SHOW,
			'(Not implemented) Can show navigation menu.'
		),
		array (
			self::API_EXEC,
			'Can execute Web API.'
		)
	);

	/**
	 * Returns the list of availabe privilege types.
	 */
	public static function getPrivilegeTypes()
	{
		return self::$privs;
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
	 * Checks if an enabled user exists for given ID / Password pair.
	 * This method only checks whether the given ID/pass pair is valid,
	 * and the user is not disabled for login.
	 * You will want to Auth::createSession() for the valid user.
	 * @param string $id
	 * @param string $passwd The MD5 hash of the password.
	 * @return User If the authentication succeeds, returns the new User.
	 * If the authentication fails, returns null.
	 */
	public static function checkAuth($id, $password)
	{
		$user = new User($id);
		if (!$user || !$user->passcode)
			return null;
		if (!$user->enabled || $password != $user->passcode)
			return null;
		return $user;
	}


	/**
	 * Creates a session for the newly logged-in user.
	 * Creates a new session ID and prepares the session variables.
	 * @param User $user The User object of the user just logged-in.
	 */
	public static function createSession($user)
	{
		global $CIRCUS_CS_VERSION;
		global $LOGIN_LOG;
		global $SESSION_TIME_LIMIT;

		// login succeed
		$loginDateTime = date("Y-m-d H:i:s");
		$_SESSION['circusVersion'] = $CIRCUS_CS_VERSION;
		$_SESSION['userID']        = $user->user_id;
		$_SESSION['userName']      = $user->user_name;
		$_SESSION['key']           = sha1($user->user_id);
		$_SESSION['lastLogin']     = $user->last_login_dt;
		$_SESSION['lastIPAddr']    = $user->ip_address;
		$_SESSION['nowIPAddr']     = getenv("REMOTE_ADDR");
		$_SESSION['groupID']       = $user->Group[0]->group_id;
		$_SESSION['todayDisp']     = $user->today_disp;
		$_SESSION['darkroomFlg']   = ($user->darkroom == 't') ? 1 : 0;
		$_SESSION['anonymizeFlg']  = ($user->anonymized == 't') ? 1 : 0;
		$_SESSION['showMissed']    = $user->show_missed;

		$user->save(array('User' => array(
			'last_login_dt' => date('Y-m-d h:i:s'),
			'ip_address' => getenv("REMOTE_ADDR"),
		)));

		$priv = array_flip($user->listPrivilege());

		$color_set = $user->hasPrivilege(Auth::PERSONAL_INFO_VIEW) ? 'user' : 'guest';
		if ($user->hasPrivilege(Auth::SERVER_OPERATION)) $color_set = 'admin';

		$_SESSION['colorSet']            = $color_set;

		$_SESSION['execCADFlg']          = isset($priv['cadExec']) ? 1 : 0;
		$_SESSION['personalFBFlg']       = isset($priv['personalFeedbackEnter']) ? 1 : 0;
		$_SESSION['consensualFBFlg']     = isset($priv['consensualFeedbackEnter']) ? 1 : 0;
		$_SESSION['modifyConsensualFlg'] = isset($priv['consensualFeedbackModify']) ? 1 : 0;
		$_SESSION['allStatFlg']          = isset($priv['allStatisticsView']) ? 1 : 0;
		$_SESSION['volumeDLFlg']         = isset($priv['volumeDownload']) ? 1 : 0;
		$_SESSION['serverOperationFlg']  = isset($priv['serverOperation']) ? 1 : 0;
		$_SESSION['serverSettingsFlg']   = isset($priv['serverSettings']) ? 1 : 0;
		$_SESSION['anonymizeGroupFlg']   = isset($priv['personalInfoView']) ? 0 : 1;
		$_SESSION['researchExecFlg']     = isset($priv['researchExec']) ? 1 : 0;
		$_SESSION['researchShowFlg']     = isset($priv['researchShow']) ? 1 : 0;
		$_SESSION['dataDeleteFlg']       = isset($priv['dataDelete']) ? 1 : 0;

		if($_SESSION['anonymizeGroupFlg'] == 1)  $_SESSION['anonymizeFlg'] = 1;

		$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;

		self::log(
			sprintf("Login: userID=%s", $_SESSION['userID']),
			$LOGIN_LOG
		);
		return true;
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
		$user = self::currentUser();
		if (!($user instanceof User))
			Auth::purge();
		if (!$user->hasPrivilege($priv_name))
			Auth::purge('unauthorized');
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

	/**
	 * Outputs the login erro log file.
	 * @param string $user_id Who tried to log-in
	 * @param string $password MD5 hash of the given password
	 */
	public static function log_failure($user_id, $password)
	{
		global $LOGIN_ERROR_LOG;
		self::log(
			sprintf(
				"Login error: userID=%s, password=%s",
				$user_id, $password
			),
			$LOGIN_ERROR_LOG
		);
	}
}

?>