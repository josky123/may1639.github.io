<?php
	require './qa-include/qa-base.php';

	// Retrieve source code
	$result;
	$type = $_POST["type"];
	$id = $_POST["id"];

	if ($type == "class") {
		$result = qa_db_query_raw("SELECT source FROM `type` WHERE ID = " . $id);
	}
	else if ($type == "method") {
		$result = qa_db_query_raw("SELECT source FROM `method` WHERE ID = " . $id);
	}

	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	echo json_encode($row);
?>