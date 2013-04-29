<?php

/**
 * This API is for internal use only.
 */
class InternalCountImagesAction extends CountImagesAction
{
	protected static $required_privileges = array(
		Auth::VOLUME_DOWNLOAD
	);

	protected static $public = false;
}
