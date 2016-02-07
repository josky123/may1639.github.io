<?php

$xml = new XMLReader();
$xml->open("http://may1639.sd.ece.iastate.edu/question2answer-1.7.2/sample-xml/cd_catalog.xml");


echo "<dl>"

while( $xml->read() ){
	
	echo xml->name;
	
	//echo "<dt>"$xml->getAttribute('TITLE')."</dt><dd>".$xml->getAttribute('ARTIST')."</dd><dd>".$xml->getAttribute('YEAR')."</dd>";
}
echo "</dl>"


$xml->close();

?>