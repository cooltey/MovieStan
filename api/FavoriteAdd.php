<?php
	 /**
 	 *  Project: LoseWeightApp
 	 *  Last Modified Date: 2014/09
	 *  Developer: Cooltey Feng
	 *  File: #
	 *  Description: API
	 */
	 
	 /*
	LoginToken=123123123&MovieId=1&MemberId=1
	 */
	 
	 include_once("../config/database.php");
	 include_once("../class/lib.php");
	 include_once("../class/api.php");
	 include_once("../class/page.php");

	 // get data
	 $getData = $_REQUEST;
	 
	 // call lib class
	 $getLib = new Lib();
	 	 
	 // prevent magic quotes
	 $getLib->preventMagicQuote();
	 if(!class_exists("Lib")){
			echo "illegal";
			exit;
	 } 
	 
	 // call main class
	 $getMain = new API($db, $getLib);
	 	 
	 // return array
	 $result_array = array();
							
	 $result_array = $getMain->FavoriteAdd($getData);
	/*						
	 if($result_array['status'] == 0){
		 echo "<pre>";
		 print_r($getData);
		 
		 print_r($result_array);
		 echo "</pre>";
		 exit;
	 }	 */
	 // output json
	 $getLib->outputJson($result_array);
?>