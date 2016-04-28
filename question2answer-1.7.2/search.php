<?php
	// ini_set('display_errors', 'On');
	// error_reporting(E_ALL | E_STRICT);
	require './qa-include/qa-base.php';

	// Retrieve link info
	$result;
	$type = $_POST["searchType"];
	$name = "%" . $_POST["name"] . "%";
	$search1 = $_POST["name"];
	$search2 = $_POST["name"] . "%";
	$search3 = "%" . $_POST["name"];
	//$name = $_POST["name"];
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
		if (mysqli_num_rows($result) == 0) {
			searchType();
			if (mysqli_num_rows($result) == 0) {
				searchPackage();
				if (mysqli_num_rows($result) == 0) {
					searchLibrary();
				}
			}
		}
	}

	function searchMethod() {
		global $result;
		global $name;
		global $search1;
		global $search2;
		global $search3;
		// $result = qa_db_query_raw(" SELECT m.Name AS MName, m.ID AS MID, m.Arguments AS Args, t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM method m, type t, package p, library l
		// 							WHERE m.Name LIKE '$name' AND m.TID = t.ID AND t.PID = p.ID AND p.LID = l.ID 
		// 							WHERE (m.name = '$search1') desc, length(m.name)
		// 							ORDER BY p.Name");

		// $result = qa_db_query_raw(" SELECT m.Name AS MName, m.ID AS MID, m.Arguments AS Args, t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM method m, type t, package p, library l
		// 							WHERE m.Name LIKE '$name' AND m.TID = t.ID AND t.PID = p.ID AND p.LID = l.ID 
		// 							WHERE (m.name = '$search1') desc, length(m.name)
		// 							");

		$result = qa_db_query_raw(" SELECT m.Name AS MName, m.ID AS MID, m.Arguments AS Args, t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
									FROM method m, `type` t, package p, library l
									WHERE m.Name LIKE '$name' AND m.TID = t.ID AND t.PID = p.ID AND p.LID = l.ID 
									ORDER BY FIELD(MName, '$search1', '$search2', '$search3', '$name') DESC, MName");


		// $result = qa_db_query_raw(" SELECT m.Name AS MName, m.ID AS MID, m.Arguments AS Args, t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM method m, type t, package p, library l
		// 							WHERE m.Name = '$search1' AND m.TID = t.ID AND t.PID = p.ID AND p.LID = l.ID 
		// 							UNION
		// 							SELECT m.Name AS MName, m.ID AS MID, m.Arguments AS Args, t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		//  							FROM method m, type t, package p, library l
		// 							WHERE m.Name LIKE '$name' AND m.TID = t.ID AND t.PID = p.ID AND p.LID = l.ID 
		//  							");


	}
	function searchType() {
		global $result;
		global $name;
		global $search1;
		global $search2;
		global $search3;
		// $result = qa_db_query_raw(" SELECT t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM type t, package p, library l
		// 							WHERE t.Name LIKE '$name' AND t.PID = p.ID AND p.LID = l.ID 
		// 							ORDER BY t.Name");

		// $result = qa_db_query_raw(" SELECT t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM type t, package p, library l
		// 							WHERE t.Name = '$search1' AND t.PID = p.ID AND p.LID = l.ID
		// 							UNION
		// 							SELECT t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM type t, package p, library l
		// 							WHERE t.Name LIKE '$name' AND t.PID = p.ID AND p.LID = l.ID
		// 							ORDER BY TName = '$search1'");

		$result = qa_db_query_raw(" SELECT t.Name AS TName, t.ID AS TID, p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
									FROM `type` t, package p, library l
									WHERE t.Name LIKE '$name' AND t.PID = p.ID AND p.LID = l.ID 
									ORDER BY FIELD(TName,'$search1', '$search2', '$search3', '$name') DESC, TName");
	}
	function searchPackage() {
		global $result;
		global $name;
		global $search1;
		global $search2;
		global $search3;
		// $result = qa_db_query_raw(" SELECT p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM package p, library l
		// 							WHERE p.Name LIKE '$name' AND p.LID = l.ID 
		// 							ORDER BY p.Name");

		// $result = qa_db_query_raw(" SELECT p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM package p, library l
		// 							WHERE p.Name = '$search1' AND p.LID = l.ID
		// 							UNION
		// 							SELECT p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
		// 							FROM package p, library l
		// 							WHERE p.Name LIKE '$name' AND p.LID = l.ID
		// 							ORDER BY PName = '$search1'");

		$result = qa_db_query_raw(" SELECT p.Name AS PName, p.ID AS PID, l.Name AS LName, l.ID AS LID
									FROM package p, library l
									WHERE p.Name LIKE '$name' AND p.LID = l.ID 
									ORDER BY FIELD(PName,'$search1', '$search2', '$search3', '$name') DESC, PName");
	}
	function searchLibrary() {
		global $result;
		global $name;
		global $search1;
		global $search2;
		global $search3;
		// $result = qa_db_query_raw(" SELECT l.Name AS LName, l.ID AS LID
		// 							FROM library l
		// 							WHERE l.Name LIKE '$name' 
		// 							ORDER BY LName");

		$result = qa_db_query_raw(" SELECT l.Name AS LName, l.ID AS LID
									FROM library l
									WHERE l.Name = '$search1'
									SELECT l.Name AS LName, l.ID AS LID
									FROM library l
									WHERE l.Name LIKE '$name'
									ORDER BY FIELD(LName,'$search1', '$search2', '$search3', '$name') DESC, LName");
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