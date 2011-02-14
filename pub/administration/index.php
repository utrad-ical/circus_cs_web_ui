<?
	header( "HTTP/1.1 301 Moved Permanently" );
	header( "Status: 301 Moved Permanently" );
	header( "Location: ../index.php?mode=timeout" ); 
	exit();
?> 