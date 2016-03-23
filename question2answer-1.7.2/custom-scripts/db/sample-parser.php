<?php

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

$xml = new XMLReader();
$xml->open("./XMLTest.xml");

while( $xml->read() ){

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
		$lastEditorId = $xml->getAttribute('LastEditorUserId');
		$lastEditorName = $xml->getAttribute('LastEditorDisplayName');
		$lastEditDate = $xml->getAttribute('LastEditDate');
		$lastActivityDate = $xml->getAttribute('LastActivityDate');
		
		// Title Requires Special Treatment
		$title = $xml->getAttribute('Title');
		// Removes apostrophes that may screw up the SQL call.
		$title = str_replace("'","''",$title);
		$parts = preg_split('/\s+/', $title);
		
		// The body requires special treatment
		$body = $xml->getAttribute('Body');		
		//$body = htmlspecialchars_decode($body, ENT_QUOTES | ENT_HTML401 );
		$partsB = preg_split('/\s+/', $body);
		
		$tags = $xml->getAttribute('Tags');
		$answerCount = $xml->getAttribute('AnswerCount');
		$commentCount = $xml->getAttribute('CommentCount');
		$favCount = $xml->getAttribute('FavoriteCount');
		$communityOwnedDate = $xml->getAttribute('CommunityOwnedDate');
		
		$url = "http://stackoverflow.com/questions/".$id;		
		
	
		//Perform the main query.
		$sql = "INSERT INTO Posts (Post_ID, PostTypeId, CreationDate, Score, Body, LastEditorUserId, LastEditDate, LastActivityDate, CommentCount, URL) VALUES (".$id.", ".$postType.", '".$creationDate."', ".$score.", 'BODY'".", ".$lastEditorId.", '".$lastEditDate."', '".$lastActivityDate."', ".$commentCount.", '".$url."')";

		if ($conn->query($sql) === TRUE) {
			//echo "New record for ID ".$id." was created successfully<br>";
		} else {
			echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
			echo $title."<br><br>";
		}
		
		
		//Perform conditional queries.
		if( $ownerId ){
			updatePostTableFieldForId( $conn, "OwnerUserId", $ownerId, $id );
		}
		
		if( $ownerName ){
			updatePostTableFieldForId( $conn, "OwnerDisplayName", "'".$ownerName."'", $id );
		}

		if( $communityOwnedDate ){
			updatePostTableFieldForId( $conn, "CommunityOwnedDate", "'".$communityOwnedDate."'", $id );
		}
		
		if( $lastEditorName ){
			updatePostTableFieldForId( $conn, "LastEditorDisplayName", "'".$lastEditorName."'", $id );
		}
			
		//Several Additional Fields to consider with dealing with type 1.
		if( $postType == 1 ){
			
			// Three fields guaranteed for a type 1 post
			$sql = "UPDATE posts SET ViewCount=".$viewCount.", Title='".$title."', AnswerCount=".$answerCount.", FavoriteCount=".$favCount." WHERE Post_ID=".$id;
			
			if ($conn->query($sql) === TRUE) {
				//echo "Successfully updated post ID ".$id."<br>";
			} else {
				//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br><br>";
			}
			
			// Accepted Answer Id in the case of an accepted answer
			if( $acceptedId ){
				updatePostTableFieldForId( $conn, "AcceptedAnswerId", "'".$acceptedId."'", $id );
			}	
		}

		//Fields necessary only for type 2.
		else if( $postType == 2 ){
			
			updatePostTableFieldForId( $conn, "ParentId", "'".$parentId."'", $id );
		}

		if( $postType == 1 ){
			
			//echo $id." ";
			//print_r("tags"
			
			// Format and Add each tag to the dictionary
			$tags2 = str_replace("<"," ",$tags);
			$tags2 = str_replace(">"," ",$tags2);
			$tags2 = substr( $tags2, 1, -1 );
			$tags2 = preg_split('/\s+/', $tags2);
			//print_r($tags2);
			//echo "<br>";
			addWordsToDictionaryAndJoin($conn, $tags2, $id, true);
			
			// Add each word in the title to the dictionary.
			addWordsToDictionaryAndJoin($conn, $parts, $id, false);
		}	
		
/*
		echo $id."<br>".$body."<br>";
		
		
		for( $i = 0; $i < count($partsB); $i++ ){
			//echo $partsB[$i];
			//echo "<br>";
			
			$part = $partsB[$i];
			$partLen = strlen($part);
			$partChar = $part[$partLen-1];
			
			if( $partChar == '?' || $partChar == '.' || $partChar == '!' ){
				$part = substr( $part, 0, -1 );
			}
		
			$dupFlag = false;
			$checkQuery = "SELECT Word_ID FROM Dictionary WHERE Word='".$part."'";
			
			$check = $conn->query($checkQuery);
			if( $check->num_rows > 0 ){
				$dupFlag = true;
			}
				
			//$dupCheck = ($dupFlag) ? 'true' : 'false';
			//echo $dupCheck."<br>";
			
			if( !$dupFlag ){
				
				$sql = "INSERT INTO Dictionary (Word) VALUES ('".$part."')";
				if ($conn->query($sql) === TRUE) {
					echo "New record for word \"".$part."\" was created successfully<br>";
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
				}
			}
		}
*/		
		
		//echo $id." | ".$title." | ".$url;
		//echo "<br>";
	}
}

$xml->close();
$conn->close();
echo "Parsing Complete.";

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