<?php

/**
 * Model class for study series.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Series extends Model
{
	protected static $_table = 'series_list';
	protected static $_primaryKey = 'sid';
	protected static $_belongsTo = array(
		'Study' => array('key' => 'study_instance_uid')
	);
}
