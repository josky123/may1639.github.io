<?php
	require './qa-include/qa-base.php';
	// echo "In file\n";
	// ini_set ("display_errors", "1");
	// error_reporting(E_ALL);

	$path = getcwd() . "/uploads";
	$dest = getcwd() . "/extract";
	$name = $_POST["name"];
	$action = $_POST["action"];

	if (file_exists($path . "/" . $name))
	{
		if ($action == "parse")
		{	// moove file to extact folder, unzip, delete file, parse new folder and add new java library to database
			copy($path . "/" . $name, $dest . "/" . $name);
			chdir($dest);
			exec("jar xf " . $name);
			unlink($name);
			$source = pathinfo($name, PATHINFO_FILENAME);
			// run parser on file and add it to the database
			echo shell_exec("java -jar ExtractSource.jar " . $source);
			//echo shell_exec("java Client " . $source);
			//echo "java " . getcwd() . "/ExtractSource.jar " . $source;
			//echo exec("ls");
			chdir("..");
		}
		else if ($action == "delete")
		{
			// delete file from directory $path
			$result = unlink($path . "/" . $name);
			if ($result == 1) {
				echo "File \"" . $name . "\" was deleted.";
			}
			else
				echo "Error: Could not delete file \"" . $name . "\".";
			
		}
		else if ($action == "remove")
		{
			// remove library from database and remove folder from extract folder
			//echo "TODO: remove library from database.";
			chdir($dest);
			$folder = pathinfo($name, PATHINFO_FILENAME);
			exec("rm -rf " . $folder);
			//exec("ls");
			//echo "DELETE FROM library WHERE Name = \"" . $folder . "\"";
			echo qa_db_query_raw("DELETE FROM library WHERE Name = \"" . $folder . "\"");
			chdir("..");
		}
	}
	else
	{
		echo "File \"" . $name . "\" does not exist.";
	}
?>