<?php

class MigrationRev8 extends Migration
{
	public function run()
	{
		global $CONF_DIR, $CONFIG_DICOM_STORAGE;

		$configFileName = $CONF_DIR . '/' . $CONFIG_DICOM_STORAGE;

		print "    updating " . $configFileName . " -> ";

		$fp = fopen($configFileName, 'r');

		if($fp == FALSE)
		{
			print "failed to open file\n";
		}
		else
		{
			$dstStr = "";

			// Change name of keys (compressFlg, overwritePtNameFlg)
			while(!feof($fp))
			{
				$buffer = fgets($fp);
				$tmpArr = explode("=", $buffer);

				if(count($tmpArr) == 2)
				{
					switch(trim($tmpArr[0]))
					{
						case 'compressFlg':
							$tmpArr[0] = 'compressDicomFile ';
							break;

						case 'overwritePtNameFlg':
							$tmpArr[0] = 'overwritePatientName ';
							break;
					}

					$dstStr .= sprintf("%s=%s", $tmpArr[0], $tmpArr[1]);
				}
				else
				{
					$dstStr .= $buffer;
				}
			}
			fclose($fp);

			// Add flag for overwrite DICOM file
			$dstStr .= sprintf("\r\n\r\n");
			$dstStr .= sprintf("; Flag for overwrite DICOM file (1: allow to overwrite)\r\n");
			$dstStr .= sprintf("overwriteDicomFile = 1\r\n");

			file_put_contents($configFileName, $dstStr);

			print "succeeded\n";

		}
	}
}