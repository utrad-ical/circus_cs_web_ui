<?php

class JobSeries extends Model
{
	protected static $_table = 'job_queue_series';
	protected static $_belongsTo = array(
		'Job' => array('key' => 'job_id'),
		'Series' => array('key' => 'series_sid')
	);
}