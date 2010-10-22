<?php

	class FormValidator
	{
		public static function validateString($str)
		{
	
	
		}
		
		public static function validateDate($date)
		{
		
		
		}
	
		public static function validateTime($time)
		{
	
	
		}

		public static function validateUid($uid)
		{
			if(preg_match('/[^\d\\.]/', $uid))	return false;
			else								return true;
		}
		
		public static function validateSex($sex)
		{
			if($sex != "M" && $sex != "F")	return false;
			else							return true;
		}
	}
?>