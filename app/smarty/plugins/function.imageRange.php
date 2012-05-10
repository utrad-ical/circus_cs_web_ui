<?php

/**
* Custom plugin function for smarty, for pretty-print volume creation range.
*/
function smarty_function_imageRange($param, $smarty)
{
	$start = $param['z_org_img_num'];
	$count = $param['image_count'];
	$end = $start + $param['image_delta'] * ($count - 1);
	return "$start -&gt; $end ($count)";
}
