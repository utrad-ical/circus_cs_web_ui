<?php

/**
 * CADResult represents result set for one CAD process.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CADResult
{
	/**
	 * The Job ID of this CAD Result. Do not modify this.
	 * @var int
	 */
	public $job_id;

	/**
	 * Retrieves the list feedback data associated with this CAD Result.
	 * @param string $feedbackMode 'personal', 'consensual', or 'all'
	 * @return array Array of Feedback objects
	 */
	public function getFeedback($feedbackMode = 'personal')
	{
		// TODO: Replace SQL
		$dummy = new Feedback();
		$arr = array();
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
		$dummy = array();
		for ($i = 0; $i < 5; $i++)
		{
			$lesion = array(
				'confidence' => sprintf('%.3f', rand(0,1000)/1000),
				'volume' => sprintf('%.3f', rand(200,10000)/1000),
				'slice_location' => floor(rand(2,10)),
				'width' => 400,
				'height' => 300,
				'src' => 'test.jpg',
				'x' => rand(0,200),
				'y' => rand(0,200),
				'z' => 23,
				'id' => $i
			);
			$dummy[] = $lesion;
		}
		return $dummy;
	}

	/**
	 * Retrieves the list of attributes associated with this CAD Result.
	 */
	public function getAttributes()
	{
		$dummy = array(
			'width' => 400,
			'height' => 300,
			'cropX' => rand(0, 100),
			'cropY' => rand(0, 100),
			'cropWidth' => 300,
			'cropHeight' => 200,
			'dispWidth' => 200,
		);
		return $dummy;
	}

	/**
	 * Retrieves the Plugin object which produced this CAD result.
	 * @return Plugin
	 */
	public function getExecutedPlugin()
	{
		return null; // not implemented
	}
}

?>