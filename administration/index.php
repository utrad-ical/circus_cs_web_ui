<? 
	header( "HTTP/1.1 301 Moved Permanently" ); 
	header( "Status: 301 Moved Permanently" ); 
	header( "Location: ../index.php?mode=timeout" ); 
	exit(0); // 任意（これを記述しておくと誤って出力されることを防げます） 
?> 