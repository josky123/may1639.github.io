<?php

ini_set('max_execution_time', 300); //300 seconds = 5 minutes

$servername = "localhost";
$username = "root";
$password = "YamadaKun2016";
$dbname = "Related_Posts";
$fileName = "../../../../../CprE 491/StackExchangeDataDump/Posts.xml";
//$fileName = "XMLTest.xml";
//$countMax = 10000;
$timeStart = microtime(true);

$count = 0;

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

while( $xml->read() && $count < 1000 ){


	if( $xml->name == "row" ){
		
		/*
		 * These are the fields every XML entry is guaranteed to have.
		 */
		$id = $xml->getAttribute('Id');
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
		$title = str_replace("'","''",$title);
		$title2 = removePunctuationFromString($title);
		$parts = preg_split('/\s+/', $title2);
		
		$tags = $xml->getAttribute('Tags');
		$answerCount = $xml->getAttribute('AnswerCount');
		$commentCount = $xml->getAttribute('CommentCount');
		$favCount = $xml->getAttribute('FavoriteCount');
		$communityOwnedDate = $xml->getAttribute('CommunityOwnedDate');
		
		$preparePost->execute();
	
		$isTag = 0;
		for( $i = 0; $i < count($parts); $i++ ){
				$word = $parts[$i];
				
				if( strcmp( $word, "" ) && strcmp( $word, " " ) ){
					$prepareDict->execute();
					$prepareJoin->execute();
				}
		}
	
		$count++;
	}
}
$preparePost->close();
$prepareDict->close();
$prepareJoin->close();
$conn->query("COMMIT");
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
	$str = str_replace("'"," ",$str);
	$str = str_replace("\t"," ",$str);
	
	return $str;
}

/*
 * Updates the given field to a new value for the given post ID.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $field
 *			The field to update.
 * @param $value
 *			The new value to assign.
 * @param $id
 *			The ID of the post to update.
 */
function updatePostTableFieldForId( $conn, $field, $value, $id ){
	
	$sql = "UPDATE posts SET ".$field."=".$value." WHERE Post_ID=".$id;
			
	if ($conn->query($sql) === TRUE) {
		//echo "Successfully updated ".$field." for post ID ".$id."<br>";
	} else {
		echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
		echo $value."<br><br>";
	}
}

/*
 * Returns the ID of the given word.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $word
 *			The word to identify.
 * @return
 *			The word ID if it exists.  0 otherwise.
 */
function getWordId( $conn, $word ){
	
	$checkQuery = "SELECT Word_ID FROM Dictionary WHERE Word='".$word."'";
	$check = $conn->query($checkQuery);					

	// If the result is okay...
	if( $check == TRUE && $check->num_rows > 0){
		$resRow = $check->fetch_row();
		return $resRow[0];
	}
	
	return 0;
}

/*
 * Adds the given word to the dictionary.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $word
 *			The word to add.
 */
function addWordToDictionary( $conn, $word ){
	$sql = "INSERT INTO Dictionary (Word) VALUES ('".$word."')";					

	if ($conn->query($sql) === TRUE) {
		//echo "New record for word \"".$word."\" was created successfully<br>";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}	
}

/*
 * Adds the indicated word ID to the JOIN table for the given post ID and appropriate tag status.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $wordId
 *			The ID of the word being joined.
 * @param $postId
 *			The ID of the post being joined.
 * @param $isTag
 *			Boolean indicating whether or not the given word is a tag for the post.
 */
function addWordIdToJoinTable( $conn, $wordId, $postId, $isTag ){
	
	$binary = ($isTag) ? 1 : 0;
	$sql = "INSERT INTO Dictionary_Post_Join (Word_ID, Post_ID, Is_Tag) VALUES (".$wordId.", ".$postId.", ".$binary.")";					

	if ($conn->query($sql) === TRUE) {
		//echo "New JOIN record for word ID ".$wordId." with post ID ".$postId." and tag status ".$binary." was created successfully<br>";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
}

/*
 * Determines whether the given word and post join already exists.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $wordId
 *			The ID of the word to check.
 * @param $postId
 *			The ID of the post to check.
 */
function checkDuplicateJoinEntry( $conn, $wordId, $postId ){
	
	$checkQuery = "SELECT * FROM Dictionary_Post_Join WHERE Word_ID=".$wordId." AND Post_ID=".$postId;
	$check = $conn->query($checkQuery);			
						
	// Check Success and Add if not already added
	if( $check != TRUE ){
		echo "Error: " . $checkQuery . "<br>" . $conn->error."<br>";
		return false;
	}
	else if( $check->num_rows > 0 ){
		return true;
	}
	return false;
}

/*
 * Determines whether or not the given word is already in the dictionary.  Can most likely be replaced by getWordId.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $word
 *			The word to check.
 */
function checkDuplicateDictionaryEntry( $conn, $word ){
	
	$checkQuery = "SELECT Word_ID FROM Dictionary WHERE Word='".$word."'";
	$check = $conn->query($checkQuery);

	if( $check == TRUE && $check->num_rows > 0 ){
		return true;
	}
	
	return false;
}

/*
 * Determines whether or not the given post has already been parsed.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $postId
 *			The post to check.
 */
function checkDuplicatePostEntry( $conn, $postId ){
	
	$checkQuery = "SELECT * FROM Posts WHERE Post_ID=".$postId;
	$check = $conn->query($checkQuery);

	if( $check == TRUE && $check->num_rows > 0 ){
		return true;
	}
	
	return false;
}

/*
 * Adds the indicated words to the dictionary and joins them to the given post with the appropriate tag status.
 * 
 * @param $conn
 *			The database connection to use.
 * @param $wordArray
 *			An array of words to add to the dictionary and to JOIN to the given post.
 * @param $id
 *			The ID of the post being joined.
 * @param $tagTrue
 *			Boolean indicating whether or not the words in the array are tags for the given post.
 */
function addWordsToDictionaryAndJoin($conn, $wordArray, $id, $tagTrue){
			
	//Add each word to the dictionary.
	for( $i = 0; $i < count($wordArray); $i++ ){

		$part = $wordArray[$i];
		$partLen = strlen($part);
		$partChar = $part[$partLen-1];
		
		//echo "Post ID ".$id." and word ".$part." with tag binary ".$tagTrue."<br>";
				
		// TODO
		// Remove end punctuation, probably need a function here at some point.
		if( $partChar == '?' || $partChar == '.' || $partChar == '!' ){
			$part = substr( $part, 0, -1 );
		}
		
		// Check whether a word is duplicate
		$dupFlag = checkDuplicateDictionaryEntry( $conn, $part );
				
		// If it is a new word
		if( !$dupFlag ){
				
			// Add the new Word to the dictionary
			addWordToDictionary( $conn, $part );
			
			// Add the new word to the Dictionary/post join table
			$resId = getWordId( $conn, $part );
			addWordIdToJoinTable( $conn, $resId, $id, $tagTrue );				
		} 
		// Check if the duplicate word needs to be added to the dictionary/post join table for this post ID.
		else{
					
			// First, get the word id.
			$resId = getWordId( $conn, $part );
					
			if( !checkDuplicateJoinEntry( $conn, $resId, $id ) ){
						
				addWordIdToJoinTable( $conn, $resId, $id, $tagTrue );		
			}
		}
	}
}

?>