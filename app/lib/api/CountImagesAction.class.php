<?php

class CountImagesAction extends ApiAction
{
	protected static $required_privileges = array(
		Auth::API_EXEC
	);


	function requiredPrivileges()
	{
		return self::$required_privileges;
	}


	function execute($api_request)
	{
		$params = $api_request['params'];
		$action = $api_request['action'];

		$seriesUIDs = $params['seriesInstanceUID'];
		$studyUIDs = $params['studyInstanceUID'];

		if($this->check_params($params) == FALSE)
		{
			throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
		}

		$result = array();
		if(isset($seriesUIDs))
		{
			$result = $this->get_series_counts($seriesUIDs);
		}
		else if(isset($studyUIDs))
		{
			$result = $this->get_study_counts($studyUIDs);
		}

		if (count($result) == 0) {
			unset($result);
		}

		$res = new ApiResponse();
		$res->setResult($action, $result);
		return $res;
	}

	private function check_params($params)
	{
		$seriesUIDs = $params['seriesInstanceUID'];
		$studyUIDs = $params['studyInstanceUID'];

		if((isset($seriesUIDs) && isset($studyUIDs))
			|| (!isset($seriesUIDs) && !isset($studyUIDs)))
		{
			return FALSE;
		}
		return TRUE;
	}

	private function get_series_counts($UIDs)
	{
		$result = array();
		foreach ($UIDs as $id)
		{
			$series = new Series($id);
			if ($series->image_number)
			{
				array_push(
					$result,
					array(
						"studyInstanceUID" => $series->study_instance_uid,
						"seriesInstanceUID" => $id,
						"number" => $series->image_number
					)
				);
			}
		}

		return $result;
	}

	private function get_study_counts($UIDs)
	{
		$result = array();
		foreach ($UIDs as $id)
		{
			$series = new Series();
			$studies = $series->find(array('study_instance_uid' => $id));
			foreach ($studies as $s)
			{
				if ($s->image_number)
				{
					array_push(
						$result,
						array(
							"studyInstanceUID" => $s->study_instance_uid,
							"seriesInstanceUID" => $s->series_instance_uid,
							"number" => $s->image_number
						)
					);
				}
			}
		}

		return $result;
	}
}
