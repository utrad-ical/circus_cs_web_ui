<?
	class PinfoScramble
	{
		private static $_baseStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		private static $_baseLength = 62;
	
		public static function encrypt($str, $key)
		{
			$ret = "";
		
			for($n=0; $n<strlen($str); $n++)
			{
				$inChar  = $str[$n];
				$keyChar = $key[$n];

				$inNum = $keyNum = -1;

				for($i=0; $i<strlen(self::$_baseStr); $i++)
				{
					if($inChar  === self::$_baseStr[$i])  $inNum  = $i;
					if($keyChar === self::$_baseStr[$i])  $keyNum = $i;
				}
				
				if($inNum == -1 || $keyNum == -1)
				{
					$ret .= $str[$n];
				}
				else
				{
					$encodeNum = ($inNum + $keyNum) % self::$_baseLength;
					$ret .= self::$_baseStr[$encodeNum];
				}
			}
		
			return $ret;
		}
	
		public static function decrypt($str, $key)
		{
			$ret = "";
		
			for($n=0; $n<strlen($str); $n++)
			{
				$inChar  = $str[$n];
				$keyChar = $key[$n];

				$inNum = $keyNum = -1;

				for($i=0; $i<strlen(self::$_baseStr); $i++)
				{
					if($inChar  === self::$_baseStr[$i])  $inNum  = $i;
					if($keyChar === self::$_baseStr[$i])  $keyNum = $i;
				}
		
				if($inNum == -1 || $keyNum == -1)
				{
					$ret .= $str[$n];
				}
				else
				{
					$encodeNum = ($inNum - $keyNum + self::$_baseLength) % self::$_baseLength;
					$ret .= self::$_baseStr[$encodeNum];
				}
			}
			
			return $ret;
		}

		public function scramblePtName()
		{
			return 'XXXXX XXXXX';
		}

		public function scrambleBirthDate()
		{
			return 'YYYY-MM-DD';
		}
	}
?>