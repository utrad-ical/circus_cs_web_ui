<?php

/**
 * UnregisterFeedbackAction is an undocumented internal API action for
 * unregistering existing personal or consensual feedback data.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class UnregisterFeedbackAction extends ApiActionBase
{
	protected static $required_privileges = array();

	protected static $rules = array(
		'jobID' => array('type' => 'int', 'required' => true, 'min' => 1),
		'feedbackMode' => array('type' => 'select', 'required' => true,
			'options' => array('personal', 'consensual')),
		'user' => array('type' => 'str'),
		'dryRun' => array('type' => 'str')
	);

	/**
	 * Check if the target Feedback data can be really unregistered or not.
	 * Exceptions are thrown for errors.
	 * @param int $job_id The job ID
	 * @param bool $is_consensual True if you want to delete consensual feedback.
	 * @param string $user Delete personal FB for this user.
	 * @return Feedback the Feedback object if it can be gracefully unregistered.
	 */
	protected function check($job_id, $is_consensual, $user = null)
	{
		$cr = new CadResult($job_id);
		if (!$cr || !$cr->job_id)
			throw new ApiSystemException('CAD result not found.'); // fetal
		$policy = $cr->PluginResultPolicy;
		if (!$policy)
			throw new ApiSystemException('CAD result policy not found.'); // fetal

		if ($is_consensual)
		{
			// Consensual feedback can be unregistered when
			// you have consensualFeedbackModify privilege.
			$fb = $cr->queryFeedback('consensual');
			$fb = reset($fb);

			if (!$fb)
			{
				throw new ApiOperationException(
					'Consensual feedback is not yet registered.'
				);
			}
			if (!$this->currentUser->hasPrivilege(Auth::CONSENSUAL_FEEDBACK_MODIFY))
			{
				throw new ApiOperationException(
					'You do not have sufficient privilege to unregister consensual feedback.'
				);
			}
		}
		else
		{
			// Personal feedback can be unregistered when all of these are met:
			// (1) Consensual feedback is not registered.
			// (2) PFB freeze time (set by CAD result policy) has not passed.
			// (3) You entered this feedback, or you have serverOperation privilege.
			$target_user = $this->currentUser;
			if ($user_id != null)
			{
				if (!$this->currentUser->hasPrivilege(Auth::SERVER_OPERATION) &&
					$target_user->user_id != $user_id)
				{
					throw new ApiOperationException(
						'You do not have sufficient privilege to unregister ' .
						'personal feedback entered by others.'
					);
				}
				$target_user = new User($user_id);
			}

			$fb = $cr->queryFeedback('personal', $target_user->user_id);
			if (count($fb) == 0)
			{
				throw new ApiOperationException(
					'Target personal feedback entered by ' .
					$target_user->user_id . ' is not found.'
				);
			}
			$fb = reset($fb);
			$cfb = $cr->queryFeedback('consensual');
			if (count($cfb) > 0)
			{
				throw new ApiOperationException(
					'You can not unregister this personal feedback: ' .
					'consensual feedback is already registered.'
				);
			}
			// check for PFB freeze time
			if ($policy->time_to_freeze_personal_fb > 0)
			{
				$limit = $policy->time_to_freeze_personal_fb;
				$reg_time = new DateTime($fb->registered_at);
				$current = new DateTime();
				$elapsed = $current->diff($reg_time)->format('%i'); // minutes
				if ($elapsed >= $limit)
				{
					throw new ApiOperationException(
						'You can not unregister this personal feedback: ' .
						"unregister time limit ($limit min.) has passed."
					);
				}
			}
			else
			{
				throw new ApiOperationException(
					'You can not unregister this personal feedback: ' .
					'this operation is prohibited by CAD result policy.'
				);
			}
		}

		return $fb;
	}

	public function execute($params)
	{
		// Determine the target Feedback data
		$job_id = $params['jobID'];
		$is_consensual = $params['feedbackMode'] == 'consensual';

		$fb = $this->check($job_id, $is_consensual, $params['user'] ?: null);

		if (!!($params['dryRun'])) {
			// You can unregister this FB now. But not for now.
			return true;
		}

		// Now we actually unregister the feedback data. This part is simple.
		$fb->unregister();

		return true; // success!
	}
}