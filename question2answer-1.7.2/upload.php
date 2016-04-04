<?php
	//echo getcwd() . "\n";
	// ini_set ("display_errors", "1");
	// error_reporting(E_ALL);
	
	// specifies the directory where the file is going to be placed
	$target_dir = getcwd() . "/uploads/";
	if($_POST["source"] == "file")
	{
		// allowed file types
		$allowed = array("application/x-zip-compressed", "application/x-compressed");

		$numfiles = count($_FILES["files"]["name"]);

		for ($count = 0; $count < $numfiles; $count++)
		{
			
			// specifies the path of the file to be uploaded
			$target_file = $target_dir . basename($_FILES["files"]["name"][$count]);
			// upload status: 1=good, 0=bad
			$uploadOk = 1;

			// holds the file extension of the file
			$fileType = pathinfo($target_file,PATHINFO_EXTENSION);

			echo "$target_file: " . $target_file . "\n";
			echo "temporary file: " . $_FILES["files"]["tmp_name"][$count] . "\n";
			echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
			echo "System temporary directory: " . sys_get_temp_dir()."\n";
			
			// //Check if file is a zip file
		 //    if(in_array($_FILES["files"]["type"][$count], $allowed)
		 //    {
		 //        echo "File is a zip file.";
		 //        $uploadOk = 1;
		 //    } else {
		 //        echo "File is not a zip file.";
		 //        $uploadOk = 0;
		 //    }
			
		// 	// Check if file already exists
			if (file_exists($target_file)) 
			{
			    echo "File already exists.\n";
			    $uploadOk = 0;
			}
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) 
			{
			    echo "Error, file was not uploaded.\n";
			} else  // if everything is ok, try to upload file
			{
			    if (move_uploaded_file($_FILES["files"]["tmp_name"][$count], $target_file)) 
			    {
			        echo "The file ". basename( $_FILES["files"]["name"][$count]). " has been uploaded.\n";
			    } else {
			        echo "There was an error uploading your file.\n";
			    }
			}
		}
		echo var_dump($_FILES);
	} else if ($_POST["source"] == "URL")
	{
		// get URL and file name
		$url = $_POST["URL"];
		$filename = pathinfo($url, PATHINFO_FILENAME);
		$filepath = $target_dir . $filename['filename'];

		$isOk = false;

		$newf = false;

	    $file = fopen ($url, 'rb');
	    if ($file) {
	        $newf = fopen ($filepath, 'wb');
	        if ($newf) {
	            while(!feof($file)) {
	                fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
	            }
	            $isOk = true;
	        }
	    }
	    if ($file) {
	        fclose($file);
	    }
	    if ($newf) {
	        fclose($newf);
	    }

		// $tempFile = file_get_contents($url);
		// file_put_contents($target_dir . $filename, $tempFile);
		if ($isOk)
			echo "Uploaded file " . $filename .".\n";
		else
			echo "Failed to upload file " . $filename . ".\n";
		echo "Uploads directory contents:\n" . exec("ls uploads");
	}
	
	
?> 