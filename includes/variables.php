<?php

	//database configuration
	
	$host       = "localhost";
	$user       = "root";
	$pass       = "Ngocchau@2016";
	$database   = "ebook_suamaybinhson";
	
	$connect    = new mysqli($host, $user, $pass,$database) or die("Error : ".mysql_error());
	
?>