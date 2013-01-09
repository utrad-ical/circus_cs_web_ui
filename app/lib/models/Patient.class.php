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
	 * If set to true, some fields became unavailable and scrambled values
	 * will be returned.
	 * @var bool
	 */
	public static $anonymizeMode = false;

	/**
	 * Calculates the patients's age.
	 */
	public function age()
	{
		return $this->calcAge($this->_data['birth_date'], date('Ymd'));
	}

	/**
	* Utility function to calculate age.
	* @param string $birthDate The date of birth in 'YYYY-MM-DD' or 'YYYYMMDD'
	* format (hyphens are optinal).
	* @param string $baseDate The date at which we calculate age
	* (typically today).
	* @return string The calculated age. Return -1 if invalid date is passed.
	*/
	protected function calcAge($birthDate, $baseDate)
	{
		$birthDate = str_replace('-', '', $birthDate);
		$baseDate  = str_replace('-', '', $baseDate);

		if(!checkdate(substr($birthDate,4,2), substr($birthDate,6,2), substr($birthDate,0,4)))	return -1;
		if(!checkdate(substr($baseDate,4,2),  substr($baseDate,6,2),  substr($baseDate,0,4)))	return -1;

		if($baseDate < $birthDate)	return -1;
		else						return (int)(($baseDate - $birthDate) / 10000);
	}

	public function __get($key)
	{
		if (!self::$anonymizeMode)
			return parent::__get($key);
		switch ($key)
		{
			case 'patient_id':
				return PinfoScramble::encrypt($this->_data['patient_id'], $_SESSION['key']);
			case 'birth_date':
				return PinfoScramble::scrambleBirthDate($this->_data['birth_date']);
			case 'patient_name':
				return PinfoScramble::scramblePtName($this->_data['patient_name']);
		}
		return parent::__get($key);
	}

	public function __toString()
	{
		return sprintf("[Patient %d %s]", $this->patient_id, $this->patient_name);
	}
}