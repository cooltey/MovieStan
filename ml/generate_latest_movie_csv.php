<?php

// https://api.themoviedb.org/3/movie/now_playing?api_key=3eb234903c8417da0d448fde2f9cc02e&language=en-US&page=1

	// url
	$apiUrl 		= "https://api.themoviedb.org/3/movie/now_playing";
	$apiKey 		= "3eb234903c8417da0d448fde2f9cc02e";
	$apiStartPage 	= 1;
	$apiMaxPage 	= 30;

	$outputArray = array();

	for($i = $apiStartPage; $i < $apiMaxPage; $i++){
		$getJSON 	= file_get_contents($apiUrl."?api_key=".$apiKey."&page=".$i);

		$getJsonData = json_decode($getJSON, true);

		// only get movie id and name
		foreach($getJsonData['results'] AS $getResultData){


			$rowArray = array($getResultData['title'], $getResultData['id']);

			array_push($outputArray, $rowArray);
		}

	}

	$file = fopen("latest_movies.csv","w");

	foreach($outputArray AS $line){
	  fputcsv($file, $line);
	}

	fclose($file);

?>