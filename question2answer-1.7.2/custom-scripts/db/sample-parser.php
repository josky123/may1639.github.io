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

	if( $xml->name == "row" && $xml->getAttribute('PostTypeId') == 1 ){
		
		$title = $xml->getAttribute('Title');
		$id = $xml->getAttribute('Id');
		$parts = preg_split('/\s+/', $title);
		$url = "http://stackoverflow.com/questions/".$id;
		$body = $xml->getAttribute('Body');
		//$partsB = preg_split('/\s+/', $body);
		
		$title = str_replace("'","\'",$title);
		
		
		$sql = "INSERT INTO Posts (Post_ID, Title, Body, URL) VALUES (".$id.", '".$title."', 'TEMP VALUE', '".$url."')";

		if ($conn->query($sql) === TRUE) {
			echo "New record for ID ".$id." was created successfully<br>";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		
		
		for( $i = 0; $i < count($parts); $i++ ){
			//echo $parts[$i];
			//echo "<br>";
			
			$part = str_replace("'","\'",$parts[$i]);
			//echo $part;
			
			$sql = "INSERT INTO Dictionary (Word) VALUES ('".$part."')";

			if ($conn->query($sql) === TRUE) {
				echo "New record for word ".$part." was created successfully<br>";
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
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