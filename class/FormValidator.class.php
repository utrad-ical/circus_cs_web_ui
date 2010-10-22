<?php

	class FormValidator
	{
		public static function validateString($str)
		{
			if(preg_match('/[<>\\&\\!]/', $str))	return false;
			else									return true;
		}

		public static function validateAlphabet($str)
		{
			if(preg_match('/[a-zA-Z]/', $str))	return true;
			else								return false;
		}

		
		public static function validateDate($date)
		{
			if(preg_match('/^\d{4}[-\\/]\d{1,2}[-\\/]\d{1,2}$/', $date)) // YYYY-mm-dd  or YYYY/mm/dd
			{
				$vals = preg_split("/[-\\/]/", $date);

				if(checkdate($vals[0], $vals[1], $vals[2]))
				{
					return true;
				}
			}
			else if(preg_match('/^\d{8}$/', $date))  // YYYYMMDD
			{
				if(checkdate(substr($date,4,2), substr($date,6,2), substr($date,0,4)))
				{
					return true;
				}
			}
			return false;
		}
	
		public static function validateTime($time)
		{
			if(preg_match('/^\d{1,2}:\d{1,2}:\d{1,2}$/', $time))
			{
				$vals = explode(":", $time);
				
				if(0<=$vals[0] && $vals[0]<=23 && 0<=$vals[1] && $vals[1]<=59 && 0<=$vals[2] && $vals[2]<=59)
				{
					return true;
				}
			}
			return false;
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