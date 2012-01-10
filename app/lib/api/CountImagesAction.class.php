<?php

class CountImagesAction extends ApiActionBase
{
	public function execute($params)
	{
		$params = $api_request['params'];

		$seriesUIDs = $params['seriesInstanceUID'];
		$studyUIDs = $params['studyInstanceUID'];

		if($this->check_params($params) == FALSE)
		{
			throw new ApiOperationException("Invalid parameter.");
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

		return $result;
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
		$dum = new Series();
		foreach ($UIDs as $id)
		{
			$series = Series::selectOne(array('series_instance_uid' => $id));
			if (!$series)
				continue;
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
			$studies = Series::select(array('study_instance_uid' => $id));
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
