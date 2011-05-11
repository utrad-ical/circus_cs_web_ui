<?php

/**
 * CadResult represents result set for one CAD process.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadResult extends Model
{
	/* Model Definitions */
	protected static $_table = 'executed_plugin_list';
	protected static $_primaryKey = 'job_id';
	protected static $_belongsTo = array(
		'Plugin' => array('key' => 'plugin_id'),
		'Storage' => array('key' => 'storage_id')
	);
	protected static $_hasMany = array(
		'Feedback' => array('key' => 'job_id'),
		'PluginAttribute' => array('key' => 'job_id')
	);
	protected static $_hasAndBelongsToMany = array(
		'Series' => array(
			'joinTable' => 'executed_series_list',
			'foreignKey' => 'job_id',
			'associationForeignKey' => 'series_sid',
			'foreignPrimaryKey' => 'sid'
		)
	);

	/* Protected Properties */
	protected $attributes;
	protected $displayPresenter;
	protected $feedbackListener;
	protected $blockSorter;
	protected $rawResult;
	protected $presentation;

	/**
	 * Retrieves the list of feedback data associated with this CAD Result.
	 * @param string $kind One of the following. 'personal', the list of
	 * all personal feedback set. 'consensual', the consensual feedback.
	 * 'all', the all feedback set. 'user', ID specified.
	 * @param string $user_id Specifies the user ID when $kind is 'user'
	 * @return array Array of Feedback objects
	 */
	public function queryFeedback($kind = 'all', $user_id = null)
	{
		$feedback_list = $this->Feedback;
		switch ($kind)
		{
			case "personal":
				return array_filter($feedback_list, function($in) {
					return $in->is_consensual == false;
				});
				break;
			case "consensual":
				return array_filter($feedback_list, function($in) {
					return $in->is_consenaual == true;
				});
				break;
			case "all":
				return $feedback_list;
				break;
			case "user":
				return array_filter($feedback_list, function($in) use ($user_id) {
					$bool = $in->is_consensual == false && $in->entered_by == $user_id;
					return $bool;
				});
				break;
		}
		throw new BadMethodCallException();
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
	public function checkCadResultAvailability()
	{
		// TODO: implement checkCadResultAavailability
		return true;
	}

	/**
	 * Retrieves the list of displays (such as lesion candidates).
	 * @return array Array of CAD dispalys
	 */
	public function getDisplays()
	{
		return $this->displayPresenter()->extractDisplays($this->rawResult);
	}

	/**
	 * Returns the raw result of CAD.
	 * @return array Array of raw CAD result from database table
	 */
	public function rawCadResult()
	{
		return $this->rawResult;
	}

	/**
	 * Retrieves the list of attributes associated with this CAD Result.
	 */
	public function getAttributes()
	{
		if (is_array($this->attributes))
			return $this->attributes;
		$tmp = $this->PluginAttribute;
		$result = array();
		foreach ($tmp as $attribute)
		{
			$result[$attribute->key] = $attribute->value;
		}
		$this->attributes = $result;
		return $result;
	}

	/**
	 * Retrieves the Plugin object which produced this CAD result.
	 * @return Plugin
	 */
	public function getExecutedPlugin()
	{
		return null; // not implemented
	}

	public function load($id)
	{
		//
		// STEP: Load using inheriting load method
		//
		parent::load($id);

		//
		// STEP: Get the table name which actually holds the result data
		//
		$pid = $this->Plugin->plugin_id;
		$sqlStr = "SELECT * FROM plugin_cad_master WHERE plugin_id = ?";
		$cad_master = DBConnector::query($sqlStr, $pid, 'ARRAY_ASSOC');
		$result_table = $cad_master['result_table'];

		//
		// STEP: Get the actual CAD results from the result table
		//
		$sqlStr = "SELECT * FROM $result_table WHERE job_id=?";
		$this->rawResult = DBConnector::query($sqlStr, $this->job_id, 'ALL_ASSOC');
	}

	protected function defaultPresentation()
	{
		return array(
			'displayPresenter' => array(
				'type' => 'DisplayPresenter'
			),
			'feedbackListener' => array(
				'type' => 'NullFeedbackListener'
			)
		);
	}

	protected function loadPresentationConfiguration()
	{
		if (is_array($this->presentation))
			return;
		$result = $this->defaultPresentation();
		$str = @file_get_contents(
			$this->pathOfPluginWeb() . '/presentation.json');
		if ($str !== false)
		{
			$tmp = json_decode($str, true);
			if (!is_null($tmp))
				$result = array_merge($result, $tmp);
		}
		$this->presentation = $result;
	}

	/**
	 * Return if block sorting is available in this CAD result, which is
	 * defined in the presentation.json file.
	 * @return mixed Data from presentation.json
	 */
	public function sorter()
	{
		$this->loadPresentationConfiguration();
		return $this->presentation['sorter'];
	}

	/**
	 * Returns DisplayPresenter associated with this cad result.
	 * @return DisplayPresenter The DisplayPresenter instance
	 */
	public function displayPresenter()
	{
		return $this->blockElement('displayPresenter');
	}

	/**
	 * Returns FeedbackListener associated with this cad result.
	 * @return FeedbackListener The FeedbackListener instance
	 */
	public function feedbackListener()
	{
		return $this->blockElement('feedbackListener');
	}

	/**
	 * Singleton builder function to build FeedbackListener and
	 * DisplayPresenter.
	 * @param unknown_type $type
	 */
	protected function blockElement($type)
	{
		if ($this->$type)
			return $this->$type;
		$this->loadPresentationConfiguration();
		$element = new $this->presentation[$type]['type']($this);
		$element->setParameter($this->presentation[$type]['params']);
		$this->$type = $element;
		return $element;
	}

	/**
	 * Returns the plugin web configuration diretory.
	 * This directory contains presentation.json configuration file,
	 * plugin-specific templates, etc.
	 * @return string Plugin web configuration directory.
	 */
	public function pathOfPluginWeb()
	{
		global $WEB_UI_ROOT;
		$plugin_name = $this->Plugin->fullName();
		return "$WEB_UI_ROOT/plugin/$plugin_name";
	}

	/**
	 * Returns the plugin-specific public directory.
	 * This directory contains plugin-specific image files, css files,
	 * javascript files, etc.
	 */
	public function webPathOfPluginPub()
	{
		$plugin_name = $this->Plugin->fillName();
		return "../plugin/$plugin_name";
	}

	/**
	 * Returns CAD result directory web path.
	 * @return string CAD result directory web path.
	 */
	public function webPathOfCadResult()
	{
		global $DIR_SEPARATOR_WEB, $SUBDIR_CAD_RESULT;
		$webPath = $this->Storage->apache_alias;
		$series = $this->Series[0];
		// TODO: This should be replaced when WEB_BASE or something is implemented
		$seriesDirWeb = '../' . $webPath .
			$series->Study->Patient->patient_id . $DIR_SEPARATOR_WEB .
			$series->Study->study_instance_uid . $DIR_SEPARATOR_WEB .
			$series->series_instance_uid;
		$result =  $seriesDirWeb . $DIR_SEPARATOR_WEB .
			$SUBDIR_CAD_RESULT . $DIR_SEPARATOR_WEB . $this->Plugin->fullName();
		return $result;
	}

	/**
	 * Returns CAD result directory path.
	 * @return string CAD result directory path.
	 */
	public function pathOfCadResult()
	{
		global $DIR_SEPARATOR, $SUBDIR_CAD_RESULT;
		$path = $this->Storage->path;
		$series = $this->Series[0];
		$seriesDir = $path . $DIR_SEPARATOR .
			$series->Study->Patient->patient_id . $DIR_SEPARATOR .
			$series->Study->study_instance_uid . $DIR_SEPARATOR .
			$series->series_instance_uid;
		$result =  $seriesDir . $DIR_SEPARATOR .
			$SUBDIR_CAD_RESULT . $DIR_SEPARATOR . $this->Plugin->fullName();
		return $result;
	}
}

?>