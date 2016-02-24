<?php

$occur = array();

/*  Proof of concept.  This works.
$occur = array("Peter"=>"35", "Ben"=>"37", "Joe"=>"43");

for( $i = 0; $i < 5; $i++){
	$str = "index".$i;
	$occur[$str] = $i;
}
print_r($occur);
*/

$xml = new XMLReader();
$xml->open("./XMLTest.xml");

while( $xml->read() ){

	if( $xml->name == "row" && $xml->getAttribute('PostTypeId') == 1 ){
		
		$title = $xml->getAttribute('Title');
		$id = $xml->getAttribute('Id');
		$parts = preg_split('/\s+/', $title);
		
		$body = $xml->getAttribute('Body');
		$partsB = preg_split('/\s+/', $body);
		
		print_r($parts);
		echo "<br>";
		//print_r($partsB);
		//echo "<br>";
		//echo "<br>";
		/*
		print_r($parts);
		echo "<br>";
		
		$tmp = array();
			
		for( $i = 0; $i < count($parts); $i++ ){
			$str = $parts[$i];
			echo $i.", ". $parts[$i]."<br>";
			$tmp[$str] = $id;
		}
		
		print_r($tmp);
		echo "<br><br>";
		*/
		
		/*
		for( $i = 0; $i < count($parts); $i++ ){
			
			$tmp_key = $parts[$i];
			//echo $tmp_key." ";
			
			if( $tmp_key != NULL && array_key_exists( $tmp_key, $occur )){
				
				$occ_len = count( $occur[$tmp_key] );
				$occur[$tmp_key][$occ_len] = $id;
			}
			else if( $tmp_key != NULL ){
				
				$occur[$tmp_key] = array();
				$occur[$tmp_key][0] = $id;
			}
			
			print_r($occur[$tmp_key]);
			echo "<br>";
			
		}
		//echo "<br>";
		*/
	}
}

/*
for( $i = 0; $i < count($occur); $i++ ){
	print_r($occur[$i]);
	echo "<br>";
}
*/
//print_r(array_keys($occur));

$xml->close();


?>