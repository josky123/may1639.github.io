<?php
	require './qa-include/qa-base.php';

	$result = qa_db_query_raw("SELECT * FROM `library`");
	//$result = "Java 8";
	//echo "This is php";
	//var_dump($result);
	//echo $result;
	
	$numrows = mysqli_num_rows($result);
	$records = array();

	for ($x = 0; $x < $numrows; $x++)
	{
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$records[$x] = $row;
		//print_r($row);
		//var_dump($records[$x]);
		// for ($y = 0; $y < count($row); $y++)
		// {
		// 	$records[$x][$y] = $row[$y];
		// }

	}
	echo json_encode($records);
?>