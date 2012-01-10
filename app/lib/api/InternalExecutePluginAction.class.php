<?php

/**
 * This API is for internal use only.
 */
class InternalExecutePluginAction extends ExecutePluginAction
{
	protected static $required_privileges = array(
		Auth::CAD_EXEC
	);
}
