<?php

ini_set('max_execution_time', 600); //300 seconds = 5 minutes

$servername = "localhost";
$username = "root";
$password = "YamadaKun2016";
$dbname = "Related_Posts";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
	//echo "success";
}

$rankArray = array();

$className = "array";
$methodName = "add";

// Rank Weights
$titleWeight = 1;
$tagWeight = 1;
$scoreWeight = 1;
$viewWeight = 1;

// The number of results to print
$numResultsToPrint = 25;

// Get the word IDs
$idQuery = "SELECT Word_ID FROM dictionary WHERE (Word='".$className."' OR Word='".$methodName."')";
$idCheck = $conn->query($idQuery);

$titleQuery;
$tagQuery;

if( $idCheck == TRUE && $idCheck->num_rows > 0 ){
	
//	print_r($idCheck);
	
	if( $idCheck->num_rows == 1 ){
		$row1 = $idCheck->fetch_row();
		print_r($row1);
		echo "<br>";
		$titleQuery = "SELECT Post_ID FROM rp_join WHERE (Word_ID='".$row1[0]."') AND Is_Tag = 0";
		$tagQuery = "SELECT Post_ID FROM rp_join WHERE (Word_ID='".$row1[0]."') AND Is_Tag = 1";
	}
	else{
		$row1 = $idCheck->fetch_row();
		print_r($row1);
		echo "<br>";
		$row2 = $idCheck->fetch_row();
		print_r($row2);
		echo "<br>";
		$titleQuery = "SELECT Post_ID FROM rp_join WHERE (Word_ID='".$row1[0]."' OR Word_ID='".$row2[0]."') AND Is_Tag = 0";
		$tagQuery = "SELECT Post_ID FROM rp_join WHERE (Word_ID='".$row1[0]."' OR Word_ID='".$row2[0]."') AND Is_Tag = 1";
	}
	
	//Get Title words
	//$titleQuery = "SELECT Post_ID FROM dictionary_post_join WHERE (Word='".$className."' OR Word='".$methodName."') AND Is_Tag = 0";
	$titleCheck = $conn->query($titleQuery);					

	// If the result is okay...
	if( $titleCheck == TRUE && $titleCheck->num_rows > 0){

		for( $i = 0; $i < $titleCheck->num_rows; $i++){
			$row = $titleCheck->fetch_row();
			$index = getRankIndex( $rankArray, $row[0] );
			
			if( $index > -1 ){
				$rankArray[$index][1]++;
			}
			else{
				$index = count($rankArray);
				$rankArray[$index] = array(0,0,0,0,0,0);
				$rankArray[$index][0] = $row[0];
				$rankArray[$index][1]++;
			}
		}
	}
	else{
		echo "No words in titles<br>";
	}


	//Get Tag words
	//$tagQuery = "SELECT Post_ID FROM dictionary_post_join WHERE (Word='".$className."' OR Word='".$methodName."') AND Is_Tag = 1";
	$tagCheck = $conn->query($tagQuery);					

	// If the result is okay...
	if( $tagCheck == TRUE && $tagCheck->num_rows > 0){

		for( $i = 0; $i < $tagCheck->num_rows; $i++){
			$row = $tagCheck->fetch_row();
			$index = getRankIndex( $rankArray, $row[0] );
			
			if( $index > -1 ){
				$rankArray[$index][2]++;
			}
			else{
				$index = count($rankArray);
				$rankArray[$index] = array(0,0,0,0,0,0);
				$rankArray[$index][0] = $row[0];
				$rankArray[$index][2]++;
			}
		}
	}
	else{
		echo "No Tags<br>";
	}

	$rankArray = getPostDetails($conn, $rankArray);
	$rankedResults = getRankResultArray($rankArray, $titleWeight, $tagWeight, $scoreWeight, $viewWeight);

	printRankedResultLinks( $rankedResults, $numResultsToPrint );
}
else{
	echo "No Results for Class: ".$className." and Method: ".$methodName."<br>";
}


//echo "<br>";
//print2DArray($rankedResults);
//print2DArray($rankArray);
//echo "<br>";
//printRankedResultLinks( $rankedResults, $numResultsToPrint );
$conn->close();

function getRankIndex( $arr, $id ){
	
	for($i = 0; $i < count($arr); $i++){
		if( $arr[$i][0] == $id ){
			return $i;
		}
	}
	return -1;
}

