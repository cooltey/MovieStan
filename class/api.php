<?php
 /**
 *  Project: Loseweight
 *  Last Modified Date: 2016/01
 *  Developer: Cooltey Feng
 *  File: class/Account.php
 *  Description: Library for control basic function
 */
 
class API{

	var $db;
	var $getLib;
	
	function API($get_db, $get_lib){				
		$this->getLib 				= $get_lib;
		$this->db					= $get_db;
	}

	function OutPutMessage($call_id){
		$theOutArray =  array("200", "404", "500");

		return $theOutArray[$call_id];
	}

	function OutPutDateFormat($get_date){
		$getDate = strtotime($get_date);

		return date("Y-m-d H:i:s", $getDate);
	}


	// check user
	function CheckUser($m_index, $m_facebook_id, $m_email){
		$returnVal = false;

		$status = "1";
		$sql 	= "SELECT `m_index`
					FROM `members` 
					WHERE `m_status` = :status
					AND `m_index` = :m_index
					AND `m_facebook_id` = :m_facebook_id
					AND `m_email` = :m_email";
		$sth 	= $this->db->prepare($sql);
		$sth->bindValue(":m_index", $m_index);
		$sth->bindValue(":m_facebook_id", $m_facebook_id);
		$sth->bindValue(":m_email", $m_email);
		$sth->bindValue(":status", $status);
		$sth->execute();						
		
		$count = $sth->rowCount();

		if($count > 0){
			$returnVal = true;
		}

		return $returnVal;
	}

	function AuthUser($m_index, $m_login_token){
		$returnVal = false;

		$status = "1";
		$sql 	= "SELECT `m_index`
					FROM `members` 
					WHERE `m_status` = :status
					AND `m_index` = :m_index
					AND `m_login_token` = :m_login_token";
		$sth 	= $this->db->prepare($sql);
		$sth->bindValue(":m_index", $m_index);
		$sth->bindValue(":m_login_token", $m_login_token);
		$sth->bindValue(":status", $status);
		$sth->execute();						
		
		$count = $sth->rowCount();

		if($count > 0){
			$returnVal = true;
		}

		return $returnVal;
	}

	function CheckFriend($m_index, $fri_m_index){
		$returnVal = false;

		$status = "1";
		$sql 	= "SELECT `fri_index`
					FROM `friends` 
					WHERE `fri_status` = :status
					AND `m_index` = :m_index
					AND `fri_m_index` = :fri_m_index";
		$sth 	= $this->db->prepare($sql);
		$sth->bindValue(":m_index", $m_index);
		$sth->bindValue(":fri_m_index", $fri_m_index);
		$sth->bindValue(":status", $status);
		$sth->execute();						
		
		$count = $sth->rowCount();

		if($count > 0){
			$returnVal = true;
		}

		return $returnVal;
	}


