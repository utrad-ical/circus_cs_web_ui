<?php
	$fname = $_REQUEST['fname'];

	$img = new Imagick();

	for($i=0; $i<100; $i++)
	{
		if($img->readImage($fname) != TRUE)  usleep(100000);
		else								 break;
	}

	header("Content-type: image/png");
	header("Cache-control: no-cache");
	echo $img;
	
	$img->destroy();
	unlink($fname);
?>