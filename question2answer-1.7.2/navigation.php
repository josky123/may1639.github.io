<?php
	require './qa-include/qa-base.php';

	// Retrieve link info
	$result;
	$type = $_POST["type"];
	if ($type == "home") {
		$result = qa_db_query_raw("SELECT * FROM `library`");
	}
	else if ($type == "library") {
		$id = $_POST["ID"];
		$result = qa_db_query_raw("SELECT p.ID, p.Name FROM `package p` WHERE p.LID = " + $id );
	}
	else if ($type == "package") {
		$id = $_POST["ID"];
		$result = qa_db_query_raw("SELECT t.ID, t.Name FROM `type t` WHERE t.PID = " + $id );
	}
	else if ($type == "class") {
		$id = $_POST["ID"];
		$result = qa_db_query_raw("SELECT m.ID, m.Name FROM `method m` WHERE m.TID = " + $id );
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