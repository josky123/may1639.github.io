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
		$body = $xml->getAttribute('Body');
		$ownerId = $xml->getAttribute('OwnerUserId');
		$ownerName = $xml->getAttribute('OwnerDisplayName');
		$lastEditorId = $xml->getAttribute('LastEditorUserId');
		$lastEditorName = $xml->getAttribute('LastEditorDisplayName');
		$lastEditDate = $xml->getAttribute('LastEditDate');
		$lastActivityDate = $xml->getAttribute('LastActivityDate');
		
		// Title Requires Special Treatment
		$title = $xml->getAttribute('Title');
		$parts = preg_split('/\s+/', $title);
		
		$tags = $xml->getAttribute('Tags');
		$answerCount = $xml->getAttribute('AnswerCount');
		$commentCount = $xml->getAttribute('CommentCount');
		$favCount = $xml->getAttribute('FavoriteCount');
		$communityOwnedDate = $xml->getAttribute('CommunityOwnedDate');
		
		$url = "http://stackoverflow.com/questions/".$id;		
		
		// Removes apostrophes that may screw up the SQL call.
		$title = str_replace("'","\'",$title);
		
		/*
		 * Perform the main query.
		 */
		$sql = "INSERT INTO Posts (Post_ID, PostTypeId, CreationDate, Score, Body, LastEditorUserId, LastEditorDisplayName, LastEditDate, LastActivityDate, CommentCount, URL) VALUES (".$id.", ".$postType.", '".$creationDate."', ".$score.", 'BODY'".", ".$lastEditorId.", '".$LastEditorName."', '".$lastEditDate."', '".$lastActivityDate."', ".$commentCount.", '".$url."')";

		if ($conn->query($sql) === TRUE) {
			//echo "New record for ID ".$id." was created successfully<br>";
		} else {
			//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
			//echo $title."<br><br>";
		}
		
		/*
		 * Perform conditional queries.
		 */
		if( $ownerId ){
			
			$sql = "UPDATE posts SET OwnerUserId=".$ownerId." WHERE Post_ID=".$id;
			
			if ($conn->query($sql) === TRUE) {
				//echo "Successfully updated OwnerUserId for post ID ".$id."<br>";
			} else {
				//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
				//echo $ownerId."<br><br>";
			}
		}
		
		if( $ownerName ){
			
			$sql = "UPDATE posts SET OwnerDisplayName='".$ownerName."' WHERE Post_ID=".$id;
			
			if ($conn->query($sql) === TRUE) {
				//echo "Successfully updated OwnerDisplayName for post ID ".$id."<br>";
			} else {
				//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
				//echo $ownerName."<br><br>";
			}
		}
		
		if( $communityOwnedDate ){
			
			$sql = "UPDATE posts SET CommunityOwnedDate='".$communityOwnedDate."' WHERE Post_ID=".$id;
			
			if ($conn->query($sql) === TRUE) {
				//echo "Successfully updated CommunityOwnedDate for post ID ".$id."<br>";
			} else {
				//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
				//echo $communityOwnedDate."<br><br>";
			}
		}
		
		if( $lastEditorName ){
			
			$sql = "UPDATE posts SET LastEditorDisplayName='".$lastEditorName."' WHERE Post_ID=".$id;
			
			if ($conn->query($sql) === TRUE) {
				//echo "Successfully updated LastEditorDisplayName for post ID ".$id."<br>";
			} else {
				//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
				//echo $lastEditorName."<br><br>";
			}
		}
		
		/*
		 * Several Additional Fields to consider with dealing with type 1.
		 */
		if( $postType == 1 ){
			
			$sql = "UPDATE posts SET ViewCount=".$viewCount.", Title='".$title."', AnswerCount=".$answerCount.", FavoriteCount=".$favCount." WHERE Post_ID=".$id;
			
			if ($conn->query($sql) === TRUE) {
				//echo "Successfully updated post ID ".$id."<br>";
			} else {
				//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br><br>";
			}

			if( $acceptedId ){
				
				$sql = "UPDATE posts SET AcceptedAnswerId='".$acceptedId."' WHERE Post_ID=".$id;
				
				if ($conn->query($sql) === TRUE) {
					//echo "Successfully updated AcceptedAnswerId for post ID ".$id."<br>";
				} else {
					//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
					//echo $acceptedId."<br><br>";
				}
			}			
		}
		/*
		 * Fields necessary only for type 2.
		 */
		else if( $postType == 2 ){
			
			$sql = "UPDATE posts SET ParentId='".$parentId."' WHERE Post_ID=".$id;
			
			if ($conn->query($sql) === TRUE) {
				//echo "Successfully updated ParentId for post ID ".$id."<br>";
			} else {
				//echo "<br>Error: " . $sql . "<br>" . $conn->error."<br>";
				//echo $parentId."<br><br>";
			}			
		}
		
		/*
		 * Add each word in the title to the dictionary.
		 */
		 
		for( $i = 0; $i < count($parts); $i++ ){
			
			$part = str_replace("'","\'",$parts[$i]);
			$dupFlag = false;
			$checkQuery = "SELECT Word_ID FROM Dictionary WHERE Word='".$part."'";
			
			$check = $conn->query($checkQuery);
			if( $check == TRUE ){
				if( mysql_num_rows($check) > 0 ){
					$dupFlag = true;
				}
			}
			else{
				echo "Error: " . $sql . "<br>" . $conn->error."<br><br>";
			}
			
			if( !$dupFlag ){
				
				$sql = "INSERT INTO Dictionary (Word) VALUES ('".$part."')";

				if ($conn->query($sql) === TRUE) {
					echo "New record for word \"".$part."\" was created successfully<br>";
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
				}
			}
		}
		
		
		/*
		for( $i = 0; $i < count($partsB); $i++ ){
			echo $partsB[$i];
			echo "<br>";
		}
		*/
		
		//echo $id." | ".$title." | ".$url;
		//echo "<br>";
	}
}

$xml->close();
$conn->close();

?>