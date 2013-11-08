<?php

/**
 * Class for windows service control.
 * @author Yukihiro Nomura <nomuray-tky@umin.ac.jp>
 */
class WinServiceControl
{
	public static function getStatus($serviceName, $hostName = 'localhost')
	{
		$cmdStr = sprintf('sc.exe \\\\%s query "%s" | find "STATE"',
							$hostName,
							$serviceName);

		$buffer = explode(":", shell_exec($cmdStr));
		$statusArr = explode(" ", $buffer[1]);

		$result = array(
			'serviceName' => $serviceName,
			'val' => 0,
			'str' => 'Status error'
		);

		switch($statusArr[1])
		{
			case 1: // Stopped
				$result['str'] = 'Stopped';
				break;

			case 2: // Start pending
				$result['str'] = 'Start pending';
				$result['val'] = 1;
				break;

			case 3: // Stop pending
					$result['str'] = 'Stop pending';
					break;

			case 4: // Running
				$result['str'] = 'Running';
				$result['val'] = 1;
				break;

			case 5: // Continue pending
				$result['str'] = 'Continue pending';
				break;

			case 6: // Pause pending
				$result['str'] = 'Pause pending';
				break;

			case 7: // Paused
				$result['str'] = 'Paused';
				break;
		}
		return $result;
	}
}

