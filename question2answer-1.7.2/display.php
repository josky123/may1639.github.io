<?php
	//$path = "http://may1639-2.ece.iastate.edu/q2a/uploads";
	//$path = "uploads";
	$uploads = "uploads";
	
	// $fileList = glob($path . "/*.zip");
	// var_dump($fileList);

	// echo "<br>";

	// $fileList = scandir("uploads");
	// var_dump($fileList);

	// echo "<br>";

	$fileList = array();
	if($dir = opendir($uploads))
	{
		while ($file = readdir($dir)) 
		{
			if (!is_dir($file))
				$fileList[] = $file;
		}
		closedir($dir);
	}
	//var_dump($fileList);

	// get list of files
	// $fileList = array();
	// if($dir = opendir($path))
	// {
	// 	while ($file = readdir($dir)) 
	// 	{
	// 		if (!is_dir($file))
	// 			$fileList[] = $file;
	// 	}
	// 	closedir($dir);
	// }
	
	// get list of files
	//$fileList = scandir("uploads");
	$numFiles = count($fileList);

	$path = "http://may1639-2.ece.iastate.edu/q2a/uploads";

	// set up html response
	$links = array();
	$parseButtons = array();
	$deleteButtons = array();
	$removeButtons = array();
	for ($x = 0; $x < $numFiles; $x++)
	{
		$entry = $fileList[$x];
		// create download links
		$links[] = "<a href=\"" . $path . "/" . $entry . "\" download=\"". $entry . "\">" . $entry . "</a>";
		// create parse buttons
		$parseButtons[] = "<button type=\"button\" onclick=\"" . "parseFile('" . $entry . "')\">" . "Add Library" . "</button>";
		// create delete buttons
		$deleteButtons[] = "<button type=\"button\" onclick=\"" . "deleteFile('" . $entry . "')\">" . "Delete File" . "</button>";
		// create remove buttons
		$removeButtons[] = "<button type=\"button\" onclick=\"" . "removeFile('" . $entry . "')\">" . "Remove Library" . "</button>";
	}

	// var_dump($links);
	// echo "<br>";

	$response = "";
	// $response = "<h1 id=\"contentsHeader\">Uploaded Files</h1>";
	// $response .= "<table id=\"fileTable\">";
	for ($x = 0; $x < $numFiles; $x++)
	{
		// start row
		$response .= "<tr>";

		// add link
		$response .= "<td>" . $links[$x] . "</td>";

		// add parse button
		$response .= "<td>" . $parseButtons[$x] . "</td>";

		// add delete button
		$response .= "<td>" . $deleteButtons[$x] . "</td>";

		// add remove button
		$response .= "<td>" . $removeButtons[$x] . "</td>";

		// end row
		$response .= "</tr>";
	}

	echo $response;
?>