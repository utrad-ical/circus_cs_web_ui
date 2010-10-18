<?
	class PinfoScramble
	{
		private $_baseStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		private $_baseLength = 62;
	
		public function Encrypt($str, $key)
		{
			$ret = "";
		
			for($n=0; $n<strlen($str); $n++)
			{
				$inChar  = $str[$n];
				$keyChar = $key[$n];

				$inNum = $keyNum = -1;

				for($i=0; $i<strlen($this->_baseStr); $i++)
				{
					if($inChar  === $this->_baseStr[$i])  $inNum  = $i;
					if($keyChar === $this->_baseStr[$i])  $keyNum = $i;
				}
				
				if($inNum == -1 || $keyNum == -1)
				{
					$ret .= $str[$n];
				}
				else
				{
					$encodeNum = ($inNum + $keyNum) % $this->_baseLength;
					$ret .= $this->_baseStr[$encodeNum];
				}
			}
		
			return $ret;
		}
	
		public function Decrypt($str, $key)
		{
			$ret = "";
		
			for($n=0; $n<strlen($str); $n++)
			{
				$inChar  = $str[$n];
				$keyChar = $key[$n];

				$inNum = $keyNum = -1;

				for($i=0; $i<strlen($this->_baseStr); $i++)
				{
					if($inChar  === $this->_baseStr[$i])  $inNum  = $i;
					if($keyChar === $this->_baseStr[$i])  $keyNum = $i;
				}
		
				if($inNum == -1 || $keyNum == -1)
				{
					$ret .= $str[$n];
				}
				else
				{
					$encodeNum = ($inNum - $keyNum + $this->_baseLength) % $this->_baseLength;
					$ret .= $this->_baseStr[$encodeNum];
				}
			}
			
			return $ret;
		}

		public function ScramblePtName()
		{
			return 'XXXXX XXXXX';
		}

		public function ScrambleBirthDate()
		{
			return 'YYYY-MM-DD';
		}
	}
?>