function print2DArray( $arr ){
	for($i = 0; $i < count($arr); $i++){
		print_r($arr[$i]);
		echo "<br>";
	}
}

function getPostDetails( $conn, $rankArray ){
	
	for( $i = 0; $i < count($rankArray); $i++ ){
	
		//Get Post Answer ID, Score, Views
		$postQuery = "SELECT AcceptedAnswerId, Score, ViewCount, Title FROM Posts WHERE Post_ID=".$rankArray[$i][0];
		$postCheck = $conn->query($postQuery);
		
		// If the result is okay...
		if( $postCheck == TRUE ){

			$row = $postCheck->fetch_row();
			//print_r($row);
			//echo"<br>";
			$rankArray[$i][3] = $row[0];
			$rankArray[$i][4] = $row[1];
			$rankArray[$i][5] = $row[2];
			$rankArray[$i][6] = $row[3];
		}
	}
	return $rankArray;
}

function getMaxRankValue( $arr, $secondIndex ){
	
	$max = 0;
	
	for( $i = 0; $i < count($arr); $i++ ){
		if( $arr[$i][$secondIndex] > $max ){
			$max = $arr[$i][$secondIndex];
		}
	}
	
	return $max;
}

function getMinRankValue( $arr, $secondIndex ){
	
	$min = 0;
	
	for( $i = 0; $i < count($arr); $i++ ){
		
		if( $i == 0 ){
				$min = $arr[$i][$secondIndex];
		}
		else if( $arr[$i][$secondIndex] < $min ){
			$min = $arr[$i][$secondIndex];
		}
	}
	
	return $min;
}

function normalizeValue( $value, $min, $max ){
		if( $min != $max ){
			return ($value - $min)/($max - $min);
		}
		
		return 1;
}

function sortRankResultArray( $resArray ){
	
	for( $i = 0; $i < count($resArray); $i++ ){
		for( $j = $i+1; $j < count($resArray); $j++ ){
			
			if( $resArray[$j][1] > $resArray[$i][1] ){
					$tmp = $resArray[$i];
					$resArray[$i] = $resArray[$j];
					$resArray[$j] = $tmp;
			}
		}
	}
	
	return $resArray;
}

function getRankResultArray( $rankArray, $titleWeight, $tagWeight, $scoreWeight, $viewWeight ){
	
	$resArray = array();
	
	$minTitle = getMinRankValue( $rankArray, 1 );
	$maxTitle = getMaxRankValue( $rankArray, 1 );
	
	$minTag = getMinRankValue( $rankArray, 2 );
	$maxTag = getMaxRankValue( $rankArray, 2 );	
	
	$minScore = getMinRankValue( $rankArray, 4 );
	$maxScore = getMaxRankValue( $rankArray, 4 );
	
	$minViews = getMinRankValue( $rankArray, 5 );
	$maxViews = getMaxRankValue( $rankArray, 5 );
	
	for( $i = 0; $i < count($rankArray); $i++ ){
		
		if( !$rankArray[$i][3] ){
			continue;
		}
		
		$resIndex = count($resArray);
		
		// Get the post ID
		$resArray[$resIndex][0] = $rankArray[$i][0];
		
		// Calculate the rank
		$resArray[$resIndex][1] = $titleWeight * normalizeValue( $rankArray[$i][1], $minTitle, $maxTitle );
		$resArray[$resIndex][1] += $tagWeight * normalizeValue( $rankArray[$i][2], $minTag, $maxTag );
		$resArray[$resIndex][1] += $scoreWeight * normalizeValue( $rankArray[$i][4], $minScore, $maxScore );
		$resArray[$resIndex][1] += $viewWeight * normalizeValue( $rankArray[$i][5], $minViews, $maxViews );
		
		// Get the title
		$resArray[$resIndex][2] = $rankArray[$i][6];
	}
	
	return sortRankResultArray($resArray);
}

function printRankedResultLinks( $resArray, $numRes ){

	for($i = 0; $i < count($resArray) && $i < $numRes; $i++ ){
		
		$url = "http://stackoverflow.com/questions/".$resArray[$i][0];
		echo "<a href=".$url." target='blank'>".$resArray[$i][2]."</a><br>";
	}	
}

?>