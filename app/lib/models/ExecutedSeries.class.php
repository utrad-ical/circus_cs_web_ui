<?php

class ExecutedSeries extends Model
{
	protected static $_table = 'executed_series_list';
	protected static $_belongsTo = array(
		'CadResult' => array('key' => 'job_id'),
		'Series' => array('key' => 'series_sid')
	);
}