<?php

/**
 * Base WebAPI Action class.
 *
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
abstract class ApiAction
{
	abstract protected function execute($api_request);
}