	// MemberRegister.php
	function MemberRegister($getData){
		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1),
							 "MemberId"		=> "");

		if($this->getLib->checkVal($getData['FacebookId']) && 
			$this->getLib->checkVal($getData['Email'])){  
			
			$dataArray			= array();
			$m_facebook_id		= $this->getLib->setFilter($getData['FacebookId']);
			$m_name				= $this->getLib->setFilter($getData['Name']);
			$m_email			= $this->getLib->setFilter($getData['Email']);
			$m_register_time	= date("Y-m-d H:i:s");
			$m_login_time		= date("Y-m-d H:i:s");
			$m_login_ip			= $this->getLib->getIp();
			$m_login_token		= "";
			$m_status			= "1";
			

			if($m_facebook_id != "" && $m_email != ""){

				$status = "1";
				$sql 	= "SELECT `m_index`
							FROM `members` 
							WHERE `m_status` = :status
							AND `m_facebook_id` = :m_facebook_id
							AND `m_email` = :m_email";
				$sth 	= $this->db->prepare($sql);
				$sth->bindValue(":m_facebook_id", $m_facebook_id);
				$sth->bindValue(":m_email", $m_email);
				$sth->bindValue(":status", $status);
				$sth->execute();						
				
				$count = $sth->rowCount();

				if($count == 0){
					// insert 
					$sql = "INSERT INTO `members`(`m_facebook_id`, 
												 `m_name`,
												 `m_email`, 
												 `m_register_time`,
												 `m_login_time`,
												 `m_login_ip`,
												 `m_login_token`,
												 `m_status`) 
							VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
					$sth = $this->db->prepare($sql);
					$sth->execute(array($m_facebook_id, 
										$m_name,
										$m_email, 
										$m_register_time,
										$m_login_time,
										$m_login_ip,
										$m_login_token,
										$m_status));


					$getId = $this->db->lastInsertId();

					$returnArray['StatusCode']	    = $this->OutPutMessage(0);
					$returnArray['MemberId']		= $getId;
				}else{
					$returnArray['StatusCode'] 		= $this->OutPutMessage(2);
					$getData = $this->getLib->fetchSQL($sth);

					$returnArray['MemberId']		= $getData['m_index'];
				}

				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}

	// MemberLogin.php
	function MemberLogin($getData){
		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1),
							 "MemberId"		=> "",
							 "LoginToken"	=> "");

		if($this->getLib->checkVal($getData['FacebookId']) && 
			$this->getLib->checkVal($getData['Email']) && 
			$this->getLib->checkVal($getData['MemberId'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_facebook_id		= $this->getLib->setFilter($getData['FacebookId']);
			$m_email			= $this->getLib->setFilter($getData['Email']);
			$m_login_time		= date("Y-m-d H:i:s");
			$m_login_ip			= $this->getLib->getIp();
			$m_login_token		= $this->getLib->generateRandomString(50);
			

			if($this->CheckUser($m_index, $m_facebook_id, $m_email)){
				$sql = "UPDATE `members` SET 
						`m_login_time` = ?,
						`m_login_ip` = ?,
						`m_login_token` = ?
						WHERE `m_index` = ?";
				$sth = $this->db->prepare($sql);
				$sth->execute(array($m_login_time,
									$m_login_ip,
									$m_login_token,
									$m_index));

				$returnArray['StatusCode']	    = $this->OutPutMessage(0);
				$returnArray['MemberId']		= $m_index;
				$returnArray['LoginToken']		= $m_login_token;

				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}
	
	// BrowsersAdd.php
	function BrowsersAdd($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1));

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['MovieId'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_login_token		= $this->getLib->setFilter($getData['LoginToken']);
			$mov_index			= $this->getLib->setFilter($getData['MovieId']);
			$b_create_time		= date("Y-m-d H:i:s");
			$b_status			= "1";
			

			if($this->AuthUser($m_index, $m_login_token)){
				// insert 
				$sql = "INSERT INTO `browsers`(`m_index`, 
											 `mov_index`, 
											 `b_create_time`,
											 `b_status`) 
						VALUES(?, ?, ?, ?)";
				$sth = $this->db->prepare($sql);
				$sth->execute(array($m_index, 
									$mov_index, 
									$b_create_time,
									$b_status));

				$returnArray['StatusCode']	    = $this->OutPutMessage(0);

				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	

	// FavoriteCheck.php // check favorite
	function FavoriteCheck($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1),
							 "FavoriteId"	=> "");

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['MovieId'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_login_token		= $this->getLib->setFilter($getData['LoginToken']);
			$mov_index			= $this->getLib->setFilter($getData['MovieId']);
			$fav_create_time	= date("Y-m-d H:i:s");
			$fav_status			= "1";
			

			if($this->AuthUser($m_index, $m_login_token)){

				// check exist
				$status = "1";
				$sql 	= "SELECT `fav_index`
							FROM `favorites` 
							WHERE `fav_status` = :status
							AND `mov_index` = :mov_index
							AND `m_index` = :m_index";
				$sth 	= $this->db->prepare($sql);
				$sth->bindValue(":mov_index", $mov_index);
				$sth->bindValue(":m_index", $m_index);
				$sth->bindValue(":status", $status);
				$sth->execute();	

				$getFavData = $this->getLib->fetchSQL($sth);

				$returnArray['StatusCode']	    = $this->OutPutMessage(0);
				$returnArray['FavoriteId']	    = intval($getFavData['fav_index']);

				


				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	

	// FavoriteAdd.php // add or delete
	function FavoriteAdd($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1),
							 "Action"		=> "");

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['MovieId'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_login_token		= $this->getLib->setFilter($getData['LoginToken']);
			$mov_index			= $this->getLib->setFilter($getData['MovieId']);
			$fav_create_time	= date("Y-m-d H:i:s");
			$fav_status			= "1";
			

			if($this->AuthUser($m_index, $m_login_token)){

				// check exist
				$status = "1";
				$sql 	= "SELECT `fav_index`
							FROM `favorites` 
							WHERE `fav_status` = :status
							AND `mov_index` = :mov_index
							AND `m_index` = :m_index";
				$sth 	= $this->db->prepare($sql);
				$sth->bindValue(":mov_index", $mov_index);
				$sth->bindValue(":m_index", $m_index);
				$sth->bindValue(":status", $status);
				$sth->execute();	

				$getFavData = $this->getLib->fetchSQL($sth);

				if($getFavData['fav_index'] == ""){
					// insert 
					$sql = "INSERT INTO `favorites`(`m_index`, 
												 `mov_index`, 
												 `fav_create_time`,
												 `fav_status`) 
							VALUES(?, ?, ?, ?)";

					$sth = $this->db->prepare($sql);
					$sth->execute(array($m_index, 
										$mov_index, 
										$fav_create_time,
										$fav_status));


					$returnArray['StatusCode']	    = $this->OutPutMessage(0);
					$returnArray['Action']	    	= "INSERT";
				}else{
					// delete
					$sql = "DELETE FROM `favorites` 
							WHERE `m_index` = :m_index
							AND `mov_index` = :mov_index";
					$sth = $this->db->prepare($sql);
					$sth->bindValue(":m_index", $m_index);
					$sth->bindValue(":mov_index", $mov_index);
					$sth->execute();	
					$returnArray['StatusCode']	    = $this->OutPutMessage(0);
					$returnArray['Action']	    	= "DELETE";
				}

				


				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	

	// FavoriteList.php // later
	function FavoriteList($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1),
							 "Member"		=> array(),
							 "List"			=> array());

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['FetchMemberId'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_login_token		= $this->getLib->setFilter($getData['LoginToken']);
			$fetch_m_index		= $this->getLib->setFilter($getData['FetchMemberId']);
			$fav_status			= "1";
			

			if($this->AuthUser($m_index, $m_login_token)){
				// get member info
				// select  
				$sql = "SELECT `m_index` AS `UserId`, `m_facebook_id` AS `UserFB`, `m_name` AS `UserName`
						FROM `members`
						WHERE `m_index` = :m_index
						AND `m_status` = :status";

				$sth = $this->db->prepare($sql);
				$sth->bindValue(":m_index", $fetch_m_index);
				$sth->bindValue(":status", $fav_status);
				$sth->execute();

				$getMemberData = $this->getLib->fetchSQL($sth);


				// select  
				$sql = "SELECT `mov_index`
						FROM `favorites`
						WHERE `m_index` = :m_index
						AND `fav_status` = :status
						ORDER BY `fav_create_time` DESC";

				$sth = $this->db->prepare($sql);
				$sth->bindValue(":m_index", $fetch_m_index);
				$sth->bindValue(":status", $fav_status);
				$sth->execute();


				// fetch the movie db api
				$apiUrl 		= "https://api.themoviedb.org/3/movie/";
				$apiKey 		= "3eb234903c8417da0d448fde2f9cc02e";

				$outputArray = array();

				foreach($this->getLib->fetchArray($sth) AS $getFavData){

					$getJSON 	= @file_get_contents($apiUrl.$getFavData['mov_index']."?api_key=".$apiKey);

					if($getJSON != ""){
						$getJsonData = json_decode($getJSON, true);

						$newArray = array();

						//    poster_path: "/45Y1G5FEgttPAwjTYic6czC9xCn.jpg",
						//    adult: false,
						//    overview: "In the near future, a weary Logan cares for an ailing Professor X in a hide out on the Mexican border. But Logan's attempts to hide from the world and his legacy are up-ended when a young mutant arrives, being pursued by dark forces.",
						//    release_date: "2017-02-28",
						//    genre_ids: [
						//            28,
						//            18,
						//            878
						//            ],
						//    id: 263115,
						//    original_title: "Logan",
						//    original_language: "en",
						//    title: "Logan",
						//    backdrop_path: "/5pAGnkFYSsFJ99ZxDIYnhQbQFXs.jpg",
						//    popularity: 172.393189,
						//    vote_count: 1234,
						//    video: false,
						//    vote_average: 7.7
						$newArray['poster_path'] 		= $getJsonData['poster_path'];
						$newArray['adult'] 				= $getJsonData['adult'];
						$newArray['overview'] 			= $getJsonData['overview'];
						$newArray['release_date'] 		= $getJsonData['release_date'];
						$newArray['genre_ids'] 			= array();
						$newArray['id'] 				= $getJsonData['id'];
						$newArray['original_title'] 	= $getJsonData['original_title'];
						$newArray['original_language'] 	= $getJsonData['original_language'];
						$newArray['title'] 				= $getJsonData['title'];
						$newArray['backdrop_path'] 		= $getJsonData['backdrop_path'];
						$newArray['popularity'] 		= $getJsonData['popularity'];
						$newArray['vote_count'] 		= $getJsonData['vote_count'];
						$newArray['video'] 				= $getJsonData['video'];
						$newArray['vote_average'] 		= $getJsonData['vote_average'];

						array_push($outputArray, $newArray);
					}
				}


				$returnArray['StatusCode']	= $this->OutPutMessage(0);
				$returnArray['Member']	    = $getMemberData;
				$returnArray['List']	    = $outputArray;

				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	

	// RatingAdd.php
	function RatingAdd($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1));

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['MovieId']) && 
			$this->getLib->checkVal($getData['Score'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_login_token		= $this->getLib->setFilter($getData['LoginToken']);
			$mov_index			= $this->getLib->setFilter($getData['MovieId']);
			$rat_score			= intval($this->getLib->setFilter($getData['Score']));
			$rat_comments		= $this->getLib->setFilter($getData['Comments']); // optional
			$rat_create_time	= date("Y-m-d H:i:s");
			$rat_status			= "1";
			

			if($this->AuthUser($m_index, $m_login_token)){
				// insert 
				$sql = "INSERT INTO `ratings`(`m_index`, 
											 `mov_index`, 
											 `rat_score`,
											 `rat_comments`,
											 `rat_create_time`,
											 `rat_status`) 
						VALUES(?, ?, ?, ?, ?, ?)";
				$sth = $this->db->prepare($sql);
				$sth->execute(array($m_index, 
									$mov_index, 
									$rat_score,
									$rat_comments,
									$rat_create_time,
									$rat_status));

				$returnArray['StatusCode']	    = $this->OutPutMessage(0);

				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	

	// RatingList.php
	function RatingList($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1),
							 "List"			=> array());

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['MovieId'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_login_token		= $this->getLib->setFilter($getData['LoginToken']);
			$mov_index			= $this->getLib->setFilter($getData['MovieId']);
			$rat_status			= "1";
			

			if($this->AuthUser($m_index, $m_login_token)){
				// insert 
				$sql = "SELECT `b`.`m_index` AS `UserId`, `b`.`m_facebook_id` AS `UserFB`, `b`.`m_name` AS `UserName`, `a`.`rat_score` AS `Score`,
						`a`.`rat_comments` AS `Comments`, `a`.`rat_create_time` AS `Date`
						FROM `ratings` AS `a`, `members` AS `b`
						WHERE `a`.`mov_index` = :mov_index 
						AND `a`.`m_index` = `b`.`m_index`
						AND `a`.`rat_status` = :status
						ORDER BY `a`.`rat_create_time` DESC";

				$sth = $this->db->prepare($sql);
				$sth->bindValue(":mov_index", $mov_index);
				$sth->bindValue(":status", $rat_status);
				$sth->execute();	

				$returnArray['StatusCode']	= $this->OutPutMessage(0);
				$returnArray['List']	    = $this->getLib->fetchArray($sth);

				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	

	// FriendAdd.php
	function FriendAdd($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1));

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['FriendMemberId'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$m_login_token		= $this->getLib->setFilter($getData['LoginToken']);
			$fri_m_index		= intval($this->getLib->setFilter($getData['FriendMemberId']));
			$fri_create_time	= date("Y-m-d H:i:s");
			$fri_is_accepted	= "2"; // 2 == await, 1 == accept, 0 == decline
			$fri_status			= "1";
			

			if($this->AuthUser($m_index, $m_login_token) && !$this->CheckFriend($m_index, $fri_m_index)){
				// insert 
				$sql = "INSERT INTO `friends`(`m_index`, 
											 `fri_m_index`, 
											 `fri_create_time`,
											 `fri_is_accepted`,
											 `fri_status`) 
						VALUES(?, ?, ?, ?, ?)";
				$sth = $this->db->prepare($sql);
				$sth->execute(array($m_index, 
									$fri_m_index, 
									$fri_create_time,
									$fri_is_accepted,
									$fri_status));

				$returnArray['StatusCode']	    = $this->OutPutMessage(0);

				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	


	// FriendConfirm.php
	function FriendConfirm($getData){

		$returnArray = array("StatusCode"	=> $this->OutPutMessage(1));

		if($this->getLib->checkVal($getData['LoginToken']) && 
			$this->getLib->checkVal($getData['MemberId']) && 
			$this->getLib->checkVal($getData['FriendMemberId']) && 
			$this->getLib->checkVal($getData['Accept'])){  
			
			$dataArray			= array();
			$m_index			= $this->getLib->setFilter($getData['MemberId']);
			$fri_m_index		= intval($this->getLib->setFilter($getData['FriendMemberId']));
			$fri_is_accepted	= intval($this->getLib->setFilter($getData['Accept']));
			

			if($this->AuthUser($m_index, $m_login_token)){

				// update the request 
				$sql = "UPDATE `friends` SET 
						`fri_is_accepted` = ?
						WHERE `m_index` = ?
						AND `fri_m_index` = ?";
				$sth = $this->db->prepare($sql);
				$sth->execute(array($fri_is_accepted,
									$fri_m_index,
									$m_index));

				if($fri_is_accepted == "1"){ // accept
					// insert 
					$sql = "INSERT INTO `friends`(`m_index`, 
												 `fri_m_index`, 
												 `fri_create_time`,
												 `fri_is_accepted`,
												 `fri_status`) 
							VALUES(?, ?, ?, ?, ?)";
					$sth = $this->db->prepare($sql);
					$sth->execute(array($m_index, 
										$fri_m_index, 
										$fri_create_time,
										$fri_is_accepted,
										$fri_status));

					$returnArray['StatusCode']	    = $this->OutPutMessage(0);
				}
				
			}else{			
				$returnArray['StatusCode'] = $this->OutPutMessage(1);
			}
		}
		return $returnArray;
	}	
}