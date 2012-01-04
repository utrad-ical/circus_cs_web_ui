<?php

/**
 * Simple modifier that returns 't' or 'f' for given boolean value.
 * @param bool $boolean
 * @return string 'O' if true, '-' otherwise.
 */
function smarty_modifier_OorMinus($boolean)
{
	return $boolean ? 'O' : '-';
}
