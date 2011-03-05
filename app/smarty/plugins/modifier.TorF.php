<?php

/**
 * Simple modifier that returns 't' or 'f' for given boolean value.
 * @param bool $boolean
 * @return string 't' if true, 'f' otherwise.
 */
function smarty_modifier_TorF($boolean)
{
	return $boolean ? 't' : 'f';
}

?>