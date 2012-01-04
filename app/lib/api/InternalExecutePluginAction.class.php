<?php

class InternalExecutePluginAction extends ExecutePluginAction
{
	protected static $required_privileges = array(
		Auth::CAD_EXEC
	);

	function requiredPrivileges()
	{
		return self::$required_privileges;
	}
}
