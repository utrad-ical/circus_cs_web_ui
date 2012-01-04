<?php

/**
 * Class for windows service control.
 * @author Yukihiro Nomura <nomuray-tky@umin.ac.jp>
 */
class WinServiceControl
{
	public static function getStatus($serviceName, $hostName = 'localhost')
	{
		$statusArr = win32_query_service_status($serviceName, $hostName);

		$result = array(
			'serviceName' => $serviceName,
			'val' => 0,
			'str' => 'Status error'
		);

		switch($statusArr['CurrentState'])
		{
			case WIN32_SERVICE_CONTINUE_PENDING:
				$result['str'] = 'Continue pending';
				break;

			case WIN32_SERVICE_RUNNING:
				$result['str'] = 'Running';
				$result['val'] = 1;
				break;

			case WIN32_SERVICE_START_PENDING:
				$result['str'] = 'Start pending';
				$result['val'] = 1;
				break;

			case WIN32_SERVICE_STOPPED:
				$result['str'] = 'Stopped';
				break;

			case WIN32_SERVICE_STOP_PENDING:
				$result['str'] = 'Stop pending';
				break;

			case WIN32_SERVICE_PAUSED:
				$result['str'] = 'Paused';
				break;

			case WIN32_SERVICE_PAUSE_PENDING:
				$result['str'] = 'Pause pending';
				break;
		}
		return $result;
	}
}

