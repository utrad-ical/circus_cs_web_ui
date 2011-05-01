<?php

/**
 * Model class for patients.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Patient extends Model
{
	protected static $_table = 'patient_list';
	protected static $_primaryKey = 'patient_id';
	protected static $_hasMany = array(
		'Study' => array('key' => 'patient_id')
	);

	/**
	 * Calculates the patients's age.
	 */
	public function age()
	{
		return CalcAge($this->birth_date, date('Ymd'));
	}

	public function __toString()
	{
		return sprintf("[Patient %d %s]", $this->patient_id, $this->patient_name);
	}
}

?>