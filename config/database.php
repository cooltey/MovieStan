<?php
 /**
 *  Project: Nvidia Portal
 *  Last Modified Date: 2015 Jan
 *  Developer: Cooltey Feng
 *  File: config/database.php
 *  Description: Database Config
 */
 ini_set('session.cookie_httponly', 1);
 ini_set("magic_quotes_gpc", "on");
 ini_set("display_errors", "on");
 error_reporting(E_ALL & ~E_NOTICE);
 date_default_timezone_set("Asia/Taipei");

 // cookie domain name
 $GLOBALS['cookie_folder_name'] = "/MovieStan/";

 // // session setting
 session_set_cookie_params(0, $GLOBALS['cookie_folder_name'], "", FALSE, TRUE);
 session_start();

 // use PDO to make the connection
 $db_host 		= "127.0.0.1";
 $db_name 		= "movie_stan";
 $db_username 	= "root";
 $db_password 	= "";

 // $db_host 		= "us-cdbr-azure-west-b.cleardb.com";
 // $db_name 		= "moviestan";
 // $db_username 	= "b3ca558c594578";
 // $db_password 	= "1dada4fd";

 try {
	$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password);
	$db->exec("SET CHARACTER SET utf8");
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 }
 catch( PDOException $Exception ) {
 }


 header('X-Frame-Options: DENY');

 
?>