<?php

class PluginAttribute extends Model
{
	protected static $_table = 'executed_plugin_attributes';
	protected static $_primaryKey = array('job_id', 'key');
	protected static $_belongsTo = array(
		'CADResult' => array(key => 'job_id')
	);
}

?>