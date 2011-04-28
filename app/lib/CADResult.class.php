<?php

/**
 * CADResult represents result set for one CAD process.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CADResult
{
	private $_jobID;
	private $_cadResult;
	private $_attributes;
	private $_seriesUID;
	private $_studyUID;
	private $_cadName;
	private $_cadVersion;
	private $_displayPresenter;
	private $_feedbackListener;

	public function __construct($jobID = null)
	{
		if ($jobID)
		{
			$this->load($jobID);
		}

	}

	public function jobID()
	{
		return $this->_jobID;
	}

	/**
	 * Load CAD results from the given job ID.
	 * @param unknown_type $jobID
	 */
	protected function load($jobID)
	{
		$pdo = DBConnector::getConnection();
		//
		// STEP 1: Get the plugin information which was executed in this job
		//
		$sqlStr =
			"SELECT el.plugin_id, pm.plugin_name, pm.version," .
			" sr.study_instance_uid, sr.series_instance_uid," .
			" el.plugin_type, el.executed_at" .
			" FROM executed_plugin_list el, executed_series_list es," .
			" plugin_master pm, series_list sr" .
			" WHERE el.job_id=? AND es.job_id=el.job_id AND es.series_id=0" .
			" AND pm.plugin_id=el.plugin_id AND sr.sid=es.series_sid";
		$r = DBConnector::query($sqlStr, $jobID, 'ARRAY_ASSOC');
		$pid = $r['plugin_id'];
		$this->_seriesUID  = $r['series_instance_uid'];
		$this->_studyUID   = $r['study_instance_uid'];
		$this->_cadName    = $r['plugin_name'];
		$this->_cadVersion = $r['version'];

		//
		// STEP 2: Get the table name which actually holds the result data
		//
		$sqlStr = "SELECT * FROM plugin_cad_master WHERE plugin_id = ?";
		$cad_master = DBConnector::query($sqlStr, $pid, 'ARRAY_ASSOC');
		$result_table = $cad_master['result_table'];

		//
		// STEP 3: Get the actual CAD results from the result table
		//
		$sqlStr = "SELECT * FROM $result_table WHERE job_id=?";
		$this->_cadResult = DBConnector::query($sqlStr, $jobID, 'ALL_ASSOC');

		//
		// STEP 4: Get the executed pluguin attributes
		//
		$sqlStr =
			"SELECT key, value FROM executed_plugin_attributes " .
			"WHERE job_id = ?";
		$r = DBConnector::query($sqlStr, $jobID, 'ALL_ASSOC');
		if (is_array($r)) foreach ($r as $v) $a[$v['key']] = $v['value'];
		$this->_attributes = $a;

		// print "<pre>"; print_r($this); print "</pre>";
	}

	/**
	 * Retrieves the list feedback data associated with this CAD Result.
	 * @param string $feedbackMode 'personal', 'consensual', or 'all'
	 * @return array Array of Feedback objects
	 */
	public function getFeedback($feedbackMode = 'personal')
	{
		// TODO: Replace SQL
		$dummy = new Feedback();
		$arr = array(0,0,0,1,1,1,-1,-1,-1);
		shuffle($arr);
		$dummy->blockFeedback = $arr;
		return $dummy;
	}

	/**
	 * Returns the feedback visiblity/availability
	 * associated with this CAD Result.
	 * This is based on the user group settings and the feedback policy.
	 *
	 * @param string $feedbackMode 'consensual' or 'personal'
	 * @return string 'normal', 'disabled', 'locked', or 'hidden'.
	 * The 'normal' status means the login user can input or see his feedback.
	 * The 'disabled' status means the user can inspect the feedback
	 * result, but you cannot enter or modify it. (But he may go back
	 * to 'normal' status for personal feedback by unregistering)
	 * The 'locked' status applies only for consensual feedback and
	 * means that the user cannot enter the consensual mode.
	 * The 'hidden' status means the feedback information is completely hidden
	 * (typically for guest users).
	 */
	public function feedbackAvailability($feedbackMode = 'personal')
	{
		// TODO: implemente the feedbackAvailability

		// The availability is 'hidden' when the user has such privilege
		if (false) {
			return 'hidden';
		}

		// The availability is 'locked' when the user has not yet entered
		// his personal feedback.
		if ($feedbackMode == 'consensual' && false) {
			return 'locked';
		}

		// The availability is 'disabled' when:
		// (1) The user has no privileges to give personal/consensual feedback
		//     at all.
		// (2) The user has already entered the feedback.
		// (3) Consensual feedback is already registered by someone.
		if (false) {
			return 'disabled';
		}
		return 'normal';
	}

	/**
	 * Returns whether the user can unregister the feedback.
	 * @param string $feedbackMode 'personal' or 'consensual'.
	 * Please note there is no plant to unregister consensual feedback,
	 * so this method always return false for consensual feedback.
	 * @return bool $feedbackMode True if the user can unregister this
	 * CAD result.
	 */
	public function feedbackUnregisterable($feedbackMode = 'personal')
	{
		if ($feedbackMode != 'personal')
			return false;
		return false; // TODO: implement feedbackUnregisterable
	}

	/**
	 * Returns the CAD result visibility for the user currently logged in.
	 * @return bool True if the user can view this CAD result.
	 */
	public function checkCADResultAvailability()
	{
		// TODO: implement checkCADResultAavailability
		return true;
	}

	/**
	 * Retrieves the list of displays (such as lesion candidates).
	 * @return array Array of CAD dispalys
	 */
	public function getDisplays()
	{
		return $this->displayPresenter()->extractDisplays($this->_cadResult);
	}

	/**
	 * Retrieves the list of attributes associated with this CAD Result.
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}

	/**
	 * Retrieves the Plugin object which produced this CAD result.
	 * @return Plugin
	 */
	public function getExecutedPlugin()
	{
		return null; // not implemented
	}

	protected function defaultPresentation()
	{
		return array(
			'displayPresenter' => array(
				'type' => 'LesionCADDisplayPresenter'
			),
			'feedbackListener' => array(
				'type' => 'SelectionFeedbackListener'
			)
		);
	}

	protected function loadPresentationConfiguration()
	{
		global $WEB_UI_ROOT;
		if (is_array($this->_presentation))
			return;
		$result = $this->defaultPresentation();
		$plugin_name = $this->_cadName . "_v" . $this->_cadVersion;
		try {
			$json = file_get_contents(
				"$WEB_UI_ROOT/plugin/$plugin_name/presentation.json" );
			$tmp = json_decode($json, true);
			$result = array_merge($result, $tmp);
		} catch (Exception $e) {
			print ($e->getMessage());
		}
		$this->_presentation = $result;
	}

	/**
	 * Returns DisplayPresenter associated with this cad result.
	 * @return DisplayPresenter The DisplayPresenter instance
	 */
	public function displayPresenter()
	{
		if ($this->_displayPresenter)
			return $this->_displayPresenter;
		$this->loadPresentationConfiguration();
		$presenter = new $this->_presentation['displayPresenter']['type'];
		$presenter->setParameter($this->_presentation['displayPresenter']['params']);
		$this->_displayPresenter = $presenter;
		return $presenter;
	}

	/**
	 * Returns FeedbackListener associated with this cad result.
	 * @return FeedbackListener The FeedbackListener instance
	 */
	public function feedbackListener()
	{
		if ($this->_feedbackListener)
			return $this->_feedbackListener;
		$this->loadPresentationConfiguration();
		$listener = new $this->_presentation['feedbackListener']['type'];
		$listener->setParameter($this->_presentation['feedbackListener']['params']);
		$this->_feedbackListener = $listener;
		return $listener;
	}
}

?>