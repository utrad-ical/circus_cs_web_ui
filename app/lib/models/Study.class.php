<?php

/**
 * Model class for studies.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Study extends Model
{
	protected static $_table = 'study_list';
	protected static $_primaryKey = 'study_instance_uid';
	protected static $_belongsTo = array(
		'Patient' => array('key' => 'patient_id')
	);
	protected static $_hasMany = array(
		'Series' => array('key' => 'study_instance_uid')
	);
}

?>