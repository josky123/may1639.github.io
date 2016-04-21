<?php

ini_set('max_execution_time', 14400); //300 seconds = 5 minutes

$servername = "localhost";
$username = "root";
$password = "YamadaKun2016";
$dbname = "Related_Posts";
$fileName = "../../../../../CprE 491/StackExchangeDataDump/Posts.xml";
//$fileName = "XMLTest.xml";
//$countMax = 10000;
$timeStart = microtime(true);

$count = 0;
$transactionCount = 0;
$postsPerTransaction = 500000;

// Giant List of Globals for Post Information
$id;
$postType;
$acceptedId;
$parentId;
$creationDate;
$score;
$viewCount;
$ownerId;
$ownerName;
$lastEditorId;
$lastEditorName;
$lastEditDate;
$lastActivityDate;
$title;
$answerCount;
$commentCount;
$favCount;
$communityOwnedDate;
$body;

// Globals for Dictionary Information
$word;

// Final Global for tag flag
$isTag;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
	//echo "success";
}

$xml = new XMLReader();

if( !$xml->open($fileName) ){
	die("Failed to open \"".$fileName."\". Program terminated.<br>");
}


$postQuery = "INSERT INTO Posts (Post_ID, PostTypeId, AcceptedAnswerId, ParentId, CreationDate, Score, ViewCount, OwnerUserId, OwnerDisplayName, LastEditorUserId, LastEditorDisplayName, LastEditDate, LastActivityDate, Title, AnswerCount, CommentCount, FavoriteCount, CommunityOwnedDate) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$dictionaryInsertQuery = "INSERT IGNORE INTO Dictionary (Word) VALUES ( ? )";

$joinQuery = "INSERT INTO dictionary_post_join (Post_ID, Word, Is_Tag) VALUES ( ?, ?, ? )";

$preparePost = $conn->prepare($postQuery);
$prepareDict = $conn->prepare($dictionaryInsertQuery);
$prepareJoin = $conn->prepare($joinQuery);

$preparePost->bind_param( "iiiisiiisissssiiis", $id, $postType, $acceptedId, $parentId, $creationDate, $score, $viewCount, $ownerId, $ownerName, $lastEditorId, $lastEditorName, $lastEditDate, $lastActivityDate, $title, $answerCount, $commentCount, $favCount, $communityOwnedDate );

$prepareDict->bind_param( "s", $word );

$prepareJoin->bind_param( "isi", $id, $word, $isTag );

$conn->query("START TRANSACTION");
while( $xml->read() && $count < 1000000 ){


	if( $xml->name == "row" ){
		
		/*
		 * These are the fields every XML entry is guaranteed to have.
		 */
		$id = $xml->getAttribute('Id');
		
		if( $id < 1282393 ){
			continue;
		}
		
		echo "Current ID:  ".$id.",  Current Count:  ".$count."<br>";
		//echo "                            Current Count:  ".$count."<br>";
		
		$postType = $xml->getAttribute('PostTypeId');
		$acceptedId = $xml->getAttribute('AcceptedAnswerId');
		$parentId = $xml->getAttribute('ParentId');
		$creationDate = $xml->getAttribute('CreationDate');
		$score = $xml->getAttribute('Score');
		$viewCount = $xml->getAttribute('ViewCount');
		$ownerId = $xml->getAttribute('OwnerUserId');
		$ownerName = $xml->getAttribute('OwnerDisplayName');
		$ownerName = str_replace("'","''",$ownerName);
		$lastEditorId = $xml->getAttribute('LastEditorUserId');
		$lastEditorName = $xml->getAttribute('LastEditorDisplayName');
		$lastEditorName = str_replace("'","''",$lastEditorName);
		$lastEditDate = $xml->getAttribute('LastEditDate');
		$lastActivityDate = $xml->getAttribute('LastActivityDate');
		
		// Title Requires Special Treatment
		$title = $xml->getAttribute('Title');
		// Removes apostrophes that may screw up the SQL call.
		//$title = str_replace("'","''",$title);
		$title2 = removePunctuationFromString($title);
		$parts = preg_split('/\s+/', $title2);
		
		$tags = $xml->getAttribute('Tags');
		$answerCount = $xml->getAttribute('AnswerCount');
		$commentCount = $xml->getAttribute('CommentCount');
		$favCount = $xml->getAttribute('FavoriteCount');
		$communityOwnedDate = $xml->getAttribute('CommunityOwnedDate');
		$body = $xml->getAttribute('Body');
		
		$preparePost->execute();
	
		$isTag = 0;
		for( $i = 0; $i < count($parts); $i++ ){
				$word = $parts[$i];
				
				if( strcmp( $word, "" ) && strcmp( $word, " " ) ){
					$prepareDict->execute();
					$prepareJoin->execute();
				}
		}
	
		if( $postType == 1 ){
			$isTag = 1;
			$tags2 = str_replace("<"," ",$tags);
			$tags2 = str_replace(">"," ",$tags2);
			$tags2 = substr( $tags2, 1, -1 );
			$tags2 = preg_split('/\s+/', $tags2);
			
			for( $i = 0; $i < count($tags2); $i++ ){
				
				$word = $tags2[$i];
				
				if( strcmp( $word, "" ) && strcmp( $word, " " ) ){
					$prepareDict->execute();
					$prepareJoin->execute();
				}
			}
		}
	
		$count++;
		$transactionCount++;
		
		if( $transactionCount == $postsPerTransaction ){
			$conn->query("COMMIT");
			$conn->query("START TRANSACTION");
			$postsPerTransaction = 0;
		}
		
	}
}
$conn->query("COMMIT");
$preparePost->close();
$prepareDict->close();
$prepareJoin->close();
$xml->close();
$conn->close();
echo "Parsing Complete.  Script executed in ".date("H:i:s",microtime(true)-$timeStart).".";


function removePunctuationFromString( $str ){
		
	$str = str_replace("."," ",$str);
	$str = str_replace(","," ",$str);
	$str = str_replace("?"," ",$str);
	$str = str_replace("!"," ",$str);
	$str = str_replace("&"," ",$str);
	$str = str_replace("("," ",$str);
	$str = str_replace(")"," ",$str);
	$str = str_replace("{"," ",$str);
	$str = str_replace("}"," ",$str);
	$str = str_replace("["," ",$str);
	$str = str_replace("]"," ",$str);
	$str = str_replace(":"," ",$str);
	$str = str_replace(";"," ",$str);
	$str = str_replace("<"," ",$str);
	$str = str_replace(">"," ",$str);
	$str = str_replace("#"," ",$str);
	$str = str_replace("+"," ",$str);
	$str = str_replace("\""," ",$str);
	$str = str_replace("/"," ",$str);
	$str = str_replace("\t"," ",$str);
	
	return $str;
}


?>