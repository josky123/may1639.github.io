<?php

$xml = new DomDocument("1.0", "UTF-8");

$container = $xml->createElement("container");
$container = $xml->appendChild($container);

$sale = $xml->createElement("sale");
$sale = $container->appendChild($sale);

$item = $xml->createElement("item", "Television");
$item = $sale->appendChild($item);

$price = $xml->createElement("price", "$100");
$price = $sale->appendChild($price);

$xml->FormatOutput = true;
$string_value = $xml->saveXML();
$xml->save("example.xml");

/*
$xml = new XMLReader();
$xml->open("./cd_catalog.xml");

while( $xml->read() ){
	
	//echo $xml->name, PHP_EOL;

	//echo "<dt>", $xml->getAttribute('TITLE'), "</dd>";//."</dt><dd>".$xml->getAttribute('ARTIST')."</dd><dd>".$xml->getAttribute('YEAR')."</dd>";
	//echo PHP_EOL;
	
	//echo $xml->name."<br>";
	
	
	if( $xml->name == "CD" ){
		
		if( $xml->getAttribute('TITLE') == NULL ){
			echo "NULL<br>";
		}
		
	}
	
	
}

$xml->close();
*/
?>