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
		'dryRun' => array('type' => 'str'),
		'delete' => array('type' => 'str')
	);

	/**
	 * Check if the target personal feedback data can be really unregistered.
	 * Personal feedback can be unregistered when the consensual
	 * feedback is not registered, and either (1) or (2) is met:
	 * (1) You entered this personal feedback, and freeze time
	 * (set by CAD result policy) has not passed.
	 * (2) You have serverOperation privilege.
	 * @param CadResult $cad_result The CadResult object.
	 * @param string $user The target user ID. If null, the personal feedback
	 * for the current user is the target.
	 * @return Feedback The Feedback object if it can be gracefully unregistered.
	 */
	protected function checkPersonal(CadResult $cad_result, $user = null)
	{
		$policy = $cad_result->PluginResultPolicy;
		if (!$policy)
			throw new ApiSystemException('CAD result policy not found.'); // fetal

		$target_user = $this->currentUser;

		$cfb = $cad_result->queryFeedback('consensual');
		if (count($cfb) > 0)
		{
			throw new ApiOperationException(
				'Can not unregister this personal feedback: ' .
				'consensual feedback is already registered.'
			);
		}

		if ($this->currentUser->hasPrivilege(Auth::SERVER_OPERATION))
		{
			if ($user_id != null)
			{
				$target_user = new User($user_id);
				if (!isset($target_user->user_id))
				{
					throw new ApiOperationException('Specified user not found.');
				}
			}
		}
		else
		{
			if ($user_id != null && $target_user->user_id != $user_id)
			{
				throw new ApiOperationException(
					'You do not have privilege to unregister ' .
					'personal feedback entered by others.'
				);
			}
		}

		$fb = $cad_result->queryFeedback('personal', $target_user->user_id);
		if (count($fb) == 0)
		{
			throw new ApiOperationException(
				'Target personal feedback entered by ' .
				$target_user->user_id . ' is not found.'
			);
		}
		$fb = reset($fb);

		if (!$this->currentUser->hasPrivilege(Auth::SERVER_OPERATION))
		{
			// check for PFB freeze time
			$limit = $policy->time_to_freeze_personal_fb;
			$reg_time = new DateTime($fb->registered_at);
			$current = new DateTime();
			$elapsed = $current->diff($reg_time)->format('%i'); // minutes
			if ($elapsed >= $limit)
			{
				if ($limit > 0)
				{
					throw new ApiOperationException(
						'Can not unregister this personal feedback: ' .
						"unregister time limit ($limit min.) has passed."
					);
				}
				else
				{
					throw new ApiOperationException(
						'Can not unregister this personal feedback: ' .
						'this operation is prohibited by CAD result policy.'
					);
				}
			}
		}

		return $fb;
	}

	/**
	 * Check if the target consensual feedback data can be really unregistered.
	 * Consensual feedback can be unregistered when
	 * you have consensualFeedbackModify privilege.
	 * @param CadResult $cad_result The CadResult object.
	 * @return Feedback The Feedback object if it can be gracefully unregistered.
	 */
	protected function checkConsensual(CadResult $cad_result)
	{
		$fb = $cad_result->queryFeedback('consensual');
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
		return $fb;
	}

	/**
	 * Check if the target Feedback data can be really unregistered or not.
	 * Exceptions are thrown for errors.
	 * @param int $job_id The job ID
	 * @param bool $is_consensual True if you want to delete consensual feedback.
	 * @param string $user Delete personal FB for this user.
	 * @return Feedback The Feedback object if it can be gracefully unregistered.
	 */
	protected function check($job_id, $is_consensual, $user = null)
	{
		$cad_result = new CadResult($job_id);
		if (!$cad_result || !$cad_result->job_id)
			throw new ApiSystemException('CAD result not found.'); // fetal

		if ($is_consensual)
		{
			return $this->checkConsensual($cad_result);
		}
		else
		{
			return $this->checkPersonal($cad_result, $user);
		}
	}

	public function execute($params)
	{
		// Determine the target Feedback data
		$job_id = $params['jobID'];
		$is_consensual = $params['feedbackMode'] == 'consensual';

		$fb = $this->check($job_id, $is_consensual, $params['user'] ?: null);

		if (!!($params['dryRun']))
		{
			// You can unregister this FB now. But not for now.
			return true;
		}

		// Now we actually unregister the feedback data. This part is simple.
		if (!!($params['delete']))
		{
			$fb->delete($fb->fb_id);
		}
		else
		{
			$fb->unregister();
		}

		return true; // success!
	}
}