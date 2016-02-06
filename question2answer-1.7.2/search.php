<?php
	ini_set('display_errors', 'On');
	error_reporting(E_ALL | E_STRICT);
	require './qa-include/qa-base.php';

	// Retrieve link info
	$result;
	$type = $_POST["searchType"];
	$name = $_POST["name"];
	if ($type == "Library") {
		searchLibrary();
	}
	else if ($type == "Package") {
		searchPackage();
	}
	else if ($type == "Class") {
		searchType();
	}
	else if ($type == "Method") {
		searchMethod();
	}
	else {
		searchMethod();
		if (!$result && mysqli_num_rows($result) == 0) {
			searchType();
			if (!$result && mysqli_num_rows($result) == 0) {
				searchPackage();
				if (!$result && mysqli_num_rows($result) == 0) {
					searchLibrary();
				}
			}
		}
	}

	function searchMethod() {
		global $result;
		global $name;
		$result = qa_db_query_raw(" SELECT m.Name AS MName, m.ID AS MID, m.Arguments AS Args, t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
									FROM method m, type t, package p, library l
									WHERE m.Name = '$name' AND m.TID = t.ID AND t.PID = p.ID AND p.LID = l.ID 
									ORDER BY p.Name");
	}
	function searchType() {
		global $result;
		global $name;
		$result = qa_db_query_raw(" SELECT t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
									FROM type t, package p, library l
									WHERE t.Name = " . $name . " AND t.PID = p.ID AND p.LID = l.ID 
									ORDER BY t.Name");
	}
	function searchPackage() {
		global $result;
		global $name;
		$result = qa_db_query_raw(" SELECT p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
									FROM package p, library l
									WHERE p.Name = '$name' AND p.LID = l.ID 
									ORDER BY p.Name");
	}
	function searchLibrary() {
		global $result;
		global $name;
		$result = qa_db_query_raw(" SELECT l.Name AS LName, l.ID AS LID
									FROM library l
									WHERE l.Name = '$name' 
									ORDER BY l.Name");
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