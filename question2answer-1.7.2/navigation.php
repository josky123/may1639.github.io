<?php
	require './qa-include/qa-base.php';

	// Retrieve link info
	$result;
	$type = $_POST["type"];
	$id = $_POST["id"];
	if ($type == "home") {
		//$result = qa_db_query_raw("SELECT * FROM `library` ORDER BY ID");
		$result = qa_db_query_raw("SELECT * FROM `library` ORDER BY Name");
	}
	else if ($type == "library") {
		//$result = qa_db_query_raw("SELECT ID, Name FROM `package` WHERE LID = " . $id . " ORDER BY ID");
		$result = qa_db_query_raw("SELECT ID, Name FROM `package` WHERE LID = " . $id . " ORDER BY Name");
	}
	else if ($type == "package") {
		//$result = qa_db_query_raw("SELECT ID, Name FROM `type` WHERE PID = " . $id . " ORDER BY ID");
		$result = qa_db_query_raw("SELECT ID, Name FROM `type` WHERE PID = " . $id . " ORDER BY Name");
	}
	else if ($type == "class") {
		//$result = qa_db_query_raw("SELECT ID, Name, Arguments FROM `method` WHERE TID = " . $id . " ORDER BY ID");
		$result = qa_db_query_raw("SELECT ID, Name, Arguments FROM `method` WHERE TID = " . $id . " ORDER BY Name");
	}

	
	$numrows = mysqli_num_rows($result);
	$records = array();

	for ($x = 0; $x < $numrows; $x++)
	{
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$records[$x] = $row;
	}
	echo json_encode($records);
?